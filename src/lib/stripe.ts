import { loadStripe } from '@stripe/stripe-js';
import Stripe from 'stripe';

// Client-side Stripe instance
export const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY);

// Server-side Stripe instance
export const stripe = new Stripe(import.meta.env.STRIPE_SECRET_KEY, {
    apiVersion: '2024-12-18.acacia',
});

// Early access product configuration
export const EARLY_ACCESS_PRICE_ID = import.meta.env.VITE_STRIPE_EARLY_ACCESS_PRICE_ID;
export const EARLY_ACCESS_AMOUNT = 200; // £2.00 in pence

// Types for payment data
export interface PaymentIntentData {
    amount: number;
    currency: string;
    metadata?: Record<string, string>;
}

export interface CreatePaymentIntentResponse {
    clientSecret: string;
    paymentIntentId: string;
}
