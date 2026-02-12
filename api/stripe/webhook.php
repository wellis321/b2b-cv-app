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
if (!$event || !isset($event['type']) || !isset($event['id'])) {
    http_response_code(400);
    echo 'Invalid payload';
    exit;
}

$eventId = $event['id'];
$type = $event['type'];
$object = $event['data']['object'] ?? [];

// Idempotency check - skip if event already processed
try {
    $existingEvent = db()->fetchOne(
        "SELECT id FROM stripe_webhook_events WHERE id = ?",
        [$eventId]
    );
    if ($existingEvent) {
        // Event already processed, acknowledge without reprocessing
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['received' => true, 'already_processed' => true]);
        exit;
    }
} catch (Throwable $e) {
    // Table might not exist yet - log and continue (non-fatal)
    error_log('Stripe webhook idempotency check failed (table may not exist): ' . $e->getMessage());
}

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
                // Handle one-time payment (lifetime, 7-day trial, 3-month access)
                $planId = $object['metadata']['plan_id'] ?? null;
                $userId = $object['metadata']['user_id'] ?? null;
                $customerId = $object['customer'] ?? null;

                if ($planId && $userId && $customerId) {
                    $profile = db()->fetchOne(
                        "SELECT id FROM profiles WHERE id = ? AND stripe_customer_id = ?",
                        [$userId, $customerId]
                    );

                    if ($profile) {
                        $updateData = [
                            'plan' => $planId,
                            'subscription_status' => 'active',
                            'subscription_cancel_at' => null,
                            'stripe_subscription_id' => null,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];

                        if ($planId === 'lifetime') {
                            $updateData['subscription_current_period_end'] = null;
                        } elseif ($planId === 'pro_trial_7day') {
                            $updateData['subscription_status'] = 'trialing';
                            $updateData['subscription_current_period_end'] = date('Y-m-d H:i:s', strtotime('+7 days'));
                        } elseif ($planId === 'pro_3month') {
                            $updateData['subscription_current_period_end'] = date('Y-m-d H:i:s', strtotime('+90 days'));
                        } else {
                            $updateData['subscription_current_period_end'] = null;
                        }

                        db()->update('profiles', $updateData, 'id = ?', [$profile['id']]);
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

// Record event as processed for idempotency
try {
    db()->insert('stripe_webhook_events', [
        'id' => $eventId,
        'event_type' => $type,
        'processed_at' => date('Y-m-d H:i:s')
    ]);
} catch (Throwable $e) {
    // Non-fatal - log but don't fail the webhook
    error_log('Failed to record webhook event for idempotency: ' . $e->getMessage());
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['received' => true]);
