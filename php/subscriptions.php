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
                ],
                'word_limits' => [
                    'summary_description' => 200,
                    'work_description' => 120,
                    'project_description' => 120,
                ],
                'allowed_templates' => ['minimal'],
                'default_template' => 'minimal',
                'pdf_enabled' => false,
                'support_level' => 'community',
            ],
            'pro_monthly' => [
                'label' => 'Pro Monthly',
                'description' => 'Unlimited sections, premium templates, and PDF exports.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal'],
                'default_template' => 'professional',
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
            'pro_annual' => [
                'label' => 'Pro Annual',
                'description' => 'Everything in Pro Monthly plus discounted pricing and priority support.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal'],
                'default_template' => 'professional',
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
            'lifetime' => [
                'label' => 'Lifetime',
                'description' => 'One-time payment for lifetime access. All Pro features, forever.',
                'limits' => [],
                'word_limits' => [],
                'allowed_templates' => ['professional', 'minimal'],
                'default_template' => 'professional',
                'pdf_enabled' => true,
                'support_level' => 'priority',
            ],
        ];
    }

    return $plans;
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

    if (isset($cache[$userId])) {
        return $cache[$userId];
    }

    $profile = db()->fetchOne(
        "SELECT plan, subscription_status, subscription_current_period_end, stripe_customer_id, stripe_subscription_id, subscription_cancel_at
         FROM profiles WHERE id = ?",
        [$userId]
    );

    $planId = $profile['plan'] ?? DEFAULT_PLAN;
    $config = getSubscriptionPlanConfig($planId);
    $status = $profile['subscription_status'] ?? 'inactive';

    $context = [
        'user_id' => $userId,
        'plan' => $planId,
        'status' => $status,
        'current_period_end' => $profile['subscription_current_period_end'] ?? null,
        'cancel_at' => $profile['subscription_cancel_at'] ?? null,
        'stripe_customer_id' => $profile['stripe_customer_id'] ?? null,
        'stripe_subscription_id' => $profile['stripe_subscription_id'] ?? null,
        'config' => $config,
        'is_paid' => $planId !== 'free',
    ];

    $cache[$userId] = $context;
    return $context;
}

function subscriptionPlanId(array $context): string {
    return $context['plan'] ?? DEFAULT_PLAN;
}

function subscriptionPlanLabel(array $context): string {
    return $context['config']['label'] ?? ucfirst(subscriptionPlanId($context));
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
    if (!empty(STRIPE_PRICE_LIFETIME) && $priceId === STRIPE_PRICE_LIFETIME) {
        return 'lifetime';
    }
    return null;
}
