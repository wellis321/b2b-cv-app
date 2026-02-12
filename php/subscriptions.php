<?php
/**
 * Subscription and entitlement utilities
 */

/**
 * Retrieve subscription plan configuration
 */
function getSubscriptionPlansConfig(): array {
    static $plans = null;

    if ($plans === null) {
        $plans = [
            'free' => [
                'label' => 'Free',
                'description' => 'Get started with a single-page CV and essential sections.',
                'limits' => [
                    'work_experience' => 1,
                    'projects' => 1,
                    'skills' => 3,
                    'certifications' => 1,
                    'education' => 1,
                    'memberships' => 1,
                    'interests' => 2,
                    'qualification_equivalence' => 1,
                    'summary_strengths' => 3,
                    'job_applications' => 5,
                ],
                'word_limits' => [
                    'summary_description' => 200,
                    'work_description' => 120,
                    'project_description' => 120,
                ],
                'allowed_templates' => ['minimal', 'classic'],
                'default_template' => 'minimal',
                'template_customization' => false,
                'pdf_enabled' => true,
                'support_level' => 'community',
            ],
            'pro_trial_7day' => [
                'label' => '7-day unlimited access',
                'description' => 'Full access for 7 days. After 7 days, renews to £22/month. Cancel anytime.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal', 'classic', 'modern', 'structured'],
                'default_template' => 'professional',
                'template_customization' => true,
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
            'pro_monthly' => [
                'label' => 'Pro Monthly',
                'description' => 'Unlimited sections, premium templates, and PDF exports. Cancel anytime.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal', 'classic', 'modern', 'structured'],
                'default_template' => 'professional',
                'template_customization' => true,
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
            'pro_3month' => [
                'label' => '3-month unlimited access',
                'description' => 'One-time payment for 3 months. Best value.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal', 'classic', 'modern', 'structured'],
                'default_template' => 'professional',
                'template_customization' => true,
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
            'pro_annual' => [
                'label' => 'Pro Annual',
                'description' => 'Everything in Pro Monthly plus discounted pricing and priority support.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal', 'classic', 'modern', 'structured'],
                'default_template' => 'professional',
                'template_customization' => true,
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
            'lifetime' => [
                'label' => 'Lifetime',
                'description' => 'One-time payment for lifetime access. All Pro features, forever.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal', 'classic', 'modern', 'structured'],
                'default_template' => 'professional',
                'template_customization' => true,
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
        ];
    }

    return $plans;
}

/**
 * Plans shown in marketing/pricing (Resume.co-style: Free, 7-day trial, 3-month only)
 */
function getMarketingPlanIds(): array {
    return ['free', 'pro_trial_7day', 'pro_3month'];
}

/**
 * Get configuration for a single plan
 */
function getSubscriptionPlanConfig(string $planId): array {
    $plans = getSubscriptionPlansConfig();
    if (!isset($plans[$planId])) {
        return $plans[DEFAULT_PLAN] ?? reset($plans);
    }
    return $plans[$planId];
}

/**
 * Build user subscription context
 */
