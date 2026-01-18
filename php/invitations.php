<?php
/**
 * Invitation System
 * Handles candidate and team member invitations
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/email.php';

/**
 * Create a candidate invitation
 */
function createCandidateInvitation($organisationId, $email, $invitedBy, $fullName = null, $assignedRecruiter = null, $message = null) {
    $db = db();

    // Check if email is already a candidate in this organisation
    $existing = $db->fetchOne(
        "SELECT id FROM profiles WHERE email = ? AND organisation_id = ? AND account_type = 'candidate'",
        [$email, $organisationId]
    );

    if ($existing) {
        return ['success' => false, 'error' => 'This email is already registered as a candidate in your organisation.'];
    }

    // Check for existing pending invitation
    $pendingInvite = $db->fetchOne(
        "SELECT id FROM candidate_invitations
         WHERE email = ? AND organisation_id = ? AND accepted_at IS NULL AND expires_at > NOW()",
        [$email, $organisationId]
    );

    if ($pendingInvite) {
        return ['success' => false, 'error' => 'An invitation has already been sent to this email address.'];
    }

    // Check organisation candidate limit
    if (!canAddCandidate($organisationId)) {
        return ['success' => false, 'error' => 'Your organisation has reached its candidate limit. Please upgrade your plan.'];
    }

    try {
        $invitationId = generateUuid();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $db->insert('candidate_invitations', [
            'id' => $invitationId,
            'organisation_id' => $organisationId,
            'email' => $email,
            'full_name' => $fullName,
            'invited_by' => $invitedBy,
            'assigned_recruiter' => $assignedRecruiter,
            'token' => $token,
            'message' => $message,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Get organisation details for email
        $org = getOrganisationById($organisationId);
        $inviter = db()->fetchOne("SELECT full_name FROM profiles WHERE id = ?", [$invitedBy]);

        // Send invitation email
        // #region agent log
        debugLog([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'invitations.php:66',
            'message' => 'About to call sendCandidateInvitationEmail',
            'data' => [
                'email' => $email,
                'fullName' => $fullName,
                'organisationName' => $org['name'],
                'inviterName' => $inviter['full_name'] ?? 'A recruiter',
                'tokenLength' => strlen($token),
                'hasMessage' => !empty($message)
            ],
            'sessionId' => 'email-debug',
            'runId' => 'run1',
            'hypothesisId' => 'E1,E2'
        ]);
        // #endregion
        
        $emailSent = sendCandidateInvitationEmail(
            $email,
            $fullName,
            $org['name'],
            $inviter['full_name'] ?? 'A recruiter',
            $token,
            $message
        );

        // #region agent log
        debugLog([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'invitations.php:74',
            'message' => 'sendCandidateInvitationEmail returned',
            'data' => [
                'emailSent' => $emailSent,
                'emailSentType' => gettype($emailSent),
                'email' => $email,
                'invitationId' => $invitationId
            ],
            'sessionId' => 'email-debug',
            'runId' => 'run1',
            'hypothesisId' => 'E1,E2,E3'
        ]);
        // #endregion

        logActivity('candidate.invited', null, [
            'email' => $email,
            'invitation_id' => $invitationId
        ], $organisationId);

        return [
            'success' => true,
            'invitation_id' => $invitationId,
            'email_sent' => $emailSent
        ];

    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to create invitation. Please try again.'];
    }
}

/**
 * Create a team member invitation
 */
function createTeamInvitation($organisationId, $email, $role, $invitedBy, $message = null) {
    $db = db();

    // Validate role
    if (!in_array($role, ['admin', 'recruiter', 'viewer'])) {
        return ['success' => false, 'error' => 'Invalid role specified.'];
    }

    // Check if email is already a team member
    $existing = $db->fetchOne(
        "SELECT om.id FROM organisation_members om
         JOIN profiles p ON om.user_id = p.id
         WHERE p.email = ? AND om.organisation_id = ?",
        [$email, $organisationId]
    );

    if ($existing) {
        return ['success' => false, 'error' => 'This email is already a team member in your organisation.'];
    }

    // Check for existing pending invitation
    $pendingInvite = $db->fetchOne(
        "SELECT id FROM team_invitations
         WHERE email = ? AND organisation_id = ? AND accepted_at IS NULL AND expires_at > NOW()",
        [$email, $organisationId]
    );

    if ($pendingInvite) {
        return ['success' => false, 'error' => 'An invitation has already been sent to this email address.'];
    }

    // Check organisation team member limit
    if (!canAddTeamMember($organisationId)) {
        return ['success' => false, 'error' => 'Your organisation has reached its team member limit. Please upgrade your plan.'];
    }

    try {
        $invitationId = generateUuid();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $db->insert('team_invitations', [
            'id' => $invitationId,
            'organisation_id' => $organisationId,
            'email' => $email,
            'role' => $role,
            'invited_by' => $invitedBy,
            'token' => $token,
            'message' => $message,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Get organisation details for email
        $org = getOrganisationById($organisationId);
        $inviter = db()->fetchOne("SELECT full_name FROM profiles WHERE id = ?", [$invitedBy]);

        // Send invitation email
        $emailSent = sendTeamInvitationEmail(
            $email,
            $org['name'],
            $role,
            $inviter['full_name'] ?? 'An administrator',
            $token,
            $message
        );

        logActivity('team.invited', null, [
            'email' => $email,
            'role' => $role,
            'invitation_id' => $invitationId
        ], $organisationId);

        return [
            'success' => true,
            'invitation_id' => $invitationId,
            'email_sent' => $emailSent
        ];

    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to create invitation. Please try again.'];
    }
}

/**
 * Validate a candidate invitation token
 */
function validateCandidateInvitation($token) {
    $invitation = db()->fetchOne(
        "SELECT ci.*, o.name as organisation_name, o.slug as organisation_slug
         FROM candidate_invitations ci
         JOIN organisations o ON ci.organisation_id = o.id
         WHERE ci.token = ?",
        [$token]
    );

    if (!$invitation) {
        return ['valid' => false, 'error' => 'Invalid invitation link.'];
    }

    if ($invitation['accepted_at']) {
        return ['valid' => false, 'error' => 'This invitation has already been used.'];
    }

    if (strtotime($invitation['expires_at']) < time()) {
        return ['valid' => false, 'error' => 'This invitation has expired. Please contact your recruiter for a new invitation.'];
    }

    return ['valid' => true, 'invitation' => $invitation];
}

/**
 * Validate a team invitation token
 */
function validateTeamInvitation($token) {
    $invitation = db()->fetchOne(
        "SELECT ti.*, o.name as organisation_name, o.slug as organisation_slug
         FROM team_invitations ti
         JOIN organisations o ON ti.organisation_id = o.id
         WHERE ti.token = ?",
        [$token]
    );

    if (!$invitation) {
        return ['valid' => false, 'error' => 'Invalid invitation link.'];
    }

    if ($invitation['accepted_at']) {
        return ['valid' => false, 'error' => 'This invitation has already been used.'];
    }

    if (strtotime($invitation['expires_at']) < time()) {
        return ['valid' => false, 'error' => 'This invitation has expired. Please contact your administrator for a new invitation.'];
    }

    return ['valid' => true, 'invitation' => $invitation];
}

/**
 * Accept a candidate invitation (creates account or links existing)
 */
function acceptCandidateInvitation($token, $password, $fullName = null) {
    $validation = validateCandidateInvitation($token);

    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }

    $invitation = $validation['invitation'];
    $db = db();

    try {
        $db->beginTransaction();

        // Check if user already exists with this email
        $existingUser = $db->fetchOne(
            "SELECT id, account_type, organisation_id FROM profiles WHERE email = ?",
            [$invitation['email']]
        );

        if ($existingUser) {
            // User exists - link them to the organisation
            if ($existingUser['organisation_id'] && $existingUser['organisation_id'] !== $invitation['organisation_id']) {
                $db->rollback();
                return ['success' => false, 'error' => 'This email is already associated with another organisation.'];
            }

            $userId = $existingUser['id'];

            $db->update('profiles', [
                'organisation_id' => $invitation['organisation_id'],
                'account_type' => 'candidate',
                'managed_by' => $invitation['assigned_recruiter'],
                'cv_visibility' => 'organisation',
                'cv_status' => 'draft',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$userId]);

        } else {
            // Create new user account
            $passwordValidation = validatePasswordStrength($password);
            if (!$passwordValidation['valid']) {
                $db->rollback();
                return ['success' => false, 'error' => implode('. ', $passwordValidation['errors'])];
            }

            $userId = generateUuid();
            $passwordHash = hashPassword($password);
            $username = 'user' . substr(str_replace('-', '', $userId), 0, 8);

            $db->insert('profiles', [
                'id' => $userId,
                'email' => $invitation['email'],
                'password_hash' => $passwordHash,
                'full_name' => $fullName ?? $invitation['full_name'],
                'username' => $username,
                'email_verified' => 1, // Auto-verify since they received the invitation email
                'organisation_id' => $invitation['organisation_id'],
                'account_type' => 'candidate',
                'managed_by' => $invitation['assigned_recruiter'],
                'cv_visibility' => 'organisation',
                'cv_status' => 'draft',
                'invitation_token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Mark invitation as accepted
        $db->update('candidate_invitations', [
            'accepted_at' => date('Y-m-d H:i:s'),
            'accepted_by' => $userId
        ], 'id = ?', [$invitation['id']]);

        $db->commit();

        logActivity('candidate.invitation_accepted', $userId, [
            'invitation_id' => $invitation['id']
        ], $invitation['organisation_id']);

        return [
            'success' => true,
            'user_id' => $userId,
            'is_new_user' => !isset($existingUser)
        ];

    } catch (Exception $e) {
        $db->rollback();
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to accept invitation. Please try again.'];
    }
}

/**
 * Accept a team invitation
 */
function acceptTeamInvitation($token, $password = null, $fullName = null) {
    $validation = validateTeamInvitation($token);

    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }

    $invitation = $validation['invitation'];
    $db = db();

    try {
        $db->beginTransaction();

        // Check if user already exists with this email
        $existingUser = $db->fetchOne(
            "SELECT id FROM profiles WHERE email = ?",
            [$invitation['email']]
        );

        if ($existingUser) {
            $userId = $existingUser['id'];
        } else {
            // Create new user account
            if (!$password) {
                $db->rollback();
                return ['success' => false, 'error' => 'Password is required for new accounts.'];
            }

            $passwordValidation = validatePasswordStrength($password);
            if (!$passwordValidation['valid']) {
                $db->rollback();
                return ['success' => false, 'error' => implode('. ', $passwordValidation['errors'])];
            }

            $userId = generateUuid();
            $passwordHash = hashPassword($password);
            $username = 'user' . substr(str_replace('-', '', $userId), 0, 8);

            $db->insert('profiles', [
                'id' => $userId,
                'email' => $invitation['email'],
                'password_hash' => $passwordHash,
                'full_name' => $fullName,
                'username' => $username,
                'email_verified' => 1,
                'account_type' => 'individual',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Add user to organisation
        $db->insert('organisation_members', [
            'id' => generateUuid(),
            'organisation_id' => $invitation['organisation_id'],
            'user_id' => $userId,
            'role' => $invitation['role'],
            'is_active' => 1,
            'invited_by' => $invitation['invited_by'],
            'joined_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Mark invitation as accepted
        $db->update('team_invitations', [
            'accepted_at' => date('Y-m-d H:i:s'),
            'accepted_by' => $userId
        ], 'id = ?', [$invitation['id']]);

        $db->commit();

        logActivity('team.invitation_accepted', $userId, [
            'invitation_id' => $invitation['id'],
            'role' => $invitation['role']
        ], $invitation['organisation_id']);

        return [
            'success' => true,
            'user_id' => $userId,
            'is_new_user' => !isset($existingUser)
        ];

    } catch (Exception $e) {
        $db->rollback();
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to accept invitation. Please try again.'];
    }
}

/**
 * Resend a candidate invitation
 */
function resendCandidateInvitation($invitationId, $organisationId) {
    $db = db();

    $invitation = $db->fetchOne(
        "SELECT * FROM candidate_invitations WHERE id = ? AND organisation_id = ?",
        [$invitationId, $organisationId]
    );

    if (!$invitation) {
        return ['success' => false, 'error' => 'Invitation not found.'];
    }

    if ($invitation['accepted_at']) {
        return ['success' => false, 'error' => 'This invitation has already been accepted.'];
    }

    try {
        // Generate new token and extend expiry
        $newToken = bin2hex(random_bytes(32));
        $newExpiry = date('Y-m-d H:i:s', strtotime('+7 days'));

        $db->update('candidate_invitations', [
            'token' => $newToken,
            'expires_at' => $newExpiry
        ], 'id = ?', [$invitationId]);

        // Get organisation details and resend email
        $org = getOrganisationById($organisationId);
        $inviter = $db->fetchOne("SELECT full_name FROM profiles WHERE id = ?", [$invitation['invited_by']]);

        $emailSent = sendCandidateInvitationEmail(
            $invitation['email'],
            $invitation['full_name'],
            $org['name'],
            $inviter['full_name'] ?? 'A recruiter',
            $newToken,
            $invitation['message']
        );

        return ['success' => true, 'email_sent' => $emailSent];

    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Failed to resend invitation.'];
    }
}

/**
 * Cancel a candidate invitation
 */
function cancelCandidateInvitation($invitationId, $organisationId) {
    $db = db();

    $result = $db->delete(
        'candidate_invitations',
        'id = ? AND organisation_id = ? AND accepted_at IS NULL',
        [$invitationId, $organisationId]
    );

    if ($result) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => 'Invitation not found or already accepted.'];
}

/**
 * Cancel a team invitation
 */
function cancelTeamInvitation($invitationId, $organisationId) {
    $db = db();

    $result = $db->delete(
        'team_invitations',
        'id = ? AND organisation_id = ? AND accepted_at IS NULL',
        [$invitationId, $organisationId]
    );

    if ($result) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => 'Invitation not found or already accepted.'];
}

/**
 * Get pending candidate invitations for an organisation
 */
function getPendingCandidateInvitations($organisationId) {
    return db()->fetchAll(
        "SELECT ci.*, inviter.full_name as invited_by_name, recruiter.full_name as recruiter_name
         FROM candidate_invitations ci
         LEFT JOIN profiles inviter ON ci.invited_by = inviter.id
         LEFT JOIN profiles recruiter ON ci.assigned_recruiter = recruiter.id
         WHERE ci.organisation_id = ? AND ci.accepted_at IS NULL AND ci.expires_at > NOW()
         ORDER BY ci.created_at DESC",
        [$organisationId]
    );
}

/**
 * Get pending team invitations for an organisation
 */
function getPendingTeamInvitations($organisationId) {
    return db()->fetchAll(
        "SELECT ti.*, inviter.full_name as invited_by_name
         FROM team_invitations ti
         LEFT JOIN profiles inviter ON ti.invited_by = inviter.id
         WHERE ti.organisation_id = ? AND ti.accepted_at IS NULL AND ti.expires_at > NOW()
         ORDER BY ti.created_at DESC",
        [$organisationId]
    );
}
