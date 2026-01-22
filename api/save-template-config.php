<?php
/**
 * Save Template Configuration
 * Saves visual builder configuration and generates Twig template
 */

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
require_once __DIR__ . '/../php/authorisation.php';
if (!isSuperAdmin($user['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../php/cv-templates.php';
require_once __DIR__ . '/../php/template-config-schema.php';
require_once __DIR__ . '/../php/template-config-to-twig.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['template_config'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$templateId = $input['template_id'] ?? null;
$templateName = $input['template_name'] ?? 'Untitled Template';
$templateDescription = $input['template_description'] ?? '';
$templateConfig = $input['template_config'];

// Validate configuration
$validation = validateTemplateConfig($templateConfig);
if (!$validation['valid']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid configuration: ' . implode(', ', $validation['errors'])]);
    exit;
}

try {
    // Convert config to Twig
    $result = convertConfigToTwig($templateConfig);
    $templateHtml = $result['html'];
    $templateCss = $result['css'];
    
    // Save template config as JSON
    $configJson = json_encode($templateConfig, JSON_PRETTY_PRINT);
    
    if ($templateId) {
        // Update existing template
        $existingTemplate = getCvTemplate($templateId, $user['id']);
        if (!$existingTemplate) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Template not found']);
            exit;
        }
        
        // Update template
        $updateResult = updateCvTemplate(
            $templateId,
            $user['id'],
            $templateName,
            $templateHtml,
            $templateCss,
            $templateDescription
        );
        
        if (!$updateResult['success']) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $updateResult['error'] ?? 'Failed to update template']);
            exit;
        }
        
        // Update template_config and builder_type
        db()->update('cv_templates', [
            'template_config' => $configJson,
            'builder_type' => 'visual',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ? AND user_id = ?', [$templateId, $user['id']]);
        
        echo json_encode(['success' => true, 'template_id' => $templateId]);
    } else {
        // Create new template
        $createResult = createCvTemplate(
            $user['id'],
            $templateName,
            $templateHtml,
            $templateCss,
            $templateDescription
        );
        
        if (!$createResult['success']) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $createResult['error'] ?? 'Failed to create template']);
            exit;
        }
        
        $newTemplateId = $createResult['template_id'];
        
        // Update template_config and builder_type
        db()->update('cv_templates', [
            'template_config' => $configJson,
            'builder_type' => 'visual',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ? AND user_id = ?', [$newTemplateId, $user['id']]);
        
        echo json_encode(['success' => true, 'template_id' => $newTemplateId]);
    }
} catch (Exception $e) {
    error_log("Error saving template config: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save template: ' . $e->getMessage()]);
}

