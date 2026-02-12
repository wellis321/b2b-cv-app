<?php
/**
 * API endpoint to get fresh CV data as JSON
 * Used by content-editor to refresh the live preview after editing sections
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../php/helpers.php';
require_once __DIR__ . '/../../php/cv-data.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$userId = getUserId();
$variantId = isset($_GET['variant_id']) ? trim($_GET['variant_id']) : null;

// Load CV data - from variant if specified, else master
$cvData = null;
if ($variantId) {
    require_once __DIR__ . '/../../php/cv-variants.php';
    $cvVariant = getCvVariant($variantId, $userId);
    if ($cvVariant) {
        $cvData = loadCvVariantData($variantId);
        if ($cvData && isset($cvData['variant'])) {
            $profileId = $cvData['variant']['user_id'] ?? $userId;
            $cvData['profile'] = db()->fetchOne("SELECT * FROM profiles WHERE id = ?", [$profileId]);
        } else {
            $cvData = null;
        }
    }
}
if (!$cvData) {
    $cvData = loadCvData($userId);
}

$profile = $cvData['profile'] ?? null;
if (!$profile) {
    echo json_encode(['error' => 'Profile not found']);
    exit;
}

// Ensure visibility flags
$profile['show_photo'] = $profile['show_photo'] ?? 1;
$profile['show_photo_pdf'] = $profile['show_photo_pdf'] ?? 1;
$profile['show_qr_code'] = $profile['show_qr_code'] ?? ($profile['show_photo'] ? 0 : 1);

// Decode entities for JSON
function decodeEntitiesRecursive($data) {
    if (is_array($data)) {
        return array_map('decodeEntitiesRecursive', $data);
    }
    if (is_string($data)) {
        return html_entity_decode($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

$subscriptionContext = getUserSubscriptionContext($userId);
$subscriptionFrontendContext = buildSubscriptionFrontendContext($subscriptionContext);

echo json_encode([
    'cvData' => decodeEntitiesRecursive($cvData),
    'profile' => decodeEntitiesRecursive($profile),
    'subscriptionContext' => $subscriptionFrontendContext,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
