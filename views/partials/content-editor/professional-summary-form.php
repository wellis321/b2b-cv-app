<?php
/**
 * Professional Summary Form Partial
 * When variant_id is in GET, loads from cv_variant_professional_summary and cv_variant_professional_summary_strengths.
 */

$variantId = $_GET['variant_id'] ?? null;
$summary = null;
$strengths = [];
$isVariantContext = false;

if ($variantId) {
    $variant = db()->fetchOne("SELECT id FROM cv_variants WHERE id = ? AND user_id = ?", [$variantId, $userId]);
    if ($variant) {
        $isVariantContext = true;
        $summary = db()->fetchOne("SELECT * FROM cv_variant_professional_summary WHERE cv_variant_id = ?", [$variantId]);
        if ($summary) {
            $strengths = db()->fetchAll("SELECT * FROM cv_variant_professional_summary_strengths WHERE professional_summary_id = ? ORDER BY sort_order ASC", [$summary['id']]);
        }
    }
}

if (!$isVariantContext) {
    $summary = db()->fetchOne("SELECT * FROM professional_summary WHERE profile_id = ?", [$userId]);
    if ($summary) {
        $strengths = db()->fetchAll("SELECT * FROM professional_summary_strengths WHERE professional_summary_id = ? ORDER BY sort_order ASC", [$summary['id']]);
    }
}

$canAddStrength = planCanAddEntry($subscriptionContext, 'summary_strengths', $userId, count($strengths));
$summaryWordLimit = planWordLimit($subscriptionContext, 'summary_description');

// Check if editing a strength
$editingStrengthId = $_GET['edit'] ?? null;
$editingStrength = null;
if ($editingStrengthId && $summary) {
    if ($variantId) {
        $editingStrength = db()->fetchOne("SELECT * FROM cv_variant_professional_summary_strengths WHERE id = ? AND professional_summary_id = ?", [$editingStrengthId, $summary['id']]);
    } else {
        $editingStrength = db()->fetchOne("SELECT * FROM professional_summary_strengths WHERE id = ? AND professional_summary_id = ?", [$editingStrengthId, $summary['id']]);
    }
}
?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Professional Summary</h1>
    
    <!-- Description Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Professional Summary Description</h2>
            <button type="button" onclick="assessSection('professional-summary')" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Assess This Section
            </button>
        </div>
        <form method="POST" data-section-form data-form-type="save">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="section_id" value="professional-summary">
            
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <?php if ($summaryWordLimit): ?>
                    <p class="text-xs text-gray-500 mb-2">Free plan summaries are limited to <?php echo $summaryWordLimit; ?> words.</p>
                <?php endif; ?>
                <p class="text-xs text-gray-500 mb-1">Use the toolbar for formatting: bold, italic, headers, lists, and links</p>
                <textarea id="description" name="description" rows="6" maxlength="5000" data-markdown class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"><?php echo e($summary['description'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500">
                Save Summary
            </button>
        </form>
    </div>
    
    <!-- Strengths Form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Strengths</h2>
        
        <?php if ($editingStrength): ?>
            <!-- Edit Strength Form -->
            <form method="POST" class="mb-4" data-section-form data-form-type="update_strength">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="update_strength">
                <input type="hidden" name="section_id" value="professional-summary">
                <input type="hidden" name="id" value="<?php echo e($editingStrength['id']); ?>">
                
                <div class="flex gap-2">
                    <input type="text" name="strength" value="<?php echo e($editingStrength['strength']); ?>" placeholder="Edit strength" required maxlength="255" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md">
                        Save
                    </button>
                    <button type="button" data-action="cancel" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        <?php else: ?>
            <!-- Add Strength Form -->
            <form method="POST" class="mb-4" data-section-form data-form-type="add_strength">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="add_strength">
                <input type="hidden" name="section_id" value="professional-summary">
                
                <div class="flex gap-2">
                    <input type="text" name="strength" placeholder="Add a strength" required maxlength="255" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" <?php echo !$canAddStrength ? 'disabled' : ''; ?> class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md <?php echo !$canAddStrength ? 'opacity-60 cursor-not-allowed' : ''; ?>">
                        Add
                    </button>
                </div>
            </form>
            
            <?php if (!$canAddStrength): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'summary_strengths'); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div id="professional-summary-entries-list">
            <?php if (empty($strengths)): ?>
                <p class="text-gray-500">No strengths added yet.</p>
            <?php else: ?>
                <ul class="space-y-2">
                    <?php foreach ($strengths as $strength): ?>
                        <li class="flex justify-between items-center bg-gray-50 p-3 rounded">
                            <span><?php echo e($strength['strength']); ?></span>
                            <div class="flex gap-2">
                                <button type="button" data-action="edit" data-entry-id="<?php echo e($strength['id']); ?>" class="px-3 py-1.5 bg-green-50 text-green-700 text-sm font-medium rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">
                                    Edit
                                </button>
                                <button type="button" data-action="delete" data-entry-id="<?php echo e($strength['id']); ?>" data-entry-type="professional-summary" class="px-3 py-1.5 bg-red-50 text-red-700 text-sm font-medium rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">
                                    Delete
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