function getUserSubscriptionContext(string $userId): array {
    static $cache = [];

    // Clear cache if plan might have changed (for lifetime plans especially)
    if (isset($cache[$userId])) {
        $cachedPlan = $cache[$userId]['plan'] ?? null;
        // Quick check if plan changed in DB
        $currentPlan = db()->fetchOne("SELECT plan FROM profiles WHERE id = ?", [$userId])['plan'] ?? null;
        if ($cachedPlan !== $currentPlan) {
            unset($cache[$userId]);
        } else {
            return $cache[$userId];
        }
    }

    $profile = db()->fetchOne(
        "SELECT plan, subscription_status, subscription_current_period_end, stripe_customer_id, stripe_subscription_id, subscription_cancel_at
         FROM profiles WHERE id = ?",
        [$userId]
    );

    $planId = $profile['plan'] ?? DEFAULT_PLAN;
    $status = $profile['subscription_status'] ?? 'inactive';
    $periodEnd = $profile['subscription_current_period_end'] ?? null;
    $stripeSubId = $profile['stripe_subscription_id'] ?? null;

    // One-time or trial plans (no Stripe subscription): when period ends, downgrade to free.
    // Applies to: pro_trial_7day (7-day trial), pro_3month (3-month access).
    $trialOrOneTimePlans = ['pro_trial_7day', 'pro_3month'];
    if (in_array($planId, $trialOrOneTimePlans, true) && empty($stripeSubId) && $periodEnd) {
        if (strtotime($periodEnd) < time()) {
            db()->update('profiles', [
                'plan' => 'free',
                'subscription_status' => 'inactive',
                'subscription_current_period_end' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$userId]);
            $planId = 'free';
            $status = 'inactive';
            $periodEnd = null;
            $profile = array_merge($profile ?? [], ['plan' => 'free', 'subscription_status' => 'inactive', 'subscription_current_period_end' => null]);
        }
    }

    $config = getSubscriptionPlanConfig($planId);

    // For lifetime plans, always treat as active regardless of subscription_status
    if ($planId === 'lifetime') {
        $status = 'active';
    }

    $context = [
        'user_id' => $userId,
        'plan' => $planId,
        'status' => $status,
        'current_period_end' => $periodEnd,
        'cancel_at' => $profile['subscription_cancel_at'] ?? null,
        'stripe_customer_id' => $profile['stripe_customer_id'] ?? null,
        'stripe_subscription_id' => $stripeSubId,
        'config' => $config,
        'is_paid' => $planId !== 'free',
    ];
    
    // Force lifetime plans to be treated as active and paid
    if ($planId === 'lifetime') {
        $context['status'] = 'active';
        $context['is_paid'] = true;
    }

    $cache[$userId] = $context;
    return $context;
}

function subscriptionPlanId(array $context): string {
    return $context['plan'] ?? DEFAULT_PLAN;
}

function subscriptionPlanLabel(array $context): string {
    return $context['config']['label'] ?? ucfirst(subscriptionPlanId($context));
}

/**
 * Calculate days remaining until subscription expires
 * Returns null for lifetime/free plans or if no expiration date
 */
function subscriptionDaysRemaining(array $context): ?int {
    $planId = subscriptionPlanId($context);
    
    // Lifetime and free plans don't expire
    if ($planId === 'lifetime' || $planId === 'free') {
        return null;
    }
    
    $expiryDate = $context['current_period_end'] ?? null;
    if (empty($expiryDate)) {
        return null;
    }
    
    $expiryTimestamp = strtotime($expiryDate);
    $now = time();
    $daysRemaining = floor(($expiryTimestamp - $now) / 86400);
    
    return max(0, $daysRemaining);
}

/**
 * Format subscription expiration info for display
 * Returns array with 'date', 'days_remaining', 'status_color', 'status_text'
 */
function formatSubscriptionExpiry(array $context): array {
    $planId = subscriptionPlanId($context);
    $daysRemaining = subscriptionDaysRemaining($context);
    $expiryDate = $context['current_period_end'] ?? null;
    
    if ($planId === 'lifetime') {
        return [
            'date' => null,
            'days_remaining' => null,
            'status_color' => 'green',
            'status_text' => 'Never expires',
            'formatted_date' => 'Never expires'
        ];
    }
    
    if ($planId === 'free') {
        return [
            'date' => null,
            'days_remaining' => null,
            'status_color' => 'gray',
            'status_text' => 'Free plan',
            'formatted_date' => 'N/A'
        ];
    }
    
    if (empty($expiryDate)) {
        return [
            'date' => null,
            'days_remaining' => null,
            'status_color' => 'yellow',
            'status_text' => 'No expiration date set',
            'formatted_date' => 'Not set'
        ];
    }
    
    $formattedDate = date('j M Y', strtotime($expiryDate));
    $statusColor = 'green';
    $statusText = '';
    
    if ($daysRemaining === null) {
        $statusText = 'Active';
    } elseif ($daysRemaining > 30) {
        $statusText = $daysRemaining . ' days remaining';
        $statusColor = 'green';
    } elseif ($daysRemaining > 7) {
        $statusText = $daysRemaining . ' days remaining';
        $statusColor = 'yellow';
    } elseif ($daysRemaining > 0) {
        $statusText = $daysRemaining . ' day' . ($daysRemaining !== 1 ? 's' : '') . ' remaining';
        $statusColor = 'red';
    } else {
        $statusText = 'Expired';
        $statusColor = 'red';
    }
    
    return [
        'date' => $expiryDate,
        'days_remaining' => $daysRemaining,
        'status_color' => $statusColor,
        'status_text' => $statusText,
        'formatted_date' => $formattedDate
    ];
}

