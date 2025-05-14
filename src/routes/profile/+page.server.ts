import type { Actions, PageServerLoad } from './$types';
import { fail, redirect } from '@sveltejs/kit';
import { requireAuth } from '$lib/auth';

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
            const defaultProfile = {
                id: authUser.userId,
                email: authUser.email,
                full_name: '',
                phone: '',
                location: ''
            };
            return { profile: defaultProfile, session: locals.session };
        }

        return { profile: data, session: locals.session };
    } catch (error) {
        console.error('Unexpected error in profile load:', error);
        return { profile: null, error: 'An unexpected error occurred', session: null };
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
            const { data: { user }, error: userError } = await locals.supabase.auth.getUser();

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
                username: string;  // Username is required for Insert but optional for Update
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
            return fail(500, { error: 'An unexpected error occurred' });
        }
    }
};