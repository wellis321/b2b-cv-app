<script lang="ts">
	import { onMount } from 'svelte';
	import { session } from '$lib/stores/authStore';
	import { goto } from '$app/navigation';
	import EarlyAccessPayment from '$lib/components/EarlyAccessPayment.svelte';
	import FeedbackForm from '$lib/components/FeedbackForm.svelte';
	import { page } from '$app/stores';

	let feedbackMessage = $state('');
	let feedbackMessageType = $state('success');

	onMount(() => {
		// Check if user is already logged in
		if (!$session?.user) {
			goto('/auth?redirect=/early-access');
			return;
		}
	});

	// Handle payment success redirect
	$effect(() => {
		if ($page.url.searchParams.get('payment') === 'success') {
			goto('/dashboard?early_access_granted=true');
		}
	});
</script>

<svelte:head>
	<title>Early Access | CV Builder</title>
</svelte:head>

<div class="min-h-screen bg-gray-50 py-12">
	<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
		<!-- Header -->
		<div class="mb-12 text-center">
			<h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">Get Early Access</h1>
			<p class="mx-auto mt-4 max-w-2xl text-xl text-gray-600">
				Be among the first to experience our CV Builder and help shape its future development.
			</p>
		</div>

		<!-- Benefits Grid -->
		<div class="mb-12 grid gap-8 md:grid-cols-3">
			<div class="text-center">
				<div
					class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100"
				>
					<svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M13 10V3L4 14h7v7l9-11h-7z"
						/>
					</svg>
				</div>
				<h3 class="mb-2 text-lg font-medium text-gray-900">Early Access</h3>
				<p class="text-gray-600">
					Get exclusive access to all premium features before public release
				</p>
			</div>

			<div class="text-center">
				<div
					class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100"
				>
					<svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"
						/>
					</svg>
				</div>
				<h3 class="mb-2 text-lg font-medium text-gray-900">Shape Development</h3>
				<p class="text-gray-600">Your feedback directly influences what features we build next</p>
			</div>

			<div class="text-center">
				<div
					class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-purple-100"
				>
					<svg
						class="h-6 w-6 text-purple-600"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
						/>
					</svg>
				</div>
				<h3 class="mb-2 text-lg font-medium text-gray-900">Lifetime Access</h3>
				<p class="text-gray-600">
					One payment, unlimited access to all current and future features
				</p>
			</div>
		</div>

		<!-- What You Get -->
		<div class="mb-12 rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-6 text-center text-2xl font-bold text-gray-900">What You'll Get</h2>
			<div class="grid gap-6 md:grid-cols-2">
				<div>
					<h3 class="mb-3 text-lg font-medium text-gray-900">Premium Features</h3>
					<ul class="space-y-2 text-gray-600">
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							Unlimited CV sections
						</li>
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							PDF export functionality
						</li>
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							All CV templates
						</li>
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							Priority support
						</li>
					</ul>
				</div>
				<div>
					<h3 class="mb-3 text-lg font-medium text-gray-900">Early Access Benefits</h3>
					<ul class="space-y-2 text-gray-600">
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							Beta feature testing
						</li>
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							Direct feedback channel
						</li>
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							Feature request priority
						</li>
						<li class="flex items-center">
							<svg class="mr-2 h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
								<path
									fill-rule="evenodd"
									d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							Exclusive community access
						</li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Payment Form -->
		<div class="flex justify-center">
			<EarlyAccessPayment />
		</div>

		<!-- Feedback Form for Early Access Users -->
		<div class="mt-16">
			<div class="mb-8 text-center">
				<h2 class="text-2xl font-bold text-gray-900">Help Shape Development</h2>
				<p class="text-gray-600">Your feedback directly influences what we build next</p>
			</div>

			{#if feedbackMessage}
				<div class="mx-auto mb-6 max-w-2xl">
					<div
						class="rounded-md p-4 {feedbackMessageType === 'success'
							? 'border border-green-400 bg-green-50 text-green-700'
							: 'border border-red-400 bg-red-50 text-red-700'}"
					>
						{feedbackMessage}
					</div>
				</div>
			{/if}

			<div class="mx-auto max-w-2xl">
				<FeedbackForm
					on:success={(e) => {
						feedbackMessage = e.detail.message;
						feedbackMessageType = 'success';
						setTimeout(() => (feedbackMessage = ''), 5000);
					}}
					on:error={(e) => {
						feedbackMessage = e.detail.message;
						feedbackMessageType = 'error';
						setTimeout(() => (feedbackMessage = ''), 5000);
					}}
				/>
			</div>
		</div>

		<!-- FAQ -->
		<div class="mt-16 rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-6 text-center text-2xl font-bold text-gray-900">Frequently Asked Questions</h2>
			<div class="space-y-6">
				<div>
					<h3 class="mb-2 text-lg font-medium text-gray-900">Is this a subscription?</h3>
					<p class="text-gray-600">
						No, this is a one-time payment of £2.00 for lifetime early access to all features.
					</p>
				</div>
				<div>
					<h3 class="mb-2 text-lg font-medium text-gray-900">What happens after I pay?</h3>
					<p class="text-gray-600">
						You'll immediately get access to all premium features and can start building your CV
						right away.
					</p>
				</div>
				<div>
					<h3 class="mb-2 text-lg font-medium text-gray-900">Can I cancel and get a refund?</h3>
					<p class="text-gray-600">
						Due to the digital nature of the product and immediate access granted, refunds are not
						available.
					</p>
				</div>
				<div>
					<h3 class="mb-2 text-lg font-medium text-gray-900">
						How long will early access pricing last?
					</h3>
					<p class="text-gray-600">
						This special pricing is only available during our early access phase. Once we launch
						publicly, pricing will increase significantly.
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