function subscriptionIsPaid(array $context): bool {
    return !empty($context['is_paid']);
}

function planSectionLimit(array $context, string $section): ?int {
    $limits = $context['config']['limits'] ?? [];
    return $limits[$section] ?? null;
}

function planWordLimit(array $context, string $key): ?int {
    $limits = $context['config']['word_limits'] ?? [];
    return $limits[$key] ?? null;
}

function planAllowedTemplates(array $context): array {
    return $context['config']['allowed_templates'] ?? ['minimal'];
}

function planAllowsTemplate(array $context, string $templateId): bool {
    $allowed = planAllowedTemplates($context);
    return in_array($templateId, $allowed, true);
}

function planDefaultTemplateId(array $context): string {
    return $context['config']['default_template'] ?? 'professional';
}

function planPdfEnabled(array $context): bool {
    return !empty($context['config']['pdf_enabled']);
}

function planCanAddEntry(array $context, string $section, string $userId, ?int $currentCount = null): bool {
    $limit = planSectionLimit($context, $section);
    if ($limit === null) {
        return true;
    }

    if ($currentCount === null) {
        $currentCount = getSectionCountForUser($userId, $section);
    }

    return $currentCount < $limit;
}

function getSectionCountForUser(string $userId, string $section): int {
    switch ($section) {
        case 'work_experience':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM work_experience WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'projects':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM projects WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'skills':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM skills WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'certifications':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM certifications WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'education':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM education WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'memberships':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM professional_memberships WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'interests':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM interests WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'qualification_equivalence':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM professional_qualification_equivalence WHERE profile_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        case 'summary_strengths':
            $summary = db()->fetchOne("SELECT id FROM professional_summary WHERE profile_id = ?", [$userId]);
            if (!$summary || empty($summary['id'])) {
                return 0;
            }
            $row = db()->fetchOne(
                "SELECT COUNT(*) AS count FROM professional_summary_strengths WHERE professional_summary_id = ?",
                [$summary['id']]
            );
            return (int)($row['count'] ?? 0);
        case 'job_applications':
            $row = db()->fetchOne("SELECT COUNT(*) AS count FROM job_applications WHERE user_id = ?", [$userId]);
            return (int)($row['count'] ?? 0);
        default:
            return 0;
    }
}

function subscriptionCountWords(?string $text): int {
    if ($text === null) {
        return 0;
    }
    $text = trim(strip_tags($text));
    if ($text === '') {
        return 0;
    }
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    return is_array($words) ? count($words) : 0;
}

function planWordLimitExceeded(array $context, string $key, ?string $text): bool {
    $limit = planWordLimit($context, $key);
    if ($limit === null || $text === null) {
        return false;
    }
    return subscriptionCountWords($text) > $limit;
}

