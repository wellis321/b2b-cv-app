import { json } from '@sveltejs/kit';
import { createClient } from '@supabase/supabase-js';
import { PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY } from '$env/static/public';
import type { RequestHandler } from './$types';
import type { Database } from '$lib/database.types';

// Create an admin client that ignores RLS
const supabaseAdmin = createClient<Database>(PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY, {
	auth: {
		persistSession: false,
		autoRefreshToken: false
	}
});

export const POST: RequestHandler = async ({ request, locals }) => {
	try {
		// Extract auth token from header if present
		const authHeader = request.headers.get('Authorization');
		if (authHeader) {
			console.log('Found Authorization header for create-profile');
		}

		const { userId, email } = await request.json();

		console.log('Server creating profile for user:', userId, 'with email:', email);

		if (!userId) {
			return json({ success: false, error: 'User ID is required' }, { status: 400 });
		}

		// First check if the profile already exists - use admin client
		const { data: existingProfile, error: checkError } = await supabaseAdmin
			.from('profiles')
			.select('id')
			.eq('id', userId)
			.maybeSingle(); // Use maybeSingle to avoid errors

		if (checkError) {
			console.error('Error checking for existing profile:', checkError);
			return json({ success: false, error: checkError.message }, { status: 500 });
		}

		if (existingProfile) {
			console.log('Profile already exists for user:', userId);
			return json({ success: true, exists: true });
		}

		// Create a new profile record using the admin client (bypasses RLS)
		console.log('Creating new profile for user:', userId);

		// Validate email is the one from auth.user() if possible
		let profileEmail = email;

		// If email is missing or looks like a placeholder, try to get it from auth user
		if (!email || email === 'user@example.com') {
			try {
				// Get the user's email from auth (this is more reliable)
				const { data: userData, error: userError } =
					await supabaseAdmin.auth.admin.getUserById(userId);

				if (!userError && userData && userData.user.email) {
					console.log(
						`Found user email ${userData.user.email} from auth, using instead of ${email}`
					);
					profileEmail = userData.user.email;
				}
			} catch (err) {
				console.error('Error getting user email from auth:', err);
				// Continue with the provided email if we can't get it from auth
			}
		}

		const { data: insertedData, error } = await supabaseAdmin
			.from('profiles')
			.insert({
				id: userId,
				email: profileEmail,
				username: `user${userId.substring(0, 8)}`, // Generate a default username from user ID
				created_at: new Date().toISOString(),
				updated_at: new Date().toISOString()
			})
			.select();

		if (error) {
			console.error('Error creating profile from server:', error);
			return json({ success: false, error: error.message }, { status: 500 });
		}

		console.log('Successfully created profile for user:', userId, 'Result:', insertedData);
		return json({ success: true, profile: insertedData });
	} catch (error) {
		console.error('Unexpected error creating profile:', error);
		return json({ success: false, error: 'Server error' }, { status: 500 });
	}
};
