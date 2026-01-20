<?php
/**
 * Delete Cover Letter API Endpoint
 */

define('SKIP_CANONICAL_REDIRECT', true);
require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/cover-letters.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$user = getCurrentUser();
$coverLetterId = $_POST['cover_letter_id'] ?? null;

if (!$coverLetterId) {
    echo json_encode(['success' => false, 'error' => 'Cover letter ID required']);
    exit;
}

$result = deleteCoverLetter($coverLetterId, $user['id']);

if ($result['success']) {
    echo json_encode(['success' => true, 'message' => 'Cover letter deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed to delete cover letter']);
}

