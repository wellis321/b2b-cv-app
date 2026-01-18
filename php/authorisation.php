<?php
/**
 * Authorisation and multi-tenant access control
 *
 * This file provides role-based access control (RBAC) for the recruitment agency
 * multi-tenant architecture. It handles organisation membership, permissions,
 * and candidate management authorisation.
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

// Role hierarchy constants (higher number = more permissions)
define('ROLE_VIEWER', 1);
define('ROLE_RECRUITER', 2);
define('ROLE_ADMIN', 3);
define('ROLE_OWNER', 4);
define('ROLE_SUPER_ADMIN', 5);

/**
 * Get role hierarchy value
 */
function getRoleLevel($role) {
    $roles = [
        'viewer' => ROLE_VIEWER,
        'recruiter' => ROLE_RECRUITER,
        'admin' => ROLE_ADMIN,
        'owner' => ROLE_OWNER
    ];
    return $roles[$role] ?? 0;
}

/**
 * Get current user's organisation membership
 * Returns organisation details along with the user's role
 */
function getUserOrganisation($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return null;
    }

    return db()->fetchOne(
        "SELECT om.id as membership_id, om.role, om.is_active, om.joined_at,
                o.id as organisation_id, o.name as organisation_name, o.slug,
                o.custom_domain, o.logo_url, o.primary_colour, o.secondary_colour,
                o.plan, o.subscription_status, o.subscription_current_period_end,
                o.max_candidates, o.max_team_members,
                o.default_cv_visibility, o.allow_candidate_self_registration,
                o.require_candidate_approval
         FROM organisation_members om
         JOIN organisations o ON om.organisation_id = o.id
         WHERE om.user_id = ? AND om.is_active = 1
         LIMIT 1",
        [$userId]
    );
}

/**
 * Get organisation by ID
 */
function getOrganisationById($organisationId) {
    return db()->fetchOne(
        "SELECT * FROM organisations WHERE id = ?",
        [$organisationId]
    );
}

/**
 * Get organisation by slug
 */
function getOrganisationBySlug($slug) {
    return db()->fetchOne(
        "SELECT * FROM organisations WHERE slug = ?",
        [$slug]
    );
}

/**
 * Check if user is a member of an organisation
 */
function isOrganisationMember($userId = null, $organisationId = null) {
    $org = getUserOrganisation($userId);

    if (!$org) {
        return false;
    }

    if ($organisationId !== null && $org['organisation_id'] !== $organisationId) {
        return false;
    }

    return true;
}

/**
 * Check if user has at least the specified role in their organisation
 */
function hasRole($requiredRole, $userId = null) {
    $org = getUserOrganisation($userId);

    if (!$org) {
        return false;
    }

    return getRoleLevel($org['role']) >= getRoleLevel($requiredRole);
}

/**
 * Check if user is an organisation owner
 */
function isOrganisationOwner($userId = null) {
    return hasRole('owner', $userId);
}

/**
 * Check if user is an organisation admin (or owner)
 */
function isOrganisationAdmin($userId = null) {
    return hasRole('admin', $userId);
}

/**
 * Check if user is a recruiter (or higher)
 */
function isRecruiter($userId = null) {
    return hasRole('recruiter', $userId);
}

/**
 * Check if user is a super admin
 */
function isSuperAdmin($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return false;
    }

    $profile = db()->fetchOne(
        "SELECT is_super_admin FROM profiles WHERE id = ?",
        [$userId]
    );

    return $profile && !empty($profile['is_super_admin']);
}

/**
 * Require super admin access - redirect if not super admin
 */
