<?php
/**
 * Guidance Panel Component
 * Displays contextual guidance and tips for the selected section
 */

if (!isset($guidance)) {
    return;
}
?>
<div class="bg-white border-l border-gray-200 h-full overflow-y-auto">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Suggestions</h2>
        
        <div class="mb-6">
            <h3 class="text-base font-semibold text-gray-800 mb-2"><?php echo e($guidance['title']); ?></h3>
            <p class="text-sm text-gray-600 leading-relaxed"><?php echo e($guidance['description']); ?></p>
        </div>

        <?php if (!empty($guidance['tips'])): ?>
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Tips</h4>
                <ul class="space-y-2">
                    <?php foreach ($guidance['tips'] as $tip): ?>
                        <li class="text-sm text-gray-600 flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo nl2br(e($tip)); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($guidance['examples'])): ?>
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Examples</h4>
                <div class="space-y-3">
                    <?php foreach ($guidance['examples'] as $example): ?>
                        <div class="bg-gray-50 p-3 rounded-md text-sm text-gray-700">
                            <?php echo nl2br(e($example)); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($guidance['common_mistakes'])): ?>
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Common Mistakes to Avoid</h4>
                <ul class="space-y-2">
                    <?php foreach ($guidance['common_mistakes'] as $mistake): ?>
                        <li class="text-sm text-gray-600 flex items-start">
                            <svg class="w-4 h-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo e($mistake); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- CV Preview -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">CV Preview</h4>
            <p class="text-sm text-gray-600 mb-3">See your changes after saving sections.</p>
            <button type="button" id="content-editor-update-preview" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                Update Preview
            </button>
            <div id="content-editor-cv-preview" class="mt-4 overflow-auto border border-gray-200 rounded-md bg-white min-h-[200px]" style="max-height: 400px;"></div>
        </div>
    </div>
</div>
