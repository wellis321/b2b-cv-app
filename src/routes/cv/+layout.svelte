<script lang="ts">
	import '../../app.css';
	import { page } from '$app/stores';
	import { session } from '$lib/stores/authStore';
	import ErrorBoundary from '$lib/components/ErrorBoundary.svelte';
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
					<a href="/security-review-client" class="text-sm text-indigo-600 hover:text-indigo-800">
						Security Review
					</a>
					{#if $session}
						<a
							href="/"
							class="text-gray-600 transition-colors hover:border-b-2 hover:border-gray-300 hover:text-gray-900"
						>
							Dashboard
						</a>
						<a
							href="/profile"
							class="text-gray-600 transition-colors hover:border-b-2 hover:border-gray-300 hover:text-gray-900"
						>
							Profile
						</a>
					{:else}
						<a
							href="/"
							class="text-gray-600 transition-colors hover:border-b-2 hover:border-gray-300 hover:text-gray-900"
						>
							Sign In
						</a>
					{/if}
				</div>
			</div>
		</nav>
	</header>

	<main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
		<ErrorBoundary>
			<slot></slot>
		</ErrorBoundary>
	</main>
</div>
