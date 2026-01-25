<?php
require_once __DIR__ . '/php/helpers.php';

requireAuth();

$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'qualification-equivalence';

$editingId = get('edit');
$subscriptionContext = getUserSubscriptionContext($userId);

if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect('/qualification-equivalence.php');
    }

    $action = post('action');

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'qualification_equivalence', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'qualification_equivalence'));
            redirect('/subscription.php');
        }

        $level = trim(post('level', ''));
        $description = trim(post('description', ''));

        if ($level === '') {
            setFlash('error', 'Qualification level is required.');
            redirect('/qualification-equivalence.php');
        }

        // Check for XSS
        if (checkForXss($level)) {
            setFlash('error', 'Invalid content in qualification level');
            redirect('/qualification-equivalence.php');
        }

        if (!empty($description) && checkForXss($description)) {
            setFlash('error', 'Invalid content in description');
            redirect('/qualification-equivalence.php');
        }

        // Length validation
        if (strlen($level) > 255) {
            setFlash('error', 'Qualification level must be 255 characters or less');
            redirect('/qualification-equivalence.php');
        }

        $id = generateUuid();

        $data = [
            'id' => $id,
            'profile_id' => $userId,
            'level' => strip_tags($level),
            'description' => $description !== '' ? strip_tags($description) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            db()->insert('professional_qualification_equivalence', $data);
            setFlash('success', 'Qualification equivalence added successfully.');
            redirect('/qualification-equivalence.php?edit=' . $id);
        } catch (Exception $e) {
            error_log("Qualification equivalence creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add qualification. Please try again.');
            redirect('/qualification-equivalence.php');
        }
    } elseif ($action === 'update') {
        $id = post('id');
        $level = trim(post('level', ''));
        $description = trim(post('description', ''));

        if ($level === '') {
            setFlash('error', 'Qualification level is required.');
            redirect('/qualification-equivalence.php?edit=' . urlencode($id));
        }

        // Check for XSS
        if (checkForXss($level)) {
            setFlash('error', 'Invalid content in qualification level');
            redirect('/qualification-equivalence.php?edit=' . urlencode($id));
        }

        if (!empty($description) && checkForXss($description)) {
            setFlash('error', 'Invalid content in description');
            redirect('/qualification-equivalence.php?edit=' . urlencode($id));
        }

        // Length validation
        if (strlen($level) > 255) {
            setFlash('error', 'Qualification level must be 255 characters or less');
            redirect('/qualification-equivalence.php?edit=' . urlencode($id));
        }

        try {
            db()->update('professional_qualification_equivalence', [
                'level' => strip_tags($level),
                'description' => $description !== '' ? strip_tags($description) : null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND profile_id = ?', [$id, $userId]);

            setFlash('success', 'Qualification updated successfully.');
            redirect('/qualification-equivalence.php?edit=' . urlencode($id));
        } catch (Exception $e) {
            error_log("Qualification equivalence update error: " . $e->getMessage());
            setFlash('error', 'Failed to update qualification. Please try again.');
            redirect('/qualification-equivalence.php?edit=' . urlencode($id));
        }
    } elseif ($action === 'delete') {
        $id = post('id');

        try {
            db()->delete('professional_qualification_equivalence', 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Qualification deleted successfully.');
        } catch (Exception $e) {
            error_log("Qualification equivalence deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to delete qualification. Please try again.');
        }

        redirect('/qualification-equivalence.php');
    } elseif ($action === 'add_evidence') {
        $qualificationId = post('qualification_id');
        $content = trim(post('content', ''));

        if ($qualificationId === '') {
            setFlash('error', 'Invalid request.');
            redirect('/qualification-equivalence.php');
        }

        if ($content === '') {
            setFlash('error', 'Supporting evidence cannot be empty.');
            redirect('/qualification-equivalence.php?edit=' . urlencode($qualificationId));
        }

        // Ensure the qualification belongs to the current user
        $qualification = db()->fetchOne(
            "SELECT id FROM professional_qualification_equivalence WHERE id = ? AND profile_id = ?",
            [$qualificationId, $userId]
        );

        if (!$qualification) {
            setFlash('error', 'Qualification not found.');
            redirect('/qualification-equivalence.php');
        }

        $orderRow = db()->fetchOne(
            "SELECT MAX(sort_order) as max_order FROM supporting_evidence WHERE qualification_equivalence_id = ?",
            [$qualificationId]
        );

        $nextOrder = isset($orderRow['max_order']) && $orderRow['max_order'] !== null
            ? ((int)$orderRow['max_order'] + 1)
            : 0;

        // Check for XSS
        if (checkForXss($content)) {
            setFlash('error', 'Invalid content in supporting evidence');
            redirect('/qualification-equivalence.php?edit=' . urlencode($qualificationId));
        }

        try {
            db()->insert('supporting_evidence', [
                'id' => generateUuid(),
                'qualification_equivalence_id' => $qualificationId,
                'content' => strip_tags($content),
                'sort_order' => $nextOrder,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            setFlash('success', 'Supporting evidence added.');
        } catch (Exception $e) {
            error_log("Supporting evidence creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add evidence. Please try again.');
        }

        redirect('/qualification-equivalence.php?edit=' . urlencode($qualificationId));
    } elseif ($action === 'delete_evidence') {
        $evidenceId = post('evidence_id');
        $qualificationId = post('qualification_id');

        if ($evidenceId === '' || $qualificationId === '') {
            setFlash('error', 'Invalid request.');
            redirect('/qualification-equivalence.php');
        }

        try {
            db()->delete('supporting_evidence', 'id = ? AND qualification_equivalence_id = ?', [$evidenceId, $qualificationId]);
            setFlash('success', 'Supporting evidence removed.');
        } catch (Exception $e) {
            error_log("Supporting evidence deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to remove evidence. Please try again.');
        }

        redirect('/qualification-equivalence.php?edit=' . urlencode($qualificationId));
    }
}

// Fetch qualifications and evidence
$qualifications = db()->fetchAll(
    "SELECT * FROM professional_qualification_equivalence WHERE profile_id = ? ORDER BY created_at DESC",
    [$userId]
);

$ids = array_column($qualifications, 'id');
$evidenceByQualification = [];

if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $evidenceItems = db()->fetchAll(
        "SELECT * FROM supporting_evidence WHERE qualification_equivalence_id IN ($placeholders) ORDER BY sort_order ASC",
        $ids
    );

    foreach ($evidenceItems as $item) {
        $qualificationId = $item['qualification_equivalence_id'];
        if (!isset($evidenceByQualification[$qualificationId])) {
            $evidenceByQualification[$qualificationId] = [];
        }
        $evidenceByQualification[$qualificationId][] = $item;
    }
}

foreach ($qualifications as &$qualification) {
    $qualification['supporting_evidence_items'] = $evidenceByQualification[$qualification['id']] ?? [];
}
unset($qualification);
$canAddQualification = planCanAddEntry($subscriptionContext, 'qualification_equivalence', $userId, count($qualifications));

$editingQualification = null;
if ($editingId) {
    foreach ($qualifications as $qualification) {
        if ($qualification['id'] === $editingId) {
            $editingQualification = $qualification;
            break;
        }
    }

    if (!$editingQualification) {
        setFlash('error', 'Qualification not found.');
        redirect('/qualification-equivalence.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Professional Qualification Equivalence | Simple CV Builder',
        'metaDescription' => 'Manage your professional qualification equivalence entries and supporting evidence.',
        'canonicalUrl' => APP_URL . '/qualification-equivalence.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Professional Qualification Equivalence</h1>

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

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingQualification ? 'Edit Qualification Equivalence' : 'Add Qualification Equivalence'; ?>
                <?php if ($editingQualification): ?>
                    <a href="/qualification-equivalence.php" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <?php endif; ?>
            </h2>

            <?php if (!$editingQualification && !$canAddQualification): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'qualification_equivalence'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/qualification-equivalence.php" class="space-y-6">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingQualification ? 'update' : 'create'; ?>">
                <?php if ($editingQualification): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingQualification['id']); ?>">
                <?php endif; ?>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Qualification Level *</label>
                    <input type="text" id="level" name="level" required value="<?php echo $editingQualification ? e($editingQualification['level']) : ''; ?>" maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3" maxlength="5000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo $editingQualification ? e($editingQualification['description']) : ''; ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Explain how this qualification maps to local standards.</p>
                </div>

                <div>
                    <button type="submit" <?php echo (!$editingQualification && !$canAddQualification) ? 'disabled' : ''; ?> class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 <?php echo (!$editingQualification && !$canAddQualification) ? 'opacity-60 cursor-not-allowed' : ''; ?>">
                        <?php echo $editingQualification ? 'Update Qualification' : 'Add Qualification'; ?>
                    </button>
                </div>
            </form>

            <?php if ($editingQualification): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Supporting Evidence</h3>

                    <?php if (empty($editingQualification['supporting_evidence_items'])): ?>
                        <p class="text-sm text-gray-600 mb-4">No supporting evidence added yet.</p>
                    <?php else: ?>
                        <ul class="space-y-3 mb-6">
                            <?php foreach ($editingQualification['supporting_evidence_items'] as $item): ?>
                                <li class="flex items-start justify-between rounded-md border border-gray-200 bg-gray-50 p-3">
                                    <span class="text-sm text-gray-800 pr-4"><?php echo e($item['content']); ?></span>
                                    <form method="POST" action="/qualification-equivalence.php" onsubmit="return confirm('Remove this evidence item?');" class="inline">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                        <input type="hidden" name="action" value="delete_evidence">
                                        <input type="hidden" name="evidence_id" value="<?php echo e($item['id']); ?>">
                                        <input type="hidden" name="qualification_id" value="<?php echo e($editingQualification['id']); ?>">
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded text-sm font-medium text-red-600 hover:bg-red-100 hover:text-red-800 transition-colors">Delete</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <form method="POST" action="/qualification-equivalence.php" class="flex flex-col sm:flex-row gap-3">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="action" value="add_evidence">
                        <input type="hidden" name="qualification_id" value="<?php echo e($editingQualification['id']); ?>">
                        <input type="text" name="content" placeholder="Add supporting evidence" maxlength="5000" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add Evidence</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($qualifications)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-600">
                No professional qualification equivalence entries yet.
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($qualifications as $qualification): ?>
                    <?php $containerId = 'qualification-' . $qualification['id']; ?>
                    <div class="bg-white shadow rounded-lg p-6 <?php echo ($editingQualification && $editingQualification['id'] === $qualification['id']) ? 'border-2 border-blue-400' : 'border border-gray-200'; ?>">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo e($qualification['level']); ?></h3>
                                <?php if (!empty($qualification['description'])): ?>
                                    <p class="mt-2 text-sm text-gray-700"><?php echo nl2br(e($qualification['description'])); ?></p>
                                <?php endif; ?>
                                <?php $evidenceCount = count($qualification['supporting_evidence_items']); ?>
                                <p class="mt-2 text-xs text-gray-500">
                                    <?php echo $evidenceCount; ?> supporting evidence item<?php echo $evidenceCount === 1 ? '' : 's'; ?>
                                </p>
                            </div>
                            <div class="inline-flex items-center gap-2">
                                <a href="/qualification-equivalence.php?edit=<?php echo e($qualification['id']); ?>" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition-colors">Edit</a>
                                <form method="POST" action="/qualification-equivalence.php" onsubmit="return confirm('Delete this qualification?');" class="inline">
                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e($qualification['id']); ?>">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-red-600 hover:bg-red-100 hover:text-red-800 transition-colors">Delete</button>
                                </form>
                            </div>
                        </div>

                        <?php if (!empty($qualification['supporting_evidence_items'])): ?>
                            <div class="mt-4">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    data-toggle="qualification"
                                    data-target="<?php echo e($containerId); ?>"
                                    data-view-label="View Supporting Evidence"
                                    data-hide-label="Hide Supporting Evidence"
                                    aria-expanded="false"
                                >
                                    <svg class="mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path class="icon-plus" fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        <path class="icon-minus hidden" fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="toggle-label">View Supporting Evidence</span>
                                </button>

                                <div id="<?php echo e($containerId); ?>" class="mt-3 hidden rounded-md border border-gray-100 bg-gray-50 p-4">
                                    <ul class="list-disc space-y-2 pl-5 text-sm text-gray-700">
                                        <?php foreach ($qualification['supporting_evidence_items'] as $item): ?>
                                            <li><?php echo e($item['content']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php partial('footer'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('[data-toggle="qualification"]');
            buttons.forEach((button) => {
                const targetId = button.getAttribute('data-target');
                const target = document.getElementById(targetId);
                if (!target) return;

                button.setAttribute('aria-expanded', 'false');
                target.classList.add('hidden');

                button.addEventListener('click', () => {
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';
                    const nextState = !isExpanded;
                    button.setAttribute('aria-expanded', nextState ? 'true' : 'false');
                    target.classList.toggle('hidden', !nextState);

                    const plusIcon = button.querySelector('.icon-plus');
                    const minusIcon = button.querySelector('.icon-minus');
                    if (plusIcon && minusIcon) {
                        plusIcon.classList.toggle('hidden', nextState);
                        minusIcon.classList.toggle('hidden', !nextState);
                    }

                    const label = button.querySelector('.toggle-label');
                    const viewText = button.getAttribute('data-view-label') || 'View';
                    const hideText = button.getAttribute('data-hide-label') || 'Hide';
                    if (label) {
                        label.textContent = nextState ? hideText : viewText;
                    }
                });
            });
        });
    </script>
</body>
</html>
