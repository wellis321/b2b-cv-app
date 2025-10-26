import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { fail, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async () => {
	console.log('Education page server load function called');
	const {
		data: { session }
	} = await supabase.auth.getSession();

	if (!session) {
		console.log('No session found in education page, returning empty educationList array');
		return { educationList: [] };
	}

	console.log('Session found in education page, user ID:', session.user.id);

	try {
		// Check for all education entries first (for debugging)
		const { data: allEducation, error: allError } = await supabase
			.from('education')
			.select('*')
			.limit(10);

		console.log(`DEBUG: Found ${allEducation?.length || 0} total education entries in database`);
		if (allEducation && allEducation.length > 0) {
			console.log('Sample education entry:', allEducation[0]);
		}

		// Get user-specific education entries
		const { data, error } = await supabase
			.from('education')
			.select('*')
			.eq('profile_id', session.user.id)
			.order('start_date', { ascending: false });

		if (error) {
			console.error('Error fetching education:', error);
			return { educationList: [], error: error.message };
		}

		console.log(
			`Successfully fetched ${data?.length || 0} education entries for user ${session.user.id}`
		);
		if (data && data.length > 0) {
			// Check field structure
			console.log('First education entry fields:', Object.keys(data[0]).join(', '));
			console.log('First education qualification:', data[0].qualification);
		} else {
			console.log('No education entries found for this user ID');
		}

		return { educationList: data || [] };
	} catch (err) {
		console.error('Unexpected error in education load function:', err);
		return { educationList: [], error: 'Failed to load education data' };
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
			const institution = formData.get('institution') as string;
			const qualification = formData.get('qualification') as string;
			const fieldOfStudy = formData.get('fieldOfStudy') as string;
			const startDate = formData.get('startDate') as string;
			const endDate = formData.get('endDate') as string;

			// Basic validation
			if (!institution) {
				return fail(400, { error: 'Institution is required' });
			}

			if (!qualification) {
				return fail(400, { error: 'Qualification is required' });
			}

			if (!startDate) {
				return fail(400, { error: 'Start date is required' });
			}

			const { error } = await supabase.from('education').insert({
				profile_id: session.user.id,
				institution,
				qualification: qualification,
				degree: qualification,
				field_of_study: fieldOfStudy || null,
				start_date: startDate,
				end_date: endDate || null
			});

			if (error) return fail(400, { error: error.message });
			throw redirect(303, '/education?success=create');
		} catch (err) {
			console.error('Unexpected error creating education entry:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while saving your education entry'
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
			const institution = formData.get('institution') as string;
			const qualification = formData.get('qualification') as string;
			const fieldOfStudy = formData.get('fieldOfStudy') as string;
			const startDate = formData.get('startDate') as string;
			const endDate = formData.get('endDate') as string;

			// Basic validation
			if (!id) {
				return fail(400, { error: 'Missing education ID' });
			}

			if (!institution) {
				return fail(400, { error: 'Institution is required' });
			}

			if (!qualification) {
				return fail(400, { error: 'Qualification is required' });
			}

			if (!startDate) {
				return fail(400, { error: 'Start date is required' });
			}

			// Verify ownership of the education entry
			const { data: existingEducation, error: existingError } = await supabase
				.from('education')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying education entry ownership:', existingError);
				return fail(404, { error: 'Education entry not found' });
			}

			if (existingEducation.profile_id !== session.user.id) {
				return fail(403, { error: 'You are not authorized to edit this education entry' });
			}

			// Update the education entry
			const { error } = await supabase
				.from('education')
				.update({
					institution,
					qualification: qualification,
					degree: qualification,
					field_of_study: fieldOfStudy || null,
					start_date: startDate,
					end_date: endDate || null
				})
				.eq('id', id);

			if (error) {
				console.error('Error updating education entry:', error);
				return fail(400, { error: `Error updating education entry: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/education?success=update');
		} catch (err) {
			console.error('Unexpected error updating education entry:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while updating your education entry'
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
				return fail(400, { error: 'Missing education ID' });
			}

			// Verify ownership of the education entry
			const { data: existingEducation, error: existingError } = await supabase
				.from('education')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying education entry ownership:', existingError);
				return fail(404, { error: 'Education entry not found' });
			}

			if (existingEducation.profile_id !== session.user.id) {
				return fail(403, { error: 'You are not authorized to delete this education entry' });
			}

			// Delete the education entry
			const { error } = await supabase.from('education').delete().eq('id', id);

			if (error) {
				console.error('Error deleting education entry:', error);
				return fail(400, { error: `Error deleting education entry: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/education?success=delete');
		} catch (err) {
			console.error('Unexpected error deleting education entry:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while deleting your education entry'
			});
		}
	}
};
