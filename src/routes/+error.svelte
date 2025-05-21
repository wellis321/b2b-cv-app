<script lang="ts">
	import { page } from '$app/state';
</script>

<div
	class="error-container flex min-h-[50vh] flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8"
>
	<div class="w-full max-w-md space-y-8 rounded-xl bg-white p-10 shadow-md">
		<div>
			<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
				{page.status}
			</h2>
			<p class="mt-2 text-center text-sm text-gray-600">
				{page.error?.message || 'An unexpected error occurred'}
			</p>
		</div>
		<div class="mt-8 space-y-6">
			<div class="rounded-md shadow-sm">
				<div class="px-5 py-3 text-sm leading-5 text-gray-700">
					{#if page.status === 404}
						The page you're looking for doesn't exist.
					{:else if page.status === 403}
						You don't have permission to access this resource.
					{:else if page.status === 401}
						You need to be logged in to view this page.
					{:else}
						Something went wrong. Please try again later.
					{/if}
				</div>
			</div>
			<div>
				<a
					href="/"
					class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
				>
					Return to Home
				</a>
			</div>
		</div>
	</div>
</div>
