<?php
/**
 * AI CV Quality Assessment API Endpoint
 * Analyzes CV quality and provides scores and recommendations
 */

// Prevent canonical redirect
define('SKIP_CANONICAL_REDIRECT', true);

// Start output buffering to prevent any output before JSON
ob_start();

// Increase timeout for AI processing (Ollama can take 30-60 seconds)
set_time_limit(180); // 3 minutes
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/ai-service.php';

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
    $cvVariantId = $_POST['cv_variant_id'] ?? null;
    $jobApplicationId = $_POST['job_application_id'] ?? null;
    
    // Load CV data
    $cvData = null;
    $jobDescription = null;
    
    if ($cvVariantId) {
        // Load from variant
        $variant = getCvVariant($cvVariantId, $user['id']);
        if (!$variant) {
            throw new Exception('CV variant not found');
        }
        $cvData = loadCvVariantData($cvVariantId);
    } else {
        // No variant specified - use master CV
        // Get or create master variant ID so assessment can be linked to it
        try {
            $cvVariantId = getOrCreateMasterVariant($user['id']);
            if (!$cvVariantId) {
                // Log detailed error for debugging
                error_log("Failed to get or create master variant for user: " . $user['id']);
                // Check if variant exists but function returned null
                $existing = db()->fetchOne(
                    "SELECT id FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
                    [$user['id']]
                );
                if ($existing) {
                    $cvVariantId = $existing['id'];
                    error_log("Found existing master variant: " . $cvVariantId);
                } else {
                    // Get more details about why it failed
                    $errorDetails = "User ID: {$user['id']}. ";
                    try {
                        $userCheck = db()->fetchOne("SELECT id FROM profiles WHERE id = ?", [$user['id']]);
                        $errorDetails .= $userCheck ? "User exists. " : "User NOT found in profiles. ";
                    } catch (Exception $e) {
                        $errorDetails .= "Error checking user: " . $e->getMessage() . ". ";
                    }
                    
                    error_log("Master variant creation failed. " . $errorDetails);
                    throw new Exception('Failed to get or create master CV variant. ' . (DEBUG ? $errorDetails : 'Please try again or contact support if the issue persists.'));
                }
            }
        } catch (Exception $e) {
            error_log("Exception in getOrCreateMasterVariant: " . $e->getMessage());
            throw new Exception('Failed to get or create master CV variant: ' . $e->getMessage());
        }
        $cvData = loadCvData($user['id']);
    }
    
    if (!$cvData || empty($cvData)) {
        throw new Exception('No CV data found');
    }
    
    // Load job description if job application provided
    if ($jobApplicationId) {
        $jobApp = db()->fetchOne(
            "SELECT * FROM job_applications WHERE id = ? AND user_id = ?",
            [$jobApplicationId, $user['id']]
        );
        
        if ($jobApp) {
            $jobDescription = $jobApp['job_description'] ?? $jobApp['notes'] ?? null;
        }
    }
    
    // Check if this is a browser AI result being saved (from client-side execution)
    $browserAiResult = $_POST['browser_ai_result'] ?? null;
    
    if ($browserAiResult) {
        // Browser AI already executed client-side - parse and use the result
        $assessment = json_decode($browserAiResult, true);
        if (!$assessment || !is_array($assessment)) {
            throw new Exception('Invalid browser AI result format');
        }
        // Validate assessment structure
        require_once __DIR__ . '/../php/ai-service.php';
        $aiService = new AIService($user['id']); // Just for validation method
        $assessment = $aiService->validateAssessment($assessment);
    } else {
        // Server-side AI execution
        // Get AI service with user ID for user-specific settings
        $aiService = getAIService($user['id']);
        
        // Call AI to assess CV
        $result = $aiService->assessCvQuality($cvData, $jobDescription);
        
        // Check if this is browser execution mode
        if (isset($result['browser_execution']) && $result['browser_execution']) {
            // Browser AI - return prompt and instructions for frontend execution
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'browser_execution' => true,
                'prompt' => $result['prompt'] ?? '',
                'model' => $result['model'] ?? 'llama3.2',
                'model_type' => $result['model_type'] ?? 'webllm',
                'cv_data' => $cvData,
                'job_description' => $jobDescription,
                'message' => 'Browser AI execution required. Frontend will handle this request.'
            ]);
            exit;
        }
        
        if (!$result['success']) {
            throw new Exception($result['error'] ?? 'AI assessment failed');
        }
        
        $assessment = $result['assessment'];
    }
    
    // Save assessment to database
    $assessmentId = generateUuid();
    $insertData = [
        'id' => $assessmentId,
        'user_id' => $user['id'],
        'cv_variant_id' => $cvVariantId,
        'overall_score' => $assessment['overall_score'] ?? 0,
        'ats_score' => $assessment['ats_score'] ?? 0,
        'content_score' => $assessment['content_score'] ?? 0,
        'formatting_score' => $assessment['formatting_score'] ?? 0,
        'keyword_match_score' => $assessment['keyword_match_score'] ?? null,
        'recommendations' => json_encode($assessment['recommendations'] ?? []),
        'strengths' => json_encode($assessment['strengths'] ?? []),
        'weaknesses' => json_encode($assessment['weaknesses'] ?? []),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Add enhanced_recommendations if available
    if (!empty($assessment['enhanced_recommendations'])) {
        $insertData['enhanced_recommendations'] = json_encode($assessment['enhanced_recommendations']);
    }
    
    db()->insert('cv_quality_assessments', $insertData);
    
    // Clean output buffer and return success
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'assessment_id' => $assessmentId,
        'cv_variant_id' => $cvVariantId, // Include for debugging
        'assessment' => $assessment
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("AI Assess CV Error: " . $e->getMessage());
    error_log("AI Assess CV Error Stack: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("AI Assess CV Fatal Error: " . $e->getMessage());
    error_log("AI Assess CV Fatal Error Stack: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'A fatal error occurred: ' . $e->getMessage()
    ]);
}

