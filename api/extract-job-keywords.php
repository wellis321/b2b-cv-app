<?php
/**
 * API endpoint to extract keywords from a job description
 */

// Prevent any PHP notices/warnings from being sent before JSON
ob_start();

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isPost()) {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!verifyCsrfToken(post(CSRF_TOKEN_NAME))) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

try {
    $userId = getUserId();
    $applicationId = post('application_id');
    
    if (!$applicationId) {
        throw new Exception('Application ID is required');
    }
    
    // Get the job application
    $job = db()->fetchOne(
        "SELECT * FROM job_applications WHERE id = ? AND user_id = ?",
        [$applicationId, $userId]
    );
    
    if (!$job) {
        throw new Exception('Job application not found');
    }
    
    // Check if job description exists
    if (empty($job['job_description'])) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'error' => 'No job description available'
        ]);
        exit;
    }
    
    // Plain text for AI (strip HTML so we don't send raw table markup)
    $descriptionForAi = trim(strip_tags($job['job_description']));
    if ($descriptionForAi === '') {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'error' => 'No job description text available after removing formatting'
        ]);
        exit;
    }
    
    // Initialize AI service
    require_once __DIR__ . '/../php/ai-service.php';
    $aiService = new AIService($userId);
    
    // Extract keywords
    $result = $aiService->extractJobKeywords($descriptionForAi);
    
    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Failed to extract keywords');
    }
    
    // Check if browser execution is required
    if (isset($result['browser_execution']) && $result['browser_execution']) {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'browser_execution' => true,
            'prompt' => $result['prompt'] ?? '',
            'model' => $result['model'] ?? 'llama3.2',
            'model_type' => $result['model_type'] ?? 'webllm',
            'job_description' => $job['job_description'],
            'application_id' => $applicationId
        ]);
        exit;
    }
    
    // Save keywords to database
    $keywords = $result['keywords'] ?? [];
    db()->update(
        'job_applications',
        ['extracted_keywords' => json_encode($keywords)],
        'id = ? AND user_id = ?',
        [$applicationId, $userId]
    );
    
    // Return keywords (discard any stray output before JSON)
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'keywords' => $keywords
    ]);
    
} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    // Catch any fatal errors or other throwables
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
