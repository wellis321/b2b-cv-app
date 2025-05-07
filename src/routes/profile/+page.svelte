<script lang="ts">
	import { onMount, onDestroy } from 'svelte';
	import { goto } from '$app/navigation';
	import { browser } from '$app/environment';
	import { session, updateProfile } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';
	import { updateSectionStatus } from '$lib/cv-sections';

	let { data, form } = $props();
	let fullName = $state(data.profile?.full_name ?? '');
	let email = $state(data.profile?.email ?? '');
	let phone = $state(data.profile?.phone ?? '');
	let location = $state(data.profile?.location ?? '');
	let error = $state<string | null>(null); // Don't initialize with server error
	let success = $state<string | null>(null);
	let loading = $state(false);
	let initialCheckDone = $state(false);
	let loadingProfile = $state(true); // Add loading state for profile

	// Check authentication on mount and try to initialize data
	onMount(async () => {
		console.log('Profile page mounted');
		console.log('Store session:', $session ? `User ID: ${$session.user.id}` : 'Missing');
		console.log('Data session:', data.session ? `User ID: ${data.session.user.id}` : 'Missing');

		// Check for authentication
		if (!data.session && !$session) {
			console.log('No session found on profile page mount');
			error = 'Not authenticated. Please login first.';
			loadingProfile = false;

			// If in browser, redirect to home
			if (browser) {
				setTimeout(() => {
					goto('/');
				}, 2000);
			}
		} else {
			// We have a session, try to load profile if it wasn't loaded from server
			if ((!data.profile || data.error) && ($session || data.session)) {
				try {
					console.log('Trying to load profile from client');
					const userId = $session?.user.id || data.session?.user.id;

					if (userId) {
						console.log('Fetching profile for user ID:', userId);

						// Create a proper query with explicit headers
						const { data: profileData, error: profileError } = await supabase
							.from('profiles')
							.select('*')
							.eq('id', userId)
							.maybeSingle(); // Use maybeSingle instead of single to avoid 406 errors

						console.log(
							'Profile fetch result:',
							profileData || 'No data',
							profileError || 'No error'
						);

						if (profileError) {
							// Check if it's just a "no rows" error, which is expected for new users
							if (profileError.code === 'PGRST116') {
								console.log('No profile found for user - this is normal for new users');

								// Set email from session if available
								if ($session?.user?.email) {
									email = $session.user.email;
								}
							} else {
								console.error('Error loading profile from client:', profileError);
								error = 'Error loading profile. Please try again.';
							}
						} else if (profileData) {
							console.log('Profile loaded from client:', profileData);
							// Update form fields with profile data
							fullName = profileData.full_name || '';
							email = profileData.email || '';
							phone = profileData.phone || '';
							location = profileData.location || '';
							// Clear any error
							error = null;
						} else {
							console.log('No profile data found, but no error either');
							// Set email from session if available
							if ($session?.user?.email) {
								email = $session.user.email;
							}
						}
					}
				} catch (err) {
					console.error('Error in client-side profile load:', err);
					error = 'Error loading profile. Please refresh the page.';
				} finally {
					loadingProfile = false;
				}
			} else if (data.profile) {
				// Profile was loaded from server
				loadingProfile = false;
				error = null; // Clear any errors from the server
			}
		}

		initialCheckDone = true;
	});

	// Subscribe to auth state changes
	$effect(() => {
		if (!initialCheckDone) return;

		// If session changes after initial check, update UI accordingly
		if (!$session) {
			console.log('Session lost during profile page lifecycle');
			error = 'Session lost. Please login again.';
			if (browser) {
				setTimeout(() => {
					goto('/');
				}, 2000);
			}
		} else {
			console.log('Session available during profile page lifecycle');
			// Clear error if it was auth-related
			if (
				error === 'Not authenticated. Please login first.' ||
				error === 'Session lost. Please login again.'
			) {
				error = null;
			}
		}
	});

	async function saveProfile(e: Event) {
		e.preventDefault();
		// Double-check authentication
		if (!$session) {
			error = 'Not authenticated. Please login first.';
			return;
		}

		loading = true;
		error = null;
		success = null;

		try {
			// Use session from store
			const userId = $session.user.id;
			const accessToken = $session.access_token;

			// Ensure we have a user ID and token
			if (!userId || !accessToken) {
				error = 'User ID or token not found. Please log in again.';
				loading = false;
				return;
			}

			// Prepare profile data
			const profileData = {
				id: userId,
				full_name: fullName,
				email,
				phone,
				location
			};

			console.log('Saving profile data:', profileData);

			// Use the updateProfile helper from authStore
			const result = await updateProfile(profileData);

			if (!result.success) {
				error = result.error || 'Failed to save profile';
				console.error('Error saving profile:', result.error);
			} else {
				success = 'Profile saved successfully!';
				console.log('Profile saved successfully:', result.profile);

				// Update local state with the returned profile data
				if (result.profile && result.profile.length > 0) {
					const savedProfile = result.profile[0];
					fullName = savedProfile.full_name || fullName;
					email = savedProfile.email || email;
					phone = savedProfile.phone || phone;
					location = savedProfile.location || location;
				}

				// Update section status to reflect the profile completion
				await updateSectionStatus();
			}
		} catch (err) {
			console.error('Error saving profile:', err);
			error = 'An unexpected error occurred';
		} finally {
			loading = false;
		}
	}
</script>

<div class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Profile</h2>
	{#if error}
		<div class="rounded bg-red-100 p-4 text-red-700">{error}</div>
	{/if}
	{#if success}
		<div class="rounded bg-green-100 p-4 text-green-700">{success}</div>
	{/if}

	{#if (!data.session && !$session) || loading}
		<div class="rounded bg-yellow-100 p-4">
			<p class="font-medium">
				{loading ? 'Loading...' : 'You need to be logged in to edit your profile.'}
			</p>
			{#if !loading}
				<button
					onclick={() => goto('/')}
					class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					Go to Login
				</button>
			{/if}
		</div>
	{:else if loadingProfile}
		<div class="rounded bg-blue-100 p-4">
			<p class="font-medium">Loading your profile...</p>
		</div>
	{:else}
		<form onsubmit={saveProfile} class="space-y-6">
			<div>
				<label class="mb-1 block text-sm font-medium text-gray-700" for="fullName">Full Name</label>
				<input
					id="fullName"
					name="fullName"
					type="text"
					bind:value={fullName}
					class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
					required
				/>
			</div>
			<div>
				<label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
				<input
					id="email"
					name="email"
					type="email"
					bind:value={email}
					class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
					required
				/>
			</div>
			<div>
				<label class="mb-1 block text-sm font-medium text-gray-700" for="phone">Phone</label>
				<input
					id="phone"
					name="phone"
					type="tel"
					bind:value={phone}
					class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
				/>
			</div>
			<div>
				<label class="mb-1 block text-sm font-medium text-gray-700" for="location">Location</label>
				<input
					id="location"
					name="location"
					type="text"
					bind:value={location}
					class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
				/>
			</div>
			<div>
				<button
					type="submit"
					disabled={loading}
					class="w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
				>
					{loading ? 'Saving...' : 'Save Profile'}
				</button>
			</div>
		</form>
	{/if}

	<SectionNavigation />
</div>
