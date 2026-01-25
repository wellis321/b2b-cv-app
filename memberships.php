<?php
require_once __DIR__ . '/php/helpers.php';
requireAuth();
$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'memberships';

$memberships = db()->fetchAll("SELECT * FROM professional_memberships WHERE profile_id = ? ORDER BY start_date DESC", [$userId]);
$subscriptionContext = getUserSubscriptionContext($userId);
$canAddMembership = planCanAddEntry($subscriptionContext, 'memberships', $userId, count($memberships));

$editingId = get('edit');
$editingMembership = null;

if ($editingId) {
    $editingMembership = db()->fetchOne(
        "SELECT * FROM professional_memberships WHERE id = ? AND profile_id = ?",
        [$editingId, $userId]
    );

    if (!$editingMembership) {
        setFlash('error', 'Membership not found.');
        redirect('/memberships.php');
    }
}

if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/memberships.php');
    }
    $action = post('action');

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'memberships', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'memberships'));
            redirect('/subscription.php');
        }

        $startDate = post('start_date', '') ?: null;
        $endDate = post('end_date', '') ?: null;
        $organisation = sanitizeInput(post('organisation', ''));
        $role = sanitizeInput(post('role', ''));

        // Validate organisation
        if (empty($organisation)) {
            setFlash('error', 'Organisation is required');
            redirect('/memberships.php');
        }

        // Check for XSS
        if (checkForXss($organisation)) {
            setFlash('error', 'Invalid content in organisation name');
            redirect('/memberships.php');
        }

        if (!empty($role) && checkForXss($role)) {
            setFlash('error', 'Invalid content in role');
            redirect('/memberships.php');
        }

        // Length validation
        if (strlen($organisation) > 255) {
            setFlash('error', 'Organisation name must be 255 characters or less');
            redirect('/memberships.php');
        }

        if (!empty($role) && strlen($role) > 255) {
            setFlash('error', 'Role must be 255 characters or less');
            redirect('/memberships.php');
        }

        $data = [
            'id' => generateUuid(),
            'profile_id' => $userId,
            'organisation' => $organisation,
            'role' => !empty($role) ? $role : null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->insert('professional_memberships', $data);
            setFlash('success', 'Membership added successfully');
        } catch (Exception $e) {
            error_log("Membership creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add membership. Please try again.');
        }
        redirect('/memberships.php');
    } elseif ($action === 'update') {
        $id = post('id');
        $startDate = post('start_date', '') ?: null;
        $endDate = post('end_date', '') ?: null;
        $organisation = sanitizeInput(post('organisation', ''));
        $role = sanitizeInput(post('role', ''));

        // Validate organisation
        if (empty($organisation)) {
            setFlash('error', 'Organization is required');
            redirect('/memberships.php?edit=' . urlencode($id));
        }

        // Check for XSS
        if (checkForXss($organisation)) {
            setFlash('error', 'Invalid content in organisation name');
            redirect('/memberships.php?edit=' . urlencode($id));
        }

        if (!empty($role) && checkForXss($role)) {
            setFlash('error', 'Invalid content in role');
            redirect('/memberships.php?edit=' . urlencode($id));
        }

        // Length validation
        if (strlen($organisation) > 255) {
            setFlash('error', 'Organisation name must be 255 characters or less');
            redirect('/memberships.php?edit=' . urlencode($id));
        }

        if (!empty($role) && strlen($role) > 255) {
            setFlash('error', 'Role must be 255 characters or less');
            redirect('/memberships.php?edit=' . urlencode($id));
        }

        $data = [
            'organisation' => $organisation,
            'role' => !empty($role) ? $role : null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->update('professional_memberships', $data, 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Membership updated successfully');
        } catch (Exception $e) {
            error_log("Membership update error: " . $e->getMessage());
            setFlash('error', 'Failed to update membership. Please try again.');
            redirect('/memberships.php?edit=' . urlencode($id));
        }

        redirect('/memberships.php');
    } elseif ($action === 'delete') {
        $id = post('id');
        try {
            db()->delete('professional_memberships', 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Membership deleted successfully');
        } catch (Exception $e) {
            error_log("Membership deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to delete membership. Please try again.');
        }
        redirect('/memberships.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Professional Memberships | Simple CV Builder',
        'metaDescription' => 'Add and maintain your professional membership history for your CV.',
        'canonicalUrl' => APP_URL . '/memberships.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Professional Memberships</h1>
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4"><p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4"><p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p></div>
        <?php endif; ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingMembership ? 'Edit Membership' : 'Add Membership'; ?>
                <?php if ($editingMembership): ?>
                    <a href="/memberships.php" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <?php endif; ?>
            </h2>
            <?php if (!$editingMembership && !$canAddMembership): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'memberships'); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingMembership ? 'update' : 'create'; ?>">
                <?php if ($editingMembership): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingMembership['id']); ?>">
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div><label for="organisation" class="block text-sm font-medium text-gray-700">Organisation *</label>
                        <input type="text" id="organisation" name="organisation" value="<?php echo $editingMembership ? e($editingMembership['organisation']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <input type="text" id="role" name="role" value="<?php echo $editingMembership ? e($editingMembership['role']) : ''; ?>" maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $editingMembership && !empty($editingMembership['start_date']) ? e(date('Y-m-d', strtotime($editingMembership['start_date']))) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $editingMembership && !empty($editingMembership['end_date']) ? e(date('Y-m-d', strtotime($editingMembership['end_date']))) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                </div>
                <div class="mt-6"><button type="submit" <?php echo (!$editingMembership && !$canAddMembership) ? 'disabled' : ''; ?> class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 <?php echo (!$editingMembership && !$canAddMembership) ? 'opacity-60 cursor-not-allowed' : ''; ?>"><?php echo $editingMembership ? 'Update Membership' : 'Add Membership'; ?></button></div>
            </form>
        </div>
        <?php if (empty($memberships)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">No memberships added yet.</div>
        <?php else: ?>
            <?php foreach ($memberships as $membership): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-4">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo e($membership['organisation']); ?></h3>
                            <?php if ($membership['role']): ?><p class="text-gray-700"><?php echo e($membership['role']); ?></p><?php endif; ?>
                            <?php if (!empty($membership['start_date']) || !empty($membership['end_date'])): ?>
                                <p class="text-sm text-gray-500">
                                    <?php echo !empty($membership['start_date']) ? date('M Y', strtotime($membership['start_date'])) : 'Unknown start'; ?>
                                    -
                                    <?php echo !empty($membership['end_date']) ? date('M Y', strtotime($membership['end_date'])) : 'Present'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <a href="/memberships.php?edit=<?php echo e($membership['id']); ?>" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition-colors">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($membership['id']); ?>">
                                <button type="submit" onclick="return confirm('Delete this membership?');" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-red-600 hover:bg-red-100 hover:text-red-800 transition-colors">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php partial('footer'); ?>
</body>
</html>
