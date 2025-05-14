import { cvStore } from '$lib/stores/cvDataStore';
import { session } from '$lib/stores/authStore';
import { redirect } from '@sveltejs/kit';
import { get } from 'svelte/store';
import type { PageLoad } from './$types';

export const load: PageLoad = async ({ url }) => {
	// Get username from query parameter if available
	const username = url.searchParams.get('username');

	// Check if a specific username is requested
	if (username) {
		// Load data for the specified username
		await cvStore.loadByUsername(username);
	} else {
		// Otherwise, check if user is logged in
		const currentSession = get(session);

		if (!currentSession || !currentSession.user) {
			throw redirect(302, '/login?redirect=/preview-cv');
		}

		// Load data for the current user
		await cvStore.loadCurrentUserData();
	}

	// Return empty props - actual data comes from the store
	return {};
};
