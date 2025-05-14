import { redirect, fail } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { getQualifications } from './qualificationUtils';

export const load: PageServerLoad = async ({ locals }) => {
	const session = locals.session;

	// If not authenticated, return empty data
	if (!session) {
		return {
			qualifications: [],
			session: null
		};
	}

	try {
		// Fetch qualifications with evidence
		const qualifications = await getQualifications(session.user.id);

		return {
			qualifications,
			session
		};
	} catch (error) {
		console.error('Error loading qualifications:', error);
		return {
			qualifications: [],
			error: 'Failed to load qualifications',
			session
		};
	}
};

export const actions: Actions = {
	create: async ({ request, locals }) => {
		const session = locals.session;

		if (!session) {
			return fail(401, {
				error: 'You must be logged in to add qualifications'
			});
		}

		const formData = await request.formData();
		const level = formData.get('level')?.toString() || '';
		const description = formData.get('description')?.toString() || '';

		// Validate required fields
		if (!level) {
			return fail(400, {
				error: 'Level is required',
				values: { level, description }
			});
		}

		try {
			// Insert new qualification
			const { data, error } = await supabase
				.from('professional_qualification_equivalence')
				.insert({
					profile_id: session.user.id,
					level,
					description
				})
				.select();

			if (error) {
				console.error('Error saving qualification:', error);
				return fail(500, {
					error: 'Failed to save qualification',
					values: { level, description }
				});
			}

			// Redirect with success parameter
			throw redirect(303, '/qualification-equivalence?success=true');
		} catch (error) {
			if (error instanceof Response) throw error;

			console.error('Unexpected error saving qualification:', error);
			return fail(500, {
				error: 'An unexpected error occurred',
				values: { level, description }
			});
		}
	},

	update: async ({ request, locals }) => {
		const session = locals.session;

		if (!session) {
			return fail(401, {
				error: 'You must be logged in to update qualifications'
			});
		}

		const formData = await request.formData();
		const id = formData.get('id')?.toString();
		const level = formData.get('level')?.toString() || '';
		const description = formData.get('description')?.toString() || '';

		// Validate required fields
		if (!id || !level) {
			return fail(400, {
				error: 'ID and Level are required',
				values: { level, description }
			});
		}

		try {
			// Verify ownership
			const { data: existingData, error: fetchError } = await supabase
				.from('professional_qualification_equivalence')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (fetchError || !existingData) {
				console.error('Error fetching qualification to update:', fetchError);
				return fail(404, {
					error: 'Qualification not found',
					values: { level, description }
				});
			}

			if (existingData.profile_id !== session.user.id) {
				return fail(403, {
					error: 'You do not have permission to update this qualification'
				});
			}

			// Update qualification
			const { error: updateError } = await supabase
				.from('professional_qualification_equivalence')
				.update({
					level,
					description,
					updated_at: new Date().toISOString()
				})
				.eq('id', id);

			if (updateError) {
				console.error('Error updating qualification:', updateError);
				return fail(500, {
					error: 'Failed to update qualification',
					values: { level, description }
				});
			}

			// Redirect with success parameter
			throw redirect(303, '/qualification-equivalence?success=true');
		} catch (error) {
			if (error instanceof Response) throw error;

			console.error('Unexpected error updating qualification:', error);
			return fail(500, {
				error: 'An unexpected error occurred',
				values: { level, description }
			});
		}
	},

	delete: async ({ request, locals }) => {
		const session = locals.session;

		if (!session) {
			return fail(401, {
				error: 'You must be logged in to delete qualifications'
			});
		}

		const formData = await request.formData();
		const id = formData.get('id')?.toString();

		if (!id) {
			return fail(400, { error: 'Qualification ID is required' });
		}

		try {
			// Verify ownership
			const { data: existingData, error: fetchError } = await supabase
				.from('professional_qualification_equivalence')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (fetchError || !existingData) {
				console.error('Error fetching qualification to delete:', fetchError);
				return fail(404, { error: 'Qualification not found' });
			}

			if (existingData.profile_id !== session.user.id) {
				return fail(403, {
					error: 'You do not have permission to delete this qualification'
				});
			}

			// Delete qualification (supporting evidence will be deleted via cascade)
			const { error: deleteError } = await supabase
				.from('professional_qualification_equivalence')
				.delete()
				.eq('id', id);

			if (deleteError) {
				console.error('Error deleting qualification:', deleteError);
				return fail(500, { error: 'Failed to delete qualification' });
			}

			// Redirect with success parameter
			throw redirect(303, '/qualification-equivalence?success=true');
		} catch (error) {
			if (error instanceof Response) throw error;

			console.error('Unexpected error deleting qualification:', error);
			return fail(500, { error: 'An unexpected error occurred' });
		}
	}
};
