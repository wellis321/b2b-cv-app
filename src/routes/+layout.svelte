<script lang="ts">
	import '../app.css';
	import AuthForm from '$lib/auth-form.svelte';
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { session, initializeSession, setupAuthListener, logout } from '$lib/stores/authStore';
	import { browser } from '$app/environment';

	// State for storing the current user's username
	let username = $state<string | null>(null);

	// Setup auth on mount
	onMount(() => {
		console.log('Layout mounted, initializing session...');

		if (browser) {
			// Force refresh page if coming from login/signup
			const fromAuth = sessionStorage.getItem('just_authenticated');
			if (fromAuth) {
				console.log('Detected authentication redirect, clearing flag');
				sessionStorage.removeItem('just_authenticated');
			}

			// Initialize session
			initializeSession()
				.then(() => {
					console.log('Session initialized in layout:', $session ? 'Present' : 'None');
					fetchUsername();
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

	// Update username when session changes
	$effect(() => {
		if ($session?.user?.id) {
			fetchUsername();
		} else {
			username = null;
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
</script>

<div class="min-h-screen bg-gray-50">
	<header class="bg-white shadow">
		<nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
			<div class="flex h-16 justify-between">
				<div class="flex">
					<div class="flex flex-shrink-0 items-center">
						<a href="/" class="text-xl font-bold text-gray-900">CV Builder</a>
					</div>
				</div>
				<div class="flex items-center gap-4">
					<a href="/security-review-client" class="text-sm text-indigo-600 hover:text-indigo-800"
						>Security Review</a
					>
					{#if $session}
						<a href="/profile" class="text-gray-600 hover:text-gray-900">Profile</a>
						<a href="/" class="text-gray-600 hover:text-gray-900">Edit CV Sections</a>
						<a href="/preview-cv" class="text-gray-600 hover:text-gray-900">Preview & PDF</a>
						{#if username}
							<a href="/cv/@{username}" class="text-gray-600 hover:text-gray-900">View CV</a>
						{/if}
						<button on:click={signOut} class="text-gray-600 hover:text-gray-900">Sign Out</button>
					{/if}
				</div>
			</div>
		</nav>
	</header>

	<main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
		{#if !$session}
			<AuthForm />
		{:else}
			<slot></slot>
		{/if}
	</main>
</div>
