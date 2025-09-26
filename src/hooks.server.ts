import { createServerClient } from '@supabase/ssr';
import { redirect, type Handle } from '@sveltejs/kit';
import { PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY } from '$env/static/public';
import { getCsrfToken } from '$lib/security/csrf';

// Public routes that don't require authentication
const publicRoutes = [
    '/',
    '/cv',
    '/security-review-client',
    '/dashboard'
];

export const handle: Handle = async ({ event, resolve }) => {
    // Generate CSRF token for this request
    const csrfToken = getCsrfToken(event.cookies);

    // Create a Supabase client for the current request
    event.locals.supabase = createServerClient(
        PUBLIC_SUPABASE_URL,
        PUBLIC_SUPABASE_ANON_KEY,
        {
            cookies: {
                get: (key) => event.cookies.get(key),
                set: (key, value, options) => {
                    // Ensure path is set to '/' if not provided
                    event.cookies.set(key, value, {
                        ...options,
                        path: options?.path || '/'
                    });
                },
                remove: (key, options) => {
                    // Ensure path is set to '/' if not provided
                    event.cookies.delete(key, {
                        ...options,
                        path: options?.path || '/'
                    });
                }
            }
        }
    );

    // Get the session from the cookies
    const { data: { session } } = await event.locals.supabase.auth.getSession();
    event.locals.session = session;

    // Allow access to CV pages without authentication
    if (event.url.pathname.startsWith('/cv/')) {
        return resolve(event);
    }

    // Allow access to public routes without authentication
    if (publicRoutes.some(route => event.url.pathname === route)) {
        return resolve(event);
    }

    // Handle API endpoints
    if (event.url.pathname.startsWith('/api/')) {
        // Special handling for session verification
        if (event.url.pathname === '/api/verify-session' && !session) {
            return new Response(JSON.stringify({ error: 'Unauthorized' }), {
                status: 401,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        // Allow API requests to proceed to endpoint handlers
        return resolve(event);
    }

    // Process the request
    const response = await resolve(event);

    // Add CSRF token to response headers for client access
    response.headers.set('X-CSRF-Token', csrfToken);

    // Add CORS headers for development
    if (process.env.NODE_ENV === 'development') {
        response.headers.set('Access-Control-Allow-Origin', 'http://localhost:5173');
        response.headers.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        response.headers.set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        response.headers.set('Access-Control-Allow-Credentials', 'true');
    }

    // We could track analytics server-side here, but we'll use the client-side
    // approach for better browser detection and to avoid duplicating logic

    return response;
};
