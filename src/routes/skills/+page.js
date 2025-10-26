import { supabase } from '$lib/supabase';
import { browser } from '$app/environment';
import { updateSectionStatus } from '$lib/cv-sections';

/**
 * Client-side load function for skills
 * This runs in the browser after the server-side load
 */
export const load = async ({ data, depends }) => {
	// Register dependency to allow for invalidation
	depends('app:skills');

	// If not in a browser, just return the server data
	if (!browser) {
		return { ...data };
	}

	// Return server data and update section status
	if (data.skills) {
		// Update section status silently in the background
		updateSectionStatus().catch((err) => {
			console.error('Error updating section status:', err);
		});
	}

	// Return the data from the server
	return { ...data };
};
