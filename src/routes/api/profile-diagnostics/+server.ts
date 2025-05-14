import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ locals }) => {
    try {
        // Get the session
        const { data: { session }, error: sessionError } = await locals.supabase.auth.getSession();

        if (sessionError) {
            console.error('Error getting session:', sessionError);
            return json({
                success: false,
                error: 'Session error',
                details: sessionError.message
            }, { status: 401 });
        }

        if (!session) {
            return json({
                success: false,
                error: 'No active session'
            }, { status: 401 });
        }

        // Get user details
        const userId = session.user.id;
        const authEmail = session.user.email;

        // Get profile data
        const { data: profile, error: profileError } = await locals.supabase
            .from('profiles')
            .select('*')
            .eq('id', userId)
            .single();

        if (profileError) {
            console.error('Error getting profile:', profileError);
            return json({
                success: false,
                error: 'Profile error',
                details: profileError.message,
                session: {
                    userId,
                    email: authEmail
                }
            }, { status: 500 });
        }

        // Return diagnostic information
        return json({
            success: true,
            auth: {
                userId,
                email: authEmail
            },
            profile,
            emailMatch: profile?.email === authEmail
        });
    } catch (error) {
        console.error('Unexpected error in profile diagnostics:', error);
        return json({
            success: false,
            error: 'Server error',
            details: error instanceof Error ? error.message : 'Unknown error'
        }, { status: 500 });
    }
};