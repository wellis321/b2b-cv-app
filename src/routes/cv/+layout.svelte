<script lang="ts">
	import '../../app.css';
	import { browser } from '$app/environment';
	import ErrorBoundary from '$lib/components/ErrorBoundary.svelte';
	import { initGlobalHelpers } from '$lib/utils/globalHelpers';

	// Initialize global helpers
	if (browser) {
		initGlobalHelpers();
	}
</script>

<div class="min-h-screen">
	<ErrorBoundary>
		<slot></slot>
	</ErrorBoundary>
</div>
