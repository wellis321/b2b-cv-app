import { json } from '@sveltejs/kit';
import { supabase } from '$lib/supabase';
import type { RequestHandler } from './$types';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		// Get the session from cookies
		const session = cookies.get('sb-access-token');

		if (!session) {
			return json({ error: 'Unauthorized' }, { status: 401 });
		}

		// Verify the session with Supabase
		const {
			data: { user },
			error: authError
		} = await supabase.auth.getUser(session);

		if (authError || !user) {
			return json({ error: 'Invalid session' }, { status: 401 });
		}

		// Get the feedback data from request body
		const { feedback, category, priority } = await request.json();

		if (!feedback || !category || !priority) {
			return json({ error: 'Missing required fields' }, { status: 400 });
		}

		// Store the feedback in the database
		const { error: insertError } = await supabase.from('user_feedback').insert({
			user_id: user.id,
			feedback,
			category,
			priority,
			created_at: new Date().toISOString()
		});

		if (insertError) {
			console.error('Error inserting feedback:', insertError);
			return json({ error: 'Failed to store feedback' }, { status: 500 });
		}

		return json({ success: true, message: 'Feedback submitted successfully' });
	} catch (error) {
		console.error('Error submitting feedback:', error);
		return json({ error: 'Failed to submit feedback' }, { status: 500 });
	}
};
