<script lang="ts">
	import { browser } from '$app/environment';
	import { safeLog } from '$lib/config';
	import { onMount } from 'svelte';

	type ErrorBoundaryState = {
		hasError: boolean;
		error: Error | null;
		errorInfo: { componentStack?: string } | null;
	};

	// Props
	let { fallback, resetErrorBoundary, onError, children } = $props<{
		fallback?: any;
		resetErrorBoundary?: () => void;
		onError?: (error: Error, errorInfo: any) => void;
		children?: () => any;
	}>();

	// State
	let state = $state<ErrorBoundaryState>({
		hasError: false,
		error: null,
		errorInfo: null
	});

	// Event handlers when the slot contains an error
	function handleSlotError(event: ErrorEvent | CustomEvent) {
		let error: Error;
		let errorInfo = {};

		if (event instanceof ErrorEvent) {
			error = event.error || new Error(event.message);
			errorInfo = { componentStack: event.filename };
		} else {
			error = event.detail?.error || new Error('Unknown error in component');
			errorInfo = event.detail?.info || {};
		}

		// Update state
		state = {
			hasError: true,
			error,
			errorInfo
		};

		// Call onError callback if provided
		if (onError) {
			onError(error, errorInfo);
		}

		// Log error
		safeLog('error', 'Component error caught by ErrorBoundary', {
			message: error.message,
			stack: error.stack,
			componentStack: errorInfo.componentStack
		});

		// Prevent the error from propagating
		event.preventDefault();
		return false;
	}

	// Reset function
	function reset() {
		state = {
			hasError: false,
			error: null,
			errorInfo: null
		};

		if (resetErrorBoundary) {
			resetErrorBoundary();
		}
	}

	// Add event listeners
	onMount(() => {
		if (browser) {
			const element = document.querySelector('[data-error-boundary]');
			if (element) {
				element.addEventListener('error', handleSlotError);
				element.addEventListener('svelte:error', handleSlotError);

				return () => {
					element.removeEventListener('error', handleSlotError);
					element.removeEventListener('svelte:error', handleSlotError);
				};
			}
		}
	});
</script>

<div data-error-boundary>
	{#if state.hasError}
		{#if fallback}
			{@const Component = fallback}
			<Component error={state.error} {reset} />
		{:else}
			<div class="error-boundary-fallback">
				<h2>Something went wrong</h2>
				<p class="error-message">{state.error?.message || 'Unknown error'}</p>
				<button class="reset-button" onclick={reset}> Try again </button>
			</div>
		{/if}
	{:else if children}
		{@render children()}
	{/if}
</div>

<style>
	.error-boundary-fallback {
		padding: 1rem;
		border: 1px solid #f56565;
		border-radius: 0.25rem;
		background-color: #fff5f5;
		margin: 1rem 0;
	}

	.error-message {
		color: #c53030;
		margin: 0.5rem 0;
	}

	.reset-button {
		background-color: #4f46e5;
		color: white;
		border: none;
		padding: 0.5rem 1rem;
		border-radius: 0.25rem;
		cursor: pointer;
	}

	.reset-button:hover {
		background-color: #4338ca;
	}
</style>
