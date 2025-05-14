<script lang="ts">
	type PhotoSize = 'sm' | 'md' | 'lg';

	let {
		photoUrl = null,
		defaultImage = '/images/default-profile.svg',
		size = 'md' as PhotoSize
	} = $props();

	let imageError = $state(false);
	let isLoading = $state(true);

	// Size classes based on the size prop
	const sizeClasses: Record<PhotoSize, string> = {
		sm: 'w-12 h-12',
		md: 'w-24 h-24',
		lg: 'w-32 h-32'
	};

	function handleError() {
		console.error('Image failed to load:', photoUrl);
		imageError = true;
	}

	function handleLoad() {
		isLoading = false;
	}
</script>

<div
	class="{sizeClasses[
		size as PhotoSize
	]} relative overflow-hidden rounded-full border-2 border-gray-300"
>
	{#if photoUrl && !imageError}
		<img
			src={photoUrl}
			alt="Profile"
			class="h-full w-full object-cover"
			onerror={handleError}
			onload={handleLoad}
		/>

		{#if isLoading}
			<div class="absolute inset-0 flex items-center justify-center bg-gray-100">
				<div class="h-full w-full animate-pulse bg-gray-200"></div>
			</div>
		{/if}
	{:else}
		<img src={defaultImage} alt="Default Profile" class="h-full w-full object-cover" />
	{/if}
</div>
