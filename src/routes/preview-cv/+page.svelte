<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { session } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';
	import ResponsibilitiesEditor from '../work-experience/ResponsibilitiesEditor.svelte';
	import {
		generateCvPdf,
		type CvData,
		type PdfExportConfig,
		defaultPdfConfig
	} from '$lib/pdfGenerator';
	import { formatDateWithPreference, getDateFormatPreference } from '$lib/utils/dateFormatting';
	import { cvStore } from '$lib/stores/cvDataStore';
	import { page } from '$app/stores';
	import { getProxiedPhotoUrl, validatePhotoUrl, DEFAULT_PROFILE_PHOTO } from '$lib/photoUtils';
	import { decodeHtmlEntities } from '$lib/validation';
	import { canExportPdf, getAvailableTemplates } from '$lib/utils/subscriptionUtils';
	import { goto } from '$app/navigation';
	import { formatDescription, formatDescriptionWithFormatting } from '$lib/utils/textFormatting';

	console.log('CV Preview page script loaded');

	// CV data
	let profile = $state<any>(null);
	let professionalSummary = $state<any>(null);
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
	let previousPhotoUrl = $state<string | null>(null);
	let initialPhotoUrlSet = $state(false);

	// Get username from URL query params if available
	const urlUsername = $page.url.searchParams.get('username');

	// PDF export configuration
	let pdfConfig = $state<PdfExportConfig>({ ...defaultPdfConfig });
	let showPdfOptions = $state(false);

	// Shareable URL
	let shareableUrl = $state<string | null>(null);

	// Template selection
	let selectedTemplate = $state('basic');
	let availableTemplates = $state<string[]>(['basic']);

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

	// Get sorted professional summary strengths
	function getSortedStrengths(professionalSummary: any) {
		if (!professionalSummary?.professional_summary_strengths) return [];
		return [...professionalSummary.professional_summary_strengths].sort(
			(a, b) => a.sort_order - b.sort_order
		);
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

	// Load all CV data
	onMount(async () => {
		console.log('CV Preview onMount function called');
		if (!browser) return;

		try {
			// Data should already be loaded by +page.ts, so we just need to extract it
			const data = $cvStore;
			console.log('CV Preview - Raw store data:', data);
			console.log('CV Preview - Store professional summary:', data?.professionalSummary);

			// More thorough validation to prevent premature error message
			if (!data) {
				error = 'Failed to load CV data. Please try again later.';
				loading = false;
				return;
			}

			// Get minimal required profile data rather than checking entire profile
			if (!data.profile) {
				error = 'Your CV profile could not be found. Please create your profile first.';
				loading = false;
				return;
			}

			// Check if first name or last name is missing
			const hasName = data.profile.first_name || data.profile.last_name || data.profile.full_name;
			if (!hasName) {
				// Instead of error, just mark loading as complete and data as loaded
				// We'll show a special message in the UI for this case
				loading = false;
				dataLoaded = true;
				profile = data.profile; // Still set the profile data
				// But don't set error - we'll handle this case in the UI with a helpful message
				return;
			}

			// Set the shareable URL based on the loaded profile
			if (data.profile.username) {
				shareableUrl = `${window.location.origin}/cv/@${data.profile.username}`;
			} else if ($session && $session.user) {
				shareableUrl = `${window.location.origin}/cv/${$session.user.id}`;
			}

			// Update the local variables with data from the store
			profile = data.profile;
			professionalSummary = data.professionalSummary || null;
			console.log('CV Preview - Professional Summary data:', professionalSummary);
			console.log(
				'CV Preview - Professional Summary description:',
				professionalSummary?.description
			);
			console.log(
				'CV Preview - Professional Summary strengths:',
				professionalSummary?.professional_summary_strengths
			);
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
				photoLoadError = !(await validatePhotoUrl(profile.photo_url));
				if (photoLoadError) {
					console.warn('Profile photo inaccessible:', profile.photo_url);
				}

				// Store the initial photo URL
				previousPhotoUrl = profile.photo_url;
				initialPhotoUrlSet = true;
			}
		} catch (err) {
			console.error('Error loading CV data:', err);
			error = 'An unexpected error occurred while loading your CV data';
		} finally {
			loading = false;
		}
	});

	// Get available templates based on subscription
	$effect(() => {
		availableTemplates = getAvailableTemplates();

		// If current template is not available, switch to basic
		if (!availableTemplates.includes(selectedTemplate)) {
			selectedTemplate = 'basic';
		}

		// Update PDF config with selected template
		pdfConfig = {
			...pdfConfig,
			template: selectedTemplate
		};
	});

	// Get template color for preview
	function getTemplateColor(template: string): string {
		switch (template) {
			case 'professional':
				return '#1f497d'; // Navy blue
			case 'modern':
				return '#3498db'; // Blue
			case 'creative':
				return '#e74c3c'; // Red
			case 'executive':
				return '#2c3e50'; // Dark blue
			case 'simple':
				return '#000000'; // Black
			case 'classic':
				return '#800000'; // Maroon
			case 'elegant':
				return '#4b0082'; // Indigo
			case 'minimalist':
				return '#333333'; // Dark gray
			case 'bold':
				return '#ff5722'; // Orange
			case 'academic':
				return '#003366'; // Dark blue
			case 'technical':
				return '#16a085'; // Teal
			default:
				return '#333333'; // Basic template - dark gray
		}
	}

	// Get template description for preview
	function getTemplateDescription(template: string): string {
		switch (template) {
			case 'basic':
				return 'Clean and simple layout';
			case 'professional':
				return 'Polished look for corporate roles';
			case 'modern':
				return 'Contemporary design with blue accents';
			case 'creative':
				return 'Bold design for creative fields';
			case 'executive':
				return 'Sophisticated style for leadership positions';
			case 'simple':
				return 'Minimalist black and white design';
			case 'classic':
				return 'Traditional format with maroon accents';
			case 'elegant':
				return 'Refined style with indigo highlights';
			case 'minimalist':
				return 'Ultra-clean with minimal elements';
			case 'bold':
				return 'Eye-catching with strong orange accents';
			case 'academic':
				return 'Formal layout for research and education';
			case 'technical':
				return 'Structured format for technical roles';
			default:
				return 'Standard CV template';
		}
	}

	// Function to handle upgrade prompt
	function handleUpgradePrompt(feature: string) {
		goto(`/subscription?required=${feature}`);
	}

	// Refresh CV data from the store
	async function refreshCvData(): Promise<void> {
		if (!browser) return;

		try {
			loading = true;

			// Get the current user's username and refresh the CV store
			if ($session?.user?.id) {
				const { data: profileData } = await supabase
					.from('profiles')
					.select('username')
					.eq('id', $session.user.id)
					.single();

				if (profileData?.username) {
					// Refresh the CV store data
					await cvStore.loadByUsername(profileData.username);

					// Update local variables with fresh data
					const freshData = $cvStore;
					profile = freshData.profile;
					workExperiences = freshData.workExperiences || [];
					projects = freshData.projects || [];
					skills = freshData.skills || [];
					education = freshData.education || [];
					certifications = freshData.certifications || [];
					memberships = freshData.memberships || [];
					interests = freshData.interests || [];
					qualificationEquivalence = freshData.qualificationEquivalence || [];

					console.log('CV data refreshed successfully');
				}
			}
		} catch (err) {
			console.error('Error refreshing CV data:', err);
		} finally {
			loading = false;
		}
	}

	// Generate and download PDF
	async function generatePdf(): Promise<void> {
		if (!browser) return;

		try {
			// Update the template selection in the config
			pdfConfig.template = selectedTemplate;

			generatingPdf = true;

			// Prepare CV data for PDF generation
			const cvData: CvData = {
				profile: {
					id: profile.id,
					full_name:
						profile.full_name ||
						`${profile.first_name || ''} ${profile.middle_name || ''} ${profile.last_name || ''}`.trim(),
					email: profile.email,
					phone: profile.phone,
					location: profile.location,
					photo_url: photoLoadError ? null : pdfConfig.includePhoto ? profile.photo_url : null,
					linkedin_url: profile.linkedin_url,
					bio: profile.bio ? decodeHtmlEntities(profile.bio) : null
				},
				professionalSummary: professionalSummary
					? {
							id: professionalSummary.id,
							description: professionalSummary.description
								? decodeHtmlEntities(professionalSummary.description)
								: null,
							strengths: professionalSummary.professional_summary_strengths || []
						}
					: undefined,
				workExperiences: workExperiences.map((exp) => ({
					id: exp.id,
					company_name: exp.company_name,
					position: exp.position,
					start_date: exp.start_date,
					end_date: exp.end_date,
					description: exp.description ? decodeHtmlEntities(exp.description) : null,
					hide_date: exp.hide_date || false
				})),
				projects: projects.map((proj) => ({
					id: proj.id,
					title: proj.title,
					description: proj.description ? decodeHtmlEntities(proj.description) : null,
					start_date: proj.start_date,
					end_date: proj.end_date,
					url: proj.url
				})),
				skills: skills.map((skill) => ({
					id: skill.id,
					name: skill.name,
					level: skill.level,
					category: skill.category
				})),
				education: education.map((edu) => ({
					id: edu.id,
					institution: edu.institution,
					degree: edu.degree,
					course: edu.course,
					start_date: edu.start_date,
					end_date: edu.end_date,
					description: edu.description ? decodeHtmlEntities(edu.description) : null
				})),
				certifications: certifications.map((cert) => ({
					id: cert.id,
					name: cert.name,
					issuer: cert.issuer,
					date_issued: cert.date_issued,
					expiry_date: cert.expiry_date,
					url: cert.url,
					description: cert.description ? decodeHtmlEntities(cert.description) : null
				})),
				memberships: memberships.map((member) => ({
					id: member.id,
					organisation: member.organisation,
					role: member.role,
					start_date: member.start_date,
					end_date: member.end_date,
					description: member.description ? decodeHtmlEntities(member.description) : null
				})),
				interests: interests.map((interest) => ({
					id: interest.id,
					name: interest.name,
					description: interest.description ? decodeHtmlEntities(interest.description) : null
				})),
				qualificationEquivalence: qualificationEquivalence.map((qual) => ({
					id: qual.id,
					profile_id: qual.profile_id,
					level: qual.level,
					description: qual.description ? decodeHtmlEntities(qual.description) : null,
					qualification: qual.qualification,
					equivalent_to: qual.equivalent_to
				}))
			};

			// Log template info for debugging
			console.log(`Generating PDF with template: ${pdfConfig.template}`);

			// Generate the PDF with the updated template
			await generateCvPdf(cvData, pdfConfig);
		} catch (err) {
			console.error('Error generating PDF:', err);
			alert('Failed to generate PDF. Please try again later.');
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

	// Group skills by category
	function getSkillsByCategory(skillsList: any[]) {
		return skillsList.reduce((acc: Record<string, any[]>, skill) => {
			const category = skill.category || 'Other';
			if (!acc[category]) acc[category] = [];
			acc[category].push(skill);
			return acc;
		}, {});
	}

	// Get categorized skills
	function getCategorizedSkills(skillsList: any[]) {
		const skillsByCategory = getSkillsByCategory(skillsList);

		// Get all category names from skills
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

		// Convert to array format for template rendering
		const categorizedSkillsArray = orderedCategories.map((category) => ({
			category,
			skills: skillsByCategory[category]
		}));

		return categorizedSkillsArray;
	}

	// Handle image error
	function handleImageError(event: Event) {
		photoLoadError = true;
		const img = event.target as HTMLImageElement;
		console.error('Failed to load image:', img.src);
	}

	// Check photo accessibility
	async function checkPhotoAccessibility(url?: string) {
		if (photoLoadError) return;

		const photoUrl = url || profile?.photo_url;
		if (!photoUrl) return;

		try {
			const isAccessible = await validatePhotoUrl(photoUrl);
			photoLoadError = !isAccessible;
		} catch (err) {
			console.error('Error checking photo accessibility:', err);
			photoLoadError = true;
		}
	}

	// Check photo URL when it changes
	$effect(() => {
		if (!browser || !profile?.photo_url) return;

		// Skip if this is the initial photo URL we already checked
		if (initialPhotoUrlSet && profile.photo_url === previousPhotoUrl) return;

		// Update the previous photo URL to prevent infinite loops
		previousPhotoUrl = profile.photo_url;

		// Check accessibility of new photo URL
		checkPhotoAccessibility(profile.photo_url);
	});
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
				onclick={refreshCvData}
				disabled={loading}
				class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none disabled:opacity-50"
			>
				{loading ? 'Refreshing...' : 'Refresh CV Data'}
			</button>
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
				<div class="ml-2 flex items-center gap-2">
					<a
						href={shareableUrl}
						target="_blank"
						rel="noopener noreferrer"
						class="flex items-center gap-1 rounded bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-800 hover:bg-indigo-200"
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
						View Public CV
					</a>
					<button
						onclick={copyToClipboard}
						title="Copy link to clipboard"
						class="rounded bg-gray-100 p-2 text-gray-700 hover:bg-gray-200"
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

	<!-- PDF Controls Section -->
	<div class="mt-8 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
		<h2 class="mb-4 text-lg font-medium text-gray-900">PDF Export Options</h2>

		<!-- Template selection section -->
		<div class="mx-auto my-8 w-full max-w-3xl rounded-lg bg-white p-6 shadow-md">
			<div class="mb-4 flex items-center justify-between">
				<h2 class="text-xl font-semibold">Choose a Template</h2>
				<a
					href="/cv/templates"
					class="text-primary-600 hover:text-primary-800 text-sm hover:underline"
				>
					View all templates
				</a>
			</div>

			<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
				{#each availableTemplates as template}
					<div
						class="template-card cursor-pointer rounded-md border p-3 transition-all hover:shadow-md {selectedTemplate ===
						template
							? 'ring-primary-500 shadow-md ring-2'
							: 'hover:border-gray-400'}"
						onclick={() => {
							selectedTemplate = template;
							pdfConfig = {
								...pdfConfig,
								template
							};
						}}
					>
						<div class="relative mb-2 aspect-[0.7] w-full overflow-hidden rounded bg-gray-50">
							<!-- Template thumbnail -->
							<div class="absolute inset-0 overflow-hidden border">
								{#if template === 'basic'}
									<div class="p-2 text-[7px]">
										<div class="mb-1 text-center text-[8px] font-bold">JOHN DOE</div>
										<div class="mb-1 text-center text-[6px]">
											johndoe@example.com | (123) 456-7890
										</div>
										<div class="my-1 h-[2px] w-full bg-gray-300"></div>
										<div class="mt-1 text-[7px] font-bold">EXPERIENCE</div>
										<div class="flex justify-between text-[6px]">
											<span class="font-semibold">Software Developer</span>
											<span>2018 - Present</span>
										</div>
									</div>
								{:else if template === 'professional'}
									<div class="p-2 text-[7px]">
										<div class="mb-1 text-center text-[8px] font-bold text-[#2c3e50]">JOHN DOE</div>
										<div class="mb-1 text-center text-[6px]">
											johndoe@example.com | (123) 456-7890
										</div>
										<div class="my-1 h-[2px] w-full bg-[#3498db]"></div>
										<div class="mt-1 text-[7px] font-bold text-[#3498db]">EXPERIENCE</div>
										<div class="flex justify-between text-[6px]">
											<span class="font-semibold">Software Developer</span>
											<span>2018 - Present</span>
										</div>
									</div>
								{:else if template === 'modern'}
									<div class="flex h-full">
										<div class="h-full w-[30%] bg-[#e8eaf6]"></div>
										<div class="flex-1 p-2 text-[7px]">
											<div class="mb-1 text-[8px] font-bold text-[#1a237e]">JOHN DOE</div>
											<div class="mb-1 text-[6px]">johndoe@example.com</div>
											<div class="mt-1 text-[7px] font-bold text-[#3f51b5]">EXPERIENCE</div>
											<div class="flex justify-between text-[6px]">
												<span class="font-semibold">Software Developer</span>
												<span class="text-[#5c6bc0]">2018 - Present</span>
											</div>
										</div>
									</div>
								{:else if template === 'executive'}
									<div class="p-2 text-[7px]">
										<div class="mb-2 h-[2px] w-full bg-[#263238]"></div>
										<div class="mb-1 text-center text-[8px] font-bold">JOHN DOE</div>
										<div class="mb-1 text-center text-[6px]">
											johndoe@example.com | (123) 456-7890
										</div>
										<div class="mt-1 text-[7px] font-bold text-[#263238]">EXPERIENCE</div>
										<div class="flex justify-between text-[6px]">
											<span class="font-semibold">Software Developer</span>
											<span>2018 - Present</span>
										</div>
										<div class="absolute bottom-2 h-[1px] w-[calc(100%-16px)] bg-[#78909c]"></div>
									</div>
								{:else if template === 'creative'}
									<div class="p-0 text-[7px]">
										<div class="mb-1 h-[15px] w-full bg-[#b388ff]"></div>
										<div class="p-2">
											<div class="mb-1 text-center text-[8px] font-bold text-[#6200ea]">
												JOHN DOE
											</div>
											<div class="mb-1 text-center text-[6px]">johndoe@example.com</div>
											<div class="mt-1 text-[7px] font-bold text-[#651fff]">EXPERIENCE</div>
											<div class="flex justify-between text-[6px]">
												<span class="font-semibold">Software Developer</span>
												<span class="text-[#9575cd]">2018 - Present</span>
											</div>
										</div>
									</div>
								{:else if template === 'minimal'}
									<div class="p-3 text-[7px]">
										<div class="mb-1 text-center text-[8px] font-bold text-[#212121]">JOHN DOE</div>
										<div class="mb-1 text-center text-[6px] text-[#616161]">
											johndoe@example.com
										</div>
										<div class="mt-2 text-[7px] font-bold text-[#424242]">EXPERIENCE</div>
										<div class="flex justify-between text-[6px]">
											<span class="font-semibold">Software Developer</span>
											<span class="text-[#757575]">2018 - Present</span>
										</div>
									</div>
								{/if}
							</div>
						</div>
						<div class="flex items-center justify-between">
							<div class="text-sm font-medium capitalize">{template}</div>
							{#if selectedTemplate === template}
								<div class="bg-primary-500 rounded-full p-1">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										class="h-4 w-4 text-white"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											fill-rule="evenodd"
											d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
											clip-rule="evenodd"
										/>
									</svg>
								</div>
							{/if}
						</div>
						<div class="mt-1 text-xs text-gray-500">
							{#if template === 'basic'}
								Simple and clean design
							{:else if template === 'professional'}
								Traditional business style
							{:else if template === 'modern'}
								Contemporary with side panel
							{:else if template === 'executive'}
								Sophisticated corporate look
							{:else if template === 'creative'}
								Vibrant and distinctive
							{:else if template === 'minimal'}
								Clean and minimal
							{:else}
								{template} design
							{/if}
						</div>
					</div>
				{/each}
			</div>
		</div>

		<!-- PDF Section Controls -->
		<div class="mb-4">
			<div class="mb-2 flex items-center justify-between">
				<h3 class="text-sm font-medium text-gray-700">Sections to Include</h3>
				<div class="flex items-center space-x-4">
					<button
						onclick={() => toggleAllSections(true)}
						class="text-sm text-indigo-600 hover:text-indigo-800">Select All</button
					>
					<button
						onclick={() => toggleAllSections(false)}
						class="text-sm text-indigo-600 hover:text-indigo-800">Deselect All</button
					>
				</div>
			</div>
			<div class="grid grid-cols-1 gap-3 md:grid-cols-3">
				{#each Object.keys(pdfConfig.sections) as section}
					<div class="relative flex items-start">
						<div class="flex h-5 items-center">
							<input
								id={`section-${section}`}
								type="checkbox"
								checked={pdfConfig.sections[section as SectionName]}
								onchange={(e) =>
									updateSectionVisibility(section as SectionName, e.currentTarget.checked)}
								class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
							/>
						</div>
						<div class="ml-3 text-sm">
							<label for={`section-${section}`} class="font-medium text-gray-700">
								{formatSectionName(section)}
							</label>
						</div>
					</div>
				{/each}
			</div>
		</div>

		<!-- Photo Inclusion Option -->
		<div class="mb-4">
			<div class="flex items-center">
				<input
					id="include-photo"
					type="checkbox"
					checked={pdfConfig.includePhoto}
					onchange={(e) => {
						pdfConfig = {
							...pdfConfig,
							includePhoto: e.currentTarget.checked
						};
					}}
					class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
				/>
				<div class="ml-3 text-sm">
					<label for="include-photo" class="font-medium text-gray-700">
						Include Profile Photo
					</label>
					<p class="text-xs text-gray-500">
						Uncheck to exclude your profile photo from the PDF while keeping other profile
						information
					</p>
				</div>
			</div>
		</div>

		<!-- Export PDF Button -->
		<div class="mt-4">
			{#if canExportPdf()}
				<button
					onclick={generatePdf}
					disabled={generatingPdf}
					class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
				>
					{#if generatingPdf}
						<svg
							class="mr-2 -ml-1 h-5 w-5 animate-spin text-white"
							xmlns="http://www.w3.org/2000/svg"
							fill="none"
							viewBox="0 0 24 24"
						>
							<circle
								class="opacity-25"
								cx="12"
								cy="12"
								r="10"
								stroke="currentColor"
								stroke-width="4"
							/>
							<path
								class="opacity-75"
								fill="currentColor"
								d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
							/>
						</svg>
						Generating PDF...
					{:else}
						<svg
							class="mr-2 -ml-1 h-5 w-5"
							xmlns="http://www.w3.org/2000/svg"
							viewBox="0 0 20 20"
							fill="currentColor"
						>
							<path
								fill-rule="evenodd"
								d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
								clip-rule="evenodd"
							/>
						</svg>
						Export as PDF
					{/if}
				</button>
			{:else}
				<div class="rounded-md border border-indigo-100 bg-indigo-50 p-4">
					<div class="flex">
						<div class="flex-shrink-0">
							<svg
								class="h-5 w-5 text-indigo-600"
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 20 20"
								fill="currentColor"
							>
								<path
									fill-rule="evenodd"
									d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
									clip-rule="evenodd"
								/>
							</svg>
						</div>
						<div class="ml-3">
							<h3 class="text-sm font-medium text-indigo-800">Premium Feature</h3>
							<div class="mt-2 text-sm text-indigo-700">
								<p>PDF export is available with our Premium subscription.</p>
							</div>
							<div class="mt-3">
								<button
									onclick={() => handleUpgradePrompt('pdf_export')}
									class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
								>
									Upgrade to Premium
								</button>
							</div>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>

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
	{:else if profile && dataLoaded && !profile.first_name && !profile.last_name && !profile.full_name}
		<div class="rounded bg-yellow-100 p-6 shadow-md">
			<h2 class="mb-2 text-xl font-semibold text-yellow-800">
				Your profile needs more information
			</h2>
			<p class="mb-4">
				Your CV profile is missing essential information. Please add at least your name to create a
				proper CV.
			</p>
			<div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-4">
				<a
					href="/profile"
					class="inline-block rounded bg-indigo-600 px-6 py-2 text-center font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
				>
					Complete Your Profile
				</a>
				<button
					onclick={() => (error = null)}
					class="inline-block rounded border border-gray-300 bg-white px-6 py-2 text-center font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-400 focus:outline-none"
				>
					View CV Anyway
				</button>
			</div>
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
							{#if profile.linkedin_url}
								<p>
									<a
										href={profile.linkedin_url}
										target="_blank"
										rel="noopener noreferrer"
										class="text-indigo-600 hover:underline"
									>
										LinkedIn Profile
									</a>
								</p>
							{/if}
						</div>

						{#if profile.bio && profile.bio.trim()}
							<div class="mt-4 text-gray-700">
								<p>{decodeHtmlEntities(profile.bio)}</p>
							</div>
						{/if}
					</div>

					{#if profile.photo_url && !photoLoadError}
						<div class="h-28 w-28 overflow-hidden rounded-full">
							<img
								src={getProxiedPhotoUrl(profile.photo_url)}
								alt={profile.full_name || 'Profile picture'}
								class="h-full w-full object-cover"
								onerror={handleImageError}
							/>
						</div>
					{/if}
				</div>
			</div>

			<!-- Preview content -->
			<div class="rounded-b-lg bg-white p-6 shadow-md">
				<div class="space-y-8">
					<!-- Professional Summary Section -->
					{#if professionalSummary && (professionalSummary.description || (professionalSummary.professional_summary_strengths && professionalSummary.professional_summary_strengths.length > 0))}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Professional Summary</h2>

							{#if professionalSummary.description}
								<div class="mb-4 leading-relaxed text-gray-700">
									{@html formatDescriptionWithFormatting(
										decodeHtmlEntities(professionalSummary.description)
									)}
								</div>
							{/if}

							{#if professionalSummary.professional_summary_strengths && professionalSummary.professional_summary_strengths.length > 0}
								<div class="mb-4">
									<h3 class="mb-2 text-lg font-semibold text-gray-800">Key Strengths</h3>
									<ul class="list-inside list-disc space-y-1 text-gray-700">
										{#each getSortedStrengths(professionalSummary) as strength}
											<li>{decodeHtmlEntities(strength.strength)}</li>
										{/each}
									</ul>
								</div>
							{/if}
						</section>
					{/if}

					<!-- Work Experience Section -->
					{#if workExperiences.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Work Experience</h2>
							{#each workExperiences as job, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<div class="mb-2 md:flex md:justify-between">
										<div>
											<h3 class="text-xl font-bold text-gray-800">
												{decodeHtmlEntities(job.position)}
											</h3>
											<div class="text-lg font-semibold text-gray-700">
												{decodeHtmlEntities(job.company_name)}
											</div>
										</div>
										{#if !job.hide_date}
											<div class="mt-2 text-gray-600 md:mt-0 md:text-right">
												{formatDateWithPreference(job.start_date, getDateFormatPreference(profile))}
												-
												{formatDateWithPreference(job.end_date, getDateFormatPreference(profile))}
											</div>
										{/if}
									</div>
									{#if job.description}
										<div class="my-3 space-y-2 text-gray-700">
											{#each formatDescriptionWithFormatting(decodeHtmlEntities(job.description)) as paragraph}
												<div>{@html paragraph}</div>
											{/each}
										</div>
									{/if}
									{#if job.responsibilities && job.responsibilities.length > 0}
										<div class="mt-4">
											<ResponsibilitiesEditor
												responsibilities={job.responsibilities}
												readOnly={true}
											/>
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}

					<!-- Qualification Equivalence Section -->
					{#if qualificationEquivalence.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Qualification Equivalence</h2>
							{#each qualificationEquivalence as qual, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<h3 class="text-xl font-bold text-gray-800">
										{decodeHtmlEntities(qual.qualification || qual.level)}
									</h3>
									{#if qual.equivalent_to && qual.equivalent_to !== 'NULL'}
										<div class="text-lg font-semibold text-gray-700">
											Equivalent to: {decodeHtmlEntities(qual.equivalent_to)}
										</div>
									{/if}
									{#if qual.description}
										<div class="my-3 text-gray-700">
											{#each formatDescription(decodeHtmlEntities(qual.description)) as paragraph}
												<p>{paragraph}</p>
											{/each}
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}

					<!-- Education Section -->
					{#if education.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Education</h2>
							{#each education as edu, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<div class="mb-2 md:flex md:justify-between">
										<div>
											<h3 class="text-xl font-bold text-gray-800">
												{decodeHtmlEntities(edu.institution)}
											</h3>
											{#if edu.degree}
												<div class="text-lg font-semibold text-gray-700">
													{decodeHtmlEntities(edu.degree)}
												</div>
											{:else if edu.course}
												<div class="text-lg font-semibold text-gray-700">
													{decodeHtmlEntities(edu.course)}
												</div>
											{/if}
										</div>
										<div class="mt-2 text-gray-600 md:mt-0 md:text-right">
											{formatDateWithPreference(edu.start_date, getDateFormatPreference(profile))} -
											{formatDateWithPreference(edu.end_date, getDateFormatPreference(profile))}
										</div>
									</div>
									{#if edu.description}
										<div class="my-3 text-gray-700">
											{#each formatDescription(decodeHtmlEntities(edu.description)) as paragraph}
												<p>{paragraph}</p>
											{/each}
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}

					<!-- Skills Section -->
					{#if skills.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Skills</h2>

							{#if skills.length > 0}
								{#each getCategorizedSkills(skills) as { category, skills: skillList }}
									<div class="mb-4">
										<h3 class="mb-2 text-lg font-semibold text-gray-700">
											{decodeHtmlEntities(category)}
										</h3>
										<div class="flex flex-wrap gap-2">
											{#each skillList as skill}
												<div
													class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800"
												>
													{decodeHtmlEntities(skill.name)}
													{#if skill.level}
														<span class="text-indigo-600">({decodeHtmlEntities(skill.level)})</span>
													{/if}
												</div>
											{/each}
										</div>
									</div>
								{/each}
							{/if}
						</section>
					{/if}

					<!-- Projects Section -->
					{#if projects.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Projects</h2>
							{#each projects as project, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<div class="mb-2 md:flex md:justify-between">
										<div>
											<h3 class="text-xl font-bold text-gray-800">
												{decodeHtmlEntities(project.title)}
											</h3>
											{#if project.url}
												<a
													href={project.url}
													target="_blank"
													class="text-indigo-600 hover:text-indigo-800 hover:underline"
													>{decodeHtmlEntities(project.url)}</a
												>
											{/if}
										</div>
										{#if project.start_date}
											<div class="mt-2 text-gray-600 md:mt-0 md:text-right">
												{formatDateWithPreference(
													project.start_date,
													getDateFormatPreference(profile)
												)} - {formatDateWithPreference(
													project.end_date,
													getDateFormatPreference(profile)
												)}
											</div>
										{/if}
									</div>
									{#if project.description}
										<div class="my-3 text-gray-700">
											{#each formatDescription(decodeHtmlEntities(project.description)) as paragraph}
												<p>{paragraph}</p>
											{/each}
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}

					<!-- Certifications Section -->
					{#if certifications.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Certifications</h2>
							{#each certifications as cert, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<div class="mb-2 md:flex md:justify-between">
										<div>
											<h3 class="text-xl font-bold text-gray-800">
												{decodeHtmlEntities(cert.name)}
											</h3>
											{#if cert.issuer}
												<div class="text-lg font-semibold text-gray-700">
													{decodeHtmlEntities(cert.issuer)}
												</div>
											{/if}
											{#if cert.url}
												<a
													href={cert.url}
													target="_blank"
													class="text-indigo-600 hover:text-indigo-800 hover:underline"
													>{decodeHtmlEntities(cert.url)}</a
												>
											{/if}
										</div>
										<div class="mt-2 text-gray-600 md:mt-0 md:text-right">
											{#if cert.date_issued}
												Issued: {formatDateWithPreference(
													cert.date_issued,
													getDateFormatPreference(profile)
												)}
												{#if cert.expiry_date}
													<br />Expires: {formatDateWithPreference(
														cert.expiry_date,
														getDateFormatPreference(profile)
													)}
												{/if}
											{/if}
										</div>
									</div>
									{#if cert.description}
										<div class="my-3 text-gray-700">
											{#each formatDescription(decodeHtmlEntities(cert.description)) as paragraph}
												<p>{paragraph}</p>
											{/each}
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}

					<!-- Professional Memberships Section -->
					{#if memberships.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Professional Memberships</h2>
							{#each memberships as membership, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<div class="mb-2 md:flex md:justify-between">
										<div>
											<h3 class="text-xl font-bold text-gray-800">
												{decodeHtmlEntities(membership.organisation)}
											</h3>
											{#if membership.role}
												<div class="text-lg font-semibold text-gray-700">
													{decodeHtmlEntities(membership.role)}
												</div>
											{/if}
										</div>
										{#if membership.start_date}
											<div class="mt-2 text-gray-600 md:mt-0 md:text-right">
												{formatDateWithPreference(
													membership.start_date,
													getDateFormatPreference(profile)
												)} - {formatDateWithPreference(
													membership.end_date,
													getDateFormatPreference(profile)
												)}
											</div>
										{/if}
									</div>
									{#if membership.description}
										<div class="my-3 text-gray-700">
											{#each formatDescription(decodeHtmlEntities(membership.description)) as paragraph}
												<p>{paragraph}</p>
											{/each}
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}

					<!-- Interests Section -->
					{#if interests.length > 0}
						<section>
							<h2 class="mb-4 text-2xl font-bold text-gray-800">Interests & Activities</h2>
							{#each interests as interest, i}
								<div class="mb-6 border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
									<h3 class="text-xl font-bold text-gray-800">
										{decodeHtmlEntities(interest.name)}
									</h3>
									{#if interest.description}
										<div class="my-3 text-gray-700">
											{#each formatDescription(decodeHtmlEntities(interest.description)) as paragraph}
												<p>{paragraph}</p>
											{/each}
										</div>
									{/if}
								</div>
							{/each}
						</section>
					{/if}
				</div>
			</div>
		</div>
	{/if}

	<div class="mt-6">
		<SectionNavigation />
	</div>
</div>
