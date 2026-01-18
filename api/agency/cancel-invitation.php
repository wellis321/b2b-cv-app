<?php
/**
 * API endpoint for cancelling invitations (candidate or team)
 */

// Prevent canonical redirect
define('SKIP_CANONICAL_REDIRECT', true);

// Start output buffering
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../php/helpers.php';

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
$csrfToken = post(CSRF_TOKEN_NAME) ?? $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $invitationId = sanitizeInput(post('invitation_id'));
    $type = sanitizeInput(post('type')); // 'candidate' or 'team'
    
    if (empty($invitationId)) {
        throw new Exception('Invitation ID is required');
    }
    
    if (empty($type) || !in_array($type, ['candidate', 'team'])) {
        throw new Exception('Invalid invitation type. Must be "candidate" or "team"');
    }
    
    $organisationId = $org['organisation_id'];
    
    // Cancel the appropriate invitation type
    if ($type === 'candidate') {
        $result = cancelCandidateInvitation($invitationId, $organisationId);
    } else {
        $result = cancelTeamInvitation($invitationId, $organisationId);
    }
    
    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Failed to cancel invitation');
    }
    
    // Log activity
    logActivity('team.invitation_cancelled', null, [
        'invitation_id' => $invitationId,
        'type' => $type
    ], $organisationId);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Invitation cancelled successfully'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    error_log("Cancel invitation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

