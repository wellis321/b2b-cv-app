<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'Job Market Insights';
$canonicalUrl = APP_URL . '/resources/jobs/';
$sections = [
    [
        'title' => 'Editor\'s Picks',
        'description' => 'High-impact guides to kick-start your job search in 2025.',
        'articles' => [
            [
                'title' => '11 Remote Roles UK Employers Are Hiring For Right Now',
                'excerpt' => 'Curated list of flexible remote roles ranging from customer success to data analysis.',
                'href' => '/resources/jobs/remote-jobs-begginers.php',
                'cta' => 'Read full guide',
            ],
            [
                'title' => 'Using AI in Your Job Applications: A Practical Guide',
                'excerpt' => 'Harness tools like ChatGPT responsibly for CVs, cover letters, and interviews without losing authenticity.',
                'href' => '/resources/jobs/using-ai-in-job-applications.php',
                'cta' => 'Read full guide',
            ],
            [
                'title' => 'How To Refresh Your CV in 30 Minutes',
                'excerpt' => 'Quick wins to modernise your CV layout, keywords, and story.',
                'href' => '/resources/jobs/how-to-refresh-your-cv-in-30-minutes.php',
                'cta' => 'Read full guide',
            ],
        ],
    ],
    [
        'title' => 'Popular Job Paths',
        'description' => 'Explore opportunities across industries and choose the next chapter of your career.',
        'articles' => [
            [
                'title' => 'Healthcare Support Roles That Don\'t Require a Degree',
                'excerpt' => 'Look at patient care, admin, and allied health positions with fast-track training.',
            ],
            [
                'title' => 'Creative Freelance Projects You Can Land This Month',
                'excerpt' => 'Graphic design, copywriting, video editing, and other gigs that pay per project.',
            ],
            [
                'title' => 'Seasonal Jobs to Boost Your Income This Quarter',
                'excerpt' => 'Retail, hospitality, and events roles hiring ahead of peak season.',
            ],
        ],
    ],
    [
        'title' => 'Job Search Playbooks',
        'description' => 'Frameworks to organise your applications, interviews, and negotiation strategy.',
        'articles' => [
            [
                'title' => 'How to Update Your CV: A Complete Guide',
                'excerpt' => 'Step-by-step advice for refreshing every section of your CV whenever opportunity knocks.',
                'href' => '/resources/career/how-to-update-your-cv.php',
                'cta' => 'Read full guide',
            ],
            [
                'title' => 'Smart Application Tracker (Template)',
                'excerpt' => 'Download a simple tracker to log jobs, follow-ups, and offer details.',
            ],
            [
                'title' => 'Email Scripts For Every Stage Of The Job Hunt',
                'excerpt' => 'Polished templates for introductions, follow-ups, and thank-you notes.',
            ],
            [
                'title' => 'Interview Prep Checklist',
                'excerpt' => 'Key research tasks, practice questions, and wrap-up etiquette.',
            ],
        ],
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Stay ahead of hiring trends with curated job search guides, templates, and advice from Simple CV Builder.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
<?php partial('header'); ?>

<main class="bg-gray-50">
    <section class="bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-600 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-1 text-sm font-medium">Insights</span>
                <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="mt-4 text-lg text-blue-100">
                    Stay ahead of hiring trends with curated guides, templates, and practical advice.
                    Updated regularly to help you unlock new opportunities faster.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/subscription.php" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-blue-600 shadow hover:bg-blue-50">
                        Get unlimited CV sections
                    </a>
                    <a href="#sections" class="inline-flex items-center justify-center rounded-lg border border-white/60 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Browse topics
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="sections" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        <?php foreach ($sections as $section): ?>
            <div class="space-y-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?php echo e($section['title']); ?></h2>
                        <p class="mt-2 text-gray-500 max-w-3xl"><?php echo e($section['description']); ?></p>
                    </div>
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                        View all guides
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($section['articles'] as $article): ?>
                        <article class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                            <div class="relative">
                                <div class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-600">
                                    Guide
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">
                                    <?php echo e($article['title']); ?>
                                </h3>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                    <?php echo e($article['excerpt']); ?>
                                </p>
                                <?php if (!empty($article['href'])): ?>
                                    <a href="<?php echo e($article['href']); ?>" class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                                        <?php echo e($article['cta'] ?? 'Read guide'); ?>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <button class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                                        Read placeholder
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <?php partial('resources-footer-cta'); ?>
</main>

<?php partial('footer'); ?>
<?php partial('auth-modals'); ?>
</body>
</html>
