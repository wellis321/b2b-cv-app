import { supabaseAdmin } from './supabase';
import { safeLog } from '$lib/config';

/**
 * Validates a username to ensure it's available and meets requirements
 * Server-side implementation that uses supabaseAdmin
 *
 * @param username The username to validate
 * @param currentUserId Optional current user ID to skip validation against user's own username
 */
export async function validateUsernameServer(
	username: string,
	currentUserId?: string
): Promise<{ valid: boolean; error?: string }> {
	// Check if empty
	if (!username.trim()) {
		return { valid: false, error: 'Username is required' };
	}

	// Check length
	if (username.length < 3) {
		return { valid: false, error: 'Username must be at least 3 characters long' };
	}

	if (username.length > 30) {
		return { valid: false, error: 'Username must be less than 30 characters' };
	}

	// Check format (lowercase letters, numbers, hyphens, underscores)
	const validFormat = /^[a-z0-9][a-z0-9\-_]+$/.test(username);
	if (!validFormat) {
		return {
			valid: false,
			error:
				'Username can only contain lowercase letters, numbers, hyphens, and underscores, and must start with a letter or number'
		};
	}

	// Check availability from database
	try {
		// If currentUserId provided, check that this isn't the user's own username
		if (currentUserId) {
			const { data: currentProfile } = await supabaseAdmin
				.from('profiles')
				.select('username')
				.eq('id', currentUserId)
				.single();

			// If it's the user's current username, it's valid
			if (currentProfile && currentProfile.username === username) {
				return { valid: true };
			}
		}

		const { data: existingUser } = await supabaseAdmin
			.from('profiles')
			.select('username')
			.eq('username', username)
			.single();

		if (existingUser) {
			return { valid: false, error: 'This username is already taken' };
		}

		// Username is available
		return { valid: true };
	} catch (err) {
		safeLog('error', 'Error checking username availability:', { error: err });
		return { valid: false, error: 'An error occurred checking username availability' };
	}
}
