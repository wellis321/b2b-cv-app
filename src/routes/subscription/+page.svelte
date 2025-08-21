<script lang="ts">
	import { onMount } from 'svelte';
	import {
		subscriptionPlans,
		currentSubscription,
		subscriptionLoading,
		updateUserSubscription
	} from '$lib/stores/subscriptionStore';
	import type { SubscriptionPlan } from '$lib/stores/subscriptionStore';
	import { goto } from '$app/navigation';

	let loadingPlan = $state(false);
	let successMessage = $state('');
	let errorMessage = $state('');
	let selectedPlanId = $state('');

	onMount(() => {
		// Set the selected plan to the current plan
		if ($currentSubscription.plan) {
			selectedPlanId = $currentSubscription.plan.id;
		}
	});

	// Format currency for display
	function formatCurrency(amount: number, currency: string): string {
		return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(amount);
	}

	// Format date for display
	function formatDate(dateString: string | null): string {
		if (!dateString) return 'Never';

		const date = new Date(dateString);
		return new Intl.DateTimeFormat('en-GB', {
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		}).format(date);
	}

	// Helper to check if a subscription is active
	function isSubscriptionActive(): boolean {
		return $currentSubscription.isActive;
	}

	// Calculate subscription end date (30 days from now for monthly, 365 for yearly)
	function calculateExpiryDate(interval: string): Date {
		const now = new Date();
		if (interval === 'year') {
			return new Date(now.setFullYear(now.getFullYear() + 1));
		} else {
			return new Date(now.setMonth(now.getMonth() + 1));
		}
	}

	// Handle subscription plan selection
	async function handleSelectPlan(plan: SubscriptionPlan) {
		if (plan.id === $currentSubscription.plan?.id) {
			// Already on this plan
			successMessage = `You are already subscribed to the ${plan.name} plan.`;
			errorMessage = '';
			return;
		}

		try {
			loadingPlan = true;
			errorMessage = '';
			successMessage = '';

			// If this is the free plan, just update without an expiry date
			if (plan.price === 0) {
				const result = await updateUserSubscription(plan.id);
				if (result) {
					successMessage = `Successfully switched to the ${plan.name} plan.`;
				} else {
					errorMessage = 'Failed to update subscription.';
				}
				return;
			}

			// For paid plans, we would normally integrate with a payment processor
			// For development, we'll just update the subscription with a future expiry date
			const expiryDate = calculateExpiryDate(plan.interval);
			const result = await updateUserSubscription(plan.id, expiryDate);

			if (result) {
				successMessage = `Successfully subscribed to the ${plan.name} plan until ${formatDate(expiryDate.toISOString())}.`;
				selectedPlanId = plan.id;
			} else {
				errorMessage = 'Failed to update subscription.';
			}
		} catch (err) {
			console.error('Error updating subscription:', err);
			errorMessage = 'An error occurred while updating your subscription.';
		} finally {
			loadingPlan = false;
		}
	}
</script>

<svelte:head>
	<title>Subscription Plans | CV Builder</title>
