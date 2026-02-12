<?php
/**
 * Edit User (Super Admin)
 * Allow super admins to edit user roles, super admin status, and organisation memberships
 */

require_once __DIR__ . '/../../php/helpers.php';

// Require super admin access
requireSuperAdmin();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

$userId = sanitizeInput(get('id') ?? '');

if (empty($userId)) {
    setFlash('error', 'User not specified.');
    redirect('/admin/users.php');
}

// Get user details
$userData = db()->fetchOne(
    "SELECT p.*, 
            COALESCE(om_org.name, o.name) as organisation_name, 
            COALESCE(om_org.id, o.id) as organisation_id,
            om.role as organisation_role,
            om.id as membership_id
     FROM profiles p
     LEFT JOIN organisations o ON p.organisation_id = o.id
     LEFT JOIN organisation_members om ON p.id = om.user_id AND om.is_active = 1
     LEFT JOIN organisations om_org ON om.organisation_id = om_org.id
     WHERE p.id = ?",
    [$userId]
);

if (!$userData) {
    setFlash('error', 'User not found.');
    redirect('/admin/users.php');
}

// Get all organisations for assignment
$allOrganisations = getAllOrganisations();

// Get subscription context for display
$subscriptionContext = getUserSubscriptionContext($userId);
$expiryInfo = formatSubscriptionExpiry($subscriptionContext);
$plans = getSubscriptionPlansConfig();