function getPlanLimitMessage(array $context, string $section): string {
    $limit = planSectionLimit($context, $section);
    if ($limit === null) {
        return '';
    }

    $sectionLabel = getSectionLabel($section);
    $planLabel = subscriptionPlanLabel($context);
    $upgradeUrl = getPlanUpgradeUrl();

    return sprintf(
        'The %s plan allows up to %d %s. Upgrade to unlock more. <a href="%s" class="underline">View plans</a>.',
        $planLabel,
        $limit,
        $sectionLabel,
        $upgradeUrl
    );
}

function getPlanWordLimitMessage(array $context, string $key): string {
    $limit = planWordLimit($context, $key);
    if ($limit === null) {
        return '';
    }

    $label = getWordLimitLabel($key);
    $planLabel = subscriptionPlanLabel($context);
    $upgradeUrl = getPlanUpgradeUrl();

    return sprintf(
        'The %s plan allows up to %d words for %s. Please shorten the text or <a href="%s" class="underline">upgrade your plan</a>.',
        $planLabel,
        $limit,
        $label,
        $upgradeUrl
    );
}

function getSectionLabel(string $section): string {
    $labels = [
        'work_experience' => 'work experience entry',
        'projects' => 'project',
        'skills' => 'skill',
        'certifications' => 'certification',
        'education' => 'education entry',
        'memberships' => 'professional membership',
        'interests' => 'interest',
        'qualification_equivalence' => 'qualification equivalence entry',
        'summary_strengths' => 'strength',
        'job_applications' => 'job application',
    ];

    return $labels[$section] ?? 'item';
}

function getWordLimitLabel(string $key): string {
    $labels = [
        'summary_description' => 'the professional summary',
        'work_description' => 'the work experience description',
        'project_description' => 'the project description',
    ];

    return $labels[$key] ?? 'this field';
}

function getPlanUpgradeUrl(): string {
    return '/subscription.php';
}

function buildSubscriptionFrontendContext(array $context): array {
    return [
        'plan' => subscriptionPlanId($context),
        'planLabel' => subscriptionPlanLabel($context),
        'isPaid' => subscriptionIsPaid($context),
        'allowedTemplateIds' => planAllowedTemplates($context),
        'defaultTemplateId' => planDefaultTemplateId($context),
        'pdfEnabled' => planPdfEnabled($context),
        'upgradeUrl' => getPlanUpgradeUrl(),
    ];
}

/**
 * Map internal plan IDs to Stripe price IDs.
 */
function getStripePriceIdForPlan(string $planId): ?string {
    switch ($planId) {
        case 'pro_monthly':
            return STRIPE_PRICE_PRO_MONTHLY ?: null;
        case 'pro_annual':
            return STRIPE_PRICE_PRO_ANNUAL ?: null;
        case 'pro_trial_7day':
            return defined('STRIPE_PRICE_PRO_TRIAL_7DAY') && STRIPE_PRICE_PRO_TRIAL_7DAY ? STRIPE_PRICE_PRO_TRIAL_7DAY : null;
        case 'pro_3month':
            return defined('STRIPE_PRICE_PRO_3MONTH') && STRIPE_PRICE_PRO_3MONTH ? STRIPE_PRICE_PRO_3MONTH : null;
        case 'lifetime':
            return STRIPE_PRICE_LIFETIME ?: null;
        default:
            return null;
    }
}

/**
 * Reverse map from Stripe price ID to our internal plan ID.
 */
function getPlanIdForStripePrice(string $priceId): ?string {
    if (!empty(STRIPE_PRICE_PRO_MONTHLY) && $priceId === STRIPE_PRICE_PRO_MONTHLY) {
        return 'pro_monthly';
    }
    if (!empty(STRIPE_PRICE_PRO_ANNUAL) && $priceId === STRIPE_PRICE_PRO_ANNUAL) {
        return 'pro_annual';
    }
    if (defined('STRIPE_PRICE_PRO_TRIAL_7DAY') && STRIPE_PRICE_PRO_TRIAL_7DAY && $priceId === STRIPE_PRICE_PRO_TRIAL_7DAY) {
        return 'pro_trial_7day';
    }
    if (defined('STRIPE_PRICE_PRO_3MONTH') && STRIPE_PRICE_PRO_3MONTH && $priceId === STRIPE_PRICE_PRO_3MONTH) {
        return 'pro_3month';
    }
    if (!empty(STRIPE_PRICE_LIFETIME) && $priceId === STRIPE_PRICE_LIFETIME) {
        return 'lifetime';
    }
    return null;
}

