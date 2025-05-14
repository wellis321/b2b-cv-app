<script lang="ts">
	import { onMount } from 'svelte';
	import { createClient } from '@supabase/supabase-js';
	import config from '$lib/config';
	import type { Database } from '$lib/database.types';
	import { browser } from '$app/environment';

	let username = $state('');
	let testResults = $state<Record<string, { success: boolean; count: number; error?: any }>>({});
	let isLoading = $state(false);
	let userId = $state<string | null>(null);

	// Create a Supabase client
	const supabase = browser
		? createClient<Database>(config.supabase.url, config.supabase.anonKey, {
				auth: {
					persistSession: false,
					autoRefreshToken: false
				}
			})
		: null;

	async function findUser() {
		if (!username || !supabase) return;

		isLoading = true;
		testResults = {};
		userId = null;

		try {
			// Find user by username
			const { data: userData, error: userError } = await supabase
				.from('profiles')
				.select('id')
				.eq('username', username)
				.single();

			if (userError) {
				testResults['profile'] = {
					success: false,
					count: 0,
					error: userError
				};
				return;
			}

			userId = userData.id;
			await testAllTables(userData.id);
		} catch (err) {
			console.error('Error finding user:', err);
		} finally {
			isLoading = false;
		}
	}

	async function testAllTables(profileId: string) {
		if (!supabase) return;

		// Test profile table
		try {
			const { data: profileData, error: profileError } = await supabase
				.from('profiles')
				.select('*')
				.eq('id', profileId)
				.single();

			testResults['profile'] = {
				success: !profileError,
				count: profileData ? 1 : 0,
				error: profileError
			};
		} catch (err) {
			testResults['profile'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test work_experience table
		try {
			const { data: workData, error: workError } = await supabase
				.from('work_experience')
				.select('*')
				.eq('profile_id', profileId);

			testResults['work_experience'] = {
				success: !workError,
				count: workData?.length || 0,
				error: workError
			};
		} catch (err) {
			testResults['work_experience'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test skills table
		try {
			const { data: skillsData, error: skillsError } = await supabase
				.from('skills')
				.select('*')
				.eq('profile_id', profileId);

			testResults['skills'] = {
				success: !skillsError,
				count: skillsData?.length || 0,
				error: skillsError
			};
		} catch (err) {
			testResults['skills'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test education table
		try {
			const { data: educationData, error: educationError } = await supabase
				.from('education')
				.select('*')
				.eq('profile_id', profileId);

			testResults['education'] = {
				success: !educationError,
				count: educationData?.length || 0,
				error: educationError
			};
		} catch (err) {
			testResults['education'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test projects table
		try {
			const { data: projectsData, error: projectsError } = await supabase
				.from('projects')
				.select('*')
				.eq('profile_id', profileId);

			testResults['projects'] = {
				success: !projectsError,
				count: projectsData?.length || 0,
				error: projectsError
			};
		} catch (err) {
			testResults['projects'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test certifications table
		try {
			const { data: certData, error: certError } = await supabase
				.from('certifications')
				.select('*')
				.eq('profile_id', profileId);

			testResults['certifications'] = {
				success: !certError,
				count: certData?.length || 0,
				error: certError
			};
		} catch (err) {
			testResults['certifications'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test memberships table
		try {
			const { data: membershipsData, error: membershipsError } = await supabase
				.from('professional_memberships')
				.select('*')
				.eq('profile_id', profileId);

			testResults['memberships'] = {
				success: !membershipsError,
				count: membershipsData?.length || 0,
				error: membershipsError
			};
		} catch (err) {
			testResults['memberships'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test interests table
		try {
			const { data: interestsData, error: interestsError } = await supabase
				.from('interests')
				.select('*')
				.eq('profile_id', profileId);

			testResults['interests'] = {
				success: !interestsError,
				count: interestsData?.length || 0,
				error: interestsError
			};
		} catch (err) {
			testResults['interests'] = {
				success: false,
				count: 0,
				error: err
			};
		}

		// Test qualification equivalence table
		try {
			const { data: qualData, error: qualError } = await supabase
				.from('professional_qualification_equivalence')
				.select('*')
				.eq('profile_id', profileId);

			testResults['qualification_equivalence'] = {
				success: !qualError,
				count: qualData?.length || 0,
				error: qualError
			};
		} catch (err) {
			testResults['qualification_equivalence'] = {
				success: false,
				count: 0,
				error: err
			};
		}
	}
</script>

<svelte:head>
	<title>Test CV Access</title>
</svelte:head>

<div class="container mx-auto max-w-4xl px-4 py-8">
	<h1 class="mb-6 text-2xl font-bold">Test CV Public Access</h1>
	<p class="mb-4 text-gray-700">
		This utility tests whether CV data is accessible for public viewing based on the Supabase RLS
		policies. Enter a username to test if all CV tables are accessible.
	</p>

	<div class="mb-6 flex gap-2">
		<input
			type="text"
			bind:value={username}
			placeholder="Enter a username"
			class="flex-1 rounded-md border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none"
			on:keydown={(e) => e.key === 'Enter' && findUser()}
		/>
		<button
			on:click={findUser}
			class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
			disabled={isLoading || !username}
		>
			{isLoading ? 'Testing...' : 'Test Access'}
		</button>
	</div>

	{#if isLoading}
		<div class="flex items-center justify-center py-8">
			<div
				class="h-10 w-10 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"
			></div>
		</div>
	{:else if userId}
		<div class="mb-4 rounded-md bg-gray-50 p-4">
			<p class="font-medium">User ID: {userId}</p>
		</div>

		<div class="overflow-hidden rounded-lg border">
			<table class="w-full text-left">
				<thead class="bg-gray-50">
					<tr>
						<th class="px-6 py-3 text-sm font-medium text-gray-500">Table</th>
						<th class="px-6 py-3 text-sm font-medium text-gray-500">Status</th>
						<th class="px-6 py-3 text-sm font-medium text-gray-500">Count</th>
						<th class="px-6 py-3 text-sm font-medium text-gray-500">Error</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-200">
					{#each Object.entries(testResults) as [table, result]}
						<tr>
							<td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">{table}</td>
							<td class="px-6 py-4 text-sm text-gray-500">
								{#if result.success}
									<span
										class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
									>
										Success
									</span>
								{:else}
									<span
										class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"
									>
										Failed
									</span>
								{/if}
							</td>
							<td class="px-6 py-4 text-sm text-gray-500">{result.count}</td>
							<td class="px-6 py-4 text-sm text-gray-500">
								{#if result.error}
									<details>
										<summary class="cursor-pointer text-xs text-red-600">Error details</summary>
										<pre
											class="mt-2 max-h-32 overflow-auto rounded bg-gray-100 p-2 text-xs whitespace-pre-wrap text-red-600">{JSON.stringify(
												result.error,
												null,
												2
											)}</pre>
									</details>
								{:else}
									None
								{/if}
							</td>
						</tr>
					{/each}
				</tbody>
			</table>
		</div>

		<div class="mt-6">
			<h2 class="mb-2 text-lg font-semibold">Summary</h2>
			{#if Object.values(testResults).every((r) => r.success)}
				<div class="rounded-md bg-green-50 p-4 text-green-800">
					<p>
						<span class="font-medium">Success!</span> All CV tables are accessible for public viewing.
						The RLS policies are working correctly.
					</p>
				</div>
			{:else}
				<div class="rounded-md bg-red-50 p-4 text-red-800">
					<p class="font-medium">Some tables are not accessible for public viewing.</p>
					<p class="mt-2">
						You may need to add RLS policies to those tables. Check the tables marked as "Failed"
						above.
					</p>
				</div>
			{/if}

			<div class="mt-4">
				<a
					href={`/cv/@${username}`}
					class="inline-block rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
				>
					View CV for @{username}
				</a>
			</div>
		</div>
	{/if}
</div>
