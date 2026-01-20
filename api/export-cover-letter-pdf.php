<?php
/**
 * Export Cover Letter as PDF
 * Returns cover letter data formatted for PDF generation
 */

define('SKIP_CANONICAL_REDIRECT', true);
require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/cover-letters.php';
require_once __DIR__ . '/../php/job-applications.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$coverLetterId = $_GET['cover_letter_id'] ?? null;

if (!$coverLetterId) {
    echo json_encode(['success' => false, 'error' => 'Cover letter ID required']);
    exit;
}

// Get cover letter
$coverLetter = getCoverLetter($coverLetterId, $user['id']);
if (!$coverLetter) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Cover letter not found']);
    exit;
}

// Get job application details
$jobApplication = getJobApplication($coverLetter['job_application_id'], $user['id']);
if (!$jobApplication) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Job application not found']);
    exit;
}

// Get user profile for name/contact info
$profile = db()->fetchOne("SELECT full_name, email, phone, location FROM profiles WHERE id = ?", [$user['id']]);

// Format date
$date = date('F j, Y');

// Return data for client-side PDF generation
echo json_encode([
    'success' => true,
    'cover_letter' => [
        'text' => $coverLetter['cover_letter_text'],
        'company_name' => $jobApplication['company_name'],
        'job_title' => $jobApplication['job_title'],
        'date' => $date,
        'applicant_name' => $profile['full_name'] ?? 'Applicant',
        'applicant_email' => $profile['email'] ?? '',
        'applicant_phone' => $profile['phone'] ?? '',
        'applicant_location' => $profile['location'] ?? ''
    ]
]);

