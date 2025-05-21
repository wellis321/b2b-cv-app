<script lang="ts">
	import { onMount, createEventDispatcher } from 'svelte';
	import {
		type CategoryWithItems,
		type ResponsibilityCategory,
		type ResponsibilityItem,
		getResponsibilitiesForExperience,
		addCategory,
		updateCategory,
		deleteCategory,
		addItem,
		updateItem,
		deleteItem,
		reorderCategories,
		reorderItems
	} from './responsibilities';

	// Event dispatcher to communicate with parent
	const dispatch = createEventDispatcher();

	// Props
	let { workExperienceId = '', readOnly = false } = $props<{
		workExperienceId: string;
		readOnly?: boolean;
	}>();

	// State
	let categories = $state<CategoryWithItems[]>([]);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let isLoadingInProgress = $state(false);

	// Form state
	let newCategoryName = $state('');
	let editingCategoryId = $state<string | null>(null);
	let editCategoryValue = $state('');
	let newItemValues = $state<Record<string, string>>({});
	let editingItemId = $state<string | null>(null);
	let editItemValue = $state('');

	// Expanded state for categories
	let expandedCategories = $state<Record<string, boolean>>({});

	// Function to notify parent that we're editing responsibilities
	function setEditingResponsibilities(editing: boolean) {
		dispatch('editingResponsibilities', { editing });
	}

	// Load responsibilities on mount
	onMount(async () => {
		await loadResponsibilities();
	});

	// Export loadResponsibilities for use by error boundary
	export async function loadResponsibilities() {
		if (!workExperienceId) {
			console.error('Missing workExperienceId in ResponsibilitiesEditor');
			error = 'Missing work experience ID';
			loading = false;
			return;
		}

		// Prevent multiple simultaneous calls
		if (isLoadingInProgress) return;

		try {
			isLoadingInProgress = true;
			loading = true;
			error = null;

			categories = await getResponsibilitiesForExperience(workExperienceId);

			// Only initialize UI state if not in read-only mode
			if (!readOnly) {
				// Initialize expanded state for categories
				categories.forEach((cat) => {
					if (expandedCategories[cat.id] === undefined) {
						expandedCategories[cat.id] = true; // Default to expanded
					}
				});

				// Initialize new item text fields
				categories.forEach((cat) => {
					if (!newItemValues[cat.id]) {
						newItemValues[cat.id] = '';
					}
				});
			}
		} catch (err) {
			console.error('Error loading responsibilities:', err);
			error = 'Failed to load responsibilities. Please try again.';
		} finally {
			loading = false;
			isLoadingInProgress = false;
		}
	}

	// Toggle category expanded state
	function toggleCategory(categoryId: string) {
		expandedCategories[categoryId] = !expandedCategories[categoryId];
	}

	// Add a new category
	async function handleAddCategory() {
		if (!newCategoryName.trim()) return;

		// Notify parent we're editing responsibilities
		setEditingResponsibilities(true);

		try {
			const result = await addCategory(workExperienceId, newCategoryName);
			if (result) {
				// Store the added category ID for expanding it
				const newCategoryId = result.id;

				// Reset the input field
				newCategoryName = '';

				// Reload responsibilities
				await loadResponsibilities();

				// Make sure the new category is expanded
				if (newCategoryId) {
					expandedCategories[newCategoryId] = true;

					// Focus the new item input for this category after a short delay
					setTimeout(() => {
						// Initialize the new item input field if needed
						if (!newItemValues[newCategoryId]) {
							newItemValues[newCategoryId] = '';
						}

						// Try to focus the input field (if it exists in the DOM)
						const inputElement = document.querySelector(
							`input[data-category-id="${newCategoryId}"]`
						) as HTMLInputElement;
						if (inputElement) {
							inputElement.focus();
						}
					}, 100);
				}
			}
		} catch (err) {
			console.error('Error adding category:', err);
			error = 'Failed to add category. Please try again.';
		}
	}

	// Start editing a category
	function startEditCategory(category: ResponsibilityCategory) {
		editingCategoryId = category.id;
		editCategoryValue = category.name;
	}

	// Cancel category editing
	function cancelEditCategory() {
		editingCategoryId = null;
		editCategoryValue = '';
	}

	// Save category edit
	async function saveEditCategory(categoryId: string) {
		if (!editCategoryValue.trim()) return;

		try {
			const success = await updateCategory(categoryId, editCategoryValue);
			if (success) {
				editingCategoryId = null;
				editCategoryValue = '';
				await loadResponsibilities();
			}
		} catch (err) {
			console.error('Error updating category:', err);
			error = 'Failed to update category. Please try again.';
		}
	}

	// Delete a category
	async function handleDeleteCategory(categoryId: string) {
		if (!confirm('Are you sure you want to delete this category and all its items?')) {
			return;
		}

		try {
			const success = await deleteCategory(categoryId);
			if (success) {
				await loadResponsibilities();
			}
		} catch (err) {
			console.error('Error deleting category:', err);
			error = 'Failed to delete category. Please try again.';
		}
	}

	// Add a new item to a category
	async function handleAddItem(categoryId: string) {
		const content = newItemValues[categoryId]?.trim();
		if (!content) return;

		// Notify parent we're editing responsibilities
		setEditingResponsibilities(true);

		try {
			const result = await addItem(categoryId, content);

			if (result) {
				// Clear the input field
				newItemValues[categoryId] = '';

				// Reload the data
				await loadResponsibilities();

				// Ensure the category remains expanded
				expandedCategories[categoryId] = true;

				// Focus back on the input field after a short delay
				setTimeout(() => {
					const inputElement = document.querySelector(
						`input[data-category-id="${categoryId}"]`
					) as HTMLInputElement;
					if (inputElement) {
						inputElement.focus();
					}
				}, 100);
			} else {
				error = 'Failed to add item. Check console for details.';
			}
		} catch (err) {
			console.error('Error adding item:', err);
			error = 'Failed to add item. Please try again.';
		}
	}

	// Start editing an item
	function startEditItem(item: ResponsibilityItem) {
		editingItemId = item.id;
		editItemValue = item.content;
	}

	// Cancel item editing
	function cancelEditItem() {
		editingItemId = null;
		editItemValue = '';
	}

	// Save item edit
	async function saveEditItem(itemId: string) {
		if (!editItemValue.trim()) return;

		try {
			const success = await updateItem(itemId, editItemValue);
			if (success) {
				editingItemId = null;
				editItemValue = '';
				await loadResponsibilities();
			}
		} catch (err) {
			console.error('Error updating item:', err);
			error = 'Failed to update item. Please try again.';
		}
	}

	// Delete an item
	async function handleDeleteItem(itemId: string) {
		if (!confirm('Are you sure you want to delete this item?')) {
			return;
		}

		try {
			const success = await deleteItem(itemId);
			if (success) {
				await loadResponsibilities();
			}
		} catch (err) {
			console.error('Error deleting item:', err);
			error = 'Failed to delete item. Please try again.';
		}
	}

	// Format responsibilities as text
	export function getFormattedText(): string {
		if (categories.length === 0) return '';

		let text = '';

		categories.forEach((category, catIndex) => {
			text += `${category.name}:\n`;

			category.items.forEach((item, itemIndex) => {
				text += `- ${item.content}\n`;
			});

			if (catIndex < categories.length - 1) {
				text += '\n';
			}
		});

		return text;
	}
