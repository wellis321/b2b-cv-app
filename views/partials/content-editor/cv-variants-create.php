<?php
/**
 * Create CV Variant Form Component
 * Form for creating a new CV variant with AI within content editor
 */

require_once __DIR__ . '/../../../php/cv-variants.php';
require_once __DIR__ . '/../../../php/job-applications.php';
require_once __DIR__ . '/../../../php/cv-data.php';

$userId = getUserId();
$user = getCurrentUser();

// Limited scope for local/browser AI: only one role or one project at a time (no full sections)
$pref = db()->fetchOne("SELECT ai_service_preference FROM profiles WHERE id = ?", [$userId]);
$ai_scope_limited = in_array($pref['ai_service_preference'] ?? '', ['ollama', 'browser']);
$cvDataForScope = $ai_scope_limited ? loadCvData($userId) : null;

// Get user's job applications
$jobApplications = db()->fetchAll(
    "SELECT id, company_name, job_title, job_description, notes, selected_keywords
     FROM job_applications 
     WHERE user_id = ? 
     ORDER BY created_at DESC",
    [$userId]
);

// Get user's CV variants
$variants = getUserCvVariants($userId);

// Suggest a unique variant name
$suggestedVariantName = suggestUniqueVariantName($userId, 'AI-Generated CV');

// Check if user has saved custom instructions
$userProfile = db()->fetchOne(
    "SELECT cv_rewrite_prompt_instructions FROM profiles WHERE id = ?",
    [$userId]
);
$hasSavedInstructions = !empty($userProfile['cv_rewrite_prompt_instructions'] ?? '');

$csrf = csrfToken();
$preselectJobId = isset($_GET['job']) ? $_GET['job'] : null;
?>
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="#cv-variants" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to CV Variants
            </a>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Generate AI CV</h1>
        <p class="mt-1 text-sm text-gray-500">Create a job-specific version of your CV using AI</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form id="rewrite-form" class="space-y-6" <?php echo $ai_scope_limited ? ' data-ai-scope-limited="1"' : ''; ?> data-job-id="<?php echo e($preselectJobId ?? ''); ?>">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo e($csrf); ?>">

            <!-- Source CV Selection -->
            <div>
                <label for="cv_variant_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Source CV
                </label>
                <select id="cv_variant_id" name="cv_variant_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Master CV</option>
                    <?php foreach ($variants as $variant): ?>
                        <?php if (!$variant['is_master']): ?>
                            <option value="<?php echo e($variant['id']); ?>">
                                <?php echo e($variant['variant_name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">Select which CV to use as the base for rewriting</p>
            </div>

            <!-- Job Application Selection -->
            <div>
                <label for="job_application_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Job application (recommended – links this CV to a specific role)
                </label>
                <?php if ($preselectJobId): ?>
                <p class="text-xs text-indigo-600 mb-2">Pre-selected from the job you were viewing.</p>
                <?php endif; ?>
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
                <p class="text-xs text-gray-500 mb-1">Use the toolbar for formatting: bold, italic, headers, lists, and links</p>
                <textarea id="job_description" 
                          name="job_description" 
                          rows="8" 
                          required
                          data-markdown
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Paste the job description here..."></textarea>
                <p class="mt-1 text-sm text-gray-500">The AI will rewrite your CV to match this job description</p>
            </div>

            <!-- Prompt Instructions -->
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
                        <a href="/cv-prompt-settings.php" target="_blank" class="ml-2 text-xs text-blue-600 hover:text-blue-800 underline">(edit)</a>
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
                    <a href="/cv-prompt-settings.php" target="_blank" class="text-blue-600 hover:text-blue-800">Manage saved instructions</a> or 
                    <a href="/resources/ai/prompt-best-practices.php" target="_blank" class="text-blue-600 hover:text-blue-800">learn best practices</a>
                </p>
            </div>

            <!-- Sections to Rewrite (one section at a time for local/browser AI; full sections for cloud API) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Section to tailor
                </label>
                <div class="space-y-2 border border-gray-300 rounded-lg p-4 bg-gray-50">
                    <?php if ($ai_scope_limited): ?>
                        <p class="text-sm text-gray-600 mb-3">With <strong>local or browser AI</strong>, tailor <strong>one item at a time</strong> for best results. Choose one below. After creating the variant, use &quot;Tailor section&quot; to tailor more roles or projects.</p>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="section_single_professional_summary" name="section_single" value="professional_summary" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="section_single_professional_summary" class="ml-2 text-sm text-gray-700">Professional summary</label>
                            </div>
                            <?php if (!empty($cvDataForScope['work_experience'])): ?>
                                <?php foreach ($cvDataForScope['work_experience'] as $we): ?>
                                    <?php $weId = $we['id'] ?? $we['original_work_experience_id'] ?? ''; $weLabel = e(($we['position'] ?? '') . ' at ' . ($we['company_name'] ?? '')); ?>
                                    <div class="flex items-center">
                                        <input type="radio" id="section_single_we_<?php echo e($weId); ?>" name="section_single" value="work_experience:<?php echo e($weId); ?>" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <label for="section_single_we_<?php echo e($weId); ?>" class="ml-2 text-sm text-gray-700">Role: <?php echo $weLabel; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($cvDataForScope['projects'])): ?>
                                <?php foreach ($cvDataForScope['projects'] as $proj): ?>
                                    <?php $projId = $proj['id'] ?? $proj['original_project_id'] ?? ''; $projTitle = e($proj['title'] ?? $proj['name'] ?? 'Project'); ?>
                                    <div class="flex items-center">
                                        <input type="radio" id="section_single_project_<?php echo e($projId); ?>" name="section_single" value="project:<?php echo e($projId); ?>" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <label for="section_single_project_<?php echo e($projId); ?>" class="ml-2 text-sm text-gray-700">Project: <?php echo $projTitle; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div class="flex items-center">
                                <input type="radio" id="section_single_skills" name="section_single" value="skills" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="section_single_skills" class="ml-2 text-sm text-gray-700">Skills</label>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-600 mb-3">Tailor <strong>one section at a time</strong> for best results. After creating the variant, open it and use &quot;Tailor section&quot; to tailor more sections.</p>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_professional_summary" name="sections_to_rewrite[]" value="professional_summary" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_professional_summary" class="ml-2 text-sm text-gray-700">Professional Summary</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_work_experience" name="sections_to_rewrite[]" value="work_experience" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_work_experience" class="ml-2 text-sm text-gray-700">Work Experience</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_skills" name="sections_to_rewrite[]" value="skills" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_skills" class="ml-2 text-sm text-gray-700">Skills</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_education" name="sections_to_rewrite[]" value="education" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_education" class="ml-2 text-sm text-gray-700">Education</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_projects" name="sections_to_rewrite[]" value="projects" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_projects" class="ml-2 text-sm text-gray-700">Projects</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_certifications" name="sections_to_rewrite[]" value="certifications" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_certifications" class="ml-2 text-sm text-gray-700">Certifications</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_professional_memberships" name="sections_to_rewrite[]" value="professional_memberships" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_professional_memberships" class="ml-2 text-sm text-gray-700">Professional Memberships</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="section_interests" name="sections_to_rewrite[]" value="interests" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="section_interests" class="ml-2 text-sm text-gray-700">Interests</label>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <?php if ($ai_scope_limited): ?>
                        With cloud AI (OpenAI, Anthropic, etc.) you can tailor full sections at once. <a href="/ai-settings.php" target="_blank" class="text-blue-600 hover:text-blue-800">AI Settings</a>.
                    <?php else: ?>
                        Choose one section (or more) to tailor. You can tailor other sections later from the variant list.
                    <?php endif; ?>
                    <a href="/cv-prompt-settings.php" target="_blank" class="text-blue-600 hover:text-blue-800">Customise prompt instructions</a> for better results.
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
                            <a href="/ai-settings.php" target="_blank" class="underline font-semibold">Switch to free options (Local Ollama or Browser-Based AI)</a> to avoid charges.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="#cv-variants" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        id="submit-btn"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
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

<script src="/js/model-cache-manager.js"></script>
<script src="/js/browser-ai-service.js"></script>
<script>
(function() {
    // Handle prompt instructions mode selection
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
    
    // Handle job application selection
    const jobApplicationSelect = document.getElementById('job_application_id');
    const jobDescriptionTextarea = document.getElementById('job_description');
    
    if (jobApplicationSelect && jobDescriptionTextarea) {
        jobApplicationSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset.description) {
                jobDescriptionTextarea.value = selectedOption.dataset.description;
            }
        });
        // Pre-select job when opened from job view (#cv-variants&create=1&job=ID)
        const rewriteForm = document.getElementById('rewrite-form');
        if (rewriteForm && rewriteForm.dataset.jobId) {
            const jid = rewriteForm.dataset.jobId;
            if (jid && Array.from(jobApplicationSelect.options).some(function(o) { return o.value === jid; })) {
                jobApplicationSelect.value = jid;
                const opt = jobApplicationSelect.options[jobApplicationSelect.selectedIndex];
                if (opt && opt.dataset.description) {
                    jobDescriptionTextarea.value = opt.dataset.description;
                }
            }
        }
    }

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
                    const costWarning = document.getElementById('cost-warning');
                    if (costWarning) {
                        costWarning.classList.remove('hidden');
                    }
                }
            }
        } catch (error) {
            console.error('Failed to check AI service:', error);
        }
    })();

    // Handle form submission
    const rewriteForm = document.getElementById('rewrite-form');
    if (rewriteForm) {
        rewriteForm.addEventListener('submit', async function(e) {
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
            // When using local/browser AI, section is chosen via a single radio; build sections_to_rewrite and single ids
            if (this.dataset.aiScopeLimited) {
                const radio = this.querySelector('input[name="section_single"]:checked');
                if (!radio) {
                    loadingOverlay.remove();
                    alert('Please choose one section or role to tailor.');
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    submitLoading.classList.add('hidden');
                    return;
                }
                const val = radio.value;
                formData.delete('sections_to_rewrite[]');
                if (val.startsWith('work_experience:')) {
                    formData.append('sections_to_rewrite[]', 'work_experience');
                    formData.set('single_work_experience_id', val.slice('work_experience:'.length));
                } else if (val.startsWith('project:')) {
                    formData.append('sections_to_rewrite[]', 'projects');
                    formData.set('single_project_id', val.slice('project:'.length));
                } else {
                    formData.append('sections_to_rewrite[]', val);
                }
            }
            
            try {
                // Create AbortController for timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minute timeout (Ollama CV rewrite can be slow)
                
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
                    // Redirect to CV variants list with success message
                    window.location.hash = '#cv-variants';
                    // Reload the section to show the new variant
                    if (typeof window.contentEditor !== 'undefined' && typeof window.contentEditor.loadSection === 'function') {
                        setTimeout(() => {
                            window.contentEditor.loadSection('cv-variants');
                        }, 500);
                    }
                } else if (result.error && result.error.indexOf('already exists for this job') !== -1) {
                    // Friendly message when a variant already exists for this job
                    const msg = 'A CV variant already exists for this job. Open it from CV Variants and use "Tailor section…" to tailor more sections.';
                    alert(msg);
                    window.location.hash = '#cv-variants';
                    if (typeof window.contentEditor !== 'undefined' && typeof window.contentEditor.loadSection === 'function') {
                        setTimeout(() => window.contentEditor.loadSection('cv-variants'), 300);
                    }
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
    }

    // Execute browser AI for CV rewriting (simplified version - full implementation would be similar to rewrite.php)
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

            // Parse and clean JSON using robust parsing similar to parseAssessmentJsonFromAI
            function parseCvRewriteJsonFromAI(raw) {
                let text = String(raw || '').trim();
                
                // Strip model special tokens (e.g. <|start_header_id|>assistant<|end_header_id|>) that break JSON
                // Handle various token formats
                text = text.replace(/<\|[^]*?\|>/g, ''); // Standard format: <|token|>
                text = text.replace(/<\|[^]*?\|>/g, ''); // Repeat to catch nested tokens
                text = text.replace(/\[INST\][\s\S]*?\[\/INST\]/gi, ''); // Llama instruction tokens
                text = text.replace(/<s>[\s\S]*?<\/s>/gi, ''); // Sentence tokens
                text = text.replace(/<\|im_start\|>[\s\S]*?<\|im_end\|>/gi, ''); // ChatML tokens
                
                // Strip malformed tokens that appear mid-JSON (like "any_name":assistant"")
                // Pattern: "key":token"" or "key":token" or :token"
                text = text.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id)""/gi, '": ""');
                text = text.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id)"/gi, '": ""');
                text = text.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id)""/gi, ': ""');
                text = text.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id)"/gi, ': ""');
                
                // Strip markdown code fences (```json ... ``` or ``` ... ```)
                const codeBlock = text.match(/```(?:json)?\s*([\s\S]*?)```/);
                if (codeBlock) {
                    text = codeBlock[1].trim();
                }
                
                // Find the first occurrence of '{' or '[' (some models return root array)
                const startObj = text.indexOf('{');
                const startArr = text.indexOf('[');
                let start = startObj;
                if (startObj < 0 && startArr >= 0) {
                    start = startArr;
                } else if (startObj >= 0 && startArr >= 0 && startArr < startObj) {
                    start = startArr;
                } else if (startObj < 0) {
                    throw new Error('No JSON object or array found in AI response. Expected JSON starting with { or [');
                }
                
                // Extract from the first '{' or '[' onwards
                text = text.slice(start);
                
                let jsonStr;
                if (text.startsWith('[')) {
                    // Root-level array: extract balanced [...] and parse; if single object, use it
                    let arrDepth = 0;
                    let arrEnd = -1;
                    for (let i = 0; i < text.length; i++) {
                        const ch = text[i];
                        if (ch === '[') arrDepth++;
                        else if (ch === ']') { arrDepth--; if (arrDepth === 0) { arrEnd = i; break; } }
                    }
                    if (arrEnd < 0) {
                        const lastBracket = text.lastIndexOf(']');
                        if (lastBracket > 0) arrEnd = lastBracket;
                    }
                    if (arrEnd >= 0) {
                        const arrayStr = text.slice(0, arrEnd + 1).replace(/,(\s*[}\]])/g, '$1');
                        try {
                            const arr = JSON.parse(arrayStr);
                            if (Array.isArray(arr) && arr.length === 1 && arr[0] && typeof arr[0] === 'object') {
                                return arr[0];
                            }
                        } catch (arrErr) { /* fall through to object path */ }
                    }
                    const firstBrace = text.indexOf('{');
                    if (firstBrace >= 0) text = text.slice(firstBrace);
                }
                
                // Extract first balanced {...} block (avoids grabbing extra text after })
                if (typeof jsonStr === 'undefined') jsonStr = text;
                let depth = 0;
                let end = -1;
                for (let i = 0; i < text.length; i++) {
                    const ch = text[i];
                    if (ch === '{') depth++;
                    else if (ch === '}') { 
                        depth--; 
                        if (depth === 0) { 
                            end = i; 
                            break; 
                        } 
                    }
                }
                
                if (end >= 0) {
                    jsonStr = text.slice(0, end + 1);
                } else if (typeof jsonStr === 'undefined' || jsonStr === text) {
                    // If we can't find a balanced closing brace, try to find the last '}'
                    const lastBrace = text.lastIndexOf('}');
                    if (lastBrace > 0) {
                        jsonStr = text.slice(0, lastBrace + 1);
                    } else {
                        throw new Error('No complete JSON object found. Missing closing brace.');
                    }
                }
                
                // Remove whole-line comments (JSON does not allow // comments; browser AI sometimes emits instruction lines)
                jsonStr = jsonStr.replace(/^\s*\/\/[^\n]*$/gm, '');
                // Collapse multiple newlines that may result from removed lines
                jsonStr = jsonStr.replace(/\n\s*\n\s*\n/g, '\n\n');
                // Remove empty objects left after comment removal (e.g. { // comment } -> { }); avoids "Expected property name or '}'"
                jsonStr = jsonStr.replace(/,(\s*\{\s*\})\s*/g, '');
                jsonStr = jsonStr.replace(/(\[\s*)(\{\s*\})(\s*,\s*)/g, '$1$3'); // empty object at start of array
                jsonStr = jsonStr.replace(/,(\s*\{\s*\})(\s*\])/g, '$2'); // empty object before ]
                
                // Remove trailing commas before ] or } (invalid in strict JSON, common in LLM output)
                jsonStr = jsonStr.replace(/,(\s*[}\]])/g, '$1');
                
                // Strip trailing ellipsis or truncation markers that can appear after the final }
                jsonStr = jsonStr.replace(/\s*\.{2,}\s*$/, '').replace(/\s*…\s*$/, '');
                
                // Remove standalone ... (ellipsis) in the middle of JSON - causes "Unexpected token '.'"
                jsonStr = jsonStr.replace(/,(\s*\.\.\.\s*)(\])/g, '$2');
                jsonStr = jsonStr.replace(/,(\s*\.\.\.\s*)(,)/g, '$2');
                jsonStr = jsonStr.replace(/(\[\s*)(\.\.\.\s*)(\])/g, '$1$3');
                jsonStr = jsonStr.replace(/(\[\s*)(\.\.\.\s*)(,)/g, '$1$3');
                
                // Strip literal placeholders that browser AI sometimes copies into JSON (invalid)
                jsonStr = jsonStr.replace(/\s*\.\.\.\s*\(\d+\s+more\s+positions\)\s*/gi, '');
                jsonStr = jsonStr.replace(/\s*\.\.\.\s*\(\d+\s+more\s+items\)\s*/gi, '');
                // Remove trailing comma that may be left after stripping placeholder (e.g. }, ... (8 more positions) ] -> }, ])
                jsonStr = jsonStr.replace(/,(\s*[}\]])/g, '$1');
                
                // CRITICAL: Fix the two most common browser-AI malformations FIRST, before any other processing.
                // 1) Empty key "": ": " or ": ": " -> "description": " (causes "double-quoted property name" at line 17)
                jsonStr = jsonStr.replace(/\{\s*"\s*:\s*"\s*:\s*"/g, '{ "description": "');
                jsonStr = jsonStr.replace(/,\s*"\s*:\s*"\s*:\s*"/g, ', "description": "');
                // 1b) Malformed id: "id": ": " (model outputs id value as ": ") -> "id": ""
                jsonStr = jsonStr.replace(/"id"\s*:\s*"\s*:\s*"/g, '"id": ""');
                // 2) Missing comma between array elements: } { -> }, { (causes "Expected ',' or ']' after array element")
                let prev = '';
                while (prev !== jsonStr) {
                    prev = jsonStr;
                    jsonStr = jsonStr.replace(/\}\s*\{/g, '}, {');
                }
                // 2b) Missing comma after ] } (end of inner array + object): ] } { -> ] }, {
                prev = '';
                while (prev !== jsonStr) {
                    prev = jsonStr;
                    jsonStr = jsonStr.replace(/\]\s*\}\s*\{/g, '] }, {');
                }
                // 2c) Missing comma before next property: } "description": or } "name": -> }, "description": / }, "name":
                jsonStr = jsonStr.replace(/\}\s*"(description|name)"\s*:/g, '}, "$1":');
                // 2d) Normalise responsibility category: AI sometimes uses "description" for category name; we need "name"
                jsonStr = jsonStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*,\s*"items"\s*:/g, '{ "name": "$1", "items":');
                // 2e) Normalise responsibility item: AI sometimes uses "description" for item text; we need "content"
                // Only match single-key object { "description": "..." } (not category which has "description", "items")
                jsonStr = jsonStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*,\s*/g, '{ "content": "$1" }, ');
                jsonStr = jsonStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*\]/g, '{ "content": "$1" } ]');
                
                // Strip any remaining model tokens inside the JSON (they can appear anywhere)
                jsonStr = jsonStr.replace(/<\|[^]*?\|>/g, ''); // Standard format
                jsonStr = jsonStr.replace(/\[INST\][\s\S]*?\[\/INST\]/gi, ''); // Llama tokens
                jsonStr = jsonStr.replace(/<s>[\s\S]*?<\/s>/gi, ''); // Sentence tokens
                jsonStr = jsonStr.replace(/<\|im_start\|>[\s\S]*?<\|im_end\|>/gi, ''); // ChatML tokens
                
                // Strip malformed tokens that appear mid-JSON structure
                // Fix patterns like "key":assistant"" or :assistant"
                jsonStr = jsonStr.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, '": ""');
                jsonStr = jsonStr.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, '": ""');
                jsonStr = jsonStr.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, ': ""');
                jsonStr = jsonStr.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                
                // Remove standalone token words that break JSON (like "assistant" appearing as a value)
                jsonStr = jsonStr.replace(/:\s*"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                
                // Fix cases where tokens appear inside string values and break them
                // Pattern: text"assistant\n\n"moretext" -> text moretext"
                // This happens when tokens are inserted mid-string
                // Match: text inside quotes, then quote, token, whitespace/newlines, quote, more text
                jsonStr = jsonStr.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*"([^"]*?)"/g, function(match, beforeText, token, whitespace, afterText) {
                    // Remove the token and extra quotes, merge the text
                    return '"' + beforeText.trim() + ' ' + afterText.trim() + '"';
                });
                
                // Fix cases where tokens break strings without proper closing
                // Pattern: "text"assistant\n\n"moretext -> "text moretext"
                jsonStr = jsonStr.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*"([^"]*)/g, function(match, beforeText, token, whitespace, afterText) {
                    // Remove the token and extra quotes/newlines, merge the text
                    return '"' + beforeText.trim() + ' ' + afterText.trim();
                });
                
                // Fix cases where tokens appear mid-string value (inside quotes)
                // Pattern: "text assistant\n\n"moretext" -> "text moretext"
                // This is trickier - we need to detect when a quote appears after a token inside what should be one string
                jsonStr = jsonStr.replace(/"([^"]*?)(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*"([^"]*?)"/g, function(match, beforeText, token, whitespace, afterText) {
                    // Remove the token and merge the text
                    return '"' + beforeText.trim() + ' ' + afterText.trim() + '"';
                });
                
                // Fix cases where tokens appear between string values breaking JSON
                // Pattern: "text"(assistant|token)\n\n"moretext" -> "text", "moretext"
                // This fixes the specific error pattern we're seeing
                jsonStr = jsonStr.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*"([^"]*?)"/g, function(match, text1, token, whitespace, text2) {
                    // Close first string, add comma, start second string
                    return '"' + text1.trim() + '", "' + text2.trim() + '"';
                });
                
                // Also handle cases where the second part doesn't have proper quotes
                // Pattern: "text"(assistant|token)\n\nmoretext" -> "text", "moretext"
                jsonStr = jsonStr.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*([^"]*?)"/g, function(match, text1, token, whitespace, text2) {
                    // Close first string, add comma, start second string
                    return '"' + text1.trim() + '", "' + text2.trim() + '"';
                });
                
                // Fix tokens appearing after closing brackets/braces
                // Pattern: ]assistant", "prop": -> ], "prop":
                jsonStr = jsonStr.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                jsonStr = jsonStr.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                // Pattern: ]assistant"prop": -> ], "prop":
                jsonStr = jsonStr.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                jsonStr = jsonStr.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                // Pattern: { assistant", "prop": -> { "prop": (token after opening brace - causes "Expected double-quoted property name")
                jsonStr = jsonStr.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1 "$3":');
                jsonStr = jsonStr.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1 "$3":');
                
                // Strip common AI explanatory phrases that break JSON structure
                // These often appear mid-JSON when the AI tries to explain what it's doing
                // Pattern: text inside quotes, then explanatory phrase, then opening brace
                // Example: "...digitalassistant  Here is the rest of the rewritten CV sections:  {"
                // We need to close the string before the explanatory text and remove it
                
                // Find patterns where explanatory text appears after unclosed string content
                // Look for: "text (explanatory phrase) {"
                jsonStr = jsonStr.replace(/"([^"]*?)\s*(Here is the rest|Here is the|The following|Now,? here|Continuing|Below|Following|I will provide|This is|These are)[^"]*?\s*\{/gi, function(match, stringContent, phraseStart) {
                    // Close the string properly before the explanatory text
                    // Remove everything from the explanatory phrase onwards, close the string, then add comma
                    return '"' + stringContent.trim() + '", ';
                });
                
                // Also handle cases where explanatory text appears without quotes before it
                // Pattern: word characters, then explanatory phrase, then {
                jsonStr = jsonStr.replace(/(\w+)\s+(Here is the rest|Here is the|The following|Now,? here|Continuing|Below|Following|I will provide|This is|These are)[^"]*?\s*\{/gi, function(match, lastWord, phraseStart) {
                    // This might be breaking out of a string - try to repair by closing any open string
                    // For now, just remove the explanatory text
                    return lastWord + ' ';
                });
                
                // Repair common LLM JSON issues
                // 1. Fix truncated property names with colons in wrong place
                // Map common truncated names to full names
                const propertyNameMap = {
                    'comp': 'company_name',
                    'pos': 'position',
                    'desc': 'description',
                    'id': 'id',
                    'name': 'name',
                    'company': 'company_name',
                    'position': 'position',
                    'description': 'description'
                };
                
                // Fix: "comp :"The Value" -> "company_name": "The Value"
                for (const [short, full] of Object.entries(propertyNameMap)) {
                    jsonStr = jsonStr.replace(new RegExp('"' + short + '\\s*:\\s*"([A-Z][^"]{1,200})"', 'gi'), function(match, value) {
                        return '"' + full + '": "' + value + '"';
                    });
                }
                
                // Fix property names with colons inside quotes: "prop :" -> "prop":
                jsonStr = jsonStr.replace(/"([a-z_]+)\s*:\s*"/gi, '"$1": "');
                
                // Fix empty property names: ": " -> remove or fix
                // Pattern: "  }, ": ": [ -> "  }, "work_experience": [
                // Handle cases where empty property appears after closing brace (with newlines/whitespace)
                // Apply multiple times to catch all instances
                for (let i = 0; i < 10; i++) {
                    const beforeEmptyFix = jsonStr;
                    // Top-level empty property (after closing brace): "work_experience"
                    jsonStr = jsonStr.replace(/}\s*,\s*[\s\n]*":\s*":\s*\[/g, '}, "work_experience": [');
                    jsonStr = jsonStr.replace(/}\s*[\s\n]*":\s*":\s*\[/g, '}, "work_experience": [');
                    jsonStr = jsonStr.replace(/[\s\n]*":\s*":\s*\[/g, '"work_experience": [');
                    // Also handle without quotes around the colons: : : [ -> work_experience: [
                    jsonStr = jsonStr.replace(/}\s*,\s*[\s\n]*:\s*:\s*\[/g, '}, "work_experience": [');
                    jsonStr = jsonStr.replace(/}\s*[\s\n]*:\s*:\s*\[/g, '}, "work_experience": [');
                    
                    // NEW: Handle empty property inside objects (after a property value): should be "items"
                    // Pattern: "name": "value", ": ": [ -> "name": "value", "items": [
                    // Handle with flexible whitespace including newlines
                    jsonStr = jsonStr.replace(/,\s*":\s*":\s*\[/g, ', "items": [');
                    jsonStr = jsonStr.replace(/,\s*[\s\n]*":\s*":\s*\[/g, ', "items": [');
                    // Also handle at the start of an object: { ": ": [ -> { "items": [
                    jsonStr = jsonStr.replace(/\{\s*":\s*":\s*\[/g, '{ "items": [');
                    jsonStr = jsonStr.replace(/\{\s*[\s\n]*":\s*":\s*\[/g, '{ "items": [');
                    
                    // Generic fallback: any ": ": [ that appears after a quoted value (inside an object)
                    // Match with flexible whitespace
                    jsonStr = jsonStr.replace(/"([^"]+)",\s*[\s\n]*":\s*":\s*\[/g, '"$1", "items": [');
                    jsonStr = jsonStr.replace(/"([^"]+)"\s*[\s\n]*":\s*":\s*\[/g, '"$1", "items": [');
                    // Most aggressive: catch ANY ": ": [ that appears after any character except }
                    // This catches remaining instances inside objects
                    jsonStr = jsonStr.replace(/([^}\s])\s*[\s\n]*":\s*":\s*\[/g, '$1, "items": [');
                    
                    if (jsonStr === beforeEmptyFix) break; // No more changes
                }
                
                // Missing comma between category objects: ] } { -> ] }, {
                prev = '';
                while (prev !== jsonStr) {
                    prev = jsonStr;
                    jsonStr = jsonStr.replace(/\]\s*\}\s*\{/g, '] }, {');
                }
                // Missing comma before next key: } "description": or } "name": -> }, "$1":
                jsonStr = jsonStr.replace(/\}\s*"(description|name)"\s*:/g, '}, "$1":');
                // Normalise category: { "description": "Category Title", "items": -> { "name": "Category Title", "items":
                jsonStr = jsonStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*,\s*"items"\s*:/g, '{ "name": "$1", "items":');
                // Normalise item: { "description": "..." } -> { "content": "..." } (single-key object only)
                jsonStr = jsonStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*,\s*/g, '{ "content": "$1" }, ');
                jsonStr = jsonStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*\]/g, '{ "content": "$1" } ]');
                
                // CRITICAL: Fix missing commas RIGHT AFTER empty property name fix
                // Pattern: "prop": "value": "prop2": -> "prop": "value", "prop2":
                // This must happen early to catch issues before other cleaning interferes
                // Use a more direct approach: find ": "prop": where prop is a property name and insert comma before it
                // #region agent log
                const beforeEarlyFix = jsonStr;
                // #endregion
                
                // CRITICAL FIX: Match the exact error pattern and fix it directly
                // Error pattern: "position": "Systems Development Manager": "company_name":
                // Fix: "position": "Systems Development Manager", "company_name":
                const commonProps = ['id', 'position', 'company_name', 'description', 'name', 'title', 'start_date', 'end_date', 'location', 'degree', 'field_of_study', 'institution', 'items', 'content'];
                // #region agent log
                // Check if the problematic pattern exists in the JSON
                const hasProblemPattern = jsonStr.includes('"position": "Systems Development Manager": "company_name":');
                const jsonSample = jsonStr.substring(0, 2000);
                // #endregion
                // FIRST: Try simple string replacement for the most common case
                // This is more reliable than regex for the exact error pattern
                // Apply multiple times to catch all instances
                const beforeSimpleFix = jsonStr;
                // #region agent log
                const testPatternMatch = jsonStr.match(/"position":\s*"Systems Development Manager":\s*"company_name":/gi);
                // #endregion
                // Use a more aggressive approach: find ANY ": "prop": pattern and fix it
                // This catches the error pattern regardless of whitespace
                for (let i = 0; i < 20; i++) {
                    const beforeIteration = jsonStr;
                    // Pattern 1: Exact match - "position": "Systems Development Manager": "company_name":
                    // Handle any whitespace including newlines between the parts
                    jsonStr = jsonStr.replace(/"position"\s*:\s*"Systems Development Manager"\s*:\s*"company_name"\s*:/gi, '"position": "Systems Development Manager", "company_name":');
                    // Pattern 2: More flexible - match with any characters (including newlines) between colons
                    jsonStr = jsonStr.replace(/"position"[\s\S]{0,200}:\s*"Systems Development Manager"[\s\S]{0,200}:\s*"company_name"[\s\S]{0,200}:/gi, '"position": "Systems Development Manager", "company_name":');
                    // Pattern 3: Generic - any quoted value followed by : "prop": where prop is a known property
                    // Use [\s\S] to match any character including newlines
                    for (const prop of commonProps) {
                        jsonStr = jsonStr.replace(new RegExp('"([^"]{5,500})"\\s*:\\s*"' + prop + '"\\s*:', 'gi'), function(match, value) {
                            // #region agent log
                            if (prop === 'company_name' && (value.includes('Systems Development Manager') || value.includes('Development Manager'))) {
                            }
                            // #endregion
                            // More permissive: any value that's not a property name
                            if (value.length >= 5 && value.length <= 500 && !commonProps.includes(value.toLowerCase())) {
                                return '"' + value + '", "' + prop + '":';
                            }
                            return match;
                        });
                    }
                    if (jsonStr === beforeIteration) break; // No more changes
                }
                // #region agent log
                if (jsonStr !== beforeSimpleFix) {
                } else {
                }
                // #endregion
                
                let changed = true;
                let iterations = 0;
                while (changed && iterations < 50) {
                    const before = jsonStr;
                    // #region agent log
                    if (iterations === 0) {
                    }
                    // #endregion
                    // SIMPLEST PATTERN: Find ": "prop": where prop is a known property, and insert comma before it
                    // This catches: "value": "prop": -> "value", "prop":
                    for (const prop of commonProps) {
                        // Match: any quoted string ending with ", then ": "prop":
                        const pattern = new RegExp('"([^"]+)":\\s*"' + prop + '":', 'gi');
                        jsonStr = jsonStr.replace(pattern, function(match, value) {
                            // #region agent log
                            if (prop === 'company_name' && value.includes('Systems Development Manager')) {
                            }
                            // #endregion
                            // Only fix if value is long enough (not a property name) and prop is a known property
                            if (value.length >= 5 && value.length <= 500 && !commonProps.includes(value.toLowerCase())) {
                                return '"' + value + '", "' + prop + '":';
                            }
                            return match;
                        });
                    }
                    // Also try the full sequence pattern: "prop1": "value": "prop2":
                    for (const prop1 of commonProps) {
                        for (const prop2 of commonProps) {
                            if (prop1 !== prop2) {
                                const pattern = new RegExp('"' + prop1 + '":\\s*"([^"]+)":\\s*"' + prop2 + '":', 'gi');
                                jsonStr = jsonStr.replace(pattern, function(match, value) {
                                    // #region agent log
                                    if (prop1 === 'position' && prop2 === 'company_name') {
                                    }
                                    // #endregion
                                    if (value.length >= 5 && value.length <= 500) {
                                        return '"' + prop1 + '": "' + value + '", "' + prop2 + '":';
                                    }
                                    return match;
                                });
                            }
                        }
                    }
                    changed = (jsonStr !== before);
                    iterations++;
                }
                // #region agent log
                if (jsonStr !== beforeEarlyFix) {
                }
                // #endregion
                // #region agent log
                if (jsonStr !== beforeEarlyFix) {
                }
                // #endregion
                
                // Only remove ": ": { if it's not part of a nested structure we want to keep
                jsonStr = jsonStr.replace(/,\s*":\s*":\s*\{/g, ', ');
                jsonStr = jsonStr.replace(/\{\s*":\s*":\s*\{/g, '{');
                
                // Fix empty property names in specific contexts
                // Pattern: "professional_summary": { ": ": "value" or " : ": "value" -> "professional_summary": { "description": "value"
                // Handle with newlines and whitespace, and optional spaces around colons in empty key
                jsonStr = jsonStr.replace(/"professional_summary"\s*:\s*\{[\s\n]*"\s*:\s*"\s*:\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"professional_summary": { "description": "$1"');
                jsonStr = jsonStr.replace(/"work_experience"\s*:\s*\[[\s\n]*\{[\s\n]*"\s*:\s*"\s*:\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"work_experience": [{ "description": "$1"');
                
                // Fix other empty property names: ": ": "value" or " : ": "value" -> "description": "value" (when inside an object)
                jsonStr = jsonStr.replace(/\{[\s\n]*"\s*:\s*"\s*:\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '{ "description": "$1"');
                
                // Fix multiple consecutive colons: ": "prop": -> "prop":
                jsonStr = jsonStr.replace(/":\s*"([a-z_]+)":/gi, '"$1":');
                jsonStr = jsonStr.replace(/:\s*"([a-z_]+)":/gi, '"$1":');
                
                // Fix missing comma between array elements: } { -> }, { (apply until no change; browser AI often omits many)
                prev = '';
                while (prev !== jsonStr) {
                    prev = jsonStr;
                    jsonStr = jsonStr.replace(/\}\s*\{/g, '}, {');
                }
                
                // Fix missing commas between properties: "value": "next_prop": -> "value", "next_prop":
                // Be careful - only fix if it looks like a VALUE (long text) followed by a property name
                // Property names are usually short (id, position, company_name, etc.)
                // Values are usually longer descriptive text
                jsonStr = jsonStr.replace(/"([^"]{20,500})":\s*"([a-z_]{1,30})":/gi, function(match, value, prop) {
                    // Only fix if:
                    // 1. value is long enough to be a real value (not a property name) - at least 20 chars
                    // 2. prop looks like a property name (short, lowercase with underscores)
                    // 3. value doesn't look like a property name itself
                    if (prop.length >= 1 && prop.length <= 30 && /^[a-z_]+$/i.test(prop)) {
                        // Check if value looks like a real value (not a property name)
                        // Property names are usually short and specific, values are descriptive text
                        if (value.length >= 20 && value.length <= 500 && !/^(id|position|company_name|description|name|title|degree|field_of_study|institution)$/i.test(value)) {
                            return '"' + value + '", "' + prop + '":';
                        }
                    }
                    return match;
                });
                
                // Fix: "value": "prop": "value2" -> "value", "prop": "value2"
                jsonStr = jsonStr.replace(/"([^"]{1,500})":\s*"([a-z_]{1,30})":\s*"([^"]+)"/gi, function(match, value1, prop, value2) {
                    if (prop.length >= 1 && prop.length <= 30 && /^[a-z_]+$/i.test(prop)) {
                        return '"' + value1 + '", "' + prop + '": "' + value2 + '"';
                    }
                    return match;
                });
                
                // Fix: "value">>>: "prop": -> "value", "prop":
                jsonStr = jsonStr.replace(/"([^"]{1,500})">>>:\s*"([a-z_]{1,30})":/gi, function(match, value, prop) {
                    if (prop.length >= 1 && prop.length <= 30 && /^[a-z_]+$/i.test(prop)) {
                        return '"' + value + '", "' + prop + '":';
                    }
                    return match;
                });
                
                // Fix: "value": "prop": -> "value", "prop": (when prop is followed by colon and value)
                jsonStr = jsonStr.replace(/"([^"]+)":\s*"([a-z_]+)":\s*"([^"]+)"/gi, '"$1", "$2": "$3"');
                
                // Fix missing commas: "value": "prop": -> "value", "prop":
                // Apply aggressively with while loops to catch all consecutive issues
                // Pattern 1: "prop1": "value": "prop2": -> "prop1": "value", "prop2":
                changed = true;
                iterations = 0;
                while (changed && iterations < 20) {
                    const before = jsonStr;
                    jsonStr = jsonStr.replace(/"([a-z_]{1,30})":\s*"([^"]{5,500})":\s*"([a-z_]{1,30})":/gi, function(match, prop1, value, prop2) {
                        if (/^[a-z_]+$/i.test(prop1) && /^[a-z_]+$/i.test(prop2) && value.length >= 5) {
                            return '"' + prop1 + '": "' + value + '", "' + prop2 + '":';
                        }
                        return match;
                    });
                    changed = (jsonStr !== before);
                    iterations++;
                }
                
                // Pattern 2: "value": "prop": (when prop is a property name, value is long)
                changed = true;
                iterations = 0;
                while (changed && iterations < 20) {
                    const before = jsonStr;
                    jsonStr = jsonStr.replace(/"([^"]{10,500})":\s*"([a-z_]{1,30})":/gi, function(match, value, prop) {
                        if (/^[a-z_]+$/i.test(prop) && value.length >= 10 && !/^(id|position|company_name|description|name|title|degree|field_of_study|institution)$/i.test(value)) {
                            return '"' + value + '", "' + prop + '":';
                        }
                        return match;
                    });
                    changed = (jsonStr !== before);
                    iterations++;
                }
                
                // Pattern 3: More general - any quoted string followed by ": "prop": where prop looks like a property name
                changed = true;
                iterations = 0;
                while (changed && iterations < 10) {
                    const before = jsonStr;
                    jsonStr = jsonStr.replace(/"([^"]{3,500})":\s*"([a-z_]{2,30})":/gi, function(match, value, prop) {
                        // Only fix if prop looks like a property name (lowercase with underscores, common CV property names)
                        if (/^[a-z_]+$/i.test(prop) && 
                            (prop.length >= 2 && prop.length <= 30) &&
                            /^(id|position|company_name|description|name|title|degree|field_of_study|institution|start_date|end_date|location|url|skills|responsibilities|key_responsibilities|achievements|summary|content|items)$/i.test(prop)) {
                            // Make sure value doesn't look like a property name itself
                            if (!/^(id|position|company_name|description|name|title|degree|field_of_study|institution)$/i.test(value)) {
                                return '"' + value + '", "' + prop + '":';
                            }
                        }
                        return match;
                    });
                    changed = (jsonStr !== before);
                    iterations++;
                }
                
                // 2. Fix missing colons: "key" "value" -> "key": "value"
                jsonStr = jsonStr.replace(/"\s*"\s*"/g, '": "');
                // 3. Fix missing colons: "key" [ -> "key": [
                jsonStr = jsonStr.replace(/"\s*(\[)/g, '": $1');
                // 4. Fix missing colons: "key" { -> "key": {
                jsonStr = jsonStr.replace(/"\s*(\{)/g, '": $1');
                
                // 5. Fix text inserted between property name and value
                // Pattern: "prop" text "value" -> "prop": "value"
                // Also handle: "prop :">>>text"value" -> "prop": "value"
                jsonStr = jsonStr.replace(/"([^"]+)"\s*([^":\s]+)\s*"([^"]+)"/g, function(match, prop, insertedText, value) {
                    // Only fix if it looks like a property name followed by inserted text and a value
                    if (prop.length < 50 && value.length < 200 && !insertedText.includes('{') && !insertedText.includes('[')) {
                        return '"' + prop + '": "' + value + '"';
                    }
                    return match;
                });
                
                // Fix specific pattern: "prop :">>>text"value" -> "prop": "value"
                jsonStr = jsonStr.replace(/"([^"]+)\s*:\s*">>>[^"]*"([^"]+)"/g, '"$1": "$2"');
                jsonStr = jsonStr.replace(/"([^"]+)\s*:\s*">[^"]*"([^"]+)"/g, '"$1": "$2"');
                
                // Fix property names with colons followed by inserted text and values
                // Pattern: "prop :"text"value" -> "prop": "value"
                jsonStr = jsonStr.replace(/"([a-z_]+)\s*:\s*"[^"]*"([^"]+)"/gi, '"$1": "$2"');
                
                // Fix: "prop :">>>text"value" or "prop :"text"value" -> "prop": "value"
                // Handle cases where text is inserted between property and value
                jsonStr = jsonStr.replace(/"([a-z_]+)\s*:\s*">>>[^"]*"([^"]+)"/gi, '"$1": "$2"');
                jsonStr = jsonStr.replace(/"([a-z_]+)\s*:\s*">[^"]*"([^"]+)"/gi, '"$1": "$2"');
                
                // Fix property with colon in quotes followed by text that should be the value
                // Pattern: "prop :"The Text Value" -> "prop": "The Text Value"
                jsonStr = jsonStr.replace(/"([a-z_]+)\s*:\s*"([A-Z][^"]{1,200})"/gi, function(match, prop, valueText) {
                    // This looks like a property name with colon inside quotes, followed by the actual value
                    return '"' + prop + '": "' + valueText + '"';
                });
                
                // Fix: "prop :"text"more" -> "prop": "textmore" (merge if both look like values)
                jsonStr = jsonStr.replace(/"([a-z_]+)\s*:\s*"([A-Z][^"]*)"([^"]+)"/gi, function(match, prop, text1, text2) {
                    // If both parts look like they could be values, merge them
                    if (text1.length < 100 && text2.length < 200 && !text1.includes('{') && !text2.includes('{')) {
                        return '"' + prop + '": "' + text1 + ' ' + text2 + '"';
                    }
                    // Otherwise, use the second part as the value
                    return '"' + prop + '": "' + text2 + '"';
                });
                
                // 6. Trailing commas before ] or } (handle nested cases)
                jsonStr = jsonStr.replace(/,(\s*[}\]])/g, '$1');
                // 5. Escape control characters in string values (newlines, tabs, etc.)
                // Process strings carefully to avoid breaking escaped sequences
                jsonStr = jsonStr.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"/g, function (m) {
                    // Process the string content (everything between the quotes)
                    const stringContent = m.slice(1, -1); // Remove surrounding quotes
                    let result = '"';
                    let i = 0;
                    while (i < stringContent.length) {
                        const ch = stringContent[i];
                        if (ch === '\\' && i + 1 < stringContent.length) {
                            // Preserve escape sequences
                            result += ch + stringContent[i + 1];
                            i += 2;
                        } else if (ch === '\n') {
                            // Escape newlines
                            result += '\\n';
                            i++;
                        } else if (ch === '\r') {
                            // Escape carriage returns
                            result += '\\r';
                            i++;
                        } else if (ch === '\t') {
                            // Escape tabs
                            result += '\\t';
                            i++;
                        } else if (ch.charCodeAt(0) < 32) {
                            // Escape other control characters (ASCII < 32)
                            result += ' ';
                            i++;
                        } else {
                            result += ch;
                            i++;
                        }
                    }
                    result += '"';
                    return result;
                });
                
                // #region agent log
                // Log problematic patterns before parsing
                const emptyPropMatch = jsonStr.match(/":\s*":\s*\[/);
                const missingCommaMatch = jsonStr.match(/"([a-z_]+)":\s*"([^"]{10,})":\s*"([a-z_]+)":/i);
                if (emptyPropMatch || missingCommaMatch) {
                }
                // #endregion
                
                // FINAL FIX: Right before parsing, re-apply the two critical fixes (empty key + } {) in a loop.
                // Other cleaning can sometimes leave these malformations; this ensures they are fixed.
                for (let i = 0; i < 10; i++) {
                    const beforeFinalFix = jsonStr;
                    jsonStr = jsonStr.replace(/\{\s*"\s*:\s*"\s*:\s*"/g, '{ "description": "');
                    jsonStr = jsonStr.replace(/,\s*"\s*:\s*"\s*:\s*"/g, ', "description": "');
                    prev = '';
                    while (prev !== jsonStr) {
                        prev = jsonStr;
                        jsonStr = jsonStr.replace(/\}\s*\{/g, '}, {');
                    }
                    if (jsonStr === beforeFinalFix) break;
                }
                
                // Additional last-resort fixes
                const commonPropsFinal = ['id', 'position', 'company_name', 'description', 'name', 'title', 'start_date', 'end_date', 'location', 'degree', 'field_of_study', 'institution', 'items', 'content'];
                for (let i = 0; i < 20; i++) {
                    const beforeFinalFix = jsonStr;
                    // CRITICAL: Fix empty property names ": ": [ -> "items": [
                    // This must be very aggressive to catch all instances
                    // First, handle top-level (after }): "work_experience"
                    jsonStr = jsonStr.replace(/}\s*,\s*[\s\n]*":\s*":\s*\[/g, '}, "work_experience": [');
                    jsonStr = jsonStr.replace(/}\s*[\s\n]*":\s*":\s*\[/g, '}, "work_experience": [');
                    // Then handle inside objects: "items"
                    jsonStr = jsonStr.replace(/,\s*[\s\n]*":\s*":\s*\[/g, ', "items": [');
                    jsonStr = jsonStr.replace(/\{\s*[\s\n]*":\s*":\s*\[/g, '{ "items": [');
                    jsonStr = jsonStr.replace(/"([^"]+)",\s*[\s\n]*":\s*":\s*\[/g, '"$1", "items": [');
                    jsonStr = jsonStr.replace(/"([^"]+)"\s*[\s\n]*":\s*":\s*\[/g, '"$1", "items": [');
                    // Most aggressive: replace ALL remaining ": ": [ patterns with "items": [
                    // This catches any instances that weren't caught by the more specific patterns above
                    // Match with very flexible whitespace
                    jsonStr = jsonStr.replace(/":\s*":\s*\[/g, '"items": [');
                    jsonStr = jsonStr.replace(/":[\s\n]+":[\s\n]+\[/g, '"items": [');
                    
                    // Match: "value": "prop": where prop is a known property
                    // Use a very permissive pattern that matches any quoted value followed by : "prop":
                    for (const prop of commonPropsFinal) {
                        // Match any sequence: quoted text, colon, quoted prop, colon
                        jsonStr = jsonStr.replace(new RegExp('"([^"]{5,500})"\\s*:\\s*"' + prop + '"\\s*:', 'gi'), function(match, value) {
                            // Only fix if value doesn't look like a property name
                            if (!commonPropsFinal.includes(value.toLowerCase()) && value.length >= 5 && value.length <= 500) {
                                return '"' + value + '", "' + prop + '":';
                            }
                            return match;
                        });
                    }
                    // Also try the exact error pattern with any whitespace
                    jsonStr = jsonStr.replace(/"position"\s*:\s*"Systems Development Manager"\s*:\s*"company_name"\s*:/gi, '"position": "Systems Development Manager", "company_name":');
                    if (jsonStr === beforeFinalFix) break; // No more changes
                }
                
                try {
                    return JSON.parse(jsonStr);
                } catch (e) {
                    // Error-specific retry: for "Expected ',' or ']' after array element" or "double-quoted property name",
                    // re-apply the two critical fixes aggressively and try once more.
                    if (/Expected ',' or '\]' after array element|Expected double-quoted property name/.test(e.message)) {
                        let retryStr = jsonStr;
                        retryStr = retryStr.replace(/\{\s*"\s*:\s*"\s*:\s*"/g, '{ "description": "');
                        retryStr = retryStr.replace(/,\s*"\s*:\s*"\s*:\s*"/g, ', "description": "');
                        let prevRetry = '';
                        while (prevRetry !== retryStr) {
                            prevRetry = retryStr;
                            retryStr = retryStr.replace(/\}\s*\{/g, '}, {');
                        }
                        prevRetry = '';
                        while (prevRetry !== retryStr) {
                            prevRetry = retryStr;
                            retryStr = retryStr.replace(/\]\s*\}\s*\{/g, '] }, {');
                        }
                        retryStr = retryStr.replace(/\}\s*"(description|name)"\s*:/g, '}, "$1":');
                        retryStr = retryStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*,\s*"items"\s*:/g, '{ "name": "$1", "items":');
                        retryStr = retryStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*,\s*/g, '{ "content": "$1" }, ');
                        retryStr = retryStr.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*\]/g, '{ "content": "$1" } ]');
                        try {
                            return JSON.parse(retryStr);
                        } catch (eRetry) { /* fall through to normal repair path */ }
                    }
                    
                    // Extract error position if available
                    const errorMatch = e.message.match(/position (\d+)/);
                    const errorPos = errorMatch ? parseInt(errorMatch[1]) : -1;
                    
                    // #region agent log
                    // Log error details
                    // #endregion
                    
                    // Log detailed error info
                    console.error('parseCvRewriteJsonFromAI: Parse error at position', errorPos);
                    console.error('parseCvRewriteJsonFromAI: Error message:', e.message);
                    
                    if (errorPos >= 0 && errorPos < jsonStr.length) {
                        // Show context around error (200 chars before and after)
                        const contextStart = Math.max(0, errorPos - 200);
                        const contextEnd = Math.min(jsonStr.length, errorPos + 200);
                        const context = jsonStr.slice(contextStart, contextEnd);
                        const markerPos = errorPos - contextStart;
                        const markedContext = context.slice(0, markerPos) + '>>>ERROR HERE<<<' + context.slice(markerPos);
                        console.error('parseCvRewriteJsonFromAI: Context around error:\n', markedContext);
                    } else {
                        // Show first 1000 and last 1000 chars if we can't find error position
                        console.error('parseCvRewriteJsonFromAI: First 1000 chars:', jsonStr.slice(0, 1000));
                        console.error('parseCvRewriteJsonFromAI: Last 1000 chars:', jsonStr.slice(-1000));
                    }
                    
                    // Try additional repairs for common array issues
                    let repaired = jsonStr.trim();
                    try {
                        // Re-apply the two critical fixes first
                        repaired = repaired.replace(/\{\s*"\s*:\s*"\s*:\s*"/g, '{ "description": "');
                        repaired = repaired.replace(/,\s*"\s*:\s*"\s*:\s*"/g, ', "description": "');
                        let prevRepaired = '';
                        while (prevRepaired !== repaired) {
                            prevRepaired = repaired;
                            repaired = repaired.replace(/\}\s*\{/g, '}, {');
                        }
                        // Missing comma after ] } (category objects in array)
                        prevRepaired = '';
                        while (prevRepaired !== repaired) {
                            prevRepaired = repaired;
                            repaired = repaired.replace(/\]\s*\}\s*\{/g, '] }, {');
                        }
                        repaired = repaired.replace(/\}\s*"(description|name)"\s*:/g, '}, "$1":');
                        repaired = repaired.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*,\s*"items"\s*:/g, '{ "name": "$1", "items":');
                        repaired = repaired.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*,\s*/g, '{ "content": "$1" }, ');
                        repaired = repaired.replace(/\{\s*"description"\s*:\s*"([^"]*)"\s*\}\s*\]/g, '{ "content": "$1" } ]');
                        // Ensure it starts with {
                        if (!repaired.startsWith('{')) {
                            const braceStart = repaired.indexOf('{');
                            if (braceStart >= 0) {
                                repaired = repaired.slice(braceStart);
                            }
                        }
                        
                        // Strip any model tokens that might have been missed
                        repaired = repaired.replace(/<\|[^]*?\|>/g, '');
                        repaired = repaired.replace(/\[INST\][\s\S]*?\[\/INST\]/gi, '');
                        repaired = repaired.replace(/<s>[\s\S]*?<\/s>/gi, '');
                        repaired = repaired.replace(/<\|im_start\|>[\s\S]*?<\|im_end\|>/gi, '');
                        repaired = repaired.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, '": ""');
                        repaired = repaired.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, '": ""');
                        repaired = repaired.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, ': ""');
                        repaired = repaired.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                        repaired = repaired.replace(/:\s*"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                        
                        // Fix tokens appearing after closing brackets/braces
                        // Pattern: ]assistant", "prop": -> ], "prop":
                        repaired = repaired.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                        repaired = repaired.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                        // Pattern: ]assistant"prop": -> ], "prop":
                        repaired = repaired.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                        repaired = repaired.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                        repaired = repaired.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1 "$3":');
                        repaired = repaired.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1 "$3":');
                        
                        // First, escape any remaining control characters that might have been missed
                        repaired = repaired.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"/g, function (m) {
                            const stringContent = m.slice(1, -1);
                            let result = '"';
                            for (let i = 0; i < stringContent.length; i++) {
                                const ch = stringContent[i];
                                if (ch === '\\' && i + 1 < stringContent.length) {
                                    result += ch + stringContent[i + 1];
                                    i++;
                                } else if (ch === '\n') {
                                    result += '\\n';
                                } else if (ch === '\r') {
                                    result += '\\r';
                                } else if (ch === '\t') {
                                    result += '\\t';
                                } else if (ch.charCodeAt(0) < 32) {
                                    result += ' ';
                                } else {
                                    result += ch;
                                }
                            }
                            result += '"';
                            return result;
                        });
                        
                        // Fix empty property names and multiple colons first
                        // Pattern: "  }, ": ": [ -> "  }, "work_experience": [
                        // Handle cases where empty property appears after closing brace (with newlines/whitespace)
                        repaired = repaired.replace(/}\s*,\s*[\s\n]*":\s*":\s*\[/g, '}, "work_experience": [');
                        repaired = repaired.replace(/}\s*[\s\n]*":\s*":\s*\[/g, '}, "work_experience": [');
                        // Also handle without quotes around the colons: : : [ -> work_experience: [
                        repaired = repaired.replace(/}\s*,\s*[\s\n]*:\s*:\s*\[/g, '}, "work_experience": [');
                        repaired = repaired.replace(/}\s*[\s\n]*:\s*:\s*\[/g, '}, "work_experience": [');
                        
                        // NEW: Handle empty property inside objects (after a property value): should be "items"
                        // Pattern: "name": "value", ": ": [ -> "name": "value", "items": [
                        // Handle with flexible whitespace including newlines
                        repaired = repaired.replace(/,\s*[\s\n]*":\s*":\s*\[/g, ', "items": [');
                        repaired = repaired.replace(/,\s*":\s*":\s*\[/g, ', "items": [');
                        // Also handle at the start of an object: { ": ": [ -> { "items": [
                        repaired = repaired.replace(/\{\s*[\s\n]*":\s*":\s*\[/g, '{ "items": [');
                        repaired = repaired.replace(/\{\s*":\s*":\s*\[/g, '{ "items": [');
                        // Generic fallback: any ": ": [ that appears after a quoted value (inside an object)
                        repaired = repaired.replace(/"([^"]+)",\s*[\s\n]*":\s*":\s*\[/g, '"$1", "items": [');
                        repaired = repaired.replace(/"([^"]+)"\s*[\s\n]*":\s*":\s*\[/g, '"$1", "items": [');
                        // Most aggressive: replace ALL remaining ": ": [ patterns with "items": [
                        repaired = repaired.replace(/":\s*":\s*\[/g, '"items": [');
                        repaired = repaired.replace(/":[\s\n]+":[\s\n]+\[/g, '"items": [');
                        
                        // Only remove ": ": { if it's not part of a nested structure we want to keep
                        repaired = repaired.replace(/,\s*":\s*":\s*\{/g, ', ');
                        repaired = repaired.replace(/\{\s*":\s*":\s*\{/g, '{');
                        
                        // Fix empty property names in specific contexts
                        // Pattern: "professional_summary": { ": ": "value" -> "professional_summary": { "description": "value"
                        // Handle with newlines and whitespace, and properly handle escaped quotes in values
                        repaired = repaired.replace(/"professional_summary"\s*:\s*\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"professional_summary": { "description": "$1"');
                        repaired = repaired.replace(/"work_experience"\s*:\s*\[[\s\n]*\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"work_experience": [{ "description": "$1"');
                        
                        // Fix other empty property names: ": ": "value" -> "description": "value" (when inside an object)
                        // Match { followed by whitespace/newlines, then ": ": "value" (handle escaped quotes)
                        repaired = repaired.replace(/\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '{ "description": "$1"');
                        repaired = repaired.replace(/":\s*"([a-z_]+)":/gi, '"$1":');
                        repaired = repaired.replace(/:\s*"([a-z_]+)":/gi, '"$1":');
                        
                        // Fix missing commas: "value": "prop": -> "value", "prop":
                        // Be more careful - only fix if it looks like a VALUE (long text) followed by a property name
                        repaired = repaired.replace(/"([^"]{20,500})":\s*"([a-z_]{1,30})":/gi, function(match, value, prop) {
                            // Only fix if:
                            // 1. value is long enough to be a real value (not a property name) - at least 20 chars
                            // 2. prop looks like a property name (short, lowercase with underscores)
                            // 3. value doesn't look like a property name itself
                            if (prop.length >= 1 && prop.length <= 30 && /^[a-z_]+$/i.test(prop)) {
                                // Check if value looks like a real value (not a property name)
                                if (value.length >= 20 && value.length <= 500 && !/^(id|position|company_name|description|name|title|degree|field_of_study|institution)$/i.test(value)) {
                                    return '"' + value + '", "' + prop + '":';
                                }
                            }
                            return match;
                        });
                        
                        // Fix missing commas: Match the exact error pattern "prop1": "value": "prop2":
                        // Use direct approach with common property names for reliability
                        const commonProps = ['id', 'position', 'company_name', 'description', 'name', 'title', 'start_date', 'end_date', 'location', 'degree', 'field_of_study', 'institution', 'items', 'content'];
                        changed = true;
                        iterations = 0;
                        while (changed && iterations < 50) {
                            const before = repaired;
                            // SIMPLEST PATTERN: Find ": "prop": where prop is a known property, and insert comma before it
                            for (const prop of commonProps) {
                                const pattern = new RegExp('"([^"]+)":\\s*"' + prop + '":', 'gi');
                                repaired = repaired.replace(pattern, function(match, value) {
                                    if (value.length >= 5 && value.length <= 500 && !commonProps.includes(value.toLowerCase())) {
                                        return '"' + value + '", "' + prop + '":';
                                    }
                                    return match;
                                });
                            }
                            // Also try the full sequence pattern: "prop1": "value": "prop2":
                            for (const prop1 of commonProps) {
                                for (const prop2 of commonProps) {
                                    if (prop1 !== prop2) {
                                        const pattern = new RegExp('"' + prop1 + '":\\s*"([^"]+)":\\s*"' + prop2 + '":', 'gi');
                                        repaired = repaired.replace(pattern, function(match, value) {
                                            if (value.length >= 5 && value.length <= 500) {
                                                return '"' + prop1 + '": "' + value + '", "' + prop2 + '":';
                                            }
                                            return match;
                                        });
                                    }
                                }
                            }
                            changed = (repaired !== before);
                            iterations++;
                        }
                        
                        // Fix broken strings caused by explanatory text mid-JSON
                        // Pattern: "text (explanatory phrase) {" -> "text", {
                        repaired = repaired.replace(/"([^"]*?)\s*(Here is|The following|Now|Continuing|Below|Following|I will|This is|These are)[^"]*?\s*\{/gi, '"$1", {');
                        
                        // Fix cases where explanatory text breaks out of string without proper closing
                        // Look for: word "explanatory { -> word", {
                        repaired = repaired.replace(/(\w+)\s*["']?\s*(Here is|The following|Now|Continuing|Below|Following|I will|This is|These are)[^"]*?\s*\{/gi, '$1", {');
                        
                        // Fix trailing commas in arrays more aggressively (but be careful not to break strings)
                        repaired = repaired.replace(/,(\s*[}\]])/g, '$1');
                        // Fix missing commas between array elements: ] [ -> ], [
                        repaired = repaired.replace(/\]\s*\[/g, '], [');
                        // Fix missing commas between object properties: } { -> }, {
                        repaired = repaired.replace(/\}\s*\{/g, '}, {');
                        
                        // Fix missing commas after array elements or object values before closing braces/brackets
                        // Be careful: only fix outside of strings
                        // Pattern: ] } or value } -> ], } or value, }
                        // But we need to be careful not to break strings, so we'll use a simpler approach
                        // Just fix obvious cases where we have ] or } followed by } or ]
                        repaired = repaired.replace(/(\])\s*(\})/g, '$1, $2');
                        repaired = repaired.replace(/(\})\s*(\})/g, '$1, $2');
                        
                        // Truncation repair: if output ends mid-string (no closing " or structure), close and balance
                        if (/Expected ',' or '\]' after array element/.test(e.message) && errorPos >= 0 && errorPos >= repaired.length * 0.85) {
                            let trunc = repaired;
                            if (!/["\}\]]$/.test(trunc.trim())) {
                                trunc += '"';
                            }
                            const stack = [];
                            let inString = false;
                            let escape = false;
                            let quote = null;
                            for (let i = 0; i < trunc.length; i++) {
                                const c = trunc[i];
                                if (inString) {
                                    if (escape) { escape = false; continue; }
                                    if (c === '\\' && quote === '"') { escape = true; continue; }
                                    if (c === quote) { inString = false; continue; }
                                    continue;
                                }
                                if (c === '"' || c === "'") { inString = true; quote = c; continue; }
                                if (c === '{') stack.push('}');
                                else if (c === '[') stack.push(']');
                                else if (c === '}' || c === ']') stack.pop();
                            }
                            trunc += stack.reverse().join('');
                            try {
                                return JSON.parse(trunc);
                            } catch (eTrunc) { repaired = trunc; }
                        }
                        // Ensure balanced braces/brackets (string-aware so we don't count inside values)
                        const stack = [];
                        let inStr = false;
                        let esc = false;
                        let q = null;
                        for (let i = 0; i < repaired.length; i++) {
                            const c = repaired[i];
                            if (inStr) {
                                if (esc) { esc = false; continue; }
                                if (c === '\\' && q === '"') { esc = true; continue; }
                                if (c === q) { inStr = false; continue; }
                                continue;
                            }
                            if (c === '"' || c === "'") { inStr = true; q = c; continue; }
                            if (c === '{') stack.push('}');
                            else if (c === '[') stack.push(']');
                            else if (c === '}' || c === ']') stack.pop();
                        }
                        if (stack.length > 0) {
                            repaired += stack.reverse().join('');
                        }
                        
                        return JSON.parse(repaired);
                    } catch (e2) {
                        console.error('parseCvRewriteJsonFromAI: Repair attempt also failed:', e2.message);
                        
                        // Try character-by-character repair at the error position
                        if (errorPos >= 0 && errorPos < repaired.length) {
                            try {
                                // Look backwards from error position to find the start of the broken string
                                let repairAttempt = repaired;
                                
                                // Strip model tokens first
                                repairAttempt = repairAttempt.replace(/<\|[^]*?\|>/g, '');
                                repairAttempt = repairAttempt.replace(/\[INST\][\s\S]*?\[\/INST\]/gi, '');
                                repairAttempt = repairAttempt.replace(/<s>[\s\S]*?<\/s>/gi, '');
                                repairAttempt = repairAttempt.replace(/<\|im_start\|>[\s\S]*?<\|im_end\|>/gi, '');
                                repairAttempt = repairAttempt.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, '": ""');
                                repairAttempt = repairAttempt.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, '": ""');
                                repairAttempt = repairAttempt.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, ': ""');
                                repairAttempt = repairAttempt.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                                repairAttempt = repairAttempt.replace(/:\s*"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                                repairAttempt = repairAttempt.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                                repairAttempt = repairAttempt.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                                repairAttempt = repairAttempt.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                                repairAttempt = repairAttempt.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                                repairAttempt = repairAttempt.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1 "$3":');
                                repairAttempt = repairAttempt.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1 "$3":');
                                
                                // Fix empty property names in specific contexts
                                repairAttempt = repairAttempt.replace(/"professional_summary"\s*:\s*\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"professional_summary": { "description": "$1"');
                                repairAttempt = repairAttempt.replace(/"work_experience"\s*:\s*\[[\s\n]*\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"work_experience": [{ "description": "$1"');
                                repairAttempt = repairAttempt.replace(/\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '{ "description": "$1"');
                                
                                // Fix tokens breaking strings between quotes
                                repairAttempt = repairAttempt.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*"([^"]*?)"/g, function(match, text1, token, whitespace, text2) {
                                    return '"' + text1.trim() + '", "' + text2.trim() + '"';
                                });
                                repairAttempt = repairAttempt.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*([^"]*?)"/g, function(match, text1, token, whitespace, text2) {
                                    return '"' + text1.trim() + '", "' + text2.trim() + '"';
                                });
                                
                                // First, escape control characters
                                repairAttempt = repairAttempt.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"/g, function (m) {
                                    const stringContent = m.slice(1, -1);
                                    let result = '"';
                                    for (let i = 0; i < stringContent.length; i++) {
                                        const ch = stringContent[i];
                                        if (ch === '\\' && i + 1 < stringContent.length) {
                                            result += ch + stringContent[i + 1];
                                            i++;
                                        } else if (ch === '\n') {
                                            result += '\\n';
                                        } else if (ch === '\r') {
                                            result += '\\r';
                                        } else if (ch === '\t') {
                                            result += '\\t';
                                        } else if (ch.charCodeAt(0) < 32) {
                                            result += ' ';
                                        } else {
                                            result += ch;
                                        }
                                    }
                                    result += '"';
                                    return result;
                                });
                                
                                // Try to fix broken strings by finding unclosed quotes before explanatory text
                                // Look for pattern: "text (explanatory) { and close the string
                                const brokenStringPattern = /"([^"]*?)\s*(Here is|The following|Now|Continuing|Below|Following|I will|This is|These are|rest of)[^"]*?\s*\{/gi;
                                repairAttempt = repairAttempt.replace(brokenStringPattern, function(match, stringContent) {
                                    // Close the string and add comma
                                    return '"' + stringContent.trim() + '", ';
                                });
                                
                                // Also try to fix cases where text appears before a brace without proper JSON structure
                                repairAttempt = repairAttempt.replace(/(\w+)\s+(Here is|The following|Now|Continuing|Below|Following|I will|This is|These are|rest of)[^"]*?\s*\{/gi, function(match, lastWord) {
                                    // Try to close any open string context
                                    return lastWord + '", ';
                                });
                                
                                // Fix: "prop1": "value": "prop2": -> "prop1": "value", "prop2":
                                repairAttempt = repairAttempt.replace(/"([a-z_]{1,30})":\s*"([^"]{5,500})":\s*"([a-z_]{1,30})":/gi, function(match, prop1, value, prop2) {
                                    if (/^[a-z_]+$/i.test(prop1) && /^[a-z_]+$/i.test(prop2) && value.length >= 5) {
                                        return '"' + prop1 + '": "' + value + '", "' + prop2 + '":';
                                    }
                                    return match;
                                });
                                
                                // Fix empty property names: "  }, ": ": [ -> "  }, "work_experience": [
                                repairAttempt = repairAttempt.replace(/}\s*,\s*":\s*":\s*\[/g, '}, "work_experience": [');
                                repairAttempt = repairAttempt.replace(/}\s*":\s*":\s*\[/g, '}, "work_experience": [');
                                repairAttempt = repairAttempt.replace(/":\s*":\s*\[/g, '"work_experience": [');
                                
                                // Fix trailing commas and missing commas
                                repairAttempt = repairAttempt.replace(/,(\s*[}\]])/g, '$1');
                                repairAttempt = repairAttempt.replace(/\]\s*\[/g, '], [');
                                repairAttempt = repairAttempt.replace(/\}\s*\{/g, '}, {');
                                
                                return JSON.parse(repairAttempt);
                            } catch (e3) {
                                console.error('parseCvRewriteJsonFromAI: Character-level repair failed:', e3.message);
                            }
                        }
                        
                        // Try one more time with just the first balanced JSON object
                        const jsonMatch = repaired.match(/\{[\s\S]*\}/);
                        if (jsonMatch && jsonMatch[0]) {
                            try {
                                let extracted = jsonMatch[0];
                                
                                // Strip model tokens first
                                extracted = extracted.replace(/<\|[^]*?\|>/g, '');
                                extracted = extracted.replace(/\[INST\][\s\S]*?\[\/INST\]/gi, '');
                                extracted = extracted.replace(/<s>[\s\S]*?<\/s>/gi, '');
                                extracted = extracted.replace(/<\|im_start\|>[\s\S]*?<\|im_end\|>/gi, '');
                                extracted = extracted.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, '": ""');
                                extracted = extracted.replace(/":\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, '": ""');
                                extracted = extracted.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)""/gi, ': ""');
                                extracted = extracted.replace(/:\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                                extracted = extracted.replace(/:\s*"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)"/gi, ': ""');
                                extracted = extracted.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                                extracted = extracted.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1, "$3":');
                                extracted = extracted.replace(/(\])\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                                extracted = extracted.replace(/(\})\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1, "$3":');
                                extracted = extracted.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*",\s*"([^"]+)":/gi, '$1 "$3":');
                                extracted = extracted.replace(/(\{)\s*(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)\s*"([^"]+)":/gi, '$1 "$3":');
                                
                                // Fix tokens breaking strings between quotes
                                extracted = extracted.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*"([^"]*?)"/g, function(match, text1, token, whitespace, text2) {
                                    return '"' + text1.trim() + '", "' + text2.trim() + '"';
                                });
                                extracted = extracted.replace(/"([^"]*?)"(assistant|user|system|any_name|start_header_id|end_header_id|im_start|im_end)(\\n|\\r|\\t|\n|\r|\t|\s)*([^"]*?)"/g, function(match, text1, token, whitespace, text2) {
                                    return '"' + text1.trim() + '", "' + text2.trim() + '"';
                                });
                                
                                // First, escape control characters
                                extracted = extracted.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"/g, function (m) {
                                    const stringContent = m.slice(1, -1);
                                    let result = '"';
                                    for (let i = 0; i < stringContent.length; i++) {
                                        const ch = stringContent[i];
                                        if (ch === '\\' && i + 1 < stringContent.length) {
                                            result += ch + stringContent[i + 1];
                                            i++;
                                        } else if (ch === '\n') {
                                            result += '\\n';
                                        } else if (ch === '\r') {
                                            result += '\\r';
                                        } else if (ch === '\t') {
                                            result += '\\t';
                                        } else if (ch.charCodeAt(0) < 32) {
                                            result += ' ';
                                        } else {
                                            result += ch;
                                        }
                                    }
                                    result += '"';
                                    return result;
                                });
                                
                                // Fix empty property names in specific contexts
                                extracted = extracted.replace(/"professional_summary"\s*:\s*\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"professional_summary": { "description": "$1"');
                                extracted = extracted.replace(/"work_experience"\s*:\s*\[[\s\n]*\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '"work_experience": [{ "description": "$1"');
                                extracted = extracted.replace(/\{[\s\n]*":\s*":\s*"([^"\\]*(?:\\.[^"\\]*)*)"/g, '{ "description": "$1"');
                                
                                // One more pass of repairs
                                extracted = extracted
                                    .replace(/"([^"]*?)\s*(Here is|The following|Now|Continuing|Below|Following|I will|This is|These are|rest of)[^"]*?\s*\{/gi, '"$1", {')
                                    .replace(/,(\s*[}\]])/g, '$1')
                                    .replace(/\]\s*\[/g, '], [')
                                    .replace(/\}\s*\{/g, '}, {');
                                return JSON.parse(extracted);
                            } catch (e3) {
                                console.error('parseCvRewriteJsonFromAI: Final repair attempt failed:', e3.message);
                            }
                        }
                    }
                    
                    throw new Error('Failed to parse AI response as JSON: ' + e.message + '. The AI may have returned malformed JSON. Please try again.');
                }
            }
            
            let rewrittenData;
            try {
                rewrittenData = parseCvRewriteJsonFromAI(rewrittenText);
            } catch (e) {
                throw new Error('Failed to parse AI response as JSON: ' + e.message);
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
                // Redirect to CV variants list
                window.location.hash = '#cv-variants';
                // Reload the section to show the new variant
                if (typeof window.contentEditor !== 'undefined' && typeof window.contentEditor.loadSection === 'function') {
                    setTimeout(() => {
                        window.contentEditor.loadSection('cv-variants');
                    }, 500);
                }
            } else if (saveResult.error && saveResult.error.indexOf('already exists for this job') !== -1) {
                alert('A CV variant already exists for this job. Open it from CV Variants and use "Tailor section…" to tailor more sections.');
                window.location.hash = '#cv-variants';
                if (typeof window.contentEditor !== 'undefined' && typeof window.contentEditor.loadSection === 'function') {
                    setTimeout(() => window.contentEditor.loadSection('cv-variants'), 300);
                }
            } else {
                throw new Error(saveResult.error || 'Failed to save rewritten CV');
            }
        } catch (error) {
            console.error('Browser AI execution error:', error);
            if (loadingOverlay) loadingOverlay.remove();
            
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            if (submitBtn) submitBtn.disabled = false;
            if (submitText) submitText.classList.remove('hidden');
            if (submitLoading) submitLoading.classList.add('hidden');
            
            alert('Browser AI Error: ' + error.message);
        }
    }
})();
</script>
