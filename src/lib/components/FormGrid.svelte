<script lang="ts">
	/**
	 * A reusable component for displaying form fields in a grid layout
	 * Default is a two-column grid on larger screens, single column on mobile
	 */
	export let columns: number = 2;
</script>

<div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-{columns}">
	<slot />
</div>
