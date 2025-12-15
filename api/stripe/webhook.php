<?php
require_once __DIR__ . '/../../php/helpers.php';

$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!stripeIsConfigured() || empty(STRIPE_WEBHOOK_SECRET)) {
    http_response_code(400);
    echo 'Stripe webhook not configured';
    exit;
}

if (!stripeVerifyWebhookSignature($payload, $signatureHeader)) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$event = json_decode($payload, true);
if (!$event || !isset($event['type'])) {
    http_response_code(400);
    echo 'Invalid payload';
    exit;
}

$type = $event['type'];
$object = $event['data']['object'] ?? [];

try {
    switch ($type) {
        case 'checkout.session.completed':
            $mode = $object['mode'] ?? '';
            if ($mode === 'subscription' && !empty($object['subscription'])) {
                try {
                    $subscription = stripeRequest('GET', '/subscriptions/' . $object['subscription']);
                    stripeUpdateProfileSubscription($subscription);
                } catch (Throwable $inner) {
                    error_log('Stripe webhook fetch subscription failed: ' . $inner->getMessage());
                }
            } elseif ($mode === 'payment') {
                // Handle one-time payment (lifetime subscription)
                $planId = $object['metadata']['plan_id'] ?? null;
                $userId = $object['metadata']['user_id'] ?? null;
                $customerId = $object['customer'] ?? null;

                if ($planId === 'lifetime' && $userId && $customerId) {
                    // Update user's plan to lifetime
                    $profile = db()->fetchOne(
                        "SELECT id FROM profiles WHERE id = ? AND stripe_customer_id = ?",
                        [$userId, $customerId]
                    );

                    if ($profile) {
                        db()->update(
                            'profiles',
                            [
                                'plan' => 'lifetime',
                                'subscription_status' => 'active',
                                'subscription_current_period_end' => null, // Lifetime has no end date
                                'subscription_cancel_at' => null,
                                'stripe_subscription_id' => null, // No subscription for lifetime
                                'updated_at' => date('Y-m-d H:i:s'),
                            ],
                            'id = ?',
                            [$profile['id']]
                        );
                    }
                }
            }
            break;

        case 'customer.subscription.created':
        case 'customer.subscription.updated':
        case 'customer.subscription.deleted':
            stripeUpdateProfileSubscription($object);
            break;

        case 'invoice.payment_failed':
            if (!empty($object['subscription'])) {
                try {
                    $subscription = stripeRequest('GET', '/subscriptions/' . $object['subscription']);
                    stripeUpdateProfileSubscription($subscription);
                } catch (Throwable $inner) {
                    error_log('Stripe webhook payment_failed fetch failed: ' . $inner->getMessage());
                }
            }
            break;

        default:
            // Unsupported events are ignored but acknowledged
            break;
    }
} catch (Throwable $e) {
    error_log('Stripe webhook handler error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Webhook error';
    exit;
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['received' => true]);
