<script lang="ts">
	// Simple footer component with links to privacy policy and terms
</script>

<footer class="relative z-10 w-full border-t-2 border-gray-200 bg-gray-50 py-6">
	<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
			<div class="text-center sm:text-left">
				<p class="text-sm text-gray-600">
					© {new Date().getFullYear()} CV Builder. All rights reserved.
				</p>
			</div>
			<div class="flex flex-wrap justify-center gap-6 text-sm text-gray-600">
				<a href="/privacy" class="font-medium hover:text-blue-600 hover:underline">Privacy Policy</a
				>
				<a href="/terms" class="font-medium hover:text-blue-600 hover:underline">Terms of Service</a
				>
			</div>
		</div>
	</div>
</footer>
