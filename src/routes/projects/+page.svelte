<script lang="ts">
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	// @ts-ignore - The Temporal polyfill doesn't have proper TypeScript definitions
	import { Temporal } from '@js-temporal/polyfill';
	import { session as authSession } from '$lib/stores/authStore';
	import BreadcrumbNavigation from '$lib/components/BreadcrumbNavigation.svelte';

	interface PageData {
		projects: Project[];
		error?: string;
		form?: {
			error: string;
		};
	}

	const { data } = $props<{ data: PageData }>();

	// Interface definition for the Project type
	interface Project {
		id: string;
		profile_id: string | null;
		title?: string | null;
		name?: string | null; // For backward compatibility if the field was renamed
		description?: string | null;
		start_date?: string | null;
		end_date?: string | null;
		url?: string | null;
		created_at?: string;
		updated_at?: string;
	}

	// Form state
	let title = $state('');
	let description = $state('');
	let startDate = $state('');
	let endDate = $state('');
	let url = $state('');

	// UI state
	let projects = $state<Project[]>(data.projects || []);
	let error = $state<string | undefined>(data.error);
	let success = $state<string>('');
	let loading = $state(false);
	let loadingProjects = $state(false);
	let showAddForm = $state(false);
	let isEditing = $state(false);
	let editingProject = $state<Project | null>(null);
	let deleteConfirmId = $state<string | null>(null);

	// Session from store
	const session = $authSession;

	console.log('Projects page - session:', session ? 'Present' : 'None');
	console.log('Projects page - data:', data);
	console.log('Projects page - projects:', projects);

	// Format dates for display
	function formatDate(dateStr: string | null | undefined): string {
		if (!dateStr) return 'Present';

		try {
			const date = Temporal.PlainDate.from(dateStr);
			return date.toLocaleString('en-GB', { month: 'short', year: 'numeric' });
		} catch (err) {
			console.error('Error formatting date:', err);
			return dateStr;
		}
	}

	// Sort projects by date
	function sortProjects(projectList: Project[]): Project[] {
		return [...projectList].sort((a, b) => {
			// If no start dates, maintain current order
			if (!a.start_date && !b.start_date) return 0;
			// Items without dates go to the bottom
			if (!a.start_date) return 1;
			if (!b.start_date) return -1;
			// Sort by date descending (newest first)
			return new Date(b.start_date).getTime() - new Date(a.start_date).getTime();
		});
	}

	// Toggle add form visibility
	function toggleAddForm() {
		showAddForm = !showAddForm;
		if (!showAddForm) {
			resetForm();
			isEditing = false;
			editingProject = null;
		}
	}

	// Reset form fields
	function resetForm() {
		title = '';
		description = '';
		startDate = '';
		endDate = '';
		url = '';
	}

	// Handle form submission
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		if (!session) {
			error = 'You need to be logged in to save a project.';
			return;
		}

		// Basic validation
		if (!title.trim()) {
			error = 'Project title is required.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			// Ensure we have a valid auth token by checking session
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				// Re-authenticate if no session
				error = 'Your session has expired. Please refresh the page and try again.';
				loading = false;
				return;
			}

			let result;

			if (isEditing && editingProject) {
				// Update existing project
				result = await supabase
					.from('projects')
					.update({
						title,
						description,
						start_date: startDate || null,
						end_date: endDate || null,
						url: url || null
					})
					.eq('id', editingProject.id)
					.select();
			} else {
				// Insert new project
				result = await supabase
					.from('projects')
					.insert({
						profile_id: sessionData.session.user.id,
						title,
						description,
						start_date: startDate || null,
						end_date: endDate || null,
						url: url || null
					})
					.select();
			}

			const { data: projectData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting project:', submitError);
				error = submitError.message || 'Failed to save project';
				success = '';
			} else {
				console.log('Project saved successfully:', projectData);

				// Clear any error first
				error = undefined;
				success = isEditing ? 'Project updated successfully!' : 'Project saved successfully!';

				// Add/update the project in the list
				if (projectData && projectData.length > 0) {
					const savedProject = projectData[0];

					if (isEditing) {
						// Update project in the list
						projects = projects.map((proj) => (proj.id === savedProject.id ? savedProject : proj));
						// Re-sort the entire list to maintain correct order
						projects = sortProjects(projects);
					} else {
						// Add new project and ensure sorting
						projects = sortProjects([savedProject, ...projects]);
					}
				}

				// Reset the form
				resetForm();

				// Reset editing state
				isEditing = false;
				editingProject = null;

				// Hide the form after successful submission
				showAddForm = false;

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = '';
				}, 3000);
			}
		} catch (err) {
			console.error('Error in handleSubmit:', err);
			error = 'An unexpected error occurred. Please try again later.';
		} finally {
			loading = false;
		}
	}

	// Function to edit a project
	function editProject(project: Project): void {
		isEditing = true;
		editingProject = project;
		title = project.title || project.name || '';
		description = project.description || '';
		startDate = project.start_date || '';
		endDate = project.end_date || '';
		url = project.url || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('projectForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingProject = null;
		resetForm();
		showAddForm = false;
	}

	// Function to confirm deletion
	function confirmDelete(id: string): void {
		deleteConfirmId = id;
	}

	// Function to cancel deletion
	function cancelDelete() {
		deleteConfirmId = null;
	}

	// Function to delete a project
	async function deleteProject(id: string): Promise<void> {
		if (!session) {
			error = 'You need to be logged in to delete projects.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			const { error: deleteError } = await supabase.from('projects').delete().eq('id', id);

			if (deleteError) {
				console.error('Error deleting project:', deleteError);
				error = deleteError.message || 'Failed to delete project';
			} else {
				success = 'Project deleted successfully!';

				// Remove the project from the list
				projects = projects.filter((proj) => proj.id !== id);

				// Reset the delete confirmation
				deleteConfirmId = null;

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = '';
				}, 3000);
			}
		} catch (err) {
			console.error('Unexpected error during deletion:', err);
			error = 'An unexpected error occurred. Please try again.';
		} finally {
			loading = false;
		}
	}

	// Client-side fallback to load projects directly
	async function loadProjectsFromClient() {
		loadingProjects = true;
		error = undefined;

		try {
			// Verify session first
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				console.log('No valid session for client-side load');
				loadingProjects = false;
				return;
			}

			console.log('Loading projects for user:', sessionData.session.user.id);

			// Fetch projects
			const { data: projectsData, error: projectsError } = await supabase
				.from('projects')
				.select('*')
				.eq('profile_id', sessionData.session.user.id)
				.order('start_date', { ascending: false });

			if (projectsError) {
				console.error('Error fetching projects from client:', projectsError);
				error = projectsError.message;
				return;
			}

			console.log('Client-side load successful:', projectsData?.length, 'projects');
			projects = projectsData || [];
		} catch (err) {
			console.error('Unexpected error loading projects from client:', err);
			error = 'Failed to load projects. Please try refreshing the page.';
		} finally {
			loadingProjects = false;
		}
	}

	// Check for success message in URL params and data loading
	onMount(async () => {
		if (browser) {
			// Check URL params for success message
			if ($page.url.searchParams.has('success')) {
				const successType = $page.url.searchParams.get('success');
				if (successType === 'create') {
					success = 'Project added successfully!';
				} else if (successType === 'update') {
					success = 'Project updated successfully!';
				} else if (successType === 'delete') {
					success = 'Project deleted successfully!';
				}

				// Clear URL params
				const url = new URL(window.location.href);
				url.searchParams.delete('success');
				history.replaceState({}, document.title, url.toString());

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = '';
				}, 3000);
			}

			// If data was loaded properly on the server, we'll have projects
			// Otherwise, try to load them directly from client
			if (session && (!projects || projects.length === 0)) {
				console.log('No projects loaded from server, trying client-side fetch');
				await loadProjectsFromClient();
			}
		}
	});
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Projects</h1>
	<p class="text-gray-700">
		Add key projects you've worked on to showcase your achievements and skills.
	</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Projects</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Project'}
			</button>
		</div>

		{#if error}
			<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
		{/if}

		{#if success}
			<div class="mb-4 rounded bg-green-100 p-4 text-green-700">{success}</div>
		{/if}

		<!-- Add/Edit form -->
		{#if showAddForm && session}
			<div id="projectForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Project' : 'Add New Project'}
				</h3>

				<form
					onsubmit={handleSubmit}
					method="POST"
					action={isEditing ? '?/update' : '?/create'}
					class="space-y-4"
				>
					{#if data.form?.error}
						<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{data.form.error}</div>
					{/if}

					{#if isEditing && editingProject}
						<input type="hidden" name="id" value={editingProject.id} />
					{/if}

					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="title">Title</label>
						<input
							id="title"
							name="title"
							type="text"
							bind:value={title}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="description"
							>Description</label
						>
						<textarea
							id="description"
							name="description"
							bind:value={description}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						></textarea>
					</div>
					<div class="flex gap-4">
						<div class="flex-1">
							<label class="mb-1 block text-sm font-medium text-gray-700" for="startDate"
								>Start Date</label
							>
							<input
								id="startDate"
								name="startDate"
								type="date"
								bind:value={startDate}
								class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							/>
						</div>
						<div class="flex-1">
							<label class="mb-1 block text-sm font-medium text-gray-700" for="endDate"
								>End Date <span class="text-xs text-gray-500">(Leave blank if ongoing)</span></label
							>
							<input
								id="endDate"
								name="endDate"
								type="date"
								bind:value={endDate}
								class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							/>
						</div>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="url">Project URL</label
						>
						<input
							id="url"
							name="url"
							type="url"
							bind:value={url}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						/>
					</div>
					<div class="flex gap-2">
						<button
							type="submit"
							disabled={loading}
							class="flex-1 rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
						>
							{loading ? 'Saving...' : isEditing ? 'Update Project' : 'Save Project'}
						</button>
						{#if isEditing}
							<button
								type="button"
								onclick={cancelEdit}
								class="rounded bg-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none"
							>
								Cancel
							</button>
						{/if}
					</div>
				</form>
			</div>
		{/if}

		{#if loadingProjects}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your projects...</p>
			</div>
		{:else if !session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your projects.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if projects.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>No projects added yet. Use the button above to add your project history.</p>
			</div>
		{:else}
			<ul class="space-y-4">
				{#each projects as project}
					<li class="rounded border bg-white p-4 shadow">
						{#if deleteConfirmId === project.id}
							<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
								<p class="font-medium">Are you sure you want to delete this project?</p>
								<div class="mt-2 flex gap-2">
									<form method="POST" action="?/delete" class="inline">
										<input type="hidden" name="id" value={project.id} />
										<button
											type="submit"
											class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
											disabled={loading}
											onclick={(e) => {
												e.preventDefault();
												deleteProject(project.id);
											}}
										>
											{loading ? 'Deleting...' : 'Yes, Delete'}
										</button>
									</form>
									<button
										onclick={cancelDelete}
										class="rounded bg-gray-200 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-300"
									>
										Cancel
									</button>
								</div>
							</div>
						{/if}
						<div class="flex items-center justify-between">
							<div>
								<div class="font-semibold">
									{project.title || project.name || 'Unnamed Project'}
								</div>
								<div class="text-sm text-gray-500">
									{project.start_date ? formatDate(project.start_date) : ''}
									{project.start_date ? '-' : ''}
									{project.end_date ? formatDate(project.end_date) : 'Present'}
								</div>
							</div>
							<div class="flex gap-2">
								<button
									onclick={() => editProject(project)}
									class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
									title="Edit"
								>
									Edit
								</button>
								<button
									onclick={() => confirmDelete(project.id)}
									class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200"
									title="Delete"
								>
									Delete
								</button>
							</div>
						</div>
						{#if project.description}
							<div class="mt-2 text-gray-700">{project.description}</div>
						{/if}
						{#if project.url}
							<div class="mt-2 text-blue-600 underline">
								<a href={project.url} target="_blank" rel="noopener noreferrer">{project.url}</a>
							</div>
						{/if}
					</li>
				{/each}
			</ul>
		{/if}
	</div>
</div>