// =========================================================================
// Organisation Subscription Functions
// =========================================================================

/**
 * Get organisation subscription plans configuration
 */
function getOrganisationPlansConfig(): array {
    static $plans = null;

    if ($plans === null) {
        $plans = [
            'agency_basic' => [
                'label' => 'Basic',
                'description' => 'Perfect for small agencies getting started.',
                'price_monthly' => 49,
                'price_currency' => 'GBP',
                'max_candidates' => 10,
                'max_team_members' => 3,
                'features' => [
                    'candidate_management' => true,
                    'team_roles' => true,
                    'cv_templates' => ['minimal', 'classic'],
                    'pdf_export' => true,
                    'custom_branding' => false,
                    'bulk_export' => false,
                    'api_access' => false,
                    'priority_support' => false,
                ],
            ],
            'agency_pro' => [
                'label' => 'Professional',
                'description' => 'For growing agencies with larger teams.',
                'price_monthly' => 149,
                'price_currency' => 'GBP',
                'max_candidates' => 50,
                'max_team_members' => 10,
                'features' => [
                    'candidate_management' => true,
                    'team_roles' => true,
                    'cv_templates' => ['minimal', 'classic', 'modern', 'professional', 'structured'],
                    'pdf_export' => true,
                    'custom_branding' => true,
                    'bulk_export' => true,
                    'api_access' => false,
                    'priority_support' => true,
                ],
            ],
            'agency_enterprise' => [
                'label' => 'Enterprise',
                'description' => 'Unlimited candidates with premium support.',
                'price_monthly' => 499,
                'price_currency' => 'GBP',
                'max_candidates' => PHP_INT_MAX, // Unlimited
                'max_team_members' => PHP_INT_MAX, // Unlimited
                'features' => [
                    'candidate_management' => true,
                    'team_roles' => true,
                    'cv_templates' => ['minimal', 'classic', 'modern', 'professional', 'structured'],
                    'pdf_export' => true,
                    'custom_branding' => true,
                    'bulk_export' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'dedicated_support' => true,
                    'custom_domain' => true,
                ],
            ],
        ];
    }

    return $plans;
}

/**
 * Get organisation subscription context
 */
function getOrganisationSubscriptionContext(string $organisationId): array {
    static $cache = [];

    if (isset($cache[$organisationId])) {
        return $cache[$organisationId];
    }

    $org = db()->fetchOne(
        "SELECT plan, subscription_status, subscription_current_period_end,
                stripe_customer_id, stripe_subscription_id, subscription_cancel_at,
                max_candidates, max_team_members
         FROM organisations WHERE id = ?",
        [$organisationId]
    );

    if (!$org) {
        return [];
    }

    $planId = $org['plan'] ?? 'agency_basic';
    $plans = getOrganisationPlansConfig();
    $config = $plans[$planId] ?? $plans['agency_basic'];

    $context = [
        'organisation_id' => $organisationId,
        'plan' => $planId,
        'status' => $org['subscription_status'] ?? 'inactive',
        'current_period_end' => $org['subscription_current_period_end'] ?? null,
        'cancel_at' => $org['subscription_cancel_at'] ?? null,
        'stripe_customer_id' => $org['stripe_customer_id'] ?? null,
        'stripe_subscription_id' => $org['stripe_subscription_id'] ?? null,
        'config' => $config,
        'max_candidates' => $org['max_candidates'] ?? $config['max_candidates'],
        'max_team_members' => $org['max_team_members'] ?? $config['max_team_members'],
        'is_active' => in_array($org['subscription_status'], ['active', 'trialing']),
    ];

    $cache[$organisationId] = $context;
    return $context;
}

