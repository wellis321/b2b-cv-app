<script lang="ts">
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import { session as authSession } from '$lib/stores/authStore';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';

	let { data } = $props();
	let name = $state('');
	let issuer = $state('');
	let dateObtained = $state('');
	let expiryDate = $state('');
	let certifications = data.certifications || [];
	let error = data.error;
</script>

<div class="mx-auto mb-8 max-w-xl">
	<h2 class="mb-4 text-xl font-bold">Your Certifications</h2>
	{#if error}
		<div class="text-red-600 italic">{error}</div>
	{/if}
	{#if certifications.length === 0}
		<div class="text-gray-500 italic">No certifications added yet.</div>
	{:else}
		<ul class="space-y-4">
			{#each certifications as cert}
				<li class="rounded border bg-white p-4 shadow">
					<div class="font-semibold">{cert.name} ({cert.issuer})</div>
					<div class="text-sm text-gray-500">
						{cert.date_obtained}
						{cert.expiry_date ? `- Expires: ${cert.expiry_date}` : ''}
					</div>
				</li>
			{/each}
		</ul>
	{/if}

	<SectionNavigation />
</div>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Certification</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="name">Certification Name</label
		>
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
		<label class="mb-1 block text-sm font-medium text-gray-700" for="issuer">Issuer</label>
		<input
			id="issuer"
			name="issuer"
			type="text"
			bind:value={issuer}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div class="flex gap-4">
		<div class="flex-1">
			<label class="mb-1 block text-sm font-medium text-gray-700" for="dateObtained"
				>Date Obtained</label
			>
			<input
				id="dateObtained"
				name="dateObtained"
				type="date"
				bind:value={dateObtained}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
				required
			/>
		</div>
		<div class="flex-1">
			<label class="mb-1 block text-sm font-medium text-gray-700" for="expiryDate"
				>Expiry Date</label
			>
			<input
				id="expiryDate"
				name="expiryDate"
				type="date"
				bind:value={expiryDate}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			/>
		</div>
	</div>
	<button
		type="submit"
		class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
		>Save Certification</button
	>
</form>
