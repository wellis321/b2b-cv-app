<?php
/**
 * AI CV Template Generation API Endpoint
 * Generates custom CV templates based on user descriptions
 */

// Prevent canonical redirect
define('SKIP_CANONICAL_REDIRECT', true);

// Start output buffering
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Increase timeout for AI processing
set_time_limit(180);
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check authentication
$user = getCurrentUser();
if (!$user) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $userDescription = $_POST['description'] ?? '';
    $optionsJson = $_POST['options'] ?? '{}';
    $options = json_decode($optionsJson, true);
    
    if (!is_array($options)) {
        $options = [];
    }
    
    // Check if at least one input is provided
    $hasDescription = !empty($userDescription);
    $hasUrl = !empty($options['reference_url']);
    $hasImage = !empty($_FILES['reference_image']['tmp_name']) && is_uploaded_file($_FILES['reference_image']['tmp_name']);
    
    if (!$hasDescription && !$hasUrl && !$hasImage) {
        throw new Exception('Please provide at least one of the following: a description, a reference URL, or an image.');
    }
    
    // If no description provided, create a default one based on what was provided
    if (empty($userDescription)) {
        if ($hasUrl) {
            $userDescription = 'Create a CV template inspired by the design and layout of: ' . $options['reference_url'];
        } elseif ($hasImage) {
            $userDescription = 'Create a CV template that matches the visual design, layout, colors, and styling shown in the uploaded reference image.';
        }
    }
    
    // Handle image upload
    $imagePath = null;
    if (!empty($_FILES['reference_image']['tmp_name']) && is_uploaded_file($_FILES['reference_image']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['reference_image']['type'];
        $fileSize = $_FILES['reference_image']['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid image type. Please upload JPEG, PNG, GIF, or WEBP.');
        }
        
        if ($fileSize > 10 * 1024 * 1024) { // 10MB
            throw new Exception('Image file is too large. Maximum size is 10MB.');
        }
        
        // Save uploaded image temporarily
        $uploadDir = STORAGE_PATH . '/template-references/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($_FILES['reference_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('ref_', true) . '.' . $extension;
        $imagePath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($_FILES['reference_image']['tmp_name'], $imagePath)) {
            throw new Exception('Failed to save uploaded image.');
        }
        
        $options['reference_image_path'] = $imagePath;
    }
    
    // Load user's CV data
    $cvData = loadCvData($user['id']);
    
    if (!$cvData || empty($cvData)) {
        throw new Exception('No CV data found. Please add some content to your CV first.');
    }
    
    // Get AI service with user ID for user-specific settings
    $aiService = getAIService($user['id']);
    
    // Check if this is a browser AI result being saved (from client-side execution)
    $browserAiResult = $_POST['browser_ai_result'] ?? null;
    
    if ($browserAiResult) {
        // Browser AI already executed client-side - parse and use the result
        $template = json_decode($browserAiResult, true);
        if (!$template || !is_array($template)) {
            throw new Exception('Invalid browser AI result format');
        }
        
        // Validate template structure
        $validation = $aiService->validateTemplate($template);
        if (!$validation['valid']) {
            throw new Exception('Generated template failed validation: ' . ($validation['error'] ?? 'Invalid template structure'));
        }
        
        $result = [
            'success' => true,
            'html' => $template['html'] ?? '',
            'css' => $template['css'] ?? '',
            'instructions' => $template['instructions'] ?? ''
        ];
    } else {
        // Server-side AI execution
        // Generate template
        $result = $aiService->generateCvTemplate($cvData, $userDescription, $options);
        
        // Check if this is browser execution mode
        if (isset($result['browser_execution']) && $result['browser_execution']) {
            // Browser AI - return prompt and instructions for frontend execution
            // Clean up temporary image file before returning
            if ($imagePath && file_exists($imagePath)) {
                @unlink($imagePath);
            }
            
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'browser_execution' => true,
                'prompt' => $result['prompt'] ?? '',
                'model' => $result['model'] ?? 'llama3.2',
                'model_type' => $result['model_type'] ?? 'webllm',
                'cv_data' => $cvData,
                'user_description' => $userDescription,
                'options' => $options,
                'message' => 'Browser AI execution required. Frontend will handle this request.'
            ]);
            exit;
        }
        
        // Clean up temporary image file after processing
        if ($imagePath && file_exists($imagePath)) {
            @unlink($imagePath);
        }
        
        if (!$result['success']) {
            // Log the raw response for debugging
            if (!empty($result['raw_response'])) {
                error_log("AI Template Generation Raw Response: " . substr($result['raw_response'], 0, 1000));
            }
            throw new Exception($result['error'] ?? 'Template generation failed');
        }
    }
    
    // Save template to database (but don't activate yet - user can preview first)
    $templateHtml = $result['html'];
    $templateCss = $result['css'] ?? '';
    $instructions = $result['instructions'] ?? '';
    
    // Generate template name from description or use default
    $templateName = 'Custom Template';
    if (!empty($userDescription)) {
        // Use first 50 chars of description as name
        $templateName = substr(trim($userDescription), 0, 50);
        if (strlen($userDescription) > 50) {
            $templateName .= '...';
        }
    }
    
    // Include cv-templates.php functions
    require_once __DIR__ . '/../php/cv-templates.php';
    
    // Create template in cv_templates table
    $createResult = createCvTemplate(
        $user['id'],
        $templateName,
        $templateHtml,
        $templateCss,
        $instructions
    );
    
    if (!$createResult['success']) {
        throw new Exception($createResult['error'] ?? 'Failed to save template');
    }
    
    $templateId = $createResult['template_id'];
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'template_id' => $templateId,
        'html' => $templateHtml,
        'css' => $templateCss,
        'instructions' => $instructions,
        'message' => 'Template generated successfully. Preview it before activating.'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("AI Generate CV Template Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

