import type { Actions, PageServerLoad } from './$types';
import { fail, redirect } from '@sveltejs/kit';
import { requireAuth } from '$lib/auth';

export const load: PageServerLoad = async (event) => {
	try {
		// Verify authentication
		const authUser = await requireAuth(event);
		const { locals } = event;

		// Fetch work experiences for the user
		const { data, error } = await locals.supabase
			.from('work_experience')
			.select('*')
			.eq('profile_id', authUser.userId)
			.order('start_date', { ascending: false });

		if (error) {
			console.error('Error loading work experiences:', error);
			return {
				workExperiences: [],
				error: `Failed to load work experiences: ${error.message}`,
				session: locals.session
			};
		}

		// Sort experiences by start date (newest first)
		const sortedData = data
			? [...data].sort((a, b) => {
					// Use end_date if available, otherwise use start_date
					const dateA = a.end_date || a.start_date;
					const dateB = b.end_date || b.start_date;
					return new Date(dateB).getTime() - new Date(dateA).getTime();
				})
			: [];

		return {
			workExperiences: sortedData,
			session: locals.session
		};
	} catch (err) {
		console.error('Unexpected error in work experience load:', err);

		// If it's a redirect, just throw it
		if (err instanceof Response) {
			throw err;
		}

		// Return empty data so client-side can handle it
		return {
			workExperiences: [],
			error: 'Failed to load work experiences. Please try again.',
			session: null
		};
	}
};

