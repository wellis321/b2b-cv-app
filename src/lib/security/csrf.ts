import { dev } from '$app/environment';
import { safeLog } from '$lib/config';

// Name of the CSRF cookie
const CSRF_COOKIE_NAME = 'csrf_token';
// Name of the CSRF header
const CSRF_HEADER_NAME = 'X-CSRF-Token';
// CSRF token expiration time (2 hours)
const CSRF_TOKEN_EXPIRE_SECONDS = 7200;

/**
 * Generate a secure random token for CSRF protection
 */
export function generateCsrfToken(): string {
    // Create a crypto-secure random token
    const buffer = new Uint8Array(32);
    crypto.getRandomValues(buffer);
    return Array.from(buffer)
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');
}

/**
 * Get the current CSRF token from cookies or generate a new one
 */
export function getCsrfToken(cookies: any): string {
    // Try to get an existing token
    let token = cookies.get(CSRF_COOKIE_NAME);

    // If no token exists or it's invalid, generate a new one
    if (!token) {
        token = generateCsrfToken();
        setCsrfCookie(cookies, token);
    }

    return token;
}

/**
 * Set the CSRF token cookie
 */
export function setCsrfCookie(cookies: any, token: string): void {
    cookies.set(CSRF_COOKIE_NAME, token, {
        path: '/',
        httpOnly: true,
        secure: !dev, // Secure in production, not in dev
        sameSite: 'lax', // Provides some CSRF protection while allowing normal navigation
        maxAge: CSRF_TOKEN_EXPIRE_SECONDS // 2 hours
    });
}

/**
 * Validate a CSRF token from a request against the expected token
 */
export function validateCsrfToken(request: Request, expectedToken: string): boolean {
    // Get token from header
    const token = request.headers.get(CSRF_HEADER_NAME);

    if (!token) {
        safeLog('warn', 'CSRF token missing from request');
        return false;
    }

    // Time-constant comparison to prevent timing attacks
    // (using a simple string comparison would be vulnerable to timing attacks)
    return timingSafeEqual(token, expectedToken);
}

/**
 * Time-constant comparison of two strings to prevent timing attacks
 */
function timingSafeEqual(a: string, b: string): boolean {
    if (a.length !== b.length) {
        return false;
    }

    let result = 0;
    for (let i = 0; i < a.length; i++) {
        result |= a.charCodeAt(i) ^ b.charCodeAt(i);
    }

    return result === 0;
}

/**
 * Helper to check if a request method requires CSRF validation
 */
export function requiresCsrfCheck(method: string): boolean {
    // Only check CSRF for state-changing methods
    const methodsToCheck = ['POST', 'PUT', 'DELETE', 'PATCH'];
    return methodsToCheck.includes(method.toUpperCase());
}

/**
 * Get CSRF header name for use in client code
 */
export function getCsrfHeaderName(): string {
    return CSRF_HEADER_NAME;
}