<?php
/**
 * Agency Settings Page
 * Organisation branding, preferences, and configuration
 */

require_once __DIR__ . '/../php/helpers.php';

// Load encryption utilities
require_once __DIR__ . '/../php/encryption.php';

// Require authentication and admin access
$org = requireOrganisationAccess('admin');

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get full organisation details
$organisation = getOrganisationById($org['organisation_id']);

// Handle POST actions
if (isPost()) {
    if (!verifyCsrfToken(post(CSRF_TOKEN_NAME))) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect('/agency/settings.php');
    }

    $action = post('action');

    // Update general settings
    if ($action === 'update_general') {
        $name = sanitizeInput(post('name'));
        $slug = sanitizeInput(post('slug'));

        // Validate name
        if (empty($name) || strlen($name) < 2) {
            setFlash('error', 'Organisation name must be at least 2 characters.');
            redirect('/agency/settings.php');
        }

        // Validate and format slug
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug));
        if (strlen($slug) < 3) {
            setFlash('error', 'URL slug must be at least 3 characters and contain only letters, numbers, and hyphens.');
            redirect('/agency/settings.php');
        }

        // Check if slug is taken (by another organisation)
        $existingSlug = db()->fetchOne(
            "SELECT id FROM organisations WHERE slug = ? AND id != ?",
            [$slug, $org['organisation_id']]
        );

        if ($existingSlug) {
            setFlash('error', 'This URL slug is already taken. Please choose another.');
            redirect('/agency/settings.php');
        }

        try {
            db()->update('organisations',
                [
                    'name' => $name,
                    'slug' => $slug,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$org['organisation_id']]
            );

            logActivity('organisation.settings_updated', null, ['type' => 'general']);

            setFlash('success', 'Organisation settings updated successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to update settings. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Update branding
    if ($action === 'update_branding') {
        $primaryColour = sanitizeInput(post('primary_colour'));
        $secondaryColour = sanitizeInput(post('secondary_colour'));

        // Validate colours (must be valid hex codes)
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $primaryColour)) {
            $primaryColour = '#4338ca';
        }
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $secondaryColour)) {
            $secondaryColour = '#7e22ce';
        }

        try {
            db()->update('organisations',
                [
                    'primary_colour' => $primaryColour,
                    'secondary_colour' => $secondaryColour,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$org['organisation_id']]
            );

            logActivity('organisation.branding_updated');

            setFlash('success', 'Branding updated successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to update branding. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Update logo
    if ($action === 'update_logo' && isset($_FILES['logo'])) {
        $file = $_FILES['logo'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowedTypes)) {
                setFlash('error', 'Logo must be a JPEG, PNG, GIF, or WebP image.');
                redirect('/agency/settings.php');
            }

            if ($file['size'] > $maxSize) {
                setFlash('error', 'Logo must be less than 2MB.');
                redirect('/agency/settings.php');
            }

            try {
                $result = uploadFile($file, 'organisation-logos/' . $org['organisation_id']);

                if ($result['success']) {
                    // Delete old logo if exists
                    if ($organisation['logo_url']) {
                        $oldPath = str_replace('/api/storage-proxy.php?path=', '', $organisation['logo_url']);
                        deleteFile($oldPath);
                    }

                    db()->update('organisations',
                        [
                            'logo_url' => $result['url'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ],
                        'id = ?',
                        [$org['organisation_id']]
                    );

                    logActivity('organisation.logo_updated');

                    setFlash('success', 'Logo uploaded successfully.');
                } else {
                    setFlash('error', 'Failed to upload logo: ' . ($result['error'] ?? 'Unknown error'));
                }
            } catch (Exception $e) {
                setFlash('error', 'Failed to upload logo. Please try again.');
            }
        } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            setFlash('error', 'Error uploading file. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Remove logo
    if ($action === 'remove_logo') {
        try {
            if ($organisation['logo_url']) {
                $oldPath = str_replace('/api/storage-proxy.php?path=', '', $organisation['logo_url']);
                deleteFile($oldPath);
            }

            db()->update('organisations',
                [
                    'logo_url' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$org['organisation_id']]
            );

            logActivity('organisation.logo_removed');

            setFlash('success', 'Logo removed successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to remove logo. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Request candidate limit increase
    if ($action === 'request_candidate_increase') {
        $requestedLimit = (int)post('requested_limit');
        $reason = sanitizeInput(post('reason'));
        
        if ($requestedLimit <= $organisation['max_candidates']) {
            setFlash('error', 'Requested limit must be greater than current limit.');
            redirect('/agency/settings.php');
        }
        
        $result = createLimitIncreaseRequest($org['organisation_id'], 'candidates', $requestedLimit, $reason);
        
        if ($result['success']) {
            setFlash('success', 'Your request has been submitted. A super admin will review it shortly.');
        } else {
            setFlash('error', $result['error']);
        }
        
        redirect('/agency/settings.php');
    }
    
    // Request team member limit increase
    if ($action === 'request_team_increase') {
        $requestedLimit = (int)post('requested_limit');
        $reason = sanitizeInput(post('reason'));
        
        if ($requestedLimit <= $organisation['max_team_members']) {
            setFlash('error', 'Requested limit must be greater than current limit.');
            redirect('/agency/settings.php');
        }
        
        $result = createLimitIncreaseRequest($org['organisation_id'], 'team_members', $requestedLimit, $reason);
        
        if ($result['success']) {
            setFlash('success', 'Your request has been submitted. A super admin will review it shortly.');
        } else {
            setFlash('error', $result['error']);
        }
        
        redirect('/agency/settings.php');
    }

    // Update candidate settings
    if ($action === 'update_candidate_settings') {
        $defaultVisibility = sanitizeInput(post('default_cv_visibility'));
        $allowSelfReg = post('allow_candidate_self_registration') ? 1 : 0;
        $requireApproval = post('require_candidate_approval') ? 1 : 0;

        if (!in_array($defaultVisibility, ['private', 'organisation', 'public'])) {
            $defaultVisibility = 'organisation';
        }

        try {
            db()->update('organisations',
                [
                    'default_cv_visibility' => $defaultVisibility,
                    'allow_candidate_self_registration' => $allowSelfReg,
                    'require_candidate_approval' => $requireApproval,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$org['organisation_id']]
            );

            logActivity('organisation.candidate_settings_updated');

            setFlash('success', 'Candidate settings updated successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to update settings. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Update email settings
    if ($action === 'update_email_settings') {
        $organisationEmail = sanitizeInput(post('organisation_email'));
        $organisationEmailName = sanitizeInput(post('organisation_email_name'));

        // Validate email if provided
        if (!empty($organisationEmail) && !filter_var($organisationEmail, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Please enter a valid email address.');
            redirect('/agency/settings.php');
        }

        try {
            db()->update('organisations',
                [
                    'organisation_email' => $organisationEmail ?: null,
                    'organisation_email_name' => $organisationEmailName ?: null,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$org['organisation_id']]
            );

            logActivity('organisation.email_settings_updated');

            setFlash('success', 'Email settings updated successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to update email settings. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Update organisation AI settings
    if ($action === 'update_ai_settings') {
        // Only owners and admins can configure organisation AI
        if (!in_array($org['role'], ['owner', 'admin'])) {
            setFlash('error', 'You do not have permission to configure organisation AI settings.');
            redirect('/agency/settings.php');
        }

        $aiService = post('org_ai_service_preference');
        $aiEnabled = post('org_ai_enabled') ? 1 : 0;
        $ollamaUrl = post('org_ollama_base_url');
        $ollamaModel = post('org_ollama_model');
        $openaiKey = post('org_openai_api_key');
        $anthropicKey = post('org_anthropic_api_key');
        $geminiKey = post('org_gemini_api_key');
        $grokKey = post('org_grok_api_key');
        $browserModel = post('org_browser_ai_model');

        // Validate inputs based on selected service
        if ($aiEnabled && $aiService === 'ollama') {
            if (empty($ollamaUrl)) {
                setFlash('error', 'Ollama base URL is required when using Ollama.');
                redirect('/agency/settings.php');
            }
            if (empty($ollamaModel)) {
                setFlash('error', 'Ollama model name is required when using Ollama.');
                redirect('/agency/settings.php');
            }
            if (!filter_var($ollamaUrl, FILTER_VALIDATE_URL)) {
                setFlash('error', 'Invalid Ollama base URL format.');
                redirect('/agency/settings.php');
            }
        } elseif ($aiEnabled && $aiService === 'openai' && !empty($openaiKey)) {
            if (!validateApiKeyFormat('openai', $openaiKey)) {
                setFlash('error', 'Invalid OpenAI API key format.');
                redirect('/agency/settings.php');
            }
        } elseif ($aiEnabled && $aiService === 'anthropic' && !empty($anthropicKey)) {
            if (!validateApiKeyFormat('anthropic', $anthropicKey)) {
                setFlash('error', 'Invalid Anthropic API key format.');
                redirect('/agency/settings.php');
            }
        } elseif ($aiEnabled && $aiService === 'gemini' && !empty($geminiKey)) {
            if (strlen($geminiKey) < 20) {
                setFlash('error', 'Invalid Gemini API key format.');
                redirect('/agency/settings.php');
            }
        } elseif ($aiEnabled && $aiService === 'grok' && !empty($grokKey)) {
            if (strlen($grokKey) < 20) {
                setFlash('error', 'Invalid Grok API key format.');
                redirect('/agency/settings.php');
            }
        } elseif ($aiEnabled && $aiService === 'browser' && empty($browserModel)) {
            setFlash('error', 'Browser AI model selection is required.');
            redirect('/agency/settings.php');
        }

        try {
            $updateData = [
                'org_ai_enabled' => $aiEnabled,
                'org_ai_service_preference' => $aiEnabled ? ($aiService ?: null) : null,
                'org_ollama_base_url' => ($aiEnabled && $aiService === 'ollama') ? $ollamaUrl : null,
                'org_ollama_model' => ($aiEnabled && $aiService === 'ollama') ? $ollamaModel : null,
                'org_browser_ai_model' => ($aiEnabled && $aiService === 'browser') ? $browserModel : null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle API keys - only update if provided (keep existing if empty)
            if ($aiEnabled && $aiService === 'openai' && !empty($openaiKey)) {
                $encryptedKey = encryptApiKey($openaiKey);
                if ($encryptedKey === false) {
                    setFlash('error', 'Failed to encrypt API key. Please try again.');
                    redirect('/agency/settings.php');
                }
                $updateData['org_openai_api_key'] = $encryptedKey;
            } elseif (!$aiEnabled || $aiService !== 'openai') {
                $updateData['org_openai_api_key'] = null;
            }

            if ($aiEnabled && $aiService === 'anthropic' && !empty($anthropicKey)) {
                $encryptedKey = encryptApiKey($anthropicKey);
                if ($encryptedKey === false) {
                    setFlash('error', 'Failed to encrypt API key. Please try again.');
                    redirect('/agency/settings.php');
                }
                $updateData['org_anthropic_api_key'] = $encryptedKey;
            } elseif (!$aiEnabled || $aiService !== 'anthropic') {
                $updateData['org_anthropic_api_key'] = null;
            }

            if ($aiEnabled && $aiService === 'gemini' && !empty($geminiKey)) {
                $encryptedKey = encryptApiKey($geminiKey);
                if ($encryptedKey === false) {
                    setFlash('error', 'Failed to encrypt API key. Please try again.');
                    redirect('/agency/settings.php');
                }
                $updateData['org_gemini_api_key'] = $encryptedKey;
            } elseif (!$aiEnabled || $aiService !== 'gemini') {
                $updateData['org_gemini_api_key'] = null;
            }

            if ($aiEnabled && $aiService === 'grok' && !empty($grokKey)) {
                $encryptedKey = encryptApiKey($grokKey);
                if ($encryptedKey === false) {
                    setFlash('error', 'Failed to encrypt API key. Please try again.');
                    redirect('/agency/settings.php');
                }
                $updateData['org_grok_api_key'] = $encryptedKey;
            } elseif (!$aiEnabled || $aiService !== 'grok') {
                $updateData['org_grok_api_key'] = null;
            }

            db()->update('organisations', $updateData, 'id = ?', [$org['organisation_id']]);

            logActivity('organisation.ai_settings_updated');

            setFlash('success', 'Organisation AI settings updated successfully.');
        } catch (Exception $e) {
            error_log("Error updating organisation AI settings: " . $e->getMessage());
            setFlash('error', 'Failed to update AI settings. Please try again.');
        }

        redirect('/agency/settings.php');
    }

    // Update custom homepage
    if ($action === 'update_custom_homepage') {
        $customHomepageEnabled = post('custom_homepage_enabled') === '1';
        $customHomepageHtml = post('custom_homepage_html', '');
        $customHomepageCss = post('custom_homepage_css', '');

        // Validate HTML/CSS length (reasonable limits)
        if (!empty($customHomepageHtml) && strlen($customHomepageHtml) > 500000) {
            setFlash('error', 'Custom HTML is too large. Maximum 500KB allowed.');
            redirect('/agency/settings.php');
        }

        if (!empty($customHomepageCss) && strlen($customHomepageCss) > 100000) {
            setFlash('error', 'Custom CSS is too large. Maximum 100KB allowed.');
            redirect('/agency/settings.php');
        }

        try {
            db()->update('organisations',
                [
                    'custom_homepage_enabled' => $customHomepageEnabled ? 1 : 0,
                    'custom_homepage_html' => !empty($customHomepageHtml) ? $customHomepageHtml : null,
                    'custom_homepage_css' => !empty($customHomepageCss) ? $customHomepageCss : null,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$org['organisation_id']]
            );

            logActivity('organisation.custom_homepage_updated');

            setFlash('success', 'Custom homepage settings updated successfully.');
        } catch (Exception $e) {
            error_log("Error updating custom homepage: " . $e->getMessage());
            setFlash('error', 'Failed to update custom homepage. Please try again.');
        }

        redirect('/agency/settings.php');
    }
}

// Refresh organisation data after potential updates
$organisation = getOrganisationById($org['organisation_id']);

// Check if user is owner or admin (for conditional UI)
$isOwnerOrAdmin = in_array($org['role'], ['owner', 'admin']);

// Load organisation AI settings
$orgAiSettings = [
    'org_ai_enabled' => $organisation['org_ai_enabled'] ?? false,
    'org_ai_service_preference' => $organisation['org_ai_service_preference'] ?? null,
    'org_ollama_base_url' => $organisation['org_ollama_base_url'] ?? null,
    'org_ollama_model' => $organisation['org_ollama_model'] ?? null,
    'org_browser_ai_model' => $organisation['org_browser_ai_model'] ?? null,
    'has_openai_key' => !empty($organisation['org_openai_api_key'] ?? null),
    'has_anthropic_key' => !empty($organisation['org_anthropic_api_key'] ?? null),
    'has_gemini_key' => !empty($organisation['org_gemini_api_key'] ?? null),
    'has_grok_key' => !empty($organisation['org_grok_api_key'] ?? null),
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Settings | ' . e($org['organisation_name']),
        'metaDescription' => 'Configure your organisation\'s settings and branding.',
        'canonicalUrl' => APP_URL . '/agency/settings.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('agency/header'); ?>

    <main id="main-content" class="py-6">
        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Organisation Settings</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage your organisation's profile, branding, and preferences.
            </p>
        </div>

        <!-- Two Column Layout: Sidebar + Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-8">
            <!-- Sticky Sidebar Navigation -->
            <aside class="lg:w-64 flex-shrink-0">
                <div class="sticky top-24">
                    <nav class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wide">Settings</h2>
                        <ul class="space-y-1">
                            <li>
                                <a href="#general-settings" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">General Settings</a>
                            </li>
                            <li>
                                <a href="#logo" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Logo</a>
                            </li>
                            <li>
                                <a href="#branding-colours" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Branding Colours</a>
                            </li>
                            <li>
                                <a href="#custom-homepage" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Custom Homepage</a>
                            </li>
                            <li>
                                <a href="#limit-increase" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Limit Increase</a>
                            </li>
                            <li>
                                <a href="#candidate-settings" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Candidate Settings</a>
                            </li>
                            <li>
                                <a href="#email-settings" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Email Settings</a>
                            </li>
                            <?php if ($isOwnerOrAdmin): ?>
                            <li>
                                <a href="#organisation-ai" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Organisation AI</a>
                            </li>
                            <?php endif; ?>
                            <?php if ($org['role'] === 'owner'): ?>
                            <li>
                                <a href="#danger-zone" class="block px-3 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors">Danger Zone</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 min-w-0 space-y-6">
            <!-- General Settings -->
            <div id="general-settings" class="bg-white shadow rounded-lg scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">General Settings</h2>
                    <form method="POST">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="action" value="update_general">

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name" class="block text-base font-semibold text-gray-900 mb-3">Organisation Name</label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="<?php echo e($organisation['name']); ?>"
                                       required
                                       minlength="2"
                                       class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                            </div>

                            <div>
                                <label for="slug" class="block text-base font-semibold text-gray-900 mb-3">URL Slug</label>
                                <div class="flex rounded-lg shadow-sm">
                                    <span class="inline-flex items-center rounded-l-lg border-2 border-r-0 border-gray-400 bg-gray-50 px-4 py-3 text-base text-gray-500">
                                        <?php echo e(parse_url(APP_URL, PHP_URL_HOST)); ?>/agency/
                                    </span>
                                    <input type="text"
                                           name="slug"
                                           id="slug"
                                           value="<?php echo e($organisation['slug']); ?>"
                                           required
                                           pattern="[a-z0-9\-]+"
                                           minlength="3"
                                           class="block w-full flex-1 rounded-r-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                </div>
                                <p class="mt-2 text-sm text-gray-600 font-medium">Only lowercase letters, numbers, and hyphens.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logo -->
            <div id="logo" class="bg-white shadow rounded-lg scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Organisation Logo</h2>

                    <div class="flex items-start space-x-6">
                        <div class="flex-shrink-0">
                            <?php if ($organisation['logo_url']): ?>
                                <img src="<?php echo e($organisation['logo_url']); ?>"
                                     alt="<?php echo e($organisation['name']); ?>"
                                     class="h-24 w-24 object-contain rounded-lg border border-gray-200">
                            <?php else: ?>
                                <div class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1">
                            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="update_logo">

                                <div>
                                    <label for="logo" class="block text-base font-semibold text-gray-900 mb-3">Upload new logo</label>
                                    <input type="file"
                                           name="logo"
                                           id="logo"
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-base file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-2 text-sm text-gray-600 font-medium">JPEG, PNG, GIF, or WebP. Max 2MB.</p>
                                </div>

                                <div class="flex space-x-3">
                                    <button type="submit"
                                            class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                        Upload Logo
                                    </button>
                                    <?php if ($organisation['logo_url']): ?>
                                        <button type="submit"
                                                name="action"
                                                value="remove_logo"
                                                onclick="return confirm('Are you sure you want to remove the logo?');"
                                                class="inline-flex justify-center rounded-lg bg-white px-6 py-3 text-base font-bold text-gray-900 shadow-lg ring-2 ring-inset ring-gray-400 hover:bg-gray-50 hover:ring-gray-500 transition-all">
                                            Remove Logo
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Colours -->
            <div id="branding-colours" class="bg-white shadow rounded-lg scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Branding Colours</h2>
                    <p class="text-sm text-gray-500 mb-4">These colours will be used in candidate CVs and branded materials.</p>

                    <form method="POST">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="action" value="update_branding">

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="primary_colour" class="block text-base font-semibold text-gray-900 mb-3">Primary Colour</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color"
                                           name="primary_colour"
                                           id="primary_colour"
                                           value="<?php echo e($organisation['primary_colour'] ?? '#4338ca'); ?>"
                                           class="h-12 w-24 rounded-lg border-2 border-gray-400 cursor-pointer">
                                    <input type="text"
                                           value="<?php echo e($organisation['primary_colour'] ?? '#4338ca'); ?>"
                                           readonly
                                           class="block w-32 rounded-lg border-2 border-gray-400 bg-gray-50 px-4 py-3 text-base font-medium text-gray-500">
                                </div>
                            </div>

                            <div>
                                <label for="secondary_colour" class="block text-base font-semibold text-gray-900 mb-3">Secondary Colour</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color"
                                           name="secondary_colour"
                                           id="secondary_colour"
                                           value="<?php echo e($organisation['secondary_colour'] ?? '#7e22ce'); ?>"
                                           class="h-12 w-24 rounded-lg border-2 border-gray-400 cursor-pointer">
                                    <input type="text"
                                           value="<?php echo e($organisation['secondary_colour'] ?? '#7e22ce'); ?>"
                                           readonly
                                           class="block w-32 rounded-lg border-2 border-gray-400 bg-gray-50 px-4 py-3 text-base font-medium text-gray-500">
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mt-6">
                            <p class="text-sm font-medium text-gray-700 mb-2">Preview</p>
                            <div class="h-4 rounded-full" style="background: linear-gradient(to right, <?php echo e($organisation['primary_colour'] ?? '#4338ca'); ?>, <?php echo e($organisation['secondary_colour'] ?? '#7e22ce'); ?>);"></div>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                Save Colours
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Custom Homepage -->
            <div id="custom-homepage" class="bg-white shadow rounded-lg scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Custom Homepage</h2>
                        <a href="/agency/custom-homepage-guide.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            View Guide
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 mb-6">
                        Create a fully customised homepage for your organisation's public page at <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">/agency/<?php echo e($organisation['slug']); ?></code>. 
                        You can use HTML and CSS to design your own unique landing page. 
                        <a href="/agency/custom-homepage-guide.php" class="text-blue-600 hover:text-blue-800 underline">Read the full guide</a> for detailed instructions and examples.
                    </p>

                    <form method="POST">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="action" value="update_custom_homepage">

                        <!-- Enable/Disable Toggle -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox"
                                       name="custom_homepage_enabled"
                                       value="1"
                                       <?php echo (!empty($organisation['custom_homepage_enabled'])) ? 'checked' : ''; ?>
                                       class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-3 text-base font-medium text-gray-900">
                                    Enable Custom Homepage
                                </span>
                            </label>
                            <p class="mt-2 text-sm text-gray-500">
                                When enabled, your custom HTML/CSS will be used instead of the default template.
                            </p>
                        </div>

                        <!-- AI Template Generation -->
                        <div class="mb-8 p-6 bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">✨ Generate with AI</h3>
                            <p class="text-sm text-gray-700 mb-4">
                                Find a website template you like? Enter the URL below and our AI will adapt it for your homepage. 
                                Or describe your ideal design and we'll create it for you.
                            </p>
                            
                            <div id="ai-template-generator" class="space-y-4">
                                <!-- Template URL Input -->
                                <div>
                                    <label for="template_reference_url" class="block text-sm font-medium text-gray-900 mb-2">
                                        Template URL (Optional)
                                    </label>
                                    <input type="url"
                                           id="template_reference_url"
                                           placeholder="https://example.com/beautiful-template"
                                           class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                    <p class="mt-1 text-xs text-gray-500">Paste a URL to a website or template you'd like to use as inspiration</p>
                                </div>
                                
                                <!-- Description Input -->
                                <div>
                                    <label for="template_description" class="block text-sm font-medium text-gray-900 mb-2">
                                        Description (Optional)
                                    </label>
                                    <textarea id="template_description"
                                              rows="3"
                                              placeholder="Describe your ideal homepage design, or leave blank to use the template URL as reference"
                                              class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"></textarea>
                                </div>
                                
                                <!-- Generate Button -->
                                <div>
                                    <button type="button"
                                            id="generate-template-btn"
                                            class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-semibold transition-colors focus:outline-none focus:ring-4 focus:ring-purple-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Generate Homepage with AI
                                    </button>
                                    <span id="generate-template-status" class="ml-3 text-sm text-gray-600 hidden"></span>
                                </div>
                                
                                <div id="generate-template-error" class="hidden p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700"></div>
                            </div>
                        </div>

                        <!-- HTML Editor -->
                        <div class="mb-6">
                            <label for="custom_homepage_html" class="block text-base font-semibold text-gray-900 mb-3">
                                Custom HTML
                            </label>
                            <textarea name="custom_homepage_html"
                                      id="custom_homepage_html"
                                      rows="15"
                                      class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-sm font-mono text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                      placeholder="<!-- Enter your custom HTML here -->
<div class='hero-section'>
  <h1>{{organisation_name}}</h1>
  <p>Welcome to our organisation...</p>
  <p>We manage {{candidate_count}} candidates.</p>
</div>"><?php echo e($organisation['custom_homepage_html'] ?? ''); ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">
                                Enter your custom HTML. You can use placeholders like <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{organisation_name}}</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{primary_colour}}</code>, etc. Maximum 500KB.
                            </p>
                        </div>

                        <!-- CSS Editor -->
                        <div class="mb-6">
                            <label for="custom_homepage_css" class="block text-base font-semibold text-gray-900 mb-3">
                                Custom CSS
                            </label>
                            <textarea name="custom_homepage_css"
                                      id="custom_homepage_css"
                                      rows="10"
                                      class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-sm font-mono text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                      placeholder="/* Enter your custom CSS here */
.hero-section {
  background: linear-gradient(135deg, #4338ca 0%, #7e22ce 100%);
  padding: 4rem 2rem;
  color: white;
}"><?php echo e($organisation['custom_homepage_css'] ?? ''); ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">
                                Enter your custom CSS styles. Maximum 100KB.
                            </p>
                        </div>

                        <!-- Preview Link -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Preview your homepage:</strong>
                            </p>
                            <a href="/agency/<?php echo e($organisation['slug']); ?>" 
                               target="_blank"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Public Page →
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Help Text -->
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Available Placeholders</h3>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{organisation_name}}</code> - Organisation name</li>
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{organisation_slug}}</code> - URL slug</li>
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{logo_url}}</code> - Logo URL (empty if not set)</li>
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{primary_colour}}</code> - Primary brand colour (hex code)</li>
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{secondary_colour}}</code> - Secondary brand colour (hex code)</li>
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{candidate_count}}</code> - Number of candidates (formatted with commas)</li>
                                <li><code class="bg-white px-1 py-0.5 rounded text-xs">{{public_url}}</code> - Full public page URL</li>
                            </ul>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                Save Custom Homepage
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Limit Increase Requests -->
            <div id="limit-increase" class="bg-white shadow rounded-lg scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Request Limit Increase</h2>
                    <p class="text-sm text-gray-500 mb-4">Request an increase in your candidate or team member limits. Super admins will review your request.</p>
                    
                    <?php
                    $pendingRequests = getOrganisationLimitRequests($org['organisation_id'], 'pending');
                    $currentCandidates = getOrganisationCandidateCount($org['organisation_id']);
                    $currentTeamMembers = getOrganisationTeamMemberCount($org['organisation_id']);
                    ?>
                    
                    <!-- Current Limits -->
                    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Candidates</p>
                                    <p class="mt-1 text-2xl font-semibold text-gray-900">
                                        <?php echo $currentCandidates; ?> / <?php echo $organisation['max_candidates']; ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        <?php 
                                        $percent = $organisation['max_candidates'] > 0 ? ($currentCandidates / $organisation['max_candidates']) * 100 : 0;
                                        echo $percent >= 90 ? 'bg-red-100 text-red-800' : ($percent >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                        ?>">
                                        <?php echo round($percent); ?>% used
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Team Members</p>
                                    <p class="mt-1 text-2xl font-semibold text-gray-900">
                                        <?php echo $currentTeamMembers; ?> / <?php echo $organisation['max_team_members']; ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        <?php 
                                        $percent = $organisation['max_team_members'] > 0 ? ($currentTeamMembers / $organisation['max_team_members']) * 100 : 0;
                                        echo $percent >= 90 ? 'bg-red-100 text-red-800' : ($percent >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                        ?>">
                                        <?php echo round($percent); ?>% used
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Requests -->
                    <?php if (!empty($pendingRequests)): ?>
                        <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                            <h3 class="text-sm font-medium text-yellow-800 mb-2">Pending Requests</h3>
                            <ul class="space-y-2">
                                <?php foreach ($pendingRequests as $req): ?>
                                    <li class="text-sm text-yellow-700">
                                        <strong><?php echo ucfirst(str_replace('_', ' ', $req['request_type'])); ?>:</strong>
                                        Request to increase from <?php echo $req['current_limit']; ?> to <?php echo $req['requested_limit']; ?>
                                        (submitted <?php echo date('j M Y', strtotime($req['created_at'])); ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    

                    <!-- Request Forms -->
                    <div class="space-y-6">
                        <!-- Request Candidate Increase -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Request Candidate Limit Increase</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="request_candidate_increase">
                                
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="candidate_limit" class="block text-base font-semibold text-gray-900 mb-3">Requested Limit</label>
                                        <input type="number" 
                                               name="requested_limit" 
                                               id="candidate_limit" 
                                               min="<?php echo $organisation['max_candidates'] + 1; ?>"
                                               value="<?php echo $organisation['max_candidates'] + 10; ?>"
                                               required
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <p class="mt-2 text-sm text-gray-600 font-medium">Current limit: <?php echo $organisation['max_candidates']; ?></p>
                                    </div>
                                    
                                    <div class="sm:col-span-2">
                                        <label for="candidate_reason" class="block text-base font-semibold text-gray-900 mb-3">Reason (Optional)</label>
                                        <textarea name="reason" 
                                                  id="candidate_reason" 
                                                  rows="3"
                                                  placeholder="Explain why you need this increase..."
                                                  class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y"></textarea>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" 
                                            class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                        Submit Request
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Request Team Member Increase -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Request Team Member Limit Increase</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="request_team_increase">
                                
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="team_limit" class="block text-base font-semibold text-gray-900 mb-3">Requested Limit</label>
                                        <input type="number" 
                                               name="requested_limit" 
                                               id="team_limit" 
                                               min="<?php echo $organisation['max_team_members'] + 1; ?>"
                                               value="<?php echo $organisation['max_team_members'] + 5; ?>"
                                               required
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <p class="mt-2 text-sm text-gray-600 font-medium">Current limit: <?php echo $organisation['max_team_members']; ?></p>
                                    </div>
                                    
                                    <div class="sm:col-span-2">
                                        <label for="team_reason" class="block text-base font-semibold text-gray-900 mb-3">Reason (Optional)</label>
                                        <textarea name="reason" 
                                                  id="team_reason" 
                                                  rows="3"
                                                  placeholder="Explain why you need this increase..."
                                                  class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y"></textarea>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" 
                                            class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                        Submit Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Request History -->
                    <?php
                    $allRequests = getOrganisationLimitRequests($org['organisation_id']);
                    if (!empty($allRequests)):
                    ?>
                        <div class="mt-8">
                            <h3 class="text-sm font-medium text-gray-900 mb-4">Request History</h3>
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($allRequests as $req): ?>
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    <?php echo ucfirst(str_replace('_', ' ', $req['request_type'])); ?>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo $req['current_limit']; ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo $req['requested_limit']; ?></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        <?php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'approved' => 'bg-green-100 text-green-800',
                                                            'denied' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800'
                                                        ];
                                                        echo $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>">
                                                        <?php echo ucfirst($req['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    <?php echo date('j M Y', strtotime($req['created_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Candidate Settings -->
            <div id="candidate-settings" class="bg-white shadow rounded-lg scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Candidate Settings</h2>

                    <form method="POST">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="action" value="update_candidate_settings">

                        <div class="space-y-6">
                            <div>
                                <label for="default_cv_visibility" class="block text-base font-semibold text-gray-900 mb-3">Default CV Visibility</label>
                                <select name="default_cv_visibility"
                                        id="default_cv_visibility"
                                        class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                    <option value="private" <?php echo $organisation['default_cv_visibility'] === 'private' ? 'selected' : ''; ?>>
                                        Private - Only the candidate can view
                                    </option>
                                    <option value="organisation" <?php echo $organisation['default_cv_visibility'] === 'organisation' ? 'selected' : ''; ?>>
                                        Organisation - Team members can view
                                    </option>
                                    <option value="public" <?php echo $organisation['default_cv_visibility'] === 'public' ? 'selected' : ''; ?>>
                                        Public - Anyone with the link can view
                                    </option>
                                </select>
                                <p class="mt-2 text-sm text-gray-600 font-medium">This sets the default visibility for new candidates. Visibility can be changed individually.</p>
                            </div>

                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input type="checkbox"
                                           name="allow_candidate_self_registration"
                                           id="allow_candidate_self_registration"
                                           value="1"
                                           <?php echo $organisation['allow_candidate_self_registration'] ? 'checked' : ''; ?>
                                           class="h-5 w-5 rounded border-2 border-gray-400 text-blue-600 focus:ring-4 focus:ring-blue-200 focus:ring-offset-0">
                                </div>
                                <div class="ml-3">
                                    <label for="allow_candidate_self_registration" class="text-base font-semibold text-gray-900">Allow Candidate Self-Registration</label>
                                    <p class="text-sm text-gray-600 font-medium">Candidates can register themselves using your organisation's registration link.</p>
                                </div>
                            </div>

                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input type="checkbox"
                                           name="require_candidate_approval"
                                           id="require_candidate_approval"
                                           value="1"
                                           <?php echo $organisation['require_candidate_approval'] ? 'checked' : ''; ?>
                                           class="h-5 w-5 rounded border-2 border-gray-400 text-blue-600 focus:ring-4 focus:ring-blue-200 focus:ring-offset-0">
                                </div>
                                <div class="ml-3">
                                    <label for="require_candidate_approval" class="text-base font-semibold text-gray-900">Require Candidate Approval</label>
                                    <p class="text-sm text-gray-600 font-medium">Self-registered candidates must be approved before they can complete their CV.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Organisation AI Settings (Owner/Admin only) -->
            <?php if (in_array($org['role'], ['owner', 'admin'])): ?>
            <div id="organisation-ai" class="bg-white shadow rounded-lg border-2 border-yellow-200 scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h2 class="text-lg font-medium text-gray-900">Organisation AI Settings</h2>
                    </div>
                    
                    <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                        <p class="text-sm font-medium text-yellow-800 mb-2">Important: API Costs</p>
                        <p class="text-sm text-yellow-700">
                            When enabled, all organisation members (candidates and team members) will automatically use this AI service. 
                            API costs will be billed to your organisation based on usage. Monitor usage regularly to avoid unexpected charges.
                        </p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="action" value="update_ai_settings">

                        <div class="space-y-6">
                            <!-- Enable/Disable Toggle -->
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input type="checkbox"
                                           name="org_ai_enabled"
                                           id="org_ai_enabled"
                                           value="1"
                                           <?php echo $orgAiSettings['org_ai_enabled'] ? 'checked' : ''; ?>
                                           class="h-5 w-5 rounded border-2 border-gray-400 text-blue-600 focus:ring-4 focus:ring-blue-200 focus:ring-offset-0">
                                </div>
                                <div class="ml-3">
                                    <label for="org_ai_enabled" class="text-base font-semibold text-gray-900">Enable Organisation AI</label>
                                    <p class="text-sm text-gray-600 font-medium">When enabled, all members can use AI features without individual configuration.</p>
                                </div>
                            </div>

                            <!-- AI Service Selection -->
                            <div id="ai-service-fields" style="display: <?php echo $orgAiSettings['org_ai_enabled'] ? 'block' : 'none'; ?>;">
                                <div>
                                    <label for="org_ai_service_preference" class="block text-base font-semibold text-gray-900 mb-3">AI Service</label>
                                    <select name="org_ai_service_preference"
                                            id="org_ai_service_preference"
                                            class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <option value="">Select AI Service...</option>
                                        <option value="openai" <?php echo $orgAiSettings['org_ai_service_preference'] === 'openai' ? 'selected' : ''; ?>>OpenAI (Paid)</option>
                                        <option value="anthropic" <?php echo $orgAiSettings['org_ai_service_preference'] === 'anthropic' ? 'selected' : ''; ?>>Anthropic Claude (Paid)</option>
                                        <option value="gemini" <?php echo $orgAiSettings['org_ai_service_preference'] === 'gemini' ? 'selected' : ''; ?>>Google Gemini (Paid)</option>
                                        <option value="grok" <?php echo $orgAiSettings['org_ai_service_preference'] === 'grok' ? 'selected' : ''; ?>>xAI Grok (Paid)</option>
                                        <option value="ollama" <?php echo $orgAiSettings['org_ai_service_preference'] === 'ollama' ? 'selected' : ''; ?>>Local Ollama (Free)</option>
                                        <option value="browser" <?php echo $orgAiSettings['org_ai_service_preference'] === 'browser' ? 'selected' : ''; ?>>Browser-Based AI (Free)</option>
                                    </select>
                                </div>

                                <!-- OpenAI Configuration -->
                                <div id="openai-fields" style="display: <?php echo $orgAiSettings['org_ai_service_preference'] === 'openai' ? 'block' : 'none'; ?>;" class="mt-4 space-y-4">
                                    <div>
                                        <label for="org_openai_api_key" class="block text-base font-semibold text-gray-900 mb-3">OpenAI API Key</label>
                                        <input type="password"
                                               name="org_openai_api_key"
                                               id="org_openai_api_key"
                                               placeholder="<?php echo $orgAiSettings['has_openai_key'] ? 'Key saved (leave blank to keep existing)' : 'sk-...'; ?>"
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <?php if ($orgAiSettings['has_openai_key']): ?>
                                            <p class="mt-2 text-sm text-green-600 font-medium">✓ API key is saved</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Anthropic Configuration -->
                                <div id="anthropic-fields" style="display: <?php echo $orgAiSettings['org_ai_service_preference'] === 'anthropic' ? 'block' : 'none'; ?>;" class="mt-4 space-y-4">
                                    <div>
                                        <label for="org_anthropic_api_key" class="block text-base font-semibold text-gray-900 mb-3">Anthropic API Key</label>
                                        <input type="password"
                                               name="org_anthropic_api_key"
                                               id="org_anthropic_api_key"
                                               placeholder="<?php echo $orgAiSettings['has_anthropic_key'] ? 'Key saved (leave blank to keep existing)' : 'sk-ant-...'; ?>"
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <?php if ($orgAiSettings['has_anthropic_key']): ?>
                                            <p class="mt-2 text-sm text-green-600 font-medium">✓ API key is saved</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Gemini Configuration -->
                                <div id="gemini-fields" style="display: <?php echo $orgAiSettings['org_ai_service_preference'] === 'gemini' ? 'block' : 'none'; ?>;" class="mt-4 space-y-4">
                                    <div>
                                        <label for="org_gemini_api_key" class="block text-base font-semibold text-gray-900 mb-3">Google Gemini API Key</label>
                                        <input type="password"
                                               name="org_gemini_api_key"
                                               id="org_gemini_api_key"
                                               placeholder="<?php echo $orgAiSettings['has_gemini_key'] ? 'Key saved (leave blank to keep existing)' : 'AIza...'; ?>"
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <?php if ($orgAiSettings['has_gemini_key']): ?>
                                            <p class="mt-2 text-sm text-green-600 font-medium">✓ API key is saved</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Grok Configuration -->
                                <div id="grok-fields" style="display: <?php echo $orgAiSettings['org_ai_service_preference'] === 'grok' ? 'block' : 'none'; ?>;" class="mt-4 space-y-4">
                                    <div>
                                        <label for="org_grok_api_key" class="block text-base font-semibold text-gray-900 mb-3">xAI Grok API Key</label>
                                        <input type="password"
                                               name="org_grok_api_key"
                                               id="org_grok_api_key"
                                               placeholder="<?php echo $orgAiSettings['has_grok_key'] ? 'Key saved (leave blank to keep existing)' : 'gsk-...'; ?>"
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                        <?php if ($orgAiSettings['has_grok_key']): ?>
                                            <p class="mt-2 text-sm text-green-600 font-medium">✓ API key is saved</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Ollama Configuration -->
                                <div id="ollama-fields" style="display: <?php echo $orgAiSettings['org_ai_service_preference'] === 'ollama' ? 'block' : 'none'; ?>;" class="mt-4 space-y-4">
                                    <div>
                                        <label for="org_ollama_base_url" class="block text-base font-semibold text-gray-900 mb-3">Ollama Base URL</label>
                                        <input type="text"
                                               name="org_ollama_base_url"
                                               id="org_ollama_base_url"
                                               value="<?php echo e($orgAiSettings['org_ollama_base_url'] ?? 'http://localhost:11434'); ?>"
                                               placeholder="http://localhost:11434"
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                    </div>
                                    <div>
                                        <label for="org_ollama_model" class="block text-base font-semibold text-gray-900 mb-3">Ollama Model</label>
                                        <input type="text"
                                               name="org_ollama_model"
                                               id="org_ollama_model"
                                               value="<?php echo e($orgAiSettings['org_ollama_model'] ?? 'llama3.2:3b'); ?>"
                                               placeholder="llama3.2:3b"
                                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                    </div>
                                </div>

                                <!-- Browser AI Configuration -->
                                <div id="browser-fields" style="display: <?php echo $orgAiSettings['org_ai_service_preference'] === 'browser' ? 'block' : 'none'; ?>;" class="mt-4 space-y-4">
                                    <div>
                                        <label for="org_browser_ai_model" class="block text-base font-semibold text-gray-900 mb-3">Browser AI Model</label>
                                        <select name="org_browser_ai_model"
                                                id="org_browser_ai_model"
                                                class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                            <option value="llama3.2" <?php echo $orgAiSettings['org_browser_ai_model'] === 'llama3.2' ? 'selected' : ''; ?>>Llama 3.2</option>
                                            <option value="mistral-7b" <?php echo $orgAiSettings['org_browser_ai_model'] === 'mistral-7b' ? 'selected' : ''; ?>>Mistral 7B</option>
                                            <option value="phi-3" <?php echo $orgAiSettings['org_browser_ai_model'] === 'phi-3' ? 'selected' : ''; ?>>Phi-3</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit"
                                        class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring-4 focus-visible:ring-blue-200 transition-all">
                                    Save AI Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Danger Zone (Owner only) -->
            <?php if ($org['role'] === 'owner'): ?>
            <div id="danger-zone" class="bg-white shadow rounded-lg border-2 border-red-200 scroll-mt-24">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-red-600 mb-4">Danger Zone</h2>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Transfer Ownership</p>
                                <p class="text-xs text-gray-500">Transfer this organisation to another admin.</p>
                            </div>
                            <a href="/agency/transfer-ownership.php"
                               class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Transfer
                            </a>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-red-600">Delete Organisation</p>
                                    <p class="text-xs text-gray-500">Permanently delete this organisation and all associated data.</p>
                                </div>
                                <a href="/agency/delete-organisation.php"
                                   class="inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                    Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <style>
        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }
        
        /* Active sidebar link highlighting */
        nav a.active {
            background-color: #eff6ff;
            color: #2563eb;
            font-weight: 500;
        }
    </style>
    <script>
        // Smooth scrolling for sidebar navigation links
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('aside nav a[href^="#"]');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        // Calculate offset for header (96px = top-24)
                        const headerOffset = 96;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });

        // Highlight active sidebar link on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('[id^="general-settings"], [id^="logo"], [id^="branding-colours"], [id^="custom-homepage"], [id^="limit-increase"], [id^="candidate-settings"], [id^="organisation-ai"], [id^="danger-zone"]');
            const navLinks = document.querySelectorAll('aside nav a');
            
            function updateActiveLink() {
                let current = '';
                const scrollPosition = window.scrollY + 150; // Offset for header
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                        current = section.getAttribute('id');
                    }
                });
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            }
            
            window.addEventListener('scroll', updateActiveLink);
            updateActiveLink(); // Initial call
        });

        // Sync colour picker with text display
        document.querySelectorAll('input[type="color"]').forEach(picker => {
            const textInput = picker.parentElement.querySelector('input[type="text"]');
            picker.addEventListener('input', (e) => {
                textInput.value = e.target.value.toUpperCase();
            });
        });

        // Toggle AI service fields based on checkbox
        const aiEnabledCheckbox = document.getElementById('org_ai_enabled');
        const aiServiceFields = document.getElementById('ai-service-fields');
        const aiServiceSelect = document.getElementById('org_ai_service_preference');

        if (aiEnabledCheckbox && aiServiceFields) {
            aiEnabledCheckbox.addEventListener('change', function() {
                aiServiceFields.style.display = this.checked ? 'block' : 'none';
                if (!this.checked) {
                    // Hide all service-specific fields when disabled
                    document.querySelectorAll('[id$="-fields"]').forEach(field => {
                        if (field.id !== 'ai-service-fields') {
                            field.style.display = 'none';
                        }
                    });
                } else if (aiServiceSelect) {
                    // Trigger service selection change when enabled
                    aiServiceSelect.dispatchEvent(new Event('change'));
                }
            });
        }

        // Toggle service-specific fields based on selection
        if (aiServiceSelect) {
            aiServiceSelect.addEventListener('change', function() {
                // Hide all service fields
                document.querySelectorAll('[id$="-fields"]').forEach(field => {
                    if (field.id !== 'ai-service-fields') {
                        field.style.display = 'none';
                    }
                });

                // Show selected service fields
                const service = this.value;
                if (service) {
                    const serviceFields = document.getElementById(service + '-fields');
                    if (serviceFields) {
                        serviceFields.style.display = 'block';
                    }
                }
            });
        }

        // AI Template Generation
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generate-template-btn');
            const templateUrlInput = document.getElementById('template_reference_url');
            const templateDescriptionInput = document.getElementById('template_description');
            const statusSpan = document.getElementById('generate-template-status');
            const errorDiv = document.getElementById('generate-template-error');
            const htmlTextarea = document.getElementById('custom_homepage_html');
            const cssTextarea = document.getElementById('custom_homepage_css');
            
            if (!generateBtn) return;
            
            generateBtn.addEventListener('click', async function() {
                const referenceUrl = templateUrlInput.value.trim();
                const description = templateDescriptionInput.value.trim();
                
                if (!referenceUrl && !description) {
                    errorDiv.textContent = 'Please provide either a template URL or a description.';
                    errorDiv.classList.remove('hidden');
                    return;
                }
                
                // Disable button and show loading state
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';
                statusSpan.textContent = 'Generating your homepage template...';
                statusSpan.classList.remove('hidden');
                errorDiv.classList.add('hidden');
                
                try {
                    const formData = new FormData();
                    formData.append('csrf_token', '<?php echo csrfToken(); ?>');
                    formData.append('description', description);
                    formData.append('reference_url', referenceUrl);
                    formData.append('options', JSON.stringify({}));
                    
                    const response = await fetch('/api/ai-generate-homepage-template.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Populate textareas with generated content
                        if (htmlTextarea && result.html) {
                            htmlTextarea.value = result.html;
                        }
                        if (cssTextarea && result.css) {
                            cssTextarea.value = result.css;
                        }
                        
                        statusSpan.textContent = '✓ Template generated! Review and save when ready.';
                        statusSpan.className = 'ml-3 text-sm text-green-600';
                        
                        // Scroll to HTML editor
                        if (htmlTextarea) {
                            htmlTextarea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            setTimeout(() => htmlTextarea.focus(), 300);
                        }
                    } else {
                        errorDiv.textContent = result.error || 'Failed to generate template. Please try again.';
                        errorDiv.classList.remove('hidden');
                        statusSpan.classList.add('hidden');
                    }
                } catch (error) {
                    errorDiv.textContent = 'An error occurred while generating the template. Please try again.';
                    errorDiv.classList.remove('hidden');
                    statusSpan.classList.add('hidden');
                } finally {
                    // Re-enable button
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Generate Homepage with AI';
                }
            });
        });
    </script>
</body>
</html>
