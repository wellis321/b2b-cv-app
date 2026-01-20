<?php
require_once __DIR__ . '/php/helpers.php';
requireAuth();
$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'education';

// Check if we're editing an existing entry
$editingId = get('edit', null);
$editingEducation = null;
if ($editingId) {
    $editingEducation = db()->fetchOne(
        "SELECT * FROM education WHERE id = ? AND profile_id = ?",
        [$editingId, $userId]
    );
    if (!$editingEducation) {
        $editingId = null; // Invalid ID, don't edit
    }
}

$educationEntries = db()->fetchAll("SELECT * FROM education WHERE profile_id = ? ORDER BY start_date DESC", [$userId]);
$subscriptionContext = getUserSubscriptionContext($userId);
$canAddEducation = planCanAddEntry($subscriptionContext, 'education', $userId, count($educationEntries));

if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/education.php');
    }
    $action = post('action');

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'education', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'education'));
            redirect('/subscription.php');
        }

        $institution = sanitizeInput(post('institution', ''));
        $degree = sanitizeInput(post('degree', ''));
        $fieldOfStudy = sanitizeInput(post('field_of_study', ''));

        // Validate required fields
        if (empty($institution) || empty($degree) || empty(post('start_date', ''))) {
            setFlash('error', 'Institution, degree, and start date are required');
            redirect('/education.php');
        }

        // Check for XSS
        if (checkForXss($institution)) {
            setFlash('error', 'Invalid content in institution name');
            redirect('/education.php');
        }

        if (checkForXss($degree)) {
            setFlash('error', 'Invalid content in degree');
            redirect('/education.php');
        }

        if (!empty($fieldOfStudy) && checkForXss($fieldOfStudy)) {
            setFlash('error', 'Invalid content in field of study');
            redirect('/education.php');
        }

        // Length validation
        if (strlen($institution) > 255) {
            setFlash('error', 'Institution name must be 255 characters or less');
            redirect('/education.php');
        }

        if (strlen($degree) > 255) {
            setFlash('error', 'Degree must be 255 characters or less');
            redirect('/education.php');
        }

        if (!empty($fieldOfStudy) && strlen($fieldOfStudy) > 255) {
            setFlash('error', 'Field of study must be 255 characters or less');
            redirect('/education.php');
        }

        $data = [
            'id' => generateUuid(),
            'profile_id' => $userId,
            'institution' => $institution,
            'degree' => $degree,
            'field_of_study' => !empty($fieldOfStudy) ? $fieldOfStudy : null,
            'start_date' => post('start_date', ''),
            'end_date' => post('end_date', '') ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->insert('education', $data);
            setFlash('success', 'Education added successfully');
        } catch (Exception $e) {
            error_log("Education creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add education. Please try again.');
        }
        redirect('/education.php');
    } elseif ($action === 'update') {
        $id = post('id');

        // Verify the education belongs to the user
        $existingEducation = db()->fetchOne(
            "SELECT id FROM education WHERE id = ? AND profile_id = ?",
            [$id, $userId]
        );

        if (!$existingEducation) {
            setFlash('error', 'Education entry not found or you do not have permission to edit it.');
            redirect('/education.php');
        }

        $institution = sanitizeInput(post('institution', ''));
        $degree = sanitizeInput(post('degree', ''));
        $fieldOfStudy = sanitizeInput(post('field_of_study', ''));

        // Validate required fields
        if (empty($institution) || empty($degree) || empty(post('start_date', ''))) {
            setFlash('error', 'Institution, degree, and start date are required');
            redirect('/education.php?edit=' . urlencode($id));
        }

        // Check for XSS
        if (checkForXss($institution)) {
            setFlash('error', 'Invalid content in institution name');
            redirect('/education.php?edit=' . urlencode($id));
        }

        if (checkForXss($degree)) {
            setFlash('error', 'Invalid content in degree');
            redirect('/education.php?edit=' . urlencode($id));
        }

        if (!empty($fieldOfStudy) && checkForXss($fieldOfStudy)) {
            setFlash('error', 'Invalid content in field of study');
            redirect('/education.php?edit=' . urlencode($id));
        }

        // Length validation
        if (strlen($institution) > 255) {
            setFlash('error', 'Institution name must be 255 characters or less');
            redirect('/education.php?edit=' . urlencode($id));
        }

        if (strlen($degree) > 255) {
            setFlash('error', 'Degree must be 255 characters or less');
            redirect('/education.php?edit=' . urlencode($id));
        }

        if (!empty($fieldOfStudy) && strlen($fieldOfStudy) > 255) {
            setFlash('error', 'Field of study must be 255 characters or less');
            redirect('/education.php?edit=' . urlencode($id));
        }

        $data = [
            'institution' => $institution,
            'degree' => $degree,
            'field_of_study' => !empty($fieldOfStudy) ? $fieldOfStudy : null,
            'start_date' => post('start_date', ''),
            'end_date' => post('end_date', '') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->update('education', $data, 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Education updated successfully');
            redirect('/education.php');
        } catch (Exception $e) {
            error_log("Education update error: " . $e->getMessage());
            setFlash('error', 'Failed to update education. Please try again.');
            redirect('/education.php?edit=' . urlencode($id));
        }
    } elseif ($action === 'delete') {
        $id = post('id');
        try {
            db()->delete('education', 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Education deleted successfully');
        } catch (Exception $e) {
            error_log("Education deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to delete education. Please try again.');
        }
        redirect('/education.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Education | Simple CV Builder',
        'metaDescription' => 'Manage your education history and qualifications.',
        'canonicalUrl' => APP_URL . '/education.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Education</h1>
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4"><p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4"><p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p></div>
        <?php endif; ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingEducation ? 'Edit Education' : 'Add New Education'; ?>
                <?php if ($editingEducation): ?>
                    <a href="/education.php" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <?php endif; ?>
            </h2>
            <?php if (!$editingEducation && !$canAddEducation): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'education'); ?>
                </div>
            <?php endif; ?>
            <?php if (!$editingEducation || $canAddEducation || $editingEducation): ?>
            <form method="POST" id="education-form">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingEducation ? 'update' : 'create'; ?>">
                <?php if ($editingEducation): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingEducation['id']); ?>">
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="institution" class="block text-sm font-medium text-gray-700">Institution *</label>
                        <input type="text" id="institution" name="institution" value="<?php echo $editingEducation ? e($editingEducation['institution']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="degree" class="block text-sm font-medium text-gray-700">Degree *</label>
                        <input type="text" id="degree" name="degree" value="<?php echo $editingEducation ? e($editingEducation['degree']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="field_of_study" class="block text-sm font-medium text-gray-700">Field of Study</label>
                        <input type="text" id="field_of_study" name="field_of_study" value="<?php echo $editingEducation ? e($editingEducation['field_of_study'] ?? '') : ''; ?>" maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $editingEducation ? date('Y-m-d', strtotime($editingEducation['start_date'])) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $editingEducation && $editingEducation['end_date'] ? date('Y-m-d', strtotime($editingEducation['end_date'])) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Leave blank if still studying</p>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" <?php echo !$editingEducation && !$canAddEducation ? 'disabled' : ''; ?> class="<?php echo $editingEducation ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white px-6 py-2 rounded-md font-medium <?php echo !$editingEducation && !$canAddEducation ? 'opacity-60 cursor-not-allowed' : ''; ?>">
                        <?php echo $editingEducation ? 'Update Education' : 'Add Education'; ?>
                    </button>
                    <?php if ($editingEducation): ?>
                        <a href="/education.php" class="ml-4 text-gray-700 hover:text-gray-900">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <?php if (empty($educationEntries)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">No education entries yet.</div>
        <?php else: ?>
            <?php foreach ($educationEntries as $edu): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900"><?php echo e($edu['degree']); ?></h3>
                            <p class="text-lg text-gray-700"><?php echo e($edu['institution']); ?></p>
                            <?php if ($edu['field_of_study']): ?>
                                <p class="text-gray-600"><?php echo e($edu['field_of_study']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-500"><?php echo date('M Y', strtotime($edu['start_date'])); ?> - <?php echo $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present'; ?></p>
                        </div>
                        <div class="flex gap-3">
                            <a href="/education.php?edit=<?php echo e($edu['id']); ?>" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($edu['id']); ?>">
                                <button type="submit" onclick="return confirm('Delete this education entry?');" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
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
