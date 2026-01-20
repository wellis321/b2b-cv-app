<?php
/**
 * AI Settings Page
 * Allows users to configure their AI service preferences
 * Super admins can use local Ollama; regular users can use cloud APIs or browser-based AI
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

$user = getCurrentUser();
$isSuperAdmin = isSuperAdmin($user['id'] ?? null);
$error = getFlash('error');
$success = getFlash('success');

// Load encryption utilities
require_once __DIR__ . '/php/encryption.php';

// Fetch full user profile including AI settings
$userId = getUserId();
$userProfile = db()->fetchOne(
    "SELECT ai_service_preference, ollama_base_url, ollama_model, browser_ai_model, 
            openai_api_key, anthropic_api_key, gemini_api_key, grok_api_key 
     FROM profiles WHERE id = ?",
    [$userId]
);

// Load current settings
$currentSettings = [
    'ai_service_preference' => $userProfile['ai_service_preference'] ?? null,
    'ollama_base_url' => $userProfile['ollama_base_url'] ?? null,
    'ollama_model' => $userProfile['ollama_model'] ?? null,
    'browser_ai_model' => $userProfile['browser_ai_model'] ?? null,
    // Note: API keys are encrypted in database, not displayed to user
    'has_openai_key' => !empty($userProfile['openai_api_key'] ?? null),
    'has_anthropic_key' => !empty($userProfile['anthropic_api_key'] ?? null),
    'has_gemini_key' => !empty($userProfile['gemini_api_key'] ?? null),
    'has_grok_key' => !empty($userProfile['grok_api_key'] ?? null),
];

// Handle form submission
if (isPost()) {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/ai-settings.php');
    }
    
    $aiService = post('ai_service_preference');
    $ollamaUrl = post('ollama_base_url');
    $ollamaModel = post('ollama_model');
    $openaiKey = post('openai_api_key'); // May be empty (to keep existing) or new key
    $anthropicKey = post('anthropic_api_key'); // May be empty (to keep existing) or new key
    $geminiKey = post('gemini_api_key'); // May be empty (to keep existing) or new key
    $grokKey = post('grok_api_key'); // May be empty (to keep existing) or new key
    $browserModel = post('browser_ai_model');
    
    // Validate inputs based on selected service
    // Only super admins can use Ollama
    if ($aiService === 'ollama' && !isSuperAdmin($user['id'])) {
        setFlash('error', 'Only super administrators can use local Ollama.');
        redirect('/ai-settings.php');
    }
    
    if ($aiService === 'ollama') {
        if (empty($ollamaUrl)) {
            setFlash('error', 'Ollama base URL is required when using Ollama.');
            redirect('/ai-settings.php');
        }
        if (empty($ollamaModel)) {
            setFlash('error', 'Ollama model name is required when using Ollama.');
            redirect('/ai-settings.php');
        }
        
        // Validate URL format
        if (!filter_var($ollamaUrl, FILTER_VALIDATE_URL)) {
            setFlash('error', 'Invalid Ollama base URL format.');
            redirect('/ai-settings.php');
        }
    } elseif ($aiService === 'openai') {
        // Validate OpenAI key if provided (user may be keeping existing key)
        if (!empty($openaiKey)) {
            if (!validateApiKeyFormat('openai', $openaiKey)) {
                setFlash('error', 'Invalid OpenAI API key format. Keys should start with "sk-" and be at least 51 characters.');
                redirect('/ai-settings.php');
            }
        }
    } elseif ($aiService === 'anthropic') {
        // Validate Anthropic key if provided (user may be keeping existing key)
        if (!empty($anthropicKey)) {
            if (!validateApiKeyFormat('anthropic', $anthropicKey)) {
                setFlash('error', 'Invalid Anthropic API key format. Keys should start with "sk-ant-" and be at least 100 characters.');
                redirect('/ai-settings.php');
            }
        }
    } elseif ($aiService === 'gemini') {
        // Validate Gemini key if provided (user may be keeping existing key)
        if (!empty($geminiKey)) {
            // Gemini API keys are typically alphanumeric strings, at least 20 characters
            if (strlen($geminiKey) < 20) {
                setFlash('error', 'Invalid Gemini API key format. Keys should be at least 20 characters.');
                redirect('/ai-settings.php');
            }
        }
    } elseif ($aiService === 'grok') {
        // Validate Grok key if provided (user may be keeping existing key)
        if (!empty($grokKey)) {
            // Grok API keys format may vary, but typically start with 'xai-' or similar
            // For now, just check minimum length
            if (strlen($grokKey) < 20) {
                setFlash('error', 'Invalid Grok API key format. Keys should be at least 20 characters.');
                redirect('/ai-settings.php');
            }
        }
    } elseif ($aiService === 'browser') {
        if (empty($browserModel)) {
            setFlash('error', 'Browser AI model selection is required.');
            redirect('/ai-settings.php');
        }
    }
    
    // Update user settings
    try {
        $updateData = [
            'ai_service_preference' => $aiService ?: null,
            'ollama_base_url' => $aiService === 'ollama' ? $ollamaUrl : null,
            'ollama_model' => $aiService === 'ollama' ? $ollamaModel : null,
            'browser_ai_model' => $aiService === 'browser' ? $browserModel : null,
        ];
        
        // Only update API keys if provided (keep existing if empty)
        if ($aiService === 'openai' && !empty($openaiKey)) {
            $encryptedKey = encryptApiKey($openaiKey);
            if ($encryptedKey === false) {
                setFlash('error', 'Failed to encrypt API key. Please try again.');
                redirect('/ai-settings.php');
            }
            $updateData['openai_api_key'] = $encryptedKey;
        } elseif ($aiService !== 'openai') {
            // Clear OpenAI key if switching away from OpenAI
            $updateData['openai_api_key'] = null;
        }
        
        if ($aiService === 'anthropic' && !empty($anthropicKey)) {
            $encryptedKey = encryptApiKey($anthropicKey);
            if ($encryptedKey === false) {
                setFlash('error', 'Failed to encrypt API key. Please try again.');
                redirect('/ai-settings.php');
            }
            $updateData['anthropic_api_key'] = $encryptedKey;
        } elseif ($aiService !== 'anthropic') {
            // Clear Anthropic key if switching away from Anthropic
            $updateData['anthropic_api_key'] = null;
        }
        
        if ($aiService === 'gemini' && !empty($geminiKey)) {
            $encryptedKey = encryptApiKey($geminiKey);
            if ($encryptedKey === false) {
                setFlash('error', 'Failed to encrypt API key. Please try again.');
                redirect('/ai-settings.php');
            }
            $updateData['gemini_api_key'] = $encryptedKey;
        } elseif ($aiService !== 'gemini') {
            // Clear Gemini key if switching away from Gemini
            $updateData['gemini_api_key'] = null;
        }
        
        if ($aiService === 'grok' && !empty($grokKey)) {
            $encryptedKey = encryptApiKey($grokKey);
            if ($encryptedKey === false) {
                setFlash('error', 'Failed to encrypt API key. Please try again.');
                redirect('/ai-settings.php');
            }
            $updateData['grok_api_key'] = $encryptedKey;
        } elseif ($aiService !== 'grok') {
            // Clear Grok key if switching away from Grok
            $updateData['grok_api_key'] = null;
        }
        
        db()->update('profiles', $updateData, 'id = ?', [$user['id']]);
        
        setFlash('success', 'AI settings updated successfully.');
        redirect('/ai-settings.php');
    } catch (Exception $e) {
        error_log("Error updating AI settings: " . $e->getMessage());
        setFlash('error', 'Failed to update settings. Please try again.');
        redirect('/ai-settings.php');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'AI Settings | Simple CV Builder',
        'metaDescription' => 'Configure your local AI settings for CV features.',
        'canonicalUrl' => APP_URL . '/ai-settings.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">AI Settings</h1>
                <p class="text-lg text-gray-600">
                    Configure your AI service preferences for CV rewriting and quality assessment.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <!-- Organization AI Banner -->
            <?php
            $userOrg = getUserOrganisation($userId);
            $orgAiInfo = null;
            if ($userOrg && !empty($userOrg['organisation_id'])) {
                try {
                    $org = db()->fetchOne(
                        "SELECT name, org_ai_enabled, org_ai_service_preference 
                         FROM organisations 
                         WHERE id = ? AND org_ai_enabled = 1",
                        [$userOrg['organisation_id']]
                    );
                    if ($org) {
                        $orgAiInfo = [
                            'name' => $org['name'],
                            'service' => $org['org_ai_service_preference']
                        ];
                    }
                } catch (Exception $e) {
                    // Organization AI columns may not exist yet
                }
            }
            ?>
            <?php if ($orgAiInfo): ?>
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800 mb-1">Organization AI Available</h3>
                            <p class="text-sm text-blue-700">
                                Your organization <strong><?php echo e($orgAiInfo['name']); ?></strong> provides AI access via 
                                <strong><?php 
                                    $serviceNames = [
                                        'openai' => 'OpenAI',
                                        'anthropic' => 'Anthropic Claude',
                                        'gemini' => 'Google Gemini',
                                        'grok' => 'xAI Grok',
                                        'ollama' => 'Local Ollama',
                                        'browser' => 'Browser-Based AI'
                                    ];
                                    echo e($serviceNames[$orgAiInfo['service']] ?? $orgAiInfo['service']);
                                ?></strong>. 
                                You can still configure your own AI settings below if you prefer.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cost Warning (Collapsible) -->
            <div class="mb-6 bg-gray-200 border border-gray-300 rounded-lg overflow-hidden">
                <button onclick="toggleSection('cost-warning-content')" class="w-full flex items-center justify-between p-4 text-left bg-gray-200 hover:bg-gray-300 transition-colors">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-xl font-bold text-black">Important: API Costs Information</h3>
                    </div>
                    <svg id="cost-warning-icon" class="h-5 w-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="cost-warning-content" class="hidden px-4 pt-4 pb-4 bg-white">
                    <div class="text-sm text-gray-700 space-y-2">
                        <p><strong class="text-gray-900">Cloud AI services (OpenAI, Anthropic, Gemini, Grok) charge per use.</strong> Costs are based on the amount of text processed and can add up quickly with repeated CV generations.</p>
                        <p><strong class="text-gray-900">Free options:</strong> Browser-Based AI is completely free - it runs in your browser with no API costs.</p>
                        <p><strong class="text-gray-900">Free tiers:</strong> Some services offer limited free tiers, but these have usage limits. Check each provider's pricing page for current free tier details.</p>
                        <p><strong class="text-gray-900">Recommendation:</strong> Start with Browser-Based AI to avoid costs. Only use paid APIs if you need specific features or higher quality outputs.</p>
                    </div>
                </div>
            </div>

            <!-- Service Comparison Info (Collapsible) -->
            <div class="mb-6 bg-gray-200 border border-gray-300 rounded-lg overflow-hidden">
                <button onclick="toggleSection('service-comparison-content')" class="w-full flex items-center justify-between p-4 text-left bg-gray-200 hover:bg-gray-300 transition-colors">
                    <h2 class="text-xl font-bold text-black">Choose Your AI Provider</h2>
                    <svg id="service-comparison-icon" class="h-5 w-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="service-comparison-content" class="hidden px-4 pt-4 pb-4 bg-white">
                    <p class="text-gray-700 mb-4">
                        Select how you want to use AI features. You can use your own API keys (no cost to us) or browser-based AI (runs entirely in your browser).
                    </p>
                    <?php if ($isSuperAdmin): ?>
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Need setup instructions?</strong> 
                            <a href="/resources/ai/setup-ollama.php" class="text-blue-600 hover:text-blue-800 underline font-medium">View complete setup guide for Local Ollama and Browser-Based AI →</a>
                        </p>
                        <p class="text-sm text-gray-700">
                            For external API setup, see the configuration sections below when you select a service.
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Need setup instructions?</strong> 
                            <a href="/resources/ai/setup-ollama.php" class="text-blue-600 hover:text-blue-800 underline font-medium">View complete setup guide for Browser-Based AI →</a>
                        </p>
                        <p class="text-sm text-gray-700">
                            For external API setup, see the configuration sections below when you select a service.
                        </p>
                    </div>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="bg-white p-3 rounded border border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-2">Your API Keys <span class="text-red-600 text-sm">(Paid)</span></h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-2">
                            <li>OpenAI, Anthropic, Gemini, or Grok</li>
                            <li>Use your own API keys</li>
                            <li><strong class="text-red-600">Charges per use</strong></li>
                            <li>Professional cloud models</li>
                            <li class="text-sm text-gray-600 mt-1">Some offer free tiers with limits</li>
                        </ul>
                    </div>
                    <div class="bg-white p-3 rounded border border-green-200 bg-green-50">
                        <h3 class="font-semibold text-gray-900 mb-2">Browser-Based AI <span class="text-green-600 text-sm">(Free)</span></h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-2">
                            <li>Runs entirely in your browser</li>
                            <li><strong class="text-green-600">Completely free</strong></li>
                            <li>No API costs</li>
                            <li>Models cached locally</li>
                        </ul>
                    </div>
                    <?php if ($isSuperAdmin): ?>
                    <div class="bg-white p-3 rounded border border-green-200 bg-green-50">
                        <h3 class="font-semibold text-gray-900 mb-2">Local Ollama <span class="text-green-600 text-sm">(Free - Admin Only)</span></h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-2">
                            <li>Runs on your computer</li>
                            <li><strong class="text-green-600">Completely free</strong></li>
                            <li>No API costs</li>
                            <li>Works offline</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <div class="bg-white p-3 rounded border border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-2">Site Default</h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-2">
                            <li>Use site's configured AI</li>
                            <li>Automatic setup</li>
                            <li>Managed by us</li>
                            <li class="text-sm text-gray-600 mt-1">Cost depends on site configuration</li>
                        </ul>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Information (Collapsible) -->
            <div class="mb-6 bg-gray-200 border border-gray-300 rounded-lg overflow-hidden">
                <button onclick="toggleSection('pricing-info-content')" class="w-full flex items-center justify-between p-4 text-left bg-gray-200 hover:bg-gray-300 transition-colors">
                    <h3 class="text-xl font-bold text-black">Understanding API Costs</h3>
                    <svg id="pricing-info-icon" class="h-5 w-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="pricing-info-content" class="hidden px-4 pt-4 pb-4 bg-white">
                    <div class="space-y-4 text-sm text-gray-700">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">How API Pricing Works</h4>
                        <p>Cloud AI services charge based on "tokens" (words or parts of words) processed. Each CV generation uses tokens for both the input (your CV data + job description) and output (the rewritten CV). Costs can range from a few pence to several pounds per generation depending on the model and amount of text.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Free Options (Recommended)</h4>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Browser-Based AI:</strong> Free forever - runs in your browser, models cached locally</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Paid Options (Use with Caution)</h4>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>OpenAI:</strong> Pay-per-use pricing. Some models have free tiers with limits. <a href="https://openai.com/pricing" target="_blank" class="text-blue-600 hover:text-blue-800 underline">View pricing →</a></li>
                            <li><strong>Anthropic:</strong> Pay-per-use pricing. <a href="https://www.anthropic.com/pricing" target="_blank" class="text-blue-600 hover:text-blue-800 underline">View pricing →</a></li>
                            <li><strong>Google Gemini:</strong> Pay-per-use pricing. Free tier available with limits. <a href="https://ai.google.dev/pricing" target="_blank" class="text-blue-600 hover:text-blue-800 underline">View pricing →</a></li>
                            <li><strong>xAI Grok:</strong> Pay-per-use pricing. <a href="https://x.ai/api" target="_blank" class="text-blue-600 hover:text-blue-800 underline">View pricing →</a></li>
                            <li><strong>Hugging Face:</strong> Freemium - $0.10/month free credits, then pay-as-you-go. <a href="https://huggingface.co/pricing" target="_blank" class="text-blue-600 hover:text-blue-800 underline">View pricing →</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Freemium Options</h4>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Hugging Face Inference API:</strong> Free tier includes $0.10/month in credits for testing. After free credits, pay-as-you-go based on model and usage. <a href="https://huggingface.co/docs/api-inference/rate-limits" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Learn more →</a></li>
                        </ul>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-sm"><strong><svg class="inline-block w-4 h-4 -mt-0.5 mr-1 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Important:</strong> Always check your API provider's pricing page and set usage limits/budgets to avoid unexpected charges. Monitor your usage regularly, especially when generating multiple CV variants.</p>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="/ai-settings.php">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    
                    <!-- AI Service Selection -->
                    <div class="mb-6">
                        <label for="ai_service_preference" class="block text-sm font-medium text-gray-700 mb-2">
                            AI Service
                        </label>
                        <select id="ai_service_preference" 
                                name="ai_service_preference" 
                                onchange="toggleServiceFields()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Use Site Default</option>
                            <option value="openai" <?php echo $currentSettings['ai_service_preference'] === 'openai' ? 'selected' : ''; ?>>OpenAI | Paid (Your API Key)</option>
                            <option value="anthropic" <?php echo $currentSettings['ai_service_preference'] === 'anthropic' ? 'selected' : ''; ?>>Anthropic Claude | Paid (Your API Key)</option>
                            <option value="gemini" <?php echo $currentSettings['ai_service_preference'] === 'gemini' ? 'selected' : ''; ?>>Google Gemini | Paid (Your API Key)</option>
                            <option value="grok" <?php echo $currentSettings['ai_service_preference'] === 'grok' ? 'selected' : ''; ?>>xAI Grok | Paid (Your API Key)</option>
                            <option value="huggingface" <?php echo $currentSettings['ai_service_preference'] === 'huggingface' ? 'selected' : ''; ?>>Hugging Face | Freemium (Your API Token)</option>
                            <option value="browser" <?php echo $currentSettings['ai_service_preference'] === 'browser' ? 'selected' : ''; ?>>Browser-Based AI | Free</option>
                            <?php if ($isSuperAdmin): ?>
                            <option value="ollama" <?php echo $currentSettings['ai_service_preference'] === 'ollama' ? 'selected' : ''; ?>>Local Ollama | Free (Admin Only)</option>
                            <?php endif; ?>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">
                            Select your preferred AI service. Use your own API keys or browser-based AI.
                            <?php if ($isSuperAdmin): ?>
                            Super admins can also use local Ollama installation.
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Ollama Configuration (shown when Ollama is selected, super admin only) -->
                    <?php if ($isSuperAdmin): ?>
                    <div id="ollama-config" style="display: <?php echo $currentSettings['ai_service_preference'] === 'ollama' ? 'block' : 'none'; ?>;">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Need help setting up Local Ollama?</strong> Follow our complete step-by-step guide to install and configure Ollama on your computer.
                            </p>
                            <a href="/resources/ai/setup-ollama.php" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium underline">
                                View Complete Ollama Setup Guide →
                            </a>
                        </div>

                        <!-- System Capability Checker -->
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h3 class="font-semibold text-gray-900 mb-2">Check Your System Capabilities</h3>
                            <p class="text-sm text-gray-700 mb-3">
                                We can check your computer's capabilities (with your permission) to recommend which local AI models you can run.
                            </p>
                            <button type="button" 
                                    onclick="checkSystemCapabilities(event)" 
                                    id="check-capabilities-btn"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <span id="check-capabilities-text">Check My System</span>
                                <span id="check-capabilities-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Checking...
                                </span>
                            </button>
                            <div id="capabilities-result" class="mt-4 hidden"></div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="ollama_base_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Ollama Base URL
                            </label>
                            <input type="text" 
                                   id="ollama_base_url" 
                                   name="ollama_base_url" 
                                   value="<?php echo e($currentSettings['ollama_base_url'] ?? 'http://localhost:11434'); ?>"
                                   placeholder="http://localhost:11434"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-2 text-sm text-gray-500">
                                The URL where your Ollama server is running. Default is <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">http://localhost:11434</code>
                            </p>
                        </div>

                        <div class="mb-6">
                            <label for="ollama_model" class="block text-sm font-medium text-gray-700 mb-2">
                                Ollama Model Name
                            </label>
                            <input type="text" 
                                   id="ollama_model" 
                                   name="ollama_model" 
                                   value="<?php echo e($currentSettings['ollama_model'] ?? 'llama3.2'); ?>"
                                   placeholder="llama3.2"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-2 text-sm text-gray-500">
                                The name of the model you downloaded (e.g., <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">llama3:latest</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">llama3.1:8b</code>, or <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">llama3.3</code>). Click "Test Connection" to auto-detect and update this field.
                            </p>
                        </div>

                        <!-- Test Connection Button -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <button type="button" 
                                    onclick="testConnection()" 
                                    id="test-btn"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <span id="test-text">Test Connection</span>
                                <span id="test-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Testing...
                                </span>
                            </button>
                            <p class="mt-2 text-sm text-gray-600" id="test-result"></p>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Non-super-admin users cannot see Ollama configuration -->
                    <div id="ollama-config" style="display: none;"></div>
                    <?php endif; ?>

                    <!-- OpenAI Configuration (shown when OpenAI is selected) -->
                    <div id="openai-config" style="display: <?php echo $currentSettings['ai_service_preference'] === 'openai' ? 'block' : 'none'; ?>;">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Setting up OpenAI API:</strong> 
                            </p>
                            <ol class="text-sm text-gray-700 list-decimal list-inside space-y-1 mb-2">
                                <li>Sign up for an account at <a href="https://platform.openai.com/signup" target="_blank" class="text-blue-600 hover:text-blue-800 underline">OpenAI Platform</a></li>
                                <li>Navigate to <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:text-blue-800 underline">API Keys</a> and create a new key</li>
                                <li>Copy your API key and paste it below</li>
                                <li>Set usage limits in your OpenAI account to avoid unexpected charges</li>
                            </ol>
                            <p class="text-sm text-gray-700">
                                <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium underline">
                                    Get OpenAI API Key →
                                </a>
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="openai_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                OpenAI API Key
                                <?php if ($currentSettings['has_openai_key']): ?>
                                    <span class="text-xs text-green-600 ml-2">(Key saved)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" 
                                   id="openai_api_key" 
                                   name="openai_api_key" 
                                   placeholder="<?php echo $currentSettings['has_openai_key'] ? 'Enter new key to update (leave blank to keep existing)' : 'sk-...'; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-2 text-sm text-gray-500">
                                Enter your OpenAI API key. You can get one from <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:text-blue-800 underline">OpenAI Platform</a>.
                                <?php if ($currentSettings['has_openai_key']): ?>
                                    Leave blank to keep your existing key.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Test OpenAI Connection Button -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <button type="button" 
                                    onclick="testOpenAIConnection()" 
                                    id="test-openai-btn"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <span id="test-openai-text">Test OpenAI Connection</span>
                                <span id="test-openai-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Testing...
                                </span>
                            </button>
                            <p class="mt-2 text-sm text-gray-600" id="test-openai-result"></p>
                        </div>
                    </div>

                    <!-- Anthropic Configuration (shown when Anthropic is selected) -->
                    <div id="anthropic-config" style="display: <?php echo $currentSettings['ai_service_preference'] === 'anthropic' ? 'block' : 'none'; ?>;">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Setting up Anthropic API:</strong> 
                            </p>
                            <ol class="text-sm text-gray-700 list-decimal list-inside space-y-1 mb-2">
                                <li>Sign up for an account at <a href="https://console.anthropic.com/signup" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Anthropic Console</a></li>
                                <li>Navigate to API Keys section and create a new key</li>
                                <li>Copy your API key and paste it below</li>
                                <li>Monitor usage in your Anthropic account dashboard</li>
                            </ol>
                            <p class="text-sm text-gray-700">
                                <a href="https://console.anthropic.com/" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium underline">
                                    Get Anthropic API Key →
                                </a>
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="anthropic_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Anthropic API Key
                                <?php if ($currentSettings['has_anthropic_key']): ?>
                                    <span class="text-xs text-green-600 ml-2">(Key saved)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" 
                                   id="anthropic_api_key" 
                                   name="anthropic_api_key" 
                                   placeholder="<?php echo $currentSettings['has_anthropic_key'] ? 'Enter new key to update (leave blank to keep existing)' : 'sk-ant-...'; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-2 text-sm text-gray-500">
                                Enter your Anthropic API key. You can get one from <a href="https://console.anthropic.com/" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Anthropic Console</a>.
                                <?php if ($currentSettings['has_anthropic_key']): ?>
                                    Leave blank to keep your existing key.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Test Anthropic Connection Button -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <button type="button" 
                                    onclick="testAnthropicConnection()" 
                                    id="test-anthropic-btn"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <span id="test-anthropic-text">Test Anthropic Connection</span>
                                <span id="test-anthropic-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Testing...
                                </span>
                            </button>
                            <p class="mt-2 text-sm text-gray-600" id="test-anthropic-result"></p>
                        </div>
                    </div>

                    <!-- Gemini Configuration (shown when Gemini is selected) -->
                    <div id="gemini-config" style="display: <?php echo $currentSettings['ai_service_preference'] === 'gemini' ? 'block' : 'none'; ?>;">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Setting up Google Gemini API:</strong> 
                            </p>
                            <ol class="text-sm text-gray-700 list-decimal list-inside space-y-1 mb-2">
                                <li>Sign in with your Google account at <a href="https://aistudio.google.com/" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Google AI Studio</a></li>
                                <li>Click "Get API Key" and create a new key (free tier available)</li>
                                <li>Copy your API key and paste it below</li>
                                <li>Check your usage limits in Google Cloud Console</li>
                            </ol>
                            <p class="text-sm text-gray-700">
                                <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium underline">
                                    Get Google Gemini API Key →
                                </a>
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="gemini_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Google Gemini API Key
                                <?php if ($currentSettings['has_gemini_key']): ?>
                                    <span class="text-xs text-green-600 ml-2">(Key saved)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" 
                                   id="gemini_api_key" 
                                   name="gemini_api_key" 
                                   placeholder="<?php echo $currentSettings['has_gemini_key'] ? 'Enter new key to update (leave blank to keep existing)' : 'Enter your Gemini API key...'; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-2 text-sm text-gray-500">
                                Enter your Google Gemini API key. You can get one from <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Google AI Studio</a>.
                                <?php if ($currentSettings['has_gemini_key']): ?>
                                    Leave blank to keep your existing key.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Test Gemini Connection Button -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <button type="button" 
                                    onclick="testGeminiConnection()" 
                                    id="test-gemini-btn"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <span id="test-gemini-text">Test Gemini Connection</span>
                                <span id="test-gemini-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Testing...
                                </span>
                            </button>
                            <p class="mt-2 text-sm text-gray-600" id="test-gemini-result"></p>
                        </div>
                    </div>

                    <!-- Grok Configuration (shown when Grok is selected) -->
                    <div id="grok-config" style="display: <?php echo $currentSettings['ai_service_preference'] === 'grok' ? 'block' : 'none'; ?>;">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Setting up xAI Grok API:</strong> 
                            </p>
                            <ol class="text-sm text-gray-700 list-decimal list-inside space-y-1 mb-2">
                                <li>Sign up for an account at <a href="https://x.ai/api" target="_blank" class="text-blue-600 hover:text-blue-800 underline">xAI</a></li>
                                <li>Navigate to API section and create a new API key</li>
                                <li>Copy your API key and paste it below</li>
                                <li>Review pricing and set usage limits to control costs</li>
                            </ol>
                            <p class="text-sm text-gray-700">
                                <a href="https://x.ai/api" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium underline">
                                    Get xAI Grok API Key →
                                </a>
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="grok_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                xAI Grok API Key
                                <?php if ($currentSettings['has_grok_key']): ?>
                                    <span class="text-xs text-green-600 ml-2">(Key saved)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" 
                                   id="grok_api_key" 
                                   name="grok_api_key" 
                                   placeholder="<?php echo $currentSettings['has_grok_key'] ? 'Enter new key to update (leave blank to keep existing)' : 'Enter your Grok API key...'; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-2 text-sm text-gray-500">
                                Enter your xAI Grok API key. You can get one from <a href="https://x.ai/api" target="_blank" class="text-blue-600 hover:text-blue-800 underline">xAI API</a>.
                                <?php if ($currentSettings['has_grok_key']): ?>
                                    Leave blank to keep your existing key.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Test Grok Connection Button -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <button type="button" 
                                    onclick="testGrokConnection()" 
                                    id="test-grok-btn"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <span id="test-grok-text">Test Grok Connection</span>
                                <span id="test-grok-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Testing...
                                </span>
                            </button>
                            <p class="mt-2 text-sm text-gray-600" id="test-grok-result"></p>
                        </div>
                    </div>

                    <!-- Browser AI Configuration (shown when Browser is selected) -->
                    <div id="browser-config" style="display: <?php echo $currentSettings['ai_service_preference'] === 'browser' ? 'block' : 'none'; ?>;">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h3 class="font-semibold text-gray-900 mb-2">Browser-Based AI</h3>
                            <p class="text-sm text-gray-700 mb-2">
                                AI models run entirely in your browser. Models are downloaded and cached locally. No data is sent to external servers.
                            </p>
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Need help getting started?</strong> Check our complete setup guide for browser compatibility, requirements, and model selection.
                            </p>
                            <a href="/resources/ai/setup-ollama.php#browser-based-ai" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium underline">
                                View Complete Browser-Based AI Setup Guide →
                            </a>
                        </div>

                        <!-- System Capability Checker -->
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h3 class="font-semibold text-gray-900 mb-2">Check Your Browser Capabilities</h3>
                            <p class="text-sm text-gray-700 mb-3">
                                We can check your browser's capabilities (with your permission) to recommend which browser-based AI models you can run.
                            </p>
                            <button type="button" 
                                    onclick="checkSystemCapabilities(event)" 
                                    id="check-browser-capabilities-btn"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <span id="check-browser-capabilities-text">Check My Browser</span>
                                <span id="check-browser-capabilities-loading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Checking...
                                </span>
                            </button>
                            <div id="browser-capabilities-result" class="mt-4 hidden"></div>
                        </div>
                            
                            <div class="mb-6">
                                <label for="browser_ai_model" class="block text-sm font-medium text-gray-700 mb-2">
                                    Browser AI Model
                                </label>
                                <select id="browser_ai_model" 
                                        name="browser_ai_model" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select a model...</option>
                                    <optgroup label="WebLLM Models (Recommended)">
                                        <option value="llama3.2" <?php echo $currentSettings['browser_ai_model'] === 'llama3.2' ? 'selected' : ''; ?>>Llama 3.2 (~2GB)</option>
                                        <option value="mistral" <?php echo $currentSettings['browser_ai_model'] === 'mistral' ? 'selected' : ''; ?>>Mistral 7B (~4GB)</option>
                                        <option value="phi3" <?php echo $currentSettings['browser_ai_model'] === 'phi3' ? 'selected' : ''; ?>>Phi-3 (~2GB)</option>
                                    </optgroup>
                                    <optgroup label="TensorFlow.js Models (Coming Soon)">
                                        <option value="universal-sentence-encoder" disabled>Universal Sentence Encoder (~100MB)</option>
                                    </optgroup>
                                </select>
                                <p class="mt-2 text-sm text-gray-500">
                                    Select a model to use in your browser. Models are downloaded on first use and cached for future sessions.
                                    Ensure you have enough browser storage available (models can be 2-4GB).
                                </p>
                            </div>

                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-gray-700 mb-2">
                                    <strong>Browser Requirements:</strong> WebGPU or WebGL support required. 
                                    Models require significant browser storage space. First use may take several minutes to download the model.
                                </p>
                                <p class="text-sm text-gray-700">
                                    <strong>Supported Browsers:</strong>
                                </p>
                                <ul class="text-sm text-gray-700 list-disc list-inside mt-1 space-y-1">
                                    <li><strong>Chrome:</strong> Version 113+ (WebGPU) or Version 56+ (WebGL 2.0)</li>
                                    <li><strong>Edge:</strong> Version 113+ (WebGPU) or Version 79+ (WebGL 2.0)</li>
                                    <li><strong>Firefox:</strong> Version 141+ on Windows only (WebGPU), Version 51+ (WebGL 2.0) on all platforms</li>
                                    <li><strong>Safari:</strong> Version 16.4+ on macOS 13.3+ (WebGPU), Version 15.2+ (WebGL 2.0)</li>
                                    <li><strong>Opera:</strong> Version 99+ (WebGPU) or Version 43+ (WebGL 2.0)</li>
                                </ul>
                                <p class="text-sm text-gray-600 mt-2 italic">
                                    Note: WebGPU provides better performance but requires newer browser versions and specific platforms. WebGL 2.0 fallback is available for older browsers but may be slower. Firefox WebGPU currently works only on Windows (not macOS or Linux); support for macOS and Linux is coming soon.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <a href="/dashboard.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <script>
        function escapeHtml(s) {
            if (s == null) return '';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function toggleServiceFields() {
            const select = document.getElementById('ai_service_preference');
            const ollamaConfig = document.getElementById('ollama-config');
            const openaiConfig = document.getElementById('openai-config');
            const anthropicConfig = document.getElementById('anthropic-config');
            const geminiConfig = document.getElementById('gemini-config');
            const grokConfig = document.getElementById('grok-config');
            const browserConfig = document.getElementById('browser-config');
            
            // Hide all configs
            ollamaConfig.style.display = 'none';
            openaiConfig.style.display = 'none';
            anthropicConfig.style.display = 'none';
            geminiConfig.style.display = 'none';
            grokConfig.style.display = 'none';
            browserConfig.style.display = 'none';
            
            // Show selected config
            if (select.value === 'ollama') {
                ollamaConfig.style.display = 'block';
            } else if (select.value === 'openai') {
                openaiConfig.style.display = 'block';
            } else if (select.value === 'anthropic') {
                anthropicConfig.style.display = 'block';
            } else if (select.value === 'gemini') {
                geminiConfig.style.display = 'block';
            } else if (select.value === 'grok') {
                grokConfig.style.display = 'block';
            } else if (select.value === 'browser') {
                browserConfig.style.display = 'block';
            }
        }

        async function testConnection() {
            const testBtn = document.getElementById('test-btn');
            const testText = document.getElementById('test-text');
            const testLoading = document.getElementById('test-loading');
            const testResult = document.getElementById('test-result');
            
            const baseUrl = document.getElementById('ollama_base_url').value;
            
            if (!baseUrl) {
                testResult.textContent = 'Please enter an Ollama base URL first.';
                testResult.className = 'mt-2 text-sm text-red-600';
                return;
            }
            
            testBtn.disabled = true;
            testText.classList.add('hidden');
            testLoading.classList.remove('hidden');
            testResult.textContent = '';
            
            try {
                // Use server-side proxy to avoid CSP issues
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('base_url', baseUrl);
                
                const response = await fetch('/api/test-ollama-connection.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>' + escapeHtml(result.message) + '</span></span>';
                    testResult.className = 'mt-2 text-sm ' + (result.model_count > 0 ? 'text-green-600' : 'text-yellow-600');
                    
                    // Auto-populate model field if models are found
                    if (result.models && result.models.length > 0) {
                        const modelInput = document.getElementById('ollama_model');
                        if (modelInput) {
                            const currentModel = modelInput.value.trim();
                            const availableModelNames = result.models.map(m => m.name || m);
                            
                            // Check if current model exists in available models (exact match or base name match)
                            const modelExists = availableModelNames.some(name => {
                                const modelName = typeof name === 'string' ? name : (name.name || '');
                                return modelName === currentModel || 
                                       (currentModel && modelName.startsWith(currentModel.split(':')[0] + ':'));
                            });
                            
                            // If model doesn't exist or field is empty, update it
                            if (!modelExists || !currentModel) {
                                // Use the first available model, prefer llama3 if available
                                let suggestedModel = availableModelNames[0];
                                if (typeof suggestedModel !== 'string') {
                                    suggestedModel = suggestedModel.name || suggestedModel;
                                }
                                
                                const llama3Model = availableModelNames.find(m => {
                                    const modelName = typeof m === 'string' ? m : (m.name || '');
                                    return modelName.includes('llama3');
                                });
                                if (llama3Model) {
                                    suggestedModel = typeof llama3Model === 'string' ? llama3Model : (llama3Model.name || llama3Model);
                                }
                                
                                modelInput.value = suggestedModel;
                                
                                // Show a helpful message
                                if (!modelExists && currentModel) {
                                    // Remove any existing warning messages
                                    const existingWarnings = testResult.parentNode.querySelectorAll('.model-warning-message');
                                    existingWarnings.forEach(w => w.remove());
                                    
                                    const warningMsg = document.createElement('div');
                                    warningMsg.className = 'model-warning-message mt-2 text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-2';
                                    warningMsg.innerHTML = '⚠️ The model "' + escapeHtml(currentModel) + '" was not found. Auto-updated to "' + escapeHtml(suggestedModel) + '"';
                                    testResult.parentNode.insertBefore(warningMsg, testResult.nextSibling);
                                    
                                    // Remove warning after 8 seconds
                                    setTimeout(() => {
                                        if (warningMsg.parentNode) {
                                            warningMsg.parentNode.removeChild(warningMsg);
                                        }
                                    }, 8000);
                                } else if (!currentModel) {
                                    // Remove any existing info messages
                                    const existingInfo = testResult.parentNode.querySelectorAll('.model-info-message');
                                    existingInfo.forEach(i => i.remove());
                                    
                                    const infoMsg = document.createElement('div');
                                    infoMsg.className = 'model-info-message mt-2 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded p-2';
                                    infoMsg.innerHTML = '✓ Auto-filled model name: "' + escapeHtml(suggestedModel) + '"';
                                    testResult.parentNode.insertBefore(infoMsg, testResult.nextSibling);
                                    
                                    // Remove info message after 6 seconds
                                    setTimeout(() => {
                                        if (infoMsg.parentNode) {
                                            infoMsg.parentNode.removeChild(infoMsg);
                                        }
                                    }, 6000);
                                }
                            }
                        }
                    }
                } else {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>' + escapeHtml(result.error || 'Connection failed. Make sure Ollama is running and the URL is correct.') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-red-600';
                }
            } catch (error) {
                console.error('Error:', error);
                testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Connection failed. Make sure Ollama is running and the URL is correct.</span></span>';
                testResult.className = 'mt-2 text-sm text-red-600';
            } finally {
                testBtn.disabled = false;
                testText.classList.remove('hidden');
                testLoading.classList.add('hidden');
            }
        }

        async function testOpenAIConnection() {
            const testBtn = document.getElementById('test-openai-btn');
            const testText = document.getElementById('test-openai-text');
            const testLoading = document.getElementById('test-openai-loading');
            const testResult = document.getElementById('test-openai-result');
            
            const apiKey = document.getElementById('openai_api_key').value;
            
            if (!apiKey) {
                testResult.textContent = 'Please enter an OpenAI API key first.';
                testResult.className = 'mt-2 text-sm text-red-600';
                return;
            }
            
            testBtn.disabled = true;
            testText.classList.add('hidden');
            testLoading.classList.remove('hidden');
            testResult.textContent = '';
            
            try {
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('api_key', apiKey);
                
                const response = await fetch('/api/test-openai-connection.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>' + escapeHtml(result.message || 'Connection successful!') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-green-600';
                } else {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>' + escapeHtml(result.error || 'Connection failed. Please check your API key.') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-red-600';
                }
            } catch (error) {
                console.error('Error:', error);
                testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Connection failed. Please check your API key and try again.</span></span>';
                testResult.className = 'mt-2 text-sm text-red-600';
            } finally {
                testBtn.disabled = false;
                testText.classList.remove('hidden');
                testLoading.classList.add('hidden');
            }
        }

        async function testAnthropicConnection() {
            const testBtn = document.getElementById('test-anthropic-btn');
            const testText = document.getElementById('test-anthropic-text');
            const testLoading = document.getElementById('test-anthropic-loading');
            const testResult = document.getElementById('test-anthropic-result');
            
            const apiKey = document.getElementById('anthropic_api_key').value;
            
            if (!apiKey) {
                testResult.textContent = 'Please enter an Anthropic API key first.';
                testResult.className = 'mt-2 text-sm text-red-600';
                return;
            }
            
            testBtn.disabled = true;
            testText.classList.add('hidden');
            testLoading.classList.remove('hidden');
            testResult.textContent = '';
            
            try {
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('api_key', apiKey);
                
                const response = await fetch('/api/test-anthropic-connection.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>' + escapeHtml(result.message || 'Connection successful!') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-green-600';
                } else {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>' + escapeHtml(result.error || 'Connection failed. Please check your API key.') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-red-600';
                }
            } catch (error) {
                console.error('Error:', error);
                testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Connection failed. Please check your API key and try again.</span></span>';
                testResult.className = 'mt-2 text-sm text-red-600';
            } finally {
                testBtn.disabled = false;
                testText.classList.remove('hidden');
                testLoading.classList.add('hidden');
            }
        }

        async function testGeminiConnection() {
            const testBtn = document.getElementById('test-gemini-btn');
            const testText = document.getElementById('test-gemini-text');
            const testLoading = document.getElementById('test-gemini-loading');
            const testResult = document.getElementById('test-gemini-result');
            
            const apiKey = document.getElementById('gemini_api_key').value;
            
            if (!apiKey) {
                testResult.textContent = 'Please enter a Gemini API key first.';
                testResult.className = 'mt-2 text-sm text-red-600';
                return;
            }
            
            testBtn.disabled = true;
            testText.classList.add('hidden');
            testLoading.classList.remove('hidden');
            testResult.textContent = '';
            
            try {
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('api_key', apiKey);
                
                const response = await fetch('/api/test-gemini-connection.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>' + escapeHtml(result.message || 'Connection successful!') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-green-600';
                } else {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>' + escapeHtml(result.error || 'Connection failed. Please check your API key.') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-red-600';
                }
            } catch (error) {
                console.error('Error:', error);
                testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Connection failed. Please check your API key and try again.</span></span>';
                testResult.className = 'mt-2 text-sm text-red-600';
            } finally {
                testBtn.disabled = false;
                testText.classList.remove('hidden');
                testLoading.classList.add('hidden');
            }
        }

        async function testGrokConnection() {
            const testBtn = document.getElementById('test-grok-btn');
            const testText = document.getElementById('test-grok-text');
            const testLoading = document.getElementById('test-grok-loading');
            const testResult = document.getElementById('test-grok-result');
            
            const apiKey = document.getElementById('grok_api_key').value;
            
            if (!apiKey) {
                testResult.textContent = 'Please enter a Grok API key first.';
                testResult.className = 'mt-2 text-sm text-red-600';
                return;
            }
            
            testBtn.disabled = true;
            testText.classList.add('hidden');
            testLoading.classList.remove('hidden');
            testResult.textContent = '';
            
            try {
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('api_key', apiKey);
                
                const response = await fetch('/api/test-grok-connection.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>' + escapeHtml(result.message || 'Connection successful!') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-green-600';
                } else {
                    testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>' + escapeHtml(result.error || 'Connection failed. Please check your API key.') + '</span></span>';
                    testResult.className = 'mt-2 text-sm text-red-600';
                }
            } catch (error) {
                console.error('Error:', error);
                testResult.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Connection failed. Please check your API key and try again.</span></span>';
                testResult.className = 'mt-2 text-sm text-red-600';
            } finally {
                testBtn.disabled = false;
                testText.classList.remove('hidden');
                testLoading.classList.add('hidden');
            }
        }

        // Toggle collapsible sections
        function toggleSection(sectionId) {
            const content = document.getElementById(sectionId);
            const icon = document.getElementById(sectionId.replace('-content', '-icon'));
            
            if (content && icon) {
                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            }
        }

        // Check system capabilities
        async function checkSystemCapabilities(event) {
            console.log('checkSystemCapabilities called', event);
            
            // Determine which button was clicked
            const clickedButton = event && event.target ? (event.target.closest('button') || event.target) : null;
            const browserBtn = document.getElementById('check-browser-capabilities-btn');
            const ollamaBtn = document.getElementById('check-capabilities-btn');
            
            let btn, text, loading, result;
            
            // Check which button was clicked
            if (clickedButton === browserBtn || (clickedButton && clickedButton.id === 'check-browser-capabilities-btn')) {
                // Browser-Based AI button was clicked
                btn = browserBtn;
                text = document.getElementById('check-browser-capabilities-text');
                loading = document.getElementById('check-browser-capabilities-loading');
                result = document.getElementById('browser-capabilities-result');
                console.log('Using browser capabilities checker');
            } else if (clickedButton === ollamaBtn || (clickedButton && clickedButton.id === 'check-capabilities-btn')) {
                // Ollama button was clicked
                btn = ollamaBtn;
                text = document.getElementById('check-capabilities-text');
                loading = document.getElementById('check-capabilities-loading');
                result = document.getElementById('capabilities-result');
                console.log('Using Ollama capabilities checker');
            } else {
                // Fallback: check which section is visible based on dropdown
                const select = document.getElementById('ai_service_preference');
                const browserConfig = document.getElementById('browser-config');
                const ollamaConfig = document.getElementById('ollama-config');
                
                if (select && select.value === 'browser' && browserConfig && browserConfig.style.display !== 'none') {
                    btn = browserBtn;
                    text = document.getElementById('check-browser-capabilities-text');
                    loading = document.getElementById('check-browser-capabilities-loading');
                    result = document.getElementById('browser-capabilities-result');
                    console.log('Using browser capabilities checker (fallback)');
                } else if (select && select.value === 'ollama' && ollamaConfig && ollamaConfig.style.display !== 'none') {
                    btn = ollamaBtn;
                    text = document.getElementById('check-capabilities-text');
                    loading = document.getElementById('check-capabilities-loading');
                    result = document.getElementById('capabilities-result');
                    console.log('Using Ollama capabilities checker (fallback)');
                } else {
                    // Last resort: try browser first, then ollama
                    btn = browserBtn || ollamaBtn;
                    text = document.getElementById('check-browser-capabilities-text') || document.getElementById('check-capabilities-text');
                    loading = document.getElementById('check-browser-capabilities-loading') || document.getElementById('check-capabilities-loading');
                    result = document.getElementById('browser-capabilities-result') || document.getElementById('capabilities-result');
                    console.log('Using last resort fallback');
                }
            }
            
            console.log('Elements found:', { btn: !!btn, text: !!text, loading: !!loading, result: !!result });
            
            if (!btn || !text || !loading || !result) {
                console.error('Capability checker elements not found:', { btn: !!btn, text: !!text, loading: !!loading, result: !!result });
                alert('Error: Unable to find capability checker elements. Please refresh the page.');
                return;
            }
            
            btn.disabled = true;
            text.classList.add('hidden');
            loading.classList.remove('hidden');
            result.classList.add('hidden');
            
            try {
                const capabilities = {
                    // CPU Information
                    cpuCores: navigator.hardwareConcurrency || 'Unknown',
                    platform: navigator.platform || 'Unknown',
                    userAgent: navigator.userAgent,
                    
                    // Memory Information (if available)
                    // Note: navigator.deviceMemory is unreliable and often inaccurate on macOS
                    // It may show incorrect values (e.g., 1GB on a 16GB Mac)
                    deviceMemory: (() => {
                        // Check for Apple Silicon Macs first (deviceMemory is often wrong on macOS)
                        const isMac = navigator.platform.includes('Mac') || navigator.userAgent.includes('Mac');
                        const isAppleSilicon = navigator.userAgent.includes('Apple') || 
                                             (navigator.platform.includes('Mac') && navigator.hardwareConcurrency >= 4);
                        
                        if (isMac && isAppleSilicon) {
                            // For Apple Silicon Macs, deviceMemory is often incorrect
                            // Check if it's suspiciously low (likely wrong)
                            if (navigator.deviceMemory && navigator.deviceMemory < 4) {
                                return '16GB+ (Estimated - Apple Silicon Mac, browser API inaccurate)';
                            } else if (!navigator.deviceMemory) {
                                return '16GB+ (Estimated - Apple Silicon Mac)';
                            }
                            // If deviceMemory seems reasonable, use it but note it might be wrong
                            return `${navigator.deviceMemory}GB (may be inaccurate on macOS)`;
                        }
                        
                        // For other systems, use deviceMemory if available
                        if (navigator.deviceMemory) {
                            return `${navigator.deviceMemory}GB`;
                        }
                        return 'Unknown (Browser API not available)';
                    })(),
                    
                    // Storage Information
                    // Note: This is browser storage quota, NOT system disk space
                    storage: null,
                    
                    // GPU Information
                    webgpu: false,
                    webgl: false,
                    gpuInfo: null,
                    
                    // Ollama Status
                    ollamaRunning: false,
                    ollamaModels: []
                };
                
                // Check WebGPU
                if (navigator.gpu) {
                    capabilities.webgpu = true;
                    try {
                        const adapter = await navigator.gpu.requestAdapter();
                        if (adapter) {
                            const info = await adapter.requestDevice();
                            capabilities.gpuInfo = {
                                vendor: adapter.info?.vendor || 'Unknown',
                                architecture: adapter.info?.architecture || 'Unknown'
                            };
                        }
                    } catch (e) {
                        console.log('WebGPU info not available:', e);
                    }
                }
                
                // Check WebGL
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (gl) {
                    capabilities.webgl = true;
                    // Try modern API first (Firefox and newer browsers)
                    try {
                        const renderer = gl.getParameter(gl.RENDERER);
                        const vendor = gl.getParameter(gl.VENDOR);
                        if (renderer) {
                            capabilities.gpuInfo = capabilities.gpuInfo || {};
                            capabilities.gpuInfo.renderer = renderer;
                            capabilities.gpuInfo.vendor = vendor;
                        }
                    } catch (e) {
                        // Fallback to deprecated API for older browsers
                        try {
                            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                            if (debugInfo) {
                                capabilities.gpuInfo = capabilities.gpuInfo || {};
                                capabilities.gpuInfo.renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                                capabilities.gpuInfo.vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                            }
                        } catch (e2) {
                            console.log('WebGL info not available:', e2);
                        }
                    }
                }
                
                // Check Storage
                // Note: This shows browser storage quota, NOT system disk space
                if (navigator.storage && navigator.storage.estimate) {
                    try {
                        const estimate = await navigator.storage.estimate();
                        capabilities.storage = {
                            quota: (estimate.quota / (1024 * 1024 * 1024)).toFixed(2) + 'GB',
                            usage: (estimate.usage / (1024 * 1024 * 1024)).toFixed(2) + 'GB',
                            available: ((estimate.quota - estimate.usage) / (1024 * 1024 * 1024)).toFixed(2) + 'GB',
                            note: 'Browser storage quota (not system disk space)'
                        };
                    } catch (e) {
                        console.log('Storage estimate not available:', e);
                    }
                }
                
                // Check Ollama (only if Ollama config is visible AND we're checking Ollama capabilities)
                // Don't check Ollama when checking browser capabilities
                const ollamaConfig = document.getElementById('ollama-config');
                const isBrowserCheck = result && result.id === 'browser-capabilities-result';
                if (ollamaConfig && ollamaConfig.style.display !== 'none' && !isBrowserCheck) {
                    const ollamaUrl = document.getElementById('ollama_base_url')?.value || 'http://localhost:11434';
                    try {
                        // Use server-side proxy to avoid CORS issues
                        const formData = new FormData();
                        formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                        formData.append('base_url', ollamaUrl);
                        
                        const response = await fetch('/api/test-ollama-connection.php', {
                            method: 'POST',
                            body: formData,
                            signal: AbortSignal.timeout(3000)
                        });
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.model_count > 0) {
                                capabilities.ollamaRunning = true;
                                capabilities.ollamaModels = data.models?.map(m => m.name) || [];
                            }
                        }
                    } catch (e) {
                        // Ollama not running or not accessible
                        capabilities.ollamaRunning = false;
                    }
                }
                
                // Generate recommendations
                const recommendations = generateRecommendations(capabilities);
                
                console.log('Capabilities checked:', capabilities);
                console.log('Recommendations generated:', recommendations);
                
                // Display results - pass the result element we already found
                console.log('About to call displayCapabilities, result element:', result);
                displayCapabilities(capabilities, recommendations, result);
                console.log('displayCapabilities call completed');
                
            } catch (error) {
                console.error('Error checking capabilities:', error);
                if (result) {
                    result.innerHTML = `
                        <div class="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                            <strong>Error:</strong> Unable to check system capabilities. ${error.message}
                            <br><small>Please check the browser console (F12) for more details.</small>
                        </div>
                    `;
                    result.classList.remove('hidden');
                } else {
                    alert('Error: Unable to check system capabilities. ' + error.message);
                }
            } finally {
                if (btn) btn.disabled = false;
                if (text) text.classList.remove('hidden');
                if (loading) loading.classList.add('hidden');
            }
        }
        
        function generateRecommendations(capabilities) {
            const recommendations = {
                ollama: [],
                browser: [],
                warnings: []
            };
            
            // Detect browser type
            const isFirefox = navigator.userAgent.toLowerCase().includes('firefox');
            
            // Ollama recommendations based on CPU cores and memory
            const cores = parseInt(capabilities.cpuCores) || 0;
            const memory = parseFloat(capabilities.deviceMemory) || 0;
            
            if (capabilities.ollamaRunning) {
                recommendations.ollama.push({
                    model: 'Any model you have installed',
                    reason: 'Ollama is running and ready to use',
                    status: 'ready'
                });
            } else {
                if (cores >= 8 && memory >= 16) {
                    recommendations.ollama.push({
                        model: 'Llama 3.3 70B, Llama 3.1 70B, or larger models',
                        reason: 'Your system has excellent resources (8+ cores, 16GB+ RAM)',
                        status: 'recommended'
                    });
                }
                if (cores >= 4 && memory >= 8) {
                    recommendations.ollama.push({
                        model: 'Llama 3.2 3B, Llama 3.1 8B, Mistral 7B',
                        reason: 'Your system can handle medium-sized models (4+ cores, 8GB+ RAM)',
                        status: 'recommended'
                    });
                }
                if (cores >= 2 && memory >= 4) {
                    recommendations.ollama.push({
                        model: 'Llama 3.2 1B, Phi-3 Mini',
                        reason: 'Your system can run smaller models (2+ cores, 4GB+ RAM)',
                        status: 'recommended'
                    });
                }
                if (cores < 2 || memory < 4) {
                    recommendations.warnings.push('Your system may struggle with local AI. Consider using Browser-Based AI or cloud APIs.');
                }
            }
            
            // Browser AI recommendations
            // Note: Browser-Based AI (WebLLM) does not work in Firefox
            if (isFirefox) {
                recommendations.warnings.push('Browser-Based AI (WebLLM) is not supported in Firefox. Please use a Chromium-based browser (Chrome, Edge, Brave, Opera) or Safari for browser-based AI features.');
            } else if (capabilities.webgpu || capabilities.webgl) {
                if (capabilities.storage && parseFloat(capabilities.storage.available) >= 4) {
                    recommendations.browser.push({
                        model: 'Llama 3.2, Mistral 7B, Phi-3',
                        reason: 'Your browser supports GPU acceleration and has sufficient storage (4GB+)',
                        status: 'recommended'
                    });
                } else if (capabilities.storage && parseFloat(capabilities.storage.available) >= 2) {
                    recommendations.browser.push({
                        model: 'Llama 3.2 (smaller variant), Phi-3 Mini',
                        reason: 'Your browser supports GPU acceleration but limited storage (2GB+)',
                        status: 'limited'
                    });
                } else {
                    recommendations.warnings.push('Browser storage may be limited. Clear some space for better model options.');
                }
            } else {
                recommendations.warnings.push('Your browser does not support WebGPU or WebGL. Browser-Based AI may not work.');
            }
            
            return recommendations;
        }
        
        function displayCapabilities(capabilities, recommendations, resultElement) {
            // Use the passed element or try to find it
            const result = resultElement || document.getElementById('capabilities-result') || document.getElementById('browser-capabilities-result');
            
            console.log('displayCapabilities called, result element:', result, 'passed element:', resultElement);
            
            if (!result) {
                console.error('Result element not found for displayCapabilities');
                alert('Error: Unable to display results. Please refresh the page.');
                return;
            }
            
            let html = '<div class="space-y-4">';
            
            // System Information
            html += '<div class="bg-white p-4 rounded border border-gray-200">';
            html += '<h4 class="font-semibold text-gray-900 mb-3">System Information</h4>';
            html += '<dl class="grid grid-cols-2 gap-2 text-sm">';
            html += `<dt class="font-medium text-gray-700">CPU Cores:</dt><dd class="text-gray-900">${capabilities.cpuCores}</dd>`;
            html += `<dt class="font-medium text-gray-700">Platform:</dt><dd class="text-gray-900">${capabilities.platform}</dd>`;
            html += `<dt class="font-medium text-gray-700">System RAM:</dt><dd class="text-gray-900">${capabilities.deviceMemory}</dd>`;
            if (capabilities.storage) {
                html += `<dt class="font-medium text-gray-700">Browser Storage:</dt><dd class="text-gray-900">${capabilities.storage.available} <span class="text-sm text-gray-500">(quota, not disk space)</span></dd>`;
            }
            if (capabilities.gpuInfo) {
                html += `<dt class="font-medium text-gray-700">GPU:</dt><dd class="text-gray-900">${capabilities.gpuInfo.renderer || capabilities.gpuInfo.architecture || 'Detected'}</dd>`;
            }
            html += `<dt class="font-medium text-gray-700">WebGPU:</dt><dd class="text-gray-900">${capabilities.webgpu ? '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Supported</span>' : '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Not Supported</span>'}</dd>`;
            html += `<dt class="font-medium text-gray-700">WebGL:</dt><dd class="text-gray-900">${capabilities.webgl ? '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Supported</span>' : '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Not Supported</span>'}</dd>`;
            // Only show Ollama status if we're checking Ollama capabilities (not browser)
            if (resultElement && resultElement.id !== 'browser-capabilities-result') {
                html += `<dt class="font-medium text-gray-700">Ollama Status:</dt><dd class="text-gray-900">${capabilities.ollamaRunning ? '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Running</span>' : '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Not Running</span>'}</dd>`;
            }
            html += '</dl></div>';
            
            // Ollama Recommendations (only show when checking Ollama capabilities, not browser)
            if (recommendations.ollama.length > 0 && resultElement && resultElement.id !== 'browser-capabilities-result') {
                html += '<div class="bg-blue-50 p-4 rounded border border-blue-200">';
                html += '<h4 class="font-semibold text-gray-900 mb-2">Local Ollama Recommendations</h4>';
                html += '<ul class="space-y-2 text-sm">';
                recommendations.ollama.forEach(rec => {
                    const statusClass = rec.status === 'ready' ? 'text-green-700' : 'text-blue-700';
                    html += `<li class="${statusClass}"><strong>${rec.model}:</strong> ${rec.reason}</li>`;
                });
                if (capabilities.ollamaModels.length > 0) {
                    html += `<li class="text-gray-700"><strong>Installed Models:</strong> ${capabilities.ollamaModels.join(', ')}</li>`;
                }
                html += '</ul></div>';
            }
            
            // Browser AI Recommendations (only show when checking browser capabilities, not Ollama)
            if (recommendations.browser.length > 0 && resultElement && resultElement.id === 'browser-capabilities-result') {
                html += '<div class="bg-purple-50 p-4 rounded border border-purple-200">';
                html += '<h4 class="font-semibold text-gray-900 mb-2">Browser-Based AI Recommendations</h4>';
                html += '<ul class="space-y-2 text-sm">';
                recommendations.browser.forEach(rec => {
                    const statusClass = rec.status === 'recommended' ? 'text-purple-700' : 'text-yellow-700';
                    html += `<li class="${statusClass}"><strong>${rec.model}:</strong> ${rec.reason}</li>`;
                });
                html += '</ul></div>';
            }
            
            // Warnings
            if (recommendations.warnings.length > 0) {
                html += '<div class="bg-yellow-50 p-4 rounded border border-yellow-200">';
                html += '<h4 class="font-semibold text-gray-900 mb-2 flex items-center gap-2"><svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Important Notes</h4>';
                html += '<ul class="space-y-1 text-sm text-yellow-800">';
                recommendations.warnings.forEach(warning => {
                    html += `<li>${warning}</li>`;
                });
                html += '</ul></div>';
            }
            
            html += '</div>';
            
            console.log('Setting HTML to result element, HTML length:', html.length);
            console.log('Result element before update:', result, 'Hidden:', result.classList.contains('hidden'), 'Display:', window.getComputedStyle(result).display);
            
            result.innerHTML = html;
            result.classList.remove('hidden');
            
            // Force display in case hidden class isn't enough
            result.style.display = 'block';
            
            console.log('Result element after update:', result, 'Hidden class:', result.classList.contains('hidden'), 'Display:', window.getComputedStyle(result).display);
            console.log('Result element HTML:', result.innerHTML.substring(0, 200));
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleServiceFields();
        });
    </script>
</body>
</html>

