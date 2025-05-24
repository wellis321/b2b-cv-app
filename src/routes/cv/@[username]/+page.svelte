<script lang="ts">
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { formatDate, generateCvPdf } from '$lib/pdfGenerator';
	import { cvStore } from '$lib/stores/cvDataStore';
	import ResponsibilitiesEditor from '../../work-experience/ResponsibilitiesEditor.svelte';
	import { getProxiedPhotoUrl, DEFAULT_PROFILE_PHOTO } from '$lib/photoUtils';
	import { session as authSession } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import { decodeHtmlEntities } from '$lib/validation';

	// Get username from the URL
	const username = $page.params.username;

	// Auth session for checking if user is viewing their own CV
	const currentSession = $authSession;

	// CV data from the store
	let cvData = $state($cvStore);
	// Extract the loading state as a separate variable to avoid type issues
	const { loading } = cvStore;
	let loadingState = $state($loading);
	let activeTab = $state('all');
	let windowWidth = $state<number>(0);
	let photoLoadError = $state(false);

	// Format profile photo URL or use default
	let photoUrl = $state(DEFAULT_PROFILE_PHOTO);

	// SEO settings - get from server data if available
	// Use any server data passed from +page.server.ts
	const { seo } = $page.data;
	let allowIndexing = $state(seo?.allowIndexing ?? false); // Default to not allow indexing

	// Update photoUrl when cvData.profile changes, but don't create a derived value
	// as this causes infinite loop
	let previousPhotoUrl = $state<string | null>(null);
	let initialPhotoUrlSet = $state(false);

	// Initial photo URL setup + updates
	$effect(() => {
		if (!cvData.profile) return;

		// Initial setup (runs only once)
		if (!initialPhotoUrlSet && cvData.profile.photo_url) {
			previousPhotoUrl = cvData.profile.photo_url;
			photoUrl = getProxiedPhotoUrl(cvData.profile.photo_url);
			initialPhotoUrlSet = true;
			return;
		}

		// Update photo URL only if it changed
		if (cvData.profile.photo_url && cvData.profile.photo_url !== previousPhotoUrl) {
			previousPhotoUrl = cvData.profile.photo_url;
			photoUrl = getProxiedPhotoUrl(cvData.profile.photo_url);
		} else if (!cvData.profile.photo_url && photoUrl !== DEFAULT_PROFILE_PHOTO) {
			photoUrl = DEFAULT_PROFILE_PHOTO;
		}
	});

	// Update window width on mount and resize
	onMount(() => {
		if (browser) {
			windowWidth = window.innerWidth;
			const handleResize = () => {
				windowWidth = window.innerWidth;
			};
			window.addEventListener('resize', handleResize);

			// Add debugging
			console.log('CV @[username] page mounted. Username:', username);

			// Check if Supabase is initialized properly
			if (!supabase) {
				console.error('Supabase client not initialized');
			} else {
				// Test Supabase connection with a simple query
				supabase
					.from('profiles')
					.select('count')
					.then(({ data, error }) => {
						if (error) {
							console.error('Error testing Supabase connection:', error);
						} else {
							console.log('Supabase connection test successful:', data);
						}
					});
			}

			// Load data by username
			if (username) {
				console.log('Loading CV data for username:', username);
				cvStore.loadByUsername(username);
			} else {
				console.error('No username provided in URL params');
			}

			return () => {
				window.removeEventListener('resize', handleResize);
			};
		}
	});

	// Interface for skill objects
	interface Skill {
		name: string;
		level?: string | null;
		category?: string | null;
	}

	// Track which work experiences have expanded responsibilities
	let expandedResponsibilities = $state<Record<string, boolean>>({});

	// Track which qualification equivalence entries have expanded supporting evidence
	let expandedSupportingEvidence = $state<Record<string, boolean>>({});

	// Toggle responsibilities visibility
	function toggleResponsibilities(workId: string) {
		expandedResponsibilities[workId] = !expandedResponsibilities[workId];
	}

	// Toggle supporting evidence visibility
	function toggleSupportingEvidence(qualId: string) {
		expandedSupportingEvidence[qualId] = !expandedSupportingEvidence[qualId];
	}

	// Predefined skill categories in preferred order
	const PREFERRED_CATEGORIES = [
		'Programming Languages',
		'Frameworks',
		'Technical Skills',
		'Frontend',
		'Backend',
		'Database',
		'Cloud Services',
		'DevOps',
		'Project Management',
		'Mobile',
		'Software',
		'Design',
		'Soft Skills',
		'Tools',
		'Other',
		'Uncategorized'
	];

	// Handle image error
	function handleImageError(event: Event) {
		const imgElement = event.target as HTMLImageElement;
		console.error('Failed to load image:', imgElement.src);
		photoLoadError = true;
		imgElement.style.display = 'none';
	}

	// Group skills by category
	let categorizedSkills = $state<{ category: string; skills: Skill[] }[]>([]);

	// Process skills by category - with safeguards to prevent infinite loops
	$effect(() => {
		// Skip if there are no skills or if CV data isn't loaded yet
		if (!cvData || !cvData.skills || !Array.isArray(cvData.skills)) {
			categorizedSkills = [];
			return;
		}

		// Skip unnecessary processing for empty arrays
		if (cvData.skills.length === 0) {
			categorizedSkills = [];
			return;
		}

		// Group skills by category
		const skillsByCategory = cvData.skills.reduce<Record<string, Skill[]>>((acc, skill) => {
			const category = skill.category || 'Other';
			if (!acc[category]) {
				acc[category] = [];
			}
			acc[category].push(skill as Skill);
			return acc;
		}, {});

		// Sort skills in each category
		Object.keys(skillsByCategory).forEach((category) => {
			skillsByCategory[category].sort((a: Skill, b: Skill) => a.name.localeCompare(b.name));
		});

		// Get all category names
		const categories = Object.keys(skillsByCategory);

		// Sort categories according to preferred order
		const orderedCategories = categories.sort((a, b) => {
			const indexA = PREFERRED_CATEGORIES.indexOf(a);
			const indexB = PREFERRED_CATEGORIES.indexOf(b);

			// If both categories are in preferred list, sort by preferred order
			if (indexA >= 0 && indexB >= 0) return indexA - indexB;

			// If only one is in preferred list, prioritize it
			if (indexA >= 0) return -1;
			if (indexB >= 0) return 1;

			// If neither is in preferred list, sort alphabetically
			return a.localeCompare(b);
		});

		// Update categorized skills - only if different from current state
		const newSkills: { category: string; skills: Skill[] }[] = [];
		orderedCategories.forEach((category) => {
			newSkills.push({
				category,
				skills: skillsByCategory[category]
			});
		});

		// Only update state if actually different
		const currentJson = JSON.stringify(categorizedSkills);
		const newJson = JSON.stringify(newSkills);
		if (currentJson !== newJson) {
			categorizedSkills = newSkills;
		}
	});

	// Set active tab function
	function setActiveTab(tab: string) {
		activeTab = tab;
	}

	// Subscribe to CV store changes - avoid infinite loops with careful comparison
	let previousStoreJson = $state('');
	$effect(() => {
		// Skip if there's no actual data change
		const storeValue = $cvStore;
		const storeJson = JSON.stringify(storeValue);

		// Only update if the store content has actually changed
		if (storeJson !== previousStoreJson) {
			previousStoreJson = storeJson;
			cvData = storeValue;
		}
	});

	// Handle print function
	async function handlePrint() {
		// Check if the current user is the owner of this CV
		if (currentSession && currentSession.user) {
			// Get the current user's profile
			const { data: myProfile } = await supabase
				.from('profiles')
				.select('username')
				.eq('id', currentSession.user.id)
				.single();

			// If this is the user's own CV, use the full preview page
			if (myProfile && myProfile.username === username) {
				window.location.href = `/preview-cv?username=${username}`;
				return;
			}
		}

		// For non-owners, just use browser print
		window.print();
	}

	// Function to generate and download a PDF of the CV
	async function downloadPdf() {
		if (!cvData) return;

		try {
			// Use default config for public PDF - this includes all sections
			await generateCvPdf(cvData);
		} catch (err) {
			console.error('Error generating PDF:', err);
			alert('Sorry, there was a problem generating the PDF. Please try again later.');
		}
	}

	// Update gradient style based on profile colors
	let headerGradientStyle = $state('');

	$effect(() => {
		if (cvData?.profile) {
			// Use hex values directly or set defaults if needed
			const fromColor = cvData.profile.cv_header_from_color?.startsWith('#')
				? cvData.profile.cv_header_from_color
				: '#4338ca'; // Default indigo-700

			const toColor = cvData.profile.cv_header_to_color?.startsWith('#')
				? cvData.profile.cv_header_to_color
				: '#7e22ce'; // Default purple-700

			headerGradientStyle = `background: linear-gradient(to right, ${fromColor}, ${toColor});`;
		}
	});

	// Initialize tabs
	$effect(() => {
		console.log('CV @[username] page mounted. Username:', username);
		if (cvData.qualificationEquivalence && cvData.qualificationEquivalence.length > 0) {
			console.log(
				'Qualification equivalence data:',
				JSON.stringify(cvData.qualificationEquivalence, null, 2)
			);
			console.log('First qualification ID:', cvData.qualificationEquivalence[0].id);
			console.log(
				'Has supporting_evidence_items?',
				cvData.qualificationEquivalence[0].supporting_evidence_items !== undefined
			);
			// Debug each qualification equivalence entry
			cvData.qualificationEquivalence.forEach((qual, index) => {
				console.log(`Qualification ${index + 1} ID:`, qual.id);
				console.log(`Qualification ${index + 1} level:`, qual.level);
				console.log(`Qualification ${index + 1} has evidence:`, !!qual.supporting_evidence_items);
				if (qual.supporting_evidence_items) {
					console.log(`Evidence items count:`, qual.supporting_evidence_items.length);
				}
			});
		}
	});
