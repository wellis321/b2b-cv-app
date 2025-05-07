<script lang="ts">
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	// @ts-ignore - The Temporal polyfill doesn't have proper TypeScript definitions
	import { Temporal } from '@js-temporal/polyfill';
	import { session as authSession } from '$lib/stores/authStore';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';

	interface PageData {
		educationList: Education[];
		error?: string;
		form?: {
			error: string;
		};
	}

	const { data } = $props<{ data: PageData }>();

	interface Education {
		id: string;
		institution: string;
		qualification?: string;
		degree?: string; // Include for backward compatibility
		field_of_study: string | null;
		start_date: string | null;
		end_date: string | null;
		profile_id: string;
	}

	// Form state
	let institution = $state('');
	let qualification = $state('');
	let fieldOfStudy = $state('');
	let startDate = $state('');
	let endDate = $state('');

	// UI state
	let educationList = $state<Education[]>(data.educationList || []);
	let error = $state<string | undefined>(data.error);
	let success = $state<string>('');
	let loading = $state(false);
	let loadingEducation = $state(false);
	let showAddForm = $state(false);
	let isEditing = $state(false);
	let editingEducation = $state<Education | null>(null);
	let deleteConfirmId = $state<string | null>(null);
	let addColumnStatus = $state('');
	let updateDataStatus = $state('');
	let isLoading = $state(false);
	let displayIssueDetected = $state(false);
	let fixingDisplayIssue = $state(false);

	// Session from store
	const session = $authSession;

	// Format dates for display
	function formatDate(dateStr: string | null): string {
		if (!dateStr) return 'Present';

		try {
			const date = Temporal.PlainDate.from(dateStr);
			return date.toLocaleString('en-GB', { month: 'short', year: 'numeric' });
		} catch (err) {
			console.error('Error formatting date:', err);
			return dateStr;
		}
	}

	// Sort education entries by date
	function sortEducation(educationData: Education[]): Education[] {
		return [...educationData].sort((a, b) => {
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
			editingEducation = null;
		}
	}

	// Reset form fields
	function resetForm() {
		institution = '';
		qualification = '';
		fieldOfStudy = '';
		startDate = '';
		endDate = '';
	}

	// Handle form submission
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		if (!session) {
			error = 'You need to be logged in to save education information.';
			return;
		}

		// Basic validation
		if (!institution.trim()) {
			error = 'Institution is required.';
			return;
		}

		if (!qualification.trim()) {
			error = 'Qualification is required.';
			return;
		}

		if (!startDate) {
			error = 'Start date is required.';
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

			if (isEditing && editingEducation) {
				// Update existing education entry
				result = await supabase
					.from('education')
					.update({
						institution,
						degree: qualification,
						field_of_study: fieldOfStudy || null,
						start_date: startDate,
						end_date: endDate || null
					})
					.eq('id', editingEducation.id)
					.select();
			} else {
				// Insert new education entry
				result = await supabase
					.from('education')
					.insert({
						profile_id: sessionData.session.user.id,
						institution,
						degree: qualification,
						field_of_study: fieldOfStudy || null,
						start_date: startDate,
						end_date: endDate || null
					})
					.select();
			}

			const { data: educationData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting education:', submitError);
				error = submitError.message || 'Failed to save education information';
				success = '';
			} else {
				console.log('Education saved successfully:', educationData);

				// Clear any error first
				error = undefined;
				success = isEditing ? 'Education updated successfully!' : 'Education saved successfully!';

				// Add/update the education in the list
				if (educationData && educationData.length > 0) {
					const savedEducation = educationData[0];

					if (isEditing) {
						// Update education in the list
						educationList = educationList.map((edu) =>
							edu.id === savedEducation.id ? savedEducation : edu
						);
					} else {
						// Add new education to the list
						educationList = sortEducation([savedEducation, ...educationList]);
					}
				}

				// Reset the form
				resetForm();

				// Reset editing state
				isEditing = false;
				editingEducation = null;

				// Hide the form after successful submission
				showAddForm = false;

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = '';
				}, 3000);
			}
		} catch (err) {
			console.error('Unexpected error during form submission:', err);
			// Ensure error is properly set and success is cleared
			success = '';
			error = 'An unexpected error occurred. Please try again.';
		} finally {
			loading = false;
		}
	}

	// Function to edit an education entry
	function editEducation(edu: Education): void {
		isEditing = true;
		editingEducation = edu;
		institution = edu.institution;
		// Use qualification if available, otherwise fall back to degree
		qualification = edu.qualification || edu.degree || '';
		fieldOfStudy = edu.field_of_study || '';
		startDate = edu.start_date || '';
		endDate = edu.end_date || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('educationForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingEducation = null;
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

	// Function to delete an education entry
	async function deleteEducation(id: string): Promise<void> {
		if (!session) {
			error = 'You need to be logged in to delete education information.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			const { error: deleteError } = await supabase.from('education').delete().eq('id', id);

			if (deleteError) {
				console.error('Error deleting education:', deleteError);
				error = deleteError.message || 'Failed to delete education';
			} else {
				success = 'Education deleted successfully!';

				// Remove the education from the list
				educationList = educationList.filter((edu) => edu.id !== id);

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

	// Check for display issues
	function checkForDisplayIssues() {
		if (educationList && educationList.length > 0) {
			// Check if any education entries have degree but not qualification or vice versa
			for (const edu of educationList) {
				if (
					(edu.degree && (!edu.qualification || edu.qualification === null)) ||
					(edu.qualification && (!edu.degree || edu.degree === null))
				) {
					displayIssueDetected = true;
					break;
				}
			}
		}
	}

	// Add this new function to directly fix education data issues
	async function fixEducationDataIssues() {
		if (!session) {
			error = 'You need to be logged in to fix your data.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			// Step 1: Fetch all education entries for the user
			const { data: educationData, error: fetchError } = await supabase
				.from('education')
				.select('*')
				.eq('profile_id', session.user.id);

			if (fetchError) {
				throw new Error('Error fetching education data: ' + fetchError.message);
			}

			// Step 2: Update education entries that need fixing
			let updatedCount = 0;
			let errorCount = 0;

			for (const edu of educationData || []) {
				let updateData: Record<string, string> = {};
				let needsUpdate = false;

				// If education has degree but not qualification
				if (edu.degree && (!edu.qualification || edu.qualification === null)) {
					updateData.qualification = edu.degree;
					needsUpdate = true;
				}

				// If education has qualification but not degree
				if (edu.qualification && (!edu.degree || edu.degree === null)) {
					updateData.degree = edu.qualification;
					needsUpdate = true;
				}

				if (needsUpdate) {
					const { error: updateError } = await supabase
						.from('education')
						.update(updateData)
						.eq('id', edu.id);

					if (updateError) {
						errorCount++;
						console.error('Error updating education:', updateError);
					} else {
						updatedCount++;
					}
				}
			}

			// Step 3: Show success message and reload education data
			if (updatedCount > 0) {
				success = `Successfully fixed ${updatedCount} education entries!`;

				// Reload the education entries to reflect the changes
				const { data: refreshedData } = await supabase
					.from('education')
					.select('*')
					.eq('profile_id', session.user.id)
					.order('start_date', { ascending: false });

				if (refreshedData) {
					educationList = refreshedData;
				}

				// Clear display issue flag now that it's fixed
				displayIssueDetected = false;
			} else if (errorCount > 0) {
				error = `Failed to update ${errorCount} education entries. Please try again.`;
			} else {
				success = 'No education updates were needed.';
				displayIssueDetected = false;
			}

			// Clear success message after 3 seconds
			setTimeout(() => {
				success = '';
			}, 3000);
		} catch (err) {
			console.error('Error fixing education data:', err);
			error = `Failed to fix education data: ${err instanceof Error ? err.message : String(err)}`;
		} finally {
			loading = false;
		}
	}

	// Add this function near the fixEducationDataIssues function
	// Check for success message in URL params
	onMount(async () => {
		if (browser) {
			// Check URL params for success message
			if ($page.url.searchParams.has('success')) {
				const successType = $page.url.searchParams.get('success');
				if (successType === 'create') {
					success = 'Education added successfully!';
				} else if (successType === 'update') {
					success = 'Education updated successfully!';
				} else if (successType === 'delete') {
					success = 'Education deleted successfully!';
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

			// Check for display issues
			checkForDisplayIssues();

			// If data was loaded properly on the server, we'll have education entries
			// Otherwise, try to load them directly from client
			if (session && (!educationList || educationList.length === 0)) {
				console.log('No education entries loaded from server, trying client-side fetch');
				await loadEducationFromClient();
			}
		}
	});

	// Client-side fallback to load education entries directly
	async function loadEducationFromClient() {
		loadingEducation = true;
		error = undefined;

		try {
			// Verify session first
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				console.log('No valid session for client-side load');
				loadingEducation = false;
				return;
			}

			console.log('Loading education for user:', sessionData.session.user.id);

			// Fetch education entries
			const { data: educationData, error: educationError } = await supabase
				.from('education')
				.select('*')
				.eq('profile_id', sessionData.session.user.id)
				.order('start_date', { ascending: false });

			if (educationError) {
				console.error('Error fetching education from client:', educationError);
				error = educationError.message;
				return;
			}

			console.log('Client-side load successful:', educationData?.length, 'education entries');
			educationList = educationData || [];

			// Check for display issues after loading
			checkForDisplayIssues();
		} catch (err) {
			console.error('Unexpected error loading education from client:', err);
			error = 'Failed to load education. Please try refreshing the page.';
		} finally {
			loadingEducation = false;
		}
	}
</script>

<div class="mx-auto max-w-xl">
	<div class="mb-4 flex items-center justify-between">
		<h2 class="text-2xl font-bold">Your Education</h2>
		<div class="flex gap-2">
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Education'}
			</button>
			<button
				onclick={fixEducationDataIssues}
				class="flex items-center rounded bg-yellow-200 px-2 py-1 text-xs font-medium text-yellow-800 hover:bg-yellow-300"
				title="Fix Display Issues"
				disabled={loading}
			>
				Fix Display Issues
			</button>
		</div>
	</div>

	{#if error}
		<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
	{/if}

	{#if success}
		<div class="mb-4 rounded bg-green-100 p-4 text-green-700">{success}</div>
	{/if}

	{#if displayIssueDetected}
		<div class="mb-4 rounded bg-yellow-100 p-4 text-yellow-800">
			<p class="mb-2 font-medium">Display issue detected!</p>
			<p class="mb-2 text-sm">
				Some of your education entries may not be displaying correctly due to a recent update.
			</p>
			<button
				onclick={fixEducationDataIssues}
				class="inline-block rounded bg-yellow-500 px-3 py-1 text-sm font-medium text-white hover:bg-yellow-600"
				disabled={loading}
			>
				{loading ? 'Fixing...' : 'Fix Now'}
			</button>
		</div>
	{/if}

	<!-- Add/Edit form -->
	{#if showAddForm && session}
		<div id="educationForm" class="mb-8 rounded bg-white p-6 shadow">
			<h3 class="mb-4 text-xl font-semibold">
				{isEditing ? 'Edit Education' : 'Add New Education'}
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

				{#if isEditing && editingEducation}
					<input type="hidden" name="id" value={editingEducation.id} />
				{/if}

				<div>
					<label class="mb-1 block text-sm font-medium text-gray-700" for="institution"
						>Institution</label
					>
					<input
						id="institution"
						name="institution"
						type="text"
						bind:value={institution}
						class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						required
					/>
				</div>
				<div>
					<label class="mb-1 block text-sm font-medium text-gray-700" for="qualification"
						>Qualification</label
					>
					<input
						id="qualification"
						name="qualification"
						type="text"
						bind:value={qualification}
						class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						required
					/>
				</div>
				<div>
					<label class="mb-1 block text-sm font-medium text-gray-700" for="fieldOfStudy"
						>Field of Study</label
					>
					<input
						id="fieldOfStudy"
						name="fieldOfStudy"
						type="text"
						bind:value={fieldOfStudy}
						class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
				<div class="flex gap-2">
					<button
						type="submit"
						disabled={loading}
						class="flex-1 rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
					>
						{loading ? 'Saving...' : isEditing ? 'Update Education' : 'Save Education'}
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

	{#if loadingEducation}
		<div class="mb-4 rounded bg-blue-100 p-4">
			<p class="font-medium">Loading your education...</p>
		</div>
	{:else if !session}
		<div class="mb-4 rounded bg-yellow-100 p-4">
			<p class="font-medium">You need to be logged in to view your education.</p>
			<button
				onclick={() => goto('/')}
				class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
			>
				Go to Login
			</button>
		</div>
	{:else if educationList.length === 0}
		<div class="rounded bg-gray-100 p-4 text-gray-700">
			<p>No education added yet. Use the button above to add your education history.</p>
		</div>
	{:else}
		<ul class="space-y-4">
			{#each educationList as edu}
				<li class="rounded border bg-white p-4 shadow">
					{#if deleteConfirmId === edu.id}
						<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
							<p class="font-medium">Are you sure you want to delete this education entry?</p>
							<div class="mt-2 flex gap-2">
								<form method="POST" action="?/delete" class="inline">
									<input type="hidden" name="id" value={edu.id} />
									<button
										type="submit"
										class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
										disabled={loading}
										onclick={(e) => {
											e.preventDefault();
											deleteEducation(edu.id);
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
								{edu.qualification || edu.degree || 'No qualification specified'} at {edu.institution}
							</div>
							<div class="text-sm text-gray-500">
								{edu.start_date ? formatDate(edu.start_date) : ''}
								{edu.start_date ? '-' : ''}
								{edu.end_date ? formatDate(edu.end_date) : 'Present'}
							</div>
						</div>
						<div class="flex gap-2">
							<button
								onclick={() => editEducation(edu)}
								class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
								title="Edit"
							>
								Edit
							</button>
							<button
								onclick={() => confirmDelete(edu.id)}
								class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200"
								title="Delete"
							>
								Delete
							</button>
						</div>
					</div>
					{#if edu.field_of_study}
						<div class="mt-2 text-gray-700">Field: {edu.field_of_study}</div>
					{/if}
				</li>
			{/each}
		</ul>
	{/if}

	<SectionNavigation />
</div>
