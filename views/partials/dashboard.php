<?php
// Get CV sections status
// Note: helpers.php is already loaded when this partial is included
// Use getUserId() directly instead of accessing $user array
$userId = isset($user) && is_array($user) && isset($user['id']) ? $user['id'] : getUserId();

// Define CV sections
$cvSections = [
    ['id' => 'profile', 'name' => 'Personal Profile', 'path' => '/profile.php', 'description' => 'Your basic information and contact details'],
    ['id' => 'professional-summary', 'name' => 'Professional Summary', 'path' => '/content-editor.php#professional-summary', 'description' => 'Overview of your professional background'],
    ['id' => 'work-experience', 'name' => 'Work Experience', 'path' => '/content-editor.php#work-experience', 'description' => 'Your employment history'],
    ['id' => 'education', 'name' => 'Education', 'path' => '/content-editor.php#education', 'description' => 'Your educational qualifications'],
    ['id' => 'skills', 'name' => 'Skills', 'path' => '/content-editor.php#skills', 'description' => 'Your professional skills'],
    ['id' => 'projects', 'name' => 'Projects', 'path' => '/content-editor.php#projects', 'description' => 'Notable projects you\'ve worked on'],
    ['id' => 'certifications', 'name' => 'Certifications', 'path' => '/content-editor.php#certifications', 'description' => 'Professional certifications'],
    ['id' => 'qualification-equivalence', 'name' => 'Professional Qualification Equivalence', 'path' => '/content-editor.php#qualification-equivalence', 'description' => 'Show how international and other qualifications align with local standards'],
    ['id' => 'memberships', 'name' => 'Professional Memberships', 'path' => '/content-editor.php#memberships', 'description' => 'Professional organisations'],
    ['id' => 'interests', 'name' => 'Interests & Activities', 'path' => '/content-editor.php#interests', 'description' => 'Your hobbies and interests'],
];

// Check completion status for each section
foreach ($cvSections as &$section) {
    $count = 0;
    $isComplete = false;

    switch ($section['id']) {
        case 'profile':
            $profile = db()->fetchOne("SELECT id FROM profiles WHERE id = ?", [$userId]);
            $isComplete = !empty($profile);
            $count = $isComplete ? 1 : 0;
            break;
        case 'work-experience':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM work_experience WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'education':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM education WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'skills':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM skills WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'projects':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM projects WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'certifications':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM certifications WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'qualification-equivalence':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM professional_qualification_equivalence WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'memberships':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM professional_memberships WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'interests':
            $count = db()->fetchOne("SELECT COUNT(*) as count FROM interests WHERE profile_id = ?", [$userId])['count'];
            $isComplete = $count > 0;
            break;
        case 'professional-summary':
            $summary = db()->fetchOne("SELECT id FROM professional_summary WHERE profile_id = ?", [$userId]);
            $isComplete = !empty($summary);
            $count = $isComplete ? 1 : 0;
            break;
    }

    $section['count'] = $count;
    $section['isComplete'] = $isComplete;
}
unset($section);
?>

<?php partial('header'); ?>

