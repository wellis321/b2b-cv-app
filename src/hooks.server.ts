import { createSupabaseServerClient } from '@supabase/auth-helpers-sveltekit';
import config, { safeLog } from '$lib/config';
import type { Handle } from '@sveltejs/kit';
import { redirect, error } from '@sveltejs/kit';
import { getCsrfToken, validateCsrfToken, requiresCsrfCheck } from '$lib/security/csrf';
import { rateLimit, applyAuthRateLimit } from '$lib/security/rateLimit';

// Define base public routes that don't require authentication
const basePublicRoutes = ['/', '/login', '/signup', '/security-review-client', '/cv'];

// Add development-only routes if not in production
const publicRoutes = [
    ...basePublicRoutes,
    // Only add security-test page to public routes in development
    ...(config.isDevelopment ? ['/security-test'] : [])
];

// List of API routes that are exempt from CSRF checks (e.g., webhooks)
const csrfExemptRoutes = [
    '/api/verify-session', // This is a read-only endpoint to verify a session
    '/api/update-profile-photo', // Special endpoint for photo uploads that handles auth separately
    '/api/csp-report' // CSP violation reporting endpoint
];

// Generate a CSP nonce - cryptographically random string for each request
function generateNonce() {
    return Buffer.from(crypto.getRandomValues(new Uint8Array(16))).toString('base64');
}

