<?php
require_once __DIR__ . '/php/helpers.php';
requireAuth();
$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'certifications';

$certifications = db()->fetchAll("SELECT * FROM certifications WHERE profile_id = ? ORDER BY date_obtained DESC", [$userId]);
$subscriptionContext = getUserSubscriptionContext($userId);
$canAddCertification = planCanAddEntry($subscriptionContext, 'certifications', $userId, count($certifications));

$editingId = get('edit');
$editingCertification = null;

if ($editingId) {
    $editingCertification = db()->fetchOne(
        "SELECT * FROM certifications WHERE id = ? AND profile_id = ?",
        [$editingId, $userId]
    );

    if (!$editingCertification) {
        setFlash('error', 'Certification not found.');
        redirect('/certifications.php');
    }
}

if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/certifications.php');
    }
    $action = post('action');

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'certifications', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'certifications'));
            redirect('/subscription.php');
        }

        $dateObtained = trim(post('date_obtained', ''));
        $expiryDate = post('expiry_date', '') ?: null;

        $data = [
            'id' => generateUuid(),
            'profile_id' => $userId,
            'name' => sanitizeInput(post('name', '')),
            'issuer' => sanitizeInput(post('issuer', '')),
            'date_obtained' => $dateObtained ?: null,
            'expiry_date' => $expiryDate,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if (empty($data['name']) || empty($data['issuer'])) {
            setFlash('error', 'Name and issuer are required');
        } else {
            try {
                db()->insert('certifications', $data);
                setFlash('success', 'Certification added successfully');
            } catch (Exception $e) {
                setFlash('error', 'Failed to add: ' . $e->getMessage());
            }
        }
        redirect('/certifications.php');
    } elseif ($action === 'update') {
        $id = post('id');
        $dateObtained = trim(post('date_obtained', ''));
        $expiryDate = post('expiry_date', '') ?: null;

        $data = [
            'name' => sanitizeInput(post('name', '')),
            'issuer' => sanitizeInput(post('issuer', '')),
            'date_obtained' => $dateObtained ?: null,
            'expiry_date' => $expiryDate,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['name']) || empty($data['issuer'])) {
            setFlash('error', 'Name and issuer are required');
            redirect('/certifications.php?edit=' . urlencode($id));
        }

        try {
            db()->update('certifications', $data, 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Certification updated successfully');
        } catch (Exception $e) {
            setFlash('error', 'Failed to update: ' . $e->getMessage());
            redirect('/certifications.php?edit=' . urlencode($id));
        }
        redirect('/certifications.php');
    } elseif ($action === 'delete') {
        $id = post('id');
        try {
            db()->delete('certifications', 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Certification deleted successfully');
        } catch (Exception $e) {
            setFlash('error', 'Failed to delete: ' . $e->getMessage());
        }
        redirect('/certifications.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Certifications | Simple CV Builder',
        'metaDescription' => 'Track and update your professional certifications for your CV profile.',
        'canonicalUrl' => APP_URL . '/certifications.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Certifications</h1>
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4"><p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4"><p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p></div>
        <?php endif; ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingCertification ? 'Edit Certification' : 'Add Certification'; ?>
                <?php if ($editingCertification): ?>
                    <a href="/certifications.php" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <?php endif; ?>
            </h2>
            <?php if (!$editingCertification && !$canAddCertification): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'certifications'); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingCertification ? 'update' : 'create'; ?>">
                <?php if ($editingCertification): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingCertification['id']); ?>">
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div><label for="name" class="block text-sm font-medium text-gray-700">Certification Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo $editingCertification ? e($editingCertification['name']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="issuer" class="block text-sm font-medium text-gray-700">Issuing Organisation *</label>
                        <input type="text" id="issuer" name="issuer" value="<?php echo $editingCertification ? e($editingCertification['issuer']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="date_obtained" class="block text-sm font-medium text-gray-700">Date Obtained</label>
                        <input type="date" id="date_obtained" name="date_obtained" value="<?php echo $editingCertification && !empty($editingCertification['date_obtained']) ? e(date('Y-m-d', strtotime($editingCertification['date_obtained']))) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" value="<?php echo $editingCertification && !empty($editingCertification['expiry_date']) ? e(date('Y-m-d', strtotime($editingCertification['expiry_date']))) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                </div>
                <div class="mt-6"><button type="submit" <?php echo (!$editingCertification && !$canAddCertification) ? 'disabled' : ''; ?> class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 <?php echo (!$editingCertification && !$canAddCertification) ? 'opacity-60 cursor-not-allowed' : ''; ?>"><?php echo $editingCertification ? 'Update Certification' : 'Add Certification'; ?></button></div>
            </form>
        </div>
        <?php if (empty($certifications)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">No certifications added yet.</div>
        <?php else: ?>
            <?php foreach ($certifications as $cert): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-4">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo e($cert['name']); ?></h3>
                            <p class="text-gray-700"><?php echo e($cert['issuer']); ?></p>
                            <?php if (!empty($cert['date_obtained'])): ?>
                                <p class="text-sm text-gray-500">Obtained: <?php echo date('M Y', strtotime($cert['date_obtained'])); ?></p>
                            <?php endif; ?>
                            <?php if ($cert['expiry_date']): ?><p class="text-sm text-gray-500">Expires: <?php echo date('M Y', strtotime($cert['expiry_date'])); ?></p><?php endif; ?>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <a href="/certifications.php?edit=<?php echo e($cert['id']); ?>" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition-colors">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($cert['id']); ?>">
                                <button type="submit" onclick="return confirm('Delete this certification?');" class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-red-600 hover:bg-red-100 hover:text-red-800 transition-colors">Delete</button>
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
