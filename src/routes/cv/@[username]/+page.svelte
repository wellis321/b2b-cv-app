<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { formatDate } from '$lib/pdfGenerator';

	// Get CV data from server load
	let { data } = $props();

	// Destructure data for easier access
	const {
		profile,
		workExperiences,
		projects,
		skills,
		education,
		certifications,
		memberships,
		interests,
		qualificationEquivalence
	} = data;

	// State variables
	let error = $state<string | null>(null);
	let loading = $state<boolean>(false);
	let activeTab = $state<string>('all');
	let windowWidth = $state<number>(0);

	// Format profile photo URL or use default
	const defaultPhotoUrl = '/images/default-profile.svg';
	const photoUrl = profile?.photo_url || defaultPhotoUrl;

	// Update window width on mount and resize
	onMount(() => {
		if (browser) {
			windowWidth = window.innerWidth;
			const handleResize = () => {
				windowWidth = window.innerWidth;
			};
			window.addEventListener('resize', handleResize);
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

	// Handle image error
	function handleImageError(event: Event) {
		const imgElement = event.target as HTMLImageElement;
		imgElement.onerror = null;
		imgElement.src = defaultPhotoUrl;
	}

	// Group skills by category
	let categorizedSkills = $state<{ category: string; skills: Skill[] }[]>([]);

	// Process skills by category
	$effect(() => {
		if (skills && skills.length > 0) {
			// Group skills by category
			const skillsByCategory = skills.reduce<Record<string, Skill[]>>((acc, skill) => {
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

			// Update categorized skills
			categorizedSkills = [];
			Object.keys(skillsByCategory)
				.sort()
				.forEach((category) => {
					categorizedSkills.push({
						category,
						skills: skillsByCategory[category]
					});
				});
		}
	});

	// Set active tab function
	function setActiveTab(tab: string) {
		activeTab = tab;
	}

	// Debug variables
	$effect(() => {
		if (browser) {
			console.log('Profile:', profile);
			console.log('Work Experiences:', workExperiences);
			console.log('Skills:', skills);
			console.log('Education:', education);
		}
	});
</script>

<svelte:head>
	{#if profile}
		<title>{profile.full_name}'s CV</title>
		<meta name="description" content="View {profile.full_name}'s professional CV" />
		<!-- Open Graph meta tags for better social sharing -->
		<meta property="og:title" content="{profile.full_name}'s Professional CV" />
		<meta
			property="og:description"
			content="View the professional CV and qualifications of {profile.full_name}"
		/>
		{#if profile.photo_url}
			<meta property="og:image" content={profile.photo_url} />
		{/if}
		<meta property="og:type" content="profile" />
		{#if browser}
			<meta property="og:url" content={window.location.href} />
		{/if}
		<!-- Twitter Card meta tags -->
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:title" content="{profile.full_name}'s Professional CV" />
		<meta
			name="twitter:description"
			content="View the professional CV and qualifications of {profile.full_name}"
		/>
		{#if profile.photo_url}
			<meta name="twitter:image" content={profile.photo_url} />
		{/if}
	{:else}
		<title>CV</title>
		<meta name="description" content="View this professional CV" />
	{/if}
</svelte:head>

{#if error}
	<div class="container mx-auto max-w-5xl px-4 py-8">
		<div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700 shadow-lg">{error}</div>
	</div>
{:else if loading}
	<div class="flex h-screen items-center justify-center">
		<div class="text-center">
			<div
				class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-indigo-600"
			></div>
			<p class="text-xl text-gray-600">Loading CV...</p>
		</div>
	</div>
{:else if !profile}
	<div class="container mx-auto my-16 max-w-2xl px-4">
		<div class="rounded-lg bg-yellow-50 p-6 shadow-lg">
			<h2 class="mb-2 text-xl font-semibold text-yellow-800">CV Not Found</h2>
			<p class="text-yellow-700">This CV is not available or no longer exists.</p>
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
	<div class="min-h-screen bg-gray-50">
		<!-- Hero section with profile info -->
		<div class="bg-gradient-to-r from-indigo-700 to-purple-700 px-4 py-16 text-white shadow-lg">
			<div class="container mx-auto max-w-5xl">
				<div class="flex flex-col items-center gap-8 md:flex-row md:items-start md:gap-12">
					<div class="order-2 flex-1 md:order-1">
						<h1 class="text-4xl font-bold">{profile.full_name || 'Professional CV'}</h1>

						{#if profile.username}
							<p class="mt-2 text-indigo-200">@{profile.username}</p>
						{/if}

						<div class="mt-6 space-y-2">
							{#if profile.location}
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
									<span>{profile.location}</span>
								</div>
							{/if}

							{#if profile.email}
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
									<a href="mailto:{profile.email}" class="hover:underline">{profile.email}</a>
								</div>
							{/if}

							{#if profile.phone}
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
									<span>{profile.phone}</span>
								</div>
							{/if}
						</div>

						<div class="mt-8 print:hidden">
							<button
								onclick={() => window.print()}
								class="inline-flex items-center gap-2 rounded bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50"
							>
								<svg
									xmlns="http://www.w3.org/2000/svg"
									class="h-4 w-4"
									viewBox="0 0 20 20"
									fill="currentColor"
								>
									<path
										fill-rule="evenodd"
										d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z"
										clip-rule="evenodd"
									/>
								</svg>
								Print CV
							</button>
						</div>
					</div>

					<!-- Profile photo -->
					<div class="order-1 md:order-2">
						<div
							class="h-40 w-40 overflow-hidden rounded-full border-4 border-white shadow-xl md:h-48 md:w-48"
						>
							<img
								src={photoUrl}
								alt={profile.full_name || 'Profile'}
								class="h-full w-full object-cover"
								onerror={(e) => handleImageError(e)}
							/>
						</div>
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
					{#if workExperiences && workExperiences.length > 0}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'work'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('work')}
						>
							Work
						</button>
					{/if}
					{#if skills && skills.length > 0}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'skills'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('skills')}
						>
							Skills
						</button>
					{/if}
					{#if education && education.length > 0}
						<button
							class="px-4 py-2 text-sm font-medium {activeTab === 'education'
								? 'border-b-2 border-indigo-600 text-indigo-600'
								: 'text-gray-500 hover:text-gray-700'}"
							onclick={() => setActiveTab('education')}
						>
							Education
						</button>
					{/if}
					{#if (projects && projects.length > 0) || (certifications && certifications.length > 0) || (memberships && memberships.length > 0) || (interests && interests.length > 0)}
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
		<main class="container mx-auto max-w-5xl px-4 py-8">
			<div class="grid gap-8 md:grid-cols-3">
				<!-- Sidebar -->
				<aside class="md:col-span-1">
					<div class="space-y-8">
						<!-- Skills section (always visible on larger screens) -->
						{#if skills && skills.length > 0 && (activeTab === 'all' || activeTab === 'skills' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Skills
								</h2>

								{#if categorizedSkills.length > 0}
									<div class="mt-4 space-y-5">
										{#each categorizedSkills as category}
											<div>
												<h3 class="mb-2 font-semibold text-gray-700">{category.category}</h3>
												<div class="flex flex-wrap gap-2">
													{#each category.skills as skill}
														<span
															class="rounded-full bg-indigo-100 px-3 py-1 text-sm text-indigo-800"
														>
															{skill.name}
															{#if skill.level}
																<span class="ml-1 text-indigo-600">({skill.level})</span>
															{/if}
														</span>
													{/each}
												</div>
											</div>
										{/each}
									</div>
								{:else}
									<div class="mt-4 flex flex-wrap gap-2">
										{#each skills as skill}
											<span class="rounded-full bg-indigo-100 px-3 py-1 text-sm text-indigo-800">
												{skill.name}
												{#if skill.level}
													<span class="ml-1 text-indigo-600">({skill.level})</span>
												{/if}
											</span>
										{/each}
									</div>
								{/if}
							</section>
						{/if}

						<!-- Education section (visible in sidebar on larger screens) -->
						{#if education && education.length > 0 && (activeTab === 'all' || activeTab === 'education' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md md:block print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Education
								</h2>

								<div class="mt-4 space-y-4">
									{#each education as edu}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<h3 class="font-semibold text-gray-800">{edu.institution}</h3>
											<p class="text-gray-700">{edu.qualification || edu.degree}</p>
											{#if edu.field_of_study}
												<p class="text-gray-600">{edu.field_of_study}</p>
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

						<!-- Certifications (in sidebar on larger screens) -->
						{#if certifications && certifications.length > 0 && (activeTab === 'all' || activeTab === 'more' || windowWidth >= 768)}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Certifications
								</h2>

								<div class="mt-4 space-y-4">
									{#each certifications as cert}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<h3 class="font-semibold text-gray-800">{cert.name}</h3>
											{#if cert.issuer}
												<p class="text-gray-700">{cert.issuer}</p>
											{/if}
											{#if cert.date_obtained || cert.date_issued}
												<p class="mt-1 text-sm text-gray-500">
													{formatDate(cert.date_obtained || cert.date_issued)}
													{#if cert.expiry_date}
														- Expires: {formatDate(cert.expiry_date)}
													{/if}
												</p>
											{/if}
										</div>
									{/each}
								</div>
							</section>
						{/if}
					</div>
				</aside>

				<!-- Main content -->
				<div class="md:col-span-2">
					<div class="space-y-8">
						<!-- Work Experience section -->
						{#if workExperiences && workExperiences.length > 0 && (activeTab === 'all' || activeTab === 'work')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Work Experience
								</h2>

								<div class="mt-6 space-y-8">
									{#each workExperiences as job}
										<div class="relative border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
											<!-- Timeline dot for visual appeal (only on larger screens) -->
											<div
												class="absolute top-1 -left-3 hidden h-6 w-6 rounded-full border-4 border-white bg-indigo-100 md:block"
											></div>

											<div class="md:ml-6">
												<div class="flex flex-wrap items-start justify-between gap-2">
													<div>
														<h3 class="text-lg font-semibold text-gray-800">{job.position}</h3>
														<h4 class="text-base text-gray-700">{job.company_name}</h4>
													</div>
													<div class="bg-gray-100 px-3 py-1 text-sm text-gray-700">
														{formatDate(job.start_date)} - {job.end_date
															? formatDate(job.end_date)
															: 'Present'}
													</div>
												</div>

												{#if job.description}
													<div class="mt-3 text-gray-700">
														<p class="whitespace-pre-line">{job.description}</p>
													</div>
												{/if}
											</div>
										</div>
									{/each}
								</div>
							</section>
						{/if}

						<!-- Projects section -->
						{#if projects && projects.length > 0 && (activeTab === 'all' || activeTab === 'more')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Projects
								</h2>

								<div class="mt-6 grid gap-6 sm:grid-cols-2">
									{#each projects as project}
										<div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
											<div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
												<h3 class="font-semibold text-gray-800">{project.title}</h3>
												{#if project.start_date}
													<p class="mt-1 text-xs text-gray-500">
														{formatDate(project.start_date)} - {project.end_date
															? formatDate(project.end_date)
															: 'Present'}
													</p>
												{/if}
											</div>

											<div class="p-4">
												{#if project.description}
													<p class="text-sm text-gray-700">{project.description}</p>
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
																	d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"
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
						{#if memberships && memberships.length > 0 && (activeTab === 'all' || activeTab === 'more')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Professional Memberships
								</h2>

								<div class="mt-4 space-y-4">
									{#each memberships as membership}
										<div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
											<div class="flex flex-wrap items-start justify-between gap-2">
												<div>
													<h3 class="font-semibold text-gray-800">{membership.organisation}</h3>
													{#if membership.role}
														<p class="text-gray-700">{membership.role}</p>
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

						<!-- Interests section -->
						{#if interests && interests.length > 0 && (activeTab === 'all' || activeTab === 'more')}
							<section class="rounded-lg bg-white p-6 shadow-md print:shadow-none">
								<h2 class="border-b border-gray-200 pb-2 text-xl font-bold text-gray-800">
									Interests & Activities
								</h2>

								<div class="mt-4 grid gap-4 sm:grid-cols-2">
									{#each interests as interest}
										<div class="rounded-lg bg-gray-50 p-4">
											<h3 class="font-semibold text-gray-800">{interest.name}</h3>
											{#if interest.description}
												<p class="mt-1 text-sm text-gray-700">{interest.description}</p>
											{/if}
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
			<div class="container mx-auto max-w-5xl px-4">
				<p class="text-gray-300">CV created with CV App</p>
				<p class="mt-2">
					<a href="/" class="text-indigo-300 hover:text-indigo-200 hover:underline"
						>Return to CV App</a
					>
				</p>
			</div>
		</footer>
	</div>
{/if}
