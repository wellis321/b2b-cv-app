<?php
require_once __DIR__ . '/php/helpers.php';

requireAuth();

$userId = getUserId();
$subscriptionContext = getUserSubscriptionContext($userId);
$allPlans = getSubscriptionPlansConfig();
$marketingPlanIds = getMarketingPlanIds();
$plans = array_intersect_key($allPlans, array_flip($marketingPlanIds));
$error = getFlash('error');
$success = getFlash('success');
$pageCsrfToken = csrfToken();

// Check if a plan is specified in URL to auto-trigger checkout
$autoCheckoutPlan = get('plan', '');

$checkoutStatus = get('checkout');
if ($checkoutStatus === 'success' && empty($success)) {
    $success = 'Thanks! Your payment was successful. It can take a few seconds for your plan to update - please refresh if it does not update automatically.';
} elseif ($checkoutStatus === 'cancelled' && empty($error)) {
    $error = 'Checkout was cancelled before completing payment.';
}

$portalStatus = get('portal');
if ($portalStatus === 'return' && empty($success)) {
    $success = 'You have closed the billing portal. Any changes you made there will sync with your account automatically.';
}

$pricingDetails = [
    'free' => [
        'price' => '£0',
        'subtext' => 'Forever free',
    ],
    'pro_trial_7day' => [
        'price' => '£1.95',
        'subtext' => 'After 7 days, renews to £22/month. Cancel anytime.',
    ],
    'pro_3month' => [
        'price' => '£27.88',
        'subtext' => 'One-time payment today — save 66%',
    ],
];

