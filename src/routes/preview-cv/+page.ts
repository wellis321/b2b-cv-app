import { cvStore } from '$lib/stores/cvDataStore';
import { session } from '$lib/stores/authStore';
import { redirect } from '@sveltejs/kit';
import { get } from 'svelte/store';
import type { PageLoad } from './$types';
import { supabase } from '$lib/supabase';

export const load: PageLoad = async ({ url }) => {
    // Get username from query parameter if available
    const username = url.searchParams.get('username');

    // Get current user session
    const currentSession = get(session);

    // Always require authentication for preview-cv page
    if (!currentSession || !currentSession.user) {
        console.log('No valid session found, redirecting to login');
        throw redirect(302, `/?returnTo=${encodeURIComponent(url.pathname + url.search)}`);
    }

    try {
        // If a username is provided, verify the user has permission to view it
        if (username) {
            // First, get the current user's profile
            const { data: myProfile, error: profileError } = await supabase
                .from('profiles')
                .select('username')
                .eq('id', currentSession.user.id)
                .single();

            if (profileError && profileError.code !== 'PGRST116') {
                console.error('Error fetching user profile:', profileError);
            }

            // Only allow access if the requested username matches the current user's username
            if (!myProfile || myProfile.username !== username) {
                console.log('Username mismatch, redirecting to public CV view');
                // Redirect to public CV view instead
                throw redirect(302, `/cv/@${username}`);
            }

            // Load data for the specified username (which we've verified belongs to the current user)
            await cvStore.loadByUsername(username);
        } else {
            // Load data for the current user - always create a default profile if none exists
            console.log('Loading CV data for current user');
            await cvStore.loadCurrentUserData();
        }

        // Return empty props - actual data comes from the store
        return {};
    } catch (error) {
        // Handle non-redirect errors
        if (error instanceof Error && !(error instanceof Response)) {
            console.error('Error in preview-cv page load:', error);

            // Just return empty props and let the page component handle the error state
            // This ensures the page still loads even with incomplete data
            return {};
        }
        // Re-throw redirect errors
        throw error;
    }
};
