<script lang="ts">
	import { createEventDispatcher } from 'svelte';

	const dispatch = createEventDispatcher();

	let feedback = $state('');
	let category = $state('general');
	let priority = $state('medium');
	let submitting = $state(false);

	const categories = [
		{ value: 'general', label: 'General Feedback' },
		{ value: 'feature_request', label: 'Feature Request' },
		{ value: 'bug_report', label: 'Bug Report' },
		{ value: 'improvement', label: 'Improvement Suggestion' },
		{ value: 'other', label: 'Other' }
	];

	const priorities = [
		{ value: 'low', label: 'Low' },
		{ value: 'medium', label: 'Medium' },
		{ value: 'high', label: 'High' },
		{ value: 'critical', label: 'Critical' }
	];

	async function handleSubmit() {
		if (!feedback.trim()) return;

		submitting = true;

		try {
			const response = await fetch('/api/feedback', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					feedback: feedback.trim(),
					category,
					priority
				})
			});

			if (!response.ok) {
				throw new Error('Failed to submit feedback');
			}

			const result = await response.json();

			// Dispatch success event
			dispatch('success', { message: result.message || 'Thank you for your feedback!' });

			// Reset form
			feedback = '';
			category = 'general';
			priority = 'medium';
		} catch (error) {
			dispatch('error', { message: 'Failed to submit feedback. Please try again.' });
		} finally {
			submitting = false;
		}
	}
</script>

<div class="rounded-lg bg-white p-6 shadow-lg">
	<h3 class="mb-4 text-lg font-medium text-gray-900">Development Feedback</h3>
	<p class="mb-6 text-sm text-gray-600">
		As an early access user, your feedback directly influences our development priorities. Help us
		build the features you need most!
	</p>

	<form on:submit|preventDefault={handleSubmit} class="space-y-4">
		<div>
			<label for="category" class="mb-1 block text-sm font-medium text-gray-700"> Category </label>
			<select
				id="category"
				bind:value={category}
				class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
			>
				{#each categories as cat}
					<option value={cat.value}>{cat.label}</option>
				{/each}
			</select>
		</div>

		<div>
			<label for="priority" class="mb-1 block text-sm font-medium text-gray-700"> Priority </label>
			<select
				id="priority"
				bind:value={priority}
				class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
			>
				{#each priorities as pri}
					<option value={pri.value}>{pri.label}</option>
				{/each}
			</select>
		</div>

		<div>
			<label for="feedback" class="mb-1 block text-sm font-medium text-gray-700">
				Your Feedback
			</label>
			<textarea
				id="feedback"
				bind:value={feedback}
				rows="4"
				placeholder="Tell us what you think, what you'd like to see, or report any issues..."
				class="w-full resize-none rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
				required
			></textarea>
		</div>

		<button
			type="submit"
			disabled={submitting || !feedback.trim()}
			class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
		>
			{submitting ? 'Submitting...' : 'Submit Feedback'}
		</button>
	</form>

	<div class="mt-4 text-center text-xs text-gray-500">
		<p>Your feedback helps us prioritize development and improve the CV Builder for everyone.</p>
	</div>
</div>
