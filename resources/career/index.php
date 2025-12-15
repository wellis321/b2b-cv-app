<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'Career Advice Hub';
$canonicalUrl = APP_URL . '/resources/career/';
$sections = [
    [
        'title' => 'Career Planning',
        'description' => 'Figure out your next move with practical frameworks and reflection prompts.',
        'articles' => [
            [
                'title' => 'Goal Setting Workshop',
                'excerpt' => 'Define a 12-month career roadmap with milestones, learning objectives, and accountability.',
            ],
            [
                'title' => 'Skills Gap Assessment Template',
                'excerpt' => 'Audit your current strengths and identify the skills that unlock your next promotion.',
            ],
            [
                'title' => 'How To Switch Careers Without Starting Over',
                'excerpt' => 'Strategies to repurpose your experience and tell a compelling transition story.',
            ],
        ],
    ],
    [
        'title' => 'CVs, Applications & Interviews',
        'description' => 'Support to help you stand out through every stage of the hiring process.',
        'articles' => [
            [
                'title' => 'How to Update Your CV: A Complete Guide',
                'excerpt' => 'Step-by-step guidance for refreshing every section of your CV whenever opportunity knocks.',
                'href' => '/resources/career/how-to-update-your-cv.php',
                'cta' => 'Read full guide',
            ],
            [
                'title' => 'Mastering Online Application Portals',
                'excerpt' => 'Organise job alerts, customise responses quickly, and avoid common mistakes.',
            ],
            [
                'title' => 'Interview Confidence Toolkit',
                'excerpt' => 'Body language, storytelling, and follow-up etiquette to leave a lasting impression.',
            ],
        ],
    ],
    [
        'title' => 'Early Career Corner',
        'description' => 'Ideal for students, graduates, and career changers building experience from scratch.',
        'articles' => [
            [
                'title' => 'Landing Internships & Work Experience',
                'excerpt' => 'Where to look, how to pitch yourself, and tips to make the most of each placement.',
            ],
            [
                'title' => 'Portfolio Ideas for Non-Tech Roles',
                'excerpt' => 'Showcase projects for marketing, operations, admin, and service-based careers.',
            ],
            [
                'title' => 'Professional Etiquette Basics',
                'excerpt' => 'Simple guidelines for email, meetings, and workplace collaboration.',
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
        'metaDescription' => 'Career planning tools, CV tips, and early-career advice from the Simple CV Builder team.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-slate-50 text-gray-800">
<?php partial('header'); ?>

<main>
    <section class="bg-gradient-to-br from-violet-600 via-indigo-500 to-blue-500 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-1 text-sm font-medium">Career Support</span>
                <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="mt-4 text-lg text-indigo-100">
                    Build confidence at every stage of your career. These guides cover planning, applications, workplace skills, and more.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/jobs/" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-indigo-600 shadow hover:bg-indigo-50">
                        Explore job insights
                    </a>
                    <a href="#guides" class="inline-flex items-center justify-center rounded-lg border border-white/60 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Browse guides
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="guides" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        <?php foreach ($sections as $section): ?>
            <div class="space-y-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?php echo e($section['title']); ?></h2>
                        <p class="mt-2 text-gray-500 max-w-3xl"><?php echo e($section['description']); ?></p>
                    </div>
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        View resource index
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($section['articles'] as $article): ?>
                        <article class="group relative overflow-hidden rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                            <div class="relative">
                                <div class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                    Toolkit
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">
                                    <?php echo e($article['title']); ?>
                                </h3>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                    <?php echo e($article['excerpt']); ?>
                                </p>
                                <?php if (!empty($article['href'])): ?>
                                    <a href="<?php echo e($article['href']); ?>" class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                        <?php echo e($article['cta'] ?? 'Read guide'); ?>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <button class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
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
