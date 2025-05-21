import type { Actions, PageServerLoad } from './$types';
import { fail, redirect } from '@sveltejs/kit';
import { requireAuth } from '$lib/auth';
import type { DefaultProfile, ProfileData } from '$lib/types/profile';

export const load: PageServerLoad = async (event) => {
    try {
        // First verify the user is authenticated
        const authUser = await requireAuth(event);
        const { locals } = event;

        // At this point we know the user is authenticated and the profile exists
        console.log('User authenticated:', authUser.userId);

        // Fetch the complete profile data
        const { data, error } = await locals.supabase
            .from('profiles')
            .select('*')
            .eq('id', authUser.userId)
            .maybeSingle();

        console.log('Profile load result:', { data, error });

        if (error) {
            console.error('Error loading profile:', error);
            return { profile: null, error: error.message, session: locals.session };
        }

        if (!data) {
            console.log('No profile found but user is authenticated - creating default profile');
            // Create default profile data with email from session
            const defaultProfile: DefaultProfile = {
                id: authUser.userId,
                email: authUser.email,
                full_name: '',
                phone: '',
                location: '',
                username: `user${authUser.userId.substring(0, 8)}`,
                photo_url: null
            };
            return { profile: defaultProfile, session: locals.session };
        }

        return { profile: data, session: locals.session };
    } catch (error) {
        console.error('Unexpected error in profile load:', error);

        // Handle SvelteKit redirect
        if (error instanceof Error && error.name === 'Redirect') {
            throw error; // Rethrow redirects to allow normal navigation
        }

        // Get a more specific error message based on the error type
        let errorMessage = 'Unable to load your profile. Please try again.';

        if (error instanceof Error) {
            const errorText = error.message.toLowerCase();

            if (errorText.includes('auth') || errorText.includes('login') || errorText.includes('session')) {
                errorMessage = 'Your session has expired. Please log in again.';
            } else if (errorText.includes('not found') || errorText.includes('404')) {
                errorMessage = 'Your profile was not found. Please complete your profile setup.';
            } else if (errorText.includes('database') || errorText.includes('query')) {
                errorMessage = 'There was an issue with the database. Please try again later.';
            } else if (errorText.includes('permission') || errorText.includes('access')) {
                errorMessage = 'You don\'t have permission to access this profile.';
            }
        }

        return { profile: null, error: errorMessage, session: null };
    }
};

export const actions: Actions = {
    default: async (event) => {
        try {
            // First verify the user is authenticated
            const authUser = await requireAuth(event);
            const { request, locals } = event;

            // At this point we know the user is authenticated
            console.log('User authenticated for profile update:', authUser.userId);

            // Get the user's auth email from their session
            const {
                data: { user },
                error: userError
            } = await locals.supabase.auth.getUser();

            if (userError) {
                console.error('Error getting auth user:', userError);
                return fail(500, { error: 'Could not get user details' });
            }

            // Use the email from auth if available
            const authEmail = user?.email;

            const formData = await request.formData();
            const fullName = formData.get('fullName') as string;
            const formEmail = formData.get('email') as string;
            const phone = formData.get('phone') as string;
            const location = formData.get('location') as string;

            // Log form data
            console.log('Form data:', { fullName, formEmail, phone, location, authEmail });

            // Create the profile data object with correct typed structure
            let profileData: {
                id: string;
                full_name: string | null;
                email: string | null;
                phone: string | null;
                location: string | null;
                updated_at: string;
                username: string; // Username is required for Insert but optional for Update
            };

            // First check if the profile exists to determine if this is an insert or update
            const { data: existingProfile, error: checkError } = await locals.supabase
                .from('profiles')
                .select('username')
                .eq('id', authUser.userId)
                .single();

            if (checkError && checkError.code !== 'PGRST116') {
                console.error('Error checking existing profile:', checkError);
                return fail(500, { error: checkError.message });
            }

            // Build the profile data object
            profileData = {
                id: authUser.userId,
                full_name: fullName || null,
                email: authEmail || formEmail || null,
                phone: phone || null,
                location: location || null,
                updated_at: new Date().toISOString(),
                // Either use existing username or generate a default one
                username: existingProfile?.username || `user${authUser.userId.substring(0, 8)}`
            };

            // Log the profile data being sent
            console.log('Profile data being sent:', profileData);

            // Try to upsert the profile
            const { data, error } = await locals.supabase
                .from('profiles')
                .upsert(profileData, { onConflict: 'id' });

            // Log the complete response
            console.log('UPSERT RESULT:', { data, error });

            if (error) {
                console.error('Profile save error:', error);
                return fail(400, { error: error.message });
            }

            console.log('Profile saved successfully');
            throw redirect(303, '/profile');
        } catch (error) {
            console.error('Unexpected error saving profile:', error);

            // Get a more specific error message based on the error type
            let errorMessage = 'Unable to save your profile. Please try again later.';

            // Handle SvelteKit redirect (don't treat as an error)
            if (error instanceof Error && error.name === 'Redirect') {
                throw error; // Rethrow redirects to allow normal navigation
            }

            // Check for common error patterns
            if (error instanceof Error) {
                const errorText = error.message.toLowerCase();

                if (errorText.includes('storage') || errorText.includes('bucket')) {
                    errorMessage = 'There was an issue with the storage system. An administrator needs to set up the storage buckets.';
                } else if (errorText.includes('permission') || errorText.includes('access')) {
                    errorMessage = 'You don\'t have permission to perform this action.';
                } else if (errorText.includes('network') || errorText.includes('connection')) {
                    errorMessage = 'Network error. Please check your connection and try again.';
                } else if (errorText.includes('duplicate') || errorText.includes('already exists')) {
                    errorMessage = 'This profile information conflicts with an existing profile.';
                } else if (errorText.includes('auth') || errorText.includes('login') || errorText.includes('session')) {
                    errorMessage = 'Your session has expired. Please log in again.';
                } else if (errorText.includes('timeout')) {
                    errorMessage = 'The request timed out. Please try again.';
                } else if (errorText.includes('validation')) {
                    errorMessage = 'The profile data you submitted contains invalid information.';
                }
            }

            return fail(500, { error: errorMessage });
        }
    }
};
