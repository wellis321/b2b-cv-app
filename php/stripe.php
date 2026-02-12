<?php
/**
 * Minimal Stripe API helper utilities.
 *
 * This project avoids adding Composer dependencies, so we talk to Stripe's REST API
 * directly via cURL. These helpers cover the pieces we need for Checkout, Billing
 * Portal, and webhook processing.
 */

const STRIPE_API_BASE = 'https://api.stripe.com/v1';

/**
 * Ensure Stripe credentials are present.
 */
function stripeIsConfigured(): bool {
    return !empty(STRIPE_SECRET_KEY);
}

/**
 * Perform a Stripe API request.
 *
 * @param string $method HTTP method (GET, POST)
 * @param string $path   API path (e.g. /customers)
 * @param array  $params Request parameters
 * @param array  $opts   Extra options ['idempotency_key' => '...']
 *
 * @return array Decoded JSON response
 *
 * @throws RuntimeException on missing configuration, curl error, or non-2xx response
 */
function stripeRequest(string $method, string $path, array $params = [], array $opts = []): array {
    if (!stripeIsConfigured()) {
        throw new RuntimeException('Stripe is not configured. Check STRIPE_SECRET_KEY in your .env file.');
    }

    $url = STRIPE_API_BASE . $path;
    $ch = curl_init();
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Stripe-Version: 2024-06-20',
        'User-Agent: SimpleCVBuilderStripeClient/1.0'
    ];

    if (!empty($opts['idempotency_key'])) {
        $headers[] = 'Idempotency-Key: ' . $opts['idempotency_key'];
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);

    $method = strtoupper($method);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
    } elseif ($method === 'GET') {
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        }
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
    }

    $body = curl_exec($ch);
    $curlErr = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        throw new RuntimeException('Stripe request failed: ' . $curlErr);
    }

    $decoded = json_decode((string)$body, true);
    if ($status >= 400 || $decoded === null) {
        $message = $decoded['error']['message'] ?? 'Unknown error';
        throw new RuntimeException('Stripe API error (' . $status . '): ' . $message);
    }

    return $decoded;
}

/**
 * Ensure the given user has a Stripe customer ID, creating one if needed.
 */
