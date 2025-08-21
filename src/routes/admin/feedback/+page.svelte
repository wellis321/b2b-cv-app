<script lang="ts">
	import { onMount } from 'svelte';
	import { supabase } from '$lib/supabase';
	import { isAdminUser } from '$lib/adminConfig';

	let feedback = $state([]);
	let loading = $state(true);
	let error = $state('');

	onMount(async () => {
		if (!isAdminUser()) {
			return;
		}

		await loadFeedback();
	});

	async function loadFeedback() {
		try {
			loading = true;
			error = '';

			const { data, error: fetchError } = await supabase
				.from('user_feedback')
				.select(
					`
          *,
          profiles:user_id (
            email,
            full_name
          )
        `
				)
				.order('created_at', { ascending: false });

			if (fetchError) {
				throw fetchError;
			}

			feedback = data || [];
		} catch (err) {
			console.error('Error loading feedback:', err);
			error = 'Failed to load feedback';
		} finally {
			loading = false;
		}
	}

	function formatDate(dateString: string) {
		return new Date(dateString).toLocaleDateString('en-GB', {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit'
		});
	}

	function getPriorityColor(priority: string) {
		switch (priority) {
			case 'critical':
				return 'bg-red-100 text-red-800';
			case 'high':
				return 'bg-orange-100 text-orange-800';
			case 'medium':
				return 'bg-yellow-100 text-yellow-800';
			case 'low':
				return 'bg-green-100 text-green-800';
			default:
				return 'bg-gray-100 text-gray-800';
		}
	}

	function getCategoryColor(category: string) {
		switch (category) {
			case 'bug_report':
				return 'bg-red-100 text-red-800';
			case 'feature_request':
				return 'bg-blue-100 text-blue-800';
			case 'improvement':
				return 'bg-green-100 text-green-800';
			case 'general':
				return 'bg-gray-100 text-gray-800';
			default:
				return 'bg-purple-100 text-purple-800';
		}
	}
</script>

<svelte:head>
	<title>User Feedback | Admin</title>
</svelte:head>

<div class="py-6">
	<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
		<div class="mb-8">
			<h1 class="text-3xl font-bold text-gray-900">User Feedback</h1>
			<p class="mt-2 text-gray-600">Feedback from early access users to help guide development</p>
		</div>

		{#if error}
			<div class="mb-6 rounded-md border border-red-400 bg-red-50 p-4 text-red-700">
				{error}
			</div>
		{/if}

		{#if loading}
			<div class="flex justify-center py-12">
				<div class="h-8 w-8 animate-spin rounded-full border-t-2 border-b-2 border-blue-500"></div>
			</div>
		{:else if feedback.length === 0}
			<div class="py-12 text-center">
				<p class="text-gray-500">No feedback submitted yet.</p>
			</div>
		{:else}
			<div class="overflow-hidden bg-white shadow sm:rounded-md">
				<ul class="divide-y divide-gray-200">
					{#each feedback as item}
						<li class="px-6 py-4">
							<div class="flex items-start justify-between">
								<div class="min-w-0 flex-1">
									<div class="mb-2 flex items-center space-x-3">
										<span
											class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {getPriorityColor(
												item.priority
											)}"
										>
											{item.priority}
										</span>
										<span
											class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {getCategoryColor(
												item.category
											)}"
										>
											{item.category.replace('_', ' ')}
										</span>
										<span class="text-sm text-gray-500">
											{formatDate(item.created_at)}
										</span>
									</div>

									<div class="mb-2 text-sm text-gray-900">
										<strong>From:</strong>
										{item.profiles?.full_name || item.profiles?.email || 'Unknown User'}
									</div>

									<p class="text-sm whitespace-pre-wrap text-gray-700">{item.feedback}</p>
								</div>
							</div>
						</li>
					{/each}
				</ul>
			</div>

			<div class="mt-6 text-center">
				<button
					on:click={loadFeedback}
					class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
				>
					Refresh
				</button>
			</div>
		{/if}
	</div>
</div>
