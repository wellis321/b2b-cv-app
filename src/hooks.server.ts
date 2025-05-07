import { createSupabaseServerClient } from '@supabase/auth-helpers-sveltekit';
import { PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY } from '$env/static/public';
import type { Handle } from '@sveltejs/kit';

console.log('SUPABASE_URL:', PUBLIC_SUPABASE_URL);
console.log('SUPABASE_ANON_KEY:', PUBLIC_SUPABASE_ANON_KEY);

// List of public routes that don't require authentication
const publicRoutes = ['/', '/login', '/signup'];

export const handle: Handle = async ({ event, resolve }) => {
    // Debug: log cookies received in the request
    const cookieHeader = event.request.headers.get('cookie');
    console.log('Request cookies:', cookieHeader);

    try {
        // Create a Supabase client specifically for this request
        event.locals.supabase = createSupabaseServerClient({
            supabaseUrl: PUBLIC_SUPABASE_URL,
            supabaseKey: PUBLIC_SUPABASE_ANON_KEY,
            event
        });

        // Get the session from the request
        const { data: { session }, error } = await event.locals.supabase.auth.getSession();

        // Handle session retrieval errors
        if (error) {
            console.error('Error getting session in hooks:', error);
            event.locals.session = null;
        } else {
            // Debug: Log session information
            console.log('Server hook session:', session ? `User ID: ${session.user.id}` : 'No session');

            if (session) {
                // Basic session validation - we'll do more thorough checks
                // on protected routes via requireAuth
                event.locals.session = session;
            } else {
                // No session
                event.locals.session = null;
            }
        }
    } catch (error) {
        console.error('Error initializing Supabase client in hooks:', error);
        event.locals.session = null;
    }

    // Process the request and get the response
    const response = await resolve(event, {
        // Set transformPageChunk to ensure proper hydration of client
        transformPageChunk: ({ html }) => html
    });

    // Return the response with any modifications
    return response;
};
