<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'Extra Income Ideas';
$canonicalUrl = APP_URL . '/resources/extra-income/';
$sections = [
    [
        'title' => 'Evening & Weekend Projects',
        'description' => 'Short bursts of effort that fit around a full-time schedule.',
        'articles' => [
            [
                'title' => '20+ Legitimate Ways to Earn Money Online & From Home in 2025',
                'excerpt' => 'Comprehensive guide covering surveys, market research, remote gigs, and long-term income builders.',
                'href' => '/resources/extra-income/legitimate-ways-to-earn-money-online.php',
                'cta' => 'Read full guide',
            ],
            [
                'title' => 'Paid User Testing Platforms',
                'excerpt' => 'Earn money sharing feedback on websites, apps, and physical products in your spare time.',
            ],
            [
                'title' => 'Micro Freelance Gigs You Can Deliver in Under 60 Minutes',
                'excerpt' => 'Simple services—like Canva templates or quick copy edits—that are easy to offer on marketplaces.',
            ],
            [
                'title' => 'Virtual Event Staffing',
                'excerpt' => 'Support webinars, digital conferences, and online workshops without leaving home.',
            ],
        ],
    ],
    [
        'title' => 'On-Demand Work',
        'description' => 'Flexible shifts you can pick up whenever you need to top-up your income.',
        'articles' => [
            [
                'title' => 'Rideshare and Delivery: What’s Worth It in 2025?',
                'excerpt' => 'Compare UK-friendly platforms, expected earnings, and mileage considerations.',
            ],
            [
                'title' => 'Hospitality Temp Agencies',
                'excerpt' => 'Jump into evening banqueting gigs, hotel shifts, or bar work with minimal notice.',
            ],
            [
                'title' => 'Tasker Marketplaces',
                'excerpt' => 'From assembling flat-pack furniture to clerical catch-up days, explore gigs in your area.',
            ],
        ],
    ],
    [
        'title' => 'Creative Cash Boosters',
        'description' => 'Ways to monetise your hobbies without turning them into a full business.',
        'articles' => [
            [
                'title' => 'Selling Digital Printables and Downloadables',
                'excerpt' => 'Design planners, worksheets, or party resources and sell via Etsy or your own storefront.',
            ],
            [
                'title' => 'Short-Form Video Commissions',
                'excerpt' => 'Offer personalised TikTok/Reels intros, video edits, or podcast audiograms per brief.',
            ],
            [
                'title' => 'Rent Out Household Items',
                'excerpt' => 'List appliances, tools, or camera gear on peer-to-peer rental sites when you’re not using them.',
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
        'metaDescription' => 'Practical, flexible extra-income ideas you can start this week to boost your savings or pay down debt.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-orange-50 text-gray-800">
<?php partial('header'); ?>

<main>
    <section class="bg-gradient-to-br from-orange-500 via-amber-500 to-rose-500 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-1 text-sm font-medium">Extra Income</span>
                <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="mt-4 text-lg text-orange-100">
                    Looking to cover rising costs, build a rainy-day fund, or fast-track debt repayments? These flexible ideas help you earn more without committing to full-time change.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/passive-income/" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-orange-600 shadow hover:bg-orange-50">
                        Explore passive streams
                    </a>
                    <a href="#ideas" class="inline-flex items-center justify-center rounded-lg border border-white/60 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Browse quick wins
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
                        <p class="mt-2 text-gray-600 max-w-3xl"><?php echo e($section['description']); ?></p>
                    </div>
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-700">
                        View all ideas
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($section['articles'] as $article): ?>
                        <article class="group relative overflow-hidden rounded-2xl border border-orange-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-100 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                            <div class="relative">
                                <div class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-orange-600">
                                    Extra cash
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">
                                    <?php echo e($article['title']); ?>
                                </h3>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                    <?php echo e($article['excerpt']); ?>
                                </p>
                                <?php if (!empty($article['href'])): ?>
                                    <a href="<?php echo e($article['href']); ?>" class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-orange-600 hover:text-orange-700">
                                        <?php echo e($article['cta'] ?? 'Read guide'); ?>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <button class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-orange-600 hover:text-orange-700">
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
