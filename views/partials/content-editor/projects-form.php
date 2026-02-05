<?php
/**
 * Projects Form Partial
 * When variant_id is in GET, loads from cv_variant_projects.
 */

$editingId = $_GET['edit'] ?? null;
$variantId = $_GET['variant_id'] ?? null;
$editingProject = null;
$projects = [];
$isVariantContext = false;

if ($variantId) {
    $variant = db()->fetchOne("SELECT id FROM cv_variants WHERE id = ? AND user_id = ?", [$variantId, $userId]);
    if ($variant) {
        $isVariantContext = true;
        if ($editingId) {
            $editingProject = db()->fetchOne("SELECT * FROM cv_variant_projects WHERE cv_variant_id = ? AND (id = ? OR original_project_id = ?)", [$variantId, $editingId, $editingId]);
        }
        $projects = db()->fetchAll("SELECT * FROM cv_variant_projects WHERE cv_variant_id = ? ORDER BY start_date DESC", [$variantId]);
    }
}

if (!$isVariantContext) {
    if ($editingId) {
        $editingProject = db()->fetchOne("SELECT * FROM projects WHERE id = ? AND profile_id = ?", [$editingId, $userId]);
    }
    $projects = db()->fetchAll("SELECT * FROM projects WHERE profile_id = ? ORDER BY start_date DESC", [$userId]);
}

$canAddProject = planCanAddEntry($subscriptionContext, 'projects', $userId, count($projects));

// Prepare image preview data
$initialImagePath = $editingProject['image_path'] ?? '';
$initialImageUrl = $editingProject['image_url'] ?? '';
$initialResponsive = $editingProject['image_responsive'] ?? null;
$previewImageSrc = '';

if (!empty($initialImageUrl)) {
    $previewImageSrc = $initialImageUrl;
} elseif (!empty($initialImagePath)) {
    $previewImageSrc = '/api/storage-proxy?path=' . urlencode($initialImagePath);
}

// Use responsive image if available (prefer small or thumb for preview)
if (!empty($initialResponsive)) {
    $responsive = is_string($initialResponsive) ? json_decode($initialResponsive, true) : $initialResponsive;
    if (is_array($responsive)) {
        if (!empty($responsive['small']['url'])) {
            $previewImageSrc = $responsive['small']['url'];
        } elseif (!empty($responsive['thumb']['url'])) {
            $previewImageSrc = $responsive['thumb']['url'];
        }
    }
}
?>
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
        <button type="button" onclick="assessSection('projects')" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <?php echo $editingProject ? 'Assess this entry' : 'Assess this section'; ?>
        </button>
    </div>
    
    <!-- Add/Edit Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">
            <?php echo $editingProject ? 'Edit Project' : 'Add New Project'; ?>
        </h2>
        
        <?php if (!$editingProject && !$canAddProject): ?>
            <div class="rounded-md bg-blue-50 border border-blue-200 p-4 text-sm text-blue-700">
                <?php echo getPlanLimitMessage($subscriptionContext, 'projects'); ?>
            </div>
        <?php else: ?>
        <form method="POST" enctype="multipart/form-data" data-section-form data-form-type="<?php echo $editingProject ? 'update' : 'create'; ?>">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="action" value="<?php echo $editingProject ? 'update' : 'create'; ?>">
            <input type="hidden" name="section_id" value="projects">
            <?php if ($editingProject): ?>
                <input type="hidden" name="id" value="<?php echo e($editingProject['id']); ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Project Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo $editingProject ? e($editingProject['title']) : ''; ?>" required maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $editingProject && $editingProject['start_date'] ? date('Y-m-d', strtotime($editingProject['start_date'])) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $editingProject && $editingProject['end_date'] ? date('Y-m-d', strtotime($editingProject['end_date'])) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <p class="text-xs text-gray-500 mb-1">Use the toolbar for formatting: bold, italic, headers, lists, and links</p>
                    <textarea id="description" name="description" rows="4" maxlength="5000" data-markdown class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"><?php echo $editingProject ? e($editingProject['description']) : ''; ?></textarea>
                </div>
                
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Project URL</label>
                    <input type="url" id="url" name="url" value="<?php echo $editingProject ? e($editingProject['url'] ?? '') : ''; ?>" maxlength="2048" placeholder="https://example.com" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-1">Project Image</span>
                    <div class="mt-2 flex flex-col md:flex-row md:items-center gap-4">
                        <div id="project-image-preview" class="w-32 h-32 rounded-md border <?php echo $previewImageSrc ? 'border-gray-200' : 'border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 text-sm'; ?>">
                            <?php if ($previewImageSrc): ?>
                                <img src="<?php echo e($previewImageSrc); ?>" alt="Project Image" class="w-32 h-32 object-cover rounded-md">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </div>
                        <div>
                            <input type="file" id="project_image" name="project_image" accept="image/*" class="text-sm text-gray-700">
                            <p class="mt-1 text-xs text-gray-500">Optional. JPG, PNG, GIF, or WebP up to 5MB.</p>
                            <div id="project-image-status" class="mt-2 hidden rounded-md border px-3 py-2 text-sm"></div>
                            <button type="button" id="project-image-clear" class="mt-2 text-sm text-red-600 hover:text-red-800 <?php echo $previewImageSrc ? '' : 'hidden'; ?>">Remove image</button>
                        </div>
                    </div>
                    <input type="hidden" id="image_url" name="image_url" value="<?php echo $editingProject && !empty($editingProject['image_url']) ? e($editingProject['image_url']) : ''; ?>">
                    <input type="hidden" id="image_path" name="image_path" value="<?php echo $editingProject && !empty($editingProject['image_path']) ? e($editingProject['image_path']) : ''; ?>">
                    <input type="hidden" id="image_responsive" name="image_responsive" value="<?php echo $editingProject && !empty($editingProject['image_responsive']) ? e($editingProject['image_responsive']) : ''; ?>">
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md">
                    <?php echo $editingProject ? 'Update Project' : 'Add Project'; ?>
                </button>
                <?php if ($editingProject): ?>
                    <button type="button" data-action="cancel" class="ml-4 text-gray-700 hover:text-gray-900">Cancel</button>
                <?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
    </div>
    
    <!-- Existing Projects List -->
    <div id="projects-entries-list">
        <?php if (empty($projects)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                <p>No projects added yet.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($projects as $project): ?>
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo e($project['title']); ?></h3>
                                <?php if ($project['start_date'] || $project['end_date']): ?>
                                    <p class="text-sm text-gray-500">
                                        <?php echo $project['start_date'] ? date('M Y', strtotime($project['start_date'])) : ''; ?> - 
                                        <?php echo $project['end_date'] ? date('M Y', strtotime($project['end_date'])) : 'Present'; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($project['description']): ?>
                                    <p class="mt-2 text-gray-600"><?php echo e($project['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($project['url']): ?>
                                    <a href="<?php echo e($project['url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">View Project →</a>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" data-action="edit" data-entry-id="<?php echo e($project['id']); ?>" class="px-3 py-1.5 bg-green-50 text-green-700 text-sm font-medium rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">Edit</button>
                                <button type="button" data-action="delete" data-entry-id="<?php echo e($project['id']); ?>" data-entry-type="projects" class="px-3 py-1.5 bg-red-50 text-red-700 text-sm font-medium rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
