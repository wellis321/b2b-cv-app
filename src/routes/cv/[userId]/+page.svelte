<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { formatDate } from '$lib/pdfGenerator';
	import ResponsibilitiesEditor from '../../work-experience/ResponsibilitiesEditor.svelte';
	import { getProxiedPhotoUrl, DEFAULT_PROFILE_PHOTO } from '$lib/photoUtils';

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
		qualificationEquivalence,
		error: serverError
	} = data;

	// State variables
	let error = $state<string | null>(serverError || null);
	let loading = $state<boolean>(false);

	// Interface for skill objects
	interface Skill {
		name: string;
		level?: string | null;
		category?: string | null;
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

	// Group skills by category if any
	let categorizedSkills = $state<{ category: string; skills: Skill[] }[]>([]);
	let photoLoadError = $state(false);
	let photoUrl = $state(DEFAULT_PROFILE_PHOTO);

	// Handle page loading/data population
	let previousPhotoUrl = $state<string | null>(null);
	let initialPhotoUrlSet = $state(false);

	// Photo URL effect with initialization and update logic
	$effect(() => {
		// Skip if no profile
		if (!profile) return;

		// Initial setup (runs only once)
		if (!initialPhotoUrlSet && profile.photo_url) {
			previousPhotoUrl = profile.photo_url;
			photoUrl = getProxiedPhotoUrl(profile.photo_url);
			initialPhotoUrlSet = true;
			return;
		}

		// Update photo URL only if it changed
		if (profile.photo_url && profile.photo_url !== previousPhotoUrl) {
			previousPhotoUrl = profile.photo_url;
			photoUrl = getProxiedPhotoUrl(profile.photo_url);
		} else if (!profile.photo_url && photoUrl !== DEFAULT_PROFILE_PHOTO) {
			photoUrl = DEFAULT_PROFILE_PHOTO;
		}

		// Group skills by category if they have categories
		if (skills && skills.length) {
			const hasCategories = skills.some((skill) => skill.category);
			if (hasCategories) {
				// Group skills by category
				const skillsByCategory = skills.reduce<Record<string, Skill[]>>((acc, skill) => {
					const category = skill.category || 'Other';
					if (!acc[category]) {
						acc[category] = [];
					}
					acc[category].push(skill as Skill);
					return acc;
				}, {});

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

				// Convert to array and sort
				categorizedSkills = [];
				orderedCategories.forEach((category) => {
					categorizedSkills.push({
						category,
						skills: skillsByCategory[category]
					});
				});
			}
		}
	});

	// Handle image error
	function handleImageError(event: Event) {
		photoLoadError = true;
		const img = event.target as HTMLImageElement;
		img.style.display = 'none';
		console.error('Failed to load image:', img.src);
	}
</script>

<svelte:head>
	{#if profile}
		<title>{profile.full_name}'s CV</title>
		<meta name="description" content="View {profile.full_name}'s professional CV" />
	{:else}
		<title>CV</title>
		<meta name="description" content="View this professional CV" />
	{/if}
</svelte:head>

<div class="mx-auto max-w-4xl px-4 py-6">
	{#if error}
		<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
	{/if}

	{#if loading}
		<div class="my-6 flex justify-center">
			<div class="text-center">
				<div
					class="mx-auto mb-2 h-10 w-10 animate-spin rounded-full border-t-2 border-b-2 border-indigo-500"
				></div>
				<p class="text-gray-600">Loading CV...</p>
			</div>
		</div>
	{:else if !profile}
		<div class="rounded bg-yellow-100 p-4">
			<p class="font-medium">CV not found or no longer available.</p>
		</div>
	{:else}
		<div class="bg-white p-8 shadow-lg print:shadow-none">
			<!-- Header with personal info -->
			<div class="border-b border-gray-300 pb-6">
				<div class="flex items-start justify-between">
					<div>
						<h1 class="text-3xl font-bold">{profile.full_name || 'Your Name'}</h1>
						<div class="mt-2 text-gray-700">
							{#if profile.location}
								<p>{profile.location}</p>
							{/if}
							{#if profile.email}
								<p>{profile.email}</p>
							{/if}
							{#if profile.phone}
								<p>{profile.phone}</p>
							{/if}
						</div>
					</div>

					{#if !photoLoadError && profile.photo_url}
						<div class="h-28 w-28 overflow-hidden rounded-full">
							<img
								src={photoUrl}
								alt={profile.full_name || 'Profile picture'}
								class="h-full w-full object-cover"
								onerror={handleImageError}
							/>
						</div>
					{/if}
				</div>
			</div>

			<!-- Work Experience -->
			{#if workExperiences && workExperiences.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Work Experience</h2>
					<div class="space-y-6">
						{#each workExperiences as job}
							<div>
								<div class="flex justify-between">
									<div>
										<h3 class="font-semibold">{job.position}</h3>
										<h4 class="text-gray-700">{job.company_name}</h4>
									</div>
									<div class="text-sm text-gray-600">
										{formatDate(job.start_date)} - {formatDate(job.end_date)}
									</div>
								</div>
								{#if job.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">
											{#if job.description.includes('Key Responsibilities:')}
												{job.description.split('Key Responsibilities:')[0].trim()}
											{:else}
												{job.description}
											{/if}
										</p>
									</div>
								{/if}

								<!-- Display job responsibilities -->
								<div class="mt-3">
									<ResponsibilitiesEditor workExperienceId={job.id} readOnly={true} />
								</div>
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Projects -->
			{#if projects && projects.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Projects</h2>
					<div class="space-y-6">
						{#each projects as project}
							<div>
								<div class="flex justify-between">
									<div>
										<h3 class="font-semibold">{project.title}</h3>
										{#if project.url}
											<a
												href={project.url}
												target="_blank"
												rel="noopener noreferrer"
												class="text-indigo-600 hover:underline"
											>
												{project.url.replace(/^https?:\/\//, '')}
											</a>
										{/if}
									</div>
									{#if project.start_date}
										<div class="text-sm text-gray-600">
											{formatDate(project.start_date)} - {formatDate(project.end_date)}
										</div>
									{/if}
								</div>
								{#if project.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">{project.description}</p>
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Skills -->
			{#if skills && skills.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Skills</h2>

					<!-- Categorized skills -->
					{#if categorizedSkills.length > 0}
						<div class="space-y-4">
							{#each categorizedSkills as category}
								<div>
									<h3 class="mb-2 font-semibold">{category.category}</h3>
									<div>
										{#each category.skills as skill}
											<span
												class="mr-2 mb-2 inline-block rounded-full bg-gray-200 px-3 py-1 text-sm"
											>
												{skill.name}
												{#if skill.level}
													<span class="ml-1 text-gray-600">({skill.level})</span>
												{/if}
											</span>
										{/each}
									</div>
								</div>
							{/each}
						</div>
						<!-- Fallback for uncategorized skills -->
					{:else}
						<div>
							{#each skills as skill}
								<span class="mr-2 mb-2 inline-block rounded-full bg-gray-200 px-3 py-1 text-sm">
									{skill.name}
									{#if skill.level}
										<span class="ml-1 text-gray-600">({skill.level})</span>
									{/if}
								</span>
							{/each}
						</div>
					{/if}
				</div>
			{/if}

			<!-- Education -->
			{#if education && education.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Education</h2>
					<div class="space-y-6">
						{#each education as edu}
							<div>
								<div class="flex justify-between">
									<div>
										<h3 class="font-semibold">{edu.degree || edu.course}</h3>
										<h4 class="text-gray-700">{edu.institution}</h4>
									</div>
									<div class="text-sm text-gray-600">
										{formatDate(edu.start_date)} - {formatDate(edu.end_date)}
									</div>
								</div>
								{#if edu.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">{edu.description}</p>
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Certifications -->
			{#if certifications && certifications.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Certifications</h2>
					<div class="space-y-6">
						{#each certifications as cert}
							<div>
								<div class="flex justify-between">
									<div>
										<h3 class="font-semibold">{cert.name}</h3>
										{#if cert.issuer}
											<h4 class="text-gray-700">{cert.issuer}</h4>
										{/if}
										{#if cert.url}
											<a
												href={cert.url}
												target="_blank"
												rel="noopener noreferrer"
												class="text-indigo-600 hover:underline"
											>
												{cert.url.replace(/^https?:\/\//, '')}
											</a>
										{/if}
									</div>
									{#if cert.date_issued || cert.date_obtained}
										<div class="text-sm text-gray-600">
											Issued: {formatDate(cert.date_issued || cert.date_obtained)}
											{#if cert.expiry_date}
												<br />Expires: {formatDate(cert.expiry_date)}
											{/if}
										</div>
									{/if}
								</div>
								{#if cert.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">{cert.description}</p>
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Professional Memberships -->
			{#if memberships && memberships.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Professional Memberships</h2>
					<div class="space-y-6">
						{#each memberships as membership}
							<div>
								<div class="flex justify-between">
									<div>
										<h3 class="font-semibold">{membership.organisation}</h3>
										{#if membership.role}
											<h4 class="text-gray-700">{membership.role}</h4>
										{/if}
									</div>
									{#if membership.start_date}
										<div class="text-sm text-gray-600">
											{formatDate(membership.start_date)} - {formatDate(membership.end_date)}
										</div>
									{/if}
								</div>
								{#if membership.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">{membership.description}</p>
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Qualification Equivalence -->
			{#if qualificationEquivalence && qualificationEquivalence.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Professional Qualification Equivalence</h2>
					<div class="space-y-6">
						{#each qualificationEquivalence as qualification}
							<div>
								<h3 class="font-semibold">{qualification.qualification}</h3>
								<h4 class="text-gray-700">Equivalent to: {qualification.equivalent_to}</h4>
								{#if qualification.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">{qualification.description}</p>
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Interests -->
			{#if interests && interests.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Interests & Activities</h2>
					<div class="space-y-6">
						{#each interests as interest}
							<div>
								<h3 class="font-semibold">{interest.name}</h3>
								{#if interest.description}
									<div class="mt-2 text-gray-700">
										<p class="whitespace-pre-line">{interest.description}</p>
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</div>
			{/if}

			<!-- Footer -->
			<div class="mt-12 border-t border-gray-300 pt-4 text-center text-sm text-gray-500">
				<p>CV created with CV App by William and Max Ellis</p>
				<p class="mt-1">
					<button
						class="text-indigo-600 hover:underline"
						onclick={() => window.print()}
						title="Print this CV"
					>
						Print this CV
					</button>
				</p>
			</div>
		</div>
	{/if}

	<!-- Link back to main site -->
	<div class="mt-6 text-center">
		<a href="/" class="text-indigo-600 hover:underline">Go to CV App</a>
	</div>
</div>