</script>

<svelte:head>
	{#if cvData.profile}
		<title>{decodeHtmlEntities(cvData.profile.full_name)}'s CV</title>
		<meta
			name="description"
			content="View {decodeHtmlEntities(cvData.profile.full_name)}'s professional CV"
		/>
		<!-- SEO/robots settings - directly use the server-provided data -->
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<!-- Open Graph meta tags for better social sharing -->
		<meta
			property="og:title"
			content="{decodeHtmlEntities(cvData.profile.full_name)}'s Professional CV"
		/>
		<meta
			property="og:description"
			content="View the professional CV and qualifications of {decodeHtmlEntities(
				cvData.profile.full_name
			)}"
		/>
		{#if cvData.profile.photo_url}
			<meta property="og:image" content={cvData.profile.photo_url} />
		{/if}
		<meta property="og:type" content="profile" />
		{#if browser}
			<meta property="og:url" content={window.location.href} />
		{/if}
		<!-- Twitter Card meta tags -->
		<meta name="twitter:card" content="summary" />
		<meta
			name="twitter:title"
			content="{decodeHtmlEntities(cvData.profile.full_name)}'s Professional CV"
		/>
		<meta
			name="twitter:description"
			content="View the professional CV and qualifications of {decodeHtmlEntities(
				cvData.profile.full_name
			)}"
		/>
		{#if cvData.profile.photo_url}
			<meta name="twitter:image" content={cvData.profile.photo_url} />
		{/if}
	{:else}
		<title>CV</title>
		<meta name="description" content="View this professional CV" />
		<!-- Always include robots meta tag even when no profile is loaded -->
		<meta name="robots" content="noindex, nofollow, noarchive" />
	{/if}
</svelte:head>

{#if loadingState.error}
	<div class="container mx-auto max-w-5xl px-4 py-8">
		<div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700 shadow-lg">
			Error: {loadingState.error}
			{#if browser}
				<br />
				<span class="text-sm text-gray-600">Debug: Username from URL: {username}</span>
			{/if}
		</div>
	</div>
{:else if loadingState.loading}
	<div class="flex h-screen items-center justify-center">
		<div class="text-center">
			<div
				class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-indigo-600"
			></div>
			<p class="text-xl text-gray-600">Loading CV for {username}...</p>
		</div>
	</div>
{:else if !cvData.profile}
	<div class="container mx-auto my-16 max-w-2xl px-4">
		<div class="rounded-lg bg-yellow-50 p-6 shadow-lg">
			<h2 class="mb-2 text-xl font-semibold text-yellow-800">CV Not Found</h2>
			<p class="text-yellow-700">This CV is not available or no longer exists.</p>
			{#if browser}
				<div class="mt-2 text-sm text-gray-600">
					Debug info: username={username}, loading={loadingState.loading}, cvData={JSON.stringify(
						cvData,
						null,
						2
					)}
				</div>
			{/if}
			<div class="mt-4">
				<a
					href="/"
					class="inline-block rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
					>Go Home</a
				>
			</div>
		</div>
	</div>
{:else}
	<!-- Main CV content when profile exists -->
	<div class="min-h-screen bg-white">
		<!-- Hero section with profile info -->
		<div style={headerGradientStyle} class="py-8 text-white shadow-lg">
			<div class="container mx-auto px-4 sm:px-6 lg:px-8">
				<div class="flex flex-col items-center gap-4 md:flex-row md:items-start md:gap-8">
					<div class="order-2 flex-1 md:order-1">
						<h1 class="text-4xl font-bold">
							{decodeHtmlEntities(cvData.profile.full_name) || 'Professional CV'}
						</h1>

						<div class="mt-6 space-y-2">
							{#if cvData.profile.email}
								<div class="flex items-center gap-2">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										class="h-5 w-5"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
										/>
										<path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
									</svg>
									<a href="mailto:{cvData.profile.email}" class="hover:underline"
										>{decodeHtmlEntities(cvData.profile.email)}</a
									>
								</div>
							{/if}

							{#if cvData.profile.phone}
								<div class="flex items-center gap-2">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										class="h-5 w-5"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
										/>
									</svg>
									<span>{decodeHtmlEntities(cvData.profile.phone)}</span>
								</div>
							{/if}

							{#if cvData.profile.location}
								<div class="flex items-center gap-2">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										class="h-5 w-5"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											fill-rule="evenodd"
											d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
											clip-rule="evenodd"
										/>
									</svg>
									<span>{decodeHtmlEntities(cvData.profile.location)}</span>
								</div>
							{/if}

							{#if cvData.profile.linkedin_url}
								<div class="flex items-center gap-2">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										class="h-5 w-5"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											fill-rule="evenodd"
											d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z"
											clip-rule="evenodd"
										/>
									</svg>
									<a
										href={cvData.profile.linkedin_url}
										target="_blank"
										rel="noopener noreferrer"
										class="hover:underline">LinkedIn Profile</a
									>
								</div>
							{/if}
						</div>

						{#if cvData.profile.bio}
							<div class="mt-4 rounded-lg bg-white/20 p-3">
								<p class="text-sm leading-relaxed">{decodeHtmlEntities(cvData.profile.bio)}</p>
							</div>
						{/if}
					</div>

					<!-- Profile photo -->
					<div class="order-1 flex items-center justify-center md:order-2">
						{#if cvData.profile.photo_url && !photoLoadError}
							<div
								class="my-2 h-40 w-40 overflow-hidden rounded-full border-4 border-white shadow-lg md:h-48 md:w-48"
							>
								<img
									src={photoUrl}
									alt={decodeHtmlEntities(cvData.profile.full_name)}
									class="h-full w-full object-cover"
									onerror={handleImageError}
								/>
							</div>
						{/if}
					</div>
				</div>
			</div>
		</div>

		<!-- Navigation tabs (only shown on small screens) -->
		<div class="sticky top-0 z-10 bg-white shadow-md md:hidden print:hidden">
			<div class="container mx-auto overflow-x-auto">
				<div class="flex px-4 py-2 whitespace-nowrap">
					<button
						class="px-4 py-2 text-sm font-medium {activeTab === 'all'
							? 'border-b-2 border-indigo-600 text-indigo-600'
							: 'text-gray-500 hover:text-gray-700'}"
						onclick={() => setActiveTab('all')}
					>
						All
					</button>
					{#if cvData.workExperiences && cvData.workExperiences.length > 0}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'work'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('work')}
						>
							Work
						</button>
					{/if}
					{#if cvData.skills && cvData.skills.length > 0}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'skills'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('skills')}
						>
							Skills
						</button>
					{/if}
					{#if cvData.education && cvData.education.length > 0}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'education'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('education')}
						>
							Education
						</button>
					{/if}
					{#if (cvData.projects && cvData.projects.length > 0) || (cvData.certifications && cvData.certifications.length > 0) || (cvData.memberships && cvData.memberships.length > 0) || (cvData.interests && cvData.interests.length > 0) || (cvData.qualificationEquivalence && cvData.qualificationEquivalence.length > 0)}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'more'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('more')}
						>
							More
						</button>
					{/if}
				</div>
			</div>
		</div>

		<!-- Main content area -->
		<main class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
			<div class="grid gap-6 md:grid-cols-3 lg:gap-8">
				<!-- Sidebar -->
				<aside class="md:col-span-1">
					<div class="space-y-8">
						<!-- Certifications (in sidebar on larger screens) -->
						{#if cvData.certifications && cvData.certifications.length > 0 && (activeTab === 'all' || activeTab === 'more' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Certifications
								</h2>

								<div class="mt-4 space-y-4">
									{#each cvData.certifications as cert}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<h3 class="font-semibold text-gray-800">{decodeHtmlEntities(cert.name)}</h3>
											{#if cert.issuer}
												<p class="text-gray-700">{decodeHtmlEntities(cert.issuer)}</p>
											{/if}
											{#if cert.date_obtained}
												<p class="mt-1 text-sm text-gray-500">
													{formatDate(cert.date_obtained)}
													{#if cert.expiry_date}
														- Expires: {formatDate(cert.expiry_date)}
													{/if}
												</p>
											{/if}
											{#if cert.description}
												<p class="mt-2 text-sm text-gray-600">
													{decodeHtmlEntities(cert.description)}
												</p>
											{/if}
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Education section (visible in sidebar on larger screens) -->
						{#if cvData.education && cvData.education.length > 0 && (activeTab === 'all' || activeTab === 'education' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md md:block print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Education
								</h2>

								<div class="mt-4 space-y-4">
									{#each cvData.education as edu}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<h3 class="font-semibold text-gray-800">
												{decodeHtmlEntities(edu.institution)}
											</h3>
											<p class="text-gray-700">
												{decodeHtmlEntities(edu.qualification || edu.degree)}
											</p>
											{#if edu.field_of_study}
												<p class="text-gray-600">{decodeHtmlEntities(edu.field_of_study)}</p>
											{/if}
											{#if edu.start_date}
												<p class="mt-1 text-sm text-gray-500">
													{formatDate(edu.start_date)} - {edu.end_date
														? formatDate(edu.end_date)
														: 'Present'}
												</p>
											{/if}
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Interests (in sidebar on larger screens) -->
						{#if cvData.interests && cvData.interests.length > 0 && (activeTab === 'all' || activeTab === 'more' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Interests & Activities
								</h2>

								<div class="mt-4 space-y-4">
									{#each cvData.interests as interest}
										<div class="pb-2 last:pb-0">
											<h3 class="font-semibold text-gray-800">
												{decodeHtmlEntities(interest.name)}
											</h3>
											{#if interest.description}
												<p class="mt-1 text-sm text-gray-600">
													{decodeHtmlEntities(interest.description)}
												</p>
											{/if}
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Skills section (always visible on larger screens) -->
						{#if cvData.skills && cvData.skills.length > 0 && (activeTab === 'all' || activeTab === 'skills' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Skills
								</h2>

								{#if categorizedSkills.length > 0}
									<div class="mt-4 space-y-5">
										{#each categorizedSkills as category}
											<div>
												<h3 class="mb-2 font-semibold text-gray-700">
													{decodeHtmlEntities(category.category)}
												</h3>
												<div class="flex flex-wrap gap-2">
													{#each category.skills as skill}
														<span
															class="rounded-full bg-indigo-100 px-3 py-1 text-sm text-indigo-800"
														>
															{decodeHtmlEntities(skill.name)}
															{#if skill.level}
																<span class="ml-1 text-indigo-600"
																	>({decodeHtmlEntities(skill.level)})</span
																>
															{/if}
														</span>
													{/each}
												</div>
											</div>
										{/each}
									</div>
								{:else}
									<div class="mt-4 flex flex-wrap gap-2">
										{#each cvData.skills as skill}
											<span class="rounded-full bg-indigo-100 px-3 py-1 text-sm text-indigo-800">
												{decodeHtmlEntities(skill.name)}
												{#if skill.level}
													<span class="ml-1 text-indigo-600"
														>({decodeHtmlEntities(skill.level)})</span
													>
												{/if}
											</span>
										{/each}
									</div>
								{/if}
							</section>
						{/if}
					</div>
				</aside>

				<!-- Main content -->
				<div class="md:col-span-2">
					<div class="space-y-8">
						<!-- Work Experience section -->
						{#if cvData.workExperiences && cvData.workExperiences.length > 0 && (activeTab === 'all' || activeTab === 'work')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Work Experience
								</h2>

								<div class="mt-4 space-y-6">
									{#each cvData.workExperiences as work}
										<div class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
											<header class="mb-2">
												<h3 class="text-lg font-semibold text-gray-800">
													{decodeHtmlEntities(work.position)}
												</h3>
												<div class="text-md font-medium text-gray-700">
													{decodeHtmlEntities(work.company_name)}
												</div>
												<p class="text-sm text-gray-500">
													{formatDate(work.start_date)} - {work.end_date
														? formatDate(work.end_date)
														: 'Present'}
												</p>
											</header>

											{#if work.description}
												<p class="my-2 text-gray-600">{decodeHtmlEntities(work.description)}</p>
											{/if}

											<!-- Responsibilities section with better visibility -->
											{#if work.responsibilities && Array.isArray(work.responsibilities) && work.responsibilities.length > 0}
												<div class="mt-3">
													<button
														onclick={() => toggleResponsibilities(work.id)}
														class="inline-flex items-center rounded bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
													>
														<svg
															xmlns="http://www.w3.org/2000/svg"
															class="mr-1.5 h-4 w-4"
															viewBox="0 0 20 20"
															fill="currentColor"
														>
															{#if expandedResponsibilities[work.id]}
																<path
																	fill-rule="evenodd"
																	d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
																	clip-rule="evenodd"
																/>
															{:else}
																<path
																	fill-rule="evenodd"
																	d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
																	clip-rule="evenodd"
																/>
															{/if}
														</svg>
														{expandedResponsibilities[work.id] ? 'Hide' : 'View'} Responsibilities
													</button>

													{#if expandedResponsibilities[work.id]}
														<div class="mt-2 pl-2">
															<div class="pl-2">
																<ResponsibilitiesEditor
																	responsibilities={work.responsibilities}
																	readOnly={true}
																/>
															</div>
														</div>
													{/if}
												</div>
											{/if}
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Professional Qualification Equivalence section -->
						{#if cvData.qualificationEquivalence && cvData.qualificationEquivalence.length > 0 && (activeTab === 'all' || activeTab === 'more')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Professional Qualification Equivalence
								</h2>

								<div class="mt-4 space-y-4">
									{#each cvData.qualificationEquivalence as qualification}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<h3 class="font-semibold text-gray-800">
												{decodeHtmlEntities(qualification.qualification || qualification.level)}
											</h3>
											{#if qualification.equivalent_to && qualification.equivalent_to !== 'NULL'}
												<h4 class="text-gray-700">
													Equivalent to: {decodeHtmlEntities(qualification.equivalent_to)}
												</h4>
											{/if}
											{#if qualification.description}
												<div class="mt-2 text-gray-700">
													<p class="whitespace-pre-line">
														{decodeHtmlEntities(qualification.description)}
													</p>
												</div>
											{/if}

											{#if qualification.supporting_evidence_items && qualification.supporting_evidence_items.length > 0}
												<div class="mt-3">
													<button
														onclick={() => toggleSupportingEvidence(qualification.id)}
														class="inline-flex items-center rounded bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
													>
														<svg
															xmlns="http://www.w3.org/2000/svg"
															class="mr-1.5 h-4 w-4"
															viewBox="0 0 20 20"
															fill="currentColor"
														>
															{#if expandedSupportingEvidence[qualification.id]}
																<path
																	fill-rule="evenodd"
																	d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
																	clip-rule="evenodd"
																/>
															{:else}
																<path
																	fill-rule="evenodd"
																	d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
																	clip-rule="evenodd"
																/>
															{/if}
														</svg>
														{expandedSupportingEvidence[qualification.id] ? 'Hide' : 'View'} Supporting
														Evidence
													</button>

													{#if expandedSupportingEvidence[qualification.id]}
														<div class="mt-2 pl-2">
															<div class="rounded-md bg-gray-50 p-3">
																<ul class="list-disc space-y-2 pl-5">
																	{#each qualification.supporting_evidence_items as evidence}
																		<li class="text-gray-700">
																			{decodeHtmlEntities(evidence.content)}
																		</li>
																	{/each}
																</ul>
															</div>
														</div>
													{/if}
												</div>
											{/if}
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Projects section -->
						{#if cvData.projects && cvData.projects.length > 0 && (activeTab === 'all' || activeTab === 'more')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Projects
								</h2>

								<div class="mt-6 space-y-6">
									{#each cvData.projects as project}
										<div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
											<div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
												<div class="flex flex-wrap items-start justify-between gap-2">
													<h3 class="font-semibold text-gray-800">
														{decodeHtmlEntities(project.title)}
													</h3>
													{#if project.start_date}
														<p class="text-sm text-gray-500">
															{formatDate(project.start_date)} - {project.end_date
																? formatDate(project.end_date)
																: 'Present'}
														</p>
													{/if}
												</div>
											</div>

											<div class="p-4">
												{#if project.description}
													<p class="text-sm text-gray-700">
														{decodeHtmlEntities(project.description)}
													</p>
												{/if}

												{#if project.url}
													<div class="mt-3">
														<a
															href={project.url}
															target="_blank"
															rel="noopener noreferrer"
															class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline"
														>
															<svg
																xmlns="http://www.w3.org/2000/svg"
																class="h-4 w-4"
																viewBox="0 0 20 20"
																fill="currentColor"
															>
																<path
																	d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"
																/>
																<path
																	d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5z"
																/>
															</svg>
															View Project
														</a>
													</div>
												{/if}
											</div>
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Memberships section -->
						{#if cvData.memberships && cvData.memberships.length > 0 && (activeTab === 'all' || activeTab === 'more')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Professional Memberships
								</h2>

								<div class="mt-4 space-y-4">
									{#each cvData.memberships as membership}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<div class="flex flex-wrap items-start justify-between gap-2">
												<div>
													<h3 class="font-semibold text-gray-800">
														{decodeHtmlEntities(membership.organisation)}
													</h3>
													{#if membership.role}
														<p class="text-gray-700">{decodeHtmlEntities(membership.role)}</p>
													{/if}
												</div>
												{#if membership.start_date}
													<div class="text-sm text-gray-500">
														{formatDate(membership.start_date)} - {membership.end_date
															? formatDate(membership.end_date)
															: 'Present'}
													</div>
												{/if}
											</div>
										</div>
									{/each}
								</div>
							</section>
						{/if}
					</div>
				</div>
			</div>
		</main>

		<!-- Footer -->
		<footer class="bg-gray-800 py-6 text-center text-white print:hidden">
			<div class="container mx-auto px-4 sm:px-6 lg:px-8">
				<p class="text-gray-300">CV created with CV App by William and Max Ellis</p>
				<div class="mt-3 flex justify-center space-x-4">
					<button
						onclick={downloadPdf}
						class="inline-flex items-center rounded bg-indigo-500 px-3 py-1.5 text-sm text-white hover:bg-indigo-600"
					>
						<svg
							class="mr-1.5 h-4 w-4"
							fill="currentColor"
							viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg"
						>
							<path
								fill-rule="evenodd"
								d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
								clip-rule="evenodd"
							></path>
						</svg>
						Download PDF
					</button>
					<a href="/" class="text-indigo-300 hover:text-indigo-200 hover:underline">
						Return to CV App
					</a>
				</div>
			</div>
		</footer>
	</div>
{/if}
