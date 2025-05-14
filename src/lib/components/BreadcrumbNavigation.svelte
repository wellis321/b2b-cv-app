<script lang="ts">
	import { page } from '$app/stores';
	import { getAdjacentSections, getCurrentSection, type CVSection } from '$lib/cv-sections';

	let currentSection = $state<CVSection | undefined>(undefined);
	let previousSection = $state<CVSection | null>(null);
	let nextSection = $state<CVSection | null>(null);

	// Get current path and adjacent sections from the page store
	$effect(() => {
		const path = $page.url.pathname;
		const currentPath = path.endsWith('/') ? path.slice(0, -1) : path;

		currentSection = getCurrentSection(currentPath);
		const { prev, next } = getAdjacentSections(currentPath);
		previousSection = prev;
		nextSection = next;
	});
</script>

<nav class="mb-6 border-b border-gray-200 py-3">
	<div class="flex items-center justify-between">
		<!-- Left: Previous link -->
		<div>
			{#if previousSection}
				<a
					href={previousSection.path}
					class="flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800"
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
					{previousSection.name}
				</a>
			{/if}
		</div>

		<!-- Center: Current section -->
		<div class="text-center">
			{#if currentSection}
				<span class="text-sm font-medium text-gray-700">
					{currentSection.name}
				</span>
			{/if}
		</div>

		<!-- Right: Next link -->
		<div>
			{#if nextSection}
				<a
					href={nextSection.path}
					class="flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800"
				>
					{nextSection.name}
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
			{/if}
		</div>
	</div>
</nav>
