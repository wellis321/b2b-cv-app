<script lang="ts">
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { supabase } from '$lib/supabase';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import { session as authSession } from '$lib/stores/authStore';
	import SectionNavigation from '$lib/components/SectionNavigation.svelte';

	let { data } = $props();
	let organisation = $state('');
	let role = $state('');
	let startDate = $state('');
	let endDate = $state('');
	let memberships = data.memberships || [];
	let error = data.error;
</script>

<div class="mx-auto mb-8 max-w-xl">
	<h2 class="mb-4 text-xl font-bold">Your Professional Memberships</h2>
	{#if error}
		<div class="text-red-600 italic">{error}</div>
	{/if}
	{#if memberships.length === 0}
		<div class="text-gray-500 italic">No memberships added yet.</div>
	{:else}
		<ul class="space-y-4">
			{#each memberships as mem}
				<li class="rounded border bg-white p-4 shadow">
					<div class="font-semibold">{mem.organisation}</div>
					{#if mem.role}
						<div class="text-sm text-gray-500">Role: {mem.role}</div>
					{/if}
					<div class="text-sm text-gray-500">{mem.start_date} - {mem.end_date || 'Present'}</div>
				</li>
			{/each}
		</ul>
	{/if}

	<SectionNavigation />
</div>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Professional Membership</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="organisation"
			>Organisation</label
		>
		<input
			id="organisation"
			name="organisation"
			type="text"
			bind:value={organisation}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="role">Role</label>
		<input
			id="role"
			name="role"
			type="text"
			bind:value={role}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		/>
	</div>
	<div class="flex gap-4">
		<div class="flex-1">
			<label class="mb-1 block text-sm font-medium text-gray-700" for="startDate">Start Date</label>
			<input
				id="startDate"
				name="startDate"
				type="date"
				bind:value={startDate}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
				required
			/>
		</div>
		<div class="flex-1">
			<label class="mb-1 block text-sm font-medium text-gray-700" for="endDate">End Date</label>
			<input
				id="endDate"
				name="endDate"
				type="date"
				bind:value={endDate}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			/>
		</div>
	</div>
	<button
		type="submit"
		class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
		>Save Membership</button
	>
</form>