function renderPlanFeatures(string $planId, array $planConfig): array {
    $features = [];

    if (!empty($planConfig['limits'])) {
        $limitStrings = [];
        foreach ($planConfig['limits'] as $section => $limit) {
            if ($limit === null) {
                continue;
            }
            $label = getSectionLabel($section);
            $limitStrings[] = sprintf('%d %s%s', $limit, $label, $limit > 1 ? 's' : '');
        }
        if (!empty($limitStrings)) {
            $features[] = 'Includes ' . implode(', ', $limitStrings);
        }
    } else {
        $features[] = 'Unlimited sections and entries';
    }

    if (!empty($planConfig['word_limits'])) {
        $features[] = 'Concise summaries enforced';
    } else {
        $features[] = 'Long-form summaries and project descriptions';
    }

    $templates = implode(', ', array_map('ucfirst', $planConfig['allowed_templates']));
    $features[] = sprintf('Templates: %s', $templates);

    if (!empty($planConfig['pdf_enabled'])) {
        $features[] = 'Download print-ready PDFs';
    } else {
        $features[] = 'PDF export available after upgrading';
    }

    if (!empty($planConfig['support_level'])) {
        $supportLabel = $planConfig['support_level'] === 'priority' ? 'Priority email support' : 'Community support';
        $features[] = $supportLabel;
    }

    return $features;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Subscription Plans | Simple CV Builder',
        'metaDescription' => 'Compare Simple CV Builder plans. Unlock unlimited sections, premium templates, and priority support with Pro Monthly or Pro Annual options.',
        'canonicalUrl' => APP_URL . '/subscription.php',
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-10 text-center">
            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700">Subscriptions</span>
            <h1 class="mt-4 text-3xl font-bold text-gray-900 sm:text-4xl">Choose the plan that fits your career goals</h1>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
                You're logged in! Upgrade to unlock unlimited sections, premium templates, and career-building resources.
            </p>
            <p class="mt-2 text-sm text-gray-500 max-w-2xl mx-auto">
                Find the plan that fits your job search best. New to Simple CV Builder? <a href="/" class="text-blue-600 hover:text-blue-800 underline">Create your free account first</a>.
            </p>
            <div class="mt-6 inline-flex flex-col items-center rounded-lg bg-white px-6 py-4 shadow">
                <div class="flex items-center">
                    <span class="text-sm text-gray-500 mr-2">Current plan:</span>
                    <span class="text-sm font-semibold text-blue-600"><?php echo e(subscriptionPlanLabel($subscriptionContext)); ?></span>
                </div>
                <?php
                $expiryInfo = formatSubscriptionExpiry($subscriptionContext);
                if ($expiryInfo['days_remaining'] !== null || $subscriptionContext['plan'] === 'lifetime' || $subscriptionContext['plan'] === 'free'):
                ?>
                    <div class="mt-2 flex items-center text-xs">
                        <?php if ($expiryInfo['status_color'] === 'green'): ?>
                            <span class="text-green-600 font-medium">✓ <?php echo e($expiryInfo['status_text']); ?></span>
                        <?php elseif ($expiryInfo['status_color'] === 'yellow'): ?>
                            <span class="text-yellow-600 font-medium">⚠ <?php echo e($expiryInfo['status_text']); ?></span>
                        <?php elseif ($expiryInfo['status_color'] === 'red'): ?>
                            <span class="text-red-600 font-medium">⚠ <?php echo e($expiryInfo['status_text']); ?></span>
                        <?php else: ?>
                            <span class="text-gray-600"><?php echo e($expiryInfo['status_text']); ?></span>
                        <?php endif; ?>
                        <?php if ($expiryInfo['formatted_date'] !== 'Never expires' && $expiryInfo['formatted_date'] !== 'N/A' && $expiryInfo['formatted_date'] !== 'Not set'): ?>
                            <span class="ml-2 text-gray-500">(expires <?php echo e($expiryInfo['formatted_date']); ?>)</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4">
                <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
            </div>
        <?php endif; ?>
        <div data-subscription-message class="hidden mb-6 rounded-md p-4 text-sm font-medium"></div>

        <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-3 max-w-4xl mx-auto">
            <?php foreach ($plans as $planId => $planConfig): ?>
                <?php
                $isCurrentPlan = subscriptionPlanId($subscriptionContext) === $planId;
                $pricing = $pricingDetails[$planId] ?? null;
                $features = renderPlanFeatures($planId, $planConfig);
                $stripePriceId = getStripePriceIdForPlan($planId);
                $planSupportsCheckout = $planId !== 'free' && $stripePriceId && stripeIsConfigured();
                ?>
                <div class="rounded-xl border <?php echo $isCurrentPlan ? 'border-blue-500 shadow-lg ring-1 ring-blue-200' : 'border-gray-200 shadow-sm'; ?> bg-white p-6 flex flex-col">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900"><?php echo e($planConfig['label']); ?></h2>
                        <?php if ($planId === 'pro_3month'): ?>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Best value</span>
                        <?php elseif ($planId === 'pro_trial_7day'): ?>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Most popular</span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-3 text-sm text-gray-600 flex-1"><?php echo e($planConfig['description']); ?></p>

                    <?php if ($pricing): ?>
                        <div class="mt-6">
                            <span class="text-3xl font-bold text-gray-900"><?php echo e($pricing['price']); ?></span>
                            <span class="ml-2 text-sm text-gray-500"><?php echo e($pricing['subtext']); ?></span>
                        </div>
                    <?php endif; ?>

                    <ul class="mt-6 space-y-3 text-sm text-gray-600">
                        <?php foreach ($features as $feature): ?>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $feature; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="mt-8">
                        <?php if ($isCurrentPlan): ?>
                            <button
                                disabled
                                class="w-full rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500 cursor-default">
                                Current plan
                            </button>
                        <?php elseif ($planId === 'free'): ?>
                            <button
                                disabled
                                class="w-full rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500 cursor-default">
                                Included
                            </button>
                        <?php elseif (!$planSupportsCheckout): ?>
                            <button
                                disabled
                                class="w-full rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500 cursor-not-allowed">
                                Currently unavailable
                            </button>
                            <p class="mt-2 text-xs text-gray-500 text-center">Contact support to upgrade while we finish configuring payments.</p>
                        <?php else: ?>
                            <button
                                type="button"
                                data-plan-button="1"
                                data-plan="<?php echo e($planId); ?>"
                                data-plan-label="<?php echo e($planConfig['label']); ?>"
                                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-200 disabled:text-gray-500"
                            >
                                Get started
                            </button>
                            <p class="mt-2 text-xs text-gray-500 text-center">Secure payments via Stripe. Cancel anytime.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        // On 7-day trial? Offer subscribe to continue before it ends
        $onTrial = subscriptionPlanId($subscriptionContext) === 'pro_trial_7day';
        $trialStripeConfigured = stripeIsConfigured() && getStripePriceIdForPlan('pro_monthly');
        if ($onTrial && $trialStripeConfigured):
        ?>
            <div class="mt-6 max-w-md mx-auto rounded-lg border border-blue-200 bg-blue-50 p-4 text-center">
                <p class="text-sm font-medium text-blue-900">On trial? Subscribe to continue after 7 days</p>
                <p class="text-xs text-blue-700 mt-1">£22/month, cancel anytime</p>
                <button type="button" data-plan-button="1" data-plan="pro_monthly" data-plan-label="Pro Monthly"
                    class="mt-3 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Subscribe to continue
                </button>
            </div>
        <?php endif; ?>

        <?php
        $hasStripeSubscription = !empty($subscriptionContext['stripe_subscription_id']);
        $showManageBilling = subscriptionIsPaid($subscriptionContext) && $hasStripeSubscription && stripeIsConfigured();
        ?>
        <?php if ($showManageBilling): ?>
            <div class="mt-10 text-center">
                <button
                    type="button"
                    data-manage-billing="1"
                    class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm ring-1 ring-inset ring-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Manage subscription &amp; billing
                </button>
                <p class="mt-2 text-xs text-gray-500">This opens Stripe&rsquo;s secure billing portal in a new tab.</p>
            </div>
        <?php endif; ?>

        <div class="mt-12 grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Why upgrade?</h3>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Unlimited sections and longer descriptions to showcase your experience in full.
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Premium templates (including Professional Blue) and print-ready PDF downloads.
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Access to curated job-hunting guides, side-income ideas, and monetisation resources updated monthly.
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Priority support to help you polish your CV and stay on top of opportunities.
                    </li>
                </ul>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Need help deciding?</h3>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <p>Stay on the Free plan to build a simple, public CV with a QR code to share.</p>
                    <p>Try 7 days for £1.95, or go straight to 3 months for the best value. Cancel anytime.</p>
                    <p class="font-medium text-gray-900">Have questions? <a href="mailto:noreply@simple-job-tracker.com" class="text-blue-600 hover:text-blue-800 underline">Email support</a> and we'll be happy to help.</p>
                </div>
            </div>
        </div>
    </div>

    <?php partial('footer'); ?>

    <script>
        (() => {
            const csrfToken = '<?php echo $pageCsrfToken; ?>';
            const messageBox = document.querySelector('[data-subscription-message]');
            const autoCheckoutPlan = <?php echo json_encode($autoCheckoutPlan); ?>;

            const showMessage = (text, variant = 'error') => {
                if (!text) {
                    return;
                }
                if (!messageBox) {
                    alert(text);
                    return;
                }
                const baseClasses = 'mb-6 rounded-md p-4 text-sm font-medium ';
                const variantClasses = variant === 'success'
                    ? 'bg-green-50 text-green-800'
                    : 'bg-red-50 text-red-800';
                messageBox.textContent = text;
                messageBox.className = baseClasses + variantClasses;
                messageBox.classList.remove('hidden');
            };

            const fetchJson = async (url, payload = {}) => {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(Object.assign({}, payload, { csrf_token: csrfToken })),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const message = data.error || 'Something went wrong. Please try again.';
                    throw new Error(message);
                }
                return data;
            };

            const triggerCheckout = async (plan) => {
                if (!plan) {
                    return;
                }

                // Find the button for this plan
                const button = document.querySelector(`[data-plan-button][data-plan="${plan}"]`);
                if (!button) {
                    showMessage('Plan not found. Please select a plan from the options below.');
                    return;
                }

                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = 'Redirecting...';

                try {
                    const data = await fetchJson('/api/stripe/create-checkout-session.php', { plan });
                    if (!data.url) {
                        throw new Error('Checkout link was not returned. Please try again.');
                    }
                    window.location.href = data.url;
                } catch (error) {
                    showMessage(error.message);
                    button.disabled = false;
                    button.textContent = originalText;
                }
            };

            // Auto-trigger checkout if plan is specified in URL
            if (autoCheckoutPlan) {
                // Small delay to ensure page is fully loaded
                setTimeout(() => {
                    triggerCheckout(autoCheckoutPlan);
                }, 100);
            }

            document.querySelectorAll('[data-plan-button]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const plan = button.dataset.plan;
                    await triggerCheckout(plan);
                });
            });

            const manageButton = document.querySelector('[data-manage-billing]');
            if (manageButton) {
                manageButton.addEventListener('click', async () => {
                    const originalText = manageButton.textContent;
                    manageButton.disabled = true;
                    manageButton.textContent = 'Opening portal...';

                    try {
                        const data = await fetchJson('/api/stripe/create-portal-session.php');
                        if (!data.url) {
                            throw new Error('Billing portal link was not returned. Please try again.');
                        }
                        window.open(data.url, '_blank', 'noopener');
                        showMessage('The billing portal opened in a new tab.', 'success');
                    } catch (error) {
                        showMessage(error.message);
                    } finally {
                        manageButton.disabled = false;
                        manageButton.textContent = originalText;
                    }
                });
            }
        })();
    </script>
</body>
</html>
