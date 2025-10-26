import { browser } from '$app/environment';
import { get } from 'svelte/store';
import { canAccessFeature, currentSubscription } from '$lib/stores/subscriptionStore';
import { goto } from '$app/navigation';

/**
 * Helper to check if user can access a particular feature
 * @param featureName Feature to check (e.g., 'pdf_export', 'templates')
 * @param value Optional value to check against (e.g., template name)
 * @returns Boolean indicating if user can access the feature
 */
export function checkFeatureAccess(featureName: string, value?: any): boolean {
	if (!browser) return true; // Always allow server-side rendering

	const featureAccessFn = get(canAccessFeature);
	return featureAccessFn(featureName, value);
}

/**
 * Redirects to subscription page if user doesn't have access to feature
 * @param featureName Feature to check (e.g., 'pdf_export', 'templates')
 * @param value Optional value to check against
 * @returns Boolean indicating if user can access the feature
 */
export function requireFeatureAccess(featureName: string, value?: any): boolean {
	if (!browser) return true;

	const hasAccess = checkFeatureAccess(featureName, value);

	if (!hasAccess) {
		// Redirect to subscription page with a query parameter
		goto('/subscription?required=' + featureName);
		return false;
	}

	return true;
}

/**
 * Count the number of CV sections a user has
 * @param sectionCounts Object containing counts of each section
 * @returns Boolean indicating if user is within their section limit
 */
export function checkSectionLimits(sectionCounts: Record<string, number>): boolean {
	if (!browser) return true;

	// Calculate total sections (excluding profile, which is always allowed)
	const totalSections = Object.values(sectionCounts).reduce((sum, count) => sum + count, 0);

	return checkFeatureAccess('max_sections', totalSections);
}

/**
 * Get the maximum number of sections allowed for the current user
 * @returns Number representing max sections, or -1 for unlimited
 */
export function getMaxSections(): number {
	if (!browser) return -1;

	const subscription = get(currentSubscription);
	if (!subscription.isActive || !subscription.plan) {
		return 3; // Default for free plan
	}

	return subscription.plan.features.max_sections;
}

/**
 * Get available templates for the current user
 * @returns Array of template names the user can access
 */
export function getAvailableTemplates(): string[] {
	if (!browser) return ['basic']; // Default for SSR

	// Temporarily return all templates in both development and production
	// until subscription system is fully implemented
	return ['basic', 'professional', 'modern', 'executive', 'creative', 'minimal'];

	// TODO: Re-enable subscription-based template access after subscription system is fully implemented
	/*
    const subscription = get(currentSubscription);
    if (!subscription.isActive || !subscription.plan) {
        return ['basic']; // Default for free/no plan
    }

    // Different templates based on subscription level
    switch (subscription.plan.name.toLowerCase()) {
        case 'free':
            return ['basic'];
        case 'starter':
        case 'basic':
            return ['basic', 'professional', 'minimal'];
        case 'pro':
        case 'professional':
            return ['basic', 'professional', 'modern', 'minimal', 'executive'];
        case 'premium':
        case 'business':
            return ['basic', 'professional', 'modern', 'executive', 'creative', 'minimal'];
        default:
            // Fall back to plan features if defined, otherwise provide basic
            return subscription.plan.features.templates || ['basic'];
    }
    */
}

/**
 * Check if PDF export is available for the current user
 * @returns Boolean indicating if PDF export is available
 */
export function canExportPdf(): boolean {
	return checkFeatureAccess('pdf_export');
}

/**
 * Check if online CV is available for the current user
 * @returns Boolean indicating if online CV is available
 */
export function canAccessOnlineCV(): boolean {
	return checkFeatureAccess('online_cv');
}
