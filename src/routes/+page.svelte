<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { browser } from '$app/environment';

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

	// Handle error query parameter
	onMount(() => {
		if (browser && $page.url.searchParams.has('error')) {
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
	});

	const sections = [
		{
			title: 'Profile',
			href: '/profile',
			description: 'Your personal information and contact details'
		},
		{
			title: 'Work Experience',
			href: '/work-experience',
			description: 'Your professional work history'
		},
		{ title: 'Projects', href: '/projects', description: 'Notable projects and achievements' },
		{ title: 'Education', href: '/education', description: 'Your academic background' },
		{ title: 'Skills', href: '/skills', description: 'Your technical and soft skills' },
		{
			title: 'Certifications',
			href: '/certifications',
			description: 'Professional certifications and qualifications'
		},
		{
			title: 'Professional Memberships',
			href: '/memberships',
			description: 'Professional organisations and memberships'
		},
		{ title: 'Interests', href: '/interests', description: 'Your hobbies and interests' }
	];

	console.log('ALL ENV:', import.meta.env);
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
			{#each sections as section}
				<div class="flex flex-col overflow-hidden rounded-lg shadow-lg">
					<div class="flex flex-1 flex-col justify-between bg-white p-6">
						<div class="flex-1">
							<a href={section.href} class="mt-2 block">
								<p class="text-xl font-semibold text-gray-900">{section.title}</p>
								<p class="mt-3 text-base text-gray-500">{section.description}</p>
							</a>
						</div>
					</div>
				</div>
			{/each}
		</div>
	</div>
</div>
