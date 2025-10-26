# CV Builder Subscription System

## Overview

The CV Builder app offers a tiered subscription model with free and premium plans. This document outlines the implementation details, features, and usage of the subscription system.

## Subscription Plans

### Free Plan

- Limited to 3 CV sections (Personal Profile + 2 more sections)
- Basic template for PDF export (PDF export disabled)
- Online CV sharing enabled
- No expiration

### Premium Plan

- Unlimited CV sections
- PDF export functionality with multiple templates
- Online CV sharing enabled
- Monthly or annual billing options

## Database Schema

The subscription system uses two main tables:

1. **subscription_plans**: Stores the details of available subscription plans

   - `id`: UUID primary key
   - `name`: Plan name (e.g., "Free", "Premium")
   - `description`: Text description of the plan
   - `price`: Decimal price amount
   - `currency`: 3-letter currency code
   - `interval`: Billing interval ("month" or "year")
   - `features`: JSONB object containing plan features
   - `is_active`: Boolean flag for active plans
   - `created_at`, `updated_at`: Timestamps

2. **profiles** (extended): User profiles with subscription fields
   - `subscription_plan_id`: Foreign key to subscription_plans
   - `subscription_expires_at`: Timestamp for subscription expiry

## Frontend Components

### Stores

#### subscriptionStore.ts

The subscription store manages the state and operations related to subscription plans:

- `subscriptionPlans`: Writable store containing all available plans
- `currentSubscription`: Writable store with the user's active subscription
- `canAccessFeature`: Derived store to check feature access
- `loadSubscriptionPlans()`: Fetches all available plans
- `loadUserSubscription()`: Loads the current user's subscription
- `updateUserSubscription()`: Updates a user's subscription plan

### Utilities

#### subscriptionUtils.ts

Utility functions for checking feature access:

- `checkFeatureAccess()`: Checks if a user can access a feature
- `requireFeatureAccess()`: Redirects to subscription page if access is denied
- `checkSectionLimits()`: Checks if user is within section limits
- `getMaxSections()`: Gets the maximum sections allowed
- `getAvailableTemplates()`: Gets available PDF templates
- `canExportPdf()`: Checks if PDF export is available
- `canAccessOnlineCV()`: Checks if online CV access is available

## Usage Examples

### Checking Feature Access

```typescript
import { checkFeatureAccess } from '$lib/utils/subscriptionUtils';

// Check if user can export PDF
if (checkFeatureAccess('pdf_export')) {
	// Show PDF export button
}

// Check if user can use a specific template
if (checkFeatureAccess('templates', 'professional')) {
	// Allow professional template selection
}

// Check section limits
if (checkFeatureAccess('max_sections', currentSectionCount)) {
	// Allow adding more sections
}
```

### Requiring Feature Access

```typescript
import { requireFeatureAccess } from '$lib/utils/subscriptionUtils';

// This will redirect to subscription page if the user doesn't have access
if (requireFeatureAccess('pdf_export')) {
	// Generate PDF
}
```

### Implementing in Components

Example of conditionally rendering features based on subscription:

```svelte
<script>
	import { canExportPdf, getAvailableTemplates } from '$lib/utils/subscriptionUtils';

	// Get available templates
	const templates = getAvailableTemplates();
</script>

{#if canExportPdf()}
	<button on:click={generatePdf}>Export PDF</button>
{:else}
	<button on:click={goToSubscriptionPage}>Upgrade to Export PDF</button>
{/if}
```

## Implementation Notes

1. The subscription system is initialized in the app layout (`+layout.svelte`) by calling `initializeSubscription()`.

2. Feature access is controlled by the `canAccessFeature` derived store, which checks the current subscription against requested features.

3. For development purposes, no actual payment processing is implemented. In production, this would integrate with a payment provider like Stripe.

4. Row Level Security (RLS) policies in Supabase ensure users can only view and modify their own subscription data.

5. PDF templates are restricted based on the user's subscription plan.

## Future Enhancements

1. Integration with a payment processor (Stripe)
2. Subscription management dashboard for administrators
3. Usage analytics and reporting
4. Promotional codes and discounts
5. Tiered feature access for different premium plans

## Conclusion

The subscription system provides a flexible framework for monetizing the CV Builder app while offering a free tier to attract users. By restricting certain premium features, we can provide value to paying customers while still offering useful functionality to free users.
