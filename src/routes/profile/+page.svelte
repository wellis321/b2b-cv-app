<script lang="ts">
	import { onMount, onDestroy } from 'svelte';
	import { goto } from '$app/navigation';
	import { browser } from '$app/environment';
	import { session, updateProfile } from '$lib/stores/authStore';
	import { supabase } from '$lib/supabase';
	import { uploadFile, deleteFile, getPathFromUrl, fileExists } from '$lib/fileUpload';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';
	import { updateSectionStatus } from '$lib/cv-sections';
	import CameraCapture from '$lib/components/CameraCapture.svelte';
	import PhotoFallback from '$lib/components/PhotoFallback.svelte';
	import { fetchWithCsrf } from '$lib/security/clientCsrf';

	let { data, form } = $props();
	let fullName = $state(data.profile?.full_name ?? '');
	let username = $state(data.profile?.username ?? '');
	let email = $state(data.profile?.email ?? '');
	let phone = $state(data.profile?.phone ?? '');
	let location = $state(data.profile?.location ?? '');
	let photoUrl = $state(data.profile?.photo_url ?? '');
	let error = $state<string | null>(null);
	let success = $state<string | null>(null);
	let loading = $state(false);
	let uploadingPhoto = $state(false);
	let photoError = $state<string | null>(null);
	let initialCheckDone = $state(false);
	let loadingProfile = $state(true);
	let photoInputEl = $state<HTMLInputElement | null>(null);
	let showCamera = $state(false);
	let storageAvailable = $state(true);
	let cameraStatus = $state('Not initialized');
	let usernameError = $state<string | null>(null);
	let usernameAvailable = $state(true);
	let checkingUsername = $state(false);

	// File validation constants
	const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
	const ALLOWED_FILE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

	// Storage bucket name
	const PROFILE_PHOTOS_BUCKET = 'profile-photos';
	const DEFAULT_PROFILE_PHOTO = '/images/default-profile.svg';

	// Fix updateProfile return type
	interface ProfileUpdateResult {
		success: boolean;
		profile?: any;
		error?: string;
		message?: string;
		requestId?: string;
	}

	// Check authentication on mount and try to initialize data
	onMount(async () => {
		// Check for authentication
		if (!data.session && !$session) {
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
					const userId = $session?.user.id || data.session?.user.id;

					if (userId) {
						// Create a proper query with explicit headers
						const { data: profileData, error: profileError } = await supabase
							.from('profiles')
							.select('*')
							.eq('id', userId)
							.maybeSingle();

						if (profileError) {
							// Check if it's just a "no rows" error, which is expected for new users
							if (profileError.code === 'PGRST116') {
								// Set email from session if available
								if ($session?.user?.email) {
									email = $session.user.email;
								}
							} else {
								console.error('Error loading profile from client:', profileError);
								error = 'Error loading profile. Please try again.';
							}
						} else if (profileData) {
							// Update form fields with profile data
							fullName = profileData.full_name || '';
							username = profileData.username || '';
							email = profileData.email || '';
							phone = profileData.phone || '';
							location = profileData.location || '';
							photoUrl = profileData.photo_url || '';
							// Clear any error
							error = null;
						} else {
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
				// Update local state with profile data
				photoUrl = data.profile.photo_url || '';
				loadingProfile = false;
				error = null; // Clear any errors from the server
			}
		}

		// Check bucket accessibility
		await checkBucketAccessibility();

		initialCheckDone = true;

		// Check if the photo URL is valid
		if (photoUrl) {
			const isValid = await validatePhotoUrl(photoUrl);
			if (!isValid) {
				console.warn('Photo URL validation failed, using default image');
			}
		}
	});

	// Username validation
	const validateUsername = async (value: string): Promise<boolean> => {
		// Clear previous errors
		usernameError = null;
		usernameAvailable = true;

		// Check if empty
		if (!value.trim()) {
			usernameError = 'Username is required';
			return false;
		}

		// Check length
		if (value.length < 3) {
			usernameError = 'Username must be at least 3 characters long';
			return false;
		}

		if (value.length > 30) {
			usernameError = 'Username must be less than 30 characters';
			return false;
		}

		// Check format (lowercase letters, numbers, hyphens, underscores)
		const validFormat = /^[a-z0-9][a-z0-9\-_]+$/.test(value);
		if (!validFormat) {
			usernameError =
				'Username can only contain lowercase letters, numbers, hyphens, and underscores, and must start with a letter or number';
			return false;
		}

		// Check availability from database
		try {
			checkingUsername = true;

			// Skip check if it's the user's current username
			if (data.profile?.username === value) {
				checkingUsername = false;
				return true;
			}

			const { data: existingUser, error: lookupError } = await supabase
				.from('profiles')
				.select('username')
				.eq('username', value)
				.single();

			if (existingUser) {
				usernameError = 'This username is already taken';
				usernameAvailable = false;
				return false;
			}

			if (lookupError && lookupError.code !== 'PGRST116') {
				// PGRST116 is the "no rows returned" error, which is what we want
				console.error('Error checking username:', lookupError);
				usernameError = 'Unable to verify username availability';
				return false;
			}

			// Username is available
			return true;
		} catch (err) {
			console.error('Error checking username availability:', err);
			usernameError = 'An error occurred checking username availability';
			return false;
		} finally {
			checkingUsername = false;
		}
	};

	// Handle username change
	async function handleUsernameChange() {
		if (username === data.profile?.username) {
			return; // No change, skip validation
		}

		await validateUsername(username);
	}

	// Handle captured photo from camera
	function handleCameraCapture(blob: Blob, url: string) {
		try {
			// Close camera
			showCamera = false;

			// Prepare the file with a proper name and format
			const file = new File([blob], `camera_capture_${Date.now()}.jpg`, { type: 'image/jpeg' });

			// Process the file
			uploadProfilePhoto(file);
		} catch (err) {
			console.error('Error processing camera capture:', err);
			photoError = `Error processing photo: ${err instanceof Error ? err.message : 'Unknown error'}`;
		}
	}

	// Handle camera errors
	function handleCameraError(error: string) {
		photoError = error;
		cameraStatus = `Error: ${error}`;
		console.error('Camera error:', error);
	}

	// Handle camera close
	function handleCameraClose() {
		showCamera = false;
		cameraStatus = 'Camera closed';
	}

	// Common photo upload function (used by both file input and camera)
	async function uploadProfilePhoto(file: File) {
		if (!$session) {
			photoError = 'You need to be logged in to upload a photo.';
			return;
		}

		if (!storageAvailable) {
			photoError =
				'Storage is not available. Please contact the administrator to set up the profile-photos bucket.';
			return;
		}

		// Validate file size
		if (file.size > MAX_FILE_SIZE) {
			photoError = `File is too large. Maximum size is ${MAX_FILE_SIZE / (1024 * 1024)}MB.`;
			// Reset input
			if (photoInputEl) photoInputEl.value = '';
			return;
		}

		// Validate file type
		if (!ALLOWED_FILE_TYPES.includes(file.type)) {
			photoError = 'Only JPEG, PNG, and WebP images are allowed.';
			// Reset input
			if (photoInputEl) photoInputEl.value = '';
			return;
		}

		// Clear any errors
		photoError = null;
		uploadingPhoto = true;

		try {
			// If there's already a photo, delete it
			if (photoUrl) {
				const path = getPathFromUrl(photoUrl, PROFILE_PHOTOS_BUCKET);
				if (path) {
					await deleteFile(PROFILE_PHOTOS_BUCKET, path);
				}
			}

			// Upload the new photo
			const result = await uploadFile($session.user.id, file, PROFILE_PHOTOS_BUCKET);

			if (!result.success) {
				// Check for specific error types
				if (result.error?.includes('bucket') || result.error?.includes('storage')) {
					console.error('Storage bucket error:', result.error);
					photoError =
						'Storage is not available. Please contact the administrator to set up the profile-photos bucket.';
					storageAvailable = false; // Mark storage as unavailable
				} else {
					photoError = result.error || 'Failed to upload photo.';
				}
				return;
			}

			// Update the photo URL - use the direct Supabase URL for storage, but display via proxy
			photoUrl = result.url || '';

			// Save the profile with the new photo URL using our special CSRF-exempt endpoint
			const updateResult = await updateProfilePhoto(photoUrl);

			if (!updateResult.success) {
				// Extract and display a more helpful error message
				let errorMessage = updateResult.error || 'Failed to update profile with new photo.';
				if (updateResult.message) {
					errorMessage += `: ${updateResult.message}`;
				}
				if (updateResult.requestId) {
					errorMessage += ` (Request ID: ${updateResult.requestId})`;
				}

				photoError = errorMessage;
				console.error('Error saving profile photo:', errorMessage);
			} else {
				success = 'Photo uploaded successfully!';

				// Clear success message after 3 seconds
				setTimeout(() => {
					success = null;
				}, 3000);
			}
		} catch (err) {
			console.error('Error uploading photo:', err);
			photoError = `Upload error: ${err instanceof Error ? err.message : 'Unknown error'}`;
		} finally {
			uploadingPhoto = false;
		}
	}

	// Handle file upload
	async function handlePhotoUpload(e: Event) {
		const input = e.target as HTMLInputElement;
		const file = input.files?.[0];

		if (!file) {
			photoError = 'No file selected.';
			return;
		}

		await uploadProfilePhoto(file);
	}

	// Delete profile photo
	async function deleteProfilePhoto() {
		if (!$session || !photoUrl) {
			return;
		}

		if (!storageAvailable) {
			photoError =
				'Storage is not available. Please contact the administrator to set up the profile-photos bucket.';
			return;
		}

		uploadingPhoto = true;
		photoError = null;

		try {
			// Extract path from URL
			const path = getPathFromUrl(photoUrl, PROFILE_PHOTOS_BUCKET);

			if (path) {
				// Delete the file from storage
				const deleteResult = await deleteFile(PROFILE_PHOTOS_BUCKET, path);

				if (!deleteResult.success) {
					photoError = deleteResult.error || 'Failed to delete photo.';
					return;
				}
			}

			// Update profile to remove photo URL using our special CSRF-exempt endpoint
			const updateResult = await updateProfilePhoto(null);

			if (!updateResult.success) {
				photoError = updateResult.error || 'Failed to update profile.';
				return;
			}

			// Clear photo URL
			photoUrl = '';
			success = 'Photo removed successfully!';

			// Clear success message after 3 seconds
			setTimeout(() => {
				success = null;
			}, 3000);
		} catch (err) {
			console.error('Error deleting photo:', err);
			photoError = 'An unexpected error occurred while deleting your photo.';
		} finally {
			uploadingPhoto = false;
		}
	}

	// Subscribe to auth state changes
	$effect(() => {
		if (!initialCheckDone) return;

		// If session changes after initial check, update UI accordingly
		if (!$session) {
			error = 'Session lost. Please login again.';
			if (browser) {
				setTimeout(() => {
					goto('/');
				}, 2000);
			}
		} else {
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
			const authEmail = $session.user.email; // Get email from auth session

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
				username,
				email: authEmail || email, // Prioritize auth email
				phone,
				location,
				photo_url: photoUrl
			};

			// Use our direct updateMinimalProfile function instead of authStore's updateProfile
			const result = await updateMinimalProfile(profileData);

			if (!result.success) {
				error = result.error || 'Failed to save profile';
				console.error('Error saving profile:', result.error);
			} else {
				// Refresh profile data to ensure everything is in sync
				await refreshProfileData();

				success = 'Profile saved successfully!';

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

	// Function to update profile with simplified structure
	async function updateMinimalProfile(partialData: Record<string, any>) {
		if (!$session) {
			return { success: false, error: 'Not authenticated' };
		}

		// Ensure we only include fields that definitely exist in the profiles table
		const profileData = {
			id: $session.user.id,
			...partialData,
			updated_at: new Date().toISOString()
		};

		// Use direct fetch to bypass authStore if needed
		try {
			// Re-fetch the CSRF token directly from the document to ensure it's current
			const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
			const csrfToken = csrfTokenElement ? (csrfTokenElement as HTMLMetaElement).content : null;

			if (!csrfToken) {
				console.error('CSRF token not found in document - updating metadata failed');
				return { success: false, error: 'Security token missing, please refresh the page' };
			}

			// Use fetchWithCsrf to ensure CSRF token is included
			const response = await fetchWithCsrf('/api/update-profile', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Authorization: `Bearer ${$session.access_token}`,
					'X-CSRF-Token': csrfToken // Explicitly include the CSRF token
				},
				body: JSON.stringify(profileData)
			});

			// Parse response as JSON
			let result;
			try {
				result = await response.json();
			} catch (jsonError) {
				console.error('Error parsing response as JSON:', jsonError);
				return {
					success: false,
					error: 'Failed to parse response from server'
				};
			}

			return result;
		} catch (error) {
			console.error('Error in minimal profile update:', error);
			return {
				success: false,
				error: error instanceof Error ? error.message : 'Unknown error in profile update'
			};
		}
	}

	// Function to refresh profile data from server
	async function refreshProfileData() {
		if (!$session) return;

		try {
			const { data: profileData, error: profileError } = await supabase
				.from('profiles')
				.select('*')
				.eq('id', $session.user.id)
				.maybeSingle();

			if (profileError) {
				console.error('Error refreshing profile:', profileError);
			} else if (profileData) {
				// Update local state
				fullName = profileData.full_name || fullName;
				username = profileData.username || username;
				email = profileData.email || email;
				phone = profileData.phone || phone;
				location = profileData.location || location;
				photoUrl = profileData.photo_url || photoUrl;
			}
		} catch (err) {
			console.error('Exception refreshing profile:', err);
		}
	}

	// Function to validate photo URL and handle errors
	async function validatePhotoUrl(url: string | null): Promise<boolean> {
		if (!url) return false;

		// Check if URL is a valid Supabase URL
		if (url.includes('supabase.co/storage') && url.includes(PROFILE_PHOTOS_BUCKET)) {
			const path = getPathFromUrl(url, PROFILE_PHOTOS_BUCKET);
			if (!path) return false;

			try {
				// Test accessing the file through our proxy
				const proxyUrl = `/api/storage-proxy?bucket=${PROFILE_PHOTOS_BUCKET}&path=${encodeURIComponent(path)}&t=${Date.now()}`;
				const response = await fetch(proxyUrl, { method: 'HEAD' });
				return response.ok;
			} catch (error) {
				console.error('Error validating photo URL via proxy:', error);
				// Fall back to fileExists if proxy fails
				return await fileExists(PROFILE_PHOTOS_BUCKET, path);
			}
		}

		return false;
	}

	// Function to get path from a Supabase URL for use with the proxy
	function getProxiedPhotoUrl(url: string | null): string {
		if (!url) return DEFAULT_PROFILE_PHOTO;

		// Check if URL is a valid Supabase URL
		if (url.includes('supabase.co/storage') && url.includes(PROFILE_PHOTOS_BUCKET)) {
			// Extract path from the URL
			const path = getPathFromUrl(url, PROFILE_PHOTOS_BUCKET);
			if (!path) return DEFAULT_PROFILE_PHOTO;

			// Use the server-side proxy to avoid CORS issues
			return `/api/storage-proxy?bucket=${PROFILE_PHOTOS_BUCKET}&path=${encodeURIComponent(path)}&t=${Date.now()}`;
		}

		return url;
	}

	// Function to check if a bucket is accessible
	async function checkBucketAccessibility() {
		try {
			const { data, error } = await supabase.storage.from(PROFILE_PHOTOS_BUCKET).list();

			if (error) {
				storageAvailable = false;

				// Check if this is specifically a "bucket not found" error
				if (error.message?.includes('bucket') || error.message?.includes('not found')) {
					photoError = `The storage bucket "${PROFILE_PHOTOS_BUCKET}" doesn't exist. Profile photos will not be available until an administrator creates this bucket.`;
				}
				return false;
			}

			storageAvailable = true;
			return true;
		} catch (err) {
			console.error('Exception checking bucket:', err);
			storageAvailable = false;
			return false;
		}
	}

	// Copy CV link to clipboard
	async function copyPublicCvLink() {
		if (browser && username) {
			const publicCvUrl = `${window.location.origin}/cv/@${username}`;
			try {
				await navigator.clipboard.writeText(publicCvUrl);
				success = 'CV link copied to clipboard!';
				setTimeout(() => {
					success = null;
				}, 3000);
			} catch (err) {
				console.error('Failed to copy URL:', err);
				error = 'Failed to copy URL to clipboard';
			}
		}
	}

	// Special function just for updating profile photos - bypasses CSRF checks
	async function updateProfilePhoto(photoUrl: string | null) {
		if (!$session) {
			return { success: false, error: 'Not authenticated' };
		}

		try {
			// Use a direct fetch to the CSRF-exempt endpoint
			const response = await fetch('/api/update-profile-photo', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Authorization: `Bearer ${$session.access_token}`
				},
				body: JSON.stringify({
					id: $session.user.id,
					photo_url: photoUrl
				})
			});

			// Parse response as JSON
			let result;
			try {
				result = await response.json();
			} catch (jsonError) {
				console.error('Error parsing response as JSON:', jsonError);
				return {
					success: false,
					error: 'Failed to parse response from server'
				};
			}

			return result;
		} catch (error) {
			console.error('Error in profile photo update:', error);
			return {
				success: false,
				error: error instanceof Error ? error.message : 'Unknown error in profile update'
			};
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
	{#if photoError}
		<div class="rounded bg-red-100 p-4 text-red-700">{photoError}</div>
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
		<!-- Profile Photo Section -->
		<div class="mt-6 space-y-4">
			<label class="block text-lg font-medium text-gray-700" for="profile-photo-section"
				>Profile Photo</label
			>

			<div
				id="profile-photo-section"
				class="flex flex-col items-start gap-4 sm:flex-row sm:items-center"
			>
				<!-- Photo Display -->
				<div class="relative h-24 w-24 overflow-hidden rounded-full">
					{#if photoUrl && photoUrl !== DEFAULT_PROFILE_PHOTO && !photoError}
						<img
							src={getProxiedPhotoUrl(photoUrl)}
							alt={fullName || 'User profile'}
							class="h-full w-full object-cover"
							onerror={() => {
								console.error('Error loading profile photo, fallback to default');
								photoError = 'Unable to load photo';
							}}
						/>
					{:else}
						<PhotoFallback photoUrl={null} defaultImage={DEFAULT_PROFILE_PHOTO} size="lg" />
					{/if}
				</div>

				<!-- Upload Controls -->
				<div class="flex flex-col gap-2">
					<div class="flex flex-wrap gap-2">
						<!-- File Upload Button -->
						<label
							for="photo-upload"
							class="cursor-pointer rounded bg-blue-500 px-4 py-2 text-white shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
						>
							Choose File
						</label>
						<input
							type="file"
							id="photo-upload"
							accept="image/jpeg,image/png,image/webp"
							class="hidden"
							onchange={handlePhotoUpload}
							bind:this={photoInputEl}
						/>

						<!-- Camera Button -->
						<button
							type="button"
							class="rounded bg-blue-500 px-4 py-2 text-white shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
							onclick={() => {
								showCamera = true;
								cameraStatus = 'Opening camera...';
								photoError = null;
							}}
						>
							Use Camera
						</button>

						{#if photoUrl && photoUrl !== DEFAULT_PROFILE_PHOTO}
							<!-- Delete Photo Button -->
							<button
								type="button"
								class="rounded bg-red-500 px-4 py-2 text-white shadow hover:bg-red-600 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
								onclick={deleteProfilePhoto}
								disabled={uploadingPhoto}
							>
								Delete Photo
							</button>
						{/if}
					</div>

					{#if photoError}
						<p class="text-sm text-red-600">{photoError}</p>
					{/if}

					{#if uploadingPhoto}
						<p class="text-sm">Uploading... Please wait</p>
					{/if}
				</div>
			</div>
		</div>

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
				<label class="mb-1 block text-sm font-medium text-gray-700" for="username">Username</label>
				<div class="relative">
					<input
						id="username"
						name="username"
						type="text"
						bind:value={username}
						onblur={() => handleUsernameChange()}
						placeholder="your-username"
						class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {usernameError
							? 'border-red-500'
							: ''} {usernameAvailable && username && !usernameError ? 'border-green-500' : ''}"
						required
					/>
					{#if checkingUsername}
						<div class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500">
							<div class="h-5 w-5 animate-spin rounded-full border-b-2 border-indigo-500"></div>
						</div>
					{:else if usernameAvailable && username && !usernameError}
						<div class="absolute inset-y-0 right-0 flex items-center pr-3 text-green-500">
							<svg
								xmlns="http://www.w3.org/2000/svg"
								class="h-5 w-5"
								viewBox="0 0 20 20"
								fill="currentColor"
							>
								<path
									fill-rule="evenodd"
									d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
									clip-rule="evenodd"
								/>
							</svg>
						</div>
					{/if}
				</div>

				{#if usernameError}
					<p class="mt-1 text-sm text-red-600">{usernameError}</p>
				{/if}

				{#if username && !usernameError && browser}
					<p class="mt-1 text-sm text-gray-500">
						A public version of your CV will be available once you save your profile.
					</p>
				{/if}
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
			<div class="mt-4 text-sm text-gray-600">
				<p>Having trouble with your profile data?</p>
				<a href="/admin/fix-profile" class="text-indigo-600 hover:text-indigo-800">
					Run Profile Repair Tool
				</a>
				<span class="mx-2">|</span>
				<a
					href="/api/profile-diagnostics"
					target="_blank"
					class="text-indigo-600 hover:text-indigo-800"
				>
					View Profile Diagnostics
				</a>
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

		{#if username && !usernameError}
			<div class="mt-8 rounded-lg bg-indigo-50 p-4">
				<h3 class="mb-2 text-lg font-medium text-indigo-700">Public CV Link</h3>
				<p class="mb-3 text-indigo-800">
					Share your CV with recruiters or include it in your resume.
				</p>
				{#if browser}
					<div class="flex flex-wrap items-center gap-3">
						<a
							href="/cv/@{username}"
							class="flex items-center gap-2 rounded-md bg-white px-4 py-2 text-indigo-600 shadow-sm hover:bg-indigo-50 hover:text-indigo-800"
							target="_blank"
						>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								class="h-5 w-5"
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
							View Your Public CV
						</a>

						<button
							type="button"
							class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
							onclick={() => copyPublicCvLink()}
						>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								class="h-5 w-5"
								viewBox="0 0 20 20"
								fill="currentColor"
							>
								<path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
								<path
									d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"
								/>
							</svg>
							Copy Link to Clipboard
						</button>
					</div>
				{/if}
			</div>
		{/if}
	{/if}

	<SectionNavigation />
</div>

{#if showCamera}
	<CameraCapture
		oncapture={handleCameraCapture}
		onerror={handleCameraError}
		onclose={handleCameraClose}
	/>
{/if}
