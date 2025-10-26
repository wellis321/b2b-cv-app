import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { getCsrfToken } from '$lib/security/csrf';

export const GET: RequestHandler = async ({ cookies }) => {
	try {
		// Get or generate CSRF token
		const csrfToken = getCsrfToken(cookies);

		// Return the token in response body and headers
		return json(
			{
				success: true,
				csrfToken: csrfToken
			},
			{
				headers: {
					'X-CSRF-Token': csrfToken
				}
			}
		);
	} catch (error) {
		console.error('Error getting CSRF token:', error);
		return json(
			{
				success: false,
				error: 'Failed to get CSRF token'
			},
			{ status: 500 }
		);
	}
};
