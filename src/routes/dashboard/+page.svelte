<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { browser } from '$app/environment';
	import { CV_SECTIONS, sectionStatus, updateSectionStatus } from '$lib/cv-sections';
	import { session as authSession } from '$lib/stores/authStore';

	const session = $authSession;
	let errorMessage = $state('');

	// Effect to update section status when session changes
	$effect(() => {
		if (browser && $authSession) {
			updateSectionStatus();
		}
	});

	// Handle error query parameter and initialize section status
	onMount(async () => {
		if (browser) {
			// Update section status
			if (session) {
				await updateSectionStatus();
			}
		}
	});

	// Get status indicator based on section completion
	function getStatusIndicator(sectionId: string) {
		const status = $sectionStatus[sectionId];
		if (!status || !status.isComplete) {
			return {
				icon: '○',
				text: 'Not started',
				className: 'text-gray-300'
			};
		}

		return {
			icon: '●',
			text: `${status.count} item${status.count !== 1 ? 's' : ''}`,
			className: 'text-green-500'
		};
	}
</script>

<div class="py-6">
	<!-- Dashboard for logged in users -->
	<div class="mb-10 text-center">
		<h1 class="text-3xl font-bold text-gray-900">Your CV Builder Dashboard</h1>
		<p class="mt-2 text-lg text-gray-600">Complete each section to create your professional CV.</p>
	</div>

	<div class="mx-auto grid max-w-7xl gap-5 sm:grid-cols-2 lg:grid-cols-3">
		{#each CV_SECTIONS as section}
			{@const status = getStatusIndicator(section.id)}
			<a
				href={section.path}
				class="group flex flex-col overflow-hidden rounded-lg shadow-lg transition-all duration-200 hover:bg-gray-50 hover:shadow-xl"
			>
				<div class="flex flex-1 flex-col justify-between bg-white p-6 group-hover:bg-gray-50">
					<div class="flex-1">
						<div class="flex justify-between">
							<p class="text-xl font-semibold text-gray-900 group-hover:text-indigo-600">
								{section.name}
							</p>
							<span class={`text-lg font-bold ${status.className}`} title={status.text}>
								{status.icon}
							</span>
						</div>
						<p class="mt-3 text-base text-gray-500">{section.description}</p>
						<div class="mt-4">
							{#if $sectionStatus[section.id]?.isComplete}
								<span
									class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
								>
									{$sectionStatus[section.id]?.count} entr{$sectionStatus[section.id]?.count !== 1
										? 'ies'
										: 'y'} added
								</span>
							{:else}
								<span
									class="inline-flex items-center text-sm font-medium text-indigo-600 group-hover:underline"
								>
									Add information
									<svg
										class="ml-1 h-4 w-4"
										fill="currentColor"
										viewBox="0 0 20 20"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path
											fill-rule="evenodd"
											d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
											clip-rule="evenodd"
										/>
									</svg>
								</span>
							{/if}
						</div>
					</div>
				</div>
			</a>
		{/each}
	</div>
</div>
