<script lang="ts">
	import { page } from '$app/stores';
	import { getAdjacentSections, getCurrentSection, type CVSection } from '$lib/cv-sections';

	let previousSection = $state<CVSection | null>(null);
	let nextSection = $state<CVSection | null>(null);

	// Get current path from the page store
	$effect(() => {
		const path = $page.url.pathname;
		const currentPath = path.endsWith('/') ? path.slice(0, -1) : path;
		const { prev, next } = getAdjacentSections(currentPath);
		previousSection = prev;
		nextSection = next;
	});
</script>

<div class="mt-10 border-t pt-6">
	<div class="flex items-center justify-between">
		<div>
			{#if previousSection}
				<a
					href={previousSection.path}
					class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 hover:underline"
				>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-4 w-4"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M15 19l-7-7 7-7"
						/>
					</svg>
					Previous: {previousSection.name}
				</a>
			{:else}
				<span class="text-gray-400">
					<span class="flex items-center gap-2">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-4 w-4"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M15 19l-7-7 7-7"
							/>
						</svg>
						First Section
					</span>
				</span>
			{/if}
		</div>

		<div>
			<a href="/" class="mx-4 text-gray-500 hover:text-gray-700 hover:underline"> Back to Home </a>
		</div>

		<div>
			{#if nextSection}
				<a
					href={nextSection.path}
					class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 hover:underline"
				>
					Next: {nextSection.name}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-4 w-4"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M9 5l7 7-7 7"
						/>
					</svg>
				</a>
			{:else}
				<span class="text-gray-400">
					<span class="flex items-center gap-2">
						Last Section
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-4 w-4"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 5l7 7-7 7"
							/>
						</svg>
					</span>
				</span>
			{/if}
		</div>
	</div>
</div>
