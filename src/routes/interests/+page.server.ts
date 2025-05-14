import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { fail, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async () => {
	const {
		data: { session }
	} = await supabase.auth.getSession();
	if (!session) return { interests: [] };
	const { data, error } = await supabase
		.from('interests')
		.select('*')
		.eq('profile_id', session.user.id)
		.order('name', { ascending: true });
	if (error) return { interests: [], error: error.message };
	return { interests: data };
};

export const actions: Actions = {
	default: async ({ request }) => {
		const {
			data: { session }
		} = await supabase.auth.getSession();
		if (!session) return fail(401, { error: 'Not authenticated' });

		const formData = await request.formData();
		const name = formData.get('name') as string;
		const description = formData.get('description') as string;
		const id = formData.get('id') as string | null;
		const action = formData.get('action') as string | null;

		// Validate required fields
		if (!name?.trim()) return fail(400, { error: 'Interest name is required' });

		try {
			// Handle delete action
			if (action === 'delete' && id) {
				const { error } = await supabase
					.from('interests')
					.delete()
					.eq('id', id)
					.eq('profile_id', session.user.id);

				if (error) return fail(400, { error: error.message });
				throw redirect(303, '/interests?success=delete');
			}

			// Handle update action
			if (id) {
				const { error } = await supabase
					.from('interests')
					.update({
						name,
						description: description || null
					})
					.eq('id', id)
					.eq('profile_id', session.user.id);

				if (error) return fail(400, { error: error.message });
				throw redirect(303, '/interests?success=update');
			}

			// Handle create action (default)
			const { error } = await supabase.from('interests').insert({
				profile_id: session.user.id,
				name,
				description: description || null
			});

			if (error) return fail(400, { error: error.message });
			throw redirect(303, '/interests?success=create');
		} catch (err) {
			// Handle unexpected errors but don't catch redirects
			if (err instanceof Error) {
				console.error('Error processing interest action:', err);
				return fail(500, { error: 'An unexpected error occurred' });
			}
			throw err;
		}
	}
};
