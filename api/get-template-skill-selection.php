<?php
/**
 * Get user's selected skills for a template
 */

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: application/json');

requireAuth();

$userId = getUserId();
$templateId = sanitizeInput(get('template_id') ?? '');

if (empty($templateId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID is required']);
    exit;
}

try {
    $selection = db()->fetchOne(
        "SELECT selected_skill_ids FROM user_template_skill_selections 
         WHERE user_id = ? AND template_id = ?",
        [$userId, $templateId]
    );
    
    if ($selection) {
        $skillIds = json_decode($selection['selected_skill_ids'], true);
        if (!is_array($skillIds)) {
            $skillIds = [];
        }
        echo json_encode([
            'success' => true,
            'selected_skill_ids' => $skillIds
        ]);
    } else {
        // Return empty array if no selection exists
        echo json_encode([
            'success' => true,
            'selected_skill_ids' => []
        ]);
    }
} catch (Exception $e) {
    error_log("Error getting template skill selection: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to get skill selection']);
}

