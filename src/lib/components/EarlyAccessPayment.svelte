<script lang="ts">
	import { onMount } from 'svelte';
	import { stripePromise } from '$lib/stripe';
	import { session } from '$lib/stores/authStore';
	import { goto } from '$app/navigation';

	let loading = $state(false);
	let error = $state('');
	let success = $state(false);
	let stripe: any;
	let elements: any;
	let paymentElement: any;

	onMount(async () => {
		// Load Stripe
		stripe = await stripePromise;
		if (!stripe) {
			error = 'Failed to load Stripe';
			return;
		}

		// Create payment element
		elements = stripe.elements();
		paymentElement = elements.create('payment');
		paymentElement.mount('#payment-element');
	});

	async function handleSubmit() {
		if (!stripe || !elements) {
			error = 'Stripe not loaded';
			return;
		}

		loading = true;
		error = '';

		try {
			// Create payment intent
			const response = await fetch('/api/stripe/create-payment-intent', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				}
			});

			if (!response.ok) {
				throw new Error('Failed to create payment intent');
			}

			const { clientSecret } = await response.json();

			// Confirm payment
			const { error: paymentError } = await stripe.confirmPayment({
				elements,
				clientSecret,
				confirmParams: {
					return_url: `${window.location.origin}/dashboard?payment=success`
				}
			});

			if (paymentError) {
				error = paymentError.message || 'Payment failed';
			} else {
				success = true;
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Payment failed';
		} finally {
			loading = false;
		}
	}

	function handleCancel() {
		goto('/dashboard');
	}
</script>

<div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow-lg">
	<div class="mb-6 text-center">
		<h2 class="mb-2 text-2xl font-bold text-gray-900">Early Access</h2>
		<p class="mb-4 text-gray-600">Get early access to our CV Builder for just £2.00</p>
		<div class="mb-2 text-3xl font-bold text-green-600">£2.00</div>
		<p class="text-sm text-gray-500">One-time payment</p>
	</div>

	{#if error}
		<div class="mb-4 rounded border border-red-400 bg-red-100 p-3 text-red-700">
			{error}
		</div>
	{/if}

	{#if success}
		<div class="mb-4 rounded border border-green-400 bg-green-100 p-3 text-green-700">
			Payment successful! Redirecting...
		</div>
	{:else}
		<form on:submit|preventDefault={handleSubmit} class="space-y-4">
			<div id="payment-element" class="min-h-[200px]"></div>

			<div class="flex space-x-3">
				<button
					type="submit"
					disabled={loading || !stripe}
					class="flex-1 rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
				>
					{loading ? 'Processing...' : 'Pay £2.00'}
				</button>

				<button
					type="button"
					on:click={handleCancel}
					class="rounded-md border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
				>
					Cancel
				</button>
			</div>
		</form>
	{/if}

	<div class="mt-6 text-center text-xs text-gray-500">
		<p>Secure payment powered by Stripe</p>
		<p>Early access includes all premium features</p>
	</div>
</div>