function requireSuperAdmin() {
    if (!isLoggedIn()) {
        redirect('/index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }

    if (!isSuperAdmin()) {
        setFlash('error', 'You do not have permission to access this area.');
        redirect('/dashboard.php');
    }
}

/**
 * Require organisation access - redirect if not a member
 * Super admins can bypass this check
 */
function requireOrganisationAccess($requiredRole = 'viewer') {
    if (!isLoggedIn()) {
        redirect('/index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }

    // Super admins can access any organisation area
    if (isSuperAdmin()) {
        // Return a dummy org object for super admin
        return [
            'organisation_id' => null,
            'organisation_name' => 'System Admin',
            'role' => 'super_admin',
            'is_active' => true
        ];
    }

    $org = getUserOrganisation();

    if (!$org) {
        // User is not part of any organisation
        setFlash('error', 'You do not have access to this area.');
        redirect('/dashboard.php');
    }

    if (!$org['is_active']) {
        setFlash('error', 'Your organisation membership has been deactivated.');
        redirect('/dashboard.php');
    }

    if (getRoleLevel($org['role']) < getRoleLevel($requiredRole)) {
        setFlash('error', 'You do not have sufficient permissions to access this area.');
        redirect('/agency/dashboard.php');
    }

    return $org;
}

/**
 * Check if user can manage a specific candidate
 * Super admins can manage any candidate
 */
function canManageCandidate($candidateId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return false;
    }

    // Super admins can manage any candidate
    if (isSuperAdmin($userId)) {
        return true;
    }

    // Get the candidate's details
    $candidate = db()->fetchOne(
        "SELECT organisation_id, managed_by, account_type
         FROM profiles
         WHERE id = ?",
        [$candidateId]
    );

    if (!$candidate) {
        return false;
    }

    // If candidate is not part of an organisation, only they can manage themselves
    if (!$candidate['organisation_id']) {
        return $candidateId === $userId;
    }

    // Get the requesting user's organisation membership
    $userMembership = db()->fetchOne(
        "SELECT role FROM organisation_members
         WHERE user_id = ? AND organisation_id = ? AND is_active = 1",
        [$userId, $candidate['organisation_id']]
    );

    if (!$userMembership) {
        return false;
    }

    // Owners and admins can manage any candidate in their organisation
    if (in_array($userMembership['role'], ['owner', 'admin'])) {
        return true;
    }

    // Recruiters can only manage their assigned candidates
    if ($userMembership['role'] === 'recruiter') {
        return $candidate['managed_by'] === $userId;
    }

    // Viewers cannot manage candidates
    return false;
}

/**
 * Check if user can view a specific candidate's CV
 * Super admins can view any CV
 */
function canViewCandidate($candidateId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }

    // Super admins can view any CV
    if (isSuperAdmin($userId)) {
        return true;
    }

    // Get the candidate's details
    $candidate = db()->fetchOne(
        "SELECT organisation_id, cv_visibility, account_type
         FROM profiles
         WHERE id = ?",
        [$candidateId]
    );

    if (!$candidate) {
        return false;
    }

    // Public CVs can be viewed by anyone
    if ($candidate['cv_visibility'] === 'public') {
        return true;
    }

    // Private CVs can only be viewed by the owner
    if ($candidate['cv_visibility'] === 'private') {
        return $candidateId === $userId;
    }

    // Organisation-visible CVs
    if ($candidate['cv_visibility'] === 'organisation') {
        if (!$userId) {
            return false;
        }

        // Check if viewer is in the same organisation
        $viewerMembership = db()->fetchOne(
            "SELECT id FROM organisation_members
             WHERE user_id = ? AND organisation_id = ? AND is_active = 1",
            [$userId, $candidate['organisation_id']]
        );

        return !empty($viewerMembership);
    }

    return false;
}

/**
 * Get all candidates in an organisation
 */
function getOrganisationCandidates($organisationId, $recruiterId = null, $filters = []) {
    $params = [$organisationId];

    $sql = "SELECT p.id, p.full_name, p.email, p.username, p.photo_url,
                   p.cv_status, p.cv_visibility, p.created_at, p.updated_at,
                   recruiter.full_name as recruiter_name, recruiter.id as recruiter_id
            FROM profiles p
            LEFT JOIN profiles recruiter ON p.managed_by = recruiter.id
            WHERE p.organisation_id = ? AND p.account_type = 'candidate'";

    // Filter by recruiter if specified (for recruiter-level users)
    if ($recruiterId !== null) {
        $sql .= " AND p.managed_by = ?";
        $params[] = $recruiterId;
    }

    // Apply additional filters
    if (!empty($filters['cv_status'])) {
        $sql .= " AND p.cv_status = ?";
        $params[] = $filters['cv_status'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.full_name LIKE ? OR p.email LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY p.created_at DESC";

    // Pagination
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];

        if (!empty($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
    }

    return db()->fetchAll($sql, $params);
}

/**
 * Get candidate count for an organisation
 */
function getOrganisationCandidateCount($organisationId) {
    $result = db()->fetchOne(
        "SELECT COUNT(*) as count FROM profiles
         WHERE organisation_id = ? AND account_type = 'candidate'",
        [$organisationId]
    );
    return $result ? (int)$result['count'] : 0;
}

/**
 * Check if organisation can add more candidates (within plan limit)
 */
function canAddCandidate($organisationId = null) {
    if ($organisationId === null) {
        $org = getUserOrganisation();
        if (!$org) {
            return false;
        }
        $organisationId = $org['organisation_id'];
        $maxCandidates = $org['max_candidates'];
    } else {
        $org = getOrganisationById($organisationId);
        if (!$org) {
            return false;
        }
        $maxCandidates = $org['max_candidates'];
    }

    $currentCount = getOrganisationCandidateCount($organisationId);
    return $currentCount < $maxCandidates;
}

/**
 * Get team members for an organisation
 */
function getOrganisationTeamMembers($organisationId) {
    return db()->fetchAll(
        "SELECT om.id as membership_id, om.role, om.is_active, om.joined_at, om.created_at,
                p.id as user_id, p.full_name, p.email, p.photo_url,
                inviter.full_name as invited_by_name
         FROM organisation_members om
         JOIN profiles p ON om.user_id = p.id
         LEFT JOIN profiles inviter ON om.invited_by = inviter.id
         WHERE om.organisation_id = ?
         ORDER BY
            CASE om.role
                WHEN 'owner' THEN 1
                WHEN 'admin' THEN 2
                WHEN 'recruiter' THEN 3
                WHEN 'viewer' THEN 4
            END,
            p.full_name ASC",
        [$organisationId]
    );
}

/**
 * Get team member count for an organisation
 */
function getOrganisationTeamMemberCount($organisationId) {
    $result = db()->fetchOne(
        "SELECT COUNT(*) as count FROM organisation_members
         WHERE organisation_id = ? AND is_active = 1",
        [$organisationId]
    );
    return $result ? (int)$result['count'] : 0;
}

/**
 * Check if organisation can add more team members (within plan limit)
 */
function canAddTeamMember($organisationId = null) {
    if ($organisationId === null) {
        $org = getUserOrganisation();
        if (!$org) {
            return false;
        }
        $organisationId = $org['organisation_id'];
        $maxMembers = $org['max_team_members'];
    } else {
        $org = getOrganisationById($organisationId);
        if (!$org) {
            return false;
        }
        $maxMembers = $org['max_team_members'];
    }

    $currentCount = getOrganisationTeamMemberCount($organisationId);
    return $currentCount < $maxMembers;
}

/**
 * Log an activity for audit trail
 * Automatically marks super admin actions in details
 */
function logActivity($action, $targetUserId = null, $details = [], $organisationId = null) {
    $userId = getUserId();

    if ($organisationId === null) {
        $org = getUserOrganisation();
        $organisationId = $org ? $org['organisation_id'] : null;
    }

    // Mark super admin actions in details
    if (isSuperAdmin($userId)) {
        $details['super_admin_action'] = true;
    }

    require_once __DIR__ . '/utils.php';

    try {
        db()->insert('activity_log', [
            'id' => generateUuid(),
            'organisation_id' => $organisationId,
            'user_id' => $userId,
            'target_user_id' => $targetUserId,
            'action' => $action,
            'details' => !empty($details) ? json_encode($details) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Don't let logging failures break the application
        if (DEBUG) {
            error_log('Activity log error: ' . $e->getMessage());
        }
    }
}

/**
 * Get activity log for an organisation
 */
function getOrganisationActivityLog($organisationId, $limit = 50, $offset = 0) {
    return db()->fetchAll(
        "SELECT al.*,
                actor.full_name as actor_name, actor.email as actor_email,
                target.full_name as target_name, target.email as target_email
         FROM activity_log al
         LEFT JOIN profiles actor ON al.user_id = actor.id
         LEFT JOIN profiles target ON al.target_user_id = target.id
         WHERE al.organisation_id = ?
         ORDER BY al.created_at DESC
         LIMIT ? OFFSET ?",
        [$organisationId, $limit, $offset]
    );
}

/**
 * Check if user account is a candidate (agency-managed)
 */
function isCandidate($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return false;
    }

    $profile = db()->fetchOne(
        "SELECT account_type FROM profiles WHERE id = ?",
        [$userId]
    );

    return $profile && $profile['account_type'] === 'candidate';
}

/**
 * Check if user account is an individual (self-registered B2C)
 */
function isIndividualUser($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return false;
    }

    $profile = db()->fetchOne(
        "SELECT account_type, organisation_id FROM profiles WHERE id = ?",
        [$userId]
    );

    // Individual users have no organisation and account_type is 'individual'
    return $profile && $profile['account_type'] === 'individual' && empty($profile['organisation_id']);
}

/**
 * Get the appropriate dashboard URL for the current user
 */
function getDashboardUrl() {
    if (isOrganisationMember()) {
        return '/agency/dashboard.php';
    }

    // Candidates use the same dashboard as individual users to edit their CV sections
    return '/dashboard.php';
}

/**
 * Create a new organisation
 */
function createOrganisation($name, $slug, $ownerId) {
    require_once __DIR__ . '/utils.php';

    $db = db();

    // Check if slug is already taken
    $existing = $db->fetchOne(
        "SELECT id FROM organisations WHERE slug = ?",
        [$slug]
    );

    if ($existing) {
        return ['success' => false, 'error' => 'This organisation URL is already taken.'];
    }

    try {
        $db->beginTransaction();

        $organisationId = generateUuid();

        // Create the organisation
        $db->insert('organisations', [
            'id' => $organisationId,
            'name' => $name,
            'slug' => $slug,
            'plan' => 'agency_basic',
            'subscription_status' => 'inactive',
            'max_candidates' => 10,
            'max_team_members' => 3,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Add owner as first member
        $db->insert('organisation_members', [
            'id' => generateUuid(),
            'organisation_id' => $organisationId,
            'user_id' => $ownerId,
            'role' => 'owner',
            'is_active' => 1,
            'joined_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $db->commit();

        logActivity('organisation.created', null, ['organisation_id' => $organisationId, 'name' => $name], $organisationId);

        return ['success' => true, 'organisation_id' => $organisationId];

    } catch (Exception $e) {
        $db->rollback();
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to create organisation. Please try again.'];
    }
}

/**
 * Generate a URL-friendly slug from organisation name
 */
function generateOrganisationSlug($name) {
    // Convert to lowercase
    $slug = strtolower($name);

    // Replace spaces and underscores with hyphens
    $slug = preg_replace('/[\s_]+/', '-', $slug);

    // Remove any characters that aren't alphanumeric or hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);

    // Trim hyphens from start and end
    $slug = trim($slug, '-');

    // Ensure minimum length
    if (strlen($slug) < 3) {
        $slug .= '-agency';
    }

    return $slug;
}

/**
 * Check if a slug is available
 */
function isSlugAvailable($slug) {
    $existing = db()->fetchOne(
        "SELECT id FROM organisations WHERE slug = ?",
        [$slug]
    );
    return empty($existing);
}

/**
 * Get all organisations (for super admin)
 */
function getAllOrganisations($filters = []) {
    $params = [];
    $sql = "SELECT o.*, 
                   COUNT(DISTINCT om.user_id) as team_member_count,
                   COUNT(DISTINCT p.id) as candidate_count
            FROM organisations o
            LEFT JOIN organisation_members om ON o.id = om.organisation_id AND om.is_active = 1
            LEFT JOIN profiles p ON o.id = p.organisation_id AND p.account_type = 'candidate'
            WHERE 1=1";

    if (!empty($filters['search'])) {
        $sql .= " AND (o.name LIKE ? OR o.slug LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($filters['subscription_status'])) {
        $sql .= " AND o.subscription_status = ?";
        $params[] = $filters['subscription_status'];
    }

    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];

        if (!empty($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
    }

    return db()->fetchAll($sql, $params);
}

/**
 * Get all users across all organisations (for super admin)
 */
function getAllUsers($filters = []) {
    $params = [];
    $sql = "SELECT p.*, 
                   COALESCE(om_org.name, o.name) as organisation_name, 
                   COALESCE(om_org.id, o.id) as organisation_id,
                   om.role as organisation_role
            FROM profiles p
            LEFT JOIN organisations o ON p.organisation_id = o.id
            LEFT JOIN organisation_members om ON p.id = om.user_id AND om.is_active = 1
            LEFT JOIN organisations om_org ON om.organisation_id = om_org.id
            WHERE 1=1";

    if (!empty($filters['account_type'])) {
        $sql .= " AND p.account_type = ?";
        $params[] = $filters['account_type'];
    }

    if (!empty($filters['organisation_id'])) {
        $sql .= " AND (p.organisation_id = ? OR om.organisation_id = ?)";
        $params[] = $filters['organisation_id'];
        $params[] = $filters['organisation_id'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.full_name LIKE ? OR p.email LIKE ? OR p.username LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (isset($filters['is_super_admin'])) {
        $sql .= " AND p.is_super_admin = ?";
        $params[] = $filters['is_super_admin'] ? 1 : 0;
    }

    $sql .= " ORDER BY p.created_at DESC";

    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];

        if (!empty($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
    }

    return db()->fetchAll($sql, $params);
}

/**
 * Get system-wide activity log (for super admin)
 */
function getSystemActivityLog($limit = 50, $offset = 0, $filters = []) {
    $params = [];
    $sql = "SELECT al.*,
                   actor.full_name as actor_name, actor.email as actor_email,
                   target.full_name as target_name, target.email as target_email,
                   o.name as organisation_name
            FROM activity_log al
            LEFT JOIN profiles actor ON al.user_id = actor.id
            LEFT JOIN profiles target ON al.target_user_id = target.id
            LEFT JOIN organisations o ON al.organisation_id = o.id
            WHERE 1=1";

    if (!empty($filters['organisation_id'])) {
        $sql .= " AND al.organisation_id = ?";
        $params[] = $filters['organisation_id'];
    }

    if (!empty($filters['user_id'])) {
        $sql .= " AND al.user_id = ?";
        $params[] = $filters['user_id'];
    }

    if (!empty($filters['action'])) {
        $sql .= " AND al.action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
    }

    $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;

    return db()->fetchAll($sql, $params);
}

/**
 * Get system statistics (for super admin dashboard)
 */
function getSystemStatistics() {
    $stats = [];

    // Total organisations
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM organisations");
    $stats['total_organisations'] = $result ? (int)$result['count'] : 0;

    // Active organisations
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM organisations WHERE subscription_status = 'active'");
    $stats['active_organisations'] = $result ? (int)$result['count'] : 0;

    // Total users
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM profiles");
    $stats['total_users'] = $result ? (int)$result['count'] : 0;

    // Individual users
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM profiles WHERE account_type = 'individual'");
    $stats['individual_users'] = $result ? (int)$result['count'] : 0;

    // Candidates
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM profiles WHERE account_type = 'candidate'");
    $stats['candidates'] = $result ? (int)$result['count'] : 0;

    // Organisation members (recruiters/admins)
    $result = db()->fetchOne("SELECT COUNT(DISTINCT user_id) as count FROM organisation_members WHERE is_active = 1");
    $stats['organisation_members'] = $result ? (int)$result['count'] : 0;

    // Super admins
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM profiles WHERE is_super_admin = 1");
    $stats['super_admins'] = $result ? (int)$result['count'] : 0;

    // Total subscriptions
    $result = db()->fetchOne("SELECT COUNT(*) as count FROM organisations WHERE subscription_status = 'active'");
    $stats['active_subscriptions'] = $result ? (int)$result['count'] : 0;

    return $stats;
}

/**
 * Create a limit increase request
 */
function createLimitIncreaseRequest($organisationId, $requestType, $requestedLimit, $reason = null) {
    require_once __DIR__ . '/utils.php';
    
    $org = getOrganisationById($organisationId);
    if (!$org) {
        return ['success' => false, 'error' => 'Organisation not found.'];
    }
    
    // Validate request type
    if (!in_array($requestType, ['candidates', 'team_members'])) {
        return ['success' => false, 'error' => 'Invalid request type.'];
    }
    
    // Get current limit
    $currentLimit = $requestType === 'candidates' ? $org['max_candidates'] : $org['max_team_members'];
    
    // Validate requested limit
    if ($requestedLimit <= $currentLimit) {
        return ['success' => false, 'error' => 'Requested limit must be greater than current limit.'];
    }
    
    // Check for existing pending request
    $existing = db()->fetchOne(
        "SELECT id FROM limit_increase_requests 
         WHERE organisation_id = ? AND request_type = ? AND status = 'pending'",
        [$organisationId, $requestType]
    );
    
    if ($existing) {
        return ['success' => false, 'error' => 'You already have a pending request for this limit type.'];
    }
    
    try {
        $requestId = generateUuid();
        
        db()->insert('limit_increase_requests', [
            'id' => $requestId,
            'organisation_id' => $organisationId,
            'requested_by' => getUserId(),
            'request_type' => $requestType,
            'current_limit' => $currentLimit,
            'requested_limit' => $requestedLimit,
            'reason' => $reason,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        logActivity('limit_increase.requested', null, [
            'request_id' => $requestId,
            'request_type' => $requestType,
            'current_limit' => $currentLimit,
            'requested_limit' => $requestedLimit
        ], $organisationId);
        
        return ['success' => true, 'request_id' => $requestId];
    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to create request. Please try again.'];
    }
}

/**
 * Get limit increase requests for an organisation
 */
function getOrganisationLimitRequests($organisationId, $status = null) {
    $params = [$organisationId];
    $sql = "SELECT lir.*, 
                   requester.full_name as requester_name, requester.email as requester_email,
                   reviewer.full_name as reviewer_name
            FROM limit_increase_requests lir
            LEFT JOIN profiles requester ON lir.requested_by = requester.id
            LEFT JOIN profiles reviewer ON lir.reviewed_by = reviewer.id
            WHERE lir.organisation_id = ?";
    
    if ($status) {
        $sql .= " AND lir.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY lir.created_at DESC";
    
    return db()->fetchAll($sql, $params);
}

/**
 * Get all pending limit increase requests (for super admin)
 */
function getAllPendingLimitRequests() {
    return db()->fetchAll(
        "SELECT lir.*,
                o.name as organisation_name, o.slug as organisation_slug,
                requester.full_name as requester_name, requester.email as requester_email
         FROM limit_increase_requests lir
         JOIN organisations o ON lir.organisation_id = o.id
         LEFT JOIN profiles requester ON lir.requested_by = requester.id
         WHERE lir.status = 'pending'
         ORDER BY lir.created_at ASC"
    );
}

/**
 * Approve a limit increase request
 */
function approveLimitIncreaseRequest($requestId, $reviewNotes = null) {
    require_once __DIR__ . '/utils.php';
    
    $request = db()->fetchOne(
        "SELECT * FROM limit_increase_requests WHERE id = ?",
        [$requestId]
    );
    
    if (!$request) {
        return ['success' => false, 'error' => 'Request not found.'];
    }
    
    if ($request['status'] !== 'pending') {
        return ['success' => false, 'error' => 'This request has already been processed.'];
    }
    
    try {
        $db = db();
        $db->beginTransaction();
        
        // Update the organisation limit
        $updateField = $request['request_type'] === 'candidates' ? 'max_candidates' : 'max_team_members';
        $db->update('organisations', [
            $updateField => $request['requested_limit'],
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$request['organisation_id']]);
        
        // Update the request
        $db->update('limit_increase_requests', [
            'status' => 'approved',
            'reviewed_by' => getUserId(),
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $reviewNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$requestId]);
        
        $db->commit();
        
        logActivity('limit_increase.approved', $request['requested_by'], [
            'request_id' => $requestId,
            'request_type' => $request['request_type'],
            'new_limit' => $request['requested_limit']
        ], $request['organisation_id']);
        
        return ['success' => true];
    } catch (Exception $e) {
        $db->rollback();
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to approve request. Please try again.'];
    }
}

/**
 * Deny a limit increase request
 */
function denyLimitIncreaseRequest($requestId, $reviewNotes = null) {
    require_once __DIR__ . '/utils.php';
    
    $request = db()->fetchOne(
        "SELECT * FROM limit_increase_requests WHERE id = ?",
        [$requestId]
    );
    
    if (!$request) {
        return ['success' => false, 'error' => 'Request not found.'];
    }
    
    if ($request['status'] !== 'pending') {
        return ['success' => false, 'error' => 'This request has already been processed.'];
    }
    
    try {
        db()->update('limit_increase_requests', [
            'status' => 'denied',
            'reviewed_by' => getUserId(),
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $reviewNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$requestId]);
        
        logActivity('limit_increase.denied', $request['requested_by'], [
            'request_id' => $requestId,
            'request_type' => $request['request_type']
        ], $request['organisation_id']);
        
        return ['success' => true];
    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to deny request. Please try again.'];
    }
}
