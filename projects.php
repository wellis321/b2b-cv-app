<?php
require_once __DIR__ . '/php/helpers.php';
requireAuth();
$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');
$currentSectionId = 'projects';

$projects = db()->fetchAll("SELECT * FROM projects WHERE profile_id = ? ORDER BY start_date DESC", [$userId]);
$subscriptionContext = getUserSubscriptionContext($userId);
$canAddProject = planCanAddEntry($subscriptionContext, 'projects', $userId, count($projects));

if (isPost()) {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/projects.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        if (!planCanAddEntry($subscriptionContext, 'projects', $userId)) {
            setFlash('error', getPlanLimitMessage($subscriptionContext, 'projects'));
            redirect('/subscription.php');
        }

        $imageUrlInput = trim(post('image_url', ''));
        $imagePathInput = trim(post('image_path', ''));
        $title = sanitizeInput(post('title', ''));
        $description = trim(post('description', ''));
        $url = sanitizeInput(post('url', ''));

        // Validate title
        if (empty($title)) {
            setFlash('error', 'Project title is required');
            redirect('/projects.php');
        }

        // Check for XSS
        if (checkForXss($title)) {
            setFlash('error', 'Invalid content in project title');
            redirect('/projects.php');
        }

        if (!empty($description) && checkForXss($description)) {
            setFlash('error', 'Invalid content in description');
            redirect('/projects.php');
        }

        if (!empty($url) && checkForXss($url)) {
            setFlash('error', 'Invalid content in project URL');
            redirect('/projects.php');
        }

        // Length validation
        if (strlen($title) > 255) {
            setFlash('error', 'Project title must be 255 characters or less');
            redirect('/projects.php');
        }

        if (!empty($url) && strlen($url) > 2048) {
            setFlash('error', 'Project URL must be 2048 characters or less');
            redirect('/projects.php');
        }

        // Validate URL format if provided
        if (!empty($url) && !validateUrl($url)) {
            setFlash('error', 'Invalid project URL format');
            redirect('/projects.php');
        }

        $data = [
            'id' => generateUuid(),
            'profile_id' => $userId,
            'title' => $title,
            'description' => !empty($description) ? strip_tags($description) : null,
            'start_date' => post('start_date', '') ?: null,
            'end_date' => post('end_date', '') ?: null,
            'url' => !empty($url) ? $url : null,
            'image_url' => $imageUrlInput !== '' ? $imageUrlInput : null,
            'image_path' => $imagePathInput !== '' ? $imagePathInput : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($data['description'] && planWordLimitExceeded($subscriptionContext, 'project_description', $data['description'])) {
            setFlash('error', getPlanWordLimitMessage($subscriptionContext, 'project_description'));
            redirect('/projects.php');
        }

        // Handle image upload if provided
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['project_image'], $userId, 'projects', true); // Generate responsive versions

            if (!$uploadResult['success']) {
                error_log("Project image upload error: " . ($uploadResult['error'] ?? 'Unknown error'));
                setFlash('error', 'Image upload failed. Please try again.');
                redirect('/projects.php');
            }

            $data['image_url'] = $uploadResult['url'];
            $data['image_path'] = $uploadResult['path'] ?? str_replace(STORAGE_URL . '/', '', $uploadResult['url']);
            // Store responsive versions as JSON
            if (!empty($uploadResult['responsive'])) {
                $data['image_responsive'] = json_encode($uploadResult['responsive']);
            }
        }

        try {
            db()->insert('projects', $data);
            setFlash('success', 'Project added successfully');
        } catch (Exception $e) {
            error_log("Project creation error: " . $e->getMessage());
            setFlash('error', 'Failed to add project. Please try again.');
        }
        redirect('/projects.php');
    } elseif ($action === 'update') {
        $id = post('id');
        $project = db()->fetchOne("SELECT * FROM projects WHERE id = ? AND profile_id = ?", [$id, $userId]);

        if (!$project) {
            setFlash('error', 'Project not found.');
            redirect('/projects.php');
        }

        $imageUrlInput = trim(post('image_url', ''));
        $imagePathInput = trim(post('image_path', ''));
        $title = sanitizeInput(post('title', ''));
        $description = trim(post('description', ''));
        $url = sanitizeInput(post('url', ''));

        // Validate title
        if (empty($title)) {
            setFlash('error', 'Project title is required');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        // Check for XSS
        if (checkForXss($title)) {
            setFlash('error', 'Invalid content in project title');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        if (!empty($description) && checkForXss($description)) {
            setFlash('error', 'Invalid content in description');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        if (!empty($url) && checkForXss($url)) {
            setFlash('error', 'Invalid content in project URL');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        // Length validation
        if (strlen($title) > 255) {
            setFlash('error', 'Project title must be 255 characters or less');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        if (!empty($url) && strlen($url) > 2048) {
            setFlash('error', 'Project URL must be 2048 characters or less');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        // Validate URL format if provided
        if (!empty($url) && !validateUrl($url)) {
            setFlash('error', 'Invalid project URL format');
            redirect('/projects.php?edit=' . urlencode($id));
        }

        $data = [
            'title' => $title,
            'description' => !empty($description) ? strip_tags($description) : null,
            'start_date' => post('start_date', '') ?: null,
            'end_date' => post('end_date', '') ?: null,
            'url' => !empty($url) ? $url : null,
            'image_url' => $imageUrlInput !== '' ? $imageUrlInput : null,
            'image_path' => $imagePathInput !== '' ? $imagePathInput : null,
            'image_responsive' => $imageResponsiveInput !== '' ? $imageResponsiveInput : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($data['description'] && planWordLimitExceeded($subscriptionContext, 'project_description', $data['description'])) {
            setFlash('error', getPlanWordLimitMessage($subscriptionContext, 'project_description'));
            redirect('/projects.php?edit=' . urlencode($id));
        }

        // Handle new image upload if provided
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['project_image'], $userId, 'projects', true); // Generate responsive versions

            if (!$uploadResult['success']) {
                error_log("Project image upload error: " . ($uploadResult['error'] ?? 'Unknown error'));
                setFlash('error', 'Image upload failed. Please try again.');
                redirect('/projects.php?edit=' . urlencode($id));
            }

            // Remove previous image if it was stored locally
            $oldPath = $project['image_path'] ?? null;
            if (empty($oldPath) && !empty($project['image_url']) && strpos($project['image_url'], STORAGE_URL) === 0) {
                $oldPath = str_replace(STORAGE_URL . '/', '', $project['image_url']);
            }

            if (!empty($oldPath)) {
                $fullPath = STORAGE_PATH . '/' . $oldPath;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }

            $data['image_url'] = $uploadResult['url'];
            $data['image_path'] = $uploadResult['path'] ?? str_replace(STORAGE_URL . '/', '', $uploadResult['url']);
            // Store responsive versions as JSON
            if (!empty($uploadResult['responsive'])) {
                $data['image_responsive'] = json_encode($uploadResult['responsive']);
            }
        } else {
            // If image fields cleared, remove old stored image
            if (empty($data['image_url']) && empty($data['image_path'])) {
                $oldPath = $project['image_path'] ?? null;
                if (empty($oldPath) && !empty($project['image_url']) && strpos($project['image_url'], STORAGE_URL) === 0) {
                    $oldPath = str_replace(STORAGE_URL . '/', '', $project['image_url']);
                }

                if (!empty($oldPath)) {
                    $fullPath = STORAGE_PATH . '/' . $oldPath;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
        }

        try {
            // Log the data being updated for debugging
            error_log("Updating project with data: " . json_encode($data));
            
            db()->update('projects', $data, 'id = ? AND profile_id = ?', [$id, $userId]);
            setFlash('success', 'Project updated successfully');
        } catch (Exception $e) {
            error_log("Project update error: " . $e->getMessage());
            error_log("Project update data: " . print_r($data, true));
            error_log("Project ID: " . $id . ", User ID: " . $userId);
            error_log("Stack trace: " . $e->getTraceAsString());
            setFlash('error', 'Failed to update project: ' . ($e->getMessage() ?? 'Unknown error'));
            redirect('/projects.php?edit=' . urlencode($id));
        }

        redirect('/projects.php');
    } elseif ($action === 'delete') {
        $id = post('id');
        try {
            // Delete associated image if stored locally
            $project = db()->fetchOne("SELECT image_url, image_path FROM projects WHERE id = ? AND profile_id = ?", [$id, $userId]);

            db()->delete('projects', 'id = ? AND profile_id = ?', [$id, $userId]);

            $imagePath = $project['image_path'] ?? null;
            if (empty($imagePath) && !empty($project['image_url']) && strpos($project['image_url'], STORAGE_URL) === 0) {
                $imagePath = str_replace(STORAGE_URL . '/', '', $project['image_url']);
            }

            if (!empty($imagePath)) {
                $fullPath = STORAGE_PATH . '/' . $imagePath;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }

            setFlash('success', 'Project deleted successfully');
        } catch (Exception $e) {
            error_log("Project deletion error: " . $e->getMessage());
            setFlash('error', 'Failed to delete project. Please try again.');
        }
        redirect('/projects.php');
    }
}

$editingId = get('edit');
$editingProject = null;
if ($editingId) {
    $editingProject = db()->fetchOne("SELECT * FROM projects WHERE id = ? AND profile_id = ?", [$editingId, $userId]);
    if (!$editingProject) {
        $editingId = null;
        $editingProject = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Projects | Simple CV Builder',
        'metaDescription' => 'Manage your projects and showcase your work.',
        'canonicalUrl' => APP_URL . '/projects.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    <?php partial('section-nav', ['currentSectionId' => $currentSectionId]); ?>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Projects</h1>
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4"><p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4"><p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p></div>
        <?php endif; ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editingProject ? 'Edit Project' : 'Add Project'; ?>
            </h2>
            <?php if ($editingProject): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <p>You are editing <strong><?php echo e($editingProject['title']); ?></strong>. Update the details below and click "Save Changes".</p>
                </div>
            <?php endif; ?>
            <?php if (!$editingProject && !$canAddProject): ?>
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    <?php echo getPlanLimitMessage($subscriptionContext, 'projects'); ?>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" <?php echo (!$editingProject && !$canAddProject) ? 'class="pointer-events-none opacity-70"' : ''; ?>>
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editingProject ? 'update' : 'create'; ?>">
                <?php if ($editingProject): ?>
                    <input type="hidden" name="id" value="<?php echo e($editingProject['id']); ?>">
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <?php
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
                    <div class="sm:col-span-2"><label for="title" class="block text-sm font-medium text-gray-700">Project Title *</label>
                        <input type="text" id="title" name="title" value="<?php echo $editingProject ? e($editingProject['title']) : ''; ?>" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div class="sm:col-span-2"><label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" maxlength="5000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo $editingProject ? e($editingProject['description']) : ''; ?></textarea></div>
                    <div><label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $editingProject && $editingProject['start_date'] ? date('Y-m-d', strtotime($editingProject['start_date'])) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div><label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $editingProject && $editingProject['end_date'] ? date('Y-m-d', strtotime($editingProject['end_date'])) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div class="sm:col-span-2"><label for="url" class="block text-sm font-medium text-gray-700">Project URL</label>
                        <input type="url" id="url" name="url" value="<?php echo $editingProject ? e($editingProject['url']) : ''; ?>" placeholder="https://..." maxlength="2048" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></div>
                    <div class="sm:col-span-2">
                        <span class="block text-sm font-medium text-gray-700">Project Image</span>
                        <div class="mt-2 flex flex-col md:flex-row md:items-center gap-4">
                            <div id="project-image-preview" class="w-32 h-32 rounded-md border <?php echo $previewImageSrc ? 'border-gray-200' : 'border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 text-sm'; ?>">
                                <?php if ($previewImageSrc): ?>
                                    <img src="<?php echo e($previewImageSrc); ?>" alt="Project Image" class="w-32 h-32 object-cover rounded-md">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" id="project_image" name="project_image" accept="image/*" class="text-sm text-gray-700" onchange="handleProjectImageUpload(event)">
                                <p class="mt-1 text-xs text-gray-500">Optional. JPG, PNG, GIF, or WebP up to 5MB.</p>
                                <div id="project-image-status" class="mt-2 hidden rounded-md border px-3 py-2 text-sm"></div>
                                <button type="button" onclick="clearProjectImage()" class="mt-2 text-sm text-red-600 hover:text-red-800 <?php echo $previewImageSrc ? '' : 'hidden'; ?>" id="project-image-clear">Remove image</button>
                            </div>
                        </div>
                        <input type="hidden" id="image_url" name="image_url" value="<?php echo $editingProject && !empty($editingProject['image_url']) ? e($editingProject['image_url']) : ''; ?>">
                        <input type="hidden" id="image_path" name="image_path" value="<?php echo $editingProject && !empty($editingProject['image_path']) ? e($editingProject['image_path']) : ''; ?>">
                    </div>
                </div>
                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" <?php echo (!$editingProject && !$canAddProject) ? 'disabled' : ''; ?> class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 <?php echo (!$editingProject && !$canAddProject) ? 'opacity-60 cursor-not-allowed' : ''; ?>">
                        <?php echo $editingProject ? 'Save Changes' : 'Add Project'; ?>
                    </button>
                    <?php if ($editingProject): ?>
                        <a href="/projects.php" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php if (empty($projects)): ?>
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">No projects added yet.</div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-4">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold"><?php echo e($project['title']); ?></h3>
                            <?php if ($project['description']): ?><p class="text-gray-700 mt-2"><?php echo nl2br(e($project['description'])); ?></p><?php endif; ?>
                            <?php if ($project['url']): ?><a href="<?php echo e($project['url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">View Project →</a><?php endif; ?>
                            <?php if ($project['start_date']): ?><p class="text-sm text-gray-500 mt-2"><?php echo date('M Y', strtotime($project['start_date'])); ?><?php echo $project['end_date'] ? ' - ' . date('M Y', strtotime($project['end_date'])) : ''; ?></p><?php endif; ?>
                        </div>
                        <?php
                        $projectImagePath = isset($project['image_path']) ? html_entity_decode($project['image_path'], ENT_QUOTES, 'UTF-8') : null;
                        $projectImageUrlRaw = isset($project['image_url']) ? html_entity_decode($project['image_url'], ENT_QUOTES, 'UTF-8') : '';
                        $projectImageUrl = '';

                        if (!empty($projectImageUrlRaw)) {
                            $projectImageUrl = $projectImageUrlRaw;
                        } elseif (!empty($projectImagePath)) {
                            $projectImageUrl = '/api/storage-proxy?path=' . urlencode($projectImagePath);
                        }
                        
                        // Get responsive image attributes (context: 'list' for projects list page)
                        $responsiveData = isset($project['image_responsive']) ? $project['image_responsive'] : null;
                        $imgAttrs = getResponsiveImageAttributes($responsiveData, $projectImageUrl, 'list');
                        ?>
                        <div class="flex flex-col items-start gap-3 md:items-end">
                            <?php if (!empty($imgAttrs['src'])): ?>
                                <div class="md:w-48 md:flex-shrink-0">
                                    <img 
                                        src="<?php echo e($imgAttrs['src']); ?>" 
                                        <?php if (!empty($imgAttrs['srcset'])): ?>
                                            srcset="<?php echo e($imgAttrs['srcset']); ?>"
                                            sizes="<?php echo e($imgAttrs['sizes']); ?>"
                                        <?php endif; ?>
                                        alt="<?php echo e($project['title']); ?> - Project image" 
                                        class="w-full h-32 object-cover rounded-md border border-gray-200"
                                        loading="lazy"
                                        width="192"
                                        height="128"
                                        decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="flex gap-4 text-sm">
                                <a href="/projects.php?edit=<?php echo urlencode($project['id']); ?>" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e($project['id']); ?>">
                                    <button type="submit" onclick="return confirm('Delete this project?');" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php partial('footer'); ?>

    <script>
        const csrfToken = '<?php echo csrfToken(); ?>';
        const projectImagePreview = document.getElementById('project-image-preview');
        const projectImageInput = document.getElementById('project_image');
        const projectImageStatus = document.getElementById('project-image-status');
        const projectImageClear = document.getElementById('project-image-clear');
        const projectImageUrlInput = document.getElementById('image_url');
        const projectImagePathInput = document.getElementById('image_path');
        const projectImageResponsiveInput = document.getElementById('image_responsive');

        function showProjectImageStatus(message, type) {
            const classes = {
                success: 'border-green-200 text-green-700 bg-green-50',
                error: 'border-red-200 text-red-700 bg-red-50',
                info: 'border-blue-200 text-blue-700 bg-blue-50'
            };

            projectImageStatus.className = 'mt-2 rounded-md border px-3 py-2 text-sm ' + (classes[type] || classes.info);
            projectImageStatus.textContent = message;
            projectImageStatus.classList.remove('hidden');
        }

        function setProjectImagePreview(src) {
            if (src) {
                projectImagePreview.innerHTML = '<img src="' + src + '" alt="Project Image" class="w-32 h-32 object-cover rounded-md border border-gray-200">';
                projectImagePreview.className = 'w-32 h-32 rounded-md';
                projectImageClear.classList.remove('hidden');
            } else {
                projectImagePreview.innerHTML = 'No image';
                projectImagePreview.className = 'w-32 h-32 rounded-md border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 text-sm';
                projectImageClear.classList.add('hidden');
            }
        }

        function resetProjectImagePreview() {
            setProjectImagePreview('');
            projectImageUrlInput.value = '';
            projectImagePathInput.value = '';
            if (projectImageResponsiveInput) {
                projectImageResponsiveInput.value = '';
            }
        }

        let isUploading = false;
        
        function handleProjectImageUpload(event) {
            // #region agent log
            fetch('http://127.0.0.1:7250/ingest/02ed2acd-ae27-46f6-8e5d-0a67a71118e5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'projects.php:499',message:'handleProjectImageUpload called',data:{isUploading:isUploading,fileName:event.target.files[0]?.name},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A1'})}).catch(()=>{});
            // #endregion
            const file = event.target.files[0];
            if (!file) {
                return;
            }
            
            // Prevent multiple simultaneous uploads
            if (isUploading) {
                // #region agent log
                fetch('http://127.0.0.1:7250/ingest/02ed2acd-ae27-46f6-8e5d-0a67a71118e5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'projects.php:507',message:'Upload blocked - already in progress',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A1'})}).catch(()=>{});
                // #endregion
                showProjectImageStatus('Upload already in progress. Please wait...', 'info');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showProjectImageStatus('File too large. Maximum size is 5MB.', 'error');
                projectImageInput.value = '';
                return;
            }

            if (!file.type.match('image.*')) {
                showProjectImageStatus('Please choose an image file.', 'error');
                projectImageInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                setProjectImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append('project_image', file);
            formData.append('<?php echo CSRF_TOKEN_NAME; ?>', csrfToken);

            isUploading = true;
            showProjectImageStatus('Uploading image...', 'info');

            // #region agent log
            fetch('http://127.0.0.1:7250/ingest/02ed2acd-ae27-46f6-8e5d-0a67a71118e5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'projects.php:536',message:'Starting fetch upload',data:{fileName:file.name,fileSize:file.size},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A1'})}).catch(()=>{});
            // #endregion
            fetch('/api/upload-project-image.php', {
                method: 'POST',
                body: formData
            })
                .then((response) => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error(text || 'Upload failed. Server returned invalid response.');
                            }
                        });
                    }
                })
                .then((data) => {
                    // #region agent log
                    fetch('http://127.0.0.1:7250/ingest/02ed2acd-ae27-46f6-8e5d-0a67a71118e5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'projects.php:520',message:'Upload response received',data:{success:data.success,url:data.url,responsiveCount:data.responsive?Object.keys(data.responsive).length:0},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A1'})}).catch(()=>{});
                    // #endregion
                    isUploading = false;
                    if (data.success && data.url) {
                        projectImageUrlInput.value = data.url;
                        projectImagePathInput.value = data.path || '';
                        // Store responsive versions if available
                        if (projectImageResponsiveInput && data.responsive) {
                            projectImageResponsiveInput.value = JSON.stringify(data.responsive);
                            // #region agent log
                            fetch('http://127.0.0.1:7250/ingest/02ed2acd-ae27-46f6-8e5d-0a67a71118e5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'projects.php:527',message:'Responsive data stored in hidden input',data:{responsiveKeys:Object.keys(data.responsive)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A5'})}).catch(()=>{});
                            // #endregion
                        }
                        showProjectImageStatus('Image uploaded successfully.', 'success');
                        setProjectImagePreview(data.url);
                        // Log for debugging
                        console.log('Image uploaded - URL:', data.url, 'Path:', data.path, 'Responsive:', data.responsive);
                    } else {
                        const errorMessage = data.error || 'Upload failed. Please try again.';
                        showProjectImageStatus(errorMessage, 'error');
                        projectImageInput.value = '';
                        resetProjectImagePreview();
                    }
                })
                .catch((error) => {
                    isUploading = false;
                    showProjectImageStatus('Error uploading image: ' + error.message, 'error');
                    projectImageInput.value = '';
                    resetProjectImagePreview();
                });
        }

        function clearProjectImage() {
            projectImageInput.value = '';
            resetProjectImagePreview();
            showProjectImageStatus('Project image removed.', 'info');
        }

        // Expose functions for inline handlers
        window.handleProjectImageUpload = handleProjectImageUpload;
        window.clearProjectImage = clearProjectImage;

        // Reset preview if form is reset / page loads
        document.addEventListener('DOMContentLoaded', function() {
            const initialUrl = projectImageUrlInput.value;
            const initialPath = projectImagePathInput.value;

            if (initialUrl) {
                setProjectImagePreview(initialUrl);
            } else if (initialPath) {
                setProjectImagePreview('/api/storage-proxy?path=' + encodeURIComponent(initialPath));
            } else {
                resetProjectImagePreview();
            }
        });
    </script>
</body>
</html>
