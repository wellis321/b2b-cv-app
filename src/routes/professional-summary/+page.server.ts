import type { Actions, PageServerLoad } from './$types';
import { fail, redirect } from '@sveltejs/kit';
import { createCsrfProtection } from '$lib/security/serverCsrf';

export const load: PageServerLoad = async (event) => {
	try {
		const { locals } = event;

		// Try to get session, but don't redirect if not found - let client handle it
		const {
			data: { session }
		} = await locals.supabase.auth.getSession();

		if (!session) {
			console.log('No session found in professional summary load, returning empty data');
			return {
				professionalSummary: null,
				session: null
			};
		}

		console.log('Professional summary load - session found for user:', session.user.id);

		// Fetch professional summary for the user
		const { data: summary, error: summaryError } = await locals.supabase
			.from('professional_summary')
			.select(
				`
				id,
				description,
				professional_summary_strengths (
					id,
					strength,
					sort_order
				)
			`
			)
			.eq('profile_id', session.user.id)
			.maybeSingle();

		if (summaryError) {
			console.error('Error loading professional summary:', summaryError);
			// If table doesn't exist, return null instead of error
			if (
				summaryError.code === 'PGRST116' ||
				summaryError.message.includes('relation') ||
				summaryError.message.includes('does not exist')
			) {
				console.warn('Professional summary table does not exist yet, returning null');
				return {
					professionalSummary: null,
					session: locals.session
				};
			}
			return {
				professionalSummary: null,
				error: `Failed to load professional summary: ${summaryError.message}`,
				session: locals.session
			};
		}

		// Ensure strengths are sorted for consistent display
		if (summary && summary.professional_summary_strengths) {
			summary.professional_summary_strengths.sort((a, b) => a.sort_order - b.sort_order);
		}

		const professionalSummary = summary
			? {
					id: summary.id,
					description: summary.description,
					strengths: summary.professional_summary_strengths || []
				}
			: null;

		console.log(
			'Professional summary loaded successfully:',
			professionalSummary ? 'has data' : 'no data'
		);

		return {
			professionalSummary,
			session: locals.session
		};
	} catch (error) {
		console.error('Error in professional summary page load:', error);
		// If it's a redirect (from requireAuth), re-throw it
		if (error instanceof Response) {
			throw error;
		}
		return {
			professionalSummary: null,
			error: 'Failed to load professional summary',
			session: event.locals.session
		};
	}
};

export const actions: Actions = {
	save: async (event) => {
		try {
			const { request, locals, cookies } = event;

			// Get the current session (same pattern as working API endpoints)
			const {
				data: { session }
			} = await locals.supabase.auth.getSession();
			if (!session) {
				console.log('No session found in professional summary save');
				return fail(401, { error: 'Not authenticated. Please log in and try again.' });
			}

			console.log('Professional summary save - session found for user:', session.user.id);

			// Add CSRF protection
			const { validateRequest } = createCsrfProtection(cookies);
			const isValidRequest = await validateRequest(request);

			if (!isValidRequest) {
				return fail(403, { error: 'Invalid CSRF token' });
			}

			// Parse form data
			const formData = await request.formData();
			const description = formData.get('description') as string;
			const strengths = formData.getAll('strengths') as string[];

			// Filter out empty strengths
			const validStrengths = strengths.filter((strength) => strength && strength.trim() !== '');

			// Start a transaction-like operation
			// First, get or create the professional summary
			let { data: existingSummary, error: fetchError } = await locals.supabase
				.from('professional_summary')
				.select('id')
				.eq('profile_id', session.user.id)
				.maybeSingle();

			if (fetchError) {
				console.error('Error fetching existing professional summary:', fetchError);
				// Check if it's a table doesn't exist error
				if (
					fetchError.code === 'PGRST116' ||
					fetchError.message.includes('relation') ||
					fetchError.message.includes('does not exist')
				) {
					console.warn('Professional summary table does not exist yet, creating new summary');
					// Continue with creating a new summary
				} else {
					return fail(500, {
						error: `Error saving professional summary: ${fetchError.message}`
					});
				}
			}

			let summaryId: string;

			if (existingSummary) {
				// Update existing summary
				const { data: updatedSummary, error: updateError } = await locals.supabase
					.from('professional_summary')
					.update({
						description: description || null,
						updated_at: new Date().toISOString()
					})
					.eq('id', existingSummary.id)
					.select('id')
					.single();

				if (updateError) {
					console.error('Error updating professional summary:', updateError);
					return fail(500, {
						error: `Error saving professional summary: ${updateError.message}`
					});
				}

				summaryId = updatedSummary.id;
			} else {
				// Create new summary
				const { data: newSummary, error: createError } = await locals.supabase
					.from('professional_summary')
					.insert({
						profile_id: session.user.id,
						description: description || null
					})
					.select('id')
					.single();

				if (createError) {
					console.error('Error creating professional summary:', createError);
					// Check if it's a table doesn't exist error
					if (
						createError.code === 'PGRST116' ||
						createError.message.includes('relation') ||
						createError.message.includes('does not exist')
					) {
						return fail(500, {
							error:
								'Professional summary feature is not available yet. Please run the database migration first.'
						});
					} else {
						return fail(500, {
							error: `Error saving professional summary: ${createError.message}`
						});
					}
				}

				summaryId = newSummary.id;
			}

			// Delete existing strengths
			const { error: deleteError } = await locals.supabase
				.from('professional_summary_strengths')
				.delete()
				.eq('professional_summary_id', summaryId);

			if (deleteError) {
				console.error('Error deleting existing strengths:', deleteError);
				// Check if it's a table doesn't exist error
				if (
					deleteError.code === 'PGRST116' ||
					deleteError.message.includes('relation') ||
					deleteError.message.includes('does not exist')
				) {
					console.warn('Professional summary strengths table does not exist yet, skipping delete');
					// Continue without deleting
				} else {
					return fail(500, {
						error: `Error saving professional summary: ${deleteError.message}`
					});
				}
			}

			// Insert new strengths if any
			if (validStrengths.length > 0) {
				const strengthsToInsert = validStrengths.map((strength, index) => ({
					professional_summary_id: summaryId,
					strength: strength.trim(),
					sort_order: index
				}));

				const { error: insertError } = await locals.supabase
					.from('professional_summary_strengths')
					.insert(strengthsToInsert);

				if (insertError) {
					console.error('Error inserting strengths:', insertError);
					// Check if it's a table doesn't exist error
					if (
						insertError.code === 'PGRST116' ||
						insertError.message.includes('relation') ||
						insertError.message.includes('does not exist')
					) {
						return fail(500, {
							error:
								'Professional summary strengths feature is not available yet. Please run the database migration first.'
						});
					} else {
						return fail(500, {
							error: `Error saving professional summary: ${insertError.message}`
						});
					}
				}
			}

			// Redirect to reload the page with the new data
			throw redirect(303, '/professional-summary?success=true');
		} catch (err) {
			// If it's a redirect (from requireAuth), pass it through
			if (err instanceof Response) {
				throw err;
			}

			console.error('Unexpected error saving professional summary:', err);

			// Log more details about the error
			if (err instanceof Error) {
				console.error('Error details:', {
					message: err.message,
					stack: err.stack,
					name: err.name
				});
			}

			return fail(500, {
				error: `An unexpected error occurred while saving your professional summary: ${err instanceof Error ? err.message : 'Unknown error'}`
			});
		}
	}
};
