import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { fail, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async () => {
	console.log('Projects page server load function called');
	const {
		data: { session }
	} = await supabase.auth.getSession();

	if (!session) {
		console.log('No session found, returning empty projects array');
		return { projects: [] };
	}

	console.log('Session found, user ID:', session.user.id);

	try {
		// Log raw query for debugging
		console.log(`Querying projects with profile_id = ${session.user.id}`);

		// First, check ALL projects (without user filtering) for debugging
		const { data: allProjects, error: allError } = await supabase
			.from('projects')
			.select('*')
			.limit(10);

		console.log(`DEBUG: Found ${allProjects?.length || 0} total projects in database`);
		if (allProjects?.length > 0) {
			console.log('Sample project:', allProjects[0]);
		}

		// Now try the actual user-specific query
		const { data, error } = await supabase
			.from('projects')
			.select('*')
			.eq('profile_id', session.user.id)
			.order('start_date', { ascending: false });

		if (error) {
			console.error('Error fetching projects:', error);
			return { projects: [], error: error.message };
		}

		console.log(`Successfully fetched ${data?.length || 0} projects for user ${session.user.id}`);
		if (data && data.length > 0) {
			// Check field structure
			console.log('First project fields:', Object.keys(data[0]).join(', '));
			console.log('First project title:', data[0].title);
			console.log('First project name:', data[0].name);
		} else {
			console.log('No projects found for this user ID');
		}

		return { projects: data || [] };
	} catch (err) {
		console.error('Unexpected error in projects load function:', err);
		return { projects: [], error: 'Failed to load projects' };
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
			const title = formData.get('title') as string;
			const description = formData.get('description') as string;
			const startDate = formData.get('startDate') as string;
			const endDate = formData.get('endDate') as string;
			const url = formData.get('url') as string;

			// Basic validation
			if (!title) {
				return fail(400, { error: 'Project title is required' });
			}

			const { error } = await supabase.from('projects').insert({
				profile_id: session.user.id,
				title,
				name: title,
				description,
				start_date: startDate || null,
				end_date: endDate || null,
				url: url || null
			});

			if (error) return fail(400, { error: error.message });
			throw redirect(303, '/projects?success=create');
		} catch (err) {
			console.error('Unexpected error creating project:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while saving your project'
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
			const title = formData.get('title') as string;
			const description = formData.get('description') as string;
			const startDate = formData.get('startDate') as string;
			const endDate = formData.get('endDate') as string;
			const url = formData.get('url') as string;

			// Basic validation
			if (!id) {
				return fail(400, { error: 'Missing project ID' });
			}

			if (!title) {
				return fail(400, { error: 'Project title is required' });
			}

			// Verify ownership of the project
			const { data: existingProject, error: existingError } = await supabase
				.from('projects')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying project ownership:', existingError);
				return fail(404, { error: 'Project not found' });
			}

			if (existingProject.profile_id !== session.user.id) {
				return fail(403, { error: 'You are not authorized to edit this project' });
			}

			// Update the project
			const { error } = await supabase
				.from('projects')
				.update({
					title,
					name: title,
					description,
					start_date: startDate || null,
					end_date: endDate || null,
					url: url || null
				})
				.eq('id', id);

			if (error) {
				console.error('Error updating project:', error);
				return fail(400, { error: `Error updating project: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/projects?success=update');
		} catch (err) {
			console.error('Unexpected error updating project:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while updating your project'
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
				return fail(400, { error: 'Missing project ID' });
			}

			// Verify ownership of the project
			const { data: existingProject, error: existingError } = await supabase
				.from('projects')
				.select('profile_id')
				.eq('id', id)
				.single();

			if (existingError) {
				console.error('Error verifying project ownership:', existingError);
				return fail(404, { error: 'Project not found' });
			}

			if (existingProject.profile_id !== session.user.id) {
				return fail(403, { error: 'You are not authorized to delete this project' });
			}

			// Delete the project
			const { error } = await supabase.from('projects').delete().eq('id', id);

			if (error) {
				console.error('Error deleting project:', error);
				return fail(400, { error: `Error deleting project: ${error.message}` });
			}

			// Redirect to reload the page
			throw redirect(303, '/projects?success=delete');
		} catch (err) {
			console.error('Unexpected error deleting project:', err);

			if (err instanceof Response) {
				// This is a redirect - pass it through
				throw err;
			}

			return fail(500, {
				error: 'An unexpected error occurred while deleting your project'
			});
		}
	}
};
