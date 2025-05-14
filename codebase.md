# .cursor/mcp.json

```json
{
	"mcpServers": {
		"supabase": {
			"command": "npx",
			"args": [
				"-y",
				"@supabase/mcp-server-supabase@latest",
				"--access-token",
				"sbp_0c6b9843c34f4cf24d0e1dcd580e9af601fc5df1"
			]
		}
	}
}
```

# .cursor/rules/overview.mdc

```mdc
---
description:
globs:
alwaysApply: true
---
You are creating an app that helps the user build their CV on the front end.

The cv will have sections such as -

Introduction
Work experience
Projects
Professional memberships
Intersts and activities
Certifications
Professional qualification equivilance
Education
Skills

Each section will then come together to make the full cv which the user will be able to display as a website or print out as a pdf.

```

# .gitignore

```
node_modules

# Output
.output
.vercel
.netlify
.wrangler
/.svelte-kit
/build

# OS
.DS_Store
Thumbs.db

# Env
.env
.env.*
!.env.example
!.env.test

# Vite
vite.config.js.timestamp-*
vite.config.ts.timestamp-*

```

# .npmrc

```
engine-strict=true

```

# .prettierignore

```
# Package Managers
package-lock.json
pnpm-lock.yaml
yarn.lock
bun.lock
bun.lockb

```

# .prettierrc

```
{
	"useTabs": true,
	"singleQuote": true,
	"trailingComma": "none",
	"printWidth": 100,
	"plugins": ["prettier-plugin-svelte", "prettier-plugin-tailwindcss"],
	"overrides": [
		{
			"files": "*.svelte",
			"options": {
				"parser": "svelte"
			}
		}
	]
}

```

# package.json

```json
{
	"name": "cv-app",
	"private": true,
	"version": "0.0.1",
	"type": "module",
	"scripts": {
		"dev": "vite dev",
		"build": "vite build",
		"preview": "vite preview",
		"prepare": "svelte-kit sync || echo ''",
		"check": "svelte-kit sync && svelte-check --tsconfig ./tsconfig.json",
		"check:watch": "svelte-kit sync && svelte-check --tsconfig ./tsconfig.json --watch",
		"format": "prettier --write .",
		"lint": "prettier --check ."
	},
	"devDependencies": {
		"@sveltejs/adapter-auto": "^6.0.0",
		"@sveltejs/kit": "^2.16.0",
		"@sveltejs/vite-plugin-svelte": "^5.0.0",
		"@tailwindcss/vite": "^4.0.0",
		"prettier": "^3.4.2",
		"prettier-plugin-svelte": "^3.3.3",
		"prettier-plugin-tailwindcss": "^0.6.11",
		"svelte": "^5.0.0",
		"svelte-check": "^4.0.0",
		"tailwindcss": "^4.0.0",
		"typescript": "^5.0.0",
		"vite": "^6.2.6"
	},
	"dependencies": {
		"@supabase/supabase-js": "^2.49.4"
	}
}
```

# README.md

```md
# sv

Everything you need to build a Svelte project, powered by [`sv`](https://github.com/sveltejs/cli).

## Creating a project

If you're seeing this, you've probably already done this step. Congrats!

\`\`\`bash

# create a new project in the current directory

npx sv create

# create a new project in my-app

npx sv create my-app
\`\`\`

## Developing

Once you've created a project and installed dependencies with `npm install` (or `pnpm install` or `yarn`), start a development server:

\`\`\`bash
npm run dev

# or start the server and open the app in a new browser tab

npm run dev -- --open
\`\`\`

## Building

To create a production version of your app:

\`\`\`bash
npm run build
\`\`\`

You can preview the production build with `npm run preview`.

> To deploy your app, you may need to install an [adapter](https://svelte.dev/docs/kit/adapters) for your target environment.
```

# src/app.css

```css
@import 'tailwindcss';
```

# src/app.d.ts

```ts
// See https://svelte.dev/docs/kit/types#app.d.ts
// for information about these interfaces
declare global {
	namespace App {
		// interface Error {}
		// interface Locals {}
		// interface PageData {}
		// interface PageState {}
		// interface Platform {}
	}
}

export {};
```

# src/app.html

```html
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<link rel="icon" href="%sveltekit.assets%/favicon.png" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		%sveltekit.head%
	</head>
	<body data-sveltekit-preload-data="hover">
		<div style="display: contents">%sveltekit.body%</div>
	</body>
</html>
```

# src/lib/auth-form.svelte

