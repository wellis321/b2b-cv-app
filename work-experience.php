<?php
/**
 * Work Experience page - manage work experience entries
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'work-experience';

// Check if we're editing an existing entry
$editingId = get('edit', null);
$editingExperience = null;
if ($editingId) {
    $editingExperience = db()->fetchOne(
        "SELECT * FROM work_experience WHERE id = ? AND profile_id = ?",
        [$editingId, $userId]
    );
    if (!$editingExperience) {
        $editingId = null; // Invalid ID, don't edit
    } else {
        // Load responsibilities for editing experience
        $categories = db()->fetchAll(
            "SELECT * FROM responsibility_categories WHERE work_experience_id = ? ORDER BY sort_order ASC",
            [$editingExperience['id']]
        );

        foreach ($categories as &$category) {
            $category['items'] = db()->fetchAll(
                "SELECT * FROM responsibility_items WHERE category_id = ? ORDER BY sort_order ASC",
                [$category['id']]
            );
        }

        $editingExperience['responsibility_categories'] = $categories;
        unset($category);
    }
}

// Get all work experience entries
$workExperiences = db()->fetchAll(
    "SELECT * FROM work_experience WHERE profile_id = ? ORDER BY sort_order ASC, start_date DESC",
    [$userId]
);
$subscriptionContext = getUserSubscriptionContext($userId);
$existingWorkCount = count($workExperiences);
$canAddWorkExperience = planCanAddEntry($subscriptionContext, 'work_experience', $userId, $existingWorkCount);

// Load responsibilities for each work experience
foreach ($workExperiences as &$work) {
    $categories = db()->fetchAll(
        "SELECT * FROM responsibility_categories WHERE work_experience_id = ? ORDER BY sort_order ASC",
        [$work['id']]
    );

    foreach ($categories as &$category) {
        $category['items'] = db()->fetchAll(
            "SELECT * FROM responsibility_items WHERE category_id = ? ORDER BY sort_order ASC",
            [$category['id']]
        );
    }

    $work['responsibility_categories'] = $categories;
}
unset($work, $category);

// Handle form submission
if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect('/work-experience.php');
    }

    $action = post('action');

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'work_experience', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'work_experience'));
            redirect('/subscription.php');
        }

        $companyNameInput = trim(post('company_name', ''));
        $positionInput = trim(post('position', ''));
        $descriptionInput = trim(post('description', ''));

        // Validate required fields
        if (empty($companyNameInput) || empty($positionInput) || empty(post('start_date', ''))) {
            setFlash('error', 'Company name, position, and start date are required');
            redirect('/work-experience.php');
        }

        // Check for XSS
        if (checkForXss($companyNameInput)) {
            setFlash('error', 'Invalid content in company name');
            redirect('/work-experience.php');
        }

        if (checkForXss($positionInput)) {
            setFlash('error', 'Invalid content in position');
            redirect('/work-experience.php');
        }

        if (!empty($descriptionInput) && checkForXss($descriptionInput)) {
            setFlash('error', 'Invalid content in description');
            redirect('/work-experience.php');
        }

        // Length validation
        if (strlen($companyNameInput) > 255) {
            setFlash('error', 'Company name must be 255 characters or less');
            redirect('/work-experience.php');
        }

        if (strlen($positionInput) > 255) {
            setFlash('error', 'Position must be 255 characters or less');
            redirect('/work-experience.php');
        }

        // Sanitize inputs
        $companyNameInput = strip_tags($companyNameInput);
        $positionInput = strip_tags($positionInput);
        $descriptionInput = !empty($descriptionInput) ? strip_tags($descriptionInput) : null;

        if ($descriptionInput && planWordLimitExceeded($subscriptionContext, 'work_description', $descriptionInput)) {
            setFlash('error', getPlanWordLimitMessage($subscriptionContext, 'work_description'));
            redirect('/work-experience.php');
        }

        $data = [
            'id' => generateUuid(),
            'profile_id' => $userId,
            'company_name' => $companyNameInput,
            'position' => $positionInput,
            'start_date' => post('start_date', ''),
            'end_date' => post('end_date', '') ?: null,
            'description' => $descriptionInput,
            'sort_order' => (int)post('sort_order', 0),
            'hide_date' => (int)post('hide_date', 0),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $newId = $data['id'];
            db()->insert('work_experience', $data);
            setFlash('success', 'Work experience added successfully. You can now add responsibilities below.');
            // Redirect to edit mode so user can add responsibilities
            redirect('/work-experience.php?edit=' . $newId);
        } catch (Exception $e) {
            error_log("Work experience creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add work experience. Please try again.');
            redirect('/work-experience.php');
        }
    } elseif ($action === 'update') {
        $id = post('id');

        $companyNameInput = trim(post('company_name', ''));
        $positionInput = trim(post('position', ''));
        $descriptionInput = trim(post('description', ''));

        // Validate required fields
        if (empty($companyNameInput) || empty($positionInput) || empty(post('start_date', ''))) {
            setFlash('error', 'Company name, position, and start date are required');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        // Check for XSS
        if (checkForXss($companyNameInput)) {
            setFlash('error', 'Invalid content in company name');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        if (checkForXss($positionInput)) {
            setFlash('error', 'Invalid content in position');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        if (!empty($descriptionInput) && checkForXss($descriptionInput)) {
            setFlash('error', 'Invalid content in description');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        // Length validation
        if (strlen($companyNameInput) > 255) {
            setFlash('error', 'Company name must be 255 characters or less');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        if (strlen($positionInput) > 255) {
            setFlash('error', 'Position must be 255 characters or less');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        // Sanitize inputs
        $companyNameInput = strip_tags($companyNameInput);
        $positionInput = strip_tags($positionInput);
        $descriptionInput = !empty($descriptionInput) ? strip_tags($descriptionInput) : null;

        if ($descriptionInput && planWordLimitExceeded($subscriptionContext, 'work_description', $descriptionInput)) {
            setFlash('error', getPlanWordLimitMessage($subscriptionContext, 'work_description'));
            redirect('/work-experience.php?edit=' . urlencode($id));
        }

        $data = [
            'company_name' => $companyNameInput,
            'position' => $positionInput,
            'start_date' => post('start_date', ''),
            'end_date' => post('end_date', '') ?: null,
            'description' => $descriptionInput,
            'sort_order' => (int)post('sort_order', 0),
            'hide_date' => (int)post('hide_date', 0),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->update('work_experience', $data, 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Work experience updated successfully');
            redirect('/work-experience.php');
        } catch (Exception $e) {
            error_log("Work experience update error: " . $e->getMessage());
            setFlash('error', 'Failed to update work experience. Please try again.');
            redirect('/work-experience.php?edit=' . urlencode($id));
        }
    } elseif ($action === 'delete') {
        $id = post('id');
        try {
            db()->delete('work_experience', 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Work experience deleted successfully');
            redirect('/work-experience.php');
        } catch (Exception $e) {
            error_log("Work experience deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to delete work experience. Please try again.');
            redirect('/work-experience.php');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Work Experience | Simple CV Builder',
        'metaDescription' => 'Manage your work experience entries, responsibilities, and achievements.',
        'canonicalUrl' => APP_URL . '/work-experience.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Work Experience</h1>

        <!-- Messages -->
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

        <!-- Add/Edit Work Experience -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingExperience ? 'Edit Work Experience' : 'Add New Work Experience'; ?>
                <?php if ($editingExperience): ?>
                    <a href="/work-experience.php" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <?php endif; ?>
            </h2>
            <?php if (!$editingExperience && !$canAddWorkExperience): ?>
                <div class="rounded-md bg-blue-50 border border-blue-200 p-4 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'work_experience'); ?>
                </div>
            <?php else: ?>
            <form method="POST" action="/work-experience.php" id="work-experience-form">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingExperience ? 'update' : 'create'; ?>">
                <?php if ($editingExperience): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingExperience['id']); ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" value="<?php echo $editingExperience ? e($editingExperience['company_name']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Position *</label>
                        <input type="text" id="position" name="position" value="<?php echo $editingExperience ? e($editingExperience['position']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $editingExperience ? date('Y-m-d', strtotime($editingExperience['start_date'])) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $editingExperience && $editingExperience['end_date'] ? date('Y-m-d', strtotime($editingExperience['end_date'])) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Leave blank if current position</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" maxlength="5000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo $editingExperience ? e($editingExperience['description']) : ''; ?></textarea>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="hide_date" value="1" <?php echo $editingExperience && $editingExperience['hide_date'] ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-sm text-gray-700">Hide date on CV</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" id="work-experience-save-btn" class="<?php echo $editingExperience ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white px-6 py-2 rounded-md font-medium">
                        <?php echo $editingExperience ? 'Update Work Experience' : 'Add Work Experience'; ?>
                    </button>
                    <?php if ($editingExperience): ?>
                        <a href="/work-experience.php" class="ml-4 text-gray-700 hover:text-gray-900">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>

            <!-- Responsibilities Editor (only when editing) -->
            <?php if ($editingExperience): ?>
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Key Responsibilities</h3>
                    <div id="responsibilities-editor-<?php echo e($editingExperience['id']); ?>"
                         data-work-experience-id="<?php echo e($editingExperience['id']); ?>">
                        <!-- Responsibilities will be loaded here via JavaScript -->
                    </div>
                    <!-- Bottom Save Button -->
                    <div class="mt-6 pt-6 border-t border-gray-200 bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <button type="button" onclick="document.getElementById('work-experience-form')?.submit();" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 font-medium shadow-sm">
                                    Update Work Experience
                                </button>
                                <a href="/work-experience.php" class="ml-4 text-gray-700 hover:text-gray-900">Cancel</a>
                            </div>
                            <p class="text-sm text-gray-600">Save after adding categories and responsibilities</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Existing Work Experiences -->
        <?php if (empty($workExperiences)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                <p>No work experience added yet. Add your first work experience above.</p>
            </div>
        <?php else: ?>
            <!-- Reorder Controls -->
            <div class="mb-4 flex justify-between items-center">
                <button type="button" id="toggle-reorder-btn" onclick="toggleReorderMode()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Reorder Experiences
                </button>
                <div id="reorder-info" class="hidden rounded-md bg-blue-50 p-4 text-blue-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm">
                            <strong>Reorder Mode:</strong> Drag and drop work experiences to change their order. The order will be saved automatically.
                        </p>
                        <button type="button" onclick="resetToDateOrder()" class="ml-4 rounded-md bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700">
                            Reset to Date Order
                        </button>
                    </div>
                </div>
            </div>

            <div id="work-experiences-list" class="space-y-6">
                <?php foreach ($workExperiences as $work): ?>
                    <div class="work-experience-item bg-white shadow rounded-lg p-6 mb-6"
                         data-id="<?php echo e($work['id']); ?>"
                         draggable="false">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-start gap-3">
                                <div class="drag-handle hidden cursor-move text-gray-400 hover:text-gray-600 mt-1">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900"><?php echo e(html_entity_decode($work['position'], ENT_QUOTES, 'UTF-8')); ?></h3>
                                    <p class="text-lg text-gray-700"><?php echo e(html_entity_decode($work['company_name'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    <?php if (!$work['hide_date']): ?>
                                        <p class="text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo date('M Y', strtotime($work['start_date'])); ?>
                                            - <?php echo $work['end_date'] ? date('M Y', strtotime($work['end_date'])) : 'Present'; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="/work-experience.php" class="inline">
                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e($work['id']); ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this work experience?');" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>

                        <?php if ($work['description']): ?>
                            <p class="text-gray-700 mb-4"><?php echo nl2br(e(html_entity_decode($work['description'], ENT_QUOTES, 'UTF-8'))); ?></p>
                        <?php endif; ?>

                        <!-- Responsibilities (Read-only) -->
                        <?php if (!empty($work['responsibility_categories'])): ?>
                            <?php $respContainerId = 'responsibilities-' . $work['id']; ?>
                            <div class="mt-4 border-t pt-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-md border-b border-gray-200 pb-2 font-medium text-gray-700">
                                        Key Responsibilities
                                    </h4>
                                    <button
                                        type="button"
                                        class="ml-4 inline-flex items-center rounded bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        data-toggle="responsibilities"
                                        data-target="<?php echo e($respContainerId); ?>"
                                        data-view-label="View Responsibilities"
                                        data-hide-label="Hide Responsibilities"
                                        aria-expanded="false"
                                    >
                                        <svg class="mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path class="icon-plus" fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                            <path class="icon-minus hidden" fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="toggle-label">View Responsibilities</span>
                                    </button>
                                </div>
                                <div id="<?php echo e($respContainerId); ?>" class="mt-3 hidden space-y-3">
                                    <?php foreach ($work['responsibility_categories'] as $category): ?>
                                        <div class="mb-2">
                                            <h5 class="font-medium text-gray-800"><?php echo e(html_entity_decode($category['name'], ENT_QUOTES, 'UTF-8')); ?></h5>
                                            <?php if (!empty($category['items'])): ?>
                                                <ul class="mt-1 list-disc space-y-1 pl-5">
                                                    <?php foreach ($category['items'] as $item): ?>
                                                        <li class="text-sm text-gray-700"><?php echo e(html_entity_decode($item['content'], ENT_QUOTES, 'UTF-8')); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="border-t pt-4">
                            <a href="/work-experience.php?edit=<?php echo e($work['id']); ?>" class="text-blue-600 hover:text-blue-800">Edit Work Experience</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
<?php endif; ?>
    </div>

    <?php partial('footer'); ?>

    <script>
        let isReordering = false;
        let draggedElement = null;
        let draggedId = null;
        const responsibilityButtons = [];

        function setResponsibilityVisibility(button, target, visible) {
            if (!button || !target) return;

            button.setAttribute('aria-expanded', visible ? 'true' : 'false');
            target.classList.toggle('hidden', !visible);

            const plusIcon = button.querySelector('.icon-plus');
            const minusIcon = button.querySelector('.icon-minus');
            if (plusIcon && minusIcon) {
                plusIcon.classList.toggle('hidden', visible);
                minusIcon.classList.toggle('hidden', !visible);
            }

            const label = button.querySelector('.toggle-label');
            const viewText = button.getAttribute('data-view-label') || 'View';
            const hideText = button.getAttribute('data-hide-label') || 'Hide';
            if (label) {
                label.textContent = visible ? hideText : viewText;
            }
        }

        function initResponsibilityToggles() {
            responsibilityButtons.length = 0;

            const buttons = document.querySelectorAll('[data-toggle="responsibilities"]');
            buttons.forEach((button) => {
                const targetId = button.getAttribute('data-target');
                const target = document.getElementById(targetId);
                if (!target) return;

                responsibilityButtons.push({ button, target });
                setResponsibilityVisibility(button, target, false);

                button.addEventListener('click', () => {
                    const expanded = button.getAttribute('aria-expanded') === 'true';
                    setResponsibilityVisibility(button, target, !expanded);
                });
            });
        }

        function collapseAllResponsibilities() {
            responsibilityButtons.forEach(({ button, target }) => {
                setResponsibilityVisibility(button, target, false);
            });
        }

        function toggleReorderMode() {
            isReordering = !isReordering;
            const items = document.querySelectorAll('.work-experience-item');
            const btn = document.getElementById('toggle-reorder-btn');
            const info = document.getElementById('reorder-info');
            const dragHandles = document.querySelectorAll('.drag-handle');

            if (isReordering) {
                collapseAllResponsibilities();
                btn.textContent = 'Done Reordering';
                btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                btn.classList.add('bg-green-600', 'hover:bg-green-700');
                info.classList.remove('hidden');

                items.forEach(item => {
                    item.setAttribute('draggable', 'true');
                    item.classList.add('cursor-move', 'border-2', 'border-blue-300');
                    item.classList.remove('border-gray-200');
                    item.querySelector('.drag-handle').classList.remove('hidden');

                    item.addEventListener('dragstart', handleDragStart);
                    item.addEventListener('dragover', handleDragOver);
                    item.addEventListener('dragleave', handleDragLeave);
                    item.addEventListener('drop', handleDrop);
                    item.addEventListener('dragend', handleDragEnd);
                });
            } else {
                btn.textContent = 'Reorder Experiences';
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                info.classList.add('hidden');

                items.forEach(item => {
                    item.setAttribute('draggable', 'false');
                    item.classList.remove('cursor-move', 'border-2', 'border-blue-300', 'border-blue-500', 'bg-blue-50');
                    item.classList.add('border-gray-200');
                    item.querySelector('.drag-handle').classList.add('hidden');

                    item.removeEventListener('dragstart', handleDragStart);
                    item.removeEventListener('dragover', handleDragOver);
                    item.removeEventListener('dragleave', handleDragLeave);
                    item.removeEventListener('drop', handleDrop);
                    item.removeEventListener('dragend', handleDragEnd);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            initResponsibilityToggles();

            const reorderableList = document.getElementById('work-experiences-list');
            if (reorderableList) {
                initDragAndDrop(reorderableList);
            }

            // Initialize responsibilities editor if present
            const editorWrapper = document.querySelector('[id^="responsibilities-editor-"]');
            if (editorWrapper) {
                loadResponsibilitiesEditor(editorWrapper.dataset.workExperienceId);
            }
        });

        function handleDragStart(e) {
            draggedElement = this;
            draggedId = this.getAttribute('data-id');
            this.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';

            if (this !== draggedElement && this.classList.contains('work-experience-item')) {
                this.classList.add('border-blue-500', 'bg-blue-50');
                this.classList.remove('border-blue-300');
            }
            return false;
        }

        function handleDragLeave(e) {
            if (this !== draggedElement && this.classList.contains('work-experience-item')) {
                this.classList.remove('border-blue-500', 'bg-blue-50');
                this.classList.add('border-blue-300');
            }
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            if (draggedElement !== this && this.classList.contains('work-experience-item')) {
                const targetId = this.getAttribute('data-id');
                const list = document.getElementById('work-experiences-list');

                // Get all items
                const items = Array.from(list.querySelectorAll('.work-experience-item'));
                const draggedIndex = items.indexOf(draggedElement);
                const targetIndex = items.indexOf(this);

                // Reorder in DOM
                if (draggedIndex < targetIndex) {
                    list.insertBefore(draggedElement, this.nextSibling);
                } else {
                    list.insertBefore(draggedElement, this);
                }

                // Save new order
                saveOrder();
            }

            this.classList.remove('border-blue-500', 'bg-blue-50');
            this.classList.add('border-blue-300');

            return false;
        }

        function handleDragEnd(e) {
            this.classList.remove('opacity-50');

            const items = document.querySelectorAll('.work-experience-item');
            items.forEach(item => {
                item.classList.remove('border-blue-500', 'bg-blue-50');
                item.classList.add('border-blue-300');
            });
        }

        function saveOrder() {
            const items = document.querySelectorAll('.work-experience-item');
            const orderedIds = Array.from(items).map(item => item.getAttribute('data-id'));

            const formData = new FormData();
            formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
            formData.append('action', 'reorder');
            formData.append('ordered_ids', JSON.stringify(orderedIds));

            fetch('/api/reorder-work-experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message briefly
                    const successMsg = document.createElement('div');
                    successMsg.className = 'mb-4 rounded-md bg-green-50 p-4 text-green-700';
                    successMsg.textContent = 'Order updated successfully!';
                    document.querySelector('h1').insertAdjacentElement('afterend', successMsg);
                    setTimeout(() => successMsg.remove(), 3000);
                } else {
                    console.error('Failed to save order:', data.error);
                    alert('Failed to save order. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error saving order:', error);
                alert('Failed to save order. Please try again.');
            });
        }

        function resetToDateOrder() {
            if (!confirm('Reset order to date-based sorting (newest first)?')) {
                return;
            }

            const formData = new FormData();
            formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
            formData.append('action', 'reset');

            fetch('/api/reorder-work-experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show new order
                    window.location.reload();
                } else {
                    console.error('Failed to reset order:', data.error);
                    alert('Failed to reset order. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error resetting order:', error);
                alert('Failed to reset order. Please try again.');
            });
        }

        // Responsibilities Editor
        <?php if ($editingExperience): ?>
        (function() {
            const workExperienceId = '<?php echo e($editingExperience['id']); ?>';
            const editorContainer = document.getElementById('responsibilities-editor-' + workExperienceId);
            if (!editorContainer) return;

            let categories = <?php echo json_encode($editingExperience['responsibility_categories'] ?? []); ?>;
            let expandedCategories = {};
            let editingCategoryId = null;
            let editingItemId = null;

            // Initialize expanded state
            categories.forEach(cat => {
                expandedCategories[cat.id] = true;
            });

            function render() {
                let html = '';

                if (categories.length === 0) {
                    html = '<p class="mb-4 text-gray-500 italic">No responsibilities added yet.</p>';
                } else {
                    html = '<div class="space-y-4">';
                    categories.forEach(category => {
                        const isExpanded = expandedCategories[category.id] !== false;
                        const isEditingCategory = editingCategoryId === category.id;

                        html += `<div class="overflow-hidden rounded-md border bg-white">
                            <div class="flex items-center justify-between bg-gray-50 p-3">
                                ${isEditingCategory ? `
                                    <input type="text" id="edit-category-${category.id}" value="${escapeHtml(category.name)}"
                                           class="mr-2 flex-1 rounded-md border-gray-300 px-2 py-1" />
                                    <div class="flex space-x-2">
                                        <button onclick="saveCategory('${category.id}')"
                                                class="rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700">
                                            Save
                                        </button>
                                        <button onclick="cancelEditCategory()"
                                                class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 hover:bg-gray-300">
                                            Cancel
                                        </button>
                                    </div>
                                ` : `
                                    <button onclick="toggleCategory('${category.id}')"
                                            class="flex flex-1 items-center text-left font-medium text-gray-800">
                                        <span class="mr-2 transform transition-transform duration-200 ${isExpanded ? 'rotate-90' : ''}">▶</span>
                                        ${escapeHtml(category.name)}
                                    </button>
                                    <div class="flex space-x-2">
                                        <button onclick="startEditCategory('${category.id}')"
                                                class="text-sm text-indigo-600 hover:text-indigo-800">
                                            Edit
                                        </button>
                                        <button onclick="deleteCategory('${category.id}')"
                                                class="text-sm text-red-600 hover:text-red-800">
                                            Delete
                                        </button>
                                    </div>
                                `}
                            </div>
                            ${isExpanded ? `
                                <div class="p-3">
                                    ${category.items && category.items.length > 0 ? `
                                        <ul class="list-disc space-y-2 pl-6">
                                            ${category.items.map(item => {
                                                const isEditingItem = editingItemId === item.id;
                                                return `
                                                    <li>
                                                        ${isEditingItem ? `
                                                            <div class="mt-1 flex items-center">
                                                                <input type="text" id="edit-item-${item.id}" value="${escapeHtml(item.content)}"
                                                                       class="mr-2 flex-1 rounded-md border-gray-300 px-2 py-1" />
                                                                <button onclick="saveItem('${item.id}')"
                                                                        class="mr-1 rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700">
                                                                    Save
                                                                </button>
                                                                <button onclick="cancelEditItem()"
                                                                        class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 hover:bg-gray-300">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        ` : `
                                                            <div class="group flex items-start">
                                                                <span class="flex-1">${escapeHtml(item.content)}</span>
                                                                <div class="ml-2 hidden space-x-2 group-hover:flex">
                                                                    <button onclick="startEditItem('${item.id}')"
                                                                            class="text-xs text-indigo-600 hover:text-indigo-800">
                                                                        Edit
                                                                    </button>
                                                                    <button onclick="deleteItem('${item.id}')"
                                                                            class="text-xs text-red-600 hover:text-red-800">
                                                                        Delete
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        `}
                                                    </li>
                                                `;
                                            }).join('')}
                                        </ul>
                                    ` : `
                                        <p class="text-gray-500 italic">No items in this category yet.</p>
                                    `}
                                    <div class="mt-4">
                                        <div class="flex items-center">
                                            <input type="text" id="new-item-${category.id}"
                                                   placeholder="Add a new responsibility item"
                                                   class="flex-1 rounded-md border-gray-300 px-2 py-1"
                                                   onkeypress="if(event.key==='Enter') addItem('${category.id}')" />
                                            <button onclick="addItem('${category.id}')"
                                                    class="ml-2 rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700">
                                                Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                        </div>`;
                    });
                    html += '</div>';
                }

                html += `
                    <div class="mt-6">
                        <h4 class="mb-2 text-sm font-medium text-gray-700">Add a new category</h4>
                        <div class="flex">
                            <input type="text" id="new-category-name"
                                   placeholder="e.g. Strategic Leadership, Project Management"
                                   class="flex-1 rounded-md rounded-r-none border-gray-300 px-2 py-1"
                                   onkeypress="if(event.key==='Enter') addCategory()" />
                            <button onclick="addCategory()"
                                    class="rounded-md rounded-l-none bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                Add Category
                            </button>
                        </div>
                    </div>
                `;

                editorContainer.innerHTML = html;
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function toggleCategory(categoryId) {
                expandedCategories[categoryId] = !expandedCategories[categoryId];
                render();
            }

            function startEditCategory(categoryId) {
                editingCategoryId = categoryId;
                editingItemId = null;
                render();
                setTimeout(() => {
                    const input = document.getElementById('edit-category-' + categoryId);
                    if (input) input.focus();
                }, 100);
            }

            function cancelEditCategory() {
                editingCategoryId = null;
                render();
            }

            function saveCategory(categoryId) {
                const input = document.getElementById('edit-category-' + categoryId);
                const name = input ? input.value.trim() : '';
                if (!name) return;

                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
                formData.append('action', 'update_category');
                formData.append('category_id', categoryId);
                formData.append('name', name);

                fetch('/api/responsibilities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadResponsibilities();
                    } else {
                        alert('Failed to update category: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update category. Please try again.');
                });
            }

            function deleteCategory(categoryId) {
                if (!confirm('Are you sure you want to delete this category and all its items?')) {
                    return;
                }

                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
                formData.append('action', 'delete_category');
                formData.append('category_id', categoryId);

                fetch('/api/responsibilities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadResponsibilities();
                    } else {
                        alert('Failed to delete category: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete category. Please try again.');
                });
            }

            function addCategory() {
                const input = document.getElementById('new-category-name');
                const name = input ? input.value.trim() : '';
                if (!name) return;

                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
                formData.append('action', 'add_category');
                formData.append('work_experience_id', workExperienceId);
                formData.append('name', name);

                fetch('/api/responsibilities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        loadResponsibilities();
                        setTimeout(() => {
                            const newItemInput = document.querySelector(`#new-item-${data.id}`);
                            if (newItemInput) newItemInput.focus();
                        }, 100);
                    } else {
                        alert('Failed to add category: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to add category. Please try again.');
                });
            }

            function addItem(categoryId) {
                const input = document.getElementById('new-item-' + categoryId);
                const content = input ? input.value.trim() : '';
                if (!content) return;

                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
                formData.append('action', 'add_item');
                formData.append('category_id', categoryId);
                formData.append('content', content);

                fetch('/api/responsibilities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        loadResponsibilities();
                        setTimeout(() => {
                            const newItemInput = document.getElementById('new-item-' + categoryId);
                            if (newItemInput) newItemInput.focus();
                        }, 100);
                    } else {
                        alert('Failed to add item: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to add item. Please try again.');
                });
            }

            function startEditItem(itemId) {
                editingItemId = itemId;
                editingCategoryId = null;
                render();
                setTimeout(() => {
                    const input = document.getElementById('edit-item-' + itemId);
                    if (input) input.focus();
                }, 100);
            }

            function cancelEditItem() {
                editingItemId = null;
                render();
            }

            function saveItem(itemId) {
                const input = document.getElementById('edit-item-' + itemId);
                const content = input ? input.value.trim() : '';
                if (!content) return;

                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
                formData.append('action', 'update_item');
                formData.append('item_id', itemId);
                formData.append('content', content);

                fetch('/api/responsibilities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadResponsibilities();
                    } else {
                        alert('Failed to update item: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update item. Please try again.');
                });
            }

            function deleteItem(itemId) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    return;
                }

                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo csrfToken(); ?>');
                formData.append('action', 'delete_item');
                formData.append('item_id', itemId);

                fetch('/api/responsibilities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadResponsibilities();
                    } else {
                        alert('Failed to delete item: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete item. Please try again.');
                });
            }

            function loadResponsibilities() {
                // Reload responsibilities from server
                fetch(`/api/responsibilities.php?work_experience_id=${workExperienceId}&action=get`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.categories) {
                            categories = data.categories;
                            categories.forEach(cat => {
                                if (expandedCategories[cat.id] === undefined) {
                                    expandedCategories[cat.id] = true;
                                }
                            });
                            render();
                        } else {
                            // Fallback: reload page
                            window.location.reload();
                        }
                    })
                    .catch(() => {
                        // Fallback: reload page
                        window.location.reload();
                    });
            }

            // Make functions globally available
            window.toggleCategory = toggleCategory;
            window.startEditCategory = startEditCategory;
            window.cancelEditCategory = cancelEditCategory;
            window.saveCategory = saveCategory;
            window.deleteCategory = deleteCategory;
            window.addCategory = addCategory;
            window.addItem = addItem;
            window.startEditItem = startEditItem;
            window.cancelEditItem = cancelEditItem;
            window.saveItem = saveItem;
            window.deleteItem = deleteItem;

            // Initial render
            render();
        })();
        <?php endif; ?>
    </script>
</body>
</html>