</svelte:head>

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
	<div class="mb-12 text-center">
		<h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
			Subscription Plans
		</h1>
		<p class="mx-auto mt-5 max-w-xl text-xl text-gray-500">
			Choose the plan that's right for you and take your CV to the next level.
		</p>
		<div class="mt-4">
			<a
				href="/early-access"
				class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-6 py-3 text-base font-medium text-white hover:bg-green-700"
			>
				🎯 Get Early Access for £2.00
			</a>
		</div>
	</div>

	{#if successMessage}
		<div class="mb-8 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
			{successMessage}
		</div>
	{/if}

	{#if errorMessage}
		<div class="mb-8 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
			{errorMessage}
		</div>
	{/if}

	{#if $subscriptionLoading || loadingPlan}
		<div class="my-12 flex justify-center">
			<div
				class="h-12 w-12 animate-spin rounded-full border-t-2 border-b-2 border-indigo-500"
			></div>
		</div>
	{:else}
		<!-- Current subscription info -->
		{#if $currentSubscription.plan}
			<div class="mb-8 overflow-hidden rounded-lg bg-white shadow">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900">Your Current Subscription</h3>
					<div class="mt-2 max-w-xl text-sm text-gray-500">
						<p>You are currently on the <strong>{$currentSubscription.plan.name}</strong> plan.</p>
						{#if $currentSubscription.expiresAt && $currentSubscription.plan.price > 0}
							<p class="mt-1">
								Your subscription is valid until {formatDate($currentSubscription.expiresAt)}.
							</p>
						{/if}
					</div>
				</div>
			</div>
		{/if}

		<!-- Subscription plans grid -->
		<div class="grid gap-6 lg:grid-cols-3">
			{#each $subscriptionPlans as plan}
				<div
					class="overflow-hidden rounded-lg border-2 bg-white shadow {selectedPlanId === plan.id
						? 'border-indigo-500'
						: 'border-transparent'}"
				>
					<div class="px-4 py-5 sm:p-6">
						<h3 class="text-lg leading-6 font-medium text-gray-900">{plan.name}</h3>
						<p class="mt-1 text-3xl font-extrabold text-gray-900">
							{formatCurrency(plan.price, plan.currency)}
							<span class="text-base font-medium text-gray-500">/{plan.interval}</span>
						</p>
						<p class="mt-3 text-sm text-gray-500">{plan.description}</p>

						<ul class="mt-6 space-y-4">
							<li class="flex items-start">
								<div class="flex-shrink-0">
									<!-- Checkmark icon -->
									<svg
										class="h-5 w-5 text-green-500"
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											fill-rule="evenodd"
											d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
											clip-rule="evenodd"
										/>
									</svg>
								</div>
								<p class="ml-3 text-sm text-gray-700">
									{plan.features.max_sections === -1 ? 'Unlimited' : plan.features.max_sections} CV sections
								</p>
							</li>
							<li class="flex items-start">
								<div class="flex-shrink-0">
									{#if plan.features.pdf_export}
										<!-- Checkmark icon -->
										<svg
											class="h-5 w-5 text-green-500"
											xmlns="http://www.w3.org/2000/svg"
											viewBox="0 0 20 20"
											fill="currentColor"
										>
											<path
												fill-rule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
												clip-rule="evenodd"
											/>
										</svg>
									{:else}
										<!-- X icon -->
										<svg
											class="h-5 w-5 text-gray-400"
											xmlns="http://www.w3.org/2000/svg"
											viewBox="0 0 20 20"
											fill="currentColor"
										>
											<path
												fill-rule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
												clip-rule="evenodd"
											/>
										</svg>
									{/if}
								</div>
								<p class="ml-3 text-sm text-gray-700">PDF Export</p>
							</li>
							<li class="flex items-start">
								<div class="flex-shrink-0">
									{#if plan.features.online_cv}
										<!-- Checkmark icon -->
										<svg
											class="h-5 w-5 text-green-500"
											xmlns="http://www.w3.org/2000/svg"
											viewBox="0 0 20 20"
											fill="currentColor"
										>
											<path
												fill-rule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
												clip-rule="evenodd"
											/>
										</svg>
									{:else}
										<!-- X icon -->
										<svg
											class="h-5 w-5 text-gray-400"
											xmlns="http://www.w3.org/2000/svg"
											viewBox="0 0 20 20"
											fill="currentColor"
										>
											<path
												fill-rule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
												clip-rule="evenodd"
											/>
										</svg>
									{/if}
								</div>
								<p class="ml-3 text-sm text-gray-700">Online CV</p>
							</li>
							<li class="flex items-start">
								<div class="flex-shrink-0">
									<!-- Checkmark icon -->
									<svg
										class="h-5 w-5 text-green-500"
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											fill-rule="evenodd"
											d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
											clip-rule="evenodd"
										/>
									</svg>
								</div>
								<p class="ml-3 text-sm text-gray-700">
									{plan.features.templates.length}
									{plan.features.templates.length === 1 ? 'template' : 'templates'}
								</p>
							</li>
						</ul>

						<div class="mt-8">
							<button
								on:click={() => handleSelectPlan(plan)}
								disabled={loadingPlan || (selectedPlanId === plan.id && isSubscriptionActive())}
								class="inline-flex w-full items-center justify-center rounded-md border border-transparent px-5 py-3 text-base font-medium
                  {selectedPlanId === plan.id && isSubscriptionActive()
									? 'cursor-not-allowed bg-gray-200 text-gray-800'
									: 'bg-indigo-600 text-white hover:bg-indigo-700'}"
							>
								{#if selectedPlanId === plan.id && isSubscriptionActive()}
									Current Plan
								{:else}
									{plan.price === 0 ? 'Select Free Plan' : 'Subscribe'}
								{/if}
							</button>
						</div>
					</div>
				</div>
			{/each}
		</div>
	{/if}

	<!-- Documentation Section -->
	<div class="mt-16 overflow-hidden rounded-lg bg-white shadow">
		<div class="px-4 py-5 sm:p-6">
			<h2 class="mb-4 text-2xl font-bold text-gray-900">Subscription Features</h2>

			<div class="prose max-w-none">
				<h3>Free Plan</h3>
				<p>The free plan gives you access to basic CV features:</p>
				<ul>
					<li>Up to 3 CV sections (Personal Profile + 2 more)</li>
					<li>Online CV sharing</li>
					<li>Basic template</li>
				</ul>

				<h3 class="mt-6">Premium Plan</h3>
				<p>Upgrade to premium for full access to all CV features:</p>
				<ul>
					<li>Unlimited CV sections</li>
					<li>PDF export functionality</li>
					<li>Online CV sharing</li>
					<li>Access to all CV templates</li>
				</ul>

				<h3 class="mt-6">Templates</h3>
				<p>Premium subscribers get access to the following templates:</p>
				<ul>
					<li><strong>Basic</strong> - Clean and simple layout (Available on all plans)</li>
					<li><strong>Professional</strong> - Sophisticated design for corporate environments</li>
					<li><strong>Modern</strong> - Contemporary styling with accent colors</li>
					<li><strong>Creative</strong> - Distinctive design for creative industries</li>
					<li><strong>Executive</strong> - Elegant design for senior positions</li>
					<li><strong>Simple</strong> - Minimalist black and white design</li>
					<li><strong>Classic</strong> - Traditional format with maroon accents</li>
					<li><strong>Elegant</strong> - Refined style with indigo highlights</li>
					<li><strong>Minimalist</strong> - Ultra-clean with minimal elements</li>
					<li><strong>Bold</strong> - Eye-catching with strong orange accents</li>
					<li><strong>Academic</strong> - Formal layout for research and education</li>
					<li><strong>Technical</strong> - Structured format for technical roles</li>
				</ul>

				<h3 class="mt-6">Pricing</h3>
				<p>
					<strong>Free Plan:</strong> £0/month<br />
					<strong>Premium Monthly:</strong> £7.99/month<br />
					<strong>Premium Annual:</strong> £79.99/year (Save over 16% compared to monthly)
				</p>

				<h3 class="mt-6">Billing</h3>
				<p>
					For development purposes, no actual payment is processed. In a production environment,
					this would integrate with a payment provider like Stripe.
				</p>
			</div>
		</div>
	</div>
</div>
