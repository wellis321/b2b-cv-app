<script lang="ts">
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import { session as authSession } from '$lib/stores/authStore';
	import BreadcrumbNavigation from '$lib/components/BreadcrumbNavigation.svelte';

	interface Interest {
		id: string;
		profile_id: string | null;
		name: string;
		description?: string | null;
		created_at?: string;
		updated_at?: string;
	}

	let { data } = $props<{ data: { interests: Interest[]; error?: string } }>();

	// Form state
	let name = $state('');
	let description = $state('');

	// UI state
	let interests = $state<Interest[]>(data.interests || []);
	let error = $state<string | undefined>(data.error);
	let success = $state<string>('');
	let loading = $state(false);
	let loadingInterests = $state(false);
	let showAddForm = $state(false);
	let isEditing = $state(false);
	let editingInterest = $state<Interest | null>(null);
	let deleteConfirmId = $state<string | null>(null);

	// Session from store
	const session = $authSession;

	// Sort interests alphabetically
	function sortInterests(interestList: Interest[]): Interest[] {
		return [...interestList].sort((a, b) => {
			return a.name.localeCompare(b.name);
		});
	}

	// Toggle add form visibility
	function toggleAddForm() {
		showAddForm = !showAddForm;
		if (!showAddForm) {
			resetForm();
			isEditing = false;
			editingInterest = null;
		}
	}

	// Reset form fields
	function resetForm() {
		name = '';
		description = '';
	}

	// Handle form submission
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		if (!session) {
			error = 'You need to be logged in to save interests.';
			return;
		}

		// Basic validation
		if (!name.trim()) {
			error = 'Interest name is required.';
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

			if (isEditing && editingInterest) {
				// Update existing interest
				result = await supabase
					.from('interests')
					.update({
						name,
						description: description || null
					})
					.eq('id', editingInterest.id)
					.select();
			} else {
				// Insert new interest
				result = await supabase
					.from('interests')
					.insert({
						profile_id: sessionData.session.user.id,
						name,
						description: description || null
					})
					.select();
			}

			const { data: interestData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting interest:', submitError);
				error = submitError.message || 'Failed to save interest';
				success = '';
			} else {
				console.log('Interest saved successfully:', interestData);

				// Clear any error first
				error = undefined;
				success = isEditing ? 'Interest updated successfully!' : 'Interest added successfully!';

				// Add/update the interest in the list
				if (interestData && interestData.length > 0) {
					const savedInterest = interestData[0];

					if (isEditing) {
						// Update interest in the list
						interests = interests.map((i) => (i.id === savedInterest.id ? savedInterest : i));
					} else {
						// Add new interest to the list
						interests = sortInterests([savedInterest, ...interests]);
					}
				}

				// Reset the form
				resetForm();

				// Reset editing state
				isEditing = false;
				editingInterest = null;

				// Hide the form after successful submission
				showAddForm = false;

				// Update section status
				await import('$lib/cv-sections').then((module) => {
					module.updateSectionStatus();
				});

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

	// Function to edit an interest
	function editInterest(interest: Interest): void {
		isEditing = true;
		editingInterest = interest;
		name = interest.name;
		description = interest.description || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('interestForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingInterest = null;
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

	// Function to delete an interest
	async function deleteInterest(id: string): Promise<void> {
		if (!session) {
			error = 'You need to be logged in to delete interests.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			const { error: deleteError } = await supabase.from('interests').delete().eq('id', id);

			if (deleteError) {
				console.error('Error deleting interest:', deleteError);
				error = deleteError.message || 'Failed to delete interest';
			} else {
				success = 'Interest deleted successfully!';

				// Remove the interest from the list
				interests = interests.filter((interest) => interest.id !== id);

				// Reset the delete confirmation
				deleteConfirmId = null;

				// Update section status
				await import('$lib/cv-sections').then((module) => {
					module.updateSectionStatus();
				});

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

	// Client-side fallback to load interests directly
	async function loadInterestsFromClient() {
		loadingInterests = true;
		error = undefined;

		try {
			// Verify session first
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				console.log('No valid session for client-side load');
				loadingInterests = false;
				return;
			}

			console.log('Loading interests for user:', sessionData.session.user.id);

			// Fetch interests
			const { data: interestsData, error: interestsError } = await supabase
				.from('interests')
				.select('*')
				.eq('profile_id', sessionData.session.user.id)
				.order('name', { ascending: true });

			if (interestsError) {
				console.error('Error fetching interests from client:', interestsError);
				error = interestsError.message;
				return;
			}

			console.log('Client-side load successful:', interestsData?.length, 'interests');
			interests = interestsData || [];

			// Update section status
			await import('$lib/cv-sections').then((module) => {
				module.updateSectionStatus();
			});
		} catch (err) {
			console.error('Unexpected error loading interests from client:', err);
			error = 'Failed to load interests. Please try refreshing the page.';
		} finally {
			loadingInterests = false;
		}
	}

	// Check for success message in URL params and data loading
	onMount(async () => {
		if (browser) {
			// Check URL params for success message
			if ($page.url.searchParams.has('success')) {
				const successType = $page.url.searchParams.get('success');
				if (successType === 'create') {
					success = 'Interest added successfully!';
				} else if (successType === 'update') {
					success = 'Interest updated successfully!';
				} else if (successType === 'delete') {
					success = 'Interest deleted successfully!';
				}

				// Clear URL params
				const url = new URL(window.location.href);
				url.searchParams.delete('success');
				history.replaceState({}, document.title, url.toString());

				// Update section status
				await import('$lib/cv-sections').then((module) => {
					module.updateSectionStatus();
				});

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = '';
				}, 3000);
			}

			// If data was loaded properly on the server, we'll have interests
			// Otherwise, try to load them directly from client
			if (session && (!interests || interests.length === 0)) {
				console.log('No interests loaded from server, trying client-side fetch');
				await loadInterestsFromClient();
			}
		}
	});
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Interests & Activities</h1>
	<p class="text-gray-700">
		Add your hobbies, personal interests, and leisure activities to show a more complete picture of
		yourself.
	</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Interests & Activities</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Interest'}
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
			<div id="interestForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Interest' : 'Add New Interest'}
				</h3>

				<form onsubmit={handleSubmit} method="POST" class="space-y-4">
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="name"
							>Interest Name</label
						>
						<input
							id="name"
							name="name"
							type="text"
							bind:value={name}
							placeholder="e.g. Photography, Hiking, Reading, Volunteering"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="description">
							Description <span class="text-xs text-gray-500">(Optional)</span>
						</label>
						<textarea
							id="description"
							name="description"
							bind:value={description}
							placeholder="Add details about your interest or activity"
							rows="3"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						></textarea>
					</div>
					<div class="flex gap-2">
						<button
							type="submit"
							disabled={loading}
							class="flex-1 rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
						>
							{loading ? 'Saving...' : isEditing ? 'Update Interest' : 'Save Interest'}
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

		{#if loadingInterests}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your interests...</p>
			</div>
		{:else if !session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your interests.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if interests.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>No interests or activities added yet. Use the button above to add your interests.</p>
			</div>
		{:else}
			<ul class="space-y-4">
				{#each interests as interest}
					<li class="rounded border bg-white p-4 shadow">
						{#if deleteConfirmId === interest.id}
							<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
								<p class="font-medium">Are you sure you want to delete this interest?</p>
								<div class="mt-2 flex gap-2">
									<button
										type="button"
										class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
										disabled={loading}
										onclick={() => deleteInterest(interest.id)}
									>
										{loading ? 'Deleting...' : 'Yes, Delete'}
									</button>
									<button
										onclick={cancelDelete}
										class="rounded bg-gray-200 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-300"
									>
										Cancel
									</button>
								</div>
							</div>
						{:else}
							<div class="flex items-center justify-between">
								<div class="flex-1">
									<div class="font-semibold">{interest.name}</div>
									{#if interest.description}
										<div class="mt-1 text-sm text-gray-600">{interest.description}</div>
									{/if}
								</div>
								<div class="flex gap-2">
									<button
										onclick={() => editInterest(interest)}
										class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
										title="Edit"
									>
										Edit
									</button>
									<button
										onclick={() => confirmDelete(interest.id)}
										class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200"
										title="Delete"
									>
										Delete
									</button>
								</div>
							</div>
						{/if}
					</li>
				{/each}
			</ul>
		{/if}
	</div>
</div>
