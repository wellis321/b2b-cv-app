<script lang="ts">
	// @ts-ignore - Temporal polyfill types issue
	import { Temporal } from '@js-temporal/polyfill';
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { browser } from '$app/environment';
	import { session, authLoading } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import BreadcrumbNavigation from '$lib/components/BreadcrumbNavigation.svelte';
	import ResponsibilitiesEditor from './ResponsibilitiesEditor.svelte';
	import { getResponsibilitiesForExperience, addCategory, addItem } from './responsibilities';
	import ErrorBoundary from '$lib/components/ErrorBoundary.svelte';
	import ResponsibilityErrorFallback from './ResponsibilityErrorFallback.svelte';

	// Define type for form values
	type FormValues = {
		companyName?: string;
		position?: string;
		startDate?: string;
		endDate?: string;
		description?: string;
	};

	// Define type for form data
	type FormData = {
		error?: string;
		values?: FormValues;
	};

	// Define type for work experience
	type WorkExperience = {
		id: string;
		profile_id: string | null;
		company_name: string;
		position: string;
		start_date: string;
		end_date: string | null;
		description: string | null;
		created_at: string;
	};

	let { data, form } = $props<{
		data: {
			workExperiences: WorkExperience[];
			error?: string;
			session?: any;
		};
		form?: FormData;
	}>();

	let companyName = $state('');
	let position = $state('');
	let startDate = $state('');
	let endDate = $state('');
	let description = $state('');
	let workExperiences = $state<WorkExperience[]>([]);
	let error = $state<string | null>(null);
	let success = $state<string | null>(null);
	let loading = $state(false);
	let loadingExperiences = $state(true);
	let initialCheckDone = $state(false);
	let showAddForm = $state(false);
	let editingExperience = $state<WorkExperience | null>(null);
	let isEditing = $state(false);
	let deleteConfirmId = $state<string | null>(null);
	let editResponsibilitiesEditor = $state<any>(null);
	let displayResponsibilitiesEditors = $state<Record<string, any>>({});
	let editingResponsibilities = $state(false);
	let warning = $state<string | null>(null);
	let loadingResponsibilities = $state(false);

	// Function to format dates with Temporal
	function formatDate(dateString: string | null): string {
		if (!dateString) return 'Present';
		try {
			const plainDate = Temporal.PlainDate.from(dateString);
			// Format as DD/MM/YYYY instead of the default ISO format
			return `${plainDate.day.toString().padStart(2, '0')}/${plainDate.month.toString().padStart(2, '0')}/${plainDate.year}`;
		} catch (e) {
			// Fallback to basic formatting if Temporal fails
			const date = new Date(dateString);
			return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
		}
	}

	// Sort experiences by date (newest first)
	function sortExperiences(experiences: WorkExperience[]): WorkExperience[] {
		return [...experiences].sort((a, b) => {
			// Get the dates to compare (end date if available, otherwise start date)
			const dateA = a.end_date || a.start_date;
			const dateB = b.end_date || b.start_date;
			// Sort in descending order (newest first)
			return new Date(dateB).getTime() - new Date(dateA).getTime();
		});
	}

	// Check for date overlaps
	function hasDateOverlap(startDate: string, endDate: string | null, excludeId?: string): boolean {
		const start = new Date(startDate).getTime();
		const end = endDate ? new Date(endDate).getTime() : Date.now();

		return workExperiences.some((exp) => {
			// Skip the current experience being edited
			if (excludeId && exp.id === excludeId) return false;

			const expStart = new Date(exp.start_date).getTime();
			const expEnd = exp.end_date ? new Date(exp.end_date).getTime() : Date.now();

			// Check for overlap
			// (start1 <= end2) && (end1 >= start2)
			return start <= expEnd && end >= expStart;
		});
	}

	// Restore form values if validation failed
	$effect(() => {
		if (form?.values) {
			companyName = form.values.companyName || '';
			position = form.values.position || '';
			startDate = form.values.startDate || '';
			endDate = form.values.endDate || '';
			description = form.values.description || '';

			// Show the add form when there are form values
			showAddForm = true;
		}
	});

	// Reset messages when form values change
	$effect(() => {
		// Clear error/success/warning when form values change
		if (companyName || position || startDate || endDate || description) {
			error = null;
			warning = null;
		}
	});

	// Check for success parameter in URL
	$effect(() => {
		if ($page.url.searchParams.get('success') === 'true') {
			success = 'Work experience saved successfully!';

			// Clear success message after 3 seconds
			setTimeout(() => {
				success = null;
				// Update URL without the success parameter
				const url = new URL(window.location.href);
				url.searchParams.delete('success');
				window.history.replaceState({}, '', url.toString());
			}, 3000);
		}
	});

	// Check authentication and load data if needed
	onMount(async () => {
		console.log('Work Experience page mounted');

		// Clear any server error after a brief delay
		if (data.error) {
			setTimeout(() => {
				error = null;
			}, 5000);
		} else {
			error = null;
		}

		// Set initial work experiences from server data
		if (data.workExperiences && data.workExperiences.length > 0) {
			workExperiences = data.workExperiences;
			loadingExperiences = false;
		}

		// Check for authentication
		if (!data.session && !$session) {
			console.log('No session found on work experience page mount');
			error = 'Not authenticated. Please login first.';
			loadingExperiences = false;

			// If in browser, redirect to home
			if (browser) {
				setTimeout(() => {
					goto('/');
				}, 2000);
			}
		} else if ($session || data.session) {
			try {
				// We have a session, try to load work experiences if needed
				if (!data.workExperiences || data.workExperiences.length === 0) {
					console.log('Trying to load work experiences from client');
					const userId = $session?.user.id || data.session?.user.id;

					if (userId) {
						const { data: experienceData, error: experienceError } = await supabase
							.from('work_experience')
							.select('*')
							.eq('profile_id', userId)
							.order('start_date', { ascending: false });

						if (experienceError) {
							console.error('Error loading work experiences from client:', experienceError);
							error = 'Error loading work experiences. Please try again.';
						} else if (experienceData && experienceData.length > 0) {
							workExperiences = sortExperiences(experienceData);

							// Update section status after loading experiences
							await import('$lib/cv-sections').then((module) => {
								module.updateSectionStatus();
							});
						}
					}
				}
			} catch (err) {
				console.error('Error in client-side work experience load:', err);
				error = 'Error loading work experiences. Please refresh the page.';
			} finally {
				loadingExperiences = false;
			}
		} else {
			loadingExperiences = false;
		}

		initialCheckDone = true;
	});

	// Subscribe to auth state changes
	$effect(() => {
		if (!initialCheckDone) return;

		// If session changes after initial check, update UI accordingly
		if (!$session && !$authLoading) {
			console.log('Session lost during work experience page lifecycle');
			error = 'Session lost. Please login again.';
			if (browser) {
				setTimeout(() => {
					goto('/');
				}, 2000);
			}
		} else if ($session) {
			// Clear error if it was auth-related
			if (
				error === 'Not authenticated. Please login first.' ||
				error === 'Session lost. Please login again.'
			) {
				error = null;
			}
		}
	});

	// Function to start editing a work experience
	function editExperience(exp: WorkExperience) {
		isEditing = true;
		editingExperience = exp;
		editingResponsibilities = false;

		// Extract description and responsibilities
		let descriptionText = exp.description || '';

		// Check if the description contains the "Key Responsibilities:" section
		const keyResponsibilitiesIndex = descriptionText.indexOf('Key Responsibilities:');

		if (keyResponsibilitiesIndex !== -1) {
			// Split the description at the key responsibilities section
			description = descriptionText.substring(0, keyResponsibilitiesIndex).trim();

			// Get the responsibilities text for potential migration
			const responsibilitiesText = descriptionText.substring(keyResponsibilitiesIndex).trim();

			// Schedule migration of old-format responsibilities to new format
			// This will run after the responsibilities component is mounted
			setTimeout(() => {
				if (editResponsibilitiesEditor) {
					migrateResponsibilitiesFromText(responsibilitiesText, exp.id);
				}
			}, 1000);
		} else {
			description = descriptionText;
		}

		companyName = exp.company_name;
		position = exp.position;
		startDate = exp.start_date;
		endDate = exp.end_date || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('experienceForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to migrate old-style responsibilities text to structured categories
	async function migrateResponsibilitiesFromText(text: string, experienceId: string) {
		if (!text.startsWith('Key Responsibilities:')) return;

		// Get existing categories
		const existingCategories = await getResponsibilitiesForExperience(experienceId);

		// Only migrate if there are no existing categories
		if (existingCategories.length > 0) return;

		// Parse the text to extract numbered categories and items
		const lines = text.split('\n').filter((line) => line.trim());

		// Remove the "Key Responsibilities:" header
		lines.shift();

		let currentCategory: any = null;

		// Process each line
		for (const line of lines) {
			// Check if this is a numbered category line (e.g., "1. Strategic Leadership:")
			const categoryMatch = line.match(/^\d+\.\s+(.*?):/);

			if (categoryMatch) {
				// It's a new category
				const categoryName = categoryMatch[1].trim();
				currentCategory = await addCategory(experienceId, categoryName);
			} else if (currentCategory && line.trim()) {
				// It's an item in the current category, add it
				// Remove any leading bullets or dashes
				const content = line.replace(/^[-•*]\s*/, '').trim();
				if (content) {
					await addItem(currentCategory.id, content);
				}
			}
		}

		// Signal that we're done editing responsibilities
		editingResponsibilities = true;

		// Reload the editor
		if (editResponsibilitiesEditor) {
			setTimeout(() => {
				editResponsibilitiesEditor.loadResponsibilities();
			}, 500);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingExperience = null;
		resetForm();
		showAddForm = false;
	}

	// Function to confirm deletion
	function confirmDelete(id: string) {
		deleteConfirmId = id;
	}

	// Function to cancel deletion
	function cancelDelete() {
		deleteConfirmId = null;
	}

	// Function to delete a work experience
	async function deleteExperience(id: string) {
		if (!$session) {
			error = 'You need to be logged in to delete work experience.';
			return;
		}

		loading = true;
		error = null;
		success = null;

		try {
			const { error: deleteError } = await supabase.from('work_experience').delete().eq('id', id);

			if (deleteError) {
				console.error('Error deleting work experience:', deleteError);
				error = deleteError.message || 'Failed to delete work experience';
			} else {
				success = 'Work experience deleted successfully!';

				// Remove the experience from the list
				workExperiences = workExperiences.filter((exp) => exp.id !== id);

				// Reset the delete confirmation
				deleteConfirmId = null;

				// Update section status
				await import('$lib/cv-sections').then((module) => {
					module.updateSectionStatus();
				});

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = null;
				}, 3000);
			}
		} catch (err) {
			console.error('Unexpected error during deletion:', err);
			error = 'An unexpected error occurred. Please try again.';
		} finally {
			loading = false;
		}
	}

	// Toggle add form visibility
	function toggleAddForm() {
		showAddForm = !showAddForm;
		if (!showAddForm) {
			// Reset form when hiding
			resetForm();
			error = null;
			isEditing = false;
			editingExperience = null;
		}
	}

	// Reset the form after submission
	function resetForm() {
		companyName = '';
		position = '';
		startDate = '';
		endDate = '';
		description = '';
	}

	// Add client-side form handling to validate session before submission
	async function handleSubmit(event: SubmitEvent) {
		// Prevent default form submission
		event.preventDefault();

		// Clear all status messages first
		error = null;
		success = null;
		warning = null;

		// Check authentication first
		if (!$session) {
			error = 'You need to be logged in to save work experience. Please refresh and try again.';
			return;
		}

		// Validate form
		if (!companyName || !position || !startDate) {
			error = 'Please fill out all required fields';
			return;
		}

		// Validate dates
		const start = new Date(startDate);
		const end = endDate ? new Date(endDate) : null;

		if (isNaN(start.getTime())) {
			error = 'Invalid start date format';
			return;
		}

		if (end) {
			if (isNaN(end.getTime())) {
				error = 'Invalid end date format';
				return;
			}

			if (start > end) {
				error = 'Start date cannot be after end date';
				return;
			}
		}

		// Check for date overlaps (excluding the current experience being edited)
		if (hasDateOverlap(startDate, endDate, isEditing ? editingExperience?.id : undefined)) {
			// Show as warning instead of error, but allow the form to submit
			warning =
				'This experience overlaps with another job. This is allowed, but please make sure this is intended.';
			// Continue with form submission - don't return
		}

		loading = true;

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

			if (isEditing && editingExperience) {
				// Update existing experience
				result = await supabase
					.from('work_experience')
					.update({
						company_name: companyName,
						position,
						start_date: startDate,
						end_date: endDate || null,
						description: description // Only store the description, not the responsibilities
					})
					.eq('id', editingExperience.id)
					.select();
			} else {
				// Insert new experience
				result = await supabase
					.from('work_experience')
					.insert({
						profile_id: sessionData.session.user.id,
						company_name: companyName,
						position,
						start_date: startDate,
						end_date: endDate || null,
						description: description // Only store the description, not the responsibilities
					})
					.select();
			}

			const { data: experienceData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting work experience:', submitError);
				error = submitError.message || 'Failed to save work experience';
				success = null;
			} else {
				console.log('Work experience saved successfully:', experienceData);

				// Clear any error first
				error = null;

				// Display success message
				success = isEditing
					? 'Work experience updated successfully!'
					: 'Work experience saved successfully!';

				// Add/update the experience in the list
				if (experienceData && experienceData.length > 0) {
					const savedExperience = experienceData[0];

					if (isEditing) {
						// Update experience in the list
						workExperiences = workExperiences.map((exp) =>
							exp.id === savedExperience.id ? savedExperience : exp
						);

						// If we're actively editing responsibilities, stay in edit mode
						// Otherwise, return to the list view
						if (editingResponsibilities) {
							// Stay in edit mode for responsibilities
							editingExperience = savedExperience;
						} else {
							// Close the form after updating
							resetForm();
							isEditing = false;
							editingExperience = null;
							showAddForm = false;
						}
					} else {
						// Add new experience to the list
						workExperiences = sortExperiences([savedExperience, ...workExperiences]);

						// Switch to editing the newly created experience
						isEditing = true;
						editingExperience = savedExperience;
						editingResponsibilities = true; // Flag that we're actively editing responsibilities

						// Scroll to the form
						if (browser) {
							setTimeout(() => {
								document.getElementById('experienceForm')?.scrollIntoView({ behavior: 'smooth' });
							}, 100);
						}
					}
				}

				// Update section status
				await import('$lib/cv-sections').then((module) => {
					module.updateSectionStatus();
				});

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = null;
				}, 3000);
			}
		} catch (err) {
			console.error('Error in handleSubmit:', err);
			error = 'An unexpected error occurred. Please try again later.';
		} finally {
			loading = false;
		}
	}
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Work Experience</h1>
	<p class="text-gray-700">Add your work history, including past and current positions.</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Work Experience</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Experience'}
			</button>
		</div>

		{#if error}
			<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
		{/if}

		{#if success}
			<div class="mb-4 rounded bg-green-100 p-4 text-green-700">{success}</div>
		{/if}

		{#if warning}
			<div class="mb-4 rounded bg-yellow-100 p-4 text-yellow-700">{warning}</div>
		{/if}

		<!-- Add/Edit form moved to the top and toggleable -->
		{#if showAddForm && ($session || data.session)}
			<div id="experienceForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Experience' : 'Add New Experience'}
				</h3>

				<form
					onsubmit={handleSubmit}
					method="POST"
					action={isEditing ? '?/update' : '?/create'}
					class="space-y-4"
				>
					{#if form?.error}
						<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{form.error}</div>
					{/if}

					{#if isEditing && editingExperience}
						<input type="hidden" name="id" value={editingExperience.id} />
					{/if}

					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="companyName"
							>Company Name</label
						>
						<input
							id="companyName"
							name="companyName"
							type="text"
							bind:value={companyName}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="position"
							>Position</label
						>
						<input
							id="position"
							name="position"
							type="text"
							bind:value={position}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
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
								required
							/>
						</div>
						<div class="flex-1">
							<label class="mb-1 block text-sm font-medium text-gray-700" for="endDate"
								>End Date <span class="text-xs text-gray-500">(Leave blank for current job)</span
								></label
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
						<label class="mb-1 block text-sm font-medium text-gray-700" for="description"
							>Description</label
						>
						<textarea
							id="description"
							name="description"
							bind:value={description}
							rows="4"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						></textarea>
					</div>

					{#if isEditing && editingExperience}
						<div class="mt-4 border-t pt-4">
							<ErrorBoundary
								fallback={ResponsibilityErrorFallback}
								onError={(error) => console.error('Responsibility editor error:', error)}
							>
								<ResponsibilitiesEditor
									workExperienceId={editingExperience.id}
									bind:this={editResponsibilitiesEditor}
									on:editingResponsibilities={(e) => (editingResponsibilities = e.detail.editing)}
								/>
							</ErrorBoundary>
						</div>
					{/if}

					<div class="flex gap-2">
						<button
							type="submit"
							disabled={loading}
							class="flex-1 rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
						>
							{loading ? 'Saving...' : isEditing ? 'Update Experience' : 'Save Experience'}
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

		{#if loadingExperiences}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your work experiences...</p>
			</div>
		{:else if !$session && !data.session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your work experiences.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if workExperiences.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>No work experience added yet. Use the button above to add your work history.</p>
			</div>
		{:else}
			<ul class="space-y-4">
				{#each workExperiences as exp}
					<li class="rounded border bg-white p-4 shadow">
						{#if deleteConfirmId === exp.id}
							<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
								<p class="font-medium">Are you sure you want to delete this experience?</p>
								<div class="mt-2 flex gap-2">
									<form method="POST" action="?/delete" class="inline">
										<input type="hidden" name="id" value={exp.id} />
										<button
											type="submit"
											class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
											disabled={loading}
											onclick={() => deleteExperience(exp.id)}
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
								<div class="font-semibold">{exp.position} at {exp.company_name}</div>
								<div class="text-sm text-gray-500">
									{formatDate(exp.start_date)} - {formatDate(exp.end_date)}
								</div>
							</div>
							<div class="flex gap-2">
								<button
									onclick={() => editExperience(exp)}
									class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
									title="Edit"
								>
									Edit
								</button>
								<button
									onclick={() => confirmDelete(exp.id)}
									class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200"
									title="Delete"
								>
									Delete
								</button>
							</div>
						</div>

						<!-- Description section (if exists) -->
						{#if exp.description && exp.description.trim()}
							<div class="mt-2">
								<h4 class="mb-1 text-sm font-medium text-gray-700">Description</h4>
								<div class="whitespace-pre-line text-gray-700">
									{#if exp.description.includes('Key Responsibilities:')}
										{exp.description.split('Key Responsibilities:')[0].trim()}
									{:else}
										{exp.description}
									{/if}
								</div>
							</div>
						{/if}

						<!-- Show responsibilities in read-only view -->
						<div class="mt-3">
							<ErrorBoundary
								fallback={ResponsibilityErrorFallback}
								resetErrorBoundary={() => {
									if (displayResponsibilitiesEditors[exp.id]) {
										displayResponsibilitiesEditors[exp.id].loadResponsibilities();
									}
								}}
							>
								<ResponsibilitiesEditor
									workExperienceId={exp.id}
									readOnly={true}
									bind:this={displayResponsibilitiesEditors[exp.id]}
								/>
							</ErrorBoundary>
						</div>
					</li>
				{/each}
			</ul>
		{/if}
	</div>
</div>