<div class="py-6">
    <!-- Error/Success Messages -->
    <?php if ($error): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="rounded-md bg-red-50 p-4">
                <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Dashboard Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Welcome to Simple CV Builder</h1>
        <p class="mt-2 text-lg text-gray-600">
            Choose a feature to get started or continue your work.
        </p>
    </div>

    <?php
    // Get subscription context
    if (function_exists('getUserSubscriptionContext')) {
        $subscriptionContext = getUserSubscriptionContext($userId);
    } else {
        require_once __DIR__ . '/../../php/subscriptions.php';
        $subscriptionContext = getUserSubscriptionContext($userId);
    }
    $expiryInfo = formatSubscriptionExpiry($subscriptionContext);
    
    // Get job application stats
    if (function_exists('getJobApplicationStats')) {
        $jobStats = getJobApplicationStats();
    } else {
        require_once __DIR__ . '/../../php/job-applications.php';
        $jobStats = getJobApplicationStats();
    }
    
    // Calculate CV completion percentage
    $completedSections = 0;
    $totalSections = count($cvSections);
    foreach ($cvSections as $section) {
        if ($section['isComplete']) {
            $completedSections++;
        }
    }
    $cvCompletionPercent = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
    
    // Get CV variants count
    if (function_exists('getUserCvVariants')) {
        $cvVariants = getUserCvVariants($userId);
    } else {
        require_once __DIR__ . '/../../php/cv-variants.php';
        $cvVariants = getUserCvVariants($userId);
    }
    $variantCount = count($cvVariants);
    $showNextSteps = ($jobStats['total'] ?? 0) === 0 || $variantCount === 0;
    ?>

    <!-- Subscription Status Widget -->
    <?php if (subscriptionIsPaid($subscriptionContext) || $subscriptionContext['plan'] === 'free'): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="rounded-lg border <?php echo $expiryInfo['status_color'] === 'red' ? 'border-red-300 bg-red-50' : ($expiryInfo['status_color'] === 'yellow' ? 'border-yellow-300 bg-yellow-50' : 'border-blue-200 bg-blue-50'); ?> p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold <?php echo $expiryInfo['status_color'] === 'red' ? 'text-red-900' : ($expiryInfo['status_color'] === 'yellow' ? 'text-yellow-900' : 'text-blue-900'); ?> mb-1">
                        Subscription: <?php echo e(subscriptionPlanLabel($subscriptionContext)); ?>
                    </h2>
                    <p class="text-sm <?php echo $expiryInfo['status_color'] === 'red' ? 'text-red-800' : ($expiryInfo['status_color'] === 'yellow' ? 'text-yellow-800' : 'text-blue-800'); ?>">
                        <?php if ($expiryInfo['days_remaining'] !== null): ?>
                            <?php echo e($expiryInfo['status_text']); ?> — Expires <?php echo e($expiryInfo['formatted_date']); ?>
                        <?php else: ?>
                            <?php echo e($expiryInfo['status_text']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <a href="/subscription.php" class="text-sm font-medium <?php echo $expiryInfo['status_color'] === 'red' ? 'text-red-700 hover:text-red-900' : ($expiryInfo['status_color'] === 'yellow' ? 'text-yellow-700 hover:text-yellow-900' : 'text-blue-700 hover:text-blue-900'); ?> underline">
                    Manage subscription →
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($showNextSteps): ?>
    <!-- Next steps for new users -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
            <h2 class="text-sm font-semibold text-blue-900 mb-1">Getting started</h2>
            <p class="text-sm text-blue-800">
                <?php if (($jobStats['total'] ?? 0) === 0): ?>
                Add your first job application in <a href="/content-editor.php#jobs" class="font-medium underline hover:no-underline">Manage Jobs</a>, then open it and use &ldquo;Generate AI CV for this job&rdquo; or &ldquo;Tailor CV for this job…&rdquo; to create a job-specific CV.
                <?php elseif ($variantCount === 0): ?>
                You have job applications. Open any job in <a href="/content-editor.php#jobs" class="font-medium underline hover:no-underline">Manage Jobs</a> and click &ldquo;Generate AI CV for this job&rdquo; to create a tailored CV.
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Feature Cards Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Build My CV Card -->
            <a href="/content-editor.php" class="group flex flex-col overflow-hidden rounded-xl shadow-lg bg-white border-2 border-transparent hover:border-blue-500 transition-all duration-200 hover:shadow-xl">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-900 group-hover:text-blue-600">Build My CV</h3>
                        </div>
                        <span class="text-sm font-medium text-gray-500"><?php echo $cvCompletionPercent; ?>%</span>
                    </div>
                    <p class="text-gray-600 mb-4">Edit all sections of your CV in one unified workspace with AI guidance.</p>
                    <div class="mt-auto">
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: <?php echo $cvCompletionPercent; ?>%"></div>
                        </div>
                        <span class="inline-flex items-center text-sm font-medium text-blue-600 group-hover:underline">
                            <?php echo $completedSections; ?> of <?php echo $totalSections; ?> sections complete
                            <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            <!-- Manage Jobs Card -->
            <a href="/content-editor.php#jobs" class="group flex flex-col overflow-hidden rounded-xl shadow-lg bg-white border-2 border-transparent hover:border-green-500 transition-all duration-200 hover:shadow-xl">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-900 group-hover:text-green-600">Manage Jobs</h3>
                        </div>
                        <span class="text-sm font-medium text-gray-500"><?php echo $jobStats['total']; ?></span>
                    </div>
                    <p class="text-gray-600 mb-4">Track job applications and generate AI-tailored CVs for each position.</p>
                    <div class="mt-auto">
                        <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                            <span class="flex items-center">
                                <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                                <?php echo $jobStats['applied']; ?> Applied
                            </span>
                            <span class="flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                                <?php echo $jobStats['interviewing']; ?> Interviewing
                            </span>
                        </div>
                        <span class="inline-flex items-center text-sm font-medium text-green-600 group-hover:underline">
                            View all applications
                            <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            <!-- CV Quality Assessment Card -->
            <a href="/cv-quality.php" class="group flex flex-col overflow-hidden rounded-xl shadow-lg bg-white border-2 border-transparent hover:border-purple-500 transition-all duration-200 hover:shadow-xl">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-900 group-hover:text-purple-600">CV Quality Assessment</h3>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">Get AI-powered feedback on your CV with scores and actionable recommendations.</p>
                    <div class="mt-auto">
                        <span class="inline-flex items-center text-sm font-medium text-purple-600 group-hover:underline">
                            Assess your CV now
                            <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            <!-- View All CVs Card -->
            <a href="/content-editor.php#cv-variants" class="group flex flex-col overflow-hidden rounded-xl shadow-lg bg-white border-2 border-transparent hover:border-indigo-500 transition-all duration-200 hover:shadow-xl">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-900 group-hover:text-indigo-600">View All CVs</h3>
                        </div>
                        <span class="text-sm font-medium text-gray-500"><?php echo $variantCount; ?></span>
                    </div>
                    <p class="text-gray-600 mb-4">Manage all your CV variants, including AI-generated job-specific versions.</p>
                    <div class="mt-auto">
                        <span class="inline-flex items-center text-sm font-medium text-indigo-600 group-hover:underline">
                            Manage CVs
                            <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            <!-- Learn Interview Skills Card (Placeholder) -->
            <div class="group flex flex-col overflow-hidden rounded-xl shadow-lg bg-gray-50 border-2 border-gray-200 opacity-75">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-gray-200 rounded-lg">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-500">Learn Interview Skills</h3>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-200 px-2 py-1 rounded">Coming Soon</span>
                    </div>
                    <p class="text-gray-500 mb-4">Master interview techniques and practice with AI-powered feedback.</p>
                </div>
            </div>

            <!-- Learn How to Use AI Card (Placeholder) -->
            <div class="group flex flex-col overflow-hidden rounded-xl shadow-lg bg-gray-50 border-2 border-gray-200 opacity-75">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-gray-200 rounded-lg">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-500">Learn How to Use AI</h3>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-200 px-2 py-1 rounded">Coming Soon</span>
                    </div>
                    <p class="text-gray-500 mb-4">Tutorials and guides to maximize your use of AI-powered CV tools.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php partial('footer'); ?>
