<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { browser } from '$app/environment';
	import { CV_SECTIONS, sectionStatus, updateSectionStatus } from '$lib/cv-sections';
	import { session as authSession } from '$lib/stores/authStore';

	const session = $authSession;
	let errorMessage = $state('');

	// Error messages
	const errorMap = {
		session: 'There was a problem with your authentication session.',
		profile: 'Your user profile could not be found.',
		'create-profile': "We couldn't create your profile.",
		unexpected: 'An unexpected error occurred.',
		verification: "We couldn't verify your account.",
		auth: 'Authentication error. Please log in again.'
	};

	// Effect to update section status when session changes
	$effect(() => {
		if (browser && $authSession) {
			updateSectionStatus();
		}
	});

	// Handle error query parameter and initialize section status
	onMount(async () => {
		if (browser) {
			// Check for error parameters
			if ($page.url.searchParams.has('error')) {
				const errorCode = $page.url.searchParams.get('error');
				if (errorCode && errorCode in errorMap) {
					errorMessage = errorMap[errorCode as keyof typeof errorMap];

					// Clean up URL after displaying error
					const url = new URL(window.location.href);
					url.searchParams.delete('error');
					history.replaceState({}, document.title, url.toString());

					// Clear error after 5 seconds
					setTimeout(() => {
						errorMessage = '';
					}, 5000);
				}
			}

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

<div class="py-12">
	<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
		{#if errorMessage}
			<div class="mb-8 rounded-md bg-red-50 p-4">
				<div class="flex">
					<div class="flex-shrink-0">
						<svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
							<path
								fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
								clip-rule="evenodd"
							/>
						</svg>
					</div>
					<div class="ml-3">
						<p class="text-sm font-medium text-red-800">{errorMessage}</p>
					</div>
				</div>
			</div>
		{/if}

		<div class="text-center">
			<h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
				Build Your Professional CV
			</h1>
			<p
				class="mx-auto mt-3 max-w-md text-base text-gray-500 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl"
			>
				Create a comprehensive CV that showcases your professional journey. Get started by filling
				out each section below.
			</p>
		</div>

		<div class="mx-auto mt-12 grid max-w-lg gap-5 lg:max-w-none lg:grid-cols-3">
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
</div>
