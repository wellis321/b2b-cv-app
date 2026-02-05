<?php
/**
 * Content Editor - Single-page CV section editor
 * Replaces individual section pages with unified interface
 */

require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/section-guidance.php';
require_once __DIR__ . '/php/cv-variants.php';

requireAuth();

$userId = getUserId();
$error = getFlash('error');
$success = getFlash('success');

// Get CV variants for the user
$cvVariants = getUserCvVariants($userId);
$masterVariantId = getOrCreateMasterVariant($userId);

// Get all CV sections
$sections = getCvSections();

// Get current section from URL hash (default to first section)
// Note: Hash is handled client-side, but we can check query param for initial load
$currentSectionId = get('section', $sections[0]['id'] ?? 'professional-summary');

// Check for hash in URL for initial load
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#') !== false) {
    $hashPart = explode('#', $_SERVER['REQUEST_URI'])[1];
    $hashSection = explode('&', $hashPart)[0];
    if ($hashSection === 'profile' || $hashSection === 'jobs' || $hashSection === 'ai-tools' || $hashSection === 'cv-variants') {
        $currentSectionId = $hashSection;
    } elseif (in_array($hashSection, array_column($sections, 'id'))) {
        $currentSectionId = $hashSection;
    }
}

// Ensure current section is valid
$validSectionIds = array_column($sections, 'id');
$specialSections = ['profile', 'jobs', 'ai-tools', 'cv-variants'];
if (!in_array($currentSectionId, $validSectionIds) && !in_array($currentSectionId, $specialSections)) {
    $currentSectionId = $sections[0]['id'] ?? 'professional-summary';
}

// Get guidance for current section
$guidance = getSectionGuidance($currentSectionId);

