<?php
/**
 * CV Quality Assessment Page
 * Display AI-generated CV quality assessment results
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

$variantId = $_GET['variant_id'] ?? null;
$jobApplicationId = $_GET['job_application_id'] ?? null;

// Get latest assessment or trigger new one
$assessment = null;
$cvVariant = null;

if ($variantId) {
    $cvVariant = getCvVariant($variantId, $user['id']);
    if (!$cvVariant) {
        setFlash('error', 'CV variant not found.');
        redirect('/cv-variants.php');
    }
    
    // Get latest assessment
    $assessment = db()->fetchOne(
        "SELECT * FROM cv_quality_assessments 
         WHERE cv_variant_id = ? AND user_id = ?
         ORDER BY created_at DESC 
         LIMIT 1",
        [$variantId, $user['id']]
    );
    
    if ($assessment) {
        $assessment['recommendations'] = json_decode($assessment['recommendations'] ?? '[]', true);
        $assessment['strengths'] = json_decode($assessment['strengths'] ?? '[]', true);
        $assessment['weaknesses'] = json_decode($assessment['weaknesses'] ?? '[]', true);
        $assessment['enhanced_recommendations'] = json_decode($assessment['enhanced_recommendations'] ?? '[]', true);
    }
} else {
    // No variant specified - use master CV
    $masterVariantId = getOrCreateMasterVariant($user['id']);
    if ($masterVariantId) {
        $cvVariant = getCvVariant($masterVariantId);
        $variantId = $masterVariantId;
        
        // Get latest assessment for master
        $assessment = db()->fetchOne(
            "SELECT * FROM cv_quality_assessments 
             WHERE cv_variant_id = ? AND user_id = ?
             ORDER BY created_at DESC 
             LIMIT 1",
            [$masterVariantId, $user['id']]
        );
        
        if ($assessment) {
            $assessment['recommendations'] = json_decode($assessment['recommendations'] ?? '[]', true);
            $assessment['strengths'] = json_decode($assessment['strengths'] ?? '[]', true);
            $assessment['weaknesses'] = json_decode($assessment['weaknesses'] ?? '[]', true);
        // Decode enhanced_recommendations (column may not exist in older assessments)
        $enhancedRecs = $assessment['enhanced_recommendations'] ?? null;
        if ($enhancedRecs !== null) {
            $decoded = json_decode($enhancedRecs, true);
            $assessment['enhanced_recommendations'] = is_array($decoded) ? $decoded : [];
        } else {
            $assessment['enhanced_recommendations'] = [];
        }
        }
    }
}

// Handle new assessment request
if (isPost() && isset($_POST['action']) && $_POST['action'] === 'assess') {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/cv-quality.php?variant_id=' . $variantId);
    }
    
    // Redirect will happen after AJAX call completes
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'CV Quality Assessment | Simple CV Builder',
        'metaDescription' => 'Get AI-powered feedback on your CV quality.',
        'canonicalUrl' => APP_URL . '/cv-quality.php',
        'metaNoindex' => true,
    ]); ?>
    <style>
        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            position: relative;
        }
        .score-excellent { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .score-good { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
        .score-fair { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .score-poor { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        
        .score-label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">CV Quality Assessment</h1>
                        <p class="mt-1 text-sm text-gray-500">Get AI-powered feedback on your CV quality</p>
                    </div>
                    <div class="flex space-x-3">
                        <?php if ($variantId): ?>
                            <a href="/cv.php?variant_id=<?php echo e($variantId); ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                View CV
                            </a>
                        <?php endif; ?>
                        <button onclick="runAssessment()" 
                                id="assess-btn"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span id="assess-text">Run Assessment</span>
                            <span id="assess-loading" class="hidden">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Assessing...
                            </span>
                        </button>
                    </div>
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
                                <strong>Cost Notice:</strong> You're using a paid AI service. This assessment will incur API costs. 
                                <a href="/ai-settings.php" class="underline font-semibold">Switch to free options (Local Ollama or Browser-Based AI)</a> to avoid charges.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- CV Selection -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700">Select CV to Assess</label>
                        <a href="/resources/ai/setup-ollama.php" class="text-xs text-purple-600 hover:text-purple-700 underline">
                            Setup Local AI
                        </a>
                    </div>
                    <select id="variant-select" 
                            onchange="window.location.href='/cv-quality.php?variant_id=' + (this.value || '')"
                            class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Master CV (Default)</option>
                        <?php 
                        $allVariants = getUserCvVariants($user['id']);
                        foreach ($allVariants as $v): 
                            $selected = ($variantId && $variantId === $v['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo e($v['id']); ?>" <?php echo $selected; ?>>
                                <?php echo e($v['variant_name']); ?>
                                <?php if ($v['is_master']): ?> (Master)<?php endif; ?>
                                <?php if ($v['ai_generated']): ?> [AI]<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        <?php if ($cvVariant): ?>
                            Currently assessing: <strong><?php echo e($cvVariant['variant_name']); ?></strong>
                            <?php if ($cvVariant['is_master']): ?>
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Master CV</span>
                            <?php endif; ?>
                        <?php else: ?>
                            Assessing your <strong>Master CV</strong> (your main CV with all sections)
                        <?php endif; ?>
                    </p>
                </div>
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

            <?php if ($assessment): ?>
                <!-- Assessment Results -->
                <div class="space-y-6">
                    <!-- Overall Score -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Overall Score</h2>
                        <div class="flex items-center justify-center">
                            <div class="text-center">
                                <div class="score-circle <?php 
                                    $score = $assessment['overall_score'];
                                    if ($score >= 80) echo 'score-excellent';
                                    elseif ($score >= 60) echo 'score-good';
                                    elseif ($score >= 40) echo 'score-fair';
                                    else echo 'score-poor';
                                ?>">
                                    <?php echo $score; ?>
                                </div>
                                <div class="score-label text-gray-600">Out of 100</div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Explanation (Collapsible) -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <button onclick="toggleExplanation('assessment-explanation')" class="w-full flex items-center justify-between text-left">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                How This Assessment Works
                            </h2>
                            <svg id="assessment-explanation-icon" class="w-5 h-5 text-blue-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="assessment-explanation" class="hidden mt-4">
                            <p class="text-gray-700 mb-4">
                                This AI-powered assessment analyzes your CV across multiple dimensions to help you improve its quality and effectiveness. The assessment evaluates your CV's structure, content, formatting, and alignment with job requirements (if a job description was provided).
                            </p>
                            <div class="mt-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Score Ranges:</h3>
                                <ul class="space-y-1 text-sm text-gray-700">
                                    <li class="flex items-center">
                                        <span class="inline-block w-16 text-right mr-3 font-medium text-green-700">80-100:</span>
                                        <span>Excellent - Your CV performs very well in this area</span>
                                    </li>
                                    <li class="flex items-center">
                                        <span class="inline-block w-16 text-right mr-3 font-medium text-blue-700">60-79:</span>
                                        <span>Good - Solid performance with room for minor improvements</span>
                                    </li>
                                    <li class="flex items-center">
                                        <span class="inline-block w-16 text-right mr-3 font-medium text-yellow-700">40-59:</span>
                                        <span>Fair - Needs improvement to be competitive</span>
                                    </li>
                                    <li class="flex items-center">
                                        <span class="inline-block w-16 text-right mr-3 font-medium text-red-700">0-39:</span>
                                        <span>Poor - Significant improvements required</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Scores -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">Detailed Scores</h2>
                            <button onclick="toggleExplanation('scores-explanation')" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors" title="Learn more about these scores">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium">What do these scores mean?</span>
                                <svg id="scores-explanation-icon" class="w-4 h-4 ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $assessment['ats_score']; ?></div>
                                <div class="text-sm font-medium text-gray-900 mb-2">ATS Compatibility</div>
                                <div class="text-xs text-gray-600">Keywords, structure, and content parsing</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $assessment['content_score']; ?></div>
                                <div class="text-sm font-medium text-gray-900 mb-2">Content Quality</div>
                                <div class="text-xs text-gray-600">Relevance, impact, and specificity</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $assessment['formatting_score']; ?></div>
                                <div class="text-sm font-medium text-gray-900 mb-2">Content Consistency</div>
                                <div class="text-xs text-gray-600">Date formatting and completeness</div>
                            </div>
                            <?php if ($assessment['keyword_match_score'] !== null): ?>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $assessment['keyword_match_score']; ?></div>
                                    <div class="text-sm font-medium text-gray-900 mb-2">Keyword Match</div>
                                    <div class="text-xs text-gray-600">Alignment with the job description requirements</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Score Explanations (Collapsible) -->
                        <div id="scores-explanation" class="hidden border-t border-gray-200 pt-4 mt-4">
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <strong>Note:</strong> These scores focus on aspects you can control through your content. Visual formatting (fonts, colors, spacing) is handled by your CV template and doesn't affect your scores. Focus on improving your content quality, consistency, and keyword usage.
                                </p>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-3">What Each Score Means:</h3>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">ATS Compatibility (<?php echo $assessment['ats_score']; ?>/100)</h4>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Measures how well Applicant Tracking Systems can read and parse your CV content. Focuses on aspects you can control through your content.
                                    </p>
                                    <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                                        <li>Use of relevant keywords throughout your content</li>
                                        <li>Clear section structure and headings</li>
                                        <li>Complete and consistent information</li>
                                        <li>How well your content can be automatically parsed</li>
                                    </ul>
                                    <p class="text-xs text-gray-500 mt-2 italic">Note: Visual formatting is handled by your CV template and doesn't affect this score.</p>
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">Content Quality (<?php echo $assessment['content_score']; ?>/100)</h4>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Evaluates the substance and impact of your CV content, including how well you present your achievements and experiences.
                                    </p>
                                    <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                                        <li>Use of quantifiable achievements and metrics</li>
                                        <li>Relevance of content to your target role</li>
                                        <li>Clarity and specificity of descriptions</li>
                                        <li>Demonstration of impact and results</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">Content Consistency (<?php echo $assessment['formatting_score']; ?>/100)</h4>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Assesses consistency and completeness of your CV content that you control.
                                    </p>
                                    <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                                        <li>Consistent date formatting across all entries</li>
                                        <li>Complete information in all sections</li>
                                        <li>Consistent description style and detail level</li>
                                        <li>No missing dates or unexplained gaps</li>
                                    </ul>
                                    <p class="text-xs text-gray-500 mt-2 italic">Note: Visual formatting (fonts, colors, spacing) is handled by your CV template.</p>
                                </div>
                                
                                <?php if ($assessment['keyword_match_score'] !== null): ?>
                                    <div>
                                        <h4 class="font-medium text-gray-900 mb-1">Keyword Match (<?php echo $assessment['keyword_match_score']; ?>/100)</h4>
                                        <p class="text-sm text-gray-700 mb-2">
                                            Measures how well your CV aligns with the specific job description you provided (if applicable).
                                        </p>
                                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                                            <li>Inclusion of relevant keywords from the job description</li>
                                            <li>Matching skills and qualifications</li>
                                            <li>Alignment with job requirements</li>
                                            <li>Relevance of experience to the role</li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Strengths -->
                    <?php if (!empty($assessment['strengths'])): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Strengths</h2>
                            <ul class="space-y-2">
                                <?php foreach ($assessment['strengths'] as $strength): ?>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700"><?php echo e($strength); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Weaknesses -->
                    <?php if (!empty($assessment['weaknesses'])): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Areas for Improvement</h2>
                            <ul class="space-y-2">
                                <?php foreach ($assessment['weaknesses'] as $weakness): ?>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700"><?php echo e($weakness); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Enhanced Recommendations -->
                    <?php if (!empty($assessment['enhanced_recommendations'])): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Actionable Recommendations</h2>
                            <div class="space-y-4">
                                <?php foreach ($assessment['enhanced_recommendations'] as $index => $rec): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1"><?php echo e($rec['issue'] ?? 'Improvement needed'); ?></h3>
                                                <p class="text-gray-700 text-sm mb-3"><?php echo e($rec['suggestion'] ?? ''); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($rec['examples'])): ?>
                                            <div class="mb-3">
                                                <p class="text-xs font-medium text-gray-600 mb-2">Examples:</p>
                                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 ml-2">
                                                    <?php foreach ($rec['examples'] as $example): ?>
                                                        <li><?php echo e($example); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        // Only show AI improvement if it's not placeholder text
                                        $aiImprovement = $rec['ai_generated_improvement'] ?? null;
                                        if ($aiImprovement) {
                                            // Additional client-side check for placeholder patterns
                                            $placeholderPatterns = [
                                                '/\[Improved.*?\]/i',
                                                '/\[.*?based on.*?CV.*?\]/i',
                                                '/\[.*?text.*?\]/i',
                                                '/placeholder/i',
                                            ];
                                            $isPlaceholder = false;
                                            foreach ($placeholderPatterns as $pattern) {
                                                if (preg_match($pattern, $aiImprovement)) {
                                                    $isPlaceholder = true;
                                                    break;
                                                }
                                            }
                                            
                                            // Also check if it's too short (likely a placeholder)
                                            if (!$isPlaceholder && strlen(trim($aiImprovement)) < 50) {
                                                $isPlaceholder = true;
                                            }
                                            
                                            if (!$isPlaceholder):
                                        ?>
                                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <p class="text-xs font-medium text-blue-900 mb-2">AI-Generated Improvement:</p>
                                                <div class="text-sm text-gray-800 bg-white p-3 rounded border border-blue-100 mb-3">
                                                    <?php echo nl2br(e($aiImprovement)); ?>
                                                </div>
                                                <?php if ($rec['can_apply'] ?? false): ?>
                                                    <button onclick="applyImprovement(<?php echo $index; ?>, '<?php echo e($rec['improvement_type'] ?? ''); ?>')" 
                                                            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Apply This Improvement
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php 
                                            endif; // !$isPlaceholder
                                        } 
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif (!empty($assessment['recommendations'])): ?>
                        <!-- Fallback to basic recommendations if enhanced ones aren't available -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Recommendations</h2>
                            <ul class="space-y-3">
                                <?php foreach ($assessment['recommendations'] as $recommendation): ?>
                                    <li class="flex items-start p-3 bg-gray-50 rounded-lg">
                                        <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700"><?php echo e($recommendation); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Assessment Date -->
                    <div class="text-sm text-gray-500 text-center">
                        Assessment generated on <?php echo date('F j, Y \a\t g:i A', strtotime($assessment['created_at'])); ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Assessment Yet -->
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No assessment yet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php if ($cvVariant): ?>
                            Click "Run Assessment" to get AI-powered feedback on <strong><?php echo e($cvVariant['variant_name']); ?></strong>.
                        <?php else: ?>
                            Click "Run Assessment" to get AI-powered feedback on your Master CV.
                        <?php endif; ?>
                        You can assess any CV variant using the selector above.
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Note: Assessment may take 30-60 seconds depending on your AI service configuration.</p>
                    <div class="mt-6">
                        <button onclick="runAssessment()" 
                                id="assess-btn-empty"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <span id="assess-text-empty">Run Assessment</span>
                            <span id="assess-loading-empty" class="hidden">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Assessing...
                            </span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php partial('footer'); ?>
    
    <!-- Browser AI Service Scripts -->
    <script src="/js/model-cache-manager.js"></script>
    <script src="/js/browser-ai-service.js"></script>
    
    <script>
        // Store enhanced recommendations data for apply functionality
        const enhancedRecommendations = <?php echo json_encode($assessment['enhanced_recommendations'] ?? []); ?>;
        
        // Toggle explanation sections
        function toggleExplanation(id) {
            const element = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                if (icon) {
                    icon.classList.add('rotate-180');
                }
            } else {
                element.classList.add('hidden');
                if (icon) {
                    icon.classList.remove('rotate-180');
                }
            }
        }
        
        async function applyImprovement(index, improvementType) {
            const rec = enhancedRecommendations[index];
            if (!rec || !rec.ai_generated_improvement) {
                alert('No improvement available to apply.');
                return;
            }
            
            if (!confirm('This will update your CV with the AI-generated improvement. Continue?')) {
                return;
            }
            
            // Show loading
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Applying...';
            
            try {
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('improvement_type', improvementType);
                formData.append('improvement_content', rec.ai_generated_improvement);
                formData.append('cv_variant_id', '<?php echo e($variantId ?? ''); ?>');
                
                const response = await fetch('/api/apply-cv-improvement.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Improvement applied successfully! Redirecting to your CV...');
                    window.location.href = '/cv.php<?php echo $variantId ? '?variant_id=' . e($variantId) : ''; ?>';
                } else {
                    alert('Error: ' + (result.error || 'Failed to apply improvement'));
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            } catch (error) {
                alert('Error: ' + error.message);
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }
    </script>

    <script>
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
                    if (data.is_paid) {
                        const warning = document.getElementById('cost-warning');
                        if (warning) warning.classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Failed to check AI service:', error);
            }
        })();

        async function runAssessment() {
            // Handle both button instances (one in header, one in empty state)
            const assessBtn = document.getElementById('assess-btn') || document.getElementById('assess-btn-empty');
            const assessText = document.getElementById('assess-text') || document.getElementById('assess-text-empty');
            const assessLoading = document.getElementById('assess-loading') || document.getElementById('assess-loading-empty');
            
            if (!assessBtn) {
                console.error('Assessment button not found');
                return;
            }
            
            assessBtn.disabled = true;
            if (assessText) assessText.classList.add('hidden');
            if (assessLoading) assessLoading.classList.remove('hidden');
            
            // Show loading overlay
            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'assessment-loading-overlay';
            loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            loadingOverlay.innerHTML = `
                <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
                    <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Assessing Your CV</h3>
                    <p class="text-sm text-gray-600 mb-4">This may take 30-60 seconds. Please wait...</p>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
            
            const formData = new FormData();
            formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
            formData.append('action', 'assess');
            <?php if ($variantId): ?>
                formData.append('cv_variant_id', '<?php echo e($variantId); ?>');
            <?php endif; ?>
            <?php if ($jobApplicationId): ?>
                formData.append('job_application_id', '<?php echo e($jobApplicationId); ?>');
            <?php endif; ?>
            
            try {
                // Create AbortController for timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 180000); // 3 minute timeout
                
                const response = await fetch('/api/ai-assess-cv.php', {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { error: errorText || 'Server error' };
                    }
                    throw new Error(errorData.error || 'Assessment failed');
                }
                
                const result = await response.json();
                
                // Check if this is browser AI execution
                if (result.success && result.browser_execution) {
                    // Browser AI mode - execute client-side
                    await executeBrowserAI(result, loadingOverlay);
                    return;
                }
                
                // Remove loading overlay
                loadingOverlay.remove();
                
                if (result.success) {
                    // Reload page to show new assessment, preserving variant_id if present
                    const urlParams = new URLSearchParams(window.location.search);
                    const variantId = urlParams.get('variant_id');
                    if (variantId) {
                        window.location.href = '/cv-quality.php?variant_id=' + variantId;
                    } else {
                        window.location.reload();
                    }
                } else {
                    throw new Error(result.error || 'Failed to assess CV');
                }
            } catch (error) {
                // Remove loading overlay
                if (loadingOverlay && loadingOverlay.parentNode) {
                    loadingOverlay.remove();
                }
                
                console.error('Error:', error);
                
                let errorMessage = 'An error occurred. Please try again.';
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timed out. The assessment is taking longer than expected. Please try again or check if Ollama is running properly.';
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                alert('Error: ' + errorMessage);
                
                if (assessBtn) assessBtn.disabled = false;
                if (assessText) assessText.classList.remove('hidden');
                if (assessLoading) assessLoading.classList.add('hidden');
            }
        }

        // Execute browser AI for CV assessment
        async function executeBrowserAI(result, loadingOverlay) {
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

                // Use prompt from backend if available, otherwise build one
                let prompt = result.prompt || '';
                if (!prompt) {
                    // Fallback: build assessment prompt from CV data
                    const cvData = result.cv_data || {};
                    const jobDescription = result.job_description || '';
                    prompt = `Assess this CV for quality and provide scores and recommendations. CV data: ${JSON.stringify(cvData)}. Job description: ${jobDescription}`;
                }

                // Update loading overlay
                if (loadingOverlay) {
                    loadingOverlay.querySelector('p').textContent = 'Assessing CV... This may take 30-60 seconds.';
                }

                // Generate assessment using browser AI
                const assessmentText = await BrowserAIService.generateText(prompt, {
                    temperature: 0.3,
                    maxTokens: 2000
                });

                // Parse assessment JSON
                let assessment;
                try {
                    assessment = JSON.parse(assessmentText);
                } catch (e) {
                    // If JSON parsing fails, try to extract JSON from markdown
                    const jsonMatch = assessmentText.match(/\{[\s\S]*\}/);
                    if (jsonMatch) {
                        assessment = JSON.parse(jsonMatch[0]);
                    } else {
                        throw new Error('Failed to parse AI response as JSON');
                    }
                }

                // Send assessment to server to save
                const formData = new FormData();
                formData.append('<?php echo CSRF_TOKEN_NAME; ?>', '<?php echo generateCsrfToken(); ?>');
                formData.append('action', 'assess');
                formData.append('browser_ai_result', JSON.stringify(assessment));
                <?php if ($variantId): ?>
                    formData.append('cv_variant_id', '<?php echo e($variantId); ?>');
                <?php endif; ?>

                const saveResponse = await fetch('/api/ai-assess-cv.php', {
                    method: 'POST',
                    body: formData
                });

                const saveResult = await saveResponse.json();

                // Cleanup
                await BrowserAIService.cleanup();
                if (loadingOverlay) loadingOverlay.remove();

                if (saveResult.success) {
                    // Reload page to show new assessment
                    const urlParams = new URLSearchParams(window.location.search);
                    const variantId = urlParams.get('variant_id');
                    if (variantId) {
                        window.location.href = '/cv-quality.php?variant_id=' + variantId;
                    } else {
                        window.location.reload();
                    }
                } else {
                    throw new Error(saveResult.error || 'Failed to save assessment');
                }
            } catch (error) {
                console.error('Browser AI execution error:', error);
                if (loadingOverlay) loadingOverlay.remove();
                
                const assessBtn = document.getElementById('assess-btn') || document.getElementById('assess-btn-empty');
                const assessText = document.getElementById('assess-text') || document.getElementById('assess-text-empty');
                const assessLoading = document.getElementById('assess-loading') || document.getElementById('assess-loading-empty');
                
                if (assessBtn) assessBtn.disabled = false;
                if (assessText) assessText.classList.remove('hidden');
                if (assessLoading) assessLoading.classList.add('hidden');
                
                alert('Browser AI Error: ' + error.message);
            }
        }
    </script>
</body>
</html>

