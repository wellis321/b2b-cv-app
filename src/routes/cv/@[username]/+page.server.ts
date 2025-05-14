import type { PageServerLoad } from './$types';
import { createClient } from '@supabase/supabase-js';
import config from '$lib/config';
import type { Database } from '$lib/database.types';
import { error } from '@sveltejs/kit';

// We'll use a minimal server-side load function since we're now loading data
// client-side with the CV data store. This is just to handle basic routing
// and username validation.
export const load: PageServerLoad = async ({ params, setHeaders }) => {
	// Set cache headers - cache for 5 minutes on the edge
	setHeaders({
		'Cache-Control': 'max-age=0, s-maxage=300'
	});

	const { username } = params;

	if (!username) {
		console.error('Username not provided in URL parameters');
		throw error(404, 'Username not provided');
	}

	console.log(`Server-side load - Looking up username: ${username}`);

	// Create a new Supabase client just for this request to verify the username exists
	const supabase = createClient<Database>(config.supabase.url, config.supabase.anonKey, {
		auth: {
			persistSession: false,
			autoRefreshToken: false
		}
	});

	try {
		// Just check if the username exists
		const { data: userData, error: userError } = await supabase
			.from('profiles')
			.select('id, full_name')
			.eq('username', username)
			.single();

		if (userError) {
			console.error(`Error finding user by username ${username}:`, userError);
			throw error(404, 'User not found');
		}

		console.log(
			`Found user for username ${username}:`,
			userData.id,
			userData.full_name || '(No name)'
		);

		// Return minimal data - client will load the full data
		return {
			username,
			userId: userData.id,
			foundProfile: true
		};
	} catch (err: any) {
		console.error(`Unexpected error checking username ${username}:`, err);

		if (err.status === 404) {
			throw err; // Re-throw not found errors
		}

		throw error(500, 'An error occurred checking this username');
	}
};
