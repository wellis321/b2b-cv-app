<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { session } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';
	import {
		generateCvPdf,
		formatDate,
		type CvData,
		type PdfExportConfig,
		defaultPdfConfig
	} from '$lib/pdfGenerator';
	import { cvStore } from '$lib/stores/cvDataStore';

	// CV data
	let profile = $state<any>(null);
	let workExperiences = $state<any[]>([]);
	let projects = $state<any[]>([]);
	let skills = $state<any[]>([]);
	let education = $state<any[]>([]);
	let certifications = $state<any[]>([]);
	let memberships = $state<any[]>([]);
	let interests = $state<any[]>([]);
	let qualificationEquivalence = $state<any[]>([]);
	let error = $state<string | null>(null);
	let loading = $state(true);
	let generatingPdf = $state(false);
	let photoLoadError = $state(false);
	let dataLoaded = $state(false);

	// PDF export configuration
	let pdfConfig = $state<PdfExportConfig>({ ...defaultPdfConfig });
	let showPdfOptions = $state(false);

	// Shareable URL
	let shareableUrl = $state<string | null>(null);

	// Interface for skill objects
	interface Skill {
		name: string;
		level?: string;
		category?: string;
	}

	// Valid section names type
	type SectionName = keyof typeof defaultPdfConfig.sections;

	// Format section name for display
	function formatSectionName(section: string): string {
		// Convert camelCase to Title Case with spaces
		const formatted = section
			.replace(/([A-Z])/g, ' $1') // Add space before capital letters
			.replace(/^./, (str) => str.toUpperCase()); // Capitalize first letter

		// Special cases
		if (section === 'workExperience') return 'Work Experience';
		if (section === 'qualificationEquivalence') return 'Qualification Equivalence';

		return formatted;
	}

	// Update section visibility in PDF config
	function updateSectionVisibility(section: SectionName, value: boolean): void {
		pdfConfig = {
			...pdfConfig,
			sections: {
				...pdfConfig.sections,
				[section]: value
			}
		};
	}

	// Toggle all sections on/off
	function toggleAllSections(value: boolean): void {
		pdfConfig = {
			...pdfConfig,
			sections: {
				profile: value,
				workExperience: value,
				projects: value,
				skills: value,
				education: value,
				certifications: value,
				memberships: value,
				interests: value,
				qualificationEquivalence: value
			}
		};
	}

	// Check if a photo URL is accessible
	async function checkPhotoAccessibility(url: string): Promise<boolean> {
		if (!url) return false;

		try {
			// Use a HEAD request to check if the photo is accessible
			const response = await fetch(url, {
				method: 'HEAD',
				// Avoid cached responses
				headers: {
					'Cache-Control': 'no-cache'
				}
			});
			return response.ok;
		} catch (err) {
			console.warn('Error checking profile photo:', err);
			return false;
		}
	}

	// Load all CV data
	onMount(async () => {
		if (!browser) return;

		if (!$session || !$session.user) {
			error = 'Please log in to view your CV';
			loading = false;
			return;
		}

		try {
			// Use the CV data store to load all data
			const data = await cvStore.loadCurrentUserData();

			if (!data || !data.profile) {
				error = 'Failed to load your CV data. Please complete your profile.';
				loading = false;
				return;
			}

			// Update the local variables with data from the store
			profile = data.profile;
			workExperiences = data.workExperiences || [];
			projects = data.projects || [];
			skills = data.skills || [];
			education = data.education || [];
			certifications = data.certifications || [];
			memberships = data.memberships || [];
			interests = data.interests || [];
			qualificationEquivalence = data.qualificationEquivalence || [];

			dataLoaded = true;

			// Check if the profile photo is valid - do this after setting all data
			// to avoid reactive updates during initial data load
			if (profile?.photo_url) {
				photoLoadError = !(await checkPhotoAccessibility(profile.photo_url));
				if (photoLoadError) {
					console.warn('Profile photo inaccessible:', profile.photo_url);
				}
			}

			// Generate shareable URL
			if (profile.username) {
				shareableUrl = `${window.location.origin}/cv/@${profile.username}`;
			} else {
				shareableUrl = `${window.location.origin}/cv/${$session.user.id}`;
			}
		} catch (err) {
			console.error('Error loading CV data:', err);
			error = 'An unexpected error occurred while loading your CV data';
		} finally {
			loading = false;
		}
	});

	// Generate PDF
	async function generatePdf() {
		if (generatingPdf || !profile) return;

		generatingPdf = true;

		try {
			// Prepare CV data
			const cvData: CvData = {
				profile,
				workExperiences,
				projects,
				skills,
				education,
				certifications,
				memberships,
				interests,
				qualificationEquivalence
			};

			// Generate and download PDF
			await generateCvPdf(cvData, pdfConfig);
		} catch (err) {
			console.error('Error generating PDF:', err);
			error = 'Failed to generate PDF. Please try again.';
		} finally {
			generatingPdf = false;
		}
	}

	// Toggle PDF options
	function togglePdfOptions() {
		showPdfOptions = !showPdfOptions;
	}

	// Copy shareable URL to clipboard
	function copyToClipboard() {
		if (!shareableUrl) return;

		navigator.clipboard
			.writeText(shareableUrl)
			.then(() => {
				// Could show a toast notification here
				console.log('URL copied to clipboard');
			})
			.catch((err) => {
				console.error('Could not copy URL:', err);
			});
	}

	// Handle image error
	function handleImageError(event: Event) {
		photoLoadError = true;
		const img = event.target as HTMLImageElement;
		img.style.display = 'none';
		console.error('Failed to load image:', img.src);
	}
