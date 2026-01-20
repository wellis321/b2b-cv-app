<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'Job Market Insights';
$canonicalUrl = APP_URL . '/resources/jobs/';
$sections = [
    [
        'title' => '',
        'description' => '',
        'articles' => [
            [
                'title' => '11 Remote Jobs Perfect for Beginners in 2026',
                'excerpt' => 'Curated list of flexible remote roles ranging from customer success to data analysis.',
                'href' => '/resources/jobs/remote-jobs-begginers.php',
                'cta' => 'Read full guide',
                'badge' => 'Guide',
            ],
            [
                'title' => 'Using AI in Your Job Applications: A Practical Guide',
                'excerpt' => 'Harness tools like ChatGPT responsibly for CVs, cover letters, and interviews without losing authenticity.',
                'href' => '/resources/jobs/using-ai-in-job-applications.php',
                'cta' => 'Read full guide',
                'badge' => 'Guide',
            ],
            [
                'title' => 'Six Steps to Refreshing Your CV in 30 Minutes',
                'excerpt' => 'Quick wins to modernise your CV layout, keywords, and story.',
                'href' => '/resources/jobs/how-to-refresh-your-cv-in-30-minutes.php',
                'cta' => 'Read full guide',
                'badge' => 'Guide',
            ],
            [
                'title' => 'AI Prompt Cheat Sheet',
                'excerpt' => 'Ready-to-use AI prompts for job applications, CVs, and cover letters.',
                'href' => '/resources/jobs/ai-prompt-cheat-sheet.php',
                'cta' => 'Read full guide',
                'badge' => 'Guide',
            ],
            [
                'title' => 'Healthcare Career Paths: Start Your Journey Without a Degree',
                'excerpt' => 'Discover rewarding healthcare support roles you can start without a university degree. Learn about entry-level positions, training requirements, salaries, and how to begin your healthcare career today.',
                'href' => '/resources/jobs/entry-level-healthcare-careers.php',
                'cta' => 'Read full guide',
                'badge' => 'Guide',
            ],
            [
                'title' => 'How to Update Your CV: A Complete Guide',
                'excerpt' => 'Step-by-step advice for refreshing every section of your CV whenever opportunity knocks.',
                'href' => '/resources/career/how-to-update-your-cv.php',
                'cta' => 'Read full guide',
                'badge' => 'Toolkit',
            ],
            [
                'title' => 'CV Update Checklist',
                'excerpt' => 'A comprehensive printable checklist to systematically update your CV and ensure nothing is missed.',
                'href' => '/resources/career/cv-update-checklist.php',
                'cta' => 'Read full guide',
                'badge' => 'Toolkit',
            ],
            [
                'title' => 'CV Keywords and ATS: A Complete Guide',
                'excerpt' => 'Learn how Applicant Tracking Systems (ATS) work, why keywords matter in your CV, and how to optimise your CV to pass ATS screening and reach human recruiters.',
                'href' => '/resources/career/keywords-and-ats-guide.php',
                'cta' => 'Read full guide',
                'badge' => 'Guide',
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
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="mt-4 text-lg text-blue-100">
                    Stay ahead of hiring trends with curated guides, templates, and practical advice.
                    Updated regularly to help you unlock new opportunities faster.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/#pricing" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-blue-600 shadow hover:bg-blue-50">
                        Get unlimited CV sections
                    </a>
                    <a href="#sections" class="inline-flex items-center justify-center rounded-lg border border-white/60 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10" data-smooth-scroll>
                        Browse topics
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="sections" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16 scroll-mt-32">
        <?php foreach ($sections as $section): ?>
            <div class="space-y-6">
                <?php if (!empty($section['title']) || !empty($section['description'])): ?>
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <?php if (!empty($section['title'])): ?>
                            <h2 class="text-2xl font-bold text-gray-900"><?php echo e($section['title']); ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($section['description'])): ?>
                            <p class="mt-2 text-gray-500 max-w-3xl"><?php echo e($section['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                        View all guides
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <?php endif; ?>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($section['articles'] as $article): ?>
                        <article class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                            <div class="relative">
                                <?php if (!empty($article['badge'])): ?>
                                <div class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-600">
                                    <?php echo e($article['badge']); ?>
                                </div>
                                <?php endif; ?>
                                <h3 class="<?php echo !empty($article['badge']) ? 'mt-4' : ''; ?> text-lg font-semibold text-gray-900">
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

<script>
    // Smooth scroll for "Browse topics" link with offset
    document.querySelectorAll('a[data-smooth-scroll], a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const headerOffset = 100; // Account for header
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
</script>
</body>
</html>
