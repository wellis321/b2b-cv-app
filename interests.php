<?php
require_once __DIR__ . '/php/helpers.php';
requireAuth();
$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'interests';

$interests = db()->fetchAll("SELECT * FROM interests WHERE profile_id = ? ORDER BY name ASC", [$userId]);
$subscriptionContext = getUserSubscriptionContext($userId);
$canAddInterest = planCanAddEntry($subscriptionContext, 'interests', $userId, count($interests));

$editingId = get('edit');
$editingInterest = null;

if ($editingId) {
    $editingInterest = db()->fetchOne(
        "SELECT * FROM interests WHERE id = ? AND profile_id = ?",
        [$editingId, $userId]
    );

    if (!$editingInterest) {
        setFlash('error', 'Interest not found.');
        redirect('/interests.php');
    }
}

if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/interests.php');
    }
    $action = post('action');

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'interests', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'interests'));
            redirect('/subscription.php');
        }

        $name = sanitizeInput(post('name', ''));
        $description = trim(post('description', ''));

        // Validate name
        if (empty($name)) {
            setFlash('error', 'Interest name is required');
            redirect('/interests.php');
        }

        // Check for XSS
        if (checkForXss($name)) {
            setFlash('error', 'Invalid content in interest name');
            redirect('/interests.php');
        }

        if (!empty($description) && checkForXss($description)) {
            setFlash('error', 'Invalid content in description');
            redirect('/interests.php');
        }

        // Length validation
        if (strlen($name) > 255) {
            setFlash('error', 'Interest name must be 255 characters or less');
            redirect('/interests.php');
        }

        $data = [
            'id' => generateUuid(),
            'profile_id' => $userId,
            'name' => $name,
            'description' => !empty($description) ? strip_tags($description) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->insert('interests', $data);
            setFlash('success', 'Interest added successfully');
        } catch (Exception $e) {
            error_log("Interest creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add interest. Please try again.');
        }
        redirect('/interests.php');
    } elseif ($action === 'update') {
        $id = post('id');
        $name = sanitizeInput(post('name', ''));
        $description = trim(post('description', ''));

        // Validate name
        if (empty($name)) {
            setFlash('error', 'Interest name is required');
            redirect('/interests.php?edit=' . urlencode($id));
        }

        // Check for XSS
        if (checkForXss($name)) {
            setFlash('error', 'Invalid content in interest name');
            redirect('/interests.php?edit=' . urlencode($id));
        }

        if (!empty($description) && checkForXss($description)) {
            setFlash('error', 'Invalid content in description');
            redirect('/interests.php?edit=' . urlencode($id));
        }

        // Length validation
        if (strlen($name) > 255) {
            setFlash('error', 'Interest name must be 255 characters or less');
            redirect('/interests.php?edit=' . urlencode($id));
        }

        $data = [
            'name' => $name,
            'description' => !empty($description) ? strip_tags($description) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->update('interests', $data, 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Interest updated successfully');
        } catch (Exception $e) {
            error_log("Interest update error: " . $e->getMessage());
            setFlash('error', 'Failed to update interest. Please try again.');
            redirect('/interests.php?edit=' . urlencode($id));
        }
        redirect('/interests.php');
    } elseif ($action === 'delete') {
        $id = post('id');
        try {
            db()->delete('interests', 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Interest deleted successfully');
        } catch (Exception $e) {
            error_log("Interest deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to delete interest. Please try again.');
        }
        redirect('/interests.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Interests & Activities | Simple CV Builder',
        'metaDescription' => 'Manage your interests and activities for your CV profile.',
        'canonicalUrl' => APP_URL . '/interests.php',
        'metaNoindex' => true,
    ]); ?>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>
    <main id="main-content" role="main">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Interests & Activities</h1>
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4"><p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4"><p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p></div>
        <?php endif; ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingInterest ? 'Edit Interest' : 'Add Interest'; ?>
                <?php if ($editingInterest): ?>
                    <a href="/interests.php" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <?php endif; ?>
            </h2>
            <?php if (!$editingInterest && !$canAddInterest): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'interests'); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingInterest ? 'update' : 'create'; ?>">
                <?php if ($editingInterest): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingInterest['id']); ?>">
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-6">
                    <div><label for="name" class="block text-sm font-medium text-gray-700">Interest Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo $editingInterest ? e($editingInterest['name']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="2" maxlength="5000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo $editingInterest ? e($editingInterest['description'] ?? '') : ''; ?></textarea></div>
                </div>
                <div class="mt-6"><button type="submit" <?php echo (!$editingInterest && !$canAddInterest) ? 'disabled' : ''; ?> class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 <?php echo (!$editingInterest && !$canAddInterest) ? 'opacity-60 cursor-not-allowed' : ''; ?>"><?php echo $editingInterest ? 'Update Interest' : 'Add Interest'; ?></button></div>
            </form>
        </div>
        <?php if (empty($interests)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">No interests added yet.</div>
        <?php else: ?>
            <?php foreach ($interests as $interest): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-4">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo e($interest['name']); ?></h3>
                            <?php if ($interest['description']): ?>
                                <p class="text-gray-700 mt-2"><?php echo e($interest['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <a href="/interests.php?edit=<?php echo e($interest['id']); ?>" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition-colors">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($interest['id']); ?>">
                                <button type="submit" onclick="return confirm('Delete this interest?');" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-red-600 hover:bg-red-100 hover:text-red-800 transition-colors">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </main>
    <?php partial('footer'); ?>
</body>
</html>