```svelte
<script lang="ts">
	import { supabase } from './supabase';
	let email = $state('');
	let password = $state('');
	let mode = $state<'sign-in' | 'sign-up'>('sign-in');
	let error = $state<string | null>(null);
	let loading = $state(false);

	async function handleAuth(e: Event) {
		e.preventDefault();
		loading = true;
		error = null;
		if (mode === 'sign-in') {
			const { error: signInError } = await supabase.auth.signInWithPassword({ email, password });
			if (signInError) error = signInError.message;
		} else {
			const { error: signUpError } = await supabase.auth.signUp({ email, password });
			if (signUpError) error = signUpError.message;
		}
		loading = false;
	}
</script>

<div class="mx-auto max-w-sm rounded bg-white p-6 shadow">
	<div class="mb-4 flex">
		<button
			type="button"
			class="flex-1 rounded-l border border-r-0 border-gray-300 py-2 font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none {mode ===
			'sign-in'
				? 'bg-indigo-600 text-white'
				: 'bg-gray-100'}"
			on:click={() => (mode = 'sign-in')}>Sign In</button
		>
		<button
			type="button"
			class="flex-1 rounded-r border border-gray-300 py-2 font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none {mode ===
			'sign-up'
				? 'bg-indigo-600 text-white'
				: 'bg-gray-100'}"
			on:click={() => (mode = 'sign-up')}>Sign Up</button
		>
	</div>
	<form on:submit={handleAuth} class="space-y-4">
		<div>
			<label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
			<input
				id="email"
				name="email"
				type="email"
				bind:value={email}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
				required
			/>
		</div>
		<div>
			<label class="mb-1 block text-sm font-medium text-gray-700" for="password">Password</label>
			<input
				id="password"
				name="password"
				type="password"
				bind:value={password}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
				required
			/>
		</div>
		{#if error}
			<div class="text-sm text-red-600">{error}</div>
		{/if}
		<button
			type="submit"
			class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
			disabled={loading}
			>{loading ? 'Loading...' : mode === 'sign-in' ? 'Sign In' : 'Sign Up'}</button
		>
	</form>
</div>
```

# src/lib/index.ts

```ts
// place files you want to import through the `$lib` alias in this folder.
```

# src/lib/supabase.ts

```ts
import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.PUBLIC_SUPABASE_URL;
const supabaseAnonKey = import.meta.env.PUBLIC_SUPABASE_ANON_KEY;

console.log('SUPABASE_URL:', import.meta.env.PUBLIC_SUPABASE_URL);
console.log('SUPABASE_ANON_KEY:', import.meta.env.PUBLIC_SUPABASE_ANON_KEY);

if (!supabaseUrl || !supabaseAnonKey) {
	throw new Error('Missing Supabase environment variables');
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey);
```

# src/routes/+layout.svelte

```svelte
<script lang="ts">
	import '../app.css';
	import AuthForm from '$lib/auth-form.svelte';
	import { supabase } from '$lib/supabase';
	let session = $state<any>(null);

	$effect(async () => {
		const {
			data: { session: s }
		} = await supabase.auth.getSession();
		session = s;
		const { data: listener } = supabase.auth.onAuthStateChange((_event, sess) => {
			session = sess;
		});
		return () => listener.subscription.unsubscribe();
	});

	async function signOut() {
		await supabase.auth.signOut();
	}
</script>

<div class="min-h-screen bg-gray-50">
	<header class="bg-white shadow">
		<nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
			<div class="flex h-16 justify-between">
				<div class="flex">
					<div class="flex flex-shrink-0 items-center">
						<a href="/" class="text-xl font-bold text-gray-900">CV Builder</a>
					</div>
				</div>
				<div class="flex items-center gap-4">
					{#if session}
						<button on:click={signOut} class="text-gray-600 hover:text-gray-900">Sign Out</button>
						<a href="/profile" class="text-gray-600 hover:text-gray-900">Profile</a>
					{/if}
				</div>
			</div>
		</nav>
	</header>

	<main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
		{#if !session}
			<AuthForm />
		{:else}
			<slot />
		{/if}
	</main>
</div>

console.log('ALL ENV:', import.meta.env);
```

# src/routes/+page.svelte

