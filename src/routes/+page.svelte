<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { browser } from '$app/environment';
	import { CV_SECTIONS, sectionStatus, updateSectionStatus } from '$lib/cv-sections';
	import { session as authSession } from '$lib/stores/authStore';
	import { goto } from '$app/navigation';
	import AuthForm from '$lib/auth-form.svelte';

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
			// Check if user just logged in and scroll to top if so
			const justAuthenticated = sessionStorage.getItem('just_authenticated');
			if (justAuthenticated) {
				// Clear the flag
				sessionStorage.removeItem('just_authenticated');
				// Scroll to top after a brief delay to ensure the page is rendered
				setTimeout(() => {
					window.scrollTo({ top: 0, behavior: 'smooth' });
				}, 100);
			}

			// Clear any refresh flags that might be causing issues
			sessionStorage.removeItem('home_page_refreshed');

			// Force page refresh if we're on the home page with an active session
			// This helps ensure we see the CV sections properly
			// DISABLED: This was causing issues with navigation
			/*
			if (session && window.location.pathname === '/') {
				console.log('Refreshing home page with active session');
				// Set a flag to prevent infinite refresh
				const hasRefreshed = sessionStorage.getItem('home_page_refreshed');
				if (!hasRefreshed) {
					sessionStorage.setItem('home_page_refreshed', 'true');
					// Use timeout to ensure the page has time to render first
					setTimeout(() => {
						window.location.reload();
					}, 500);
				} else {
					// Clear the flag after a while to allow future refreshes
					setTimeout(() => {
						sessionStorage.removeItem('home_page_refreshed');
					}, 5000);
				}
			}
			*/

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

	// Features of the CV app
	const features = [
		{
			title: 'Dynamic Online CV',
			description:
				'Create a professional CV that updates in real-time and can be shared as a simple link.',
			icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'
		},
		{
			title: 'Comprehensive Sections',
			description:
				'Include everything from work experience to professional memberships in your CV.',
			icon: 'M4 6h16M4 10h16M4 14h16M4 18h16'
		},
		{
			title: 'Print & Share',
			description: 'Download as PDF or share a unique link with employers and your network.',
			icon: 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
		}
	];

	// Testimonials
	const testimonials = [
		{
			quote:
				'This CV builder helped me land my dream job. The online link was a game-changer during my application process.',
			author: 'Alex Johnson',
			role: 'Software Developer'
		},
		{
			quote:
				'I love how easy it is to update my CV in real-time. My profile stays current without having to send new PDFs.',
			author: 'Sarah Williams',
			role: 'Marketing Manager'
		}
	];

	function startBuilding() {
		if (session) {
			goto('/profile');
		} else {
			// Scroll to auth form if not logged in
			const authForm = document.querySelector('#auth-section');
			if (authForm) {
				authForm.scrollIntoView({ behavior: 'smooth' });
			}
		}
	}
</script>

