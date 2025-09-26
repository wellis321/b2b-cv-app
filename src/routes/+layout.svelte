<script lang="ts">
	import '../app.css';
	import AuthForm from '$lib/auth-form.svelte';
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { session, initializeSession, setupAuthListener, logout } from '$lib/stores/authStore';
	import { browser } from '$app/environment';
	import ErrorBoundary from '$lib/components/ErrorBoundary.svelte';
	import { page } from '$app/stores';
	import { initGlobalHelpers } from '$lib/utils/globalHelpers';
	import ContentWrapper from '$lib/components/ContentWrapper.svelte';
	import { initializeSubscription } from '$lib/stores/subscriptionStore';
	import AnalyticsTracker from '$lib/components/AnalyticsTracker.svelte';
	import { isAdminUser } from '$lib/adminConfig';
	import { initializeCsrfToken } from '$lib/security/clientCsrf';

	// Initialize global helpers
	if (browser) {
		initGlobalHelpers();
	}

	// State for storing the current user's username
	let username = $state<string | null>(null);
	let isAdmin = $state(false);

	// Navigation items with their paths
	const navItems = [
		{ name: 'Profile', path: '/profile', forceReload: false },
		{ name: 'Edit CV Sections', path: '/dashboard', forceReload: false },
		{ name: 'Preview & PDF', path: '/preview-cv', forceReload: false },
		{ name: 'Subscription', path: '/subscription', forceReload: false },
		{ name: 'Early Access', path: '/early-access', forceReload: false }
	];

	// Admin navigation items - only visible to admins
	const adminNavItems = [
		{ name: 'Analytics', path: '/admin/analytics', forceReload: false },
		{ name: 'Feedback', path: '/admin/feedback', forceReload: false }
	];

	// Function to check if a path is active
	function isActive(path: string): boolean {
		if (path === '/') {
			// Special case for home page
			return (
				$page.url.pathname === '/' ||
				// Check if we're on one of the CV section pages
				[
					'work-experience',
					'education',
					'projects',
					'skills',
					'certifications',
					'qualification-equivalence',
					'memberships',
					'interests'
				].some((section) => $page.url.pathname.includes(section))
			);
		}

		if (path === '/dashboard') {
			return (
				$page.url.pathname === '/dashboard' ||
				// Also consider CV section pages as part of dashboard
				[
					'work-experience',
					'education',
					'projects',
					'skills',
					'certifications',
					'qualification-equivalence',
					'memberships',
					'interests'
				].some((section) => $page.url.pathname.includes(section))
			);
		}

		return $page.url.pathname.startsWith(path);
	}

	// Function to check if current page is a public CV profile page
	function isPublicCvPage(): boolean {
		// Check if we're on any CV page - including @username routes
		return $page.url.pathname.startsWith('/cv/');
	}

	// Setup auth on mount
	onMount(() => {
		console.log('Layout mounted, initializing session...');

		if (browser) {
			// Initialize CSRF token
			initializeCsrfToken();

			// Initialize subscription system
			initializeSubscription();

			// Check if we're on a public CV page and skip auth if so
			if (isPublicCvPage() && $page.url.pathname.includes('/cv/@')) {
				console.log('Public CV page detected, skipping authentication');
				return;
			}

			// Force refresh page if coming from login/signup
			const fromAuth = sessionStorage.getItem('just_authenticated');
			if (fromAuth) {
				console.log('Detected authentication redirect, clearing flag');
				sessionStorage.removeItem('just_authenticated');
			}

			// Initialize session with force refresh to ensure token validity
			initializeSession(true)
				.then(() => {
					console.log('Session initialized in layout:', $session ? 'Present' : 'None');
					fetchUsername();
					checkAdmin();
				})
				.catch((err) => {
					console.error('Error initializing session in layout:', err);
				});

			// Set up auth listener and return cleanup function
			const unsubscribe = setupAuthListener();
			return unsubscribe;
		}
	});

	// Fetch the current user's username
	async function fetchUsername() {
		if ($session?.user?.id) {
			try {
				// Import supabase only when needed to avoid issues with SSR
				const { supabase } = await import('$lib/supabase');
				const { data } = await supabase
					.from('profiles')
					.select('username')
					.eq('id', $session.user.id)
					.single();

				username = data?.username || null;
			} catch (err) {
				console.error('Error fetching username:', err);
			}
		}
	}

	// Check if the current user is an admin
	function checkAdmin() {
		if ($session?.user) {
			// Use the isAdminUser helper function from adminConfig
			isAdmin = isAdminUser($session.user.email);
		} else {
			isAdmin = false;
		}
	}

	// Update username when session changes
	$effect(() => {
		if ($session?.user?.id) {
			fetchUsername();
			checkAdmin();
		} else {
			username = null;
			isAdmin = false;
		}
	});

	// Handle sign out
	async function signOut() {
		console.log('Signing out...');
		try {
			await logout();
			// Force page reload after logout to clear any cached state
			if (browser) {
				window.location.href = '/';
			} else {
				goto('/');
			}
		} catch (err) {
			console.error('Error during sign out:', err);
		}
	}

	// A function to handle navigation with optional force reload
	function handleNavigation(path: string, forceReload: boolean = false) {
		if (forceReload) {
			window.location.href = path;
		} else {
			goto(path);
		}
	}
</script>

<div class="min-h-screen bg-gray-50">
	<!-- Track page visits with the AnalyticsTracker component -->
	<AnalyticsTracker />

	<header class="bg-white shadow">
		<nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
			<div class="flex h-16 justify-between">
				<div class="flex">
					<div class="flex flex-shrink-0 items-center">
						<a href="/" class="text-xl font-bold text-gray-900">CV Builder</a>
					</div>
				</div>
				<div class="flex items-center gap-4">
					{#if $session}
						{#each navItems as item}
							<a
								href={item.path}
								class={isActive(item.path)
									? 'border-b-2 border-indigo-600 font-medium text-indigo-800 transition-colors hover:text-indigo-900'
									: 'text-gray-600 transition-colors hover:border-b-2 hover:border-gray-300 hover:text-gray-900'}
							>
								{item.name}
							</a>
						{/each}
						{#if isAdmin}
							{#each adminNavItems as item}
								<a
									href={item.path}
									class={isActive(item.path)
										? 'border-b-2 border-purple-600 font-medium text-purple-800 transition-colors hover:text-purple-900'
										: 'text-purple-600 transition-colors hover:border-b-2 hover:border-purple-300 hover:text-purple-900'}
								>
									{item.name}
								</a>
							{/each}
						{/if}
						{#if username}
							<a
								href="/cv/@{username}"
								class={$page.url.pathname.includes('/cv/@')
									? 'border-b-2 border-indigo-600 font-medium text-indigo-800 transition-colors hover:text-indigo-900'
									: 'text-gray-600 transition-colors hover:border-b-2 hover:border-gray-300 hover:text-gray-900'}
							>
								View CV
							</a>
						{/if}
						<button
							onclick={signOut}
							class="text-gray-600 transition-colors hover:border-b-2 hover:border-gray-300 hover:text-gray-900"
							>Sign Out</button
						>
					{/if}
				</div>
			</div>
		</nav>
	</header>

	<main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
		{#if !$session && !isPublicCvPage() && $page.url.pathname !== '/'}
			<AuthForm />
		{:else}
			<ContentWrapper>
				<slot />
			</ContentWrapper>
		{/if}
	</main>
</div>
