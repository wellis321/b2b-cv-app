import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ locals }) => {
	try {
		// Get the session from locals
		const {
			data: { session }
		} = await locals.supabase.auth.getSession();

		if (!session) {
			return json(
				{
					success: false,
					error: 'No valid session found'
				},
				{ status: 401 }
			);
		}

		// Return basic session info
		return json({
			success: true,
			user: {
				id: session.user.id,
				email: session.user.email
			}
		});
	} catch (error) {
		console.error('Error verifying session:', error);
		return json(
			{
				success: false,
				error: 'Session verification failed'
			},
			{ status: 500 }
		);
	}
};
