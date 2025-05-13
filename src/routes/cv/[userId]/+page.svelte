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
		qualificationEquivalence,
		error: serverError
	} = data;

	// State variables
	let error = $state<string | null>(serverError || null);
	let loading = $state<boolean>(false);

	// Interface for skill objects
	interface Skill {
		name: string;
		level?: string;
		category?: string;
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
				acc[category].push(skill);
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

<div class="mx-auto max-w-4xl px-4 py-8">
	{#if error}
		<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
	{/if}

	{#if loading}
		<div class="my-8 flex justify-center">
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

					{#if profile.photo_url}
						<div class="h-28 w-28 overflow-hidden rounded-full">
							<img
								src={profile.photo_url}
								alt={profile.full_name || 'Profile picture'}
								class="h-full w-full object-cover"
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
										<p class="whitespace-pre-line">{job.description}</p>
									</div>
								{/if}
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
				<p>CV created with CV App</p>
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