// Handle form submission
if (isPost()) {
    if (!verifyCsrfToken(post(CSRF_TOKEN_NAME))) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect('/admin/users/edit.php?id=' . $userId);
    }

    $updateData = [];
    $updates = [];

    // Super admin status
    $isSuperAdmin = post('is_super_admin') === '1';
    if ($isSuperAdmin != (!empty($userData['is_super_admin']))) {
        $updateData['is_super_admin'] = $isSuperAdmin ? 1 : 0;
        $updates[] = 'Super admin status: ' . ($isSuperAdmin ? 'Enabled' : 'Disabled');
    }

    // Subscription plan and expiration
    $newPlan = sanitizeInput(post('plan') ?? '');
    $newSubscriptionStatus = sanitizeInput(post('subscription_status') ?? '');
    $newExpiryDate = sanitizeInput(post('subscription_current_period_end') ?? '');
    
    if ($newPlan !== '' && $newPlan !== ($userData['plan'] ?? '')) {
        $updateData['plan'] = $newPlan;
        $updates[] = 'Plan changed to: ' . (getSubscriptionPlanConfig($newPlan)['label'] ?? $newPlan);
    }
    
    if ($newSubscriptionStatus !== '' && $newSubscriptionStatus !== ($userData['subscription_status'] ?? '')) {
        $updateData['subscription_status'] = $newSubscriptionStatus;
        $updates[] = 'Subscription status changed to: ' . ucfirst($newSubscriptionStatus);
    }
    
    if ($newExpiryDate !== '') {
        // Validate date format
        $expiryTimestamp = strtotime($newExpiryDate);
        if ($expiryTimestamp !== false) {
            $formattedExpiry = date('Y-m-d H:i:s', $expiryTimestamp);
            if ($formattedExpiry !== ($userData['subscription_current_period_end'] ?? '')) {
                $updateData['subscription_current_period_end'] = $formattedExpiry;
                $updates[] = 'Expiration date changed to: ' . date('j M Y', $expiryTimestamp);
            }
        }
    } elseif (post('clear_expiry') === '1') {
        // Clear expiration date
        if (!empty($userData['subscription_current_period_end'])) {
            $updateData['subscription_current_period_end'] = null;
            $updates[] = 'Expiration date cleared';
        }
    }
    
    // Organisation membership and role
    $newOrgId = sanitizeInput(post('organisation_id') ?? '');
    $newRole = sanitizeInput(post('organisation_role') ?? '');
    
    // If organisation is being changed
    if ($newOrgId !== ($userData['organisation_id'] ?? '')) {
        // Remove from old organisation if exists
        if (!empty($userData['membership_id'])) {
            db()->update('organisation_members',
                ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$userData['membership_id']]
            );
            $updates[] = 'Removed from organisation: ' . ($userData['organisation_name'] ?? 'N/A');
        }
        
        // Add to new organisation if specified
        if (!empty($newOrgId) && !empty($newRole)) {
            // Check if membership already exists (inactive)
            $existingMembership = db()->fetchOne(
                "SELECT id FROM organisation_members WHERE user_id = ? AND organisation_id = ?",
                [$userId, $newOrgId]
            );
            
            if ($existingMembership) {
                // Reactivate and update role
                db()->update('organisation_members',
                    [
                        'role' => $newRole,
                        'is_active' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'id = ?',
                    [$existingMembership['id']]
                );
            } else {
                // Create new membership
                $org = getOrganisationById($newOrgId);
                db()->insert('organisation_members', [
                    'id' => generateUuid(),
                    'organisation_id' => $newOrgId,
                    'user_id' => $userId,
                    'role' => $newRole,
                    'is_active' => 1,
                    'joined_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $org = getOrganisationById($newOrgId);
            $updates[] = 'Added to organisation: ' . ($org['name'] ?? 'N/A') . ' as ' . ucfirst($newRole);
        }
    } elseif (!empty($userData['membership_id']) && $newRole !== ($userData['organisation_role'] ?? '')) {
        // Just updating role
        db()->update('organisation_members',
            ['role' => $newRole, 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$userData['membership_id']]
        );
        $updates[] = 'Role updated to: ' . ucfirst($newRole);
    }

    // Update profile if needed
    if (!empty($updateData)) {
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        db()->update('profiles', $updateData, 'id = ?', [$userId]);
    }

    if (!empty($updates)) {
        logActivity('admin.user.updated', null, [
            'user_id' => $userId,
            'updates' => $updates
        ], null);
        setFlash('success', 'User updated successfully.');
    } else {
        setFlash('success', 'No changes made.');
    }

    redirect('/admin/users.php');
}

// Reload user data after potential updates
$userData = db()->fetchOne(
    "SELECT p.*, 
            COALESCE(om_org.name, o.name) as organisation_name, 
            COALESCE(om_org.id, o.id) as organisation_id,
            om.role as organisation_role,
            om.id as membership_id
     FROM profiles p
     LEFT JOIN organisations o ON p.organisation_id = o.id
     LEFT JOIN organisation_members om ON p.id = om.user_id AND om.is_active = 1
     LEFT JOIN organisations om_org ON om.organisation_id = om_org.id
     WHERE p.id = ?",
    [$userId]
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Edit User | Super Admin',
        'metaDescription' => 'Edit user roles and permissions',
        'canonicalUrl' => APP_URL . '/admin/users/edit.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('admin/header'); ?>

    <main id="main-content" class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-6">
                <a href="/admin/users.php" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                    ← Back to Users
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="mt-1 text-sm text-gray-500">Manage user roles and permissions</p>
            </div>

            <!-- User Info Display -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">User Information</h2>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($userData['full_name'] ?? 'N/A'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($userData['email']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Username</dt>
                        <dd class="mt-1 text-sm text-gray-900">@<?php echo e($userData['username']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $userData['account_type'] === 'candidate' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($userData['account_type'] ?? 'individual'); ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current Plan</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $subscriptionContext['plan'] === 'lifetime' ? 'bg-purple-100 text-purple-800' : ($subscriptionContext['plan'] === 'free' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800'); ?>">
                                <?php echo e(subscriptionPlanLabel($subscriptionContext)); ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Subscription Expires</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php if ($expiryInfo['days_remaining'] !== null): ?>
                                <span class="<?php echo $expiryInfo['status_color'] === 'red' ? 'text-red-600 font-semibold' : ($expiryInfo['status_color'] === 'yellow' ? 'text-yellow-600 font-medium' : ''); ?>">
                                    <?php echo e($expiryInfo['formatted_date']); ?> (<?php echo e($expiryInfo['status_text']); ?>)
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500"><?php echo e($expiryInfo['formatted_date']); ?></span>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Edit Form -->
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">

                    <!-- Super Admin Status -->
                    <div>
                        <label class="block text-base font-semibold text-gray-900 mb-3">
                            <input type="checkbox" name="is_super_admin" value="1" 
                                   <?php echo !empty($userData['is_super_admin']) ? 'checked' : ''; ?>
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                            <span class="ml-2">Super Admin</span>
                        </label>
                        <p class="mt-2 text-sm text-gray-600 font-medium">
                            Grant this user super admin privileges (full system access)
                        </p>
                    </div>

                    <!-- Organisation Assignment -->
                    <div>
                        <label for="organisation_id" class="block text-base font-semibold text-gray-900 mb-3">
                            Organisation
                        </label>
                        <select name="organisation_id" id="organisation_id" 
                                class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                onchange="toggleRoleField()">
                            <option value="">No Organisation</option>
                            <?php foreach ($allOrganisations as $org): ?>
                                <option value="<?php echo e($org['id']); ?>" 
                                        <?php echo ($userData['organisation_id'] ?? '') === $org['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($org['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-2 text-sm text-gray-600 font-medium">
                            Assign this user to an organisation (optional)
                        </p>
                    </div>

                    <!-- Organisation Role -->
                    <div id="role-field" style="display: <?php echo !empty($userData['organisation_id']) ? 'block' : 'none'; ?>;">
                        <label for="organisation_role" class="block text-base font-semibold text-gray-900 mb-3">
                            Organisation Role
                        </label>
                        <select name="organisation_role" id="organisation_role" 
                                class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                            <option value="">Select Role</option>
                            <option value="owner" <?php echo ($userData['organisation_role'] ?? '') === 'owner' ? 'selected' : ''; ?>>
                                Owner - Full control of organisation
                            </option>
                            <option value="admin" <?php echo ($userData['organisation_role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                Admin - Manage team and candidates
                            </option>
                            <option value="recruiter" <?php echo ($userData['organisation_role'] ?? '') === 'recruiter' ? 'selected' : ''; ?>>
                                Recruiter - Manage assigned candidates
                            </option>
                            <option value="viewer" <?php echo ($userData['organisation_role'] ?? '') === 'viewer' ? 'selected' : ''; ?>>
                                Viewer - Read-only access
                            </option>
                        </select>
                        <p class="mt-2 text-sm text-gray-600 font-medium">
                            Required when assigning to an organisation
                        </p>
                    </div>

                    <!-- Subscription Management -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Management</h3>
                        
                        <!-- Plan Selection -->
                        <div class="mb-4">
                            <label for="plan" class="block text-base font-semibold text-gray-900 mb-3">
                                Subscription Plan
                            </label>
                            <select name="plan" id="plan" 
                                    class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <?php foreach ($plans as $planId => $planConfig): ?>
                                    <option value="<?php echo e($planId); ?>" 
                                            <?php echo ($userData['plan'] ?? 'free') === $planId ? 'selected' : ''; ?>>
                                        <?php echo e($planConfig['label']); ?> - <?php echo e($planConfig['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-2 text-sm text-gray-600 font-medium">
                                Change the user's subscription plan
                            </p>
                        </div>

                        <!-- Subscription Status -->
                        <div class="mb-4">
                            <label for="subscription_status" class="block text-base font-semibold text-gray-900 mb-3">
                                Subscription Status
                            </label>
                            <select name="subscription_status" id="subscription_status" 
                                    class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <option value="">Not set</option>
                                <option value="active" <?php echo ($userData['subscription_status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="inactive" <?php echo ($userData['subscription_status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                                <option value="cancelled" <?php echo ($userData['subscription_status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>
                                    Cancelled
                                </option>
                                <option value="past_due" <?php echo ($userData['subscription_status'] ?? '') === 'past_due' ? 'selected' : ''; ?>>
                                    Past Due
                                </option>
                            </select>
                            <p class="mt-2 text-sm text-gray-600 font-medium">
                                Current status: <strong><?php echo e(ucfirst($userData['subscription_status'] ?? 'Not set')); ?></strong>
                            </p>
                        </div>

                        <!-- Expiration Date -->
                        <div class="mb-4">
                            <label for="subscription_current_period_end" class="block text-base font-semibold text-gray-900 mb-3">
                                Subscription Expiration Date
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="datetime-local" 
                                       name="subscription_current_period_end" 
                                       id="subscription_current_period_end"
                                       value="<?php echo !empty($userData['subscription_current_period_end']) ? date('Y-m-d\TH:i', strtotime($userData['subscription_current_period_end'])) : ''; ?>"
                                       class="block flex-1 rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <label class="flex items-center text-sm text-gray-600">
                                    <input type="checkbox" name="clear_expiry" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 mr-2">
                                    Clear expiration
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 font-medium">
                                <?php if (!empty($userData['subscription_current_period_end'])): ?>
                                    Current expiration: <strong><?php echo date('j M Y, H:i', strtotime($userData['subscription_current_period_end'])); ?></strong>
                                <?php else: ?>
                                    No expiration date currently set
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg text-base font-bold shadow-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Save Changes
                        </button>
                        <a href="/admin/users.php" 
                           class="px-6 py-3 border-2 border-gray-400 rounded-lg text-base font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function toggleRoleField() {
            const orgSelect = document.getElementById('organisation_id');
            const roleField = document.getElementById('role-field');
            const roleSelect = document.getElementById('organisation_role');
            
            if (orgSelect.value) {
                roleField.style.display = 'block';
                roleSelect.required = true;
            } else {
                roleField.style.display = 'none';
                roleSelect.required = false;
                roleSelect.value = '';
            }
        }
    </script>

    <?php partial('footer'); ?>
</body>
</html>


