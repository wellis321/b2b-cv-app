<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { page } from '$app/stores';

	let photoUrl = $state('');
	let isLoading = $state(false);
	let isSuccess = $state<boolean | null>(null);
	let errorMessage = $state('');

	// Initialize with the URL from the query params or the sample URL if available
	onMount(() => {
		if (browser) {
			const urlParam = $page.url.searchParams.get('url');
			if (urlParam) {
				photoUrl = decodeURIComponent(urlParam);
				testPhotoAccess();
			} else if (!photoUrl) {
				// Initialize with a sample URL if no URL is provided in params
				photoUrl =
					'https://jnebkgmkgatejsjgbaqo.supabase.co/storage/v1/object/public/profile-photos/5aa15097-2d24-4039-9ed4-15c0b0a0626d/1747225772534.jpg';
			}
		}
	});

	// Test accessing a photo
	async function testPhotoAccess() {
		if (!photoUrl) return;

		isLoading = true;
		isSuccess = null;
		errorMessage = '';

		try {
			// Create a promise that resolves when the image loads and rejects on error
			const imageLoadPromise = new Promise((resolve, reject) => {
				const img = new Image();
				img.onload = () => resolve(true);
				img.onerror = (err) => reject(err);
				img.src = photoUrl;
			});

			// Also try a fetch call with HEAD
			const fetchPromise = fetch(photoUrl, {
				method: 'HEAD',
				headers: {
					'Cache-Control': 'no-cache'
				}
			}).then((response) => {
				if (!response.ok) {
					throw new Error(`HTTP error! Status: ${response.status}`);
				}
				return response;
			});

			// Use Promise.all to see if both methods work
			await Promise.all([imageLoadPromise, fetchPromise]);
			isSuccess = true;
		} catch (err) {
			console.error('Error loading image:', err);
			isSuccess = false;
			errorMessage = 'Failed to load the image. Check the browser console for details.';
		} finally {
			isLoading = false;
		}
	}

	function extractPhotoUrl(event: Event) {
		const target = event.target as HTMLInputElement;
		photoUrl = target.value;
	}
</script>

<svelte:head>
	<title>Test Photo Access</title>
</svelte:head>

<div class="container mx-auto max-w-4xl px-4 py-8">
	<h1 class="mb-6 text-2xl font-bold">Test Profile Photo Public Access</h1>
	<p class="mb-4 text-gray-700">
		This utility tests whether a profile photo is publicly accessible. Enter the full photo URL to
		test.
	</p>

	<div class="mb-6 flex gap-2">
		<input
			type="text"
			value={photoUrl}
			oninput={extractPhotoUrl}
			placeholder="Enter photo URL"
			class="flex-1 rounded-md border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none"
		/>
		<button
			onclick={testPhotoAccess}
			class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
			disabled={isLoading || !photoUrl}
		>
			{isLoading ? 'Testing...' : 'Test Access'}
		</button>
	</div>

	{#if isLoading}
		<div class="flex items-center justify-center py-8">
			<div
				class="h-10 w-10 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"
			></div>
		</div>
	{:else if isSuccess === true}
		<div class="mb-6 rounded-md bg-green-50 p-4 text-green-800">
			<p>
				<span class="font-medium">Success!</span> The image is publicly accessible.
			</p>
			<div class="mt-4">
				<h2 class="mb-2 text-lg font-semibold">Image Preview</h2>
				<div class="overflow-hidden rounded-lg border border-gray-200 p-4">
					<img
						src={photoUrl}
						alt="Tested image"
						class="mx-auto max-h-80 object-contain"
						onerror={(e) => {
							const img = e.target as HTMLImageElement;
							img.alt = 'Failed to load image';
							img.classList.add('border', 'border-red-500');
						}}
					/>
				</div>
			</div>
		</div>
	{:else if isSuccess === false}
		<div class="mb-6 rounded-md bg-red-50 p-4 text-red-800">
			<p class="font-medium">Failed to access the image.</p>
			<p class="mt-2">{errorMessage}</p>
			<p class="mt-4">Possible issues:</p>
			<ul class="mt-2 list-inside list-disc">
				<li>The storage bucket doesn't have a public access policy</li>
				<li>The image doesn't exist at this URL</li>
				<li>CORS settings are preventing access</li>
				<li>The URL format is incorrect</li>
			</ul>
		</div>
	{/if}

	<div class="mt-8 rounded-md bg-blue-50 p-4 text-blue-800">
		<h2 class="mb-2 text-lg font-semibold">Debug Information</h2>
		<p class="mb-2">A typical Supabase Storage URL format looks like this:</p>
		<pre
			class="mb-4 overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-800">https://[project-ref].supabase.co/storage/v1/object/public/profile-photos/[user-id]/image.jpg</pre>

		<p class="mb-2">Make sure you have the following policy in Supabase:</p>
		<pre
			class="overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-800">CREATE POLICY "Profile photos are publicly accessible"
ON storage.objects
FOR SELECT
USING (bucket_id = 'profile-photos');</pre>
	</div>
</div>