export const handle: Handle = async ({ event, resolve }) => {
    try {
        const path = event.url.pathname;
        const requestId = crypto.randomUUID(); // Generate a unique ID for this request

        // Generate a unique nonce for CSP if enabled in config
        if (config.security.cspNonce) {
            const nonce = generateNonce();
            event.locals.cspNonce = nonce;
        }

        // Use safeLog instead of console.log for secure logging
        safeLog('debug', `[${requestId}] Processing request`, { path });

        // Apply rate limiting for authentication and API endpoints
        if (path.startsWith('/api/')) {
            const rateLimitResponse = await rateLimit({
                max: 60, // 60 requests per minute
                windowMs: 60 * 1000, // 1 minute window
                keyGenerator: (req) => {
                    const ip = req.headers.get('x-forwarded-for') || 'unknown';
                    // Group similar API endpoints (e.g., /api/profiles/123 and /api/profiles/456)
                    const pathSegments = path.split('/').slice(0, 3).join('/');
                    return `api:${ip}:${pathSegments}`;
                }
            })(event.request, event.locals);

            if (rateLimitResponse) {
                return rateLimitResponse;
            }
        }

        // Apply stricter rate limiting for authentication endpoints
        if (path.startsWith('/auth/') || path === '/login' || path === '/signup') {
            const authRateLimitResponse = await applyAuthRateLimit(event.request, event.locals);
            if (authRateLimitResponse) {
                return authRateLimitResponse;
            }
        }

        // Create a Supabase client specifically for this request
        event.locals.supabase = createSupabaseServerClient({
            supabaseUrl: config.supabase.url,
            supabaseKey: config.supabase.anonKey,
            event
        });

        // Get the session from the request
        const {
            data: { session },
            error: sessionError
        } = await event.locals.supabase.auth.getSession();

        // Add session to event.locals
        if (sessionError) {
            safeLog('error', `[${requestId}] Error getting session`, {
                path,
                errorCode: sessionError.code,
                errorMessage: sessionError.message
            });
            event.locals.session = null;
        } else if (session) {
            // Set the session in locals
            event.locals.session = session;
            safeLog('debug', `[${requestId}] Session found for user`, {
                userId: session.user.id,
                path
            });

            // Optionally verify the user exists in profiles
            try {
                const { data: profile, error: profileError } = await event.locals.supabase
                    .from('profiles')
                    .select('id')
                    .eq('id', session.user.id)
                    .maybeSingle();

                if (profileError && profileError.code !== 'PGRST116') {
                    safeLog('error', `[${requestId}] Error verifying profile`, {
                        userId: session.user.id,
                        errorCode: profileError.code
                    });
                }

                // Create profile if it doesn't exist
                if (!profile) {
                    safeLog('info', `[${requestId}] Creating new profile`, { userId: session.user.id });
                    const { error: insertError } = await event.locals.supabase.from('profiles').insert({
                        id: session.user.id,
                        email: session.user.email,
                        updated_at: new Date().toISOString(),
                        username: `user${session.user.id.substring(0, 8)}` // Add default username
                    });

                    if (insertError) {
                        safeLog('error', `[${requestId}] Error creating profile`, {
                            userId: session.user.id,
                            errorCode: insertError.code
                        });
                    }
                }
            } catch (profileError) {
                safeLog('error', `[${requestId}] Unexpected error checking profile`, {
                    error: profileError
                });
            }
        } else {
            // No session
            event.locals.session = null;
            safeLog('debug', `[${requestId}] No session found`, { path });
        }

        // Always get or create a CSRF token regardless of auth status
        // This ensures CSRF protection for both authenticated and unauthenticated users
        const csrfToken = getCsrfToken(event.cookies);
        event.locals.csrfToken = csrfToken;

        // Check for CSRF token for API routes with state-changing methods
        const isApiRoute = path.startsWith('/api');
        const isExemptFromCsrf = csrfExemptRoutes.some(
            (route) => path === route || path.startsWith(`${route}/`)
        );
        const method = event.request.method;

        if (isApiRoute && !isExemptFromCsrf && requiresCsrfCheck(method)) {
            const isValidCsrf = validateCsrfToken(event.request, csrfToken);

            if (!isValidCsrf) {
                safeLog('warn', `[${requestId}] CSRF token validation failed`, {
                    path,
                    method
                });
                return new Response(
                    JSON.stringify({
                        success: false,
                        error: 'CSRF validation failed'
                    }),
                    {
                        status: 403,
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    }
                );
            }

            safeLog('debug', `[${requestId}] CSRF token validation passed`, { path });
        }

        // Check if this is a protected route and redirect if needed
        const isPublicRoute = publicRoutes.some(
            (route) => path === route || path.startsWith(`${route}/`)
        );

        safeLog('debug', `[${requestId}] Route check`, {
            path,
            isPublic: isPublicRoute,
            hasSession: !!event.locals.session
        });

        if (!isPublicRoute && !event.locals.session) {
            // Only redirect if not an API route
            if (!isApiRoute) {
                safeLog('info', `[${requestId}] Redirecting unauthenticated user`, { from: path });
                // Add current path to redirect back after login
                const returnTo = encodeURIComponent(path);
                throw redirect(303, `/?returnTo=${returnTo}`);
            }
        }
    } catch (hookError) {
        if (hookError instanceof Response && hookError.status === 303) {
            throw hookError;
        }

        safeLog('error', 'Unexpected error in hooks', { error: hookError });
        // For other errors, set session to null
        event.locals.session = null;
    }

    // Process the request and get the response
    const response = await resolve(event, {
        transformPageChunk: ({ html }) => {
            let modified = html;

            // If a CSRF token exists in locals, inject it into the page
            // This makes it available to client-side scripts
            if (event.locals.csrfToken) {
                modified = modified.replace(
                    '</head>',
                    `<meta name="csrf-token" content="${event.locals.csrfToken}"></head>`
                );
            }

            // Add the CSP nonce to any script tags if enabled
            if (config.security.cspNonce && event.locals.cspNonce) {
                // Add nonce to script tags
                modified = modified.replace(/<script\b/g, `<script nonce="${event.locals.cspNonce}"`);

                // Add meta tag with CSP nonce for client-side reference
                modified = modified.replace(
                    '</head>',
                    `<meta name="csp-nonce" content="${event.locals.cspNonce}"></head>`
                );
            }

            return modified;
        }
    });

    // Add security headers if enabled
    if (config.security.strictHeaders) {
        // Set security headers
        response.headers.set('X-Content-Type-Options', 'nosniff');
        response.headers.set('X-Frame-Options', 'DENY');
        response.headers.set('X-XSS-Protection', '1; mode=block');
        response.headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Add CORS headers for API routes in production
        if (event.url.pathname.startsWith('/api')) {
            // In production, only allow requests from our domain
            if (config.isProduction) {
                const allowedOrigin =
                    new URL(event.request.headers.get('origin') || '').hostname ===
                        new URL(config.appUrl || '').hostname
                        ? event.request.headers.get('origin')
                        : config.appUrl;

                response.headers.set('Access-Control-Allow-Origin', allowedOrigin || '');
                response.headers.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                response.headers.set(
                    'Access-Control-Allow-Headers',
                    'Content-Type, Authorization, X-CSRF-Token'
                );
                response.headers.set('Access-Control-Allow-Credentials', 'true');
                response.headers.set('Access-Control-Max-Age', '3600');
            } else {
                // In development, be more permissive
                response.headers.set('Access-Control-Allow-Origin', '*');
                response.headers.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                response.headers.set(
                    'Access-Control-Allow-Headers',
                    'Content-Type, Authorization, X-CSRF-Token'
                );
            }

            // Handle preflight requests
            if (event.request.method === 'OPTIONS') {
                return new Response(null, {
                    status: 204,
                    headers: response.headers
                });
            }
        }

        // Apply CSP in both production and development mode for testing purposes
        // Use nonce-based CSP if available and enabled
        if (config.security.cspNonce && event.locals.cspNonce) {
            const scriptSrc = `'self' 'nonce-${event.locals.cspNonce}'`;
            const styleSrc = `'self' 'unsafe-inline'`; // Keep 'unsafe-inline' for styles in development

            response.headers.set(
                'Content-Security-Policy',
                "default-src 'self'; " +
                `script-src ${scriptSrc}; ` +
                `style-src ${styleSrc}; ` +
                "img-src 'self' data: blob: https://*.supabase.co; " +
                "font-src 'self'; " +
                "connect-src 'self' https://*.supabase.co;" +
                "report-uri /api/csp-report;"
            );
        }
    }

    return response;
};
