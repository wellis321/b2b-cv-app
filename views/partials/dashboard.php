<?php
// Get CV sections status
// Note: helpers.php is already loaded when this partial is included
// Use getUserId() directly instead of accessing $user array
$userId = isset($user) && is_array($user) && isset($user['id']) ? $user['id'] : getUserId();

// Define CV sections
$cvSections = [
    ['id' => 'profile', 'name' => 'Personal Profile', 'path' => '/profile.php', 'description' => 'Your basic information and contact details'],
    ['id' => 'professional-summary', 'name' => 'Professional Summary', 'path' => '/professional-summary.php', 'description' => 'Overview of your professional background'],
    ['id' => 'work-experience', 'name' => 'Work Experience', 'path' => '/work-experience.php', 'description' => 'Your employment history'],
    ['id' => 'education', 'name' => 'Education', 'path' => '/education.php', 'description' => 'Your educational qualifications'],
    ['id' => 'skills', 'name' => 'Skills', 'path' => '/skills.php', 'description' => 'Your professional skills'],
    ['id' => 'projects', 'name' => 'Projects', 'path' => '/projects.php', 'description' => 'Notable projects you\'ve worked on'],
    ['id' => 'certifications', 'name' => 'Certifications', 'path' => '/certifications.php', 'description' => 'Professional certifications'],
    ['id' => 'qualification-equivalence', 'name' => 'Professional Qualification Equivalence', 'path' => '/qualification-equivalence.php', 'description' => 'Show how international and other qualifications align with local standards'],
    ['id' => 'memberships', 'name' => 'Professional Memberships', 'path' => '/memberships.php', 'description' => 'Professional organisations'],
    ['id' => 'interests', 'name' => 'Interests & Activities', 'path' => '/interests.php', 'description' => 'Your hobbies and interests'],
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
        <h1 class="text-3xl font-bold text-gray-900">Your Simple CV Builder Dashboard</h1>
        <p class="mt-2 text-lg text-gray-600">
            Complete each section to create your professional CV.
        </p>
    </div>

    <!-- AI Features Banner -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">New: AI-Powered CV Tools</h3>
                    <p class="text-sm text-gray-700 mb-3">
                        Generate job-specific CVs automatically and get AI-powered quality feedback. Perfect for tailoring your CV to each job application.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="/cv-variants/rewrite.php" class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate AI CV
                        </a>
                        <a href="/cv-quality.php" class="inline-flex items-center px-3 py-2 border border-purple-600 text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Assess CV Quality
                        </a>
<?php /* Template customizer temporarily hidden
                        <a href="/cv-template-customizer.php" class="inline-flex items-center px-3 py-2 border border-purple-600 text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            Customise Template
                        </a>
*/ ?>
                        <a href="/cv-variants.php" class="inline-flex items-center px-3 py-2 text-purple-600 text-sm font-medium hover:text-purple-700 transition-colors">
                            Manage CV Variants →
                        </a>
                        <a href="/resources/ai/setup-ollama.php" class="inline-flex items-center px-3 py-2 text-purple-600 text-sm font-medium hover:text-purple-700 transition-colors">
                            Setup Local AI →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CV Sections Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($cvSections as $section): ?>
                <a href="<?php echo e($section['path']); ?>" class="group flex flex-col overflow-hidden rounded-lg shadow-lg transition-all duration-200 hover:bg-gray-50 hover:shadow-xl">
                    <div class="flex flex-1 flex-col justify-between bg-white p-6 group-hover:bg-gray-50">
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <p class="text-xl font-semibold text-gray-900 group-hover:text-blue-600">
                                    <?php echo e($section['name']); ?>
                                </p>
                                <span class="text-lg font-bold <?php echo $section['isComplete'] ? 'text-green-500' : 'text-gray-300'; ?>" title="<?php echo $section['isComplete'] ? $section['count'] . ' item(s)' : 'Not started'; ?>">
                                    <?php echo $section['isComplete'] ? '●' : '○'; ?>
                                </span>
                            </div>
                            <p class="mt-3 text-base text-gray-500"><?php echo e($section['description']); ?></p>
                            <div class="mt-4">
                                <?php if ($section['isComplete']): ?>
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        <?php echo $section['count']; ?> entr<?php echo $section['count'] !== 1 ? 'ies' : 'y'; ?> added
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center text-sm font-medium text-blue-600 group-hover:underline">
                                        Add information
                                        <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php partial('footer'); ?>
