import { error, fail, redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import { requireAuth } from '$lib/auth';

export const load: PageServerLoad = async (event) => {
    const { locals } = event;

    try {
        // Ensure user is authenticated
        const authUser = await requireAuth(event);

        // Get the user's profile
        const { data: profile, error: profileError } = await locals.supabase
            .from('profiles')
            .select('*')
            .eq('id', authUser.userId)
            .single();

        if (profileError) {
            console.error('Error fetching profile:', profileError);
            throw error(500, 'Could not fetch profile');
        }

        // Get the user's auth details
        const { data: { user }, error: userError } = await locals.supabase.auth.getUser();

        if (userError) {
            console.error('Error fetching user:', userError);
            throw error(500, 'Could not fetch user details');
        }

        return {
            profile,
            user,
            fixed: false
        };
    } catch (err) {
        console.error('Unexpected error:', err);
        throw error(500, 'An unexpected error occurred');
    }
};

export const actions: Actions = {
    default: async (event) => {
        const { request, locals } = event;

        try {
            // Ensure user is authenticated
            const authUser = await requireAuth(event);

            // Get the user's auth details to get the correct email
            const { data: { user }, error: userError } = await locals.supabase.auth.getUser();

            if (userError) {
                console.error('Error fetching user:', userError);
                return fail(500, { error: 'Could not fetch user details' });
            }

            if (!user) {
                return fail(400, { error: 'User not found' });
            }

            const formData = await request.formData();
            const fullName = formData.get('fullName') as string;
            const email = user.email; // Always use the auth email
            const phone = formData.get('phone') as string;
            const location = formData.get('location') as string;

            console.log('Attempting to fix profile with data:', {
                fullName,
                email,
                phone,
                location
            });

            // Update the profile with correct information
            const { data, error: updateError } = await locals.supabase
                .from('profiles')
                .update({
                    full_name: fullName || null,
                    email, // Guaranteed to be the authenticated email
                    phone: phone || null,
                    location: location || null,
                    updated_at: new Date().toISOString()
                })
                .eq('id', authUser.userId)
                .select();

            if (updateError) {
                console.error('Error updating profile:', updateError);
                return fail(500, { error: 'Could not update profile: ' + updateError.message });
            }

            console.log('Profile successfully fixed:', data);

            // Try to fetch the profile again to confirm
            const { data: updatedProfile, error: fetchError } = await locals.supabase
                .from('profiles')
                .select('*')
                .eq('id', authUser.userId)
                .single();

            if (fetchError) {
                console.error('Error fetching updated profile:', fetchError);
                return fail(500, {
                    error: 'Profile was updated but could not verify the result',
                    fixed: true
                });
            }

            return {
                profile: updatedProfile,
                fixed: true
            };
        } catch (err) {
            console.error('Unexpected error fixing profile:', err);
            return fail(500, { error: 'An unexpected error occurred' });
        }
    }
};