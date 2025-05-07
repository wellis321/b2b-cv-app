import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ locals }) => {
    try {
        // Get the session from the request
        const { data: { session }, error } = await locals.supabase.auth.getSession();

        if (error) {
            console.error('Error getting session in verify-session endpoint:', error);
            return new Response(
                JSON.stringify({ error: 'Authentication error' }),
                { status: 401, headers: { 'Content-Type': 'application/json' } }
            );
        }

        if (!session) {
            console.log('No session found in verify-session endpoint');
            return new Response(
                JSON.stringify({ error: 'Not authenticated' }),
                { status: 401, headers: { 'Content-Type': 'application/json' } }
            );
        }

        // Verify the user exists in the database
        const { data: profile, error: profileError } = await locals.supabase
            .from('profiles')
            .select('id')
            .eq('id', session.user.id)
            .maybeSingle();

        if (profileError) {
            console.error('Error verifying user profile:', profileError);
            return new Response(
                JSON.stringify({ error: 'Profile verification error' }),
                { status: 401, headers: { 'Content-Type': 'application/json' } }
            );
        }

        if (!profile) {
            console.log('User profile not found during verification');
            return new Response(
                JSON.stringify({ error: 'Profile not found' }),
                { status: 401, headers: { 'Content-Type': 'application/json' } }
            );
        }

        // If we made it here, the session is valid
        return json({
            status: 'authenticated',
            userId: session.user.id
        });
    } catch (error) {
        console.error('Unexpected error in verify-session endpoint:', error);
        return new Response(
            JSON.stringify({ error: 'Server error' }),
            { status: 500, headers: { 'Content-Type': 'application/json' } }
        );
    }
};