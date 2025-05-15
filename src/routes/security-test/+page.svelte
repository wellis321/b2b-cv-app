<script context="module">
	// DEVELOPMENT USE ONLY - This page is automatically redirected in production
	// This page is used to test Content Security Policy (CSP) implementations
	// This ensures the page is publicly accessible for testing
	// export const prerender = true; - Moved to +page.js
</script>

<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';

	let nonce = '';
	let testResults = {
		noncePresent: false,
		safeScriptExecuted: false,
		unsafeScriptBlocked: true,
		cspViolationReported: false
	};

	// TypeScript types for the test
	type CustomWindow = Window & {
		unsafeTestExecuted?: boolean;
		safeScriptExecuted?: () => void;
	};

	onMount(() => {
		if (browser) {
			// Get the nonce from the meta tag
			const nonceMetaTag = document.querySelector('meta[name="csp-nonce"]');
			nonce = nonceMetaTag ? nonceMetaTag.getAttribute('content') || '' : 'not-found';
			testResults.noncePresent = !!nonceMetaTag;

			// Set the safeScriptExecuted function on window
			(window as CustomWindow).safeScriptExecuted = safeScriptExecuted;

			// Add event listener for CSP violations
			document.addEventListener('securitypolicyviolation', (e) => {
				console.log('CSP Violation:', e);
				testResults.cspViolationReported = true;
				testResults = testResults; // Force reactivity update
			});

			// Try to inject an unsafe inline script (this should be blocked)
			setTimeout(() => {
				try {
					const unsafeScript = document.createElement('script');
					unsafeScript.textContent = 'window.unsafeTestExecuted = true;';
					document.body.appendChild(unsafeScript);

					// If this executes, the CSP isn't working properly
					setTimeout(() => {
						testResults.unsafeScriptBlocked = !(window as CustomWindow).unsafeTestExecuted;
						testResults = testResults; // Force reactivity update
					}, 500);
				} catch (e) {
					console.error('Error injecting unsafe script:', e);
				}
			}, 1000);
		}
	});

	// This function will be executed by the safe script with nonce
	function safeScriptExecuted() {
		testResults.safeScriptExecuted = true;
		testResults = testResults; // Force reactivity update
	}
</script>

<svelte:head>
	<title>CSP Nonce Test</title>

	<!-- This script should execute because it has a nonce (will be added by SvelteKit) -->
	<script>
		// This will be executed if the nonce is correctly applied
		window.addEventListener('DOMContentLoaded', function () {
			// Access the global window object safely
			if (typeof window.safeScriptExecuted === 'function') {
				window.safeScriptExecuted();
			}
		});
	</script>
</svelte:head>

<div class="container mx-auto max-w-2xl p-8">
	<h1 class="mb-6 text-2xl font-bold">Content Security Policy Test Page</h1>

	<div class="mb-8 rounded bg-blue-50 p-6">
		<h2 class="mb-4 text-xl font-semibold">CSP Nonce Information</h2>
		<p class="mb-2">
			Detected nonce: <code class="rounded bg-gray-100 px-2 py-1">{nonce || 'None'}</code>
		</p>
		<p>
			Nonce present in page: <span
				class={testResults.noncePresent ? 'text-green-600' : 'text-red-600'}
			>
				{testResults.noncePresent ? '✓ Yes' : '✗ No'}
			</span>
		</p>
	</div>

	<div class="mb-8 rounded bg-green-50 p-6">
		<h2 class="mb-4 text-xl font-semibold">Test Results</h2>
		<ul class="space-y-2">
			<li>
				Script with nonce executed:
				<span class={testResults.safeScriptExecuted ? 'text-green-600' : 'text-red-600'}>
					{testResults.safeScriptExecuted ? '✓ Success' : '✗ Failed'}
				</span>
			</li>
			<li>
				Unsafe script blocked:
				<span class={testResults.unsafeScriptBlocked ? 'text-green-600' : 'text-red-600'}>
					{testResults.unsafeScriptBlocked ? '✓ Success' : '✗ Failed'}
				</span>
			</li>
			<li>
				CSP violation reported:
				<span class={testResults.cspViolationReported ? 'text-green-600' : 'text-yellow-600'}>
					{testResults.cspViolationReported ? '✓ Success' : '⚠ Waiting...'}
				</span>
			</li>
		</ul>
	</div>

	<div class="rounded bg-yellow-50 p-6">
		<h2 class="mb-4 text-xl font-semibold">Manual Testing Instructions</h2>
		<ol class="list-decimal space-y-2 pl-5">
			<li>Open browser developer tools (F12)</li>
			<li>Go to the Network tab</li>
			<li>
				Check if there's a request to <code>/api/csp-report</code> (may take a moment to appear)
			</li>
			<li>Verify the Console tab for any CSP violation messages</li>
			<li>Inspect the page source to confirm script tags have nonce attributes</li>
		</ol>
	</div>
</div>
