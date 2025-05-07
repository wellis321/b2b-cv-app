<script lang="ts">
	let { data } = $props();
	let name = $state('');
	let description = $state('');
	let interests = data.interests || [];
	let error = data.error;
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';
</script>

<div class="mx-auto mb-8 max-w-xl">
	<h2 class="mb-4 text-xl font-bold">Your Interests</h2>
	{#if error}
		<div class="text-red-600 italic">{error}</div>
	{/if}
	{#if interests.length === 0}
		<div class="text-gray-500 italic">No interests added yet.</div>
	{:else}
		<ul class="space-y-4">
			{#each interests as interest}
				<li class="rounded border bg-white p-4 shadow">
					<div class="font-semibold">{interest.name}</div>
					{#if interest.description}
						<div class="text-sm text-gray-500">{interest.description}</div>
					{/if}
				</li>
			{/each}
		</ul>
	{/if}
</div>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Interest</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="name">Interest Name</label>
		<input
			id="name"
			name="name"
			type="text"
			bind:value={name}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="description">Description</label
		>
		<textarea
			id="description"
			name="description"
			bind:value={description}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		></textarea>
	</div>
	<button
		type="submit"
		class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
		>Save Interest</button
	>
</form>

<div class="mx-auto mt-10 max-w-xl">
	<SectionNavigation />
</div>
