<script lang="ts">
	import { onMount, onDestroy } from 'svelte';

	// Props
	const {
		showCamera = true,
		oncapture = undefined,
		onerror = undefined,
		onclose = undefined,
		onCapture = undefined,
		onError = () => {},
		constraints = { video: true, audio: false }
	} = $props<{
		showCamera?: boolean;
		oncapture?: (blob: Blob, url: string) => void;
		onerror?: (error: string) => void;
		onclose?: () => void;
		onCapture?: (blob: Blob) => void;
		onError?: (error: string) => void;
		constraints?: MediaStreamConstraints;
	}>();

	// State
	let canvasEl: HTMLCanvasElement;
	let videoContainer: HTMLDivElement;
	let videoEl = $state<HTMLVideoElement | null>(null);
	let stream: MediaStream | null = null;
	let error = $state<string | null>(null);
	let loading = $state(true);
	let facingMode = $state<'user' | 'environment'>('user');
	let cameraReady = $state(false);

	// Clean up when component unmounts
	onDestroy(() => {
		stopCamera();
	});

	// Setup camera stream when component mounts
	onMount(() => {
		// Create video element programmatically
		createVideoElement();

		// Delay camera setup slightly to ensure DOM is ready
		setTimeout(() => {
			if (showCamera) {
				setupCamera();
			} else {
				loading = false;
			}
		}, 300);
	});

	// Create video element programmatically
	function createVideoElement() {
		if (!videoContainer) {
			console.error('Video container element not found');
			if (onerror) onerror('Video container not found');
			return;
		}

		// Remove any existing video element
		if (videoEl) {
			videoContainer.removeChild(videoEl);
			videoEl = null;
		}

		// Create new video element
		videoEl = document.createElement('video');
		videoEl.className = 'w-full h-full object-cover';
		videoEl.playsInline = true;
		videoEl.autoplay = true;
		videoEl.muted = true;

		// Add event listeners
		videoEl.onloadedmetadata = () => {
			// Video metadata loaded
		};

		videoEl.onplaying = () => {
			loading = false;
			cameraReady = true;
		};

		videoEl.onerror = (e) => {
			error = `Video error: ${videoEl?.error?.message || 'Unknown error'}`;
			loading = false;
			if (onerror) onerror(error || 'Unknown video error');
		};

		// Append to container
		videoContainer.appendChild(videoEl);
	}

	// Switch camera between front and back
	function switchCamera() {
		facingMode = facingMode === 'user' ? 'environment' : 'user';
		setupCamera();
	}

	// Force reconnect the camera
	function forceReconnectCamera() {
		// Stop any existing streams
		stopCamera();

		// Recreate video element
		createVideoElement();

		// Wait a moment before restarting
		setTimeout(() => {
			setupCamera();
		}, 500);
	}

	// Setup camera stream
	async function setupCamera() {
		// Stop any existing stream
		stopCamera();

		// Reset state
		loading = true;
		error = null;
		cameraReady = false;

		try {
			// Check if video element exists
			if (!videoEl) {
				error = 'Video element not found';
				loading = false;
				if (onerror) onerror(error);
				return;
			}

			// Simple check for camera support
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				error = 'Your browser does not support camera access';
				loading = false;
				if (onerror) onerror(error);
				return;
			}

			// Camera constraints
			const constraints = {
				audio: false,
				video: {
					facingMode: facingMode,
					width: { ideal: 1280 },
					height: { ideal: 720 }
				}
			};

			// Get media stream
			stream = await navigator.mediaDevices.getUserMedia(constraints);

			// Connect stream to video element
			if (videoEl && stream) {
				// Connect stream to video
				videoEl.srcObject = stream;

				// Explicitly try to play the video
				try {
					await videoEl.play();

					// Safety timeout in case the play event doesn't fire
					setTimeout(() => {
						if (loading) {
							loading = false;

							// Check if video is actually playing
							if (
								videoEl &&
								videoEl.currentTime > 0 &&
								!videoEl.paused &&
								!videoEl.ended &&
								videoEl.readyState > 2
							) {
								cameraReady = true;
							}
						}
					}, 3000);
				} catch (playErr) {
					console.error('Error playing video:', playErr);
					// Still set loading to false so user can retry
					loading = false;
					error = 'Video playback failed. Click "Try Again" to retry.';
					if (onerror) onerror(error);
				}
			} else {
				error = 'Video element not initialized';
				loading = false;
				if (onerror) onerror(error);
			}
		} catch (err) {
			loading = false;
			error = 'Camera error';

			// Handle specific error types
			if (err instanceof DOMException) {
				switch (err.name) {
					case 'NotAllowedError':
						error = 'Camera access denied. Please allow camera access and try again.';
						break;
					case 'NotFoundError':
						error = 'No camera found on this device.';
						break;
					case 'NotReadableError':
					case 'AbortError':
						error = 'Camera is already in use or has been disconnected.';
						break;
					default:
						error = `Camera error: ${err.message || err.name}`;
				}
			} else {
				error = `Camera error: ${err instanceof Error ? err.message : String(err)}`;
			}

			console.error('Camera setup error:', error);
			if (onerror) onerror(error);
		}
	}

	// Handle video click, which will take a photo
	function capturePhoto() {
		if (loading || !cameraReady) {
			console.warn('Camera not ready for capture');
			return;
		}

		try {
			// Use canvas to capture frame
			if (!videoEl) {
				error = 'Video element not available';
				if (onerror) onerror(error);
				return;
			}

			// Get video dimensions
			const width = videoEl.videoWidth;
			const height = videoEl.videoHeight;

			// Set canvas dimensions to match video
			canvasEl.width = width;
			canvasEl.height = height;

			// Draw video frame to canvas
			const ctx = canvasEl.getContext('2d');
			if (!ctx) {
				error = 'Could not get canvas context';
				if (onerror) onerror(error);
				return;
			}

			// Draw video frame to canvas
			ctx.drawImage(videoEl, 0, 0, width, height);

			// Get image data as blob
			canvasEl.toBlob(
				(blob) => {
					if (blob) {
						// Create object URL for display
						const url = URL.createObjectURL(blob);

						// Call capture handler
						if (oncapture) {
							oncapture(blob, url);
						}
					} else {
						error = 'Failed to capture image';
						if (onerror) onerror(error);
					}
				},
				'image/jpeg',
				0.9
			);
		} catch (err) {
			console.error('Error capturing photo:', err);
			error = 'Error capturing photo';
			if (onerror) onerror(error);
		}
	}

	// Stop camera stream
	function stopCamera() {
		if (stream) {
			// Stop all tracks
			stream.getTracks().forEach((track) => {
				track.stop();
			});
			stream = null;
		}

		// Clear video source
		if (videoEl && videoEl.srcObject) {
			videoEl.srcObject = null;
		}

		// Reset state
		cameraReady = false;
	}

	// Handle close action
	function handleClose() {
		stopCamera();
		if (onclose) {
			onclose();
		}
	}
