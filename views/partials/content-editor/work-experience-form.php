<?php
/**
 * Work Experience Form Partial
 * When variant_id is in GET, loads and lists data from the CV variant tables (cv_variant_work_experience, etc.).
 */

$editingId = $_GET['edit'] ?? null;
$variantId = $_GET['variant_id'] ?? null;
$editingExperience = null;
$workExperiences = [];
$isVariantContext = false;

if ($variantId) {
    $variant = db()->fetchOne("SELECT id FROM cv_variants WHERE id = ? AND user_id = ?", [$variantId, $userId]);
    if ($variant) {
        $isVariantContext = true;
        if ($editingId) {
            $editingExperience = db()->fetchOne(
                "SELECT * FROM cv_variant_work_experience WHERE cv_variant_id = ? AND (id = ? OR original_work_experience_id = ?)",
                [$variantId, $editingId, $editingId]
            );
            if ($editingExperience) {
                $categories = db()->fetchAll(
                    "SELECT * FROM cv_variant_responsibility_categories WHERE work_experience_id = ? ORDER BY sort_order ASC",
                    [$editingExperience['id']]
                );
                foreach ($categories as &$category) {
                    $category['items'] = db()->fetchAll(
                        "SELECT * FROM cv_variant_responsibility_items WHERE category_id = ? ORDER BY sort_order ASC",
                        [$category['id']]
                    );
                }
                $editingExperience['responsibility_categories'] = $categories;
            }
        }
        $workExperiences = db()->fetchAll(
            "SELECT * FROM cv_variant_work_experience WHERE cv_variant_id = ? ORDER BY sort_order ASC, start_date DESC",
            [$variantId]
        );
    }
}

if (!$isVariantContext) {
    if ($editingId) {
        $editingExperience = db()->fetchOne(
            "SELECT * FROM work_experience WHERE id = ? AND profile_id = ?",
            [$editingId, $userId]
        );
        if ($editingExperience) {
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
        }
    }
    $workExperiences = db()->fetchAll(
        "SELECT * FROM work_experience WHERE profile_id = ? ORDER BY sort_order ASC, start_date DESC",
        [$userId]
    );
}

$existingWorkCount = count($workExperiences);
$canAddWorkExperience = planCanAddEntry($subscriptionContext, 'work_experience', $userId, $existingWorkCount);
?>
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Work Experience</h1>
        <button type="button" onclick="assessSection('work-experience')" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Assess This Section
        </button>
    </div>
    
    <!-- Add/Edit Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">
            <?php echo $editingExperience ? 'Edit Work Experience' : 'Add New Work Experience'; ?>
        </h2>
        
        <?php if (!$editingExperience && !$canAddWorkExperience): ?>
            <div class="rounded-md bg-blue-50 border border-blue-200 p-4 text-sm text-blue-700">
                <?php echo getPlanLimitMessage($subscriptionContext, 'work_experience'); ?>
            </div>
        <?php else: ?>
        <form method="POST" data-section-form data-form-type="<?php echo $editingExperience ? 'update' : 'create'; ?>">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="action" value="<?php echo $editingExperience ? 'update' : 'create'; ?>">
            <input type="hidden" name="section_id" value="work-experience">
            <?php if ($editingExperience): ?>
                <input type="hidden" name="id" value="<?php echo e($editingExperience['id']); ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo $editingExperience ? e($editingExperience['company_name']) : ''; ?>" required maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                    <input type="text" id="position" name="position" value="<?php echo $editingExperience ? e($editingExperience['position']) : ''; ?>" required maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $editingExperience ? date('Y-m-d', strtotime($editingExperience['start_date'])) : ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $editingExperience && $editingExperience['end_date'] ? date('Y-m-d', strtotime($editingExperience['end_date'])) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Leave blank if current position</p>
                </div>
                
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <p class="text-xs text-gray-500 mb-1">Use the toolbar for formatting: bold, italic, headers, lists, and links</p>
                    <textarea id="description" name="description" rows="3" maxlength="5000" data-markdown class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"><?php echo $editingExperience ? e($editingExperience['description']) : ''; ?></textarea>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="hide_date" value="1" <?php echo $editingExperience && $editingExperience['hide_date'] ? 'checked' : ''; ?> class="mr-2">
                        <span class="text-sm text-gray-700">Hide date on CV</span>
                    </label>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md">
                    <?php echo $editingExperience ? 'Update Work Experience' : 'Add Work Experience'; ?>
                </button>
                <?php if ($editingExperience): ?>
                    <button type="button" data-action="cancel" class="ml-4 text-gray-700 hover:text-gray-900">Cancel</button>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Responsibilities Editor (only when editing) -->
        <?php if ($editingExperience): ?>
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Key Responsibilities</h3>
                <div id="responsibilities-editor-<?php echo e($editingExperience['id']); ?>"
                     data-work-experience-id="<?php echo e($editingExperience['id']); ?>">
                    <!-- Responsibilities will be loaded here via JavaScript -->
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2 text-sm text-gray-500">Loading responsibilities...</p>
                    </div>
                </div>
            </div>
            <!-- Responsibilities editor will be initialized by content-editor.js -->
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Existing Entries List -->
    <div id="work-experience-entries-list">
        <?php if (empty($workExperiences)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                <p>No work experience added yet.</p>
            </div>
        <?php else: ?>
            <?php
            $showReorder = !$isVariantContext && count($workExperiences) >= 2;
            if ($showReorder):
            ?>
            <!-- Reorder controls (main profile only, 2+ items) -->
            <div class="mb-4 flex flex-wrap justify-between items-center gap-3">
                <button type="button" id="toggle-reorder-btn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Reorder experiences
                </button>
                <div id="reorder-info" class="hidden rounded-md bg-blue-50 p-4 text-blue-700 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm">
                            <strong>Reorder mode:</strong> Drag and drop to change order. Order is saved automatically.
                        </p>
                        <button type="button" id="reset-reorder-btn" class="shrink-0 rounded-md bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700">
                            Reset to date order
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div id="work-experiences-list" class="space-y-4">
                <?php foreach ($workExperiences as $work): ?>
                    <div class="work-experience-item bg-white shadow rounded-lg p-6 <?php echo $showReorder ? 'border border-gray-200' : ''; ?>"
                         data-id="<?php echo e($work['id']); ?>"
                         <?php echo $showReorder ? 'draggable="false"' : ''; ?>>
                        <div class="flex justify-between items-start">
                            <div class="flex items-start gap-3">
                                <?php if ($showReorder): ?>
                                <div class="drag-handle hidden cursor-move text-gray-400 hover:text-gray-600 mt-1 shrink-0" aria-hidden="true">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900"><?php echo e($work['position']); ?></h3>
                                    <p class="text-lg text-gray-700"><?php echo e($work['company_name']); ?></p>
                                    <?php if (empty($work['hide_date'])): ?>
                                        <p class="text-sm text-gray-500">
                                            <?php echo date('M Y', strtotime($work['start_date'])); ?> -
                                            <?php echo $work['end_date'] ? date('M Y', strtotime($work['end_date'])) : 'Present'; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" data-action="edit" data-entry-id="<?php echo e($work['id']); ?>" class="px-3 py-1.5 bg-green-50 text-green-700 text-sm font-medium rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">Edit</button>
                                <button type="button" data-action="delete" data-entry-id="<?php echo e($work['id']); ?>" data-entry-type="work-experience" class="px-3 py-1.5 bg-red-50 text-red-700 text-sm font-medium rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