// Get subscription context for limit checks
$subscriptionContext = getUserSubscriptionContext($userId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Content Editor | Simple CV Builder',
        'metaDescription' => 'Edit all sections of your CV in one place.',
        'canonicalUrl' => APP_URL . '/content-editor.php',
        'metaNoindex' => true,
    ]); ?>
    <style>
        /* Smooth scrolling for hash navigation */
        html {
            scroll-behavior: smooth;
        }
        /* Ensure body doesn't overflow */
        body {
            overflow-x: hidden;
        }
        /* Three-column layout */
        .content-editor-wrapper {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 64px);
        }
        .content-editor-grid {
            display: flex;
            flex: 1;
            overflow: hidden;
            min-height: 0;
        }
        .content-editor-grid aside,
        .content-editor-grid main {
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            max-height: 100%;
        }
        .content-editor-grid aside:first-child {
            min-width: 200px;
            max-width: 500px;
            position: relative;
        }
        .content-editor-grid main {
            flex: 1;
            min-width: 300px;
        }
        .content-editor-grid aside:last-child {
            min-width: 250px;
            max-width: 600px;
            position: relative;
        }
        .resize-handle {
            width: 4px;
            background-color: #e5e7eb;
            cursor: col-resize;
            flex-shrink: 0;
            align-self: stretch;
            position: relative;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }
        .resize-handle:hover {
            background-color: #3b82f6;
        }
        .resize-handle.active {
            background-color: #3b82f6;
        }
        .resize-handle::before {
            content: '';
            position: absolute;
            left: -2px;
            right: -2px;
            top: 0;
            bottom: 0;
        }
        /* Collapsed sidebar: hide content, show only expand tab */
        .content-editor-sidebar.collapsed {
            width: 0 !important;
            min-width: 0 !important;
            max-width: 0 !important;
            overflow: hidden !important;
            border-width: 0 !important;
            transition: width 0.2s ease, min-width 0.2s ease;
        }
        /* Expand tab when sidebar is collapsed: wider handle, click to expand */
        .resize-handle.collapsed-tab {
            width: 24px !important;
            min-width: 24px;
            background-color: #e5e7eb;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .resize-handle.collapsed-tab:hover {
            background-color: #d1d5db;
        }
        .resize-handle.collapsed-tab svg {
            width: 14px;
            height: 14px;
            color: #6b7280;
        }
        /* Toggle button on resize handle – near top for consistent, accessible placement */
        .resize-handle .handle-toggle-wrap {
            position: absolute;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            pointer-events: none;
        }
        .resize-handle .handle-toggle-wrap .sidebar-toggle-btn {
            pointer-events: auto;
            width: 28px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: #e5e7eb;
            color: #4b5563;
            cursor: pointer;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: background-color 0.15s, color 0.15s;
        }
        .resize-handle .handle-toggle-wrap .sidebar-toggle-btn:hover {
            background: #d1d5db;
            color: #1f2937;
        }
        .resize-handle .handle-toggle-wrap .sidebar-toggle-btn:focus {
            outline: none;
            box-shadow: 0 0 0 2px #3b82f6;
        }
        .resize-handle .handle-toggle-wrap .sidebar-toggle-btn svg {
            width: 16px;
            height: 16px;
        }
        /* When handle is the collapsed tab, toggle stays near top */
        .resize-handle.collapsed-tab .handle-toggle-wrap {
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
        }
        @media (max-width: 1024px) {
            .content-editor-grid {
                grid-template-columns: 1fr;
            }
            .content-editor-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>
    
    <?php if ($error): ?>
        <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full">
            <div class="bg-red-50 border border-red-200 rounded-md p-4 shadow-lg">
                <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full">
            <div class="bg-green-50 border border-green-200 rounded-md p-4 shadow-lg">
                <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- CV Navigation Bar -->
    <?php partial('content-editor/cv-nav-bar', [
        'cvVariants' => $cvVariants,
        'masterVariantId' => $masterVariantId
    ]); ?>
    
    <!-- Generate CV Modal -->
    <?php partial('content-editor/generate-cv-modal'); ?>

    <!-- Notification / Confirm modal (used by tailor section, delete, etc.) -->
    <?php partial('content-editor/notification-modal'); ?>

    <div class="content-editor-wrapper">
        <div class="content-editor-grid" id="content-editor-grid">
            <!-- Left Sidebar (section nav) – use toggle on handle to open/close -->
            <aside class="content-editor-sidebar border-r border-gray-200" id="left-sidebar" style="width: 280px;" data-sidebar="left">
                <?php partial('content-editor/section-sidebar', [
                    'sections' => $sections,
                    'currentSectionId' => $currentSectionId
                ]); ?>
            </aside>

            <!-- Resize Handle 1 – drag to resize; use toggle button to open/close left sidebar -->
            <div class="resize-handle" id="resize-handle-1" data-handle-for="left" title="Drag to resize. Use the toggle to open or close section nav."></div>

            <!-- Center Content Area -->
            <main class="bg-white" id="main-content">
                <div id="section-content" class="p-6">
                    <div class="max-w-3xl mx-auto">
                        <div class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="mt-4 text-gray-500">Loading section...</p>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Resize Handle 2 – drag to resize; use toggle button to open/close right sidebar -->
            <div class="resize-handle" id="resize-handle-2" data-handle-for="right" title="Drag to resize. Use the toggle to open or close suggestions panel."></div>

            <!-- Right Sidebar (guidance) – use toggle on handle to open/close -->
            <aside class="content-editor-sidebar border-l border-gray-200" id="right-sidebar" style="width: 320px;" data-sidebar="right">
                <?php partial('content-editor/guidance-panel', ['guidance' => $guidance]); ?>
            </aside>
        </div>
    </div>

    <script>
        // Pass data to JavaScript
        window.contentEditorData = {
            currentSectionId: <?php echo json_encode($currentSectionId); ?>,
            sections: <?php echo json_encode($sections); ?>,
            csrfToken: <?php echo json_encode(csrfToken()); ?>,
            csrfTokenName: <?php echo json_encode(CSRF_TOKEN_NAME); ?>,
            userId: <?php echo json_encode($userId); ?>,
            subscriptionContext: <?php echo json_encode($subscriptionContext); ?>
        };
        // Register pass-through service worker so app requests are not broken by other SWs (e.g. WebLLM)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js', { scope: '/' }).then(function () {
                return navigator.serviceWorker.ready;
            }).catch(function () {});
        }
    </script>
    <script src="/js/model-cache-manager.js?v=<?php echo time(); ?>"></script>
    <script src="/js/browser-ai-service.js?v=<?php echo time(); ?>"></script>
    <script src="/js/markdown-editor.js?v=<?php echo time(); ?>"></script>
    <script src="/js/content-editor.js?v=<?php echo time(); ?>"></script>
    <script src="/js/resizable-panes.js?v=<?php echo time(); ?>"></script>
    <script>
    // Enhance markdown rendering with marked.js
    if (typeof marked !== 'undefined') {
        document.querySelectorAll('.markdown-content').forEach(function(el) {
            const originalHtml = el.innerHTML;
            try {
                const rendered = marked.parse(originalHtml, { breaks: true, gfm: true });
                el.innerHTML = rendered;
            } catch (e) {
                console.warn('Markdown parsing failed:', e);
            }
        });
    }
    </script>
    <?php partial('footer'); ?>
</body>
</html>
