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

	interface Certification {
		id: string;
		profile_id: string | null;
		name: string;
		issuer: string;
		date_obtained: string;
		expiry_date?: string | null;
		created_at?: string;
		updated_at?: string;
	}

	let { data } = $props<{ data: { certifications: Certification[]; error?: string } }>();

	// Form state
	let name = $state('');
	let issuer = $state('');
	let dateObtained = $state('');
	let expiryDate = $state('');

	// UI state
	let certifications = $state<Certification[]>(data.certifications || []);
	let error = $state<string | undefined>(data.error);
	let success = $state<string>('');
	let loading = $state(false);
	let loadingCertifications = $state(false);
	let showAddForm = $state(false);
	let isEditing = $state(false);
	let editingCertification = $state<Certification | null>(null);
	let deleteConfirmId = $state<string | null>(null);

	// Session from store
	const session = $authSession;

	// Format dates for display
	function formatDate(dateStr: string | null | undefined): string {
		if (!dateStr) return '';

		try {
			const date = Temporal.PlainDate.from(dateStr);
			// Format as MM/YYYY
			return `${date.month.toString().padStart(2, '0')}/${date.year}`;
		} catch (err) {
			console.error('Error formatting date:', err);
			// Fallback to basic formatting
			const date = new Date(dateStr);
			return `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
		}
	}

	// Sort certifications by date
	function sortCertifications(certList: Certification[]): Certification[] {
		return [...certList].sort((a, b) => {
			// Sort by date descending (newest first)
			return new Date(b.date_obtained).getTime() - new Date(a.date_obtained).getTime();
		});
	}

	// Toggle add form visibility
	function toggleAddForm() {
		showAddForm = !showAddForm;
		if (!showAddForm) {
			resetForm();
			isEditing = false;
			editingCertification = null;
		}
	}

	// Reset form fields
	function resetForm() {
		name = '';
		issuer = '';
		dateObtained = '';
		expiryDate = '';
	}

	// Handle form submission
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		if (!session) {
			error = 'You need to be logged in to save certifications.';
			return;
		}

		// Basic validation
		if (!name.trim()) {
			error = 'Certification name is required.';
			return;
		}

		if (!issuer.trim()) {
			error = 'Issuer is required.';
			return;
		}

		if (!dateObtained) {
			error = 'Date obtained is required.';
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

			if (isEditing && editingCertification) {
				// Update existing certification
				result = await supabase
					.from('certifications')
					.update({
						name,
						issuer,
						date_obtained: dateObtained,
						expiry_date: expiryDate || null
					})
					.eq('id', editingCertification.id)
					.select();
			} else {
				// Insert new certification
				result = await supabase
					.from('certifications')
					.insert({
						profile_id: sessionData.session.user.id,
						name,
						issuer,
						date_obtained: dateObtained,
						expiry_date: expiryDate || null
					})
					.select();
			}

			const { data: certData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting certification:', submitError);
				error = submitError.message || 'Failed to save certification';
				success = '';
			} else {
				console.log('Certification saved successfully:', certData);

				// Clear any error first
				error = undefined;
				success = isEditing
					? 'Certification updated successfully!'
					: 'Certification added successfully!';

				// Add/update the certification in the list
				if (certData && certData.length > 0) {
					const savedCert = certData[0];

					if (isEditing) {
						// Update certification in the list
						certifications = certifications.map((c) => (c.id === savedCert.id ? savedCert : c));
					} else {
						// Add new certification to the list
						certifications = sortCertifications([savedCert, ...certifications]);
					}
				}

				// Reset the form
				resetForm();

				// Reset editing state
				isEditing = false;
				editingCertification = null;

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

	// Function to edit a certification
	function editCertification(cert: Certification): void {
		isEditing = true;
		editingCertification = cert;
		name = cert.name;
		issuer = cert.issuer;
		dateObtained = cert.date_obtained;
		expiryDate = cert.expiry_date || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('certForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingCertification = null;
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

	// Function to delete a certification
	async function deleteCertification(id: string): Promise<void> {
		if (!session) {
			error = 'You need to be logged in to delete certifications.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			const { error: deleteError } = await supabase.from('certifications').delete().eq('id', id);

			if (deleteError) {
				console.error('Error deleting certification:', deleteError);
				error = deleteError.message || 'Failed to delete certification';
			} else {
				success = 'Certification deleted successfully!';

				// Remove the certification from the list
				certifications = certifications.filter((cert) => cert.id !== id);

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

	// Client-side fallback to load certifications directly
	async function loadCertificationsFromClient() {
		loadingCertifications = true;
		error = undefined;

		try {
			// Verify session first
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				console.log('No valid session for client-side load');
				loadingCertifications = false;
				return;
			}

			console.log('Loading certifications for user:', sessionData.session.user.id);

			// Fetch certifications
			const { data: certificationsData, error: certificationsError } = await supabase
				.from('certifications')
				.select('*')
				.eq('profile_id', sessionData.session.user.id)
				.order('date_obtained', { ascending: false });

			if (certificationsError) {
				console.error('Error fetching certifications from client:', certificationsError);
				error = certificationsError.message;
				return;
			}

			console.log('Client-side load successful:', certificationsData?.length, 'certifications');
			certifications = sortCertifications(certificationsData || []);

			// Update section status
			await import('$lib/cv-sections').then((module) => {
				module.updateSectionStatus();
			});
		} catch (err) {
			console.error('Unexpected error loading certifications from client:', err);
			error = 'Failed to load certifications. Please try refreshing the page.';
		} finally {
			loadingCertifications = false;
		}
	}

	// Check for success message in URL params and data loading
	onMount(async () => {
		if (browser) {
			// Check URL params for success message
			if ($page.url.searchParams.has('success')) {
				const successType = $page.url.searchParams.get('success');
				if (successType === 'create') {
					success = 'Certification added successfully!';
				} else if (successType === 'update') {
					success = 'Certification updated successfully!';
				} else if (successType === 'delete') {
					success = 'Certification deleted successfully!';
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

			// If data was loaded properly on the server, we'll have certifications
			// Otherwise, try to load them directly from client
			if (session && (!certifications || certifications.length === 0)) {
				console.log('No certifications loaded from server, trying client-side fetch');
				await loadCertificationsFromClient();
			}
		}
	});
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Certifications</h1>
	<p class="text-gray-700">
		Add your professional certifications and credentials to showcase your expertise.
	</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Certifications</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Certification'}
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
			<div id="certForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Certification' : 'Add New Certification'}
				</h3>

				<form onsubmit={handleSubmit} method="POST" class="space-y-4">
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="name"
							>Certification Name</label
						>
						<input
							id="name"
							name="name"
							type="text"
							bind:value={name}
							placeholder="e.g. AWS Certified Developer, CCNA, PMP"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="issuer">Issuer</label>
						<input
							id="issuer"
							name="issuer"
							type="text"
							bind:value={issuer}
							placeholder="e.g. Amazon Web Services, Cisco, PMI"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div class="flex gap-4">
						<div class="flex-1">
							<label class="mb-1 block text-sm font-medium text-gray-700" for="dateObtained"
								>Date Obtained</label
							>
							<input
								id="dateObtained"
								name="dateObtained"
								type="date"
								bind:value={dateObtained}
								class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
								required
							/>
						</div>
						<div class="flex-1">
							<label class="mb-1 block text-sm font-medium text-gray-700" for="expiryDate">
								Expiry Date <span class="text-xs text-gray-500">(Optional)</span>
							</label>
							<input
								id="expiryDate"
								name="expiryDate"
								type="date"
								bind:value={expiryDate}
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
							{loading ? 'Saving...' : isEditing ? 'Update Certification' : 'Save Certification'}
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

		{#if loadingCertifications}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your certifications...</p>
			</div>
		{:else if !session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your certifications.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if certifications.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>No certifications added yet. Use the button above to add your certifications.</p>
			</div>
		{:else}
			<ul class="space-y-4">
				{#each certifications as cert}
					<li class="rounded border bg-white p-4 shadow">
						{#if deleteConfirmId === cert.id}
							<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
								<p class="font-medium">Are you sure you want to delete this certification?</p>
								<div class="mt-2 flex gap-2">
									<button
										type="button"
										class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
										disabled={loading}
										onclick={() => deleteCertification(cert.id)}
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
									<div class="font-semibold">{cert.name}</div>
									<div class="text-sm text-gray-600">Issued by: {cert.issuer}</div>
									<div class="text-sm text-gray-500">
										Obtained: {formatDate(cert.date_obtained)}
										{#if cert.expiry_date}
											<span class="ml-2">
												{new Date(cert.expiry_date) < new Date()
													? '(Expired: '
													: '(Expires: '}{formatDate(cert.expiry_date)})
											</span>
										{/if}
									</div>
								</div>
								<div class="flex gap-2">
									<button
										onclick={() => editCertification(cert)}
										class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
										title="Edit"
									>
										Edit
									</button>
									<button
										onclick={() => confirmDelete(cert.id)}
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