</script>

<div
	class="camera-modal bg-opacity-75 fixed inset-0 z-50 flex items-center justify-center bg-black"
>
	<div class="relative max-w-lg rounded-lg bg-white p-4 shadow-xl">
		<!-- Close button -->
		<button
			type="button"
			class="absolute top-2 right-2 rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
			onclick={handleClose}
		>
			<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					stroke-width="2"
					d="M6 18L18 6M6 6l12 12"
				/>
			</svg>
		</button>

		<h2 class="mb-4 text-xl font-bold">Take a Photo</h2>

		<!-- Loading indicator -->
		{#if loading}
			<div class="mb-4 flex items-center justify-center">
				<div
					class="h-8 w-8 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"
				></div>
				<span class="ml-2">Initializing camera...</span>
			</div>
		{/if}

		<!-- Error message -->
		{#if error}
			<div class="mb-4 rounded bg-red-100 p-4 text-red-700">
				<p>{error}</p>
				<button
					onclick={setupCamera}
					class="mt-2 rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
				>
					Try Again
				</button>
			</div>
		{/if}

		<!-- Camera preview -->
		<div class="camera-preview relative mb-4">
			<div
				class="relative flex h-64 w-full items-center justify-center overflow-hidden rounded bg-gray-100"
				bind:this={videoContainer}
			>
				{#if !videoEl && loading}
					<div class="text-center text-gray-500">Loading camera...</div>
				{/if}
			</div>

			<!-- Hidden canvas for photo capture -->
			<canvas class="hidden" bind:this={canvasEl}></canvas>
		</div>

		<div class="flex justify-between">
			<!-- Switch camera button (only on mobile devices) -->
			<button
				onclick={switchCamera}
				class="rounded bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300"
			>
				Switch Camera
			</button>

			<!-- Capture button -->
			<button
				onclick={capturePhoto}
				class="rounded bg-blue-500 px-6 py-2 text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
				disabled={loading || !cameraReady}
			>
				Take Photo
			</button>
		</div>
	</div>
</div>

<style>
	.camera-modal {
		transition: opacity 0.2s ease;
	}
</style>