```svelte
<script lang="ts">
	const sections = [
		{
			title: 'Profile',
			href: '/profile',
			description: 'Your personal information and contact details'
		},
		{
			title: 'Work Experience',
			href: '/work-experience',
			description: 'Your professional work history'
		},
		{ title: 'Projects', href: '/projects', description: 'Notable projects and achievements' },
		{ title: 'Education', href: '/education', description: 'Your academic background' },
		{ title: 'Skills', href: '/skills', description: 'Your technical and soft skills' },
		{
			title: 'Certifications',
			href: '/certifications',
			description: 'Professional certifications and qualifications'
		},
		{
			title: 'Professional Memberships',
			href: '/memberships',
			description: 'Professional organisations and memberships'
		},
		{ title: 'Interests', href: '/interests', description: 'Your hobbies and interests' }
	];

	console.log('ALL ENV:', import.meta.env);
</script>

<div class="py-12">
	<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
		<div class="text-center">
			<h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
				Build Your Professional CV
			</h1>
			<p
				class="mx-auto mt-3 max-w-md text-base text-gray-500 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl"
			>
				Create a comprehensive CV that showcases your professional journey. Get started by filling
				out each section below.
			</p>
		</div>

		<div class="mx-auto mt-12 grid max-w-lg gap-5 lg:max-w-none lg:grid-cols-3">
			{#each sections as section}
				<div class="flex flex-col overflow-hidden rounded-lg shadow-lg">
					<div class="flex flex-1 flex-col justify-between bg-white p-6">
						<div class="flex-1">
							<a href={section.href} class="mt-2 block">
								<p class="text-xl font-semibold text-gray-900">{section.title}</p>
								<p class="mt-3 text-base text-gray-500">{section.description}</p>
							</a>
						</div>
					</div>
				</div>
			{/each}
		</div>
	</div>
</div>
```

# src/routes/certifications/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Certification saved (not yet connected to database).' };
	}
};
```

# src/routes/certifications/+page.svelte

```svelte
<script lang="ts">
	let name = $state('');
	let issuer = $state('');
	let dateObtained = $state('');
	let expiryDate = $state('');
</script>

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
```

# src/routes/education/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Education saved (not yet connected to database).' };
	}
};
```

# src/routes/education/+page.svelte

```svelte
<script lang="ts">
	let institution = $state('');
	let degree = $state('');
	let fieldOfStudy = $state('');
	let startDate = $state('');
	let endDate = $state('');

	// Placeholder for fetched education entries
	let educationList: Array<{
		id: string;
		institution: string;
		degree: string;
		fieldOfStudy?: string;
		startDate: string;
		endDate?: string;
	}> = [];
</script>

<div class="mx-auto mb-8 max-w-xl">
	<h2 class="mb-4 text-xl font-bold">Your Education</h2>
	{#if educationList.length === 0}
		<div class="text-gray-500 italic">No education added yet.</div>
	{:else}
		<ul class="space-y-4">
			{#each educationList as edu}
				<li class="rounded border bg-white p-4 shadow">
					<div class="flex items-center justify-between">
						<div>
							<div class="font-semibold">{edu.degree} at {edu.institution}</div>
							<div class="text-sm text-gray-500">{edu.startDate} - {edu.endDate || 'Present'}</div>
						</div>
					</div>
					{#if edu.fieldOfStudy}
						<div class="mt-2 text-gray-700">Field: {edu.fieldOfStudy}</div>
					{/if}
				</li>
			{/each}
		</ul>
	{/if}
</div>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Add Education</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="institution">Institution</label
		>
		<input
			id="institution"
			name="institution"
			type="text"
			bind:value={institution}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="degree">Degree</label>
		<input
			id="degree"
			name="degree"
			type="text"
			bind:value={degree}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="fieldOfStudy"
			>Field of Study</label
		>
		<input
			id="fieldOfStudy"
			name="fieldOfStudy"
			type="text"
			bind:value={fieldOfStudy}
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
		>Save Education</button
	>
</form>
```

# src/routes/interests/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Interest saved (not yet connected to database).' };
	}
};
```

# src/routes/interests/+page.svelte

```svelte
<script lang="ts">
	let name = $state('');
	let description = $state('');
</script>

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
```

# src/routes/memberships/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Membership saved (not yet connected to database).' };
	}
};
```

# src/routes/memberships/+page.svelte

```svelte
<script lang="ts">
	let organisation = $state('');
	let role = $state('');
	let startDate = $state('');
	let endDate = $state('');
</script>

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
```

# src/routes/profile/+page.server.ts

```ts
import type { Actions } from './$types';
import { fail } from '@sveltejs/kit';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		// In the future, connect to Supabase here
		// For now, just return a success message
		return { success: true, message: 'Profile saved (not yet connected to database).' };
	}
};
```

# src/routes/profile/+page.svelte

