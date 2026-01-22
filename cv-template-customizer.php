<?php
/**
 * CV Template Customiser
 * Allows users to generate and manage custom CV templates using AI
 */

require_once __DIR__ . '/php/helpers.php';

if (!isLoggedIn()) {
    redirect('/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$user = getCurrentUser();

// Only allow super admins
require_once __DIR__ . '/php/authorisation.php';
if (!isSuperAdmin($user['id'])) {
    setFlash('error', 'This feature is only available to super administrators. Please contact a super admin to create CV templates for your organisation.');
    redirect('/dashboard.php');
    exit;
}

require_once __DIR__ . '/php/cv-templates.php';

// Get all templates for the user
$templates = getUserCvTemplates($user['id']);
$stats = getCvTemplateStats($user['id']);
$activeTemplate = getActiveCvTemplate($user['id']);

$success = getFlash('success');
$error = getFlash('error');

$pageTitle = 'CV Template Builder';
$canonicalUrl = APP_URL . '/cv-template-customizer.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Create and customize CV templates visually with drag-and-drop. No code required.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" role="main">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">CV Template Builder</h1>
                <p class="mt-2 text-gray-600">Create and customize your CV templates using the visual builder. No code required.</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Template Statistics -->
            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Your Templates</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            <?php echo $stats['count']; ?> of <?php echo $stats['max_templates']; ?> templates 
                            (<?php echo $stats['total_size_kb']; ?> KB total)
                        </p>
                        <?php if ($stats['count'] === 0): ?>
                            <p class="mt-2 text-sm text-blue-600">
                                Create your first template using the Visual Builder below. Drag and drop sections, adjust colors and fonts, and see your changes in real-time.
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($stats['count'] >= $stats['max_templates']): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                            <p class="text-xs font-medium text-yellow-800">
                                Template limit reached. Delete a template to create a new one.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Templates List -->
            <?php if (!empty($templates)): ?>
                <div class="mb-6 bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Your Templates</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($templates as $template): ?>
                            <div class="px-6 py-4 hover:bg-gray-50 transition-colors" data-template-id="<?php echo e($template['id']); ?>">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-base font-semibold text-gray-900">
                                                <?php echo e($template['template_name']); ?>
                                            </h4>
                                            <?php if ($template['is_active']): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($template['template_description'])): ?>
                                            <p class="mt-1 text-sm text-gray-600">
                                                <?php echo e($template['template_description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Created: <?php echo date('M j, Y', strtotime($template['created_at'])); ?>
                                            • Size: <?php echo round((strlen($template['template_html'] ?? '') + strlen($template['template_css'] ?? '')) / 1024, 2); ?> KB
                                        </p>
                                    </div>
                                    <div class="ml-4 flex gap-2">
                                        <?php if (!empty($template['builder_type']) && $template['builder_type'] === 'visual'): ?>
                                            <a href="/cv-template-builder.php?template_id=<?php echo e($template['id']); ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                                </svg>
                                                Edit in Visual Builder
                                            </a>
                                        <?php else: ?>
                                            <a href="/cv-template-builder.php?template_id=<?php echo e($template['id']); ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                                </svg>
                                                Open in Visual Builder
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" onclick="toggleEditTemplate('<?php echo e($template['id']); ?>')" class="inline-flex items-center px-3 py-1.5 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors" title="Advanced: Edit HTML/CSS code directly">
                                            <span id="edit-btn-text-<?php echo e($template['id']); ?>">Code Editor</span>
                                            <span id="edit-btn-hide-<?php echo e($template['id']); ?>" class="hidden">Hide Editor</span>
                                        </button>
                                        <a href="/cv.php?template=<?php echo e($template['id']); ?>" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            Preview
                                        </a>
                                        <?php if (!$template['is_active']): ?>
                                            <form method="POST" action="/api/activate-cv-template.php" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                                <input type="hidden" name="template_id" value="<?php echo e($template['id']); ?>">
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                                    Activate
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/api/deactivate-cv-template.php" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                                                    Deactivate
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/api/delete-cv-template.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this template? This cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                            <input type="hidden" name="template_id" value="<?php echo e($template['id']); ?>">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Edit Form (Hidden by default) -->
                                <div id="edit-form-<?php echo e($template['id']); ?>" class="hidden mt-4 pt-4 border-t border-gray-200">
                                    <form method="POST" action="/api/update-cv-template.php" id="template-edit-form-<?php echo e($template['id']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                        <input type="hidden" name="template_id" value="<?php echo e($template['id']); ?>">
                                        
                                        <!-- HTML Editor -->
                                        <div class="mb-6">
                                            <label for="template_html_<?php echo e($template['id']); ?>" class="block text-base font-semibold text-gray-900 mb-3">
                                                Custom HTML
                                            </label>
                                            <textarea name="template_html"
                                                      id="template_html_<?php echo e($template['id']); ?>"
                                                      rows="15"
                                                      class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-sm font-mono text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"><?php echo htmlspecialchars($template['template_html'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <p class="mt-2 text-sm text-gray-500">
                                                Enter your custom HTML. Variables like <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">$profile</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">$cvData</code>, and <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">formatCvDate()</code> are available. 
                                                <strong>CSS frameworks (Tailwind, Bootstrap) are automatically available</strong>. Maximum 500KB.
                                            </p>
                                        </div>

                                        <!-- CSS Editor -->
                                        <div class="mb-6">
                                            <label for="template_css_<?php echo e($template['id']); ?>" class="block text-base font-semibold text-gray-900 mb-3">
                                                Custom CSS
                                            </label>
                                            <textarea name="template_css"
                                                      id="template_css_<?php echo e($template['id']); ?>"
                                                      rows="10"
                                                      class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-sm font-mono text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"><?php echo htmlspecialchars($template['template_css'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <p class="mt-2 text-sm text-gray-500">
                                                Enter your custom CSS styles. Maximum 100KB.
                                            </p>
                                        </div>

                                        <div class="flex justify-end gap-3">
                                            <button type="button" onclick="toggleEditTemplate('<?php echo e($template['id']); ?>')" class="px-6 py-3 border-2 border-gray-300 rounded-lg text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-6 py-3 border border-transparent rounded-lg text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Create Template Options -->
            <div class="mb-6 bg-white rounded-lg shadow-lg border-2 border-indigo-200 p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Template</h2>
                    <p class="text-gray-600">Use the Visual Builder to create templates easily - no coding required!</p>
                </div>
                <div class="max-w-2xl mx-auto">
                    <a href="/cv-template-builder.php" class="block w-full flex flex-col items-center justify-center p-8 border-2 border-indigo-500 rounded-xl hover:border-indigo-600 hover:bg-indigo-50 transition-all shadow-md hover:shadow-lg">
                        <svg class="w-16 h-16 text-indigo-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Visual Builder</h3>
                        <p class="text-base text-gray-700 text-center mb-4">Create templates visually with drag-and-drop. Adjust colors, fonts, and layout with live preview.</p>
                        <span class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg">
                            Start Building →
                        </span>
                    </a>
                </div>
                
                <!-- Alternative Options (Collapsed by default) -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" onclick="toggleAdvancedOptions()" class="w-full text-center text-sm text-gray-600 hover:text-gray-900 font-medium">
                        <span id="advanced-options-text">Show Advanced Options</span>
                        <span id="advanced-options-hide" class="hidden">Hide Advanced Options</span>
                        <svg id="advanced-options-arrow" class="inline-block w-4 h-4 ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="advanced-options" class="hidden mt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="#ai-generator" onclick="document.getElementById('ai-generator-section').scrollIntoView({behavior: 'smooth'}); return false;" class="flex flex-col items-center justify-center p-6 border-2 border-gray-300 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-colors">
                                <svg class="w-12 h-12 text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Generator</h3>
                                <p class="text-sm text-gray-600 text-center">Describe your ideal design and let AI generate it for you.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Generator Form -->
            <div id="ai-generator-section" class="bg-white rounded-lg shadow border border-gray-200 p-6 md:p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Generate New Template with AI (Advanced)</h2>
                <p class="text-gray-600 mb-6">
                    Describe how you want your CV to look. Be as specific as possible about colors, layout, fonts, and style.
                </p>

                <form id="template-generator-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                            Describe Your Ideal CV Design
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="6"
                            class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-base text-gray-900 shadow-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y"
                            placeholder="Example: I want a modern, minimalist design with a dark blue header, two-column layout with photo on the right, clean typography, and subtle shadows. Use a professional color scheme with blue and gray accents. Or: I want my CV to look similar to the design I'm uploading below. (Optional if you provide a URL or image)"></textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            Be specific about: colors, layout (single/two columns), photo placement, typography style, and overall aesthetic. You can also upload an image or provide a URL of a website you'd like to emulate. At least one of description, URL, or image is required.
                        </p>
                    </div>

                    <!-- Reference Options -->
                    <div class="mb-6 space-y-4">
                        <div>
                            <label for="reference-url" class="block text-sm font-semibold text-gray-900 mb-2">
                                Reference Website URL (Optional)
                            </label>
                            <input 
                                type="url" 
                                id="reference-url" 
                                name="reference_url"
                                class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-base text-gray-900 shadow-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                placeholder="https://example.com/cv-page"
                            />
                            <p class="mt-2 text-sm text-gray-500">
                                Enter the URL of a website or CV page you'd like to use as inspiration.
                            </p>
                        </div>

                        <div>
                            <label for="reference-image" class="block text-sm font-semibold text-gray-900 mb-2">
                                Upload Reference Image (Optional)
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                                <div class="space-y-1 text-center w-full">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="reference-image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload an image</span>
                                            <input id="reference-image" name="reference_image" type="file" class="sr-only" accept="image/jpeg,image/png,image/gif,image/webp" />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 10MB</p>
                                    <div id="image-preview-container" class="hidden mt-4">
                                        <img id="image-preview" src="" alt="Preview" class="max-w-full max-h-64 mx-auto rounded-lg border border-gray-300" />
                                        <button type="button" id="remove-image" class="mt-2 text-sm text-red-600 hover:text-red-800">Remove image</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="layout" class="block text-sm font-semibold text-gray-900 mb-2">
                                Layout Preference (Optional)
                            </label>
                            <select 
                                id="layout" 
                                name="layout"
                                class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-base text-gray-900 shadow-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <option value="">Let AI decide</option>
                                <option value="single-column">Single Column</option>
                                <option value="two-column">Two Column</option>
                                <option value="three-column">Three Column</option>
                                <option value="sidebar">Sidebar Layout</option>
                            </select>
                        </div>

                        <div>
                            <label for="color-scheme" class="block text-sm font-semibold text-gray-900 mb-2">
                                Color Scheme (Optional)
                            </label>
                            <select 
                                id="color-scheme" 
                                name="color_scheme"
                                class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-base text-gray-900 shadow-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <option value="">Let AI decide</option>
                                <option value="professional-blue">Professional Blue</option>
                                <option value="modern-gray">Modern Gray</option>
                                <option value="bold-colorful">Bold & Colorful</option>
                                <option value="minimal-black-white">Minimal Black & White</option>
                                <option value="warm-tones">Warm Tones</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button 
                            type="button" 
                            onclick="window.location.href='/cv.php'"
                            class="px-6 py-3 border-2 border-gray-300 rounded-lg text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            id="generate-btn"
                            <?php if ($stats['count'] >= $stats['max_templates']): ?>disabled<?php endif; ?>
                            class="px-6 py-3 border border-transparent rounded-lg text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="generate-btn-text">Generate Template</span>
                            <span id="generate-btn-loading" class="hidden">Generating...</span>
                        </button>
                    </div>
                    <?php if ($stats['count'] >= $stats['max_templates']): ?>
                        <p class="mt-3 text-sm text-red-600 text-right">
                            You've reached the template limit. Please delete an existing template to create a new one.
                        </p>
                    <?php endif; ?>
                </form>

                <!-- Loading Indicator -->
                <div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Generating Your Template</h3>
                        <p class="text-gray-600">This may take 30-60 seconds. Please don't close this page.</p>
                    </div>
                </div>

                <!-- Results -->
                <div id="results" class="hidden mt-8">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-green-800">
                            <strong>Template generated successfully!</strong> Preview it below or activate it to use on your CV page.
                        </p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Preview</h3>
                        <iframe id="template-preview" class="w-full border border-gray-300 rounded" style="height: 600px;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <script>
        // Toggle edit form for templates
        function toggleAdvancedOptions() {
            const options = document.getElementById('advanced-options');
            const text = document.getElementById('advanced-options-text');
            const hide = document.getElementById('advanced-options-hide');
            const arrow = document.getElementById('advanced-options-arrow');
            
            if (options.classList.contains('hidden')) {
                options.classList.remove('hidden');
                text.classList.add('hidden');
                hide.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                options.classList.add('hidden');
                text.classList.remove('hidden');
                hide.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }

        function toggleEditTemplate(templateId) {
            const editForm = document.getElementById('edit-form-' + templateId);
            const editBtnText = document.getElementById('edit-btn-text-' + templateId);
            const editBtnHide = document.getElementById('edit-btn-hide-' + templateId);
            
            if (editForm.classList.contains('hidden')) {
                editForm.classList.remove('hidden');
                editBtnText.classList.add('hidden');
                editBtnHide.classList.remove('hidden');
            } else {
                editForm.classList.add('hidden');
                editBtnText.classList.remove('hidden');
                editBtnHide.classList.add('hidden');
            }
        }
        
        // Image preview functionality
        const imageInput = document.getElementById('reference-image');
        const imagePreview = document.getElementById('image-preview');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const removeImageBtn = document.getElementById('remove-image');
        
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreviewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', function() {
                imageInput.value = '';
                imagePreview.src = '';
                imagePreviewContainer.classList.add('hidden');
            });
        }
        
        // Drag and drop functionality
        const dropZone = document.querySelector('.border-dashed');
        if (dropZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('border-blue-400', 'bg-blue-50');
                }, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
                }, false);
            });
            
            dropZone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0 && imageInput) {
                    imageInput.files = files;
                    const event = new Event('change', { bubbles: true });
                    imageInput.dispatchEvent(event);
                }
            }, false);
        }
        
        document.getElementById('template-generator-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const generateBtn = document.getElementById('generate-btn');
            const generateBtnText = document.getElementById('generate-btn-text');
            const generateBtnLoading = document.getElementById('generate-btn-loading');
            const loadingOverlay = document.getElementById('loading-overlay');
            const results = document.getElementById('results');
            
            // Validate that at least one input is provided
            const description = document.getElementById('description').value.trim();
            const referenceUrl = document.getElementById('reference-url').value.trim();
            const imageInput = document.getElementById('reference-image');
            const hasImage = imageInput && imageInput.files && imageInput.files[0];
            
            if (!description && !referenceUrl && !hasImage) {
                alert('Please provide at least one of the following: a description, a reference URL, or an image.');
                return;
            }
            
            // Show loading
            generateBtn.disabled = true;
            generateBtnText.classList.add('hidden');
            generateBtnLoading.classList.remove('hidden');
            loadingOverlay.classList.remove('hidden');
            results.classList.add('hidden');
            
            const formData = new FormData(form);
            const options = {
                layout_preference: document.getElementById('layout').value,
                color_scheme: document.getElementById('color-scheme').value,
                reference_url: referenceUrl
            };
            formData.append('options', JSON.stringify(options));
            
            // Add image file if selected
            if (hasImage) {
                formData.append('reference_image', imageInput.files[0]);
            }
            
            try {
                const response = await fetch('/api/ai-generate-cv-template.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // Check if this is browser AI execution
                if (result.success && result.browser_execution) {
                    // Browser AI mode - execute client-side
                    await executeBrowserAITemplate(result, loadingOverlay, formData);
                    return;
                }
                
                if (result.success) {
                    // Show preview
                    const previewFrame = document.getElementById('template-preview');
                    const previewDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
                    
                    // Clean HTML/CSS content - remove PHP tags
                    let cssContent = (result.css || '').replace(/<\?php[\s\S]*?\?>/g, '');
                    let htmlContent = (result.html || '').replace(/<\?php[\s\S]*?\?>/g, '<!-- PHP code removed for preview -->');
                    
                    // Use DOM methods to safely insert content
                    previewDoc.open();
                    previewDoc.write('<!DOCTYPE html><html><head></head><body></body></html>');
                    previewDoc.close();
                    
                    // Add Tailwind script
                    const script = previewDoc.createElement('script');
                    script.src = 'https://cdn.tailwindcss.com';
                    previewDoc.head.appendChild(script);
                    
                    // Add CSS
                    const style = previewDoc.createElement('style');
                    style.textContent = cssContent;
                    previewDoc.head.appendChild(style);
                    
                    // Add HTML content to body
                    previewDoc.body.innerHTML = htmlContent;
                    
                    results.classList.remove('hidden');
                    // Reload after a short delay to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Error: ' + (result.error || 'Failed to generate template'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error generating template. Please try again.');
            } finally {
                generateBtn.disabled = false;
                generateBtnText.classList.remove('hidden');
                generateBtnLoading.classList.add('hidden');
                loadingOverlay.classList.add('hidden');
            }
        });

        // Execute browser AI for template generation
        async function executeBrowserAITemplate(result, loadingOverlay, originalFormData) {
            try {
                // Check browser support
                const support = BrowserAIService.checkBrowserSupport();
                if (!support.required) {
                    throw new Error('Browser does not support WebGPU or WebGL. Browser AI requires a modern browser with GPU support.');
                }

                // Update loading overlay to show model loading
                if (loadingOverlay) {
                    loadingOverlay.querySelector('p').textContent = 'Loading AI model. This may take a few minutes on first use...';
                }

                // Initialize browser AI
                const modelType = result.model_type === 'webllm' ? 'webllm' : 'tensorflow';
                await BrowserAIService.initBrowserAI(modelType, result.model, (progress) => {
                    if (loadingOverlay && progress.message) {
                        loadingOverlay.querySelector('p').textContent = progress.message;
                    }
                });

                // Use prompt from backend if available
                let prompt = result.prompt || '';
                if (!prompt) {
                    // Fallback: build template generation prompt
                    const cvData = result.cv_data || {};
                    const userDescription = result.user_description || '';
                    prompt = `Generate a custom CV template based on this description: ${userDescription}. CV data structure: ${JSON.stringify(cvData)}. Return a JSON object with 'html', 'css', and 'instructions' fields.`;
                }

                // Update loading overlay
                if (loadingOverlay) {
                    loadingOverlay.querySelector('p').textContent = 'Generating template... This may take 30-60 seconds.';
                }

                // Generate template using browser AI
                let templateText;
                try {
                    templateText = await BrowserAIService.generateText(prompt, {
                        temperature: 0.7,
                        maxTokens: 8000
                    });
                    
                    // Check if response is valid (not HTML/error)
                    if (!templateText || typeof templateText !== 'string') {
                        throw new Error('Browser AI returned invalid response type');
                    }
                    
                    if (templateText.trim().startsWith('<') || templateText.includes('<br') || templateText.includes('<b>') || templateText.includes('Fatal error') || templateText.includes('Parse error')) {
                        throw new Error('Browser AI returned an error response (HTML) instead of JSON. Please try again.');
                    }
                } catch (error) {
                    throw error;
                }

                // Clean the template text - remove markdown code blocks and fix control characters
                let cleanedText = templateText.trim();
                
                // Remove AI model metadata tokens that might break JSON
                cleanedText = cleanedText.replace(/<\|[^|]+\|>/g, '');
                cleanedText = cleanedText.replace(/<\|start_header_id\|>[^<]*<\|end_header_id\|>/g, '');
                cleanedText = cleanedText.replace(/assistant/g, ''); // Remove stray "assistant" tokens
                
                // Remove explanatory text that AI might add (e.g., "Here is the rest of the HTML structure:")
                cleanedText = cleanedText.replace(/Here is the rest of the HTML structure:[\s\n]*/gi, '');
                cleanedText = cleanedText.replace(/Here is the completed JSON:[\s\n]*/gi, '');
                cleanedText = cleanedText.replace(/Here is the template:[\s\n]*/gi, '');
                cleanedText = cleanedText.replace(/Here is your custom CV template:[\s\n]*/gi, '');
                
                // Remove markdown code blocks if present
                cleanedText = cleanedText.replace(/```json\s*/gi, '');
                cleanedText = cleanedText.replace(/```\s*/g, '');
                
                // Remove any text before the first { and after the last }
                const firstBrace = cleanedText.indexOf('{');
                const lastBrace = cleanedText.lastIndexOf('}');
                if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                    cleanedText = cleanedText.substring(firstBrace, lastBrace + 1);
                } else {
                    // Fallback: try to extract JSON object if wrapped in text
                    const jsonMatch = cleanedText.match(/\{[\s\S]*\}/);
                    if (jsonMatch) {
                        cleanedText = jsonMatch[0];
                    }
                }
                
                // Remove any nested JSON-like structures that might be inside string values
                // This handles cases where AI includes explanatory JSON inside the actual JSON
                // We'll be more aggressive - if we see `{\n  \"html\"` inside a string, remove it
                // But we need to be careful not to break valid JSON
                // For now, we'll let the parser handle it, but we'll improve the parser logic
                
                // Check if response contains HTML/error messages before parsing
                if (cleanedText.trim().startsWith('<') || cleanedText.includes('<br') || cleanedText.includes('<b>') || cleanedText.includes('Fatal error') || cleanedText.includes('Parse error')) {
                    throw new Error('AI service returned an error response instead of JSON. The response may contain HTML error messages. Please try again.');
                }

                // Parse template JSON
                let template;
                try {
                    template = JSON.parse(cleanedText);
                } catch (e) {
                    
                    // Try to fix common JSON issues - escape control characters in string values
                    // Use a state machine to properly handle escaped sequences
                    // Track which key we're currently in to better detect closing quotes
                    let fixedText = '';
                    try {
                        let inString = false;
                        let controlCharsFixed = 0;
                        let escapeNext = false;
                        let currentKey = null; // Track which JSON key we're currently processing
                        let keyStartPos = -1; // Track where the current key started
                        
                        for (let i = 0; i < cleanedText.length; i++) {
                            const char = cleanedText[i];
                            const code = char.charCodeAt(0);
                            
                            if (escapeNext) {
                                // We're processing an escaped character
                                // Check if it's a control character that needs proper escaping
                                if (inString && ((code >= 0x00 && code <= 0x1F) || code === 0x7F)) {
                                    // This is a control character after a backslash - replace the backslash+char with proper escape
                                    // Remove the backslash we added, then add proper escape sequence
                                    fixedText = fixedText.slice(0, -1); // Remove the backslash we added
                                    controlCharsFixed++;
                                    if (code === 0x08) fixedText += '\\b';
                                    else if (code === 0x09) fixedText += '\\t';
                                    else if (code === 0x0A) fixedText += '\\n';
                                    else if (code === 0x0C) fixedText += '\\f';
                                    else if (code === 0x0D) fixedText += '\\r';
                                    else fixedText += '\\u' + ('0000' + code.toString(16)).slice(-4);
                                } else {
                                    // Normal escaped character - add it as-is
                                    fixedText += char;
                                }
                                escapeNext = false;
                                continue;
                            }
                            
                            if (char === '\\') {
                                // Start of escape sequence
                                escapeNext = true;
                                fixedText += char;
                                continue;
                            }
                            
                            if (char === '"') {
                                // Quote character
                                if (escapeNext) {
                                    // Escaped quote - part of string content
                                    fixedText += char;
                                    escapeNext = false;
                                } else if (inString) {
                                    // We're inside a string and see an unescaped quote
                                    // Look ahead to determine if this is a closing quote
                                    let lookAhead = cleanedText.substring(i + 1, Math.min(i + 50, cleanedText.length));
                                    let lookAheadTrimmed = lookAhead.trim();
                                    
                                    // Be very conservative - only close string if we're CERTAIN it's a closing quote
                                    // Check for patterns that indicate this is a closing quote:
                                    // 1. Followed by comma and whitespace (very likely closing)
                                    // 2. Followed by closing brace/bracket (very likely closing)
                                    // 3. Followed by colon BUT only if we see a quote after it (like `": "value"`)
                                    //    AND we're NOT in html/css value (those can have colons in CSS)
                                    
                                    // More sophisticated: if we see a pattern like `\"html\":` or `\"css\":` 
                                    // and we're already processing html/css, it's content, not a closing quote
                                    const jsonKeyPattern = /^\s*\\?"(html|css|instructions)"\s*:/;
                                    if (jsonKeyPattern.test(lookAhead) && (currentKey === 'html' || currentKey === 'css' || currentKey === 'instructions')) {
                                        // This is AI-generated explanatory text inside the value - escape the quote
                                        fixedText += '\\"';
                                    } else if (lookAheadTrimmed.match(/^\s*[,}\]]/)) {
                                        // Very likely a closing quote - followed by comma or closing brace/bracket
                                        inString = false;
                                        currentKey = null;
                                        fixedText += char;
                                    } else if (lookAheadTrimmed.match(/^\s*:\s*["']/) && currentKey !== 'html' && currentKey !== 'css') {
                                        // Followed by colon and quote (like `": "value"`) - likely closing
                                        // But NOT if we're in html/css (those can have colons in content)
                                        inString = false;
                                        currentKey = null;
                                        fixedText += char;
                                    } else {
                                        // Uncertain - be conservative and escape it (treat as content)
                                        fixedText += '\\"';
                                    }
                                } else {
                                    // Unescaped quote outside string - start a new string
                                    inString = true;
                                    
                                    // Check if this might be a JSON key by looking backwards
                                    let lookBack = fixedText.substring(Math.max(0, fixedText.length - 20));
                                    if (lookBack.match(/{\s*$/) || lookBack.match(/,\s*$/)) {
                                        // We're likely starting a JSON key
                                        keyStartPos = i;
                                    }
                                    
                                    fixedText += char;
                                }
                                continue;
                            }
                            
                            // Track when we enter a known JSON key value
                            if (!inString && char === ':' && keyStartPos !== -1) {
                                // Extract the key name (between the quotes)
                                let keyText = cleanedText.substring(keyStartPos + 1, i - 1);
                                if (keyText === 'html' || keyText === 'css' || keyText === 'instructions') {
                                    currentKey = keyText;
                                }
                                keyStartPos = -1;
                            }
                            
                            // Reset key tracking if we hit a comma or closing brace outside strings
                            if (!inString && (char === ',' || char === '}')) {
                                currentKey = null;
                                keyStartPos = -1;
                            }
                            
                            if (inString && !escapeNext) {
                                // Inside a string and not part of an escape sequence - escape control characters
                                if ((code >= 0x00 && code <= 0x1F) || code === 0x7F) {
                                    // Control character - escape it
                                    controlCharsFixed++;
                                    if (code === 0x08) fixedText += '\\b';
                                    else if (code === 0x09) fixedText += '\\t';
                                    else if (code === 0x0A) fixedText += '\\n';
                                    else if (code === 0x0C) fixedText += '\\f';
                                    else if (code === 0x0D) fixedText += '\\r';
                                    else fixedText += '\\u' + ('0000' + code.toString(16)).slice(-4);
                                } else {
                                    fixedText += char;
                                }
                            } else {
                                fixedText += char;
                                escapeNext = false;
                            }
                        }
                        
                        // If we're still inside a string at the end, close it
                        // This handles cases where the AI-generated JSON is incomplete
                        if (inString) {
                            fixedText += '"';
                            inString = false;
                        }
                        
                        // Ensure the JSON object is properly closed
                        let openBraces = (fixedText.match(/{/g) || []).length;
                        let closeBraces = (fixedText.match(/}/g) || []).length;
                        while (openBraces > closeBraces) {
                            fixedText += '}';
                            closeBraces++;
                        }
                        
                        
                        // Check if fixed text still contains HTML/error messages
                        if (fixedText.trim().startsWith('<') || fixedText.includes('<br') || fixedText.includes('<b>') || fixedText.includes('Fatal error') || fixedText.includes('Parse error')) {
                            throw new Error('Response contains HTML/error messages. The AI service may have encountered an error.');
                        }
                        
                        template = JSON.parse(fixedText);
                    } catch (e2) {
                        const errorPos = parseInt(e2.message.match(/position (\d+)/)?.[1] || 0);
                        const contextStart = Math.max(0, errorPos - 50);
                        const contextEnd = Math.min(fixedText.length, errorPos + 50);
                        const context = fixedText.substring(contextStart, contextEnd);
                        
                        throw new Error('Failed to parse AI response as JSON: ' + e.message + '. Second attempt: ' + e2.message);
                    }
                }

                // Send template to server to save
                
                const saveFormData = new FormData();
                saveFormData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                saveFormData.append('browser_ai_result', JSON.stringify(template));
                // Copy original form data (description, options, etc.)
                for (const [key, value] of originalFormData.entries()) {
                    if (key !== '<?php echo CSRF_TOKEN_NAME; ?>') {
                        saveFormData.append(key, value);
                    }
                }

                const saveResponse = await fetch('/api/ai-generate-cv-template.php', {
                    method: 'POST',
                    body: saveFormData
                });

                const saveResult = await saveResponse.json();
                
                // Cleanup
                await BrowserAIService.cleanup();
                if (loadingOverlay) loadingOverlay.classList.add('hidden');

                if (saveResult.success) {
                    // Show preview
                    const previewFrame = document.getElementById('template-preview');
                    const previewDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
                    
                    // Clean HTML/CSS content - remove PHP tags
                    let cssContent = (saveResult.css || '').replace(/<\?php[\s\S]*?\?>/g, '');
                    let htmlContent = (saveResult.html || '').replace(/<\?php[\s\S]*?\?>/g, '<!-- PHP code removed for preview -->');
                    
                    // Use DOM methods to safely insert content
                    previewDoc.open();
                    previewDoc.write('<!DOCTYPE html><html><head></head><body></body></html>');
                    previewDoc.close();
                    
                    // Add Tailwind script
                    const script = previewDoc.createElement('script');
                    script.src = 'https://cdn.tailwindcss.com';
                    previewDoc.head.appendChild(script);
                    
                    // Add CSS
                    const style = previewDoc.createElement('style');
                    style.textContent = cssContent;
                    previewDoc.head.appendChild(style);
                    
                    // Add HTML content to body
                    previewDoc.body.innerHTML = htmlContent;
                    
                    document.getElementById('results').classList.remove('hidden');
                    
                    // Reload after a short delay to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(saveResult.error || 'Failed to save template');
                }
            } catch (error) {
                console.error('Browser AI execution error:', error);
                if (loadingOverlay) loadingOverlay.classList.add('hidden');
                
                const generateBtn = document.getElementById('generate-btn');
                const generateBtnText = document.getElementById('generate-btn-text');
                const generateBtnLoading = document.getElementById('generate-btn-loading');
                
                if (generateBtn) generateBtn.disabled = false;
                if (generateBtnText) generateBtnText.classList.remove('hidden');
                if (generateBtnLoading) generateBtnLoading.classList.add('hidden');
                
                alert('Error: ' + error.message);
            }
        }
    </script>
    <script src="/js/model-cache-manager.js"></script>
    <script src="/js/browser-ai-service.js"></script>
</body>
</html>

