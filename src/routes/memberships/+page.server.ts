import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { fail, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async () => {
	const {
		data: { session }
	} = await supabase.auth.getSession();
	if (!session) return { memberships: [] };
	const { data, error } = await supabase
		.from('professional_memberships')
		.select('*')
		.eq('profile_id', session.user.id)
		.order('start_date', { ascending: false });
	if (error) return { memberships: [], error: error.message };
	return { memberships: data };
};

export const actions: Actions = {
	default: async ({ request }) => {
		const {
			data: { session }
		} = await supabase.auth.getSession();
		if (!session) return fail(401, { error: 'Not authenticated' });

		const formData = await request.formData();
		const organisation = formData.get('organisation') as string;
		const role = formData.get('role') as string;
		const startDate = formData.get('startDate') as string;
		const endDate = formData.get('endDate') as string;
		const id = formData.get('id') as string | null;
		const action = formData.get('action') as string | null;

		// Validate required fields
		if (!organisation?.trim()) return fail(400, { error: 'Organisation is required' });
		if (!startDate) return fail(400, { error: 'Start date is required' });

		try {
			// Handle delete action
			if (action === 'delete' && id) {
				const { error } = await supabase
					.from('professional_memberships')
					.delete()
					.eq('id', id)
					.eq('profile_id', session.user.id);

				if (error) return fail(400, { error: error.message });
				throw redirect(303, '/memberships?success=delete');
			}

			// Handle update action
			if (id) {
				const { error } = await supabase
					.from('professional_memberships')
					.update({
						organisation,
						role: role || null,
						start_date: startDate,
						end_date: endDate || null
					})
					.eq('id', id)
					.eq('profile_id', session.user.id);

				if (error) return fail(400, { error: error.message });
				throw redirect(303, '/memberships?success=update');
			}

			// Handle create action (default)
			const { error } = await supabase.from('professional_memberships').insert({
				profile_id: session.user.id,
				organisation,
				role: role || null,
				start_date: startDate,
				end_date: endDate || null
			});

			if (error) return fail(400, { error: error.message });
			throw redirect(303, '/memberships?success=create');
		} catch (err) {
			// Handle unexpected errors but don't catch redirects
			if (err instanceof Error) {
				console.error('Error processing membership action:', err);
				return fail(500, { error: 'An unexpected error occurred' });
			}
			throw err;
		}
	}
};
