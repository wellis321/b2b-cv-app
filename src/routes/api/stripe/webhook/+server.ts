import { json } from '@sveltejs/kit';
import { stripe, isStripeConfigured } from '$lib/stripe';
import { supabase } from '$lib/supabase';
import type { RequestHandler } from './$types';

export const POST: RequestHandler = async ({ request }) => {
    // Check if Stripe is configured
    if (!isStripeConfigured()) {
        return json({ error: 'Stripe is not configured' }, { status: 500 });
    }

    if (!stripe) {
        return json({ error: 'Stripe instance not available' }, { status: 500 });
    }

    // Check if webhook secret is configured
    const webhookSecret = import.meta.env.STRIPE_WEBHOOK_SECRET;
    if (!webhookSecret) {
        return json({ error: 'Webhook secret not configured' }, { status: 500 });
    }

    const body = await request.text();
    const signature = request.headers.get('stripe-signature');

    if (!signature) {
        return json({ error: 'No signature' }, { status: 400 });
    }

    let event;

    try {
        // Verify the webhook signature
        event = stripe.webhooks.constructEvent(
            body,
            signature,
            webhookSecret
        );
    } catch (err) {
        console.error('Webhook signature verification failed:', err);
        return json({ error: 'Invalid signature' }, { status: 500 });
    }

    try {
        switch (event.type) {
            case 'payment_intent.succeeded':
                const paymentIntent = event.data.object;

                // Check if this is an early access payment
                if (paymentIntent.metadata?.type === 'early_access') {
                    const userId = paymentIntent.metadata.userId;

                    // Update user's subscription to early access
                    const { error } = await supabase
                        .from('profiles')
                        .update({
                            subscription_plan_id: 'early_access',
                            subscription_expires_at: null, // Early access doesn't expire
                            early_access_granted_at: new Date().toISOString()
                        })
                        .eq('id', userId);

                    if (error) {
                        console.error('Error updating user subscription:', error);
                        return json({ error: 'Failed to update subscription' }, { status: 500 });
                    }

                    console.log(`Early access granted to user: ${userId}`);
                }
                break;

            case 'payment_intent.payment_failed':
                console.log('Payment failed:', event.data.object.id);
                break;

            default:
                console.log(`Unhandled event type: ${event.type}`);
        }

        return json({ received: true });
    } catch (error) {
        console.error('Error processing webhook:', error);
        return json({ error: 'Webhook processing failed' }, { status: 500 });
    }
};
