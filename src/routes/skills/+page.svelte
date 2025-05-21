<script lang="ts">
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import { onMount, onDestroy } from 'svelte';
	import { session as authSession } from '$lib/stores/authStore';
	import BreadcrumbNavigation from '$lib/components/BreadcrumbNavigation.svelte';
	import { invalidate } from '$app/navigation';
	import { enhance } from '$app/forms';

	interface PageData {
		skills: Skill[];
		error?: string;
		form?: {
			error: string;
		};
	}

	const { data } = $props<{ data: PageData }>();

	interface Skill {
		id: string;
		profile_id: string | null;
		name: string;
		level: string | null;
		category: string | null;
		created_at: string;
		updated_at: string;
	}

	// Form state
	let name = $state('');
	let level = $state('');
	let category = $state('');

	// UI state
	let skills = $state<Skill[]>(data.skills || []);
	let error = $state<string | undefined>(data.error);
	let success = $state<string>('');
	let loading = $state(false);
	let loadingSkills = $state(false);
	let showAddForm = $state(false);
	let isEditing = $state(false);
	let editingSkill = $state<Skill | null>(null);
	let deleteConfirmId = $state<string | null>(null);

	// Predefined options
	const SKILL_LEVELS = [
		{ value: 'Beginner', label: 'Beginner' },
		{ value: 'Intermediate', label: 'Intermediate' },
		{ value: 'Advanced', label: 'Advanced' },
		{ value: 'Expert', label: 'Expert' }
	];

	const COMMON_CATEGORIES = [
		{ value: 'Programming Languages', label: 'Programming Languages' },
		{ value: 'Frontend', label: 'Frontend Development' },
		{ value: 'Backend', label: 'Backend Development' },
		{ value: 'Database', label: 'Database' },
		{ value: 'DevOps', label: 'DevOps' },
		{ value: 'Mobile', label: 'Mobile Development' },
		{ value: 'Design', label: 'Design' },
		{ value: 'Soft Skills', label: 'Soft Skills' },
		{ value: 'Tools', label: 'Tools & Software' },
		{ value: 'Other', label: 'Other' }
	];

	// Session from store
	const session = $authSession;

	// Group skills by category
	function getSkillsByCategory(skillsList: Skill[]): Record<string, Skill[]> {
		const grouped: Record<string, Skill[]> = {};

		// First, collect all categories
		for (const skill of skillsList) {
			const cat = skill.category || 'Uncategorized';
			if (!grouped[cat]) {
				grouped[cat] = [];
			}
			grouped[cat].push(skill);
		}

		// Sort skills within each category by name
		for (const cat in grouped) {
			grouped[cat].sort((a, b) => a.name.localeCompare(b.name));
		}

		return grouped;
	}

	// Toggle add form visibility
	function toggleAddForm() {
		showAddForm = !showAddForm;
		if (!showAddForm) {
			resetForm();
			isEditing = false;
			editingSkill = null;
		}
	}

	// Reset form fields
	function resetForm() {
		name = '';
		level = '';
		category = '';
	}

	// Handle form submission
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		if (!session) {
			error = 'You need to be logged in to save skills.';
			return;
		}

		// Basic validation
		if (!name.trim()) {
			error = 'Skill name is required.';
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

			if (isEditing && editingSkill) {
				// Update existing skill
				result = await supabase
					.from('skills')
					.update({
						name,
						level: level || null,
						category: category || null
					})
					.eq('id', editingSkill.id)
					.select();
			} else {
				// Insert new skill
				result = await supabase
					.from('skills')
					.insert({
						profile_id: sessionData.session.user.id,
						name,
						level: level || null,
						category: category || null
					})
					.select();
			}

			const { data: skillData, error: submitError } = result;

			if (submitError) {
				console.error('Error submitting skill:', submitError);
				error = submitError.message || 'Failed to save skill';
				success = '';
			} else {
				console.log('Skill saved successfully:', skillData);

				// Clear any error first
				error = undefined;
				success = isEditing ? 'Skill updated successfully!' : 'Skill added successfully!';

				// Add/update the skill in the list
				if (skillData && skillData.length > 0) {
					const savedSkill = skillData[0];

					if (isEditing) {
						// Update skill in the list
						skills = skills.map((s) => (s.id === savedSkill.id ? savedSkill : s));
					} else {
						// Add new skill to the list
						skills = [...skills, savedSkill];
					}
				}

				// Reset the form
				resetForm();

				// Reset editing state
				isEditing = false;
				editingSkill = null;

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

	// Function to edit a skill
	function editSkill(skill: Skill): void {
		isEditing = true;
		editingSkill = skill;
		name = skill.name;
		level = skill.level || '';
		category = skill.category || '';
		showAddForm = true;

		// Scroll to the form
		if (browser) {
			setTimeout(() => {
				document.getElementById('skillForm')?.scrollIntoView({ behavior: 'smooth' });
			}, 100);
		}
	}

	// Function to cancel editing
	function cancelEdit() {
		isEditing = false;
		editingSkill = null;
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

	// Function to delete a skill
	async function deleteSkill(id: string): Promise<void> {
		if (!session) {
			error = 'You need to be logged in to delete skills.';
			return;
		}

		loading = true;
		error = undefined;
		success = '';

		try {
			// First update UI optimistically
			const skillToDelete = skills.find((skill) => skill.id === id);
			const backupSkills = [...skills];
			skills = skills.filter((skill) => skill.id !== id);

			// Then attempt the deletion in the database
			const { error: deleteError } = await supabase.from('skills').delete().eq('id', id);

			if (deleteError) {
				console.error('Error deleting skill:', deleteError);
				error = deleteError.message || 'Failed to delete skill';
				// Restore the backup state if there was an error
				skills = backupSkills;
			} else {
				success = 'Skill deleted successfully!';

				// Invalidate to ensure data consistency
				invalidate('app:skills');

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

			// Refresh data to ensure UI is consistent after an error
			refreshSkills();
		} finally {
			loading = false;
		}
	}

	// Client-side fallback to load skills directly
	async function loadSkillsFromClient() {
		loadingSkills = true;
		error = undefined;

		try {
			// Verify session first
			const { data: sessionData } = await supabase.auth.getSession();

			if (!sessionData.session) {
				console.log('No valid session for client-side load');
				loadingSkills = false;
				return;
			}

			console.log('Loading skills for user:', sessionData.session.user.id);

			// Fetch skills
			const { data: skillsData, error: skillsError } = await supabase
				.from('skills')
				.select('*')
				.eq('profile_id', sessionData.session.user.id)
				.order('category', { ascending: true })
				.order('name', { ascending: true });

			if (skillsError) {
				console.error('Error fetching skills from client:', skillsError);
				error = skillsError.message;
				return;
			}

			console.log('Client-side load successful:', skillsData?.length, 'skills');
			skills = skillsData || [];
		} catch (err) {
			console.error('Unexpected error loading skills from client:', err);
			error = 'Failed to load skills. Please try refreshing the page.';
		} finally {
			loadingSkills = false;
		}
	}

	// Check for success parameter in URL and show success message
	$effect(() => {
		if ($page.url.searchParams.get('success') === 'true') {
			success = 'Skill saved successfully!';

			// Clear success message after 3 seconds
			setTimeout(() => {
				success = '';
				// Update URL without the success parameter
				const url = new URL(window.location.href);
				url.searchParams.delete('success');
				window.history.replaceState({}, '', url.toString());
			}, 3000);
		}
	});

	// Set initial skills state from data prop whenever it changes
	$effect(() => {
		if (data.skills) {
			skills = data.skills;
		}

		// Show any server errors
		if (data.error) {
			error = data.error;
		}
	});

	// Function to refresh skills data
	async function refreshSkills() {
		loadingSkills = true;
		try {
			// Use SvelteKit's invalidation to trigger the +page.js load function
			await invalidate('app:skills');

			// Success feedback to user
			success = 'Skills refreshed successfully!';

			// Clear success message after 3 seconds
			setTimeout(() => {
				success = '';
			}, 3000);
		} catch (err) {
			console.error('Error refreshing skills:', err);
			error = 'Failed to refresh skills. Please try again.';
		} finally {
			loadingSkills = false;
		}
	}
</script>

<div class="mx-auto max-w-4xl space-y-6">
	<BreadcrumbNavigation />

	<h1 class="text-2xl font-bold">Skills</h1>
	<p class="text-gray-700">
		Add your technical and professional skills. These can be organized into categories on your CV.
	</p>

	<div class="mx-auto max-w-xl">
		<div class="mb-4 flex items-center justify-between">
			<h2 class="text-2xl font-bold">Your Skills</h2>
			<button
				onclick={toggleAddForm}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
			>
				{showAddForm ? 'Cancel' : 'Add Skill'}
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
			<div id="skillForm" class="mb-8 rounded bg-white p-6 shadow">
				<h3 class="mb-4 text-xl font-semibold">
					{isEditing ? 'Edit Skill' : 'Add New Skill'}
				</h3>

				<form
					method="POST"
					action={isEditing ? '?/update' : '?/create'}
					class="space-y-4"
					use:enhance={({ formData, cancel }) => {
						// Validate form before submission
						if (!formData.get('name')) {
							error = 'Skill name is required';
							return cancel();
						}

						// Set form status
						loading = true;
						error = undefined;

						// Get form data for optimistic update
						const optimisticName = formData.get('name') as string;
						const optimisticLevel = formData.get('level') as string;
						const optimisticCategory = formData.get('category') as string;

						// Create optimistic skill
						const optimisticSkill =
							isEditing && editingSkill
								? {
										...editingSkill,
										name: optimisticName,
										level: optimisticLevel || null,
										category: optimisticCategory || null
									}
								: {
										id: 'temp-' + Date.now(),
										profile_id: session?.user?.id,
										name: optimisticName,
										level: optimisticLevel || null,
										category: optimisticCategory || null,
										created_at: new Date().toISOString(),
										updated_at: new Date().toISOString()
									};

						// Apply optimistic update
						if (isEditing && editingSkill) {
							skills = skills.map((s) => (s.id === editingSkill.id ? optimisticSkill : s));
						} else {
							skills = [...skills, optimisticSkill];
						}

						return async ({ result, update }) => {
							loading = false;

							if (result.type === 'success') {
								success = isEditing ? 'Skill updated successfully!' : 'Skill added successfully!';

								// Ensure we have fresh data
								await invalidate('app:skills');

								// Reset form state
								resetForm();
								isEditing = false;
								editingSkill = null;

								// Hide form after successful submission
								showAddForm = false;
							} else if (result.type === 'failure') {
								// Handle errors
								if (result.data?.error) {
									error = result.data.error;
								} else {
									error = 'Failed to save skill. Please try again.';
								}

								// Refresh data to ensure UI consistency
								await refreshSkills();
							}

							await update();
						};
					}}
				>
					{#if data.form?.error}
						<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{data.form.error}</div>
					{/if}

					{#if isEditing && editingSkill}
						<input type="hidden" name="id" value={editingSkill.id} />
					{/if}

					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="name">Skill Name</label
						>
						<input
							id="name"
							name="name"
							type="text"
							bind:value={name}
							placeholder="e.g. JavaScript, Project Management, Adobe Photoshop"
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="level"
							>Skill Level</label
						>
						<select
							id="level"
							name="level"
							bind:value={level}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						>
							<option value="">Select Level (Optional)</option>
							{#each SKILL_LEVELS as skillLevel}
								<option value={skillLevel.value}>{skillLevel.label}</option>
							{/each}
						</select>
					</div>
					<div>
						<label class="mb-1 block text-sm font-medium text-gray-700" for="category"
							>Category</label
						>
						<select
							id="category"
							name="category"
							bind:value={category}
							class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
						>
							<option value="">Select Category (Optional)</option>
							{#each COMMON_CATEGORIES as cat}
								<option value={cat.value}>{cat.label}</option>
							{/each}
						</select>
						<p class="mt-1 text-xs text-gray-500">
							Categorizing your skills helps organize them on your CV.
						</p>
					</div>
					<div class="flex gap-2">
						<button
							type="submit"
							disabled={loading}
							class="flex-1 rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
						>
							{loading ? 'Saving...' : isEditing ? 'Update Skill' : 'Save Skill'}
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

		{#if loadingSkills}
			<div class="mb-4 rounded bg-blue-100 p-4">
				<p class="font-medium">Loading your skills...</p>
			</div>
		{:else if !session}
			<div class="mb-4 rounded bg-yellow-100 p-4">
				<p class="font-medium">You need to be logged in to view your skills.</p>
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			</div>
		{:else if skills.length === 0}
			<div class="rounded bg-gray-100 p-4 text-gray-700">
				<p>No skills added yet. Use the button above to add your skills.</p>
			</div>
		{:else}
			<!-- Group skills by category -->
			{@const skillsByCategory = getSkillsByCategory(skills)}

			<div class="space-y-6">
				{#each Object.entries(skillsByCategory) as [category, categorySkills]}
					<div class="rounded bg-white p-4 shadow">
						<h3 class="mb-3 text-lg font-semibold text-gray-800">{category}</h3>
						<ul class="grid grid-cols-1 gap-3 md:grid-cols-2">
							{#each categorySkills as skill}
								<li class="rounded border bg-gray-50 p-3">
									{#if deleteConfirmId === skill.id}
										<div class="mb-2 rounded bg-red-50 p-2 text-red-800">
											<p class="text-sm font-medium">Delete this skill?</p>
											<div class="mt-1 flex gap-2">
												<form method="POST" action="?/delete" class="inline">
													<input type="hidden" name="id" value={skill.id} />
													<button
														type="button"
														class="rounded bg-red-600 px-2 py-1 text-xs font-semibold text-white hover:bg-red-700"
														disabled={loading}
														onclick={() => deleteSkill(skill.id)}
													>
														{loading ? 'Deleting...' : 'Yes, Delete'}
													</button>
												</form>
												<button
													onclick={cancelDelete}
													class="rounded bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-300"
												>
													Cancel
												</button>
											</div>
										</div>
									{:else}
										<div class="flex items-center justify-between">
											<div>
												<div class="font-semibold">{skill.name}</div>
												{#if skill.level}
													<div class="mt-1">
														<span
															class="inline-block rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800"
														>
															{skill.level}
														</span>
													</div>
												{/if}
											</div>
											<div class="flex gap-1">
												<button
													onclick={() => editSkill(skill)}
													class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
													title="Edit"
												>
													Edit
												</button>
												<button
													onclick={() => confirmDelete(skill.id)}
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
					</div>
				{/each}
			</div>
		{/if}
	</div>
</div>
