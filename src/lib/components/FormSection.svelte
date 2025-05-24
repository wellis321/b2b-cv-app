<script lang="ts">
	/**
	 * A reusable component for CV form sections with consistent styling
	 */
	export let title: string;
	export let required = false;
</script>

<div class="rounded-lg border border-gray-200 p-6 shadow-sm">
	<h3 class="mb-4 flex items-center text-lg font-medium text-gray-900">
		{title}
		{#if required}
			<span class="ml-1 text-sm text-gray-500">Required</span>
		{/if}
	</h3>

	<slot />
</div>
