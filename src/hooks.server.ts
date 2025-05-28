import { createSupabaseServerClient } from '@supabase/auth-helpers-sveltekit';
import { redirect, type Handle } from '@sveltejs/kit';
import { PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY } from '$env/static/public';

// Public routes that don't require authentication
const publicRoutes = [
    '/',
    '/cv',
    '/security-review-client',
    '/dashboard'
];

export const handle: Handle = async ({ event, resolve }) => {
    // Create a Supabase client for the current request
    event.locals.supabase = createSupabaseServerClient({
        supabaseUrl: PUBLIC_SUPABASE_URL,
        supabaseKey: PUBLIC_SUPABASE_ANON_KEY,
        event
    });

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
    return resolve(event);
};
