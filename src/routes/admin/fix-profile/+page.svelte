<script lang="ts">
	let { data, form } = $props();
	let success = $state(form?.fixed ?? false);
	let error = $state(form?.error ?? null);
	let currentProfile = $state(data.profile);
	let authUser = $state(data.user);
</script>

<div class="container mx-auto px-4 py-8">
	<h1 class="mb-6 text-2xl font-bold">Fix Profile Data</h1>

	{#if success}
		<div class="mb-6 rounded-md bg-green-100 p-4 text-green-800">
			<p class="font-medium">Profile fixed successfully!</p>
			<p>Your profile has been updated with the correct information.</p>
		</div>
	{/if}

	{#if error}
		<div class="mb-6 rounded-md bg-red-100 p-4 text-red-800">
			<p class="font-medium">Error</p>
			<p>{error}</p>
		</div>
	{/if}

	<div class="mb-6 rounded-md bg-blue-100 p-4 text-blue-800">
		<p class="font-medium">Current Profile Data</p>
		<ul class="mt-2 list-inside list-disc">
			<li><strong>Auth Email:</strong> {authUser?.email ?? 'Not found'}</li>
			<li><strong>Profile Email:</strong> {currentProfile?.email ?? 'Not set'}</li>
			<li><strong>Full Name:</strong> {currentProfile?.full_name ?? 'Not set'}</li>
			<li><strong>Username:</strong> {currentProfile?.username ?? 'Not set'}</li>
			<li><strong>Phone:</strong> {currentProfile?.phone ?? 'Not set'}</li>
			<li><strong>Location:</strong> {currentProfile?.location ?? 'Not set'}</li>
		</ul>
	</div>

	<div class="rounded-md bg-white p-6 shadow-md">
		<h2 class="mb-4 text-xl font-semibold">Update Profile</h2>
		<p class="mb-4">
			This form will update your profile with the correct information. Your email will be set to
			match your authentication email: <strong>{authUser?.email}</strong>
		</p>

		<form method="POST" class="space-y-4">
			<div>
				<label for="fullName" class="mb-1 block text-sm font-medium text-gray-700">Full Name</label>
				<input
					type="text"
					id="fullName"
					name="fullName"
					value={currentProfile?.full_name ?? ''}
					class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
				/>
			</div>

			<div>
				<label for="phone" class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
				<input
					type="tel"
					id="phone"
					name="phone"
					value={currentProfile?.phone ?? ''}
					class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
				/>
			</div>

			<div>
				<label for="location" class="mb-1 block text-sm font-medium text-gray-700">Location</label>
				<input
					type="text"
					id="location"
					name="location"
					value={currentProfile?.location ?? ''}
					class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
				/>
			</div>

			<div class="pt-4">
				<button
					type="submit"
					class="w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
				>
					Fix Profile
				</button>
			</div>
		</form>
	</div>

	<div class="mt-8">
		<a href="/profile" class="text-indigo-600 hover:text-indigo-800">← Back to Profile</a>
	</div>
</div>
