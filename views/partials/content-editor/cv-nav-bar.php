<?php
/**
 * CV Navigation Bar Component
 * Displays CV-related actions: variants, templates, PDF export
 */

if (!isset($cvVariants) || !isset($masterVariantId)) {
    return;
}
$isPreviewPage = !empty($isPreviewPage);

// Content editor edits the master CV, but we can show variants for viewing
// Get recent variants (last 5)
$recentVariants = array_slice($cvVariants, 0, 5);
$masterVariant = null;
foreach ($cvVariants as $variant) {
    if ($variant['is_master']) {
        $masterVariant = $variant;
        break;
    }
}
?>
<div class="bg-white border-b border-gray-200 px-6 py-3">
    <div class="flex items-center justify-between gap-3 flex-wrap min-w-0">
        <!-- Left: CV Variant Selector -->
        <div class="flex items-center gap-3 flex-shrink-0">
            <div class="relative" style="overflow: visible;">
                <button id="cv-variant-dropdown-btn" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>My CVs</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="cv-variant-dropdown" class="hidden absolute left-0 mt-1 w-72 bg-white rounded-md shadow-lg border border-gray-200 z-50 max-h-96 overflow-y-auto">
                    <div class="py-2">
                        <!-- Current CV being edited -->
                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Editing</div>
                        <div class="px-4 py-2 text-sm text-gray-900 bg-blue-50 border-l-2 border-blue-600">
                            <div class="font-medium"><?php echo e($masterVariant['variant_name'] ?? 'Master CV'); ?></div>
                            <div class="text-xs text-gray-500 mt-1">This is your main CV</div>
                        </div>
                        
                        <?php if (!empty($recentVariants)): ?>
                            <div class="border-t border-gray-200 my-2"></div>
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Recent CVs</div>
                            <?php foreach ($recentVariants as $variant): ?>
                                <?php if (!$variant['is_master']): ?>
                                    <a href="/cv.php?variant_id=<?php echo e($variant['id']); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-l-2 border-transparent hover:border-blue-300">
                                        <div class="font-medium"><?php echo e($variant['variant_name']); ?></div>
                                        <?php if ($variant['job_title']): ?>
                                            <div class="text-xs text-gray-500 mt-0.5"><?php echo e($variant['job_title']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($variant['updated_at']): ?>
                                            <div class="text-xs text-gray-400 mt-1">Edited <?php echo date('M j, Y', strtotime($variant['updated_at'])); ?></div>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="border-t border-gray-200 my-2"></div>
                        <a href="/content-editor.php#cv-variants" class="block px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                <span>View All CVs</span>
                            </div>
                        </a>
                        <a href="/cv-variants/rewrite.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Create New CV with AI</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!$isPreviewPage): ?>
        <!-- Center: Layout (column visibility) - content editor only -->
        <div class="flex items-center gap-1 border border-gray-200 rounded-md p-1 bg-gray-50 flex-shrink-0" id="layout-presets" role="group" aria-label="Layout">
            <button type="button" class="layout-preset-btn rounded p-1.5 text-gray-600 hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset" data-layout="all" title="All three columns (section nav + content + guidance)">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="2" y="4" width="5" height="16" rx="1"/><rect x="9" y="4" width="5" height="16" rx="1"/><rect x="16" y="4" width="5" height="16" rx="1"/></svg>
            </button>
            <button type="button" class="layout-preset-btn rounded p-1.5 text-gray-600 hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset" data-layout="left-middle" title="Left + middle (section nav + content)">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="2" y="4" width="7" height="16" rx="1"/><rect x="11" y="4" width="7" height="16" rx="1"/><rect x="18" y="4" width="4" height="16" rx="1" opacity="0.25"/></svg>
            </button>
            <button type="button" class="layout-preset-btn rounded p-1.5 text-gray-600 hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset" data-layout="right-middle" title="Right + middle (content + guidance)">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="2" y="4" width="4" height="16" rx="1" opacity="0.25"/><rect x="8" y="4" width="7" height="16" rx="1"/><rect x="17" y="4" width="5" height="16" rx="1"/></svg>
            </button>
            <button type="button" class="layout-preset-btn rounded p-1.5 text-gray-600 hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset" data-layout="middle" title="Middle only (content only)">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="2" y="4" width="5" height="16" rx="1" opacity="0.25"/><rect x="9" y="4" width="5" height="16" rx="1"/><rect x="16" y="4" width="5" height="16" rx="1" opacity="0.25"/></svg>
            </button>
        </div>
        <?php endif; ?>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <!-- Jobs Link -->
            <a href="<?php echo $isPreviewPage ? '/content-editor.php#jobs' : '#jobs'; ?>" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span>Jobs</span>
            </a>
            
            <!-- Templates Link -->
            <a href="/preview-cv.php" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                <span>Templates</span>
            </a>
            
            <!-- Edit CV Link -->
            <a href="/content-editor.php" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit CV</span>
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle CV variant dropdown
        const dropdownBtn = document.getElementById('cv-variant-dropdown-btn');
        const dropdown = document.getElementById('cv-variant-dropdown');
        if (dropdownBtn && dropdown) {
            dropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
                if (!dropdownBtn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Layout preset buttons – wire after resizable-panes sets contentEditorLayout
        function updateLayoutButtons() {
            if (typeof window.contentEditorLayout === 'undefined' || !window.contentEditorLayout.getLayout) return;
            var current = window.contentEditorLayout.getLayout();
            document.querySelectorAll('.layout-preset-btn').forEach(function(btn) {
                var layout = btn.getAttribute('data-layout');
                if (layout === current) {
                    btn.classList.add('bg-blue-100', 'text-blue-700');
                    btn.classList.remove('text-gray-600');
                } else {
                    btn.classList.remove('bg-blue-100', 'text-blue-700');
                    btn.classList.add('text-gray-600');
                }
            });
        }
        function wireLayoutPresetButtons() {
            if (typeof window.contentEditorLayout === 'undefined' || !window.contentEditorLayout.setLayout) return;
            document.querySelectorAll('.layout-preset-btn').forEach(function(btn) {
                if (btn.dataset.layoutWired) return;
                btn.dataset.layoutWired = '1';
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var layout = this.getAttribute('data-layout');
                    if (window.contentEditorLayout && window.contentEditorLayout.setLayout) {
                        window.contentEditorLayout.setLayout(layout);
                        updateLayoutButtons();
                    }
                });
            });
            updateLayoutButtons();
        }
        window.addEventListener('contenteditorlayoutready', wireLayoutPresetButtons);
        window.addEventListener('contenteditorlayoutchange', updateLayoutButtons);
        if (document.readyState === 'complete') {
            setTimeout(wireLayoutPresetButtons, 100);
        } else {
            window.addEventListener('load', function() { setTimeout(wireLayoutPresetButtons, 50); });
        }
    });
</script>
