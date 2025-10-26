# Vercel Deployment Guide

## Environment Variables Required

To deploy this CV app to Vercel, you need to set the following environment variables in your Vercel project settings:

### Required for Build

- `STRIPE_SECRET_KEY` - Your Stripe secret key (required for server-side operations)
- `STRIPE_WEBHOOK_SECRET` - Your Stripe webhook endpoint secret
- `VITE_STRIPE_PUBLISHABLE_KEY` - Your Stripe publishable key (for client-side)
- `VITE_STRIPE_EARLY_ACCESS_PRICE_ID` - Your Stripe price ID for early access

### Supabase Configuration

- `SUPABASE_URL` - Your Supabase project URL
- `SUPABASE_ANON_KEY` - Your Supabase anonymous key
- `SUPABASE_SERVICE_ROLE_KEY` - Your Supabase service role key

## Setting Environment Variables in Vercel

1. Go to your Vercel project dashboard
2. Navigate to Settings > Environment Variables
3. Add each variable with the appropriate value
4. Make sure to set them for all environments (Production, Preview, Development)

## Build Process

The app is configured to handle missing environment variables gracefully during build time:

- Stripe instances are only created when environment variables are available
- API routes check for configuration before processing requests
- Build will succeed even if Stripe keys are not set (though runtime functionality will be limited)

## Troubleshooting

If you encounter build errors:

1. Verify all required environment variables are set in Vercel
2. Check that the environment variables are set for the correct environment
3. Ensure the Stripe API keys are valid and have the correct permissions
4. Verify Supabase configuration is correct

## Local Development

For local development, create a `.env.local` file with the same environment variables.