/**
 * Check if organisation has a feature enabled
 */
function organisationHasFeature(string $organisationId, string $feature): bool {
    $context = getOrganisationSubscriptionContext($organisationId);

    if (empty($context) || empty($context['config']['features'])) {
        return false;
    }

    $featureValue = $context['config']['features'][$feature] ?? false;
    return !empty($featureValue);
}

/**
 * Get allowed templates for organisation
 */
function getOrganisationAllowedTemplates(string $organisationId): array {
    $context = getOrganisationSubscriptionContext($organisationId);

    if (empty($context) || empty($context['config']['features']['cv_templates'])) {
        return ['minimal', 'classic'];
    }

    return $context['config']['features']['cv_templates'];
}

/**
 * Update organisation subscription from Stripe webhook
 */
function updateOrganisationSubscription(string $stripeCustomerId, array $subscriptionData): bool {
    $org = db()->fetchOne(
        "SELECT id FROM organisations WHERE stripe_customer_id = ?",
        [$stripeCustomerId]
    );

    if (!$org) {
        return false;
    }

    try {
        $planId = getOrganisationPlanIdForStripePrice($subscriptionData['price_id'] ?? '');

        $updateData = [
            'subscription_status' => $subscriptionData['status'],
            'stripe_subscription_id' => $subscriptionData['subscription_id'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($planId) {
            $plans = getOrganisationPlansConfig();
            $planConfig = $plans[$planId] ?? null;

            if ($planConfig) {
                $updateData['plan'] = $planId;
                $updateData['max_candidates'] = $planConfig['max_candidates'];
                $updateData['max_team_members'] = $planConfig['max_team_members'];
            }
        }

        if (!empty($subscriptionData['current_period_end'])) {
            $updateData['subscription_current_period_end'] = date('Y-m-d H:i:s', $subscriptionData['current_period_end']);
        }

        if (!empty($subscriptionData['cancel_at'])) {
            $updateData['subscription_cancel_at'] = date('Y-m-d H:i:s', $subscriptionData['cancel_at']);
        }

        db()->update('organisations', $updateData, 'id = ?', [$org['id']]);

        return true;
    } catch (Exception $e) {
        if (DEBUG) {
            error_log('Organisation subscription update error: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Map Stripe price ID to organisation plan ID
 * Note: These constants should be defined in config.php
 */
function getOrganisationPlanIdForStripePrice(string $priceId): ?string {
    // These constants should be defined in config.php
    if (defined('STRIPE_PRICE_AGENCY_BASIC') && $priceId === STRIPE_PRICE_AGENCY_BASIC) {
        return 'agency_basic';
    }
    if (defined('STRIPE_PRICE_AGENCY_PRO') && $priceId === STRIPE_PRICE_AGENCY_PRO) {
        return 'agency_pro';
    }
    if (defined('STRIPE_PRICE_AGENCY_ENTERPRISE') && $priceId === STRIPE_PRICE_AGENCY_ENTERPRISE) {
        return 'agency_enterprise';
    }
    return null;
}

/**
 * Get Stripe price ID for organisation plan
 */
function getStripePriceIdForOrganisationPlan(string $planId): ?string {
    switch ($planId) {
        case 'agency_basic':
            return defined('STRIPE_PRICE_AGENCY_BASIC') ? STRIPE_PRICE_AGENCY_BASIC : null;
        case 'agency_pro':
            return defined('STRIPE_PRICE_AGENCY_PRO') ? STRIPE_PRICE_AGENCY_PRO : null;
        case 'agency_enterprise':
            return defined('STRIPE_PRICE_AGENCY_ENTERPRISE') ? STRIPE_PRICE_AGENCY_ENTERPRISE : null;
        default:
            return null;
    }
}