function stripeEnsureCustomer(string $userId): string {
    $profile = db()->fetchOne(
        "SELECT email, full_name, stripe_customer_id FROM profiles WHERE id = ?",
        [$userId]
    );

    if (!$profile) {
        throw new RuntimeException('Profile not found for Stripe customer creation.');
    }

    if (!empty($profile['stripe_customer_id'])) {
        return $profile['stripe_customer_id'];
    }

    $params = [
        'email' => $profile['email'],
        'metadata[user_id]' => $userId,
    ];

    if (!empty($profile['full_name'])) {
        $params['name'] = $profile['full_name'];
    }

    $customer = stripeRequest('POST', '/customers', $params, [
        'idempotency_key' => 'customer_' . $userId,
    ]);

    $customerId = $customer['id'];

    db()->update(
        'profiles',
        [
            'stripe_customer_id' => $customerId,
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        'id = ?',
        [$userId]
    );

    return $customerId;
}

/**
 * Create a Stripe Checkout Session for the requested plan.
 */
function stripeCreateCheckoutSession(string $userId, string $planId): array {
    $priceId = getStripePriceIdForPlan($planId);
    if (!$priceId) {
        throw new RuntimeException('Plan not available for checkout: ' . $planId);
    }

    $customerId = stripeEnsureCustomer($userId);

    $successUrl = APP_URL . '/subscription.php?checkout=success&session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = APP_URL . '/subscription.php?checkout=cancelled';

    // One-time payment plans: lifetime, 7-day trial, 3-month access
    $oneTimePlans = ['lifetime', 'pro_trial_7day', 'pro_3month'];
    $isOneTime = in_array($planId, $oneTimePlans, true);
    $mode = $isOneTime ? 'payment' : 'subscription';

    $params = [
        'mode' => $mode,
        'customer' => $customerId,
        'client_reference_id' => $userId,
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'allow_promotion_codes' => 'true',
        'line_items[0][price]' => $priceId,
        'line_items[0][quantity]' => 1,
        'metadata[user_id]' => $userId,
        'metadata[plan_id]' => $planId,
    ];

    if (!$isOneTime) {
        $params['subscription_data[metadata][user_id]'] = $userId;
        $params['subscription_data[metadata][plan_id]'] = $planId;
    }

    $session = stripeRequest(
        'POST',
        '/checkout/sessions',
        $params,
        ['idempotency_key' => 'checkout_' . $userId . '_' . $planId . '_' . time()]
    );

    return $session;
}

/**
 * Create a Stripe Billing Portal session so the customer can manage/cancel.
 */
function stripeCreateBillingPortalSession(string $userId): array {
    $customerId = stripeEnsureCustomer($userId);
    $returnUrl = APP_URL . '/subscription.php?portal=return';

    return stripeRequest('POST', '/billing_portal/sessions', [
        'customer' => $customerId,
        'return_url' => $returnUrl,
    ], [
        'idempotency_key' => 'portal_' . $userId . '_' . time(),
    ]);
}

/**
 * Update profile subscription fields based on Stripe data.
 */
function stripeUpdateProfileSubscription(array $payload): void {
    $customerId = $payload['customer'] ?? null;
    $subscriptionId = $payload['id'] ?? null;
    $status = $payload['status'] ?? 'inactive';
    $currentPeriodEnd = $payload['current_period_end'] ?? null;
    $cancelAt = $payload['cancel_at'] ?? null;

    if (!$customerId) {
        return;
    }

    $profile = db()->fetchOne(
        "SELECT id FROM profiles WHERE stripe_customer_id = ?",
        [$customerId]
    );

    if (!$profile) {
        return;
    }

    $planId = 'free';
    if (!empty($payload['items']['data'][0]['price']['id'])) {
        $priceId = $payload['items']['data'][0]['price']['id'];
        $planId = getPlanIdForStripePrice($priceId) ?? 'free';
    }

    $data = [
        'plan' => $planId,
        'subscription_status' => $status,
        'stripe_subscription_id' => $subscriptionId,
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    if ($currentPeriodEnd) {
        $data['subscription_current_period_end'] = date('Y-m-d H:i:s', (int)$currentPeriodEnd);
    } else {
        $data['subscription_current_period_end'] = null;
    }

    if ($cancelAt) {
        $data['subscription_cancel_at'] = date('Y-m-d H:i:s', (int)$cancelAt);
    } else {
        $data['subscription_cancel_at'] = null;
    }

    if (in_array($status, ['canceled', 'incomplete', 'incomplete_expired'], true)) {
        $data['plan'] = 'free';
        $data['stripe_subscription_id'] = null;
    }

    db()->update('profiles', $data, 'id = ?', [$profile['id']]);
}

/**
 * Verify the Stripe webhook signature.
 */
function stripeVerifyWebhookSignature(string $payload, string $signatureHeader): bool {
    if (empty(STRIPE_WEBHOOK_SECRET)) {
        return false;
    }

    if (empty($signatureHeader)) {
        return false;
    }

    $timestamp = null;
    $signatures = [];

    foreach (explode(',', $signatureHeader) as $part) {
        [$key, $value] = array_map('trim', explode('=', $part, 2));
        if ($key === 't') {
            $timestamp = (int)$value;
        } elseif ($key === 'v1') {
            $signatures[] = $value;
        }
    }

    if (!$timestamp || empty($signatures)) {
        return false;
    }

    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, STRIPE_WEBHOOK_SECRET);
    $tolerance = 300;

    foreach ($signatures as $signature) {
        if (hash_equals($expected, $signature) && abs(time() - $timestamp) <= $tolerance) {
            return true;
        }
    }

    return false;
}