</script>

{#if loading}
	<div class="py-4 text-center text-gray-500">
		<p>Loading responsibilities...</p>
	</div>
{:else if error}
	<div class="py-4 text-red-500">
		<p>{error}</p>
		<button class="mt-2 text-sm text-indigo-600 underline" onclick={() => loadResponsibilities()}>
			Try again
		</button>
	</div>
{:else}
	<div class="mt-6">
		<!-- Conditional heading for editing mode vs read-only mode -->
		{#if !readOnly}
			<h3 class="mb-4 text-lg font-semibold text-gray-800">Key Responsibilities</h3>
		{:else if categories.length > 0}
			<h3 class="text-md mb-2 border-b border-gray-200 pb-2 font-medium text-gray-700">
				Key Responsibilities
			</h3>
		{/if}

		{#if categories.length === 0}
			{#if !readOnly}
				<p class="mb-4 text-gray-500 italic">No responsibilities added yet.</p>
			{/if}
		{:else}
			<div class="space-y-4">
				{#each categories as category (category.id)}
					{#if readOnly}
						<!-- Read-only compact display -->
						<div class="mb-2">
							<h4 class="font-medium text-gray-800">{category.name}</h4>
							{#if category.items.length > 0}
								<ul class="mt-1 list-disc space-y-1 pl-5">
									{#each category.items as item (item.id)}
										<li class="text-sm text-gray-700">{item.content}</li>
									{/each}
								</ul>
							{/if}
						</div>
					{:else}
						<!-- Editable view -->
						<div class="overflow-hidden rounded-md border bg-white">
							<!-- Category header -->
							<div class="flex items-center justify-between bg-gray-50 p-3">
								{#if editingCategoryId === category.id}
									<input
										type="text"
										bind:value={editCategoryValue}
										class="mr-2 flex-1 rounded-md border-gray-300"
										placeholder="Category name"
									/>
									<div class="flex space-x-2">
										<button
											onclick={() => saveEditCategory(category.id)}
											class="rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700"
											disabled={!editCategoryValue.trim()}
										>
											Save
										</button>
										<button
											onclick={cancelEditCategory}
											class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 hover:bg-gray-300"
										>
											Cancel
										</button>
									</div>
								{:else}
									<button
										onclick={() => toggleCategory(category.id)}
										class="flex flex-1 items-center text-left font-medium text-gray-800"
									>
										<span
											class="mr-2 transform transition-transform duration-200"
											class:rotate-90={expandedCategories[category.id]}
										>
											▶
										</span>
										{category.name}
									</button>
									<div class="flex space-x-2">
										<button
											onclick={() => startEditCategory(category)}
											class="text-sm text-indigo-600 hover:text-indigo-800"
										>
											Edit
										</button>
										<button
											onclick={() => handleDeleteCategory(category.id)}
											class="text-sm text-red-600 hover:text-red-800"
										>
											Delete
										</button>
									</div>
								{/if}
							</div>

							<!-- Category content - items -->
							{#if expandedCategories[category.id]}
								<div class="p-3">
									{#if category.items.length === 0}
										<p class="text-gray-500 italic">No items in this category yet.</p>
									{:else}
										<ul class="list-disc space-y-2 pl-6">
											{#each category.items as item (item.id)}
												<li>
													{#if editingItemId === item.id}
														<div class="mt-1 flex items-center">
															<input
																type="text"
																bind:value={editItemValue}
																class="mr-2 flex-1 rounded-md border-gray-300"
																placeholder="Item content"
															/>
															<button
																onclick={() => saveEditItem(item.id)}
																class="mr-1 rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700"
																disabled={!editItemValue.trim()}
															>
																Save
															</button>
															<button
																onclick={cancelEditItem}
																class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 hover:bg-gray-300"
															>
																Cancel
															</button>
														</div>
													{:else}
														<div class="group flex items-start">
															<span class="flex-1">{item.content}</span>
															<div class="ml-2 hidden space-x-2 group-hover:flex">
																<button
																	onclick={() => startEditItem(item)}
																	class="text-xs text-indigo-600 hover:text-indigo-800"
																>
																	Edit
																</button>
																<button
																	onclick={() => handleDeleteItem(item.id)}
																	class="text-xs text-red-600 hover:text-red-800"
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

									<div class="mt-4">
										<div class="flex items-center">
											<input
												type="text"
												bind:value={newItemValues[category.id]}
												class="flex-1 rounded-md border-gray-300"
												placeholder="Add a new responsibility item"
												data-category-id={category.id}
											/>
											<button
												onclick={() => handleAddItem(category.id)}
												class="ml-2 rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700 disabled:opacity-50"
												disabled={!newItemValues[category.id]?.trim()}
											>
												Add
											</button>
										</div>
									</div>
								</div>
							{/if}
						</div>
					{/if}
				{/each}
			</div>
		{/if}

		{#if !readOnly}
			<div class="mt-6">
				<h4 class="mb-2 text-sm font-medium text-gray-700">Add a new category</h4>
				<div class="flex">
					<input
						type="text"
						bind:value={newCategoryName}
						class="flex-1 rounded-md rounded-r-none border-gray-300"
						placeholder="e.g. Strategic Leadership, Project Management"
					/>
					<button
						onclick={handleAddCategory}
						class="rounded-md rounded-l-none bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
						disabled={!newCategoryName.trim()}
					>
						Add Category
					</button>
				</div>
			</div>
		{/if}
	</div>
{/if}
