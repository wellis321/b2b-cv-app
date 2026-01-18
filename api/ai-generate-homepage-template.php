<?php
/**
 * AI Homepage Template Generation API Endpoint
 * Generates custom homepage templates for organisations based on URL references or descriptions
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

// Check authentication and organisation access
$org = requireOrganisationAccess('admin');
$user = getCurrentUser();

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $userDescription = $_POST['description'] ?? '';
    $referenceUrl = $_POST['reference_url'] ?? '';
    $optionsJson = $_POST['options'] ?? '{}';
    $options = json_decode($optionsJson, true);
    
    if (!is_array($options)) {
        $options = [];
    }
    
    // Add reference URL to options
    if (!empty($referenceUrl)) {
        $options['reference_url'] = $referenceUrl;
    }
    
    // Check if at least one input is provided
    $hasDescription = !empty($userDescription);
    $hasUrl = !empty($referenceUrl);
    $hasImage = !empty($_FILES['reference_image']['tmp_name']) && is_uploaded_file($_FILES['reference_image']['tmp_name']);
    
    if (!$hasDescription && !$hasUrl && !$hasImage) {
        throw new Exception('Please provide at least one of the following: a description, a reference URL, or an image.');
    }
    
    // Get organisation data for template context
    $organisation = getOrganisationById($org['organisation_id']);
    
    // Build organisation data for template
    $orgData = [
        'name' => $organisation['name'],
        'slug' => $organisation['slug'],
        'logo_url' => $organisation['logo_url'] ?? null,
        'primary_colour' => $organisation['primary_colour'] ?? '#4338ca',
        'secondary_colour' => $organisation['secondary_colour'] ?? '#7e22ce',
        'candidate_count' => db()->fetchOne(
            "SELECT COUNT(*) as count FROM profiles WHERE organisation_id = ? AND account_type = 'candidate'",
            [$organisation['id']]
        )['count'] ?? 0,
        'public_url' => APP_URL . '/agency/' . $organisation['slug']
    ];
    
    // If no description provided, create one based on what was provided
    if (empty($userDescription)) {
        if ($hasUrl) {
            $userDescription = 'Create a homepage template inspired by the design and layout of: ' . $referenceUrl . '. Adapt it for a recruitment agency organisation homepage.';
        } elseif ($hasImage) {
            $userDescription = 'Create a homepage template that matches the visual design, layout, colors, and styling shown in the uploaded reference image. Adapt it for a recruitment agency organisation homepage.';
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
    
    // Get AI service with user ID for user-specific settings
    $aiService = getAIService($user['id']);
    
    // Generate homepage template
    $result = $aiService->generateHomepageTemplate($orgData, $userDescription, $options);
    
    // Clean up temporary image file after processing
    if ($imagePath && file_exists($imagePath)) {
        @unlink($imagePath);
    }
    
    if (!$result['success']) {
        // Log the raw response for debugging
        if (!empty($result['raw_response'])) {
            error_log("AI Homepage Template Generation Raw Response: " . substr($result['raw_response'], 0, 1000));
        }
        throw new Exception($result['error'] ?? 'Template generation failed');
    }
    
    $templateHtml = $result['html'];
    $templateCss = $result['css'] ?? '';
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'html' => $templateHtml,
        'css' => $templateCss,
        'message' => 'Homepage template generated successfully. Review and save when ready.'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("AI Generate Homepage Template Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

