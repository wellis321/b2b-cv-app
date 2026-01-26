<?php
/**
 * Save user's selected skills for a template
 */

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: application/json');

requireAuth();

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!verifyCsrfToken(post(CSRF_TOKEN_NAME))) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

$userId = getUserId();
$templateId = sanitizeInput(post('template_id') ?? '');
$raw = post('selected_skill_ids');
if (is_string($raw)) {
    $decoded = json_decode($raw, true);
    $selectedSkillIds = is_array($decoded) ? $decoded : [];
} else {
    $selectedSkillIds = is_array($raw) ? $raw : [];
}

if (empty($templateId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID is required']);
    exit;
}

// Validate that all skill IDs belong to the user
if (!empty($selectedSkillIds)) {
    $placeholders = str_repeat('?,', count($selectedSkillIds) - 1) . '?';
    $userSkills = db()->fetchAll(
        "SELECT id FROM skills WHERE profile_id = ? AND id IN ($placeholders)",
        array_merge([$userId], $selectedSkillIds)
    );
    $validSkillIds = array_column($userSkills, 'id');
    
    // Filter to only include valid skill IDs
    $selectedSkillIds = array_intersect($selectedSkillIds, $validSkillIds);
}

try {
    $skillIdsJson = json_encode($selectedSkillIds);
    $selectionId = generateUuid();
    
    // Check if selection already exists
    $existing = db()->fetchOne(
        "SELECT id FROM user_template_skill_selections 
         WHERE user_id = ? AND template_id = ?",
        [$userId, $templateId]
    );
    
    if ($existing) {
        // Update existing selection
        db()->update(
            'user_template_skill_selections',
            [
                'selected_skill_ids' => $skillIdsJson,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$existing['id']]
        );
    } else {
        // Insert new selection
        db()->insert('user_template_skill_selections', [
            'id' => $selectionId,
            'user_id' => $userId,
            'template_id' => $templateId,
            'selected_skill_ids' => $skillIdsJson
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'selected_skill_ids' => $selectedSkillIds
    ]);
} catch (Exception $e) {
    error_log("Error saving template skill selection: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save skill selection']);
}

