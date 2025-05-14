<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { browser } from '$app/environment';
	import { session, authLoading } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import BreadcrumbNavigation from '$lib/components/BreadcrumbNavigation.svelte';
	import EvidenceEditor from './EvidenceEditor.svelte';
	import {
		type QualificationWithEvidence,
		type SupportingEvidence,
		getQualifications,
		createQualification,
		updateQualification,
		deleteQualification,
		addEvidence
	} from './qualificationUtils';

	let { data, form } = $props<{
		data: {
			qualifications?: QualificationWithEvidence[];
			error?: string;
			session?: any;
		};
		form?: {
			error?: string;
			values?: {
				level?: string;
				description?: string;
			};
		};
	}>();

	let level = $state('');
	let description = $state('');
	let qualifications = $state<QualificationWithEvidence[]>([]);
	let error = $state<string | null>(null);
	let success = $state<string | null>(null);
	let loading = $state(false);
	let loadingQualifications = $state(true);
	let initialCheckDone = $state(false);
	let showAddForm = $state(false);
	let editingQualification = $state<QualificationWithEvidence | null>(null);
	let isEditing = $state(false);
	let deleteConfirmId = $state<string | null>(null);
	let evidenceEditors = $state<Record<string, any>>({});
	let editingEvidence = $state(false);
	let tempEvidenceItems = $state<{ content: string }[]>([]);
	let newEvidenceContent = $state('');

	// Restore form values if validation failed
	$effect(() => {
		if (form?.values) {
			level = form.values.level || '';
			description = form.values.description || '';

			// Show the add form when there are form values
			showAddForm = true;
		}
	});

	// Reset messages when form values change
	$effect(() => {
		// Clear error/success when form values change
		if (level || description) {
			error = null;
		}
	});

	// Check for success parameter in URL
	$effect(() => {
		if ($page.url.searchParams.get('success') === 'true') {
			success = 'Qualification equivalence saved successfully!';

			// Clear success message after 3 seconds
			setTimeout(() => {
				success = null;
				// Update URL without the success parameter
				const url = new URL(window.location.href);
				url.searchParams.delete('success');
				window.history.replaceState({}, document.title, url.toString());
			}, 3000);
		}
	});

	// Check authentication and load data if needed
	onMount(async () => {
		console.log('Qualification Equivalence page mounted');

		// Clear any server error after a brief delay
		if (data.error) {
			setTimeout(() => {
				error = null;
			}, 5000);
		} else {
			error = null;
		}

		// Set initial qualifications from server data
		if (data.qualifications && data.qualifications.length > 0) {
			qualifications = data.qualifications;
			loadingQualifications = false;
		}

		// Check for authentication
		if (!data.session && !$session) {
			console.log('No session found on qualification equivalence page mount');
			error = 'Not authenticated. Please login first.';
			loadingQualifications = false;

			// If in browser, redirect to home
			if (browser) {
				setTimeout(() => {
					goto('/');
				}, 2000);
			}
		} else if ($session || data.session) {
			try {
				// We have a session, try to load qualifications if needed
				if (!data.qualifications || data.qualifications.length === 0) {
					console.log('Trying to load qualifications from client');
					const userId = $session?.user.id || data.session?.user.id;

					if (userId) {
						const fetchedQualifications = await getQualifications(userId);

						if (fetchedQualifications.length > 0) {
							qualifications = fetchedQualifications;

							// Update section status after loading qualifications
							await import('$lib/cv-sections').then((module) => {
								module.updateSectionStatus();
							});
						}
					}
				}
			} catch (err) {
				console.error('Error in client-side qualification load:', err);
				error = 'Error loading qualifications. Please refresh the page.';
			} finally {
				loadingQualifications = false;
			}
		} else {
			loadingQualifications = false;
		}

		initialCheckDone = true;
	});

	// Subscribe to auth state changes
	$effect(() => {
		if (!initialCheckDone) return;

		// If session changes after initial check, update UI accordingly
		if (!$session && !$authLoading) {
			console.log('Session lost during qualification equivalence page lifecycle');
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

	// Function to start editing a qualification
	function editQualification(qualification: QualificationWithEvidence) {
		isEditing = true;
		editingQualification = qualification;
		editingEvidence = false;

		level = qualification.level;
		description = qualification.description || '';
		showAddForm = true;

		// Initialize the evidence editor with a slight delay to ensure the component is mounted
		setTimeout(() => {
			if (editingQualification && evidenceEditors[editingQualification.id]) {
				evidenceEditors[editingQualification.id].updateEvidenceItems(qualification.evidence || []);
			}
		}, 300);

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('qualificationForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingQualification = null;
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

	// Function to delete a qualification
	async function handleDeleteQualification(id: string) {
		if (!$session) {
			error = 'You need to be logged in to delete qualification.';
			return;
		}

		loading = true;
		error = null;
		success = null;

		try {
			const successResult = await deleteQualification(id);

			if (successResult) {
				// Remove the qualification from the list
				qualifications = qualifications.filter((qual) => qual.id !== id);

				// Reset the delete confirmation
				deleteConfirmId = null;

				// Update section status
				await import('$lib/cv-sections').then((module) => {
					module.updateSectionStatus();
				});

				// Show success message
				success = 'Qualification deleted successfully!';

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = null;
				}, 3000);
			} else {
				error = 'Failed to delete qualification. Please try again.';
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
			editingQualification = null;
		}
	}

	// Reset the form after submission
	function resetForm() {
		level = '';
		description = '';
		tempEvidenceItems = [];
		newEvidenceContent = '';
	}

	// Add client-side form handling to validate session before submission
	async function handleSubmit(event: SubmitEvent) {
		// Prevent default form submission
		event.preventDefault();

		// Clear all status messages first
		error = null;
		success = null;

		// Check authentication first
		if (!$session) {
			error = 'You need to be logged in to save qualification. Please refresh and try again.';
			return;
		}

		// Validate form
		if (!level) {
			error = 'Please provide a qualification level';
			return;
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

			if (isEditing && editingQualification) {
				// Update existing qualification
				const updateSuccess = await updateQualification(
					editingQualification.id,
					level,
					description
				);

				if (updateSuccess) {
					// Update the qualification in the list
					const updatedQualification = {
						...editingQualification,
						level,
						description
					};

					qualifications = qualifications.map((qual) =>
						qual.id === updatedQualification.id ? updatedQualification : qual
					);

					// Display success message
					success = 'Qualification updated successfully!';

					// If we're actively editing evidence, stay in edit mode
					// Otherwise, return to the list view
					if (editingEvidence) {
						// Stay in edit mode for evidence
						editingQualification = updatedQualification;
					} else {
						// Close the form after updating
						resetForm();
						isEditing = false;
						editingQualification = null;
						showAddForm = false;
					}
				} else {
					error = 'Failed to update qualification';
				}
			} else {
				// Insert new qualification
				const userId = sessionData.session.user.id;
				const newQualification = await createQualification(userId, level, description);

				if (newQualification) {
					// Create a qualification with evidence object
					const qualificationWithEvidence: QualificationWithEvidence = {
						...newQualification,
						evidence: []
					};

					// If we have temp evidence items, save them
					if (tempEvidenceItems.length > 0) {
						const savedEvidenceItems: SupportingEvidence[] = [];

						// Add each evidence item to the database
						for (const item of tempEvidenceItems) {
							const result = await addEvidence(newQualification.id, item.content);
							if (result) {
								savedEvidenceItems.push(result);
							}
						}

						// Update the qualification object with the saved evidence
						qualificationWithEvidence.evidence = savedEvidenceItems;

						// Clear temp evidence items
						tempEvidenceItems = [];
					}

					// Add to the list
					qualifications = [qualificationWithEvidence, ...qualifications];

					// Display success message
					success = 'Qualification saved successfully!';

					// Switch to editing the newly created qualification for evidence
					isEditing = true;
					editingQualification = qualificationWithEvidence;
					editingEvidence = true;

					// Ensure the evidence editor will be initialized with the correct data
					setTimeout(() => {
						if (editingQualification && evidenceEditors[editingQualification.id]) {
							evidenceEditors[editingQualification.id].loadEvidence();
						}
					}, 300);

					// Scroll to the form
					if (browser) {
						setTimeout(() => {
							document.getElementById('qualificationForm')?.scrollIntoView({ behavior: 'smooth' });
						}, 100);
					}
				} else {
					error = 'Failed to save qualification';
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
		} catch (err) {
			console.error('Unexpected error during form submission:', err);
			error = 'An unexpected error occurred. Please try again.';
		} finally {
			loading = false;
		}
	}

	// Handle evidence editor event
	function handleEvidenceEditing(event: CustomEvent) {
		editingEvidence = event.detail.editing;
	}

	// Helper function to add temporary evidence when creating a new qualification
	function addTempEvidence() {
		if (!newEvidenceContent.trim()) return;

		tempEvidenceItems = [...tempEvidenceItems, { content: newEvidenceContent }];
		newEvidenceContent = '';
	}

	// Helper function to remove temporary evidence
	function removeTempEvidence(index: number) {
		tempEvidenceItems = tempEvidenceItems.filter((_, i) => i !== index);
	}
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Professional Qualification Equivalence</h1>
	<p class="text-gray-700">
		Show how your international and other qualifications are equivalent to local standards.
	</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Qualification Equivalences</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Qualification'}
			</button>
		</div>

		{#if error}
			<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
		{/if}

		{#if success}
			<div class="mb-4 rounded bg-green-100 p-4 text-green-700">{success}</div>
		{/if}

		<!-- Add/Edit form -->
		{#if showAddForm && ($session || data.session)}
			<div id="qualificationForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Qualification Equivalence' : 'Add New Qualification Equivalence'}
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

					{#if isEditing && editingQualification}
						<input type="hidden" name="id" value={editingQualification.id} />
					{/if}

					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="level">
							Level/Name <span class="text-red-600">*</span>
						</label>
						<input
							id="level"
							name="level"
							type="text"
							bind:value={level}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
							placeholder="e.g., Master's Degree, Professional Certification"
						/>
					</div>

					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="description">
							Description
						</label>
						<textarea
							id="description"
							name="description"
							bind:value={description}
							rows="4"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							placeholder="Describe what this qualification is equivalent to"
						></textarea>
					</div>

					{#if isEditing && editingQualification}
						<div class="mt-4 border-t pt-4">
							<EvidenceEditor
								qualificationId={editingQualification.id}
								bind:this={evidenceEditors[editingQualification.id]}
								on:editingEvidence={handleEvidenceEditing}
							/>
						</div>
					{:else}
						<!-- Supporting evidence section for new qualifications -->
						<div class="mt-4 border-t pt-4">
							<h3 class="mb-4 text-lg font-medium text-gray-700">Supporting Evidence</h3>

							{#if tempEvidenceItems.length === 0}
								<p class="mb-4 text-gray-500 italic">No supporting evidence added yet.</p>
							{:else}
								<ul class="mb-4 list-disc space-y-2 pl-6">
									{#each tempEvidenceItems as evidence, index}
										<li class="group flex items-start">
											<span class="flex-1">{evidence.content}</span>
											<button
												onclick={() => removeTempEvidence(index)}
												class="ml-2 text-xs text-red-600 hover:text-red-800"
												title="Remove"
											>
												Remove
											</button>
										</li>
									{/each}
								</ul>
							{/if}

							<!-- Add new evidence input -->
							<div class="flex items-center">
								<input
									type="text"
									bind:value={newEvidenceContent}
									class="flex-1 rounded-md border-gray-300"
									placeholder="Add supporting evidence"
								/>
								<button
									onclick={addTempEvidence}
									class="ml-2 rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700 disabled:opacity-50"
									disabled={!newEvidenceContent.trim()}
								>
									Add
								</button>
							</div>
						</div>
					{/if}

					<div class="flex gap-2">
						<button
							type="submit"
							disabled={loading}
							class="flex-1 rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
						>
							{loading ? 'Saving...' : isEditing ? 'Update Qualification' : 'Save Qualification'}
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

		{#if loadingQualifications}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your qualification equivalences...</p>
			</div>
		{:else if !$session && !data.session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your qualification equivalences.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if qualifications.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>
					No qualification equivalences added yet. Use the button above to add your professional
					qualification equivalence information.
				</p>
			</div>
		{:else}
			<ul class="space-y-4">
				{#each qualifications as qualification}
					<li class="rounded border bg-white p-4 shadow">
						{#if deleteConfirmId === qualification.id}
							<div class="mb-3 rounded bg-red-50 p-3 text-red-800">
								<p class="font-medium">Are you sure you want to delete this qualification?</p>
								<div class="mt-2 flex gap-2">
									<button
										type="button"
										class="rounded bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700"
										disabled={loading}
										onclick={() => handleDeleteQualification(qualification.id)}
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
						{/if}

						<div class="flex items-center justify-between">
							<div>
								<div class="text-lg font-semibold">{qualification.level}</div>
							</div>
							<div class="flex gap-2">
								<button
									onclick={() => editQualification(qualification)}
									class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
									title="Edit"
								>
									Edit
								</button>
								<button
									onclick={() => confirmDelete(qualification.id)}
									class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200"
									title="Delete"
								>
									Delete
								</button>
							</div>
						</div>

						<!-- Description section (if exists) -->
						{#if qualification.description && qualification.description.trim()}
							<div class="mt-2">
								<h4 class="mb-1 text-sm font-medium text-gray-700">Description</h4>
								<div class="whitespace-pre-line text-gray-700">
									{qualification.description}
								</div>
							</div>
						{/if}

						<!-- Supporting Evidence section (if exists) -->
						{#if qualification.evidence && qualification.evidence.length > 0}
							<div class="mt-3">
								<h4 class="mb-1 text-sm font-medium text-gray-700">Supporting Evidence</h4>
								<ul class="list-disc pl-5">
									{#each qualification.evidence as evidence}
										<li>{evidence.content}</li>
									{/each}
								</ul>
							</div>
						{/if}
					</li>
				{/each}
			</ul>
		{/if}
	</div>
</div>
