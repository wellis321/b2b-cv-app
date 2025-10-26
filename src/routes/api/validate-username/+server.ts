import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { createCsrfProtection } from '$lib/security/serverCsrf';
import { getSessionFromEvent } from '$lib/server/session';
import { validateUsernameServer } from '$lib/server/validation';

/**
 * API endpoint to validate a username on the server
 * Checks both format and availability
 */
export const POST: RequestHandler = async ({ request, cookies, locals }) => {
	// Validate CSRF token
	const { validateRequest } = createCsrfProtection(cookies);
	const isValidRequest = await validateRequest(request);

	if (!isValidRequest) {
		return json({ success: false, error: 'Invalid CSRF token' }, { status: 403 });
	}

	try {
		// Get the current session for determining current user (if any)
		const session = getSessionFromEvent({ locals } as any);
		const currentUserId = session?.user?.id;

		// Get the username from the request body
		const data = await request.json();
		const { username } = data;

		if (!username) {
			return json({ success: false, error: 'Username is required' }, { status: 400 });
		}

		// Validate the username using the server-side validation function
		const result = await validateUsernameServer(username, currentUserId);

		if (result.valid) {
			return json({ success: true });
		} else {
			return json({ success: false, error: result.error }, { status: 400 });
		}
	} catch (error) {
		console.error('Error validating username:', error);
		return json(
			{ success: false, error: 'An error occurred while validating the username' },
			{ status: 500 }
		);
	}
};
