import { writable, derived, get } from 'svelte/store';
import { session } from '$lib/stores/authStore';
import { supabase } from '$lib/supabase';
import { browser } from '$app/environment';
import { safeLog } from '$lib/config';
import type { Json } from '$lib/database.types';

export interface SubscriptionFeatures {
    max_sections: number;  // -1 means unlimited
    pdf_export: boolean;
    online_cv: boolean;
    templates: string[];
}

export interface SubscriptionPlan {
    id: string;
    name: string;
    description: string;
    price: number;
    currency: string;
    interval: string;
    features: SubscriptionFeatures;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface UserSubscription {
    plan: SubscriptionPlan | null;
    expiresAt: string | null;
    isActive: boolean;
}

// Create stores
export const subscriptionPlans = writable<SubscriptionPlan[]>([]);
export const currentSubscription = writable<UserSubscription>({
    plan: null,
    expiresAt: null,
    isActive: false
});
export const subscriptionLoading = writable(false);
export const subscriptionError = writable<string | null>(null);

// Default free plan fallback if we can't load plans
const DEFAULT_FREE_PLAN: SubscriptionPlan = {
    id: 'free',
    name: 'Free',
    description: 'Basic CV features',
    price: 0,
    currency: 'GBP',
    interval: 'month',
    features: {
        max_sections: 3,
        pdf_export: false,
        online_cv: true,
        templates: ['basic']
    },
    is_active: true
};

// Early access plan
const EARLY_ACCESS_PLAN: SubscriptionPlan = {
    id: 'early_access',
    name: 'Early Access',
    description: 'Lifetime access to all premium features',
    price: 0, // Already paid £2
    currency: 'GBP',
    interval: 'lifetime',
    features: {
        max_sections: -1, // Unlimited
        pdf_export: true,
        online_cv: true,
        templates: ['basic', 'professional', 'modern', 'creative', 'executive', 'simple', 'classic', 'elegant', 'minimalist', 'bold', 'academic', 'technical']
    },
    is_active: true
};

// Derived store to check if user can access a specific feature
export const canAccessFeature = derived(
    currentSubscription,
    ($currentSubscription) => (featureName: string, value?: any) => {
        // If no active subscription, use the default free plan
        if (!$currentSubscription.isActive || !$currentSubscription.plan) {
            return checkFeatureAccess(DEFAULT_FREE_PLAN.features, featureName, value);
        }

        return checkFeatureAccess($currentSubscription.plan.features, featureName, value);
    }
);

// Helper to check feature access
function checkFeatureAccess(features: SubscriptionFeatures, featureName: string, value?: any): boolean {
    if (!features) return false;

    switch (featureName) {
        case 'max_sections':
            // If max_sections is -1, it means unlimited
            if (features.max_sections === -1) return true;
            // Otherwise check if we're within the limit
            return value <= features.max_sections;

        case 'pdf_export':
            return !!features.pdf_export;

        case 'online_cv':
            return !!features.online_cv;

        case 'templates':
            // Check if the requested template is in the allowed templates list
            return features.templates?.includes(value);

        default:
            return false;
    }
}

// Parse JSON features to strongly typed SubscriptionFeatures
function parseFeatures(jsonFeatures: Json): SubscriptionFeatures {
    const defaultFeatures: SubscriptionFeatures = {
        max_sections: 3,
        pdf_export: false,
        online_cv: true,
        templates: ['basic']
    };

    if (!jsonFeatures || typeof jsonFeatures !== 'object') {
        return defaultFeatures;
    }

    try {
        // Handle features as JSON object
        const features = jsonFeatures as Record<string, any>;

        return {
            max_sections: typeof features.max_sections === 'number' ? features.max_sections : defaultFeatures.max_sections,
            pdf_export: !!features.pdf_export,
            online_cv: !!features.online_cv,
            templates: Array.isArray(features.templates) ? features.templates : defaultFeatures.templates
        };
    } catch (err) {
        safeLog('error', 'Error parsing subscription features', err);
        return defaultFeatures;
    }
}

// Load all available subscription plans
export async function loadSubscriptionPlans() {
    if (!browser) return;

    try {
        subscriptionLoading.set(true);
        subscriptionError.set(null);

        const { data, error } = await supabase
            .from('subscription_plans')
            .select('*')
            .eq('is_active', true)
            .order('price', { ascending: true });

        if (error) {
            safeLog('error', 'Error loading subscription plans', { error: error.message });
            subscriptionError.set(error.message);
            return;
        }

        // Convert database records to SubscriptionPlan type with proper feature parsing
        const plans = data.map(plan => ({
            ...plan,
            features: parseFeatures(plan.features)
        })) as SubscriptionPlan[];

        subscriptionPlans.set(plans);

        // Ensure we have at least the free plan
        if (plans.length === 0 || !plans.some(plan => plan.price === 0)) {
            subscriptionPlans.update(plans => [...plans, DEFAULT_FREE_PLAN]);
        }

    } catch (err) {
        safeLog('error', 'Error in loadSubscriptionPlans', err);
        subscriptionError.set('Failed to load subscription plans');
    } finally {
        subscriptionLoading.set(false);
    }
}

// Load the current user's subscription
export async function loadUserSubscription() {
    if (!browser) return;

    const currentSession = get(session);
    if (!currentSession?.user?.id) {
        // Reset subscription if no user is logged in
        currentSubscription.set({
            plan: null,
            expiresAt: null,
            isActive: false
        });
        return;
    }

    try {
        subscriptionLoading.set(true);
        subscriptionError.set(null);

        // Get user profile with subscription info
        // Temporarily disable subscription columns until migration is run
        const { data: profile, error: profileError } = await supabase
            .from('profiles')
            .select('id')
            .eq('id', currentSession.user.id)
            .single();

        if (profileError) {
            safeLog('error', 'Error loading user subscription profile', { error: profileError.message });
            subscriptionError.set(profileError.message);
            return;
        }

        // Temporarily set to default free plan until migration is run
        // TODO: Re-enable subscription logic after running database migration
        const allPlans = get(subscriptionPlans);
        const freePlan = allPlans.find(plan => plan.price === 0) || DEFAULT_FREE_PLAN;

        currentSubscription.set({
            plan: freePlan,
            expiresAt: null,
            isActive: true // Free plan is always active
        });
        return;

        // Check if user has early access
        if (profile.subscription_plan_id === 'early_access') {
            currentSubscription.set({
                plan: EARLY_ACCESS_PLAN,
                expiresAt: null,
                isActive: true // Early access is always active
            });
            return;
        }

        // Get the subscription plan details
        const { data: plan, error: planError } = await supabase
            .from('subscription_plans')
            .select('*')
            .eq('id', profile.subscription_plan_id)
            .single();

        if (planError) {
            safeLog('error', 'Error loading subscription plan', { error: planError.message });
            subscriptionError.set(planError.message);
            return;
        }

        // Convert to proper SubscriptionPlan with typed features
        const typedPlan: SubscriptionPlan = {
            ...plan,
            features: parseFeatures(plan.features)
        };

        // Determine if subscription is active
        const now = new Date();
        const expiresAt = profile.subscription_expires_at ? new Date(profile.subscription_expires_at) : null;
        const isActive = typedPlan.price === 0 || (expiresAt !== null && expiresAt > now);

        currentSubscription.set({
            plan: typedPlan,
            expiresAt: profile.subscription_expires_at,
            isActive
        });

    } catch (err) {
        safeLog('error', 'Error in loadUserSubscription', err);
        subscriptionError.set('Failed to load user subscription');
    } finally {
        subscriptionLoading.set(false);
    }
}

// Update user subscription (for use in upgrade flow)
export async function updateUserSubscription(planId: string, expiryDate?: Date) {
    if (!browser) return false;

    const currentSession = get(session);
    if (!currentSession?.user?.id) {
        subscriptionError.set('No user is logged in');
        return false;
    }

    try {
        subscriptionLoading.set(true);
        subscriptionError.set(null);

        // Update the user's profile with the new subscription
        const { error } = await supabase
            .from('profiles')
            .update({
                subscription_plan_id: planId,
                subscription_expires_at: expiryDate ? expiryDate.toISOString() : null
            })
            .eq('id', currentSession.user.id);

        if (error) {
            safeLog('error', 'Error updating user subscription', { error: error.message });
            subscriptionError.set(error.message);
            return false;
        }

        // Reload the user's subscription to refresh the state
        await loadUserSubscription();
        return true;

    } catch (err) {
        safeLog('error', 'Error in updateUserSubscription', err);
        subscriptionError.set('Failed to update subscription');
        return false;
    } finally {
        subscriptionLoading.set(false);
    }
}

// Initialize subscription data when the module is imported
export function initializeSubscription() {
    if (!browser) return;

    // Load plans first
    loadSubscriptionPlans();

    // Then watch for session changes to load user subscription
    session.subscribe(currentSession => {
        if (currentSession?.user?.id) {
            loadUserSubscription();
        } else {
            // Reset subscription if no user is logged in
            currentSubscription.set({
                plan: null,
                expiresAt: null,
                isActive: false
            });
        }
    });
}