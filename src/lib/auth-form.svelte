<script lang="ts">
	import { login, signup, createProfile } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import { browser } from '$app/environment';

	// Use $props() instead of export let
	const { redirectTo = '/' } = $props();

	let email = $state('');
	let password = $state('');
	let isSignUp = $state(false);
	let loading = $state(false);
	let error = $state('');
	let success = $state('');

	async function handleAuth(e: Event) {
		e.preventDefault();
		loading = true;
		error = '';
		success = '';

		try {
			if (isSignUp) {
				// Sign up flow
				const result = await signup(email, password);

				if (result && result.user) {
					// Create profile for the new user
					try {
						// Add a delay to ensure the user is fully registered in Supabase
						await new Promise((resolve) => setTimeout(resolve, 500));

						const profileResult = await createProfile(result.user.id, email);

						// Use type assertion based on API response structure
						if ((profileResult as any).success) {
							console.log('Profile created successfully on signup');
							success = 'Account created! Redirecting...';
						} else {
							console.error('Error creating profile:', (profileResult as any).error);
							// Continue anyway, we can create profile later
							success = 'Account created but profile setup failed. Redirecting...';
						}
					} catch (profileErr: any) {
						console.error('Exception creating profile:', profileErr);
						// Continue anyway, we can create profile later
						success = 'Account created but profile setup failed. Redirecting...';
					}

					// Set a flag to indicate we just authenticated (for layout to detect)
					if (browser) {
						sessionStorage.setItem('just_authenticated', 'true');
					}

					// Wait a moment before redirecting to ensure cookies are set
					setTimeout(() => {
						// Force page reload to ensure all components get the latest session
						window.location.href = '/profile';
					}, 1500);
				}
			} else {
				// Login flow
				const result = await login(email, password);

				// Set a flag to indicate we just authenticated (for layout to detect)
				if (browser) {
					sessionStorage.setItem('just_authenticated', 'true');
				}

				// Wait a moment before redirecting to ensure cookies are set
				success = 'Logged in! Redirecting...';
				setTimeout(() => {
					// Force a complete page reload rather than navigation
					window.location.href = redirectTo;
				}, 1500);
			}
		} catch (err: any) {
			console.error('Auth error:', err);
			error = err.message || 'Authentication failed';
		} finally {
			loading = false;
		}
	}

	function toggleSignUpMode() {
		isSignUp = !isSignUp;
	}
</script>

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
	<div class="sm:mx-auto sm:w-full sm:max-w-sm">
		<h2 class="mt-10 text-center text-2xl leading-9 font-bold tracking-tight text-gray-900">
			{isSignUp ? 'Create your account' : 'Sign in to your account'}
		</h2>
	</div>

	<div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
		<form class="space-y-6" onsubmit={handleAuth}>
			<div>
				<label for="email" class="block text-sm leading-6 font-medium text-gray-900"
					>Email address</label
				>
				<div class="mt-2">
					<input
						id="email"
						name="email"
						type="email"
						bind:value={email}
						required
						class="block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:ring-inset sm:text-sm sm:leading-6"
					/>
				</div>
			</div>

			<div>
				<div class="flex items-center justify-between">
					<label for="password" class="block text-sm leading-6 font-medium text-gray-900"
						>Password</label
					>
				</div>
				<div class="mt-2">
					<input
						id="password"
						name="password"
						type="password"
						bind:value={password}
						required
						class="block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:ring-inset sm:text-sm sm:leading-6"
					/>
				</div>
			</div>

			{#if error}
				<div class="text-sm text-red-500">{error}</div>
			{/if}

			{#if success}
				<div class="text-sm text-green-500">{success}</div>
			{/if}

			<div>
				<button
					type="submit"
					disabled={loading}
					class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm leading-6 font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-70"
				>
					{loading ? 'Processing...' : isSignUp ? 'Sign up' : 'Sign in'}
				</button>
			</div>
		</form>

		<div class="mt-4 text-center">
			<button
				onclick={toggleSignUpMode}
				class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
			>
				{isSignUp ? 'Already have an account? Sign in' : "Don't have an account? Sign up"}
			</button>
		</div>
	</div>
</div>
