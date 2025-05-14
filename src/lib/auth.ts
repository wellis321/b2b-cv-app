import { redirect } from '@sveltejs/kit';
import type { RequestEvent } from '@sveltejs/kit';

/**
 * Helper function to verify authentication on protected routes
 * This performs additional verification beyond just checking if a session exists
 */
export async function requireAuth(event: RequestEvent) {
	const { locals } = event;

	try {
		// Check if we have a session in locals (set by the hooks)
		if (!locals.session) {
			// Try to get a fresh session directly
			const {
				data: { session }
			} = await locals.supabase.auth.getSession();

			if (!session) {
				console.log('No valid session found, redirecting to login');
				throw redirect(303, '/');
			}

			// Store the session in locals for future use
			locals.session = session;
		}

		// Ensure the user has a profile
		try {
			// Check if profile exists
			const { data: profile } = await locals.supabase
				.from('profiles')
				.select('id, email')
				.eq('id', locals.session.user.id)
				.maybeSingle();

			if (!profile) {
				// Create a profile for the user
				const { error: insertError } = await locals.supabase.from('profiles').insert({
					id: locals.session.user.id,
					email: locals.session.user.email,
					updated_at: new Date().toISOString()
				});

				if (insertError) {
					console.error('Error creating profile:', insertError);
				}
			}

			// Return user data
			return {
				userId: locals.session.user.id,
				email: locals.session.user.email
			};
		} catch (err) {
			console.error('Error verifying profile:', err);

			// Continue anyway with basic user data
			return {
				userId: locals.session.user.id,
				email: locals.session.user.email
			};
		}
	} catch (err) {
		// Pass through redirects
		if (err instanceof Response) {
			throw err;
		}

		console.error('Unexpected error in requireAuth:', err);
		throw redirect(303, '/');
	}
}
