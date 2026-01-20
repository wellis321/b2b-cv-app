<?php
/**
 * Get Cover Letter API Endpoint
 * Returns cover letter for a job application
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

$user = getCurrentUser();
$applicationId = $_GET['application_id'] ?? null;

if (!$applicationId) {
    echo json_encode(['success' => false, 'error' => 'Application ID required']);
    exit;
}

$coverLetter = getCoverLetterByApplication($applicationId, $user['id']);

if ($coverLetter) {
    echo json_encode([
        'success' => true,
        'cover_letter' => $coverLetter
    ]);
} else {
    echo json_encode([
        'success' => false,
        'cover_letter' => null
    ]);
}

