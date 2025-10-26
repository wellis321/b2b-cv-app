import type { PageServerLoad } from './$types';
import { createClient } from '@supabase/supabase-js';
import config from '$lib/config';
import type { Database } from '$lib/database.types';
import { error } from '@sveltejs/kit';

// Validate header colors
// This ensures that if a user tries to set invalid colors, they'll be replaced with defaults
function validateHeaderColors(profile: any): void {
	const isValidHexColor = (color: string): boolean => {
		return /^#[0-9A-F]{6}$/i.test(color);
	};

	// Set default colors if invalid or missing
	if (!profile.cv_header_from_color || !isValidHexColor(profile.cv_header_from_color)) {
		profile.cv_header_from_color = '#4338ca'; // Default indigo-700
	}

	if (!profile.cv_header_to_color || !isValidHexColor(profile.cv_header_to_color)) {
		profile.cv_header_to_color = '#7e22ce'; // Default purple-700
	}
}

// We'll use a minimal server-side load function since we're now loading data
// client-side with the CV data store. This is just to handle basic routing
// and username validation.
export const load: PageServerLoad = async ({ params, setHeaders, fetch }) => {
	// Set cache headers - cache for 5 minutes on the edge
	setHeaders({
		'Cache-Control': 'max-age=0, s-maxage=300',
		// Add explicit header to ensure search engines respect our wishes
		'X-Robots-Tag': 'noindex, nofollow, noarchive'
	});

	const { username } = params;
	console.log(`[SERVER] +page.server.ts load function called for username: ${username}`);

	if (!username) {
		console.error('[SERVER] Username not provided in URL parameters');
		throw error(404, 'Username not provided');
	}

	console.log(`[SERVER] Server-side load - Looking up username: ${username}`);

	// Create a new Supabase client just for this request to verify the username exists
	const supabase = createClient<Database>(config.supabase.url, config.supabase.anonKey, {
		auth: {
			persistSession: false,
			autoRefreshToken: false
		}
	});

	try {
		// Only check if the profile exists - the full data will be loaded client-side
		console.log(`[SERVER] Checking if profile exists for username: ${username}`);
		const { data: profile, error: profileError } = await supabase
			.from('profiles')
			.select('id, username, full_name')
			.eq('username', username)
			.single();

		if (profileError) {
			console.error(`[SERVER] Error finding profile for username ${username}:`, profileError);

			// Check if this is a data not found error
			if (profileError.code === 'PGRST116') {
				throw error(404, `Profile not found for username: ${username}`);
			} else {
				// For database connection errors, return a minimal response to prevent 404
				console.error(`[SERVER] Database error for username ${username}:`, profileError);
				return {
					seo: {
						allowIndexing: false
					},
					profileExists: false,
					errorMessage: 'Database connection error, please try again later',
					username
				};
			}
		}

		if (!profile) {
			console.error(`[SERVER] No profile found for username ${username}`);
			throw error(404, 'Profile not found');
		}

		console.log(`[SERVER] Profile found for ${username}:`, profile);

		// Return minimal data - seo and profile indicator
		return {
			seo: {
				allowIndexing: false // For now, we're not allowing any CV profiles to be indexed
			},
			profileExists: true,
			username,
			profileName: profile.full_name || username
		};
	} catch (e) {
		console.error(`[SERVER] Error in load function for username ${username}:`, e);

		// If it's already a SvelteKit error, rethrow it
		if (e && typeof e === 'object' && 'status' in e && 'message' in e) {
			throw e;
		}

		// For other errors, return a minimal response to prevent 404
		return {
			seo: {
				allowIndexing: false
			},
			profileExists: false,
			errorMessage: 'An error occurred while loading the profile',
			username
		};
	}
};
