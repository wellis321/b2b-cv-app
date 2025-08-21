import { json } from '@sveltejs/kit';
import { stripe, EARLY_ACCESS_AMOUNT } from '$lib/stripe';
import { supabase } from '$lib/supabase';
import type { RequestHandler } from './$types';

export const POST: RequestHandler = async ({ request, cookies }) => {
    try {
        // Get the session from cookies
        const session = cookies.get('sb-access-token');

        if (!session) {
            return json({ error: 'Unauthorized' }, { status: 401 });
        }

        // Verify the session with Supabase
        const { data: { user }, error: authError } = await supabase.auth.getUser(session);

        if (authError || !user) {
            return json({ error: 'Invalid session' }, { status: 401 });
        }

        // Create a payment intent for £2 early access
        const paymentIntent = await stripe.paymentIntents.create({
            amount: EARLY_ACCESS_AMOUNT,
            currency: 'gbp',
            metadata: {
                userId: user.id,
                type: 'early_access',
                product: 'cv_builder_early_access'
            },
            automatic_payment_methods: {
                enabled: true,
            },
        });

        return json({
            clientSecret: paymentIntent.client_secret,
            paymentIntentId: paymentIntent.id
        });

    } catch (error) {
        console.error('Error creating payment intent:', error);
        return json({ error: 'Failed to create payment intent' }, { status: 500 });
    }
};
