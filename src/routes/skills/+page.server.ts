import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { fail, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async () => {
	console.log('Skills page server load function called');
	const {
		data: { session }
	} = await supabase.auth.getSession();

	if (!session) {
		console.log('No session found in skills page, returning empty skills array');
		return { skills: [] };
	}

	console.log('Session found in skills page, user ID:', session.user.id);

	try {
		// Get all skills for the user
		const { data, error } = await supabase
			.from('skills')
			.select('*')
			.eq('profile_id', session.user.id)
			.order('category', { ascending: true })
			.order('name', { ascending: true });

		if (error) {
			console.error('Error fetching skills:', error);
			return { skills: [], error: error.message };
		}

		console.log(`Successfully fetched ${data?.length || 0} skills for user ${session.user.id}`);
		return { skills: data || [] };
	} catch (err) {
		console.error('Unexpected error in skills load function:', err);
		return { skills: [], error: 'Failed to load skills data' };
	}
};

export const actions: Actions = {
	create: async ({ request }) => {
		try {
			const {
				data: { session }
			} = await supabase.auth.getSession();
			if (!session) return fail(401, { error: 'Not authenticated' });

			const formData = await request.formData();
			const name = formData.get('name') as string;
			const level = formData.get('level') as string;
			const category = formData.get('category') as string;

			// Basic validation
			if (!name.trim()) {
				return fail(400, { error: 'Skill name is required' });
			}

			const { error } = await supabase.from('skills').insert({
				profile_id: session.user.id,
				name,
				level: level || null,
				category: category || null
			});

			if (error) return fail(400, { error: error.message });
			throw redirect(303, '/skills?success=create');
		} catch (err) {
			console.error('Unexpected error creating skill:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while saving your skill'
			});
		}
	},

	update: async ({ request }) => {
		try {
			const {
				data: { session }
			} = await supabase.auth.getSession();
			if (!session) return fail(401, { error: 'Not authenticated' });

			const formData = await request.formData();
			const id = formData.get('id') as string;
			const name = formData.get('name') as string;
			const level = formData.get('level') as string;
			const category = formData.get('category') as string;

			// Basic validation
			if (!id) {
				return fail(400, { error: 'Missing skill ID' });
			}

			if (!name.trim()) {
				return fail(400, { error: 'Skill name is required' });
			}

			// Verify ownership of the skill
			const { data: existingSkill, error: existingError } = await supabase
				.from('skills')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying skill ownership:', existingError);
				return fail(404, { error: 'Skill not found' });
			}

			if (existingSkill.profile_id !== session.user.id) {
				return fail(403, { error: 'You are not authorized to edit this skill' });
			}

			// Update the skill
			const { error } = await supabase
				.from('skills')
				.update({
					name,
					level: level || null,
					category: category || null
				})
				.eq('id', id);

			if (error) {
				console.error('Error updating skill:', error);
				return fail(400, { error: `Error updating skill: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/skills?success=update');
		} catch (err) {
			console.error('Unexpected error updating skill:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while updating your skill'
			});
		}
	},

	delete: async ({ request }) => {
		try {
			const {
				data: { session }
			} = await supabase.auth.getSession();
			if (!session) return fail(401, { error: 'Not authenticated' });

			const formData = await request.formData();
			const id = formData.get('id') as string;

			if (!id) {
				return fail(400, { error: 'Missing skill ID' });
			}

			// Verify ownership of the skill
			const { data: existingSkill, error: existingError } = await supabase
				.from('skills')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying skill ownership:', existingError);
				return fail(404, { error: 'Skill not found' });
			}

			if (existingSkill.profile_id !== session.user.id) {
				return fail(403, { error: 'You are not authorized to delete this skill' });
			}

			// Delete the skill
			const { error } = await supabase.from('skills').delete().eq('id', id);

			if (error) {
				console.error('Error deleting skill:', error);
				return fail(400, { error: `Error deleting skill: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/skills?success=delete');
		} catch (err) {
			console.error('Unexpected error deleting skill:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while deleting your skill'
			});
		}
	}
};
