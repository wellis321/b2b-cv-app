import { redirect } from '@sveltejs/kit';
import type { RequestEvent } from '@sveltejs/kit';

/**
 * Helper function to verify authentication on protected routes
 * This performs additional verification beyond just checking if a session exists
 */
export async function requireAuth(event: RequestEvent) {
    const { locals } = event;

    try {
        // First, check if session exists in locals (populated by hooks)
        if (!locals.session) {
            // If no session in locals, try to get one directly
            const { data: { session }, error } = await locals.supabase.auth.getSession();

            if (error) {
                console.error('Error getting session in requireAuth:', error.message);
                throw redirect(303, '/?error=session');
            }

            if (!session) {
                console.log('No valid session found, redirecting to login');
                throw redirect(303, '/');
            }

            // If we found a session that wasn't in locals, update locals
            locals.session = session;

            // Wait a short time for session to propagate
            await new Promise(resolve => setTimeout(resolve, 100));
        }

        // At this point we have a session, now verify the user exists
        try {
            const { data: profile, error } = await locals.supabase
                .from('profiles')
                .select('id')
                .eq('id', locals.session.user.id)
                .maybeSingle();

            if (error) {
                console.error('Error verifying user:', error);
                // If there's a database error, we'll still proceed if non-critical
                if (error.code === 'PGRST116') {
                    // This is just a not found error, which is critical
                    await locals.supabase.auth.signOut();
                    throw redirect(303, '/?error=profile');
                }
            } else if (!profile) {
                console.error('User profile not found, creating profile');

                // Instead of failing, try to create a profile for the user
                try {
                    const newProfile = {
                        id: locals.session.user.id,
                        email: locals.session.user.email,
                        updated_at: new Date().toISOString()
                    };

                    const { error: insertError } = await locals.supabase
                        .from('profiles')
                        .insert(newProfile);

                    if (insertError) {
                        console.error('Error creating profile:', insertError);
                        // Critical error, redirect to login
                        await locals.supabase.auth.signOut();
                        throw redirect(303, '/?error=create-profile');
                    }

                    // Profile created successfully
                    console.log('Profile created for user:', locals.session.user.id);
                } catch (profileErr) {
                    console.error('Unexpected error creating profile:', profileErr);
                    await locals.supabase.auth.signOut();
                    throw redirect(303, '/?error=unexpected');
                }
            }
        } catch (verifyErr) {
            // Only throw redirect if it's not already a redirect
            if (!(verifyErr instanceof Response)) {
                console.error('Error during user verification:', verifyErr);
                throw redirect(303, '/?error=verification');
            }
            throw verifyErr;
        }

        // If we reach here, the user is authenticated
        return {
            userId: locals.session.user.id,
            email: locals.session.user.email
        };
    } catch (error) {
        if (error instanceof Response) {
            // This is a redirect - pass it through
            throw error;
        }

        console.error('Unexpected error in requireAuth:', error);
        throw redirect(303, '/?error=auth');
    }
}