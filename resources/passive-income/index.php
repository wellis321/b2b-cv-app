<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'Passive Income Ideas';
$canonicalUrl = APP_URL . '/resources/passive-income/';
$sections = [
    [
        'title' => 'Starter Strategies',
        'description' => 'Simple ways to layer in extra income without quitting your day job.',
        'articles' => [
            [
                'title' => 'Getting Started with Low-Risk Investing Apps',
                'excerpt' => 'A quick primer on round-up investing, robo-advisors, and diversified portfolios.',
            ],
            [
                'title' => 'Turn Your Spare Room into a Rental Asset',
                'excerpt' => 'Checklist for preparing your space, pricing it fairly, and welcoming guests.',
            ],
            [
                'title' => 'Digital Products You Can Launch in a Weekend',
                'excerpt' => 'Ideas for templates, mini-courses, and printables that deliver evergreen sales.',
            ],
        ],
    ],
    [
        'title' => 'Scale & Automate',
        'description' => 'Build systems that generate revenue while you focus on your career.',
        'articles' => [
            [
                'title' => 'Subscription & Membership Playbook',
                'excerpt' => 'Structure your offer, pricing, and onboarding workflows for recurring revenue.',
            ],
            [
                'title' => 'Affiliate Partnerships 101',
                'excerpt' => 'How to choose programmes, track earnings, and create helpful content that converts.',
            ],
            [
                'title' => 'Outsourcing the Busywork',
                'excerpt' => 'Use virtual assistants and automation tools to keep your side income running smoothly.',
            ],
        ],
    ],
    [
        'title' => 'Success Stories & Ideas',
        'description' => 'Real-world inspiration to spark your next income stream.',
        'articles' => [
            [
                'title' => 'From Hobby Photographer to Print Shop Owner',
                'excerpt' => 'See how licensing, marketplaces, and bundles created predictable cash flow.',
            ],
            [
                'title' => 'Renting Out Vehicles, Storage, and Equipment',
                'excerpt' => 'Ways to earn from assets you already own with peer-to-peer platforms.',
            ],
            [
                'title' => 'Building a Micro SaaS with No-Code Tools',
                'excerpt' => 'Small software ideas that solve niche problems and run on autopilot.',
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
        'metaDescription' => 'Explore realistic passive income ideas that complement your career, curated by Simple CV Builder.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-slate-50 text-gray-800">
<?php partial('header'); ?>

<main>
    <section class="bg-gradient-to-br from-emerald-600 via-teal-500 to-sky-600 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-1 text-sm font-medium">Financial Freedom</span>
                <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="mt-4 text-lg text-emerald-100">
                    Discover realistic ways to earn while you sleep. These guides focus on sustainable ideas that complement your career and grow over time.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/jobs/" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-emerald-600 shadow hover:bg-emerald-50">
                        Explore job opportunities
                    </a>
                    <a href="#ideas" class="inline-flex items-center justify-center rounded-lg border border-white/60 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Browse ideas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="ideas" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        <?php foreach ($sections as $section): ?>
            <div class="space-y-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?php echo e($section['title']); ?></h2>
                        <p class="mt-2 text-gray-500 max-w-3xl"><?php echo e($section['description']); ?></p>
                    </div>
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                        View all ideas
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($section['articles'] as $article): ?>
                        <article class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                            <div class="relative">
                                <div class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">
                                    Idea highlight
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">
                                    <?php echo e($article['title']); ?>
                                </h3>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                    <?php echo e($article['excerpt']); ?>
                                </p>
                                <button class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                                    Read placeholder
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
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
