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
	import FormSection from '$lib/components/FormSection.svelte';
	import FormGrid from '$lib/components/FormGrid.svelte';
	import FormField from '$lib/components/FormField.svelte';
	import RichTextEditor from '$lib/components/RichTextEditor.svelte';
	import { formatDescription, formatDescriptionWithFormatting } from '$lib/utils/textFormatting';

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
		sort_order?: number;
		hide_date?: boolean;
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
	let hideDate = $state(false);
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
	let isReordering = $state(false);
	let draggedExperience = $state<WorkExperience | null>(null);
	let dateFormatPreference = $state<'month-year' | 'year-only'>('month-year');

	// Load date format preference from user profile
	async function loadDateFormatPreference() {
		try {
			const userId = $session?.user.id || data.session?.user.id;
			if (userId) {
				const { data: profileData } = await supabase
					.from('profiles')
					.select('date_format_preference')
					.eq('id', userId)
					.single();

				if (profileData?.date_format_preference) {
					dateFormatPreference = profileData.date_format_preference;
				}
			}
		} catch (err) {
			console.warn('Could not load date format preference:', err);
		}
	}

	// Save date format preference to user profile
	async function saveDateFormatPreference() {
		try {
			const userId = $session?.user.id || data.session?.user.id;
			if (userId) {
				await supabase
					.from('profiles')
					.update({ date_format_preference: dateFormatPreference })
					.eq('id', userId);
			}
		} catch (err) {
			console.warn('Could not save date format preference:', err);
		}
	}

	// Watch for changes in date format preference and save to profile
	$effect(() => {
		if (dateFormatPreference && initialCheckDone) {
			saveDateFormatPreference();
		}
	});

	// Function to format dates with preference
	function formatDate(dateString: string | null, hideDate: boolean = false): string {
		if (hideDate) return '';
		if (!dateString) return 'Present';
		try {
			const plainDate = Temporal.PlainDate.from(dateString);

			if (dateFormatPreference === 'year-only') {
				return plainDate.year.toString();
			} else {
				// Format as MM/YYYY
				return `${plainDate.month.toString().padStart(2, '0')}/${plainDate.year}`;
			}
		} catch (e) {
			// Fallback to basic formatting if Temporal fails
			const date = new Date(dateString);

			if (dateFormatPreference === 'year-only') {
				return date.getFullYear().toString();
			} else {
				return `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
			}
		}
	}

	// Sort experiences by sort_order first, then by date (newest first)
	function sortExperiences(experiences: WorkExperience[]): WorkExperience[] {
		return [...experiences].sort((a, b) => {
			// First sort by sort_order if available
			if (a.sort_order !== undefined && b.sort_order !== undefined) {
				return a.sort_order - b.sort_order;
			}

			// Fall back to date-based sorting (newest first)
			const dateA = a.end_date || a.start_date;
			const dateB = b.end_date || b.start_date;
			return new Date(dateB).getTime() - new Date(dateA).getTime();
		});
	}

	// Function to handle drag start
	function handleDragStart(experience: WorkExperience) {
		draggedExperience = experience;
	}

	// Function to handle drag over
	function handleDragOver(e: DragEvent, targetExperience: WorkExperience) {
		e.preventDefault();
		if (draggedExperience && draggedExperience.id !== targetExperience.id) {
			e.dataTransfer!.dropEffect = 'move';
		}
	}

	// Function to handle drop and reorder
	async function handleDrop(e: DragEvent, targetExperience: WorkExperience) {
		e.preventDefault();
		if (!draggedExperience || draggedExperience.id === targetExperience.id) return;

		try {
			// Find the indices of the dragged and target experiences
			const draggedIndex = workExperiences.findIndex((exp) => exp.id === draggedExperience!.id);
			const targetIndex = workExperiences.findIndex((exp) => exp.id === targetExperience.id);

			if (draggedIndex === -1 || targetIndex === -1) return;

			// Create a new array with the reordered experiences
			const newOrder = [...workExperiences];
			const [draggedItem] = newOrder.splice(draggedIndex, 1);
			newOrder.splice(targetIndex, 0, draggedItem);

			// Update sort_order values
			const updatedExperiences = newOrder.map((exp, index) => ({
				...exp,
				sort_order: index
			}));

			// Update local state immediately for responsive UI
			workExperiences = updatedExperiences;

			// Debug logging
			console.log('Reordered experiences:', updatedExperiences);
			console.log(
				'New sort_order values:',
				updatedExperiences.map((exp) => ({ id: exp.id, sort_order: exp.sort_order }))
			);

			// Update the database
			const userId = $session?.user.id || data.session?.user.id;
			if (userId) {
				// Update all experiences with new sort_order values
				const updates = updatedExperiences.map((exp) => ({
					id: exp.id,
					sort_order: exp.sort_order
				}));

				// Update each experience individually to avoid field validation issues
				let hasError = false;
				console.log('Updating database with sort_order values:', updates);

				for (const update of updates) {
					console.log(`Updating experience ${update.id} with sort_order ${update.sort_order}`);
					const { error: updateError } = await supabase
						.from('work_experience')
						.update({ sort_order: update.sort_order })
						.eq('id', update.id);

					if (updateError) {
						console.error('Error updating sort order:', updateError);
						hasError = true;
						break;
					} else {
						console.log(`Successfully updated experience ${update.id}`);
					}
				}

				if (hasError) {
					// Revert to original order on error
					workExperiences = sortExperiences(data.workExperiences || []);
					error = 'Failed to save new order. Please try again.';
				} else {
					success = 'Work experience order updated successfully!';
					setTimeout(() => {
						success = null;
					}, 3000);

					// Refresh the page data to ensure changes are reflected
					try {
						// Reload work experiences from the database
						const userId = $session?.user.id || data.session?.user.id;
						if (userId) {
							const { data: experienceData, error: experienceError } = await supabase
								.from('work_experience')
								.select('*')
								.eq('profile_id', userId)
								.order('sort_order', { ascending: true })
								.order('start_date', { ascending: false });

							if (!experienceError && experienceData) {
								workExperiences = experienceData;
							}

							// Also refresh the CV store data to update preview-cv page
							try {
								await import('$lib/stores/cvDataStore').then(async (module) => {
									const { cvStore } = module;
									// Force refresh the CV store data
									if ($session?.user?.id) {
										// Get the current username from the profile
										const { data: profileData } = await supabase
											.from('profiles')
											.select('username')
											.eq('id', $session.user.id)
											.single();

										if (profileData?.username) {
											await cvStore.loadByUsername(profileData.username);
										}
									}
								});
							} catch (err) {
								console.warn('Could not refresh CV store data:', err);
							}
						}
					} catch (err) {
						console.warn('Could not refresh work experience data:', err);
					}
				}
			}
		} catch (err) {
			console.error('Error reordering experiences:', err);
			error = 'Failed to reorder experiences. Please try again.';
		} finally {
			draggedExperience = null;
		}
	}

	// Function to toggle reorder mode
	function toggleReorderMode() {
		isReordering = !isReordering;
		if (!isReordering) {
			draggedExperience = null;
		}
	}

	// Function to reset order to date-based sorting
	async function resetToDateOrder() {
		try {
			const userId = $session?.user.id || data.session?.user.id;
			if (!userId) return;

			// Sort experiences by date (newest first)
			const dateSortedExperiences = sortExperiences(workExperiences);

			// Update sort_order values based on date order
			const updates = dateSortedExperiences.map((exp, index) => ({
				id: exp.id,
				sort_order: index
			}));

			// Update each experience individually
			for (const update of updates) {
				const { error: updateError } = await supabase
					.from('work_experience')
					.update({ sort_order: update.sort_order })
					.eq('id', update.id);

				if (updateError) {
					throw updateError;
				}
			}

			// Update local state
			workExperiences = dateSortedExperiences.map((exp, index) => ({
				...exp,
				sort_order: index
			}));

			success = 'Order reset to date-based sorting!';
			setTimeout(() => {
				success = null;
			}, 3000);
		} catch (err) {
			console.error('Error resetting order:', err);
			error = 'Failed to reset order. Please try again.';
		}
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
							.order('sort_order', { ascending: true })
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

		// Load date format preference
		await loadDateFormatPreference();
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
		hideDate = exp.hide_date || false;
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
		hideDate = false;
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
						description: description, // Only store the description, not the responsibilities
						hide_date: hideDate
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
						description: description, // Only store the description, not the responsibilities
						hide_date: hideDate
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
			<div class="flex items-center gap-4">
				<!-- Date Format Preference -->
				<div class="flex items-center gap-2">
					<label class="text-sm font-medium text-gray-700">Date Format:</label>
					<select
						bind:value={dateFormatPreference}
						class="rounded-md border border-gray-300 px-3 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500"
					>
						<option value="month-year">Month/Year (01/2024)</option>
						<option value="year-only">Year Only (2024)</option>
					</select>
				</div>

				<div class="flex gap-2">
					{#if workExperiences.length > 1}
						<button
							onclick={toggleReorderMode}
							class="rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:outline-none"
						>
							{isReordering ? 'Done Reordering' : 'Reorder'}
						</button>
					{/if}
					<button
						onclick={toggleAddForm}
						class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
					>
						{showAddForm ? 'Cancel' : 'Add Experience'}
					</button>
				</div>
			</div>
		</div>

		{#if error}
			<div class="mb-4 rounded-md bg-red-100 p-4 text-red-700">{error}</div>
		{/if}

		{#if success}
			<div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">{success}</div>
		{/if}

		{#if warning}
			<div class="mb-4 rounded-md bg-yellow-100 p-4 text-yellow-700">{warning}</div>
		{/if}

		<!-- Add/Edit form moved to the top and toggleable -->
		{#if showAddForm && ($session || data.session)}
			<div id="experienceForm" class="mx-auto mb-8 w-full max-w-4xl">
				<FormSection title={isEditing ? 'Edit Experience' : 'Add New Experience'}>
					<form
						onsubmit={handleSubmit}
						method="POST"
						action={isEditing ? '?/update' : '?/create'}
						class="space-y-4"
					>
						{#if form?.error}
							<div class="mb-4 rounded-md bg-red-100 p-4 text-red-700">{form.error}</div>
						{/if}

						{#if isEditing && editingExperience}
							<input type="hidden" name="id" value={editingExperience.id} />
						{/if}

						<FormGrid>
							<FormField
								label="Company Name"
								id="companyName"
								bind:value={companyName}
								placeholder="e.g. Acme Corporation"
								required={true}
								errorMessage={!companyName && error ? 'Company name is required' : null}
							/>

							<FormField
								label="Position"
								id="position"
								bind:value={position}
								placeholder="e.g. Software Developer"
								required={true}
								errorMessage={!position && error ? 'Position is required' : null}
							/>

							<FormField
								label="Start Date"
								id="startDate"
								type="date"
								bind:value={startDate}
								required={true}
								errorMessage={!startDate && error ? 'Start date is required' : null}
							/>

							<FormField
								label="End Date"
								id="endDate"
								type="date"
								bind:value={endDate}
								placeholder="Leave blank for current position"
							/>
						</FormGrid>

						<!-- Description section with more space -->
						<div class="mt-6">
							<RichTextEditor
								bind:value={description}
								placeholder="Briefly describe your role and responsibilities... Use the toolbar above to make text bold, italic, or add bullet points."
								rows={8}
								label="Description"
							/>
						</div>

						<!-- Hide Date Option -->
						<div class="mt-4 flex items-center">
							<input
								id="hideDate"
								type="checkbox"
								bind:checked={hideDate}
								class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
							/>
							<label for="hideDate" class="ml-2 text-sm text-gray-700">
								Hide date for this work experience
							</label>
						</div>

						<div class="flex justify-end gap-2">
							{#if isEditing}
								<button
									type="button"
									onclick={() => {
										showAddForm = false;
										isEditing = false;
										editingExperience = null;
										resetForm();
									}}
									class="rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:outline-none"
								>
									Cancel
								</button>
							{/if}
							<button
								type="submit"
								disabled={loading}
								class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none disabled:opacity-50"
							>
								{loading ? 'Saving...' : isEditing ? 'Update Experience' : 'Add Experience'}
							</button>
						</div>
					</form>
				</FormSection>

				<!-- Responsibilities Editor for editing experiences -->
				{#if isEditing && editingExperience}
					<div class="mt-6">
						<FormSection title="Key Responsibilities">
							<ErrorBoundary fallback={ResponsibilityErrorFallback}>
								<ResponsibilitiesEditor
									bind:this={editResponsibilitiesEditor}
									workExperienceId={editingExperience.id}
									on:saved={() => {
										success = 'Responsibilities saved successfully!';
										setTimeout(() => {
											success = null;
										}, 3000);
									}}
								/>
							</ErrorBoundary>
						</FormSection>
					</div>
				{/if}
			</div>
		{/if}

		<!-- Work Experience List -->
		{#if loadingExperiences}
			<div class="text-center">
				<p>Loading your work experience...</p>
			</div>
		{:else if workExperiences.length === 0}
			<div class="rounded-md bg-gray-50 p-6 text-center">
				<p class="text-gray-500">You haven't added any work experience yet.</p>
				{#if !showAddForm}
					<button
						onclick={toggleAddForm}
						class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
					>
						Add Your First Experience
					</button>
				{/if}
			</div>
		{:else}
			{#if isReordering}
				<div class="mb-4 rounded-md bg-blue-50 p-4 text-blue-700">
					<div class="flex items-center justify-between">
						<p class="text-sm">
							<strong>Reorder Mode:</strong> Drag and drop work experiences to change their order. The
							order will be saved automatically. Click "Done Reordering" when finished.
						</p>
						<button
							onclick={resetToDateOrder}
							class="rounded-md bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
						>
							Reset to Date Order
						</button>
					</div>
				</div>
			{/if}
			<div class="space-y-6">
				{#each workExperiences as experience}
					<div
						class="rounded-md border border-gray-200 bg-white p-4 shadow-sm {isReordering
							? 'cursor-move'
							: ''} {draggedExperience?.id === experience.id ? 'opacity-50' : ''}"
						draggable={isReordering}
						ondragstart={() => handleDragStart(experience)}
						ondragover={(e) => handleDragOver(e, experience)}
						ondrop={(e) => handleDrop(e, experience)}
					>
						<div class="flex justify-between">
							{#if isReordering}
								<div class="mr-3 flex items-center text-gray-400">
									<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
										<path
											d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
										/>
									</svg>
								</div>
							{/if}
							<div>
								<h3 class="text-lg font-medium text-gray-900">{experience.position}</h3>
								<p class="text-gray-600">{experience.company_name}</p>
								<p class="text-sm text-gray-500">
									{formatDate(experience.start_date, experience.hide_date)} - {formatDate(
										experience.end_date,
										experience.hide_date
									)}
								</p>
								{#if experience.description}
									<div class="mt-2 space-y-1 text-gray-700">
										{#each formatDescriptionWithFormatting(experience.description) as paragraph}
											{@html paragraph}
										{/each}
									</div>
								{/if}
							</div>
							<div class="flex items-center space-x-2">
								<button
									onclick={() => {
										editingExperience = experience;
										companyName = experience.company_name;
										position = experience.position;
										startDate = experience.start_date;
										endDate = experience.end_date || '';
										description = experience.description || '';
										isEditing = true;
										showAddForm = true;
										editingResponsibilities = false;
									}}
									class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
								>
									Edit
								</button>
								<button
									onclick={() => (deleteConfirmId = experience.id)}
									class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-red-300 ring-inset hover:bg-red-50"
								>
									Delete
								</button>
							</div>
						</div>

						<!-- Display Responsibilities -->
						<div class="mt-4">
							<ErrorBoundary fallback={ResponsibilityErrorFallback}>
								<ResponsibilitiesEditor
									bind:this={displayResponsibilitiesEditors[experience.id]}
									workExperienceId={experience.id}
									readOnly={true}
								/>
							</ErrorBoundary>
						</div>
					</div>
				{/each}
			</div>
		{/if}
	</div>
</div>

<!-- Delete Confirmation Dialog -->
{#if deleteConfirmId}
	<div class="fixed inset-0 z-50 overflow-y-auto">
		<div
			class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0"
		>
			<div class="fixed inset-0 transition-opacity" aria-hidden="true">
				<div class="absolute inset-0 bg-gray-500 opacity-75"></div>
			</div>
			<span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true"
				>&#8203;</span
			>
			<div
				class="relative z-50 inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
			>
				<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
					<div class="sm:flex sm:items-start">
						<div
							class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
						>
							<svg
								class="h-6 w-6 text-red-600"
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
								aria-hidden="true"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
								/>
							</svg>
						</div>
						<div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
							<h3 class="text-lg leading-6 font-medium text-gray-900">Delete Experience</h3>
							<div class="mt-2">
								<p class="text-sm text-gray-500">
									Are you sure you want to delete this work experience? This action cannot be
									undone.
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
					<button
						onclick={() => {
							if (deleteConfirmId) {
								deleteExperience(deleteConfirmId);
							}
							deleteConfirmId = null;
						}}
						type="button"
						class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
					>
						Delete
					</button>
					<button
						onclick={() => {
							deleteConfirmId = null;
						}}
						type="button"
						class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
					>
						Cancel
					</button>
				</div>
			</div>
		</div>
	</div>
{/if}

<!-- Camera Capture component goes here if needed -->
