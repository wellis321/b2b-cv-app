<?php
/**
 * AI Cover Letter Generation API Endpoint
 * Generates personalized cover letters based on job application and CV data
 */

define('SKIP_CANONICAL_REDIRECT', true);

ob_start();

// Increase timeout for AI processing
set_time_limit(180);
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/ai-service.php';
require_once __DIR__ . '/../php/cover-letters.php';
require_once __DIR__ . '/../php/cv-data.php';
require_once __DIR__ . '/../php/job-applications.php';
require_once __DIR__ . '/../php/cv-variants.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check authentication
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $jobApplicationId = $_POST['job_application_id'] ?? null;
    $cvVariantId = $_POST['cv_variant_id'] ?? null;
    $customInstructions = $_POST['custom_instructions'] ?? null;
    
    if (!$jobApplicationId) {
        throw new Exception('Job application ID is required');
    }
    
    // Verify job application belongs to user
    $jobApplication = getJobApplication($jobApplicationId, $user['id']);
    if (!$jobApplication) {
        throw new Exception('Job application not found');
    }
    
    // Load CV data
    $cvData = null;
    if ($cvVariantId) {
        // Load from variant
        $variant = getCvVariant($cvVariantId, $user['id']);
        if (!$variant) {
            throw new Exception('CV variant not found');
        }
        require_once __DIR__ . '/../php/cv-variants.php';
        $cvData = loadCvVariantData($cvVariantId);
    } else {
        // Load master CV
        $cvData = loadCvData($user['id']);
    }
    
    if (!$cvData || empty($cvData)) {
        throw new Exception('No CV data found. Please create your CV first.');
    }
    
    // Initialize AI service
    $aiService = new AIService($user['id']);
    
    // Generate cover letter
    $result = $aiService->generateCoverLetter($cvData, $jobApplication, [
        'custom_instructions' => $customInstructions
    ]);
    
    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Failed to generate cover letter');
    }
    
    $coverLetterText = $result['cover_letter_text'];
    
    // Save or update cover letter in database
    $coverLetterResult = createCoverLetter($user['id'], $jobApplicationId, $coverLetterText);
    
    if (!$coverLetterResult['success']) {
        throw new Exception($coverLetterResult['error'] ?? 'Failed to save cover letter');
    }
    
    // Get the saved cover letter
    $coverLetter = getCoverLetterByApplication($jobApplicationId, $user['id']);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'cover_letter_id' => $coverLetter['id'],
        'cover_letter_text' => $coverLetter['cover_letter_text'],
        'message' => 'Cover letter generated successfully'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("AI Generate Cover Letter Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