export const actions: Actions = {
	create: async (event) => {
		try {
			// Verify authentication
			const authUser = await requireAuth(event);
			const { request, locals } = event;

			// Parse form data
			const formData = await request.formData();
			const companyName = formData.get('companyName') as string;
			const position = formData.get('position') as string;
			const startDate = formData.get('startDate') as string;
			const endDate = formData.get('endDate') as string;
			const description = formData.get('description') as string;

			// Validate required fields
			if (!companyName || !position || !startDate) {
				return fail(400, {
					error: 'Please fill out all required fields',
					values: { companyName, position, startDate, endDate, description }
				});
			}

			// Validate dates
			const start = new Date(startDate);
			if (isNaN(start.getTime())) {
				return fail(400, {
					error: 'Invalid start date format',
					values: { companyName, position, startDate, endDate, description }
				});
			}

			if (endDate) {
				const end = new Date(endDate);
				if (isNaN(end.getTime())) {
					return fail(400, {
						error: 'Invalid end date format',
						values: { companyName, position, startDate, endDate, description }
					});
				}

				if (start > end) {
					return fail(400, {
						error: 'Start date cannot be after end date',
						values: { companyName, position, startDate, endDate, description }
					});
				}
			}

			// Check for date overlaps with existing experiences
			const { data: existingExperiences, error: overlapCheckError } = await locals.supabase
				.from('work_experience')
				.select('start_date, end_date')
				.eq('profile_id', authUser.userId);

			if (overlapCheckError) {
				console.error('Error checking for overlaps:', overlapCheckError);
			} else if (existingExperiences && existingExperiences.length > 0) {
				// Convert dates for comparison
				const newStart = new Date(startDate).getTime();
				const newEnd = endDate ? new Date(endDate).getTime() : Date.now();

				// Check for overlaps
				const hasOverlap = existingExperiences.some((exp) => {
					const expStart = new Date(exp.start_date).getTime();
					const expEnd = exp.end_date ? new Date(exp.end_date).getTime() : Date.now();

					// Check if dates overlap
					return newStart <= expEnd && newEnd >= expStart;
				});

				if (hasOverlap) {
					// Instead of failing, continue but include a warning in the response
					console.log('Overlapping dates detected, but allowing as requested');
				}
			}

			// Insert the work experience
			const { data, error } = await locals.supabase
				.from('work_experience')
				.insert({
					profile_id: authUser.userId,
					company_name: companyName,
					position,
					start_date: startDate,
					end_date: endDate || null,
					description
				})
				.select();

			if (error) {
				console.error('Error saving work experience:', error);
				return fail(400, {
					error: `Error saving work experience: ${error.message}`,
					values: { companyName, position, startDate, endDate, description }
				});
			}

			// Redirect to reload the page with the new data and clear form
			throw redirect(303, '/work-experience?success=true');
		} catch (err) {
			console.error('Unexpected error saving work experience:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while saving your work experience'
			});
		}
	},

	update: async (event) => {
		try {
			// Verify authentication
			const authUser = await requireAuth(event);
			const { request, locals } = event;

			// Parse form data
			const formData = await request.formData();
			const id = formData.get('id') as string;
			const companyName = formData.get('companyName') as string;
			const position = formData.get('position') as string;
			const startDate = formData.get('startDate') as string;
			const endDate = formData.get('endDate') as string;
			const description = formData.get('description') as string;

			// Validate required fields
			if (!id || !companyName || !position || !startDate) {
				return fail(400, {
					error: 'Please fill out all required fields',
					values: { id, companyName, position, startDate, endDate, description }
				});
			}

			// Validate dates
			const start = new Date(startDate);
			if (isNaN(start.getTime())) {
				return fail(400, {
					error: 'Invalid start date format',
					values: { id, companyName, position, startDate, endDate, description }
				});
			}

			if (endDate) {
				const end = new Date(endDate);
				if (isNaN(end.getTime())) {
					return fail(400, {
						error: 'Invalid end date format',
						values: { id, companyName, position, startDate, endDate, description }
					});
				}

				if (start > end) {
					return fail(400, {
						error: 'Start date cannot be after end date',
						values: { id, companyName, position, startDate, endDate, description }
					});
				}
			}

			// Verify ownership of the experience
			const { data: existingExperience, error: existingError } = await locals.supabase
				.from('work_experience')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying experience ownership:', existingError);
				return fail(404, { error: 'Experience not found' });
			}

			if (existingExperience.profile_id !== authUser.userId) {
				return fail(403, { error: 'You are not authorized to edit this experience' });
			}

			// Check for date overlaps with existing experiences (excluding the one being edited)
			const { data: otherExperiences, error: overlapCheckError } = await locals.supabase
				.from('work_experience')
				.select('id, start_date, end_date')
				.eq('profile_id', authUser.userId)
				.neq('id', id);

			if (overlapCheckError) {
				console.error('Error checking for overlaps:', overlapCheckError);
			} else if (otherExperiences && otherExperiences.length > 0) {
				// Convert dates for comparison
				const updatedStart = new Date(startDate).getTime();
				const updatedEnd = endDate ? new Date(endDate).getTime() : Date.now();

				// Check for overlaps
				const hasOverlap = otherExperiences.some((exp) => {
					const expStart = new Date(exp.start_date).getTime();
					const expEnd = exp.end_date ? new Date(exp.end_date).getTime() : Date.now();

					// Check if dates overlap
					return updatedStart <= expEnd && updatedEnd >= expStart;
				});

				if (hasOverlap) {
					return fail(400, {
						error: 'This experience overlaps with another job. Please adjust the dates.',
						values: { id, companyName, position, startDate, endDate, description }
					});
				}
			}

			// Update the work experience
			const { data, error } = await locals.supabase
				.from('work_experience')
				.update({
					company_name: companyName,
					position,
					start_date: startDate,
					end_date: endDate || null,
					description
				})
				.eq('id', id)
				.select();

			if (error) {
				console.error('Error updating work experience:', error);
				return fail(400, {
					error: `Error updating work experience: ${error.message}`,
					values: { id, companyName, position, startDate, endDate, description }
				});
			}

			// Redirect to reload the page with the updated data
			throw redirect(303, '/work-experience?success=update');
		} catch (err) {
			console.error('Unexpected error updating work experience:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while updating your work experience'
			});
		}
	},

	delete: async (event) => {
		try {
			// Verify authentication
			const authUser = await requireAuth(event);
			const { request, locals } = event;

			// Parse form data
			const formData = await request.formData();
			const id = formData.get('id') as string;

			if (!id) {
				return fail(400, { error: 'Missing experience ID' });
			}

			// Verify ownership of the experience
			const { data: existingExperience, error: existingError } = await locals.supabase
				.from('work_experience')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying experience ownership:', existingError);
				return fail(404, { error: 'Experience not found' });
			}

			if (existingExperience.profile_id !== authUser.userId) {
				return fail(403, { error: 'You are not authorized to delete this experience' });
			}

			// Delete the work experience
			const { error } = await locals.supabase.from('work_experience').delete().eq('id', id);

			if (error) {
				console.error('Error deleting work experience:', error);
				return fail(400, { error: `Error deleting work experience: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/work-experience?success=delete');
		} catch (err) {
			console.error('Unexpected error deleting work experience:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while deleting your work experience'
			});
		}
	},

	// Default action for form compatibility
	default: async (event) => {
		// Forward to create action
		return actions.create(event);
	}
};
