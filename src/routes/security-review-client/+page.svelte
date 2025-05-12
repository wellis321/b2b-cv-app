<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { session as authSession } from '$lib/stores/authStore';

	// Define the task categories and items
	const categories = [
		{
			name: 'Sensitive Information Exposure',
			items: [
				{
					id: 'remove-credential-logging',
					description: 'Remove logging of Supabase credentials in src/lib/supabase.ts',
					completed: true
				},
				{
					id: 'env-var-handling',
					description:
						'Implement proper environment variable handling with separate dev/prod configs',
					completed: true
				}
			]
		},
		{
			name: 'Authentication and Authorization',
			items: [
				{
					id: 'improve-token-handling',
					description: 'Improve token handling in update-profile endpoint',
					completed: true
				},
				{
					id: 'remove-admin-client-rls-bypass',
					description: 'Remove or strictly limit admin client bypassing of RLS',
					completed: true
				},
				{
					id: 'csrf-protection',
					description: 'Add proper CSRF protection in API routes',
					completed: true
				},
				{
					id: 'cors-config',
					description: 'Implement proper CORS configuration for production',
					completed: false
				}
			]
		},
		{
			name: 'Error Handling and Logging',
			items: [
				{
					id: 'sanitize-logs',
					description: 'Sanitize error logs to remove sensitive information',
					completed: true
				},
				{
					id: 'structured-logging',
					description: 'Implement structured logging for production',
					completed: true
				},
				{
					id: 'error-boundaries',
					description: 'Add proper error boundaries in Svelte components',
					completed: false
				}
			]
		},
		{
			name: 'Security Headers',
			items: [
				{
					id: 'add-security-headers',
					description: 'Add security headers in svelte.config.js',
					completed: true
				}
			]
		},
		{
			name: 'Database Security',
			items: [
				{
					id: 'complete-rls-policies',
					description: 'Complete RLS policies for all operations on all tables',
					completed: false
				},
				{
					id: 'review-nested-data-access',
					description: 'Review nested data access patterns for potential security issues',
					completed: false
				}
			]
		},
		{
			name: 'Deployment Configuration',
			items: [
				{
					id: 'specific-adapter',
					description: 'Use a specific adapter for your hosting environment',
					completed: false
				},
				{
					id: 'implement-rate-limiting',
					description: 'Implement rate limiting for authentication and API endpoints',
					completed: false
				},
				{
					id: 'add-waf',
					description: 'Consider adding a Web Application Firewall (WAF)',
					completed: false
				}
			]
		},
		{
			name: 'Dependencies and Versioning',
			items: [
				{
					id: 'update-dependencies',
					description: 'Update all dependencies to latest stable versions',
					completed: false
				},
				{
					id: 'standardize-auth-approach',
					description: 'Standardize auth approach using latest SvelteKit patterns',
					completed: false
				},
				{
					id: 'dependency-scanning',
					description: 'Implement dependency scanning in CI/CD',
					completed: false
				}
			]
		},
		{
			name: 'Data Validation',
			items: [
				{
					id: 'strong-validation',
					description: 'Add strong validation for all user inputs using a library like Zod',
					completed: false
				},
				{
					id: 'server-side-validation',
					description: 'Implement server-side validation even if client has validation',
					completed: false
				}
			]
		},
		{
			name: 'Secrets Management',
			items: [
				{
					id: 'env-vars-for-secrets',
					description: 'Use environment variables for all secrets',
					completed: true
				},
				{
					id: 'secrets-management',
					description: 'Implement proper secrets management for production (e.g., Supabase Vault)',
					completed: false
				},
				{
					id: 'no-env-commit',
					description: 'Never commit any .env files to the repository',
					completed: true
				}
			]
		},
		{
			name: 'Logging and Monitoring',
			items: [
				{
					id: 'structured-logging-impl',
					description: 'Implement proper structured logging',
					completed: false
				},
				{
					id: 'monitoring-auth-failures',
					description: 'Add monitoring for authentication failures and suspicious activity',
					completed: false
				},
				{
					id: 'security-alerts',
					description: 'Set up alerts for security events',
					completed: false
				}
			]
		},
		{
			name: 'Performance Optimization',
			items: [
				{
					id: 'caching-strategies',
					description: 'Implement proper caching strategies',
					completed: false
				},
				{
					id: 'error-boundaries-perf',
					description: 'Add proper error boundaries for component failures',
					completed: false
				},
				{
					id: 'http2-support',
					description: 'Enable HTTP/2 support',
					completed: false
				},
				{
					id: 'service-worker',
					description: 'Consider implementing a Service Worker for offline capabilities',
					completed: false
				}
			]
		},
		{
			name: 'CI/CD Pipeline',
			items: [
				{
					id: 'security-scanning',
					description: 'Add automated security scanning',
					completed: false
				},
				{
					id: 'dependency-vulnerability',
					description: 'Implement dependency vulnerability checking',
					completed: false
				},
				{
					id: 'automated-testing',
					description: 'Add automated testing',
					completed: false
				},
				{
					id: 'staging-environment',
					description: 'Configure staging environment',
					completed: false
				}
			]
		},
		{
			name: 'Backup and Recovery',
			items: [
				{
					id: 'database-backups',
					description: 'Implement regular database backups',
					completed: false
				},
				{
					id: 'disaster-recovery',
					description: 'Create disaster recovery procedures',
					completed: false
				},
				{
					id: 'test-restoration',
					description: 'Test restoration processes',
					completed: false
				}
			]
		},
		{
			name: 'Scalability',
			items: [
				{
					id: 'bottlenecks-review',
					description: 'Review potential bottlenecks in data loading',
					completed: false
				},
				{
					id: 'database-indexing',
					description: 'Implement proper database indexing',
					completed: false
				},
				{
					id: 'edge-caching',
					description: 'Consider edge caching for static assets',
					completed: false
				}
			]
		},
		{
			name: 'Documentation',
			items: [
				{
					id: 'api-documentation',
					description: 'Create detailed API documentation',
					completed: false
				},
				{
					id: 'security-docs',
					description: 'Document security practices and policies',
					completed: false
				},
				{
					id: 'user-guides',
					description: 'Create user guides for account security',
					completed: false
				}
			]
		}
	];

	// Using runes for reactive state
	let errorMessage = $state('');
	let progressPercentage = $state(0);
	let isLoading = $state(true);
	let userIdSource = $state('client-only');

	// Make the categories reactive to track changes
	let categoriesState = $state(categories);

	// Use client-only ID
	let userId = $state('client-only');

	// Local storage key
	const storageKey = 'security-review-progress-client-only';

	// Save to localStorage whenever the completion status changes
	function saveProgress() {
		if (!browser) return;

		const progressData = categoriesState.map((category) => ({
			name: category.name,
			items: category.items.map((item) => ({
				id: item.id,
				completed: item.completed
			}))
		}));

		localStorage.setItem(storageKey, JSON.stringify(progressData));
		console.log(`Progress saved to localStorage with key: ${storageKey}`);
	}

	// Load from localStorage on mount
	onMount(() => {
		if (!browser) return;

		console.log('Security review page mounted');

		// Try to check session if available
		if ($authSession?.user?.id) {
			console.log('Auth session detected:', $authSession.user.id);
		} else {
			console.log('No auth session detected, using client-only mode');
		}

		const savedProgress = localStorage.getItem(storageKey);
		if (savedProgress) {
			try {
				const progressData = JSON.parse(savedProgress);
				console.log(`Loaded progress from localStorage key: ${storageKey}`);

				// Update the completion status based on saved data
				progressData.forEach((savedCategory: any) => {
					const category = categoriesState.find((c) => c.name === savedCategory.name);
					if (category) {
						savedCategory.items.forEach((savedItem: any) => {
							const item = category.items.find((i) => i.id === savedItem.id);
							if (item) {
								item.completed = savedItem.completed;
							}
						});
					}
				});

				// Recalculate progress
				updateProgress();
			} catch (error) {
				console.error('Error loading saved progress:', error);
			}
		} else {
			console.log(`No saved progress found for key: ${storageKey}`);
		}

		isLoading = false;
	});

	// Function to update the progress percentage
	function updateProgress() {
		let completedCount = 0;
		let totalCount = 0;

		categoriesState.forEach((category) => {
			category.items.forEach((item) => {
				totalCount++;
				if (item.completed) {
					completedCount++;
				}
			});
		});

		// Update progress percentage
		if (totalCount > 0) {
			progressPercentage = Math.floor((completedCount / totalCount) * 100);
		}
	}

	// Toggle item completion
	function toggleItem(item: any) {
		item.completed = !item.completed;
		updateProgress();
		saveProgress();
	}

	// Effect to update progress when items change
	$effect(() => {
		// This will re-run whenever categoriesState changes
		updateProgress();
	});
