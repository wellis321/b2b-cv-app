<script lang="ts">
	import { page } from '$app/state';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';
</script>

<div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
	<SectionNavigation />

	<div class="error-container my-6 overflow-hidden bg-white shadow sm:rounded-lg">
		<div class="border-b border-gray-200 px-4 py-5 sm:px-6">
			<h3 class="text-lg leading-6 font-medium text-gray-900">Profile Error</h3>
			<p class="mt-1 max-w-2xl text-sm text-gray-500">
				{page.status}: {page.error?.message ||
					'An unexpected error occurred while loading your profile'}
			</p>
		</div>

		<div class="px-4 py-5 sm:p-6">
			<div class="rounded-md bg-red-50 p-4">
				<div class="flex">
					<div class="flex-shrink-0">
						<svg
							class="h-5 w-5 text-red-400"
							xmlns="http://www.w3.org/2000/svg"
							viewBox="0 0 20 20"
							fill="currentColor"
							aria-hidden="true"
						>
							<path
								fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
								clip-rule="evenodd"
							/>
						</svg>
					</div>
					<div class="ml-3">
						<p class="text-sm font-medium text-red-800">
							{#if page.status === 404}
								The profile you're looking for doesn't exist or has been removed.
							{:else if page.status === 403}
								You don't have permission to access this profile.
							{:else if page.status === 401}
								You need to be logged in to view or edit your profile.
							{:else}
								There was an error processing your profile data. This could be due to a server issue
								or invalid data.
							{/if}
						</p>
					</div>
				</div>
			</div>

			<div class="mt-5 flex justify-between">
				<a
					href="/"
					class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
				>
					Back to Dashboard
				</a>

				<button
					onclick={() => window.location.reload()}
					class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
				>
					Try Again
				</button>
			</div>
		</div>
	</div>
</div>
