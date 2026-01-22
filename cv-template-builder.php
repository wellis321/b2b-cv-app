<?php
/**
 * Visual CV Template Builder
 * Drag-and-drop interface for creating CV templates without code
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
require_once __DIR__ . '/php/template-config-schema.php';
require_once __DIR__ . '/php/cv-data.php';

// Load user's CV data for preview
$cvData = loadCvData($user['id']);
$profile = db()->fetchOne("SELECT * FROM profiles WHERE id = ?", [$user['id']]);

// Get template ID if editing existing template
$templateId = $_GET['template_id'] ?? null;
$template = null;
$templateConfig = null;

if ($templateId) {
    $template = getCvTemplate($templateId, $user['id']);
    if ($template && !empty($template['template_config'])) {
        $templateConfig = json_decode($template['template_config'], true);
    }
}

// Use default config if no template or config exists
if (!$templateConfig) {
    $templateConfig = getDefaultTemplateConfig();
}

$availableSections = getAvailableCvSections();

$pageTitle = 'Visual Template Builder';
$canonicalUrl = APP_URL . '/cv-template-builder.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Create CV templates visually with drag-and-drop interface',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
    <style>
        .builder-panel {
            height: calc(100vh - 200px);
            overflow-y: auto;
        }
        .section-card {
            cursor: move;
            transition: all 0.2s;
        }
        .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .section-card.dragging {
            opacity: 0.5;
        }
        .preview-container {
            border: 2px dashed #e5e7eb;
            height: calc(100vh - 280px);
            min-height: 600px;
            background: white;
            overflow: auto;
        }
        .preview-iframe {
            width: 100%;
            height: 100%;
            min-height: 600px;
            border: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" role="main">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Visual Template Builder</h1>
                        <p class="mt-2 text-gray-600">Create CV templates visually with drag-and-drop. No code required.</p>
                    </div>
                    <div class="flex gap-3">
                        <button id="save-template-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Template
                        </button>
                        <a href="/cv-template-customizer.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Back to Templates
                        </a>
                    </div>
                </div>
            </div>

            <!-- Three Panel Layout -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Left Panel: Available Sections -->
                <div class="col-span-3 bg-white rounded-lg shadow border border-gray-200 p-4 builder-panel">
                    <h2 class="text-lg font-semibold mb-4 text-gray-900">Sections</h2>
                    <div id="available-sections" class="space-y-2">
                        <?php foreach ($availableSections as $section): ?>
                            <div class="section-card bg-gray-50 border border-gray-200 rounded-lg p-3" 
                                 data-section-id="<?php echo e($section['id']); ?>"
                                 draggable="true">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium text-sm text-gray-900"><?php echo e($section['name']); ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo e($section['description']); ?></p>
                                    </div>
                                    <input type="checkbox" 
                                           class="section-toggle" 
                                           data-section-id="<?php echo e($section['id']); ?>"
                                           <?php 
                                           $sectionConfig = array_filter($templateConfig['sections'], fn($s) => $s['id'] === $section['id']);
                                           if (!empty($sectionConfig) && (reset($sectionConfig)['enabled'] ?? true)) echo 'checked';
                                           ?>>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Center Panel: Preview -->
                <div class="col-span-6 bg-white rounded-lg shadow border border-gray-200 p-4 builder-panel">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Preview</h2>
                        <button id="refresh-preview-btn" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                            Refresh
                        </button>
                    </div>
                    <div id="preview-container" class="preview-container">
                        <iframe id="preview-iframe" class="preview-iframe" src="about:blank"></iframe>
                    </div>
                </div>

                <!-- Right Panel: Style Controls -->
                <div class="col-span-3 bg-white rounded-lg shadow border border-gray-200 p-4 builder-panel">
                    <h2 class="text-lg font-semibold mb-4 text-gray-900">Style Controls</h2>
                    
                    <!-- Layout Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Layout</label>
                        <select id="layout-select" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="single-column" <?php echo ($templateConfig['layout'] ?? 'single-column') === 'single-column' ? 'selected' : ''; ?>>Single Column</option>
                            <option value="two-column" <?php echo ($templateConfig['layout'] ?? '') === 'two-column' ? 'selected' : ''; ?>>Two Column</option>
                            <option value="sidebar" <?php echo ($templateConfig['layout'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
                        </select>
                    </div>

                    <!-- Colors -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Colors</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Header</label>
                                <input type="color" id="color-header" 
                                       value="<?php echo e($templateConfig['styling']['colors']['header'] ?? '#1f2937'); ?>"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Accent</label>
                                <input type="color" id="color-accent" 
                                       value="<?php echo e($templateConfig['styling']['colors']['accent'] ?? '#2563eb'); ?>"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Text</label>
                                <input type="color" id="color-text" 
                                       value="<?php echo e($templateConfig['styling']['colors']['text'] ?? '#374151'); ?>"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Muted</label>
                                <input type="color" id="color-muted" 
                                       value="<?php echo e($templateConfig['styling']['colors']['muted'] ?? '#6b7280'); ?>"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Background</label>
                                <input type="color" id="color-background" 
                                       value="<?php echo e($templateConfig['styling']['colors']['background'] ?? '#ffffff'); ?>"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Typography -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Typography</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Heading Font</label>
                                <select id="font-heading" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option value="Arial, sans-serif" <?php echo ($templateConfig['styling']['fonts']['heading'] ?? 'Arial, sans-serif') === 'Arial, sans-serif' ? 'selected' : ''; ?>>Arial</option>
                                    <option value="Georgia, serif" <?php echo ($templateConfig['styling']['fonts']['heading'] ?? '') === 'Georgia, serif' ? 'selected' : ''; ?>>Georgia</option>
                                    <option value="'Times New Roman', serif" <?php echo ($templateConfig['styling']['fonts']['heading'] ?? '') === "'Times New Roman', serif" ? 'selected' : ''; ?>>Times New Roman</option>
                                    <option value="'Courier New', monospace" <?php echo ($templateConfig['styling']['fonts']['heading'] ?? '') === "'Courier New', monospace" ? 'selected' : ''; ?>>Courier New</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Body Font</label>
                                <select id="font-body" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option value="Arial, sans-serif" <?php echo ($templateConfig['styling']['fonts']['body'] ?? 'Arial, sans-serif') === 'Arial, sans-serif' ? 'selected' : ''; ?>>Arial</option>
                                    <option value="Georgia, serif" <?php echo ($templateConfig['styling']['fonts']['body'] ?? '') === 'Georgia, serif' ? 'selected' : ''; ?>>Georgia</option>
                                    <option value="'Times New Roman', serif" <?php echo ($templateConfig['styling']['fonts']['body'] ?? '') === "'Times New Roman', serif" ? 'selected' : ''; ?>>Times New Roman</option>
                                    <option value="'Courier New', monospace" <?php echo ($templateConfig['styling']['fonts']['body'] ?? '') === "'Courier New', monospace" ? 'selected' : ''; ?>>Courier New</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Spacing -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Spacing</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    Section Spacing: <span id="spacing-section-value"><?php echo $templateConfig['styling']['spacing']['section'] ?? 24; ?></span>px
                                </label>
                                <input type="range" id="spacing-section" min="12" max="48" step="4"
                                       value="<?php echo $templateConfig['styling']['spacing']['section'] ?? 24; ?>"
                                       class="w-full">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    Item Spacing: <span id="spacing-item-value"><?php echo $templateConfig['styling']['spacing']['item'] ?? 12; ?></span>px
                                </label>
                                <input type="range" id="spacing-item" min="4" max="24" step="2"
                                       value="<?php echo $templateConfig['styling']['spacing']['item'] ?? 12; ?>"
                                       class="w-full">
                            </div>
                        </div>
                    </div>

                    <!-- Section Inspector (shown when section is selected) -->
                    <div id="section-inspector" class="hidden">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Section Settings</h3>
                        <div id="section-settings-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Save Template Modal -->
    <div id="save-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Save Template</h3>
            <form id="save-template-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                    <input type="text" id="template-name" 
                           value="<?php echo e($template['template_name'] ?? 'Untitled Template'); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (optional)</label>
                    <textarea id="template-description" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2"
                              rows="3"><?php echo e($template['template_description'] ?? ''); ?></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" id="cancel-save-btn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Template configuration
        let templateConfig = <?php echo json_encode($templateConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const cvData = <?php echo json_encode($cvData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const profile = <?php echo json_encode($profile, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const templateId = <?php echo json_encode($templateId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        // Initialize drag and drop
        initializeDragAndDrop();
        
        // Initialize style controls
        initializeStyleControls();
        
        // Initialize preview
        updatePreview();

        function initializeDragAndDrop() {
            // Load SortableJS from CDN
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js';
            script.onload = () => {
                // Initialize sortable for section list
                const sectionList = document.getElementById('available-sections');
                if (sectionList && typeof Sortable !== 'undefined') {
                    new Sortable(sectionList, {
                        animation: 150,
                        handle: '.section-card',
                        onEnd: function(evt) {
                            // Update section order in config
                            const sections = Array.from(sectionList.querySelectorAll('.section-card'));
                            sections.forEach((card, index) => {
                                const sectionId = card.dataset.sectionId;
                                const section = templateConfig.sections.find(s => s.id === sectionId);
                                if (section) {
                                    section.order = index;
                                }
                            });
                            // Re-sort config sections
                            templateConfig.sections.sort((a, b) => a.order - b.order);
                            updatePreview();
                        }
                    });
                }
            };
            document.head.appendChild(script);
        }

        function initializeStyleControls() {
            // Layout
            document.getElementById('layout-select').addEventListener('change', (e) => {
                templateConfig.layout = e.target.value;
                updatePreview();
            });
            
            // Colors
            ['header', 'accent', 'text', 'muted', 'background'].forEach(color => {
                const input = document.getElementById(`color-${color}`);
                input.addEventListener('change', (e) => {
                    templateConfig.styling.colors[color] = e.target.value;
                    updatePreview();
                });
            });
            
            // Fonts
            ['heading', 'body'].forEach(font => {
                const input = document.getElementById(`font-${font}`);
                input.addEventListener('change', (e) => {
                    templateConfig.styling.fonts[font] = e.target.value;
                    updatePreview();
                });
            });
            
            // Spacing
            ['section', 'item'].forEach(spacing => {
                const input = document.getElementById(`spacing-${spacing}`);
                const valueSpan = document.getElementById(`spacing-${spacing}-value`);
                input.addEventListener('input', (e) => {
                    const value = parseInt(e.target.value);
                    templateConfig.styling.spacing[spacing] = value;
                    valueSpan.textContent = value;
                    updatePreview();
                });
            });
            
            // Section toggles
            document.querySelectorAll('.section-toggle').forEach(toggle => {
                toggle.addEventListener('change', (e) => {
                    const sectionId = e.target.dataset.sectionId;
                    const section = templateConfig.sections.find(s => s.id === sectionId);
                    if (section) {
                        section.enabled = e.target.checked;
                        updatePreview();
                    }
                });
            });
        }

        function updatePreview() {
            // Send config to preview endpoint
            fetch('/api/generate-template-preview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    config: templateConfig,
                    cvData: cvData,
                    profile: profile
                })
            })
            .then(response => response.text())
            .then(html => {
                const iframe = document.getElementById('preview-iframe');
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.open();
                iframeDoc.write(html);
                iframeDoc.close();
                
                // Ensure iframe has proper height after content loads
                setTimeout(() => {
                    if (iframe.contentWindow) {
                        const height = iframe.contentWindow.document.body.scrollHeight;
                        iframe.style.height = Math.max(height, 600) + 'px';
                    }
                }, 100);
            })
            .catch(error => {
                console.error('Preview error:', error);
            });
        }

        // Save template
        document.getElementById('save-template-btn').addEventListener('click', () => {
            document.getElementById('save-modal').classList.remove('hidden');
        });
        
        document.getElementById('cancel-save-btn').addEventListener('click', () => {
            document.getElementById('save-modal').classList.add('hidden');
        });
        
        document.getElementById('save-template-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const templateName = document.getElementById('template-name').value;
            const templateDescription = document.getElementById('template-description').value;
            
            const response = await fetch('/api/save-template-config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    template_id: templateId,
                    template_name: templateName,
                    template_description: templateDescription,
                    template_config: templateConfig
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = '/cv-template-customizer.php?success=' + encodeURIComponent('Template saved successfully');
            } else {
                alert('Error: ' + (result.error || 'Failed to save template'));
            }
        });

        // Refresh preview button
        document.getElementById('refresh-preview-btn').addEventListener('click', () => {
            updatePreview();
        });
    </script>
</body>
</html>

