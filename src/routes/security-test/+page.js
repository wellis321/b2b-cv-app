// Set this page to be prerendered at build time
export const prerender = true;

// In production, redirect away from this page
export function load({ url }) {
    // Check if we're in production mode
    const isDev = process.env.NODE_ENV === 'development';

    // If not in development mode, redirect to home page
    if (!isDev) {
        return {
            status: 302,
            redirect: '/'
        };
    }

    // Otherwise allow access in development
    return {};
}