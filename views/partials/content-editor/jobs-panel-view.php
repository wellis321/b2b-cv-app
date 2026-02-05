<?php
/**
 * Single job application view within content-editor (#jobs&view=id)
 * $job and $userId are set by get-section-form.php
 */
$statusLabels = [
    'interested' => 'Interested',
    'in_progress' => 'In Progress',
    'applied' => 'Applied',
    'interviewing' => 'Interviewing',
    'offered' => 'Offered',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
    'withdrawn' => 'Withdrawn',
];
$statusClass = [
    'interested' => 'bg-sky-100 text-sky-800',
    'in_progress' => 'bg-orange-100 text-orange-800',
    'applied' => 'bg-amber-100 text-amber-800',
    'interviewing' => 'bg-purple-100 text-purple-800',
    'offered' => 'bg-blue-100 text-blue-800',
    'accepted' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800',
    'withdrawn' => 'bg-gray-100 text-gray-800',
];
$status = $job['status'] ?? 'applied';
$statusLabel = $statusLabels[$status] ?? $status;
$statusCss = $statusClass[$status] ?? 'bg-gray-100 text-gray-800';
$csrf = csrfToken();
$appDate = !empty($job['application_date']) ? date('j M Y', strtotime($job['application_date'])) : '—';
?>
<div class="flex gap-6 relative" data-jobs-view-container data-application-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>">
    <!-- Sticky Navigation Sidebar -->
    <aside class="hidden lg:block w-64 flex-shrink-0 self-start">
        <nav class="bg-white border border-gray-200 rounded-lg shadow-sm" id="job-view-nav" aria-label="Job view navigation" style="display: flex; flex-direction: column; overflow: hidden;">
            <div class="p-4 pb-2 flex-shrink-0 border-b border-gray-100" id="nav-heading" style="flex-shrink: 0;">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Quick Navigation</h3>
            </div>
            <ul class="space-y-1 px-4 py-2 flex-1 overflow-y-auto" id="nav-menu-list" style="min-height: 0;">
                <li><a href="#job-overview" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="job-overview">Overview</a></li>
                <?php if (!empty($job['job_description'])): ?>
                <li><a href="#job-description" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="job-description">Description</a></li>
                <?php endif; ?>
                <?php if (!empty($job['application_url'])): ?>
                <li><a href="#application-link" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="application-link">Application Link</a></li>
                <?php endif; ?>
                <?php if (!empty($job['extracted_keywords']) || !empty($job['job_description'])): ?>
                <li><a href="#keywords" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="keywords">Keywords & Skills</a></li>
                <?php endif; ?>
                <?php if (!empty($job['notes'])): ?>
                <li><a href="#notes" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="notes">Notes</a></li>
                <?php endif; ?>
                <li><a href="#application-questions" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="application-questions">Application questions</a></li>
                <li><a href="#job-actions" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="job-actions">Job Actions</a></li>
                <li><a href="#cover-letter" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="cover-letter">Cover Letter</a></li>
                <li><a href="#cover-letter-actions" class="job-nav-link block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors" data-section="cover-letter-actions">Cover Letter Actions</a></li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="flex-1 min-w-0">
        <div class="p-6 max-w-3xl mx-auto">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <a href="#jobs" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900" data-jobs-back>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to list
                </a>
                <nav class="flex flex-wrap items-center gap-2" aria-label="Job actions">
                    <a href="#jobs&amp;edit=<?php echo e($job['id']); ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-green-700 bg-green-50 rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors" data-jobs-edit data-edit-id="<?php echo e($job['id']); ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <button type="button" data-jobs-delete data-job-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </nav>
            </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div id="job-overview" class="scroll-mt-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo e($job['job_title'] ?? 'Untitled'); ?></h1>
                    <p class="text-lg text-gray-600 font-medium mt-1"><?php echo e($job['company_name'] ?? ''); ?></p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusCss; ?>"><?php echo e($statusLabel); ?></span>
            </div>
        <div class="space-y-4 text-sm">
            <?php if (!empty($job['job_location'])): ?>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-gray-700"><?php echo e($job['job_location']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($job['salary_range'])): ?>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0-7v1m0-1c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-gray-700"><?php echo e($job['salary_range']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($job['remote_type']) && $job['remote_type'] !== 'onsite'): ?>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-gray-700"><?php echo e(ucfirst($job['remote_type'])); ?></span>
            </div>
            <?php endif; ?>
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Applied: <?php echo e($appDate); ?>
            </div>
            <?php if (!empty($job['next_follow_up'])): ?>
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Follow-up / closing date: <?php echo e(date('j M Y', strtotime($job['next_follow_up']))); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($job['job_description'])): ?>
        <div id="job-description" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Description</h2>
            <div class="text-gray-700 job-description-content"><?php echo renderJobDescription($job['job_description'] ?? ''); ?></div>
        </div>
        <style>.job-description-content table { border-collapse: collapse; width: 100%; margin: 0.75rem 0; }
.job-description-content td, .job-description-content th { border: 1px solid #d1d5db; padding: 0.375rem 0.5rem; text-align: left; vertical-align: top; }
.job-description-content th { background: #f3f4f6; font-weight: 600; }</style>
        
        <!-- Keywords Section -->
        <div id="keywords" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-gray-900">Key Keywords & Skills</h2>
                <div class="flex items-center gap-2">
                    <?php 
                    $hasKeywords = !empty($job['extracted_keywords']) && is_array(json_decode($job['extracted_keywords'], true)) && count(json_decode($job['extracted_keywords'], true)) > 0;
                    ?>
                    <button type="button" id="delete-keywords-btn" data-application-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors <?php echo $hasKeywords ? '' : 'hidden'; ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete keywords
                    </button>
                    <button type="button" id="extract-keywords-btn" data-application-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md border border-blue-200 hover:bg-blue-100 hover:border-blue-300 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <?php 
                        $extractedKeywords = !empty($job['extracted_keywords']) ? json_decode($job['extracted_keywords'], true) : null;
                        echo $extractedKeywords ? 'Re-extract Keywords' : 'Extract Keywords';
                        ?>
                    </button>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-800 mb-3">Select keywords to use when generating your CV for this role</p>
            <div id="keywords-container" class="space-y-3">
                <?php 
                $extractedKeywords = !empty($job['extracted_keywords']) ? json_decode($job['extracted_keywords'], true) : null;
                $selectedKeywords = !empty($job['selected_keywords']) ? json_decode($job['selected_keywords'], true) : [];
                if (!$selectedKeywords || !is_array($selectedKeywords)) {
                    $selectedKeywords = [];
                }
                ?>
                <?php if ($extractedKeywords && is_array($extractedKeywords) && count($extractedKeywords) > 0): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                        <p class="text-xs font-medium text-blue-900 mb-2">Select keywords to include when generating your CV for this role:</p>
                        <div class="flex flex-wrap gap-2" id="keywords-list">
                            <?php foreach ($extractedKeywords as $keyword): ?>
                                <?php $isSelected = in_array($keyword, $selectedKeywords); ?>
                                <label class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border cursor-pointer transition-all <?php echo $isSelected ? 'bg-green-100 text-green-800 border-green-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'; ?>">
                                    <input type="checkbox" 
                                           class="sr-only keyword-checkbox" 
                                           value="<?php echo e($keyword); ?>" 
                                           <?php echo $isSelected ? 'checked' : ''; ?>
                                           data-keyword="<?php echo e($keyword); ?>">
                                    <?php if ($isSelected): ?>
                                        <svg class="w-4 h-4 mr-1.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    <?php endif; ?>
                                    <span><?php echo e($keyword); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="mt-3 text-sm font-medium text-gray-800">Selected keywords will be used to tailor your CV when you generate a CV variant for this job.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <p class="text-sm text-gray-600 mb-2">Click "Extract Keywords" to identify important keywords from this job description</p>
                        <p class="text-xs text-gray-500">These keywords can help you tailor your CV and cover letter for this application</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($job['notes'])): ?>
        <div id="notes" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Notes</h2>
            <div class="text-gray-700 notes-content"><?php echo renderMarkdown($job['notes'] ?? ''); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($job['application_url'])): ?>
        <div id="application-link" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Application Link</h2>
            <a href="<?php echo e($job['application_url']); ?>" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Link here</a>
        </div>
        <?php endif; ?>

        <!-- Application questions -->
        <div id="application-questions" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Application questions</h2>
            <p class="text-xs text-gray-600 mb-4">Add questions from the application form and generate draft answers tailored to this role and your CV.</p>
            <div class="space-y-4 mb-4">
                <div class="flex gap-2 flex-wrap items-start">
                    <div class="flex-1 min-w-[200px] space-y-1">
                        <input type="text" id="app-question-new-text" placeholder="Paste or type a question from the application form…" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <input type="text" id="app-question-new-instructions" placeholder="Instructions (optional) – e.g. Max 100 words, use bullet points" class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-md focus:ring-green-500 focus:border-green-500 text-gray-600">
                    </div>
                    <button type="button" id="app-question-add-btn" data-application-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add question
                    </button>
                </div>
            </div>
            <div id="application-questions-list" class="space-y-6">
                <?php 
                $jobQuestions = isset($job['questions']) && is_array($job['questions']) ? $job['questions'] : [];
                foreach ($jobQuestions as $q): 
                    $qId = $q['id'] ?? '';
                    $qText = $q['question_text'] ?? '';
                    $qAnswer = $q['answer_text'] ?? '';
                    $qInstructions = $q['answer_instructions'] ?? '';
                ?>
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50" data-question-id="<?php echo e($qId); ?>">
                    <p class="text-sm font-medium text-gray-900 mb-2"><?php echo e($qText); ?></p>
                    <label class="block text-xs text-gray-600 mb-1">Instructions (optional) – e.g. max 100 words, use bullet points</label>
                    <input type="text" class="app-question-instructions w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 mb-2" data-question-id="<?php echo e($qId); ?>" placeholder="e.g. Max 150 words, use bullet points" value="<?php echo e($qInstructions); ?>">
                    <div class="flex flex-wrap gap-2 mb-2">
                        <button type="button" class="app-question-generate inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700" data-question-id="<?php echo e($qId); ?>" data-application-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Generate answer with AI
                        </button>
                        <button type="button" class="app-question-save inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" data-question-id="<?php echo e($qId); ?>" data-csrf="<?php echo e($csrf); ?>">Save answer</button>
                        <button type="button" class="app-question-delete inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100" data-question-id="<?php echo e($qId); ?>" data-csrf="<?php echo e($csrf); ?>">Delete question</button>
                    </div>
                    <textarea class="app-question-answer w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 min-h-[120px]" data-question-id="<?php echo e($qId); ?>" placeholder="Answer (generate with AI or type)"><?php echo e($qAnswer); ?></textarea>
                </div>
                <?php endforeach; ?>
                <?php if (empty($jobQuestions)): ?>
                <div id="application-questions-empty" class="text-sm text-gray-500 italic">No questions yet. Add a question above to get started.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Job actions (pertain to job description, not cover letter) -->
        <div id="job-actions" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6" role="group" aria-labelledby="job-actions-heading">
            <p id="job-actions-heading" class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Job actions</p>
            <p class="text-xs text-gray-500 mb-2">Use <strong>Generate AI CV for this job</strong> for a one-click tailored CV, or <strong>Tailor CV for this job…</strong> to choose which sections to tailor.</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-ai-cv-generate class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-purple-600 rounded-md border border-purple-700 hover:bg-purple-700 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Generate AI CV for this job
                </button>
                <a href="#cv-variants&amp;create=1&amp;job=<?php echo e($job['id']); ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-md border border-indigo-200 hover:bg-indigo-100 hover:border-indigo-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Tailor CV for this job…
                </a>
                <a href="#jobs" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100" data-jobs-back>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to list
                </a>
                <a href="#jobs&amp;edit=<?php echo e($job['id']); ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-green-700 bg-green-50 rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors" data-jobs-edit data-edit-id="<?php echo e($job['id']); ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <button type="button" data-jobs-delete data-job-id="<?php echo e($job['id']); ?>" data-csrf="<?php echo e($csrf); ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </div>
        </div>

        <!-- Cover Letter -->
        <div id="cover-letter" class="mt-6 pt-6 border-t border-gray-200 scroll-mt-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Cover Letter</h2>
            <div id="cover-letter-container-<?php echo e($job['id']); ?>" class="space-y-3" data-cover-letter-container>
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-600 mb-4">No cover letter generated yet</p>
                    <button type="button" data-cover-letter-generate class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate Cover Letter with AI
                    </button>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
<script>
(function() {
    var container = document.querySelector('[data-jobs-view-container]');
    if (!container) return;
    
    // Initialize keyword checkboxes if they exist (loaded from database)
    var existingCheckboxes = container.querySelectorAll('.keyword-checkbox');
    if (existingCheckboxes.length > 0) {
        var applicationId = container.getAttribute('data-application-id');
        var csrf = container.getAttribute('data-csrf');
        existingCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                saveSelectedKeywords(applicationId, csrf, container);
            });
        });
    }
    
    // Handle keyword extraction
    var extractBtn = container.querySelector('#extract-keywords-btn');
    if (extractBtn) {
        extractBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var applicationId = extractBtn.getAttribute('data-application-id');
            var csrf = extractBtn.getAttribute('data-csrf');
            var keywordsContainer = container.querySelector('#keywords-container');
            
            if (!applicationId || !csrf) return;
            
            // Disable button and show loading
            extractBtn.disabled = true;
            extractBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Extracting...';
            
            // Show loading state
            if (keywordsContainer) {
                keywordsContainer.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div><p class="text-sm text-blue-800">Extracting keywords from job description...</p></div>';
            }
            
            var formData = new FormData();
            formData.append('<?php echo CSRF_TOKEN_NAME; ?>', csrf);
            formData.append('application_id', applicationId);
            
            fetch('/api/extract-job-keywords.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(function(response) {
                if (!response.ok) {
                    var contentType = response.headers.get('content-type') || '';
                    if (contentType.indexOf('application/json') !== -1) {
                        // JSON error response - parse it
                        return response.json().then(function(json) {
                            return Promise.reject(new Error(json.error || 'Request failed'));
                        });
                    } else {
                        // Non-JSON error response - read as text
                        return response.text().then(function(text) {
                            // Try to parse as JSON in case it's JSON but content-type is wrong
                            try {
                                var json = JSON.parse(text);
                                return Promise.reject(new Error(json.error || 'Request failed'));
                            } catch (e) {
                                // Not JSON - return user-friendly error
                                if (response.status === 503) {
                                    return Promise.reject(new Error('Service temporarily unavailable. Please check your connection and try again.'));
                                }
                                return Promise.reject(new Error('Server error: ' + response.status + ' - ' + (text || response.statusText)));
                            }
                        });
                    }
                }
                var contentType = response.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') === -1) {
                    return response.text().then(function(text) {
                        return Promise.reject(new Error('Expected JSON but got ' + contentType + ': ' + text.substring(0, 200)));
                    });
                }
                return response.text().then(function(text) {
                    // Check if response starts with HTML (PHP error)
                    if (text.trim().indexOf('<') === 0 || text.indexOf('<br') !== -1 || text.indexOf('<b>') !== -1) {
                        return Promise.reject(new Error('Server error: The server returned an error page instead of JSON. Please check the server logs or try again.'));
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        return Promise.reject(new Error('Invalid JSON response from server: ' + text.substring(0, 200)));
                    }
                });
            })
            .then(function(result) {
                if (result.success && result.browser_execution) {
                    // Handle browser AI execution
                    executeBrowserAIKeywordExtraction(result, keywordsContainer, extractBtn, csrf, applicationId);
                } else if (result.success && result.keywords) {
                    // Get current selected keywords from the page
                    var currentSelected = [];
                    var existingCheckboxes = keywordsContainer.querySelectorAll('.keyword-checkbox');
                    existingCheckboxes.forEach(function(cb) {
                        if (cb.checked) currentSelected.push(cb.value);
                    });
                    displayKeywords(result.keywords, keywordsContainer, extractBtn, currentSelected);
                } else {
                    throw new Error(result.error || 'Failed to extract keywords');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                if (keywordsContainer) {
                    keywordsContainer.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-sm text-red-800">Error: ' + (error.message || 'Failed to extract keywords') + '</p></div>';
                }
                extractBtn.disabled = false;
                extractBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Extract Keywords';
            });
        });
    }
    
    var deleteKeywordsBtn = container.querySelector('#delete-keywords-btn');
    if (deleteKeywordsBtn) {
        deleteKeywordsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var applicationId = deleteKeywordsBtn.getAttribute('data-application-id');
            var csrf = deleteKeywordsBtn.getAttribute('data-csrf');
            var keywordsContainer = container.querySelector('#keywords-container');
            if (!applicationId || !csrf || !keywordsContainer) return;
            if (!confirm('Remove all extracted and selected keywords for this job?')) return;
            deleteKeywordsBtn.disabled = true;
            deleteKeywordsBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span> Deleting...';
            var formData = new FormData();
            formData.append('<?php echo CSRF_TOKEN_NAME; ?>', csrf);
            formData.append('application_id', applicationId);
            fetch('/api/delete-job-keywords.php', { method: 'POST', body: formData, credentials: 'include' })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success) {
                        keywordsContainer.innerHTML = '<div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"><svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg><p class="text-sm text-gray-600 mb-2">Click "Extract Keywords" to identify important keywords from this job description</p><p class="text-xs text-gray-500">These keywords can help you tailor your CV and cover letter for this application</p></div>';
                        deleteKeywordsBtn.classList.add('hidden');
                        var extractBtn = container.querySelector('#extract-keywords-btn');
                        if (extractBtn) {
                            extractBtn.disabled = false;
                            extractBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Extract Keywords';
                        }
                    } else {
                        alert(result.error || 'Failed to delete keywords');
                    }
                })
                .catch(function(err) {
                    alert('Failed to delete keywords. Please try again.');
                })
                .finally(function() {
                    deleteKeywordsBtn.disabled = false;
                    deleteKeywordsBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Delete keywords';
                });
        });
    }
    
    // --- Application questions ---
    var appQuestionAddBtn = container.querySelector('#app-question-add-btn');
    var appQuestionNewText = container.querySelector('#app-question-new-text');
    var appQuestionNewInstructions = container.querySelector('#app-question-new-instructions');
    var appQuestionsList = container.querySelector('#application-questions-list');
    var appQuestionsEmpty = container.querySelector('#application-questions-empty');
    
    function escapeHtmlApp(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
    
    function renderQuestionRow(q) {
        var id = q.id || '';
        var text = q.question_text || '';
        var answer = q.answer_text || '';
        var instructions = q.answer_instructions || '';
        var csrf = container.getAttribute('data-csrf');
        var applicationId = container.getAttribute('data-application-id');
        return '<div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50" data-question-id="' + escapeHtmlApp(id) + '">' +
            '<p class="text-sm font-medium text-gray-900 mb-2">' + escapeHtmlApp(text) + '</p>' +
            '<label class="block text-xs text-gray-600 mb-1">Instructions (optional) – e.g. max 100 words, use bullet points</label>' +
            '<input type="text" class="app-question-instructions w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 mb-2" data-question-id="' + escapeHtmlApp(id) + '" placeholder="e.g. Max 150 words, use bullet points" value="' + escapeHtmlApp(instructions) + '">' +
            '<div class="flex flex-wrap gap-2 mb-2">' +
            '<button type="button" class="app-question-generate inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700" data-question-id="' + escapeHtmlApp(id) + '" data-application-id="' + escapeHtmlApp(applicationId) + '" data-csrf="' + escapeHtmlApp(csrf) + '">' +
            '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Generate answer with AI</button>' +
            '<button type="button" class="app-question-save inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" data-question-id="' + escapeHtmlApp(id) + '" data-csrf="' + escapeHtmlApp(csrf) + '">Save answer</button>' +
            '<button type="button" class="app-question-delete inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100" data-question-id="' + escapeHtmlApp(id) + '" data-csrf="' + escapeHtmlApp(csrf) + '">Delete question</button>' +
            '</div>' +
            '<textarea class="app-question-answer w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 min-h-[120px]" data-question-id="' + escapeHtmlApp(id) + '" placeholder="Answer (generate with AI or type)">' + escapeHtmlApp(answer) + '</textarea>' +
            '</div>';
    }
    
    if (appQuestionAddBtn && appQuestionNewText && appQuestionsList) {
        appQuestionAddBtn.addEventListener('click', function() {
            var applicationId = appQuestionAddBtn.getAttribute('data-application-id');
            var csrf = appQuestionAddBtn.getAttribute('data-csrf');
            var questionText = (appQuestionNewText.value || '').trim();
            if (!questionText) {
                alert('Please enter a question.');
                return;
            }
            if (!applicationId || !csrf) return;
            appQuestionAddBtn.disabled = true;
            appQuestionAddBtn.textContent = 'Adding…';
            var instructions = (appQuestionNewInstructions && appQuestionNewInstructions.value) ? appQuestionNewInstructions.value.trim() : '';
            var fd = new FormData();
            fd.append('<?php echo CSRF_TOKEN_NAME; ?>', csrf);
            fd.append('application_id', applicationId);
            fd.append('question_text', questionText);
            if (instructions) fd.append('answer_instructions', instructions);
            fetch('/api/job-application-questions.php', { method: 'POST', body: fd, credentials: 'include' })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success && result.id) {
                        if (appQuestionsEmpty) appQuestionsEmpty.style.display = 'none';
                        var div = document.createElement('div');
                        div.innerHTML = renderQuestionRow({ id: result.id, question_text: questionText, answer_text: '', answer_instructions: instructions });
                        appQuestionsList.appendChild(div.firstElementChild);
                        appQuestionNewText.value = '';
                        if (appQuestionNewInstructions) appQuestionNewInstructions.value = '';
                    } else {
                        alert(result.error || 'Failed to add question');
                    }
                })
                .catch(function() { alert('Failed to add question. Please try again.'); })
                .finally(function() {
                    appQuestionAddBtn.disabled = false;
                    appQuestionAddBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add question';
                });
        });
    }
    
    container.addEventListener('click', function(e) {
        var applicationId = container.getAttribute('data-application-id');
        var csrf = container.getAttribute('data-csrf');
        var genBtn = e.target.closest('.app-question-generate');
        var saveBtn = e.target.closest('.app-question-save');
        var delBtn = e.target.closest('.app-question-delete');
        
        if (genBtn) {
            e.preventDefault();
            var questionId = genBtn.getAttribute('data-question-id');
            var row = genBtn.closest('[data-question-id]');
            var ta = row ? row.querySelector('.app-question-answer') : null;
            var instInput = row ? row.querySelector('.app-question-instructions') : null;
            if (!questionId || !csrf || !applicationId) return;
            genBtn.disabled = true;
            genBtn.textContent = 'Generating…';
            var fd = new FormData();
            fd.append('<?php echo CSRF_TOKEN_NAME; ?>', genBtn.getAttribute('data-csrf'));
            fd.append('application_id', applicationId);
            fd.append('question_id', questionId);
            if (instInput && instInput.value.trim()) fd.append('answer_instructions', instInput.value.trim());
            fetch('/api/ai-generate-application-answer.php', { method: 'POST', body: fd, credentials: 'include' })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success && result.answer_text) {
                        if (ta) ta.value = result.answer_text;
                    } else if (result.success && result.browser_execution) {
                        if (typeof BrowserAIService !== 'undefined' && BrowserAIService.generateText) {
                            BrowserAIService.initBrowserAI(result.model_type || 'webllm', result.model).then(function() {
                                return BrowserAIService.generateText(result.prompt, { temperature: 0.6, maxTokens: 600 });
                            }).then(function(text) {
                                if (ta) ta.value = text || '';
                                var saveFd = new FormData();
                                saveFd.append('<?php echo CSRF_TOKEN_NAME; ?>', csrf);
                                saveFd.append('application_id', applicationId);
                                saveFd.append('question_id', questionId);
                                saveFd.append('answer_text', text || '');
                                return fetch('/api/ai-generate-application-answer.php', { method: 'POST', body: saveFd, credentials: 'include' });
                            }).then(function(r) { return r.json(); }).then(function() {
                                if (ta) { /* already set */ }
                            }).catch(function(err) {
                                alert('Browser AI failed: ' + (err.message || 'Please try again.'));
                            });
                        } else {
                            alert('Browser AI is not available. Please use server AI or try again.');
                        }
                    } else {
                        alert(result.error || 'Failed to generate answer');
                    }
                })
                .catch(function(err) {
                    alert(err.message || 'Failed to generate answer. Please try again.');
                })
                .finally(function() {
                    genBtn.disabled = false;
                    genBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Generate answer with AI';
                });
            return;
        }
        
        if (saveBtn) {
            e.preventDefault();
            var questionId = saveBtn.getAttribute('data-question-id');
            var row = saveBtn.closest('[data-question-id]');
            var ta = row ? row.querySelector('.app-question-answer') : null;
            var instInput = row ? row.querySelector('.app-question-instructions') : null;
            var answerText = ta ? ta.value : '';
            var answerInstructions = instInput ? instInput.value : '';
            var body = JSON.stringify({
                question_id: questionId,
                answer_text: answerText,
                answer_instructions: answerInstructions,
                csrf_token: saveBtn.getAttribute('data-csrf')
            });
            fetch('/api/job-application-questions.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: body,
                credentials: 'include'
            })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success) {
                        saveBtn.textContent = 'Saved';
                        setTimeout(function() { saveBtn.textContent = 'Save answer'; }, 1500);
                    } else {
                        alert(result.error || 'Failed to save');
                    }
                })
                .catch(function() { alert('Failed to save. Please try again.'); });
            return;
        }
        
        if (delBtn) {
            e.preventDefault();
            if (!confirm('Delete this question and its answer?')) return;
            var questionId = delBtn.getAttribute('data-question-id');
            var row = delBtn.closest('[data-question-id]');
            var body = JSON.stringify({ question_id: questionId, csrf_token: delBtn.getAttribute('data-csrf') });
            fetch('/api/job-application-questions.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: body,
                credentials: 'include'
            })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success && row) {
                        row.remove();
                        if (appQuestionsList && appQuestionsList.querySelectorAll('[data-question-id]').length === 0 && appQuestionsEmpty) {
                            appQuestionsEmpty.style.display = 'block';
                        }
                    } else {
                        alert(result.error || 'Failed to delete');
                    }
                })
                .catch(function() { alert('Failed to delete. Please try again.'); });
        }
    });
    
    function displayKeywords(keywords, container, button, selectedKeywords) {
        var deleteBtn = document.getElementById('delete-keywords-btn');
        if (!container || !Array.isArray(keywords) || keywords.length === 0) {
            container.innerHTML = '<div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"><svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg><p class="text-sm text-gray-600 mb-2">Click "Extract Keywords" to identify important keywords from this job description</p><p class="text-xs text-gray-500">These keywords can help you tailor your CV and cover letter for this application</p></div>';
            button.disabled = false;
            button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Extract Keywords';
            if (deleteBtn) deleteBtn.classList.add('hidden');
            return;
        }
        
        selectedKeywords = selectedKeywords || [];
        var applicationId = extractBtn.getAttribute('data-application-id');
        var csrf = extractBtn.getAttribute('data-csrf');
        
        var html = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">';
        html += '<p class="text-xs font-medium text-blue-900 mb-2">Select keywords to include when generating your CV for this role:</p>';
        html += '<div class="flex flex-wrap gap-2" id="keywords-list">';
        
        keywords.forEach(function(keyword) {
            var isSelected = selectedKeywords.indexOf(keyword) !== -1;
            html += '<label class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border cursor-pointer transition-all ' + (isSelected ? 'bg-green-100 text-green-800 border-green-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50') + '">';
            html += '<input type="checkbox" class="sr-only keyword-checkbox" value="' + escapeHtml(keyword) + '" data-keyword="' + escapeHtml(keyword) + '"' + (isSelected ? ' checked' : '') + '>';
            if (isSelected) {
                html += '<svg class="w-4 h-4 mr-1.5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>';
            }
            html += '<span>' + escapeHtml(keyword) + '</span>';
            html += '</label>';
        });
        
        html += '</div>';
        html += '<p class="mt-3 text-sm font-medium text-gray-800">Selected keywords will be used to tailor your CV when you generate a CV variant for this job.</p>';
        html += '</div>';
        
        container.innerHTML = html;
        button.disabled = false;
        button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Re-extract Keywords';
        if (deleteBtn) deleteBtn.classList.remove('hidden');
        
        // Attach event listeners to checkboxes
        var checkboxes = container.querySelectorAll('.keyword-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                saveSelectedKeywords(applicationId, csrf, container);
            });
        });
    }
    
    function saveSelectedKeywords(applicationId, csrf, container) {
        var checkboxes = container.querySelectorAll('.keyword-checkbox');
        var selectedKeywords = [];
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                selectedKeywords.push(checkbox.value);
            }
        });
        
        var formData = new FormData();
        formData.append('<?php echo CSRF_TOKEN_NAME; ?>', csrf);
        formData.append('application_id', applicationId);
        formData.append('selected_keywords', JSON.stringify(selectedKeywords));
        
        fetch('/api/save-selected-keywords.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            if (result.success) {
                // Update visual state of checkboxes
                var keywordsList = container.querySelector('#keywords-list');
                if (keywordsList) {
                    checkboxes.forEach(function(checkbox) {
                        var label = checkbox.closest('label');
                        if (checkbox.checked) {
                            label.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border cursor-pointer transition-all bg-green-100 text-green-800 border-green-300';
                            if (!label.querySelector('svg')) {
                                var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                                svg.setAttribute('class', 'w-4 h-4 mr-1.5 text-green-600');
                                svg.setAttribute('fill', 'currentColor');
                                svg.setAttribute('viewBox', '0 0 20 20');
                                svg.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />';
                                label.insertBefore(svg, label.firstChild);
                            }
                        } else {
                            label.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border cursor-pointer transition-all bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
                            var svg = label.querySelector('svg');
                            if (svg) svg.remove();
                        }
                    });
                }
            }
        })
        .catch(function(error) {
            console.error('Error saving selected keywords:', error);
        });
    }
    
    async function executeBrowserAIKeywordExtraction(result, container, button, csrf, applicationId) {
        try {
            // Check if BrowserAIService is available
            if (typeof BrowserAIService === 'undefined') {
                throw new Error('Browser AI service not available. Please refresh the page.');
            }
            
            const support = BrowserAIService.checkBrowserSupport();
            if (!support.required) {
                throw new Error('Browser does not support WebGPU or WebGL. Browser AI requires a modern browser with GPU support.');
            }
            
            if (container) {
                container.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div><p class="text-sm text-blue-800">Loading AI model. This may take a few minutes on first use...</p></div>';
            }
            
            const modelType = result.model_type === 'webllm' ? 'webllm' : 'tensorflow';
            await BrowserAIService.initBrowserAI(modelType, result.model, (progress) => {
                if (container && progress && progress.message) {
                    const p = container.querySelector('p');
                    if (p) p.textContent = progress.message;
                }
            });
            
            const prompt = result.prompt || '';
            if (!prompt) {
                throw new Error('No prompt provided');
            }
            
            if (container) {
                const p = container.querySelector('p');
                if (p) p.textContent = 'Extracting keywords... This may take 30-60 seconds.';
            }
            
            const responseText = await BrowserAIService.generateText(prompt, {
                temperature: 0.2,
                maxTokens: 800
            });
            
            let keywords;
            try {
                keywords = JSON.parse(responseText);
            } catch (e) {
                const jsonMatch = responseText.match(/\[[\s\S]*\]/);
                if (jsonMatch) {
                    keywords = JSON.parse(jsonMatch[0]);
                } else {
                    throw new Error('Failed to parse AI response as JSON');
                }
            }
            
            await BrowserAIService.cleanup();
            
            // Clean and deduplicate keywords
            if (!Array.isArray(keywords)) {
                keywords = [];
            }
            keywords = keywords.map(function(k) { return String(k).trim(); }).filter(function(k) { return k.length > 2; });
            keywords = Array.from(new Set(keywords));
            
            // Save keywords to database
            if (applicationId) {
                var saveFormData = new FormData();
                saveFormData.append('<?php echo CSRF_TOKEN_NAME; ?>', csrf);
                saveFormData.append('application_id', applicationId);
                saveFormData.append('extracted_keywords', JSON.stringify(keywords));
                
                await fetch('/api/save-extracted-keywords.php', {
                    method: 'POST',
                    body: saveFormData,
                    credentials: 'include'
                });
            }
            
            // Get current selected keywords from the page
            var currentSelected = [];
            var existingCheckboxes = container.querySelectorAll('.keyword-checkbox');
            existingCheckboxes.forEach(function(cb) {
                if (cb.checked) currentSelected.push(cb.value);
            });
            
            displayKeywords(keywords, container, button, currentSelected);
        } catch (error) {
            console.error('Browser AI execution error:', error);
            if (container) {
                container.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-sm text-red-800">Error: ' + error.message + '</p></div>';
            }
            button.disabled = false;
            button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Extract Keywords';
        }
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize sticky navigation
    function initJobViewNavigation() {
        var navLinks = document.querySelectorAll('.job-nav-link');
        var sections = document.querySelectorAll('[id^="job-"], #keywords, #notes, #application-link, #application-questions, #cover-letter, #cover-letter-actions');
        
        // Ensure nav structure is correct
        var nav = document.getElementById('job-view-nav');
        var heading = document.getElementById('nav-heading');
        var menuList = document.getElementById('nav-menu-list');
        
        if (!nav || !heading || !menuList) {
            return;
        }
        
        // Smooth scroll on nav link click
        navLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('href').substring(1);
                var target = document.getElementById(targetId);
                if (target) {
                    var offset = 80; // Account for any sticky headers
                    var targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Make sidebar truly sticky using JavaScript (since CSS sticky doesn't work with overflow containers)
        var sidebar = document.querySelector('[data-jobs-view-container] aside');
        var mainContent = document.getElementById('main-content');
        var nav = document.getElementById('job-view-nav');
        
        if (sidebar && nav && mainContent) {
            // Calculate top offset: account for main navbar height
            var header = document.querySelector('header');
            var headerHeight = header ? header.offsetHeight : 64; // Default to 64px if not found
            var sidebarTop = headerHeight + 24; // Navbar height + padding
            var bottomPadding = 24; // Bottom padding
            var isSticky = false;
            
            function updateStickySidebar() {
                var container = sidebar.closest('[data-jobs-view-container]');
                if (!container) return;
                
                var containerRect = container.getBoundingClientRect();
                var viewportHeight = window.innerHeight;
                var navHeight = nav.scrollHeight; // Full content height
                var availableHeight = viewportHeight - sidebarTop - bottomPadding;
                
                // Always ensure nav fits in viewport when sticky
                var maxHeight = Math.min(navHeight, availableHeight);
                
                // Check if sidebar should be sticky
                if (containerRect.top <= sidebarTop && containerRect.bottom > sidebarTop + navHeight) {
                    if (!isSticky) {
                        // Get the sidebar's left position relative to viewport
                        var sidebarRect = sidebar.getBoundingClientRect();
                        var heading = nav.querySelector('#nav-heading');
                        var menuList = nav.querySelector('#nav-menu-list');
                        
                        // Measure heading height before making sticky - use getBoundingClientRect for accurate measurement
                        var headingRect = heading ? heading.getBoundingClientRect() : null;
                        var headingHeight = headingRect ? headingRect.height : 50;
                        // Account for nav's border
                        var computedStyle = window.getComputedStyle(nav);
                        var borderTop = parseInt(computedStyle.borderTopWidth) || 0;
                        var borderBottom = parseInt(computedStyle.borderBottomWidth) || 0;
                        var navBorderHeight = borderTop + borderBottom;
                        // Menu gets remaining space after heading
                        var menuMaxHeight = availableHeight - headingHeight;
                        
                        // Calculate actual nav border height
                        var computedStyle = window.getComputedStyle(nav);
                        var borderTop = parseInt(computedStyle.borderTopWidth) || 0;
                        var borderBottom = parseInt(computedStyle.borderBottomWidth) || 0;
                        var actualNavBorderHeight = borderTop + borderBottom;
                        
                        nav.style.position = 'fixed';
                        nav.style.top = sidebarTop + 'px';
                        nav.style.left = sidebarRect.left + 'px';
                        nav.style.width = sidebar.offsetWidth + 'px';
                        // Ensure it's below the header (z-40) but above content
                        nav.style.zIndex = '30'; // Below header (z-40) but above content
                        // Constrain to viewport height - account for borders
                        nav.style.height = (availableHeight + actualNavBorderHeight) + 'px';
                        nav.style.maxHeight = (availableHeight + actualNavBorderHeight) + 'px';
                        nav.style.overflowY = 'hidden'; // Container doesn't scroll, inner ul does
                        nav.style.overflowX = 'hidden';
                        nav.style.display = 'flex';
                        nav.style.flexDirection = 'column';
                        nav.style.zIndex = '30'; // Below header (z-40) but above content
                        nav.style.boxSizing = 'border-box';
                        nav.style.margin = '0';
                        nav.style.padding = '0';
                        
                        // Ensure heading stays visible and menu scrolls
                        if (heading) {
                            heading.style.flexShrink = '0';
                            heading.style.flexGrow = '0';
                            heading.style.overflow = 'visible';
                            heading.style.position = 'relative';
                            heading.style.zIndex = '1';
                        }
                        if (menuList) {
                            menuList.style.flex = '1 1 0%'; // Use 0% instead of auto for better flex behavior
                            menuList.style.minHeight = '0';
                            menuList.style.maxHeight = menuMaxHeight + 'px';
                            menuList.style.overflowY = 'auto';
                            menuList.style.overflowX = 'hidden';
                            menuList.style.position = 'relative';
                        }
                        
                        isSticky = true;
                    } else {
                        // Update position and dimensions in case they changed
                        var sidebarRect = sidebar.getBoundingClientRect();
                        var heading = nav.querySelector('#nav-heading');
                        var menuList = nav.querySelector('#nav-menu-list');
                        var headingRect = heading ? heading.getBoundingClientRect() : null;
                        var headingHeight = headingRect ? headingRect.height : 50;
                        var computedStyle = window.getComputedStyle(nav);
                        var borderTop = parseInt(computedStyle.borderTopWidth) || 0;
                        var borderBottom = parseInt(computedStyle.borderBottomWidth) || 0;
                        var navBorderHeight = borderTop + borderBottom;
                        var menuMaxHeight = availableHeight - headingHeight;
                        
                        nav.style.left = sidebarRect.left + 'px';
                        nav.style.width = sidebar.offsetWidth + 'px';
                        // Always ensure it fits in viewport - account for borders
                        nav.style.height = (availableHeight + navBorderHeight) + 'px';
                        nav.style.maxHeight = (availableHeight + navBorderHeight) + 'px';
                        nav.style.overflowY = 'hidden';
                        nav.style.overflowX = 'hidden';
                        nav.style.display = 'flex';
                        nav.style.flexDirection = 'column';
                        nav.style.zIndex = '30'; // Below header (z-40) but above content
                        
                        // Ensure heading stays visible and menu scrolls
                        if (heading) {
                            heading.style.flexShrink = '0';
                            heading.style.flexGrow = '0';
                            heading.style.overflow = 'visible';
                            heading.style.position = 'relative';
                            heading.style.zIndex = '1';
                        }
                        if (menuList) {
                            menuList.style.flex = '1 1 0%';
                            menuList.style.minHeight = '0';
                            menuList.style.maxHeight = menuMaxHeight + 'px';
                            menuList.style.overflowY = 'auto';
                            menuList.style.overflowX = 'hidden';
                            menuList.style.position = 'relative';
                        }
                    }
                } else {
                    if (isSticky) {
                        var heading = nav.querySelector('#nav-heading');
                        var menuList = nav.querySelector('#nav-menu-list');
                        nav.style.position = '';
                        nav.style.top = '';
                        nav.style.left = '';
                        nav.style.width = '';
                        nav.style.height = '';
                        nav.style.maxHeight = '';
                        nav.style.overflowY = '';
                        nav.style.display = '';
                        nav.style.flexDirection = '';
                        nav.style.zIndex = '';
                        nav.style.boxSizing = '';
                        // Reset top position
                        nav.style.top = '';
                        if (heading) {
                            heading.style.flexShrink = '';
                            heading.style.overflow = '';
                        }
                        if (menuList) {
                            menuList.style.flex = '';
                            menuList.style.minHeight = '';
                            menuList.style.maxHeight = '';
                            menuList.style.overflowY = '';
                            menuList.style.overflowX = '';
                        }
                        isSticky = false;
                    }
                }
            }
            
            // Update on scroll (both window and main-content)
            var ticking = false;
            function handleScroll() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        updateStickySidebar();
                        updateActiveNav();
                        ticking = false;
                    });
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', handleScroll, { passive: true });
            if (mainContent) {
                mainContent.addEventListener('scroll', handleScroll, { passive: true });
            }
            
            // Initial update
            updateStickySidebar();
        }
        
        // Highlight active section on scroll
        function updateActiveNav() {
            // Use main-content scroll if available, otherwise window scroll
            var scrollContainer = mainContent || window;
            var scrollPos = (mainContent ? mainContent.scrollTop : window.scrollY) + 100;
            
            sections.forEach(function(section) {
                var container = section.closest('[data-jobs-view-container]');
                if (!container) return;
                
                var containerTop = container.getBoundingClientRect().top + (mainContent ? mainContent.scrollTop : window.scrollY);
                var sectionTop = containerTop + section.offsetTop - container.offsetTop;
                var sectionBottom = sectionTop + section.offsetHeight;
                var id = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    navLinks.forEach(function(link) {
                        link.classList.remove('bg-blue-50', 'text-blue-700', 'font-medium');
                        link.classList.add('text-gray-700');
                    });
                    var activeLink = document.querySelector('.job-nav-link[href="#' + id + '"]');
                    if (activeLink) {
                        activeLink.classList.add('bg-blue-50', 'text-blue-700', 'font-medium');
                        activeLink.classList.remove('text-gray-700');
                    }
                }
            });
        }
        
        // Initial update
        updateActiveNav();
    }
    
    // Initialize navigation after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initJobViewNavigation);
    } else {
        initJobViewNavigation();
    }
    
    container.addEventListener('click', function(e) {
        var back = e.target.closest('[data-jobs-back]');
        var editLink = e.target.closest('[data-jobs-edit]');
        var del = e.target.closest('[data-jobs-delete]');
        if (back) {
            e.preventDefault();
            window.location.hash = '#jobs';
        } else if (editLink) {
            e.preventDefault();
            var id = editLink.getAttribute('data-edit-id');
            if (id) window.location.hash = '#jobs&edit=' + id;
        } else if (del) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this application?')) return;
            var id = del.getAttribute('data-job-id');
            var csrf = del.getAttribute('data-csrf');
            fetch('/api/job-applications.php?id=' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: csrf }),
                credentials: 'include'
            }).then(function(r) {
                if (r.ok) {
                    window.location.hash = '#jobs';
                } else {
                    alert('Could not delete. Please try again.');
                }
            }).catch(function() {
                alert('Could not delete. Please try again.');
            });
        }
    });
})();
</script>