{#if session}
	<div class="py-6">
		<!-- Error Message -->
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

		<!-- Dashboard for logged in users -->
		<div class="mb-10 text-center">
			<h1 class="text-3xl font-bold text-gray-900">Your CV Builder Dashboard</h1>
			<p class="mt-2 text-lg text-gray-600">
				Complete each section to create your professional CV.
			</p>
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
								<p class="text-xl font-semibold text-gray-900 group-hover:text-blue-600">
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
										class="inline-flex items-center text-sm font-medium text-blue-600 group-hover:underline"
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
{:else}
	<!-- Marketing Page for non-logged in users -->
	<div class="bg-white">
		<!-- Hero Section -->
		<div class="relative overflow-hidden">
			<div class="pt-16 pb-80 sm:pt-24 sm:pb-40 lg:pt-40 lg:pb-48">
				<div class="relative mx-auto max-w-7xl px-4 sm:static sm:px-6 lg:px-8">
					<div class="sm:max-w-lg">
						<h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
							Your CV, Reimagined
						</h1>
						<p class="mt-4 text-xl text-gray-500">
							Create a professional CV that stands out, updates in real-time, and can be shared as a
							simple link.
						</p>
						<div class="mt-10">
							<button
								onclick={startBuilding}
								class="inline-block rounded-md border border-transparent bg-blue-600 px-8 py-3 text-center font-medium text-white hover:bg-blue-700"
							>
								Start Building Your CV
							</button>
						</div>
					</div>
					<div>
						<div class="mt-10">
							<!-- Decorative image grid -->
							<div
								aria-hidden="true"
								class="pointer-events-none lg:absolute lg:inset-y-0 lg:mx-auto lg:w-full lg:max-w-7xl"
							>
								<div
									class="absolute transform sm:top-0 sm:left-1/2 sm:translate-x-8 lg:top-1/2 lg:left-1/2 lg:translate-x-8 lg:-translate-y-1/2"
								>
									<div class="flex items-center space-x-6 lg:space-x-8">
										<div class="grid flex-shrink-0 grid-cols-1 gap-y-6 lg:gap-y-8">
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-200 to-blue-300"
												></div>
											</div>
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-300 to-blue-400"
												></div>
											</div>
										</div>
										<div class="grid flex-shrink-0 grid-cols-1 gap-y-6 lg:gap-y-8">
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-400 to-blue-500"
												></div>
											</div>
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-500 to-blue-600"
												></div>
											</div>
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-600 to-blue-700"
												></div>
											</div>
										</div>
										<div class="grid flex-shrink-0 grid-cols-1 gap-y-6 lg:gap-y-8">
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-700 to-blue-800"
												></div>
											</div>
											<div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
												<div
													class="h-full w-full bg-gradient-to-br from-blue-800 to-blue-900"
												></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Feature Section -->
		<div class="bg-gray-50 py-12 sm:py-16">
			<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
				<div class="lg:text-center">
					<h2 class="text-lg font-semibold text-blue-600">CV Builder</h2>
					<p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
						A better way to showcase your professional journey
					</p>
					<p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
						Build a comprehensive CV that stands out from the crowd with our intuitive tools and
						unique sharing features.
					</p>
				</div>

				<div class="mt-10">
					<dl class="space-y-10 md:grid md:grid-cols-3 md:space-y-0 md:gap-x-8 md:gap-y-10">
						{#each features as feature}
							<div class="relative">
								<dt>
									<div
										class="absolute flex h-12 w-12 items-center justify-center rounded-md bg-blue-500 text-white"
									>
										<svg
											class="h-6 w-6"
											xmlns="http://www.w3.org/2000/svg"
											fill="none"
											viewBox="0 0 24 24"
											stroke="currentColor"
										>
											<path
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												d={feature.icon}
											/>
										</svg>
									</div>
									<p class="ml-16 text-lg leading-6 font-medium text-gray-900">{feature.title}</p>
								</dt>
								<dd class="mt-2 ml-16 text-base text-gray-500">{feature.description}</dd>
							</div>
						{/each}
					</dl>
				</div>
			</div>
		</div>

		<!-- How It Works Section -->
		<div class="bg-white py-12 sm:py-16">
			<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
				<div class="lg:text-center">
					<h2 class="text-lg font-semibold text-blue-600">Simple Process</h2>
					<p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
						How Our CV Builder Works
					</p>
				</div>

				<div class="mt-10">
					<div class="relative">
						<div class="absolute inset-0 flex items-center" aria-hidden="true">
							<div class="w-full border-t border-gray-300"></div>
						</div>
						<div class="relative flex justify-center">
							<span class="bg-white px-3 text-lg font-medium text-gray-900">Three simple steps</span
							>
						</div>
					</div>

					<div class="mt-6 grid grid-cols-1 gap-10 sm:grid-cols-3">
						<div class="text-center">
							<div
								class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600"
							>
								<span class="text-xl font-bold">1</span>
							</div>
							<h3 class="mt-3 text-lg font-medium text-gray-900">Create Your Profile</h3>
							<p class="mt-2 text-base text-gray-500">
								Sign up and fill in your personal information and professional details.
							</p>
						</div>

						<div class="text-center">
							<div
								class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600"
							>
								<span class="text-xl font-bold">2</span>
							</div>
							<h3 class="mt-3 text-lg font-medium text-gray-900">Build Your CV</h3>
							<p class="mt-2 text-base text-gray-500">
								Add your work experience, education, skills and other professional achievements.
							</p>
						</div>

						<div class="text-center">
							<div
								class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600"
							>
								<span class="text-xl font-bold">3</span>
							</div>
							<h3 class="mt-3 text-lg font-medium text-gray-900">Share & Download</h3>
							<p class="mt-2 text-base text-gray-500">
								Get a unique link to share with employers or download as a professional PDF.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Testimonials Section -->
		<div class="bg-gray-50 py-12 sm:py-16">
			<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
				<div class="lg:text-center">
					<h2 class="text-lg font-semibold text-blue-600">Testimonials</h2>
					<p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
						What Our Users Say
					</p>
				</div>

				<div class="mt-10 grid gap-8 sm:grid-cols-2">
					{#each testimonials as testimonial}
						<div class="rounded-lg bg-white p-6 shadow-lg">
							<div class="flex items-center">
								<svg class="h-8 w-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
									<path
										d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"
									/>
								</svg>
								<div class="ml-4">
									<p class="text-base font-medium text-gray-900">{testimonial.author}</p>
									<p class="text-sm text-gray-500">{testimonial.role}</p>
								</div>
							</div>
							<p class="mt-4 text-base text-gray-500">"{testimonial.quote}"</p>
						</div>
					{/each}
				</div>
			</div>
		</div>

		<!-- Simple Job Tracker Promotion -->
		<div class="bg-gradient-to-r from-blue-50 to-green-50 py-12 sm:py-16">
			<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
				<div class="overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-xl">
					<div class="grid lg:grid-cols-2">
						<!-- Left side: Content -->
						<div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
							<div class="mb-6 flex items-center gap-3">
								<div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
									<svg
										class="h-7 w-7 text-green-600"
										fill="none"
										stroke="currentColor"
										viewBox="0 0 24 24"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
										/>
									</svg>
								</div>
								<span
									class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800"
									>Job Search Tools</span
								>
							</div>
							<h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
								Land Your Dream Job
							</h2>
							<p class="mt-4 text-lg text-gray-600">
								Now that you've built your professional CV, track your job applications with
								<strong>Simple Job Tracker</strong>. Never lose track of where you've applied, stay
								on top of follow-ups, and land your next role.
							</p>
							<ul class="mt-6 space-y-3 text-gray-600">
								<li class="flex items-start">
									<svg
										class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500"
										fill="currentColor"
										viewBox="0 0 20 20"
									>
										<path
											fill-rule="evenodd"
											d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
											clip-rule="evenodd"
										/>
									</svg>
									<span>Track all your job applications in one place</span>
								</li>
								<li class="flex items-start">
									<svg
										class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500"
										fill="currentColor"
										viewBox="0 0 20 20"
									>
										<path
											fill-rule="evenodd"
											d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
											clip-rule="evenodd"
										/>
									</svg>
									<span>Attach resumes and cover letters to each application</span>
								</li>
								<li class="flex items-start">
									<svg
										class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500"
										fill="currentColor"
										viewBox="0 0 20 20"
									>
										<path
											fill-rule="evenodd"
											d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
											clip-rule="evenodd"
										/>
									</svg>
									<span>Monitor your progress from application to offer</span>
								</li>
							</ul>
							<div class="mt-8">
								<a
									href="https://simple-job-tracker.com/"
									target="_blank"
									rel="noopener noreferrer"
									class="inline-flex items-center rounded-md bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
								>
									Visit Simple Job Tracker
									<svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
										/>
									</svg>
								</a>
							</div>
						</div>

						<!-- Right side: Visual -->
						<div
							class="flex items-center justify-center bg-gradient-to-br from-green-500 to-green-600 px-6 py-12 sm:px-10 sm:py-16 lg:px-12"
						>
							<div class="text-center">
								<div
									class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white/20 shadow-lg backdrop-blur-sm"
								>
									<svg
										class="h-12 w-12 text-white"
										fill="none"
										stroke="currentColor"
										viewBox="0 0 24 24"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
										/>
									</svg>
								</div>
								<h3 class="text-2xl font-bold text-white">Simple Job Tracker</h3>
								<p class="mt-2 text-green-100">Organise • Track • Follow Up</p>
								<div class="mt-6 text-sm text-white">
									<p>Free while you're searching</p>
									<p>£9.99/year once employed</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Call to Action Section -->
		<div class="bg-blue-700" id="auth-section">
			<div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
				<h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
					<span class="block">Ready to build your professional CV?</span>
					<span class="block">Start for free today</span>
				</h2>
				<p class="mt-4 mb-8 text-lg leading-6 text-blue-200">
					Create a CV that gets you noticed with our easy-to-use builder. Share your professional
					story with a unique link or downloadable PDF.
				</p>

				<!-- Auth Form embedded directly on the landing page -->
				<div class="rounded-lg bg-white p-6 shadow-lg">
					<AuthForm redirectTo="/" />
				</div>
			</div>
		</div>
	</div>
{/if}
