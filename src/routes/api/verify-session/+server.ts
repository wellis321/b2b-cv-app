import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ locals, request }) => {
	try {
		// Get the session from the request
		const {
			data: { session }
		} = await locals.supabase.auth.getSession();

		if (!session) {
			return json(
				{
					valid: false,
					message: 'No active session'
				},
				{ status: 401 }
			);
		}

		// Return success response with user info
		return json({
			valid: true,
			userId: session.user.id,
			email: session.user.email
		});
	} catch (error) {
		console.error('Error in verify-session endpoint:', error);
		return json(
			{
				valid: false,
				message: 'Server error'
			},
			{ status: 500 }
		);
	}
};
