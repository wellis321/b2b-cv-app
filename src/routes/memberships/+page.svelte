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

	interface Membership {
		id: string;
		profile_id: string | null;
		organisation: string;
		role?: string | null;
		start_date: string;
		end_date?: string | null;
		created_at?: string;
		updated_at?: string;
	}

	let { data } = $props<{ data: { memberships: Membership[]; error?: string } }>();

	// Form state
	let organisation = $state('');
	let role = $state('');
	let startDate = $state('');
	let endDate = $state('');

	// UI state
	let memberships = $state<Membership[]>(data.memberships || []);
	let error = $state<string | undefined>(data.error);
	let success = $state<string>('');
	let loading = $state(false);
	let loadingMemberships = $state(false);
	let showAddForm = $state(false);
	let isEditing = $state(false);
	let editingMembership = $state<Membership | null>(null);
	let deleteConfirmId = $state<string | null>(null);

	// Session from store
	const session = $authSession;

	// Format dates for display
	function formatDate(dateStr: string | null | undefined): string {
		if (!dateStr) return 'Present';

		try {
			const date = Temporal.PlainDate.from(dateStr);
			// Format as DD/MM/YYYY
			return `${date.day.toString().padStart(2, '0')}/${date.month.toString().padStart(2, '0')}/${date.year}`;
		} catch (err) {
			console.error('Error formatting date:', err);
			// Fallback to basic formatting
			const date = new Date(dateStr);
			return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
		}
	}

	// Sort memberships by date
	function sortMemberships(memList: Membership[]): Membership[] {
		return [...memList].sort((a, b) => {
			// Sort by date descending (newest first)
			// Use end_date if available, otherwise start_date
			const dateA = a.end_date ? a.end_date : a.start_date;
			const dateB = b.end_date ? b.end_date : b.start_date;

			// Present/ongoing memberships should appear first
			if (!a.end_date && b.end_date) return -1;
			if (a.end_date && !b.end_date) return 1;

			return new Date(dateB).getTime() - new Date(dateA).getTime();
		});
	}

	// Toggle add form visibility
	function toggleAddForm() {
		showAddForm = !showAddForm;
		if (!showAddForm) {
			resetForm();
			isEditing = false;
			editingMembership = null;
		}
	}

	// Reset form fields
	function resetForm() {
		organisation = '';
		role = '';
		startDate = '';
		endDate = '';
	}

	// Handle form submission
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		if (!session) {
			error = 'You need to be logged in to save memberships.';
			return;
		}

		// Basic validation
		if (!organisation.trim()) {
			error = 'Organisation name is required.';
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

			if (isEditing && editingMembership) {
				// Update existing membership
				result = await supabase
					.from('professional_memberships')
					.update({
						organisation,
						role: role || null,
						start_date: startDate,
						end_date: endDate || null
					})
					.eq('id', editingMembership.id)
					.select();
			} else {
				// Insert new membership
				result = await supabase
					.from('professional_memberships')
					.insert({
						profile_id: sessionData.session.user.id,
						organisation,
						role: role || null,
						start_date: startDate,
						end_date: endDate || null
					})
					.select();
			}

			const { data: memData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting membership:', submitError);
				error = submitError.message || 'Failed to save membership';
				success = '';
			} else {
				console.log('Membership saved successfully:', memData);

				// Clear any error first
				error = undefined;
				success = isEditing ? 'Membership updated successfully!' : 'Membership added successfully!';

				// Add/update the membership in the list
				if (memData && memData.length > 0) {
					const savedMem = memData[0];

					if (isEditing) {
						// Update membership in the list
						memberships = memberships.map((m) => (m.id === savedMem.id ? savedMem : m));
					} else {
						// Add new membership to the list
						memberships = sortMemberships([savedMem, ...memberships]);
					}
				}

				// Reset the form
				resetForm();

				// Reset editing state
				isEditing = false;
				editingMembership = null;

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

	// Function to edit a membership
	function editMembership(mem: Membership): void {
		isEditing = true;
		editingMembership = mem;
		organisation = mem.organisation;
		role = mem.role || '';
		startDate = mem.start_date;
		endDate = mem.end_date || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('membershipForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingMembership = null;
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

	// Function to delete a membership
	async function deleteMembership(id: string): Promise<void> {
		if (!session) {
			error = 'You need to be logged in to delete memberships.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			const { error: deleteError } = await supabase
				.from('professional_memberships')
				.delete()
				.eq('id', id);

			if (deleteError) {
				console.error('Error deleting membership:', deleteError);
				error = deleteError.message || 'Failed to delete membership';
			} else {
				success = 'Membership deleted successfully!';

				// Remove the membership from the list
				memberships = memberships.filter((membership) => membership.id !== id);

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

	// Client-side fallback to load memberships directly
	async function loadMembershipsFromClient() {
		loadingMemberships = true;
		error = undefined;

		try {
			// Verify session first
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				console.log('No valid session for client-side load');
				loadingMemberships = false;
				return;
			}

			console.log('Loading memberships for user:', sessionData.session.user.id);

			// Fetch memberships
			const { data: membershipsData, error: membershipsError } = await supabase
				.from('professional_memberships')
				.select('*')
				.eq('profile_id', sessionData.session.user.id)
				.order('start_date', { ascending: false });

			if (membershipsError) {
				console.error('Error fetching memberships from client:', membershipsError);
				error = membershipsError.message;
				return;
			}

			console.log('Client-side load successful:', membershipsData?.length, 'memberships');
			memberships = sortMemberships(membershipsData || []);

			// Update the section status after successfully loading memberships
			await import('$lib/cv-sections').then((module) => {
				module.updateSectionStatus();
			});
		} catch (err) {
			console.error('Unexpected error loading memberships from client:', err);
			error = 'Failed to load memberships. Please try refreshing the page.';
		} finally {
			loadingMemberships = false;
		}
	}

	// Check for success message in URL params and data loading
	onMount(async () => {
		if (browser) {
			// Check URL params for success message
			if ($page.url.searchParams.has('success')) {
				const successType = $page.url.searchParams.get('success');
				if (successType === 'create') {
					success = 'Membership added successfully!';
				} else if (successType === 'update') {
					success = 'Membership updated successfully!';
				} else if (successType === 'delete') {
					success = 'Membership deleted successfully!';
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

			// If data was loaded properly on the server, we'll have memberships
			// Otherwise, try to load them directly from client
			if (session && (!memberships || memberships.length === 0)) {
				console.log('No memberships loaded from server, trying client-side fetch');
				await loadMembershipsFromClient();
			}
		}
	});
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Professional Memberships</h1>
	<p class="text-gray-700">
		Add your memberships in professional organisations and industry associations.
	</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Professional Memberships</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Membership'}
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
			<div id="membershipForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Membership' : 'Add New Membership'}
				</h3>

				<form onsubmit={handleSubmit} method="POST" class="space-y-4">
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="organisation"
							>Organisation</label
						>
						<input
							id="organisation"
							name="organisation"
							type="text"
							bind:value={organisation}
							placeholder="e.g. IEEE, ACM, BCS"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="role">
							Role <span class="text-xs text-gray-500">(Optional)</span>
						</label>
						<input
							id="role"
							name="role"
							type="text"
							bind:value={role}
							placeholder="e.g. Member, Committee Member, Fellow"
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
							<label class="mb-1 block text-sm font-medium text-gray-700" for="endDate">
								End Date <span class="text-xs text-gray-500">(Leave blank if current)</span>
							</label>
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
							{loading ? 'Saving...' : isEditing ? 'Update Membership' : 'Save Membership'}
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

		{#if loadingMemberships}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your memberships...</p>
			</div>
		{:else if !session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your memberships.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if memberships.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>No professional memberships added yet. Use the button above to add your memberships.</p>
			</div>
		{:else}
			<ul class="space-y-4">
				{#each memberships as mem}
					<li class="rounded border bg-white p-4 shadow">
						{#if deleteConfirmId === mem.id}
							<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
								<p class="font-medium">Are you sure you want to delete this membership?</p>
								<div class="mt-2 flex gap-2">
									<button
										type="button"
										class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
										disabled={loading}
										onclick={() => deleteMembership(mem.id)}
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
								<div>
									<div class="font-semibold">{mem.organisation}</div>
									{#if mem.role}
										<div class="text-sm text-gray-600">Role: {mem.role}</div>
									{/if}
									<div class="text-sm text-gray-500">
										{formatDate(mem.start_date)} - {mem.end_date
											? formatDate(mem.end_date)
											: 'Present'}
									</div>
								</div>
								<div class="flex gap-2">
									<button
										onclick={() => editMembership(mem)}
										class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
										title="Edit"
									>
										Edit
									</button>
									<button
										onclick={() => confirmDelete(mem.id)}
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