</script>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
	{#if isLoading}
		<div class="flex h-32 items-center justify-center">
			<div class="text-center">
				<p class="text-lg">Loading security review tracker...</p>
			</div>
		</div>
	{:else}
		<div class="mb-12 text-center">
			<h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">
				Security & Production Review Tracker
			</h1>
			<p class="mt-4 text-lg text-gray-500">
				Track progress implementing security and production improvements
			</p>

			<div class="mx-auto mt-8 max-w-xl">
				<div class="h-4 overflow-hidden rounded-full bg-gray-200">
					<div
						class="h-full rounded-full bg-indigo-600 transition-all duration-500"
						style="width: {progressPercentage}%"
					></div>
				</div>
				<p class="mt-2 text-sm text-gray-600">
					<span class="font-medium">{progressPercentage}%</span> of tasks completed
				</p>
			</div>
		</div>

		<div class="space-y-12">
			{#each categoriesState as category}
				<div class="overflow-hidden bg-white shadow sm:rounded-md">
					<div class="bg-gray-50 px-4 py-5 sm:px-6">
						<h3 class="text-lg leading-6 font-medium text-gray-900">{category.name}</h3>
					</div>
					<ul class="divide-y divide-gray-200">
						{#each category.items as item}
							<li>
								<div class="flex items-center px-4 py-4 sm:px-6">
									<label class="flex flex-1 cursor-pointer items-center">
										<input
											type="checkbox"
											class="h-5 w-5 rounded text-indigo-600 focus:ring-indigo-500"
											checked={item.completed}
											onchange={() => toggleItem(item)}
										/>
										<span
											class="ml-3 text-sm text-gray-700 transition-all duration-200"
											class:line-through={item.completed}
											class:text-gray-400={item.completed}
										>
											{item.description}
										</span>
									</label>
								</div>
							</li>
						{/each}
					</ul>
				</div>
			{/each}
		</div>
	{/if}
</div>
