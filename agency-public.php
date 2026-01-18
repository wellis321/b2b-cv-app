<?php
/**
 * Organisation Public Page
 * Public-facing landing page for recruitment agencies
 * Accessible at /agency/{slug}
 */

require_once __DIR__ . '/php/helpers.php';

// Get slug from query parameter (set by routing)
$slug = get('slug', '');

if (empty($slug)) {
    // If no slug, redirect to general organisations page
    redirect('/organisations.php');
}

// Get organisation by slug
$organisation = getOrganisationBySlug($slug);

if (!$organisation) {
    // Organisation not found
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$pageTitle = e($organisation['name']) . ' | Simple CV Builder';
$metaDescription = e($organisation['name']) . ' uses Simple CV Builder to manage candidate CVs efficiently.';
$canonicalUrl = APP_URL . '/agency/' . e($organisation['slug']);

// Get organisation branding
$primaryColour = $organisation['primary_colour'] ?? '#4338ca';
$secondaryColour = $organisation['secondary_colour'] ?? '#7e22ce';
$logoUrl = $organisation['logo_url'] ?? null;

// Get organisation stats (public data only)
$candidateCount = db()->fetchOne(
    "SELECT COUNT(*) as count FROM profiles WHERE organisation_id = ? AND account_type = 'candidate'",
    [$organisation['id']]
)['count'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle,
        'metaDescription' => $metaDescription,
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
    <style>
        :root {
            --org-primary: <?php echo e($primaryColour); ?>;
            --org-secondary: <?php echo e($secondaryColour); ?>;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" role="main">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r" style="background: linear-gradient(to right, <?php echo e($primaryColour); ?>, <?php echo e($secondaryColour); ?>);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <?php if ($logoUrl): ?>
                        <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($organisation['name']); ?>" class="h-20 mx-auto mb-6 object-contain">
                    <?php endif; ?>
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl text-white">
                        <?php echo e($organisation['name']); ?>
                    </h1>
                    <p class="mt-4 text-xl text-white/90 max-w-3xl mx-auto">
                        Professional CV Management for Recruitment Agencies
                    </p>
                </div>
            </div>
        </div>

        <!-- Organisation Info Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">About Our Organisation</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold" style="color: <?php echo e($primaryColour); ?>;">
                            <?php echo number_format($candidateCount); ?>
                        </div>
                        <div class="text-sm text-gray-600 mt-2">Candidates Managed</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold" style="color: <?php echo e($primaryColour); ?>;">
                            <svg class="w-8 h-8 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="text-sm text-gray-600 mt-2">Professional CVs</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold" style="color: <?php echo e($primaryColour); ?>;">
                            <svg class="w-8 h-8 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                            </svg>
                        </div>
                        <div class="text-sm text-gray-600 mt-2">Recruitment Excellence</div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">How We Help Our Candidates</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: <?php echo e($primaryColour); ?>20;">
                            <svg class="w-6 h-6" style="color: <?php echo e($primaryColour); ?>;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Professional CV Management</h3>
                        <p class="text-gray-600 text-sm">We help our candidates create and maintain professional, up-to-date CVs that stand out to employers.</p>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: <?php echo e($primaryColour); ?>20;">
                            <svg class="w-6 h-6" style="color: <?php echo e($primaryColour); ?>;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Real-Time Updates</h3>
                        <p class="text-gray-600 text-sm">Candidates can update their CVs instantly, ensuring employers always see the latest information.</p>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: <?php echo e($primaryColour); ?>20;">
                            <svg class="w-6 h-6" style="color: <?php echo e($primaryColour); ?>;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Quality Assurance</h3>
                        <p class="text-gray-600 text-sm">Our team reviews and optimises CVs to ensure they meet the highest standards for job applications.</p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-gradient-to-r rounded-lg shadow-lg p-8 text-center" style="background: linear-gradient(to right, <?php echo e($primaryColour); ?>, <?php echo e($secondaryColour); ?>);">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to Work With Us?</h2>
                <p class="text-white/90 mb-6 max-w-2xl mx-auto">
                    If you're a candidate looking to work with <?php echo e($organisation['name']); ?>, contact us to learn more about our CV management services.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if (!isLoggedIn()): ?>
                        <a href="/?register=1" class="inline-flex items-center justify-center px-6 py-3 bg-white text-base font-semibold rounded-lg hover:bg-gray-50 transition-colors" style="color: <?php echo e($primaryColour); ?>;">
                            Create Your Account
                        </a>
                        <a href="/?login=1" class="inline-flex items-center justify-center px-6 py-3 border-2 border-white text-white text-base font-semibold rounded-lg hover:bg-white/10 transition-colors">
                            Log In
                        </a>
                    <?php else: ?>
                        <a href="/dashboard.php" class="inline-flex items-center justify-center px-6 py-3 bg-white text-base font-semibold rounded-lg hover:bg-gray-50 transition-colors" style="color: <?php echo e($primaryColour); ?>;">
                            Go to Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>
    <?php partial('auth-modals'); ?>
</body>
</html>