```svelte
<script lang="ts">
	let fullName = $state('');
	let email = $state('');
	let phone = $state('');
	let location = $state('');
</script>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Profile</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="fullName">Full Name</label>
		<input
			id="fullName"
			name="fullName"
			type="text"
			bind:value={fullName}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
		<input
			id="email"
			name="email"
			type="email"
			bind:value={email}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="phone">Phone</label>
		<input
			id="phone"
			name="phone"
			type="tel"
			bind:value={phone}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="location">Location</label>
		<input
			id="location"
			name="location"
			type="text"
			bind:value={location}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		/>
	</div>
	<button
		type="submit"
		class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
		>Save Profile</button
	>
</form>
```

# src/routes/projects/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Project saved (not yet connected to database).' };
	}
};
```

# src/routes/projects/+page.svelte

```svelte
<script lang="ts">
	let title = $state('');
	let description = $state('');
	let startDate = $state('');
	let endDate = $state('');
	let url = $state('');

	// Placeholder for fetched projects
	let projects: Array<{
		id: string;
		title: string;
		description?: string;
		startDate?: string;
		endDate?: string;
		url?: string;
	}> = [];
</script>

<div class="mx-auto mb-8 max-w-xl">
	<h2 class="mb-4 text-xl font-bold">Your Projects</h2>
	{#if projects.length === 0}
		<div class="text-gray-500 italic">No projects added yet.</div>
	{:else}
		<ul class="space-y-4">
			{#each projects as project}
				<li class="rounded border bg-white p-4 shadow">
					<div class="flex items-center justify-between">
						<div>
							<div class="font-semibold">{project.title}</div>
							<div class="text-sm text-gray-500">
								{project.startDate || ''}
								{project.endDate ? `- ${project.endDate}` : ''}
							</div>
						</div>
					</div>
					{#if project.description}
						<div class="mt-2 text-gray-700">{project.description}</div>
					{/if}
					{#if project.url}
						<div class="mt-2 text-blue-600 underline">
							<a href={project.url} target="_blank">{project.url}</a>
						</div>
					{/if}
				</li>
			{/each}
		</ul>
	{/if}
</div>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Add Project</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="title">Title</label>
		<input
			id="title"
			name="title"
			type="text"
			bind:value={title}
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
	<div class="flex gap-4">
		<div class="flex-1">
			<label class="mb-1 block text-sm font-medium text-gray-700" for="startDate">Start Date</label>
			<input
				id="startDate"
				name="startDate"
				type="date"
				bind:value={startDate}
				class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="url">Project URL</label>
		<input
			id="url"
			name="url"
			type="url"
			bind:value={url}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		/>
	</div>
	<button
		type="submit"
		class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
		>Save Project</button
	>
</form>
```

# src/routes/skills/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Skill saved (not yet connected to database).' };
	}
};
```

# src/routes/skills/+page.svelte

```svelte
<script lang="ts">
	let name = $state('');
	let level = $state('');
	let category = $state('');
</script>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Skill</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="name">Skill Name</label>
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
		<label class="mb-1 block text-sm font-medium text-gray-700" for="level">Level</label>
		<input
			id="level"
			name="level"
			type="text"
			bind:value={level}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="category">Category</label>
		<input
			id="category"
			name="category"
			type="text"
			bind:value={category}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
		/>
	</div>
	<button
		type="submit"
		class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
		>Save Skill</button
	>
</form>
```

# src/routes/work-experience/+page.server.ts

```ts
import type { Actions } from './$types';

export const actions: Actions = {
	default: async ({ request }) => {
		const formData = await request.formData();
		return { success: true, message: 'Work experience saved (not yet connected to database).' };
	}
};
```

# src/routes/work-experience/+page.svelte

```svelte
<script lang="ts">
	let companyName = $state('');
	let position = $state('');
	let startDate = $state('');
	let endDate = $state('');
	let description = $state('');

	// Placeholder for fetched work experiences
	let workExperiences: Array<{
		id: string;
		companyName: string;
		position: string;
		startDate: string;
		endDate?: string;
		description?: string;
	}> = [];
</script>

<div class="mx-auto mb-8 max-w-xl">
	<h2 class="mb-4 text-xl font-bold">Your Work Experience</h2>
	{#if workExperiences.length === 0}
		<div class="text-gray-500 italic">No work experience added yet.</div>
	{:else}
		<ul class="space-y-4">
			{#each workExperiences as exp}
				<li class="rounded border bg-white p-4 shadow">
					<div class="flex items-center justify-between">
						<div>
							<div class="font-semibold">{exp.position} at {exp.companyName}</div>
							<div class="text-sm text-gray-500">{exp.startDate} - {exp.endDate || 'Present'}</div>
						</div>
						<!-- Edit/Delete buttons can go here -->
					</div>
					{#if exp.description}
						<div class="mt-2 text-gray-700">{exp.description}</div>
					{/if}
				</li>
			{/each}
		</ul>
	{/if}
</div>

<form method="POST" class="mx-auto max-w-xl space-y-6 rounded bg-white p-8 shadow">
	<h2 class="mb-4 text-2xl font-bold">Add Work Experience</h2>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="companyName"
			>Company Name</label
		>
		<input
			id="companyName"
			name="companyName"
			type="text"
			bind:value={companyName}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
		/>
	</div>
	<div>
		<label class="mb-1 block text-sm font-medium text-gray-700" for="position">Position</label>
		<input
			id="position"
			name="position"
			type="text"
			bind:value={position}
			class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
			required
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
		>Save Experience</button
	>
</form>
```

# static/favicon.png

This is a binary file of the type: Image

# supabase/schema.sql

```sql
-- Create profiles table
CREATE TABLE profiles (
    id UUID REFERENCES auth.users ON DELETE CASCADE,
    full_name TEXT,
    email TEXT UNIQUE,
    phone TEXT,
    location TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    PRIMARY KEY (id)
);

-- Create work_experience table
CREATE TABLE work_experience (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    company_name TEXT NOT NULL,
    position TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create projects table
CREATE TABLE projects (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    url TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create education table
CREATE TABLE education (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    institution TEXT NOT NULL,
    degree TEXT NOT NULL,
    field_of_study TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create skills table
CREATE TABLE skills (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    level TEXT,
    category TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create certifications table
CREATE TABLE certifications (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    issuer TEXT NOT NULL,
    date_obtained DATE NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create professional_memberships table
CREATE TABLE professional_memberships (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    organisation TEXT NOT NULL,
    role TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create interests table
CREATE TABLE interests (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create RLS policies
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE work_experience ENABLE ROW LEVEL SECURITY;
ALTER TABLE projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE education ENABLE ROW LEVEL SECURITY;
ALTER TABLE skills ENABLE ROW LEVEL SECURITY;
ALTER TABLE certifications ENABLE ROW LEVEL SECURITY;
ALTER TABLE professional_memberships ENABLE ROW LEVEL SECURITY;
ALTER TABLE interests ENABLE ROW LEVEL SECURITY;

-- Create policies for authenticated users
CREATE POLICY "Users can view their own profile"
    ON profiles FOR SELECT
    USING (auth.uid() = id);

CREATE POLICY "Users can update their own profile"
    ON profiles FOR UPDATE
    USING (auth.uid() = id);

-- Similar policies for other tables
CREATE POLICY "Users can manage their own work experience"
    ON work_experience FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own projects"
    ON projects FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own education"
    ON education FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own skills"
    ON skills FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own certifications"
    ON certifications FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own professional memberships"
    ON professional_memberships FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own interests"
    ON interests FOR ALL
    USING (auth.uid() = profile_id);
```

# svelte.config.js

```js
import adapter from '@sveltejs/adapter-auto';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	// Consult https://svelte.dev/docs/kit/integrations
	// for more information about preprocessors
	preprocess: vitePreprocess(),

	kit: {
		// adapter-auto only supports some environments, see https://svelte.dev/docs/kit/adapter-auto for a list.
		// If your environment is not supported, or you settled on a specific environment, switch out the adapter.
		// See https://svelte.dev/docs/kit/adapters for more information about adapters.
		adapter: adapter()
	}
};

export default config;
```

# tsconfig.json

```json
{
	"extends": "./.svelte-kit/tsconfig.json",
	"compilerOptions": {
		"allowJs": true,
		"checkJs": true,
		"esModuleInterop": true,
		"forceConsistentCasingInFileNames": true,
		"resolveJsonModule": true,
		"skipLibCheck": true,
		"sourceMap": true,
		"strict": true,
		"moduleResolution": "bundler"
	}
	// Path aliases are handled by https://svelte.dev/docs/kit/configuration#alias
	// except $lib which is handled by https://svelte.dev/docs/kit/configuration#files
	//
	// If you want to overwrite includes/excludes, make sure to copy over the relevant includes/excludes
	// from the referenced tsconfig.json - TypeScript does not merge them in
}
```

# vite.config.ts

```ts
import tailwindcss from '@tailwindcss/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [tailwindcss(), sveltekit()]
});
```
