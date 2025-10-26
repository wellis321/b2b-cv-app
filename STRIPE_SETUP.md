# Stripe Setup for Early Access

## Environment Variables Required

Add these to your `.env` file:

```bash
# Stripe Configuration
VITE_STRIPE_PUBLISHABLE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET_KEY=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

# Optional: Stripe Price ID for early access (if you want to use a pre-created price)
VITE_STRIPE_EARLY_ACCESS_PRICE_ID=price_your_price_id_here
```

## Stripe Dashboard Setup

1. **Create a Stripe Account**

   - Go to [stripe.com](https://stripe.com) and create an account
   - Complete the verification process

2. **Get Your API Keys**

   - In the Stripe Dashboard, go to Developers > API keys
   - Copy your publishable key and secret key
   - Use test keys for development, live keys for production

3. **Set Up Webhooks**

   - Go to Developers > Webhooks
   - Add endpoint: `https://yourdomain.com/api/stripe/webhook`
   - Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`
   - Copy the webhook signing secret

4. **Create a Product (Optional)**
   - Go to Products in the Dashboard
   - Create a product called "CV Builder Early Access"
   - Set price to £2.00
   - Copy the price ID if you want to use it

## Testing

- Use Stripe's test card numbers for testing:
  - Success: `4242 4242 4242 4242`
  - Decline: `4000 0000 0000 0002`
- Test with different expiry dates and CVC codes

## Production Deployment

1. Switch to live API keys
2. Update webhook endpoint to production URL
3. Test with small amounts first
4. Monitor webhook delivery in Stripe Dashboard
