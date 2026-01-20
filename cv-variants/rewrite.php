<?php
/**
 * AI CV Rewriting Interface
 * Generate job-specific CV variants using AI
 */

require_once __DIR__ . '/../php/helpers.php';

requireAuth();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get user's job applications
$jobApplications = db()->fetchAll(
    "SELECT id, company_name, job_title, job_description, notes 
     FROM job_applications 
     WHERE user_id = ? 
     ORDER BY created_at DESC",
    [$user['id']]
);

// Get user's CV variants
$variants = getUserCvVariants($user['id']);

// Suggest a unique variant name
$suggestedVariantName = suggestUniqueVariantName($user['id'], 'AI-Generated CV');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Generate AI CV | Simple CV Builder',
        'metaDescription' => 'Generate a job-specific CV using AI.',
        'canonicalUrl' => APP_URL . '/cv-variants/rewrite.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Generate AI CV</h1>
                <p class="mt-1 text-sm text-gray-500">Create a job-specific version of your CV using AI</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form id="rewrite-form" class="space-y-6">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">

                    <!-- Source CV Selection -->
                    <div>
                        <label for="cv_variant_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Source CV
                        </label>
                        <select id="cv_variant_id" name="cv_variant_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Master CV (Default)</option>
                            <?php foreach ($variants as $variant): ?>
                                <option value="<?php echo e($variant['id']); ?>">
                                    <?php echo e($variant['variant_name']); ?>
                                    <?php if ($variant['is_master']): ?> (Master)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Select which CV to use as the base for rewriting</p>
                    </div>

                    <!-- Job Application Selection -->
                    <div>
                        <label for="job_application_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Job Application (Optional)
                        </label>
                        <select id="job_application_id" name="job_application_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a job application...</option>
                            <?php foreach ($jobApplications as $jobApp): ?>
                                <?php 
                                // Get job description and decode any existing HTML entities to prevent double encoding
                                $jobDesc = $jobApp['job_description'] ?? $jobApp['notes'] ?? '';
                                // Decode any existing HTML entities first
                                $jobDesc = html_entity_decode($jobDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // Then encode properly for HTML attribute
                                $jobDescEncoded = htmlspecialchars($jobDesc, ENT_QUOTES, 'UTF-8');
                                ?>
                                <option value="<?php echo e($jobApp['id']); ?>" 
                                        data-description="<?php echo $jobDescEncoded; ?>">
                                    <?php echo e($jobApp['company_name']); ?> - <?php echo e($jobApp['job_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Or paste a job description below</p>
                    </div>

                    <!-- Job Description -->
                    <div>
                        <label for="job_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Job Description <span class="text-red-600">*</span>
                        </label>
                        <textarea id="job_description" 
                                  name="job_description" 
                                  rows="8" 
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Paste the job description here..."></textarea>
                        <p class="mt-1 text-sm text-gray-500">The AI will rewrite your CV to match this job description</p>
                    </div>

                    <!-- Prompt Instructions -->
                    <?php
                    // Check if user has saved custom instructions
                    $userProfile = db()->fetchOne(
                        "SELECT cv_rewrite_prompt_instructions FROM profiles WHERE id = ?",
                        [$user['id']]
                    );
                    $hasSavedInstructions = !empty($userProfile['cv_rewrite_prompt_instructions'] ?? '');
                    ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Prompt Instructions
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" 
                                       id="prompt_default" 
                                       name="prompt_instructions_mode" 
                                       value="default"
                                       <?php echo !$hasSavedInstructions ? 'checked' : ''; ?>
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="prompt_default" class="ml-2 text-sm text-gray-700">
                                    Use default instructions
                                </label>
                            </div>
                            <?php if ($hasSavedInstructions): ?>
                            <div class="flex items-center">
                                <input type="radio" 
                                       id="prompt_saved" 
                                       name="prompt_instructions_mode" 
                                       value="saved"
                                       checked
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="prompt_saved" class="ml-2 text-sm text-gray-700">
                                    Use saved custom instructions
                                </label>
                                <a href="/cv-prompt-settings.php" class="ml-2 text-xs text-blue-600 hover:text-blue-800 underline">(edit)</a>
                            </div>
                            <?php endif; ?>
                            <div class="flex items-start">
                                <input type="radio" 
                                       id="prompt_custom" 
                                       name="prompt_instructions_mode" 
                                       value="custom"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 mt-1">
                                <div class="ml-2 flex-1">
                                    <label for="prompt_custom" class="text-sm text-gray-700">
                                        Enter custom instructions for this generation only
                                    </label>
                                    <textarea id="prompt_custom_text" 
                                              name="prompt_custom_text" 
                                              rows="6" 
                                              maxlength="2000"
                                              class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm hidden"
                                              placeholder="Enter custom instructions that will only be used for this CV generation..."></textarea>
                                    <p class="mt-1 text-xs text-gray-500 hidden" id="prompt_custom_help">
                                        These instructions will only be used for this CV generation and won't be saved.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            <a href="/cv-prompt-settings.php" class="text-blue-600 hover:text-blue-800">Manage saved instructions</a> or 
                            <a href="/resources/ai/prompt-best-practices.php" class="text-blue-600 hover:text-blue-800">learn best practices</a>
                        </p>
                    </div>

                    <!-- Sections to Rewrite -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sections to Rewrite
                        </label>
                        <div class="space-y-2 border border-gray-300 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_professional_summary" 
                                       name="sections_to_rewrite[]" 
                                       value="professional_summary"
                                       checked
                                       disabled
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_professional_summary" class="ml-2 text-sm text-gray-700">
                                    Professional Summary <span class="text-gray-500">(always included)</span>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_work_experience" 
                                       name="sections_to_rewrite[]" 
                                       value="work_experience"
                                       checked
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_work_experience" class="ml-2 text-sm text-gray-700">
                                    Work Experience
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_skills" 
                                       name="sections_to_rewrite[]" 
                                       value="skills"
                                       checked
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_skills" class="ml-2 text-sm text-gray-700">
                                    Skills
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_education" 
                                       name="sections_to_rewrite[]" 
                                       value="education"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_education" class="ml-2 text-sm text-gray-700">
                                    Education
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_projects" 
                                       name="sections_to_rewrite[]" 
                                       value="projects"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_projects" class="ml-2 text-sm text-gray-700">
                                    Projects
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_certifications" 
                                       name="sections_to_rewrite[]" 
                                       value="certifications"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_certifications" class="ml-2 text-sm text-gray-700">
                                    Certifications
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_professional_memberships" 
                                       name="sections_to_rewrite[]" 
                                       value="professional_memberships"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_professional_memberships" class="ml-2 text-sm text-gray-700">
                                    Professional Memberships
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="section_interests" 
                                       name="sections_to_rewrite[]" 
                                       value="interests"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="section_interests" class="ml-2 text-sm text-gray-700">
                                    Interests
                                </label>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Select which sections of your CV should be rewritten to match the job description. 
                            <a href="/cv-prompt-settings.php" class="text-blue-600 hover:text-blue-800">Customise prompt instructions</a> for better results.
                        </p>
                    </div>

                    <!-- Variant Name -->
                    <div>
                        <label for="variant_name" class="block text-sm font-medium text-gray-700 mb-2">
                            CV Variant Name
                        </label>
                        <input type="text" 
                               id="variant_name" 
                               name="variant_name" 
                               value="<?php echo e($suggestedVariantName); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Give this CV variant a name for easy identification</p>
                    </div>

                    <!-- Cost Warning -->
                    <div id="cost-warning" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Cost Notice:</strong> You're using a paid AI service. This generation will incur API costs. 
                                    <a href="/ai-settings.php" class="underline font-semibold">Switch to free options (Local Ollama or Browser-Based AI)</a> to avoid charges.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <a href="/cv-variants.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                    <button type="submit" 
                            id="submit-btn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="submit-text">Generate CV</span>
                        <span id="submit-loading" class="hidden">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating...
                        </span>
                    </button>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">How it works</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>The AI will analyse your CV and the job description, then rewrite relevant sections to better match the job requirements. You can review and edit the generated CV variant after it's created.</p>
                            <p class="mt-2 text-xs text-blue-600"><strong>Note:</strong> Generation may take 30-60 seconds. Please wait for the process to complete.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <!-- Browser AI Service Scripts -->
    <script src="/js/model-cache-manager.js"></script>
    <script src="/js/browser-ai-service.js"></script>

    <script>
        // Handle prompt instructions mode selection
        document.addEventListener('DOMContentLoaded', () => {
            const promptModes = document.querySelectorAll('input[name="prompt_instructions_mode"]');
            const customTextarea = document.getElementById('prompt_custom_text');
            const customHelp = document.getElementById('prompt_custom_help');
            
            function toggleCustomTextarea() {
                const selectedMode = document.querySelector('input[name="prompt_instructions_mode"]:checked');
                if (selectedMode && selectedMode.value === 'custom') {
                    customTextarea.classList.remove('hidden');
                    customHelp.classList.remove('hidden');
                    customTextarea.required = true;
                } else {
                    customTextarea.classList.add('hidden');
                    customHelp.classList.add('hidden');
                    customTextarea.required = false;
                }
            }
            
            promptModes.forEach(mode => {
                mode.addEventListener('change', toggleCustomTextarea);
            });
            
            toggleCustomTextarea();
        });
        
        document.getElementById('job_application_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset.description) {
                document.getElementById('job_description').value = selectedOption.dataset.description;
            }
        });

        // Check AI service and show cost warning if using paid service
        (async function() {
            try {
                const response = await fetch('/api/get-ai-service.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    const paidServices = ['openai', 'anthropic', 'gemini', 'grok'];
                    if (data.service && paidServices.includes(data.service.toLowerCase())) {
                        document.getElementById('cost-warning').classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Failed to check AI service:', error);
            }
        })();

        document.getElementById('rewrite-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            
            // Show loading overlay
            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'generation-loading-overlay';
            loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            loadingOverlay.innerHTML = `
                <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
                    <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Generating Your CV</h3>
                    <p class="text-sm text-gray-600 mb-4">This may take 30-60 seconds. Please wait...</p>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
            
            const formData = new FormData(this);
            
            try {
                // Create AbortController for timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 180000); // 3 minute timeout
                
                const response = await fetch('/api/ai-rewrite-cv.php', {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                // Remove loading overlay
                loadingOverlay.remove();
                
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { error: errorText || 'Server error' };
                    }
                    throw new Error(errorData.error || 'Generation failed');
                }
                
                const result = await response.json();
                
                // Check if this is browser AI execution
                if (result.success && result.browser_execution) {
                    // Browser AI mode - execute client-side
                    console.log('Browser AI execution detected, starting client-side processing...', result);
                    await executeBrowserAI(result, loadingOverlay);
                    return;
                }
                
                if (result.success) {
                    window.location.href = '/cv-variants.php?success=CV generated successfully';
                } else {
                    throw new Error(result.error || 'Failed to generate CV');
                }
            } catch (error) {
                // Remove loading overlay
                if (loadingOverlay && loadingOverlay.parentNode) {
                    loadingOverlay.remove();
                }
                
                console.error('Error:', error);
                
                let errorMessage = 'An error occurred. Please try again.';
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timed out. The generation is taking longer than expected. Please try again or check if Ollama is running properly.';
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                alert('Error: ' + errorMessage);
                
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        });

        // Execute browser AI for CV rewriting
        async function executeBrowserAI(result, loadingOverlay) {
            try {
                console.log('executeBrowserAI called with result:', result);
                
                // Check browser support
                const support = BrowserAIService.checkBrowserSupport();
                console.log('Browser support check:', support);
                if (!support.required) {
                    throw new Error('Browser does not support WebGPU or WebGL. Browser AI requires a modern browser with GPU support.');
                }

                // Update loading overlay to show model loading
                if (loadingOverlay) {
                    loadingOverlay.querySelector('p').textContent = 'Loading AI model. This may take a few minutes on first use...';
                }

                // Initialize browser AI
                const modelType = result.model_type === 'webllm' ? 'webllm' : 'tensorflow';
                console.log('Initializing browser AI:', { modelType, model: result.model });
                await BrowserAIService.initBrowserAI(modelType, result.model, (progress) => {
                    if (loadingOverlay && progress.message) {
                        loadingOverlay.querySelector('p').textContent = progress.message;
                    }
                });

                // Update loading overlay
                if (loadingOverlay) {
                    loadingOverlay.querySelector('p').textContent = 'Rewriting CV... This may take 30-60 seconds.';
                }

                // Generate rewritten CV using browser AI
                console.log('Generating text with prompt length:', result.prompt?.length);
                const rewrittenText = await BrowserAIService.generateText(result.prompt, {
                    temperature: 0.7,
                    maxTokens: 4000
                });
                console.log('Generated text length:', rewrittenText?.length);
                console.log('Generated text preview (first 500 chars):', rewrittenText?.substring(0, 500));

                // Clean and parse rewritten CV JSON (using robust parsing similar to template generation)
                let cleanedText = rewrittenText.trim();
                
                // Remove AI model metadata tokens
                cleanedText = cleanedText.replace(/<\|[^|]+\|>/g, '');
                cleanedText = cleanedText.replace(/<\|start_header_id\|>[^<]*<\|end_header_id\|>/g, '');
                cleanedText = cleanedText.replace(/assistant/g, '');
                
                // Remove explanatory text BEFORE JSON (common AI pattern)
                cleanedText = cleanedText.replace(/Here is the rewritten CV:[\s\n]*/gi, '');
                cleanedText = cleanedText.replace(/Here is the JSON:[\s\n]*/gi, '');
                cleanedText = cleanedText.replace(/Here is your rewritten CV:[\s\n]*/gi, '');
                cleanedText = cleanedText.replace(/Here is the rewritten JSON object[\s\S]*?\{/gi, '{');
                cleanedText = cleanedText.replace(/The following is[\s\S]*?\{/gi, '{');
                cleanedText = cleanedText.replace(/Here is[\s\S]*?the\s+rewritten[\s\S]*?\{/gi, '{');
                
                // Remove markdown code blocks
                cleanedText = cleanedText.replace(/```json\s*/gi, '');
                cleanedText = cleanedText.replace(/```\s*/g, '');
                
                // Find the main JSON object by looking for the expected structure
                // Look for patterns like { "professional_summary" or { "work_experience"
                // This helps us skip over any explanatory text that might be embedded in the response
                const mainJsonPattern = /\{\s*"professional_summary"|\{\s*"work_experience"/i;
                const mainJsonMatch = cleanedText.match(mainJsonPattern);
                if (mainJsonMatch) {
                    const mainJsonStart = cleanedText.indexOf(mainJsonMatch[0]);
                    // Remove everything before this point (including any embedded explanatory text)
                    cleanedText = cleanedText.substring(mainJsonStart);
                } else {
                    // Fallback: Look for any { followed by a quote and a known key
                    const fallbackPattern = /\{\s*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)"/i;
                    const fallbackMatch = cleanedText.match(fallbackPattern);
                    if (fallbackMatch) {
                        const fallbackStart = cleanedText.indexOf(fallbackMatch[0]);
                        cleanedText = cleanedText.substring(fallbackStart);
                    }
                }
                
                // Before extracting, fix cases where JSON objects are embedded inside string values
                // Pattern: "key": "value...\n\n{ "professional_summary" or "key": "value...\n\n{ "work_experience"
                // This happens when AI puts explanatory text + JSON inside a string value
                // Solution: Close the string value before the embedded JSON starts
                // Use [\s\S]*? instead of [^"]*? to match newlines and any character
                let fixCount = 0;
                
                // More aggressive approach: Find any string value that contains a { followed by a JSON key
                // This handles cases like: "content": "...text,\"\"\n\n{\n  \"professional_summary"
                // We'll iterate through the text and find string values that contain embedded JSON
                const stringValuePattern = /("(?:content|description|name|title)":\s*")([\s\S]*?)(?="\s*[,}])/gi;
                let match;
                const replacements = [];
                
                while ((match = stringValuePattern.exec(cleanedText)) !== null) {
                    const [fullMatch, keyPart, valuePart] = match;
                    const valueStart = match.index + keyPart.length;
                    const valueEnd = valueStart + valuePart.length;
                    
                    // Check if this string value contains a { followed by a JSON key
                    const embeddedJsonPattern = /\{\s*\n?\s*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)"/i;
                    const embeddedMatch = valuePart.match(embeddedJsonPattern);
                    
                    if (embeddedMatch) {
                        // Find where the embedded JSON starts
                        const embeddedStart = valuePart.indexOf(embeddedMatch[0]);
                        // Close the string before the embedded JSON
                        const newValuePart = valuePart.substring(0, embeddedStart);
                        const replacement = {
                            start: valueStart,
                            end: valueEnd,
                            newValue: newValuePart + '"'
                        };
                        replacements.push(replacement);
                        fixCount++;
                        console.log(`Found embedded JSON in string value at position ${valueStart}-${valueEnd}, closing string at ${valueStart + embeddedStart}`);
                    }
                }
                
                // Apply replacements in reverse order to maintain positions
                replacements.reverse().forEach(replacement => {
                    cleanedText = cleanedText.substring(0, replacement.start) + 
                                  replacement.newValue + 
                                  cleanedText.substring(replacement.end);
                });
                
                // Also try simpler regex patterns as fallback
                // Pattern 1: Look for string values that contain a newline followed by { and a JSON key
                cleanedText = cleanedText.replace(/("(?:content|description|name|title)":\s*")([\s\S]*?)(\n\n\s*\{[\s\n]*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)")/gi, (match, keyPart, valuePart, jsonPart) => {
                    fixCount++;
                    console.log(`Fixed embedded JSON pattern 1 (fix #${fixCount})`);
                    return keyPart + valuePart + '"' + jsonPart;
                });
                
                // Pattern 2: Handle cases with escaped quotes before the JSON (like "tools,\"\"\n\n{")
                // Match various forms: ,\"\", ,"", ,\\"\\", etc.
                cleanedText = cleanedText.replace(/("(?:content|description|name|title)":\s*")([\s\S]*?)((?:,\\"\\"|,""|,\\"")?\s*\n\n\s*\{[\s\n]*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)")/gi, (match, keyPart, valuePart, jsonPart) => {
                    // Only fix if jsonPart starts with comma and quotes or just newlines and brace
                    if (jsonPart.match(/^(?:,\\"\\"|,""|,\\"")?\s*\n\n\s*\{/)) {
                        fixCount++;
                        console.log(`Fixed embedded JSON pattern 2 (fix #${fixCount})`);
                        // Remove any comma and quotes before the brace
                        const cleanedJsonPart = jsonPart.replace(/^(?:,\\"\\"|,""|,\\"")?\s*/, '');
                        return keyPart + valuePart + '"' + cleanedJsonPart;
                    }
                    return match;
                });
                
                // Pattern 3: Handle single newline cases
                cleanedText = cleanedText.replace(/("(?:content|description|name|title)":\s*")([\s\S]*?)(\n\s*\{[\s\n]*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)")/gi, (match, keyPart, valuePart, jsonPart) => {
                    // Verify this is actually embedded JSON by checking what comes after
                    const matchIndex = cleanedText.indexOf(match);
                    if (matchIndex !== -1) {
                        const afterMatch = cleanedText.substring(matchIndex + match.length);
                        if (afterMatch.match(/^\s*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)"/i)) {
                            fixCount++;
                            console.log(`Fixed embedded JSON pattern 3 (fix #${fixCount})`);
                            return keyPart + valuePart + '"' + jsonPart;
                        }
                    }
                    return match;
                });
                
                if (fixCount > 0) {
                    console.log(`Applied ${fixCount} fix(es) for embedded JSON in string values`);
                }
                
                // Extract JSON object (from first { to last })
                // But be smarter: find the MAIN JSON object, not one embedded in a string
                // Reuse mainJsonPattern from above if it exists, otherwise create it
                const mainJsonMatch2 = cleanedText.match(mainJsonPattern);
                let firstBrace, lastBrace;
                
                if (mainJsonMatch2) {
                    // Start from the main JSON object
                    firstBrace = cleanedText.indexOf(mainJsonMatch2[0]);
                    // Find the matching closing brace
                    lastBrace = cleanedText.lastIndexOf('}');
                } else {
                    // Fallback: use first { to last }
                    firstBrace = cleanedText.indexOf('{');
                    lastBrace = cleanedText.lastIndexOf('}');
                }
                
                if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                    cleanedText = cleanedText.substring(firstBrace, lastBrace + 1);
                } else {
                    const jsonMatch = cleanedText.match(/\{[\s\S]*\}/);
                    if (jsonMatch) {
                        cleanedText = jsonMatch[0];
                    }
                }
                
                // Remove explanatory text that might be INSIDE JSON string values
                // Pattern: Look for common explanatory phrases followed by JSON structure inside string values
                // This handles cases where AI puts explanatory text in the middle of a JSON string
                // We need to be careful - match the pattern but preserve the string structure
                
                let postExtractionFixCount = 0;
                
                // Pattern 1: Explanatory text followed by { inside a string value
                // Match: "key": "value...\n\nHere is...{" and replace with "key": "value..."
                const beforePattern1 = cleanedText;
                cleanedText = cleanedText.replace(/("(?:content|description)":\s*")([^"]*?)(\n\nHere is[\s\S]*?\{)/gi, '$1$2"');
                if (cleanedText !== beforePattern1) {
                    postExtractionFixCount++;
                    console.log(`Post-extraction fix pattern 1 applied`);
                }
                
                cleanedText = cleanedText.replace(/("(?:content|description)":\s*")([^"]*?)(Here is the rewritten JSON object[\s\S]*?\{)/gi, '$1$2"');
                
                // Pattern 2: Handle escaped quotes followed by JSON (like "tools,\"\"\n\n{")
                // This is the main issue we're seeing - string values that contain ,\"\"\n\n{ followed by JSON
                const beforePattern2 = cleanedText;
                cleanedText = cleanedText.replace(/("(?:content|description|name|title)":\s*")([\s\S]*?)((?:,\\"\\"|,""|,\\"")?\s*\n\n\s*\{[\s\n]*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)")/gi, (match, keyPart, valuePart, jsonPart) => {
                    postExtractionFixCount++;
                    console.log(`Post-extraction fix pattern 2 applied (escaped quotes)`);
                    // Remove any comma and quotes before the brace, close the string
                    const cleanedJsonPart = jsonPart.replace(/^(?:,\\"\\"|,""|,\\"")?\s*/, '');
                    return keyPart + valuePart + '"' + cleanedJsonPart;
                });
                
                // Pattern 3: If we see a { inside a string value that's followed by "professional_summary" or "work_experience",
                // it's likely explanatory text - remove everything from "Here is" to the closing quote before the {
                cleanedText = cleanedText.replace(/("(?:content|description|name)":\s*"[^"]*?)(Here is[\s\S]*?)(\n\s*\{[\s\S]*?"(?:professional_summary|work_experience)")/gi, '$1"');
                
                // Pattern 4: More aggressive - if we see unescaped newlines followed by { and a JSON key, remove the explanatory part
                cleanedText = cleanedText.replace(/("(?:content|description)":\s*"[^"]*?)(\n\n[^"]*?Here is[\s\S]*?\{[\s\S]*?"(?:professional_summary|work_experience)")/gi, '$1"');
                
                // Pattern 5: Catch any string value that contains { followed by a JSON key (most aggressive)
                // This handles cases where the pattern doesn't match the above patterns
                const beforePattern5 = cleanedText;
                cleanedText = cleanedText.replace(/("(?:content|description|name|title)":\s*")([\s\S]*?)(\n\n\s*\{[\s\n]*"(?:professional_summary|work_experience|skills|education|projects|certifications|interests)")/gi, (match, keyPart, valuePart, jsonPart) => {
                    postExtractionFixCount++;
                    console.log(`Post-extraction fix pattern 5 applied (newline+brace)`);
                    return keyPart + valuePart + '"' + jsonPart;
                });
                
                if (postExtractionFixCount > 0) {
                    console.log(`Applied ${postExtractionFixCount} post-extraction fix(es) for embedded JSON in string values`);
                }
                
                // Try parsing first - if it works, we're done!
                // Only apply fixes if parsing fails
                
                // Parse JSON with robust error handling
                let rewrittenData;
                try {
                    // First attempt: try parsing as-is (often works!)
                    rewrittenData = JSON.parse(cleanedText);
                    console.log('JSON parsed successfully on first attempt');
                } catch (e) {
                    console.log('First parse attempt failed, applying fixes. Error:', e.message);
                    
                    // Only apply fixes if initial parse fails
                    // Fix malformed escaped quotes in keys (like "work_experience\": should be "work_experience":)
                    // But ONLY if the pattern actually exists (don't break valid JSON)
                    if (cleanedText.includes('\\":')) {
                        cleanedText = cleanedText.replace(/"([^"]+)\\":/g, '"$1":');
                    }
                    
                    // Convert literal \n sequences to actual newlines (if they exist as literal sequences)
                    // Pattern: \n that's not already part of \\n (escaped newline in JSON)
                    // We need to be careful - only convert standalone \n sequences
                    cleanedText = cleanedText.replace(/(?<!\\)\\([nrtbf])/g, (match, char) => {
                        if (char === 'n') return '\n';
                        if (char === 'r') return '\r';
                        if (char === 't') return '\t';
                        if (char === 'b') return '\b';
                        if (char === 'f') return '\f';
                        return match;
                    });
                    
                    // Try parsing again after basic fixes
                    try {
                        rewrittenData = JSON.parse(cleanedText);
                        console.log('JSON parsed successfully after basic fixes');
                    } catch (e1) {
                        // If still failing, use sophisticated parser
                    // Try to fix common JSON issues - use sophisticated parser similar to template customizer
                    let fixedText = '';
                    let inString = false;
                    let escapeNext = false;
                    let controlCharsFixed = 0;
                    
                    for (let i = 0; i < cleanedText.length; i++) {
                        const char = cleanedText[i];
                        const code = char.charCodeAt(0);
                        
                        if (escapeNext) {
                            // We're processing an escaped character
                            // Check if it's a control character that needs proper escaping
                            if (inString && ((code >= 0x00 && code <= 0x1F) || code === 0x7F)) {
                                // This is a control character after a backslash - replace with proper escape
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
                                
                                // Be conservative - only close string if we're CERTAIN it's a closing quote
                                // Check for common JSON patterns that indicate end of string value
                                if (lookAheadTrimmed.match(/^\s*[,}\]]/)) {
                                    // Very likely a closing quote - followed by comma or closing brace/bracket
                                    inString = false;
                                    fixedText += char;
                                } else if (lookAheadTrimmed.match(/^\s*:\s*["\[]/)) {
                                    // Followed by colon and quote/bracket (like `": "value"` or `": [`) - likely closing
                                    // This is a JSON key closing, not a value quote
                                    inString = false;
                                    fixedText += char;
                                } else {
                                    // Check if we're at a JSON key boundary (quote followed by colon)
                                    // Look back to see if we're in a key context
                                    let lookBack = fixedText.substring(Math.max(0, fixedText.length - 20));
                                    // If we see pattern like { or , followed by quote, we're likely starting a key
                                    // If we see : after quote, we're closing a key
                                    if (lookAheadTrimmed.startsWith(':')) {
                                        // This quote is closing a JSON key - definitely close the string
                                        inString = false;
                                        fixedText += char;
                                    } else {
                                        // Uncertain - be conservative and escape it (treat as content)
                                        fixedText += '\\"';
                                    }
                                }
                            } else {
                                // Unescaped quote outside string - start a new string
                                inString = true;
                                fixedText += char;
                            }
                            continue;
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
                    if (inString) {
                        fixedText += '"';
                        inString = false;
                    }
                    
                    // Ensure the JSON object is properly closed
                    // Count braces only OUTSIDE of strings to avoid false positives
                    let openBraces = 0;
                    let closeBraces = 0;
                    let inStringForCounting = false;
                    let escapeNextForCounting = false;
                    
                    for (let i = 0; i < fixedText.length; i++) {
                        const char = fixedText[i];
                        if (escapeNextForCounting) {
                            escapeNextForCounting = false;
                            continue;
                        }
                        if (char === '\\') {
                            escapeNextForCounting = true;
                            continue;
                        }
                        if (char === '"') {
                            inStringForCounting = !inStringForCounting;
                            continue;
                        }
                        if (!inStringForCounting) {
                            if (char === '{') openBraces++;
                            if (char === '}') closeBraces++;
                        }
                    }
                    
                    // Only add closing braces if we're actually missing them
                    // But be conservative - don't add too many
                    const missingBraces = openBraces - closeBraces;
                    if (missingBraces > 0 && missingBraces <= 5) { // Limit to prevent over-correction
                        for (let i = 0; i < missingBraces; i++) {
                            fixedText += '}';
                        }
                        console.log(`Added ${missingBraces} closing brace(s) to balance JSON`);
                    } else if (missingBraces < 0) {
                        // Too many closing braces - remove excess from the end
                        const excessBraces = Math.abs(missingBraces);
                        let removed = 0;
                        for (let i = fixedText.length - 1; i >= 0 && removed < excessBraces; i--) {
                            if (fixedText[i] === '}') {
                                fixedText = fixedText.substring(0, i) + fixedText.substring(i + 1);
                                removed++;
                            }
                        }
                        if (removed > 0) {
                            console.log(`Removed ${removed} excess closing brace(s)`);
                        }
                    }
                    
                    // Try parsing again
                    try {
                        rewrittenData = JSON.parse(fixedText);
                        console.log(`JSON parsing succeeded after fixing ${controlCharsFixed} control characters`);
                    } catch (e2) {
                        const errorPos = parseInt(e2.message.match(/position (\d+)/)?.[1] || 0);
                        const contextStart = Math.max(0, errorPos - 200);
                        const contextEnd = Math.min(fixedText.length, errorPos + 200);
                        const context = fixedText.substring(contextStart, contextEnd);
                        
                        console.error('JSON parsing failed after fixes. Context around error:', context);
                        console.error('Fixed text length:', fixedText.length, 'Error position:', errorPos);
                        console.error('Original text preview:', rewrittenText.substring(0, 1000));
                        console.error('Cleaned text preview:', cleanedText.substring(0, 1000));
                        console.error('Fixed text preview:', fixedText.substring(0, 1000));
                        
                        // Try to fix common structural issues before final attempt
                        // Issue: Missing comma after array element or object property
                        // Pattern: } followed by } or ] without comma
                        let structuralFix = fixedText;
                        let structuralFixesApplied = 0;
                        
                        // Fix: } followed by } without comma (missing closing brace for object)
                        // But only if we're not inside a string
                        structuralFix = structuralFix.replace(/(\})\s*(\})/g, (match, brace1, brace2, offset) => {
                            // Check if we're inside a string by counting quotes before this position
                            const before = structuralFix.substring(0, offset);
                            const quoteCount = (before.match(/"/g) || []).length;
                            if (quoteCount % 2 === 0) { // Even number of quotes = outside string
                                structuralFixesApplied++;
                                return brace1 + ',' + brace2;
                            }
                            return match;
                        });
                        
                        // Fix: } followed by ] without comma (object closing inside array)
                        structuralFix = structuralFix.replace(/(\})\s*(\])/g, (match, brace, bracket, offset) => {
                            const before = structuralFix.substring(0, offset);
                            const quoteCount = (before.match(/"/g) || []).length;
                            if (quoteCount % 2 === 0) {
                                // Check if there's already a comma before this
                                const beforeMatch = structuralFix.substring(Math.max(0, offset - 10), offset);
                                if (!beforeMatch.trim().endsWith(',')) {
                                    structuralFixesApplied++;
                                    return brace + ',' + bracket;
                                }
                            }
                            return match;
                        });
                        
                        // Fix: ] followed by } without comma (array closing before object closing)
                        structuralFix = structuralFix.replace(/(\])\s*(\})/g, (match, bracket, brace, offset) => {
                            const before = structuralFix.substring(0, offset);
                            const quoteCount = (before.match(/"/g) || []).length;
                            if (quoteCount % 2 === 0) {
                                const beforeMatch = structuralFix.substring(Math.max(0, offset - 10), offset);
                                if (!beforeMatch.trim().endsWith(',')) {
                                    structuralFixesApplied++;
                                    return bracket + ',' + brace;
                                }
                            }
                            return match;
                        });
                        
                        if (structuralFixesApplied > 0) {
                            console.log(`Applied ${structuralFixesApplied} structural fix(es) for missing commas`);
                            try {
                                rewrittenData = JSON.parse(structuralFix);
                                console.log('JSON parsing succeeded after structural fixes');
                                fixedText = structuralFix; // Use the fixed version
                            } catch (e3) {
                                // Structural fixes didn't work, continue to final attempt
                            }
                        }
                        
                        // Try one more time with even more aggressive fixes
                        // The AI might be generating literal \n sequences that need to be converted to actual newlines first
                        let finalAttempt = fixedText;
                        // Convert literal \n sequences to actual newlines (but only if they're not already escaped)
                        // Pattern: \n that's not preceded by a backslash (or is preceded by an even number of backslashes)
                        finalAttempt = finalAttempt.replace(/(?<!\\)(?:\\\\)*\\([nrt])/g, (match, char) => {
                            if (char === 'n') return '\n';
                            if (char === 'r') return '\r';
                            if (char === 't') return '\t';
                            return match;
                        });
                        // Now escape all actual newlines properly
                        finalAttempt = finalAttempt.replace(/\n/g, '\\n');
                        finalAttempt = finalAttempt.replace(/\r/g, '\\r');
                        finalAttempt = finalAttempt.replace(/\t/g, '\\t');
                        
                        try {
                            rewrittenData = JSON.parse(finalAttempt);
                            console.log('JSON parsing succeeded on final attempt with literal \\n conversion');
                        } catch (e3) {
                            throw new Error('Failed to parse AI response as JSON after multiple attempts. Original error: ' + e.message + '. Second: ' + e2.message + '. Third: ' + e3.message + '. Context: ' + context.substring(0, 500));
                        }
                    }
                    }
                }

                // Send rewritten CV to server to save as variant
                const formData = new FormData(document.getElementById('rewrite-form'));
                formData.append('browser_ai_result', JSON.stringify(rewrittenData));

                const saveResponse = await fetch('/api/ai-rewrite-cv.php', {
                    method: 'POST',
                    body: formData
                });

                const saveResult = await saveResponse.json();

                // Cleanup
                await BrowserAIService.cleanup();
                if (loadingOverlay) loadingOverlay.remove();

                if (saveResult.success) {
                    window.location.href = '/cv-variants.php?success=CV generated successfully';
                } else {
                    throw new Error(saveResult.error || 'Failed to save rewritten CV');
                }
            } catch (error) {
                console.error('Browser AI execution error:', error);
                if (loadingOverlay) loadingOverlay.remove();
                
                // If browser AI fails to load, offer fallback to server-side AI
                if (error.message && error.message.includes('Failed to load WebLLM')) {
                    const useServerAI = confirm('Browser AI failed to load (network/CDN issue). Would you like to use server-side AI instead? This requires Ollama or a cloud AI service to be configured.');
                    if (useServerAI) {
                        // Force server-side AI by adding a flag
                        const form = document.getElementById('rewrite-form');
                        const formData = new FormData(form);
                        formData.append('force_server_ai', '1');
                        
                        // Show loading overlay again
                        const newLoadingOverlay = document.createElement('div');
                        newLoadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                        newLoadingOverlay.innerHTML = '<div class="bg-white p-6 rounded-lg"><p>Using server-side AI...</p></div>';
                        document.body.appendChild(newLoadingOverlay);
                        
                        // Submit with server-side AI flag
                        const response = await fetch('/api/ai-rewrite-cv.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        newLoadingOverlay.remove();
                        
                        if (result.success) {
                            window.location.href = '/cv-variants.php?success=CV generated successfully';
                        } else {
                            throw new Error(result.error || 'Failed to generate CV');
                        }
                        return;
                    }
                }
                
                const submitBtn = document.getElementById('submit-btn');
                const submitText = document.getElementById('submit-text');
                const submitLoading = document.getElementById('submit-loading');
                
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.classList.remove('hidden');
                if (submitLoading) submitLoading.classList.add('hidden');
                
                alert('Browser AI Error: ' + error.message);
            }
        }
    </script>
</body>
</html>

