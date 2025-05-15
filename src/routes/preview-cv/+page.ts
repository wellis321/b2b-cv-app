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
        throw redirect(302, '/login?redirect=/preview-cv');
    }

    // If a username is provided, verify the user has permission to view it
    if (username) {
        // First, get the current user's profile
        const { data: myProfile } = await supabase
            .from('profiles')
            .select('username')
            .eq('id', currentSession.user.id)
            .single();

        // Only allow access if the requested username matches the current user's username
        if (!myProfile || myProfile.username !== username) {
            // Redirect to public CV view instead
            throw redirect(302, `/cv/@${username}`);
        }

        // Load data for the specified username (which we've verified belongs to the current user)
        await cvStore.loadByUsername(username);
    } else {
        // Load data for the current user
        await cvStore.loadCurrentUserData();
    }

    // Return empty props - actual data comes from the store
    return {};
};