</script>

<svelte:head>
	<title>CV Preview</title>
	<meta name="description" content="Preview your CV" />
</svelte:head>

<div class="container mx-auto max-w-4xl px-4 py-8">
	<div class="mb-6 flex items-center justify-between">
		<h1 class="text-2xl font-bold">CV Preview</h1>
		<div class="flex gap-2">
			<button
				onclick={togglePdfOptions}
				class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 focus:outline-none"
			>
				{showPdfOptions ? 'Hide PDF Options' : 'PDF Options'}
			</button>

			<button
				onclick={generatePdf}
				disabled={loading || generatingPdf || !profile}
				class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none disabled:opacity-50"
			>
				{generatingPdf ? 'Generating...' : 'Download PDF'}
			</button>

			{#if shareableUrl}
				<div class="relative ml-2">
					<input
						type="text"
						readonly
						value={shareableUrl}
						class="w-60 rounded border px-3 py-2 text-sm"
					/>
					<button
						onclick={copyToClipboard}
						class="absolute top-1/2 right-2 -translate-y-1/2 rounded text-indigo-600 hover:text-indigo-800"
						title="Copy to clipboard"
						aria-label="Copy shareable URL to clipboard"
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
							/>
						</svg>
					</button>
				</div>
			{/if}
		</div>
	</div>

	<!-- PDF Options Panel -->
	{#if showPdfOptions}
		<div class="mb-8 rounded-lg border border-gray-300 bg-gray-50 p-6">
			<h2 class="mb-4 text-xl font-semibold">PDF Export Options</h2>

			<div class="mb-4">
				<div class="mb-2 flex items-center justify-between">
					<label class="font-medium">What to include:</label>
					<div>
						<button
							onclick={() => toggleAllSections(true)}
							class="mr-2 text-sm text-indigo-600 hover:underline"
							type="button"
						>
							Select All
						</button>
						<button
							onclick={() => toggleAllSections(false)}
							class="text-sm text-indigo-600 hover:underline"
							type="button"
						>
							Deselect All
						</button>
					</div>
				</div>
				<div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
					{#each ['profile', 'workExperience', 'projects', 'skills', 'education', 'certifications', 'memberships', 'qualificationEquivalence', 'interests'] as section}
						{@const sectionName = section as SectionName}
						<div class="flex items-center rounded p-1 hover:bg-gray-100">
							<input
								type="checkbox"
								id={`include-${section}`}
								checked={pdfConfig.sections[sectionName]}
								onclick={() =>
									updateSectionVisibility(sectionName, !pdfConfig.sections[sectionName])}
								class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
							/>
							<label for={`include-${section}`} class="ml-2 block text-gray-700">
								{formatSectionName(section)}
							</label>
						</div>
					{/each}
				</div>
			</div>

			<div class="mb-4">
				<div class="flex items-center">
					<input
						type="checkbox"
						id="include-photo"
						checked={pdfConfig.includePhoto}
						onclick={(e) => {
							const target = e.target as HTMLInputElement;
							pdfConfig = {
								...pdfConfig,
								includePhoto: target.checked
							};
						}}
						class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
					/>
					<label for="include-photo" class="ml-2 block text-gray-700">
						Include Profile Photo
						{#if photoLoadError}
							<span class="ml-2 text-xs text-red-600">
								(Photo may not be accessible. If PDF generation fails, try disabling this option)
							</span>
						{/if}
					</label>
				</div>
			</div>

			<div class="mb-4">
				<label class="mb-2 block font-medium">Template:</label>
				<div class="flex space-x-4">
					{#each ['standard', 'minimal', 'professional'] as templateOption}
						<div class="flex items-center">
							<input
								type="radio"
								id={`template-${templateOption}`}
								name="template"
								value={templateOption}
								checked={pdfConfig.template === templateOption}
								disabled={templateOption !== 'standard'}
								onclick={(e) => {
									const target = e.target as HTMLInputElement;
									pdfConfig = {
										...pdfConfig,
										template: target.value as 'standard' | 'minimal' | 'professional'
									};
								}}
								class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
							/>
							<label for={`template-${templateOption}`} class="ml-2 block text-gray-700 capitalize">
								{templateOption}
								{#if templateOption !== 'standard'}
									<span class="ml-1 text-xs text-gray-500">(Coming soon)</span>
								{/if}
							</label>
						</div>
					{/each}
				</div>
			</div>

			<div class="mt-4 text-right">
				<button
					onclick={generatePdf}
					disabled={generatingPdf}
					class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none disabled:opacity-50"
				>
					Generate PDF with Current Settings
				</button>
			</div>
		</div>
	{/if}

	{#if error}
		<div class="mb-4 rounded bg-red-100 p-4 text-red-700">{error}</div>
	{/if}

	{#if loading}
		<div class="my-8 flex justify-center">
			<div class="text-center">
				<div
					class="mx-auto mb-2 h-10 w-10 animate-spin rounded-full border-t-2 border-b-2 border-indigo-500"
				></div>
				<p class="text-gray-600">Loading your CV data...</p>
			</div>
		</div>
	{:else if !profile}
		<div class="rounded bg-yellow-100 p-4">
			<p class="font-medium">Please complete your profile to preview your CV.</p>
			<a
				href="/profile"
				class="mt-2 inline-block rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
			>
				Go to Profile
			</a>
		</div>
	{:else if dataLoaded}
		<div id="cv-content" class="bg-white p-8 shadow-lg">
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

					{#if profile.photo_url && !photoLoadError}
						<div class="h-28 w-28 overflow-hidden rounded-full">
							<img
								src={profile.photo_url}
								alt={profile.full_name || 'Profile picture'}
								class="h-full w-full object-cover"
								onerror={handleImageError}
							/>
						</div>
					{/if}
				</div>
			</div>

			<!-- Work Experience -->
			{#if workExperiences.length > 0}
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
			{#if projects.length > 0}
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
			{#if skills.length > 0}
				<div class="mt-8">
					<h2 class="mb-4 text-xl font-bold">Skills</h2>
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
				</div>
			{/if}

			<!-- Education -->
			{#if education.length > 0}
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
									{#if cert.date_issued}
										<div class="text-sm text-gray-600">
											Issued: {formatDate(cert.date_issued)}
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
		</div>
	{/if}

	<div class="mt-6">
		<SectionNavigation />
	</div>
</div>
