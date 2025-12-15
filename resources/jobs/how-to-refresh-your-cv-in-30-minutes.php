<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'How to Refresh Your CV in 30 Minutes';
$metaDescription = 'Use this 30-minute workflow to modernise your CV summary, skills, and experience so you can respond to new job opportunities fast.';
$canonicalUrl = APP_URL . '/resources/jobs/how-to-refresh-your-cv-in-30-minutes.php';

$sections = [
    [
        'title' => 'Refresh Your Professional Summary',
        'content' => [
            ['type' => 'paragraph', 'text' => 'Start with your professional summary at the top of your CV. This opening paragraph is prime real estate that recruiters read first, so it needs to accurately reflect where you are now in your career.'],
            ['type' => 'paragraph', 'text' => 'Update this section to include:'],
            ['type' => 'list', 'items' => [
                'Your current experience level and years in the industry',
                'Recently acquired skills that are in demand',
                'New certifications or qualifications',
                'Updated metrics about your scope of responsibility',
            ]],
            ['type' => 'paragraph', 'text' => 'This takes just a few minutes but significantly impacts how recruiters perceive your current capabilities and career progression.'],
        ],
    ],
    [
        'title' => 'Revise Your Key Skills Section',
        'content' => [
            ['type' => 'paragraph', 'text' => 'If your CV includes a skills section (and it should), this is one of the fastest areas to update. This bulleted list provides recruiters with an at-a-glance view of your capabilities.'],
            ['type' => 'paragraph', 'text' => 'Make these quick adjustments:'],
            ['type' => 'list', 'items' => [
                'Add technical skills you\'ve developed in recent roles',
                'Include skills mentioned in job descriptions you\'re targeting',
                'Remove outdated skills that no longer apply to your career direction',
                'Prioritise skills that align with your next career move',
            ]],
            ['type' => 'paragraph', 'text' => 'If you have a specific role in mind, review the job posting and mirror relevant skills that you genuinely possess.'],
        ],
    ],
    [
        'title' => 'Add Your Most Recent Position',
        'content' => [
            ['type' => 'paragraph', 'text' => 'Your current or latest role receives the most attention from recruiters, making this section critical to update. While you might normally spend considerable time perfecting this, focus on essential details when time is limited.'],
            ['type' => 'paragraph', 'text' => 'Include these elements:'],
            ['type' => 'list', 'items' => [
                'A brief overview of your role and the company',
                'Three to five key responsibilities that relate to your target positions',
                'Quantifiable achievements that demonstrate your impact',
                'Projects or initiatives relevant to jobs you\'re pursuing',
            ]],
            ['type' => 'paragraph', 'text' => 'Use specific numbers and metrics where possible, such as revenue increased, costs saved, or team members managed, to show tangible results.'],
        ],
    ],
    [
        'title' => 'Remove Outdated Information',
        'content' => [
            ['type' => 'paragraph', 'text' => 'Creating space for new, relevant content often means trimming the old. This editing process is quick and makes a significant difference.'],
            ['type' => 'paragraph', 'text' => 'Consider reducing or removing:'],
            ['type' => 'list', 'items' => [
                'Early career positions from 10+ years ago (keep them brief or remove entirely)',
                'Outdated technical skills or software you no longer use',
                'Hobbies and interests that don\'t relate to your career goals',
                'The references line — these can be provided upon request',
            ]],
            ['type' => 'paragraph', 'text' => 'Aim to keep your CV to two pages maximum. This length is professional, respects recruiters\' time, and forces you to prioritise your most impressive achievements.'],
        ],
    ],
    [
        'title' => 'Highlight Recent Professional Development',
        'content' => [
            ['type' => 'paragraph', 'text' => 'If you\'ve taken courses, earned certifications, or developed new skills recently, these additions demonstrate your commitment to continuous learning.'],
            ['type' => 'paragraph', 'text' => 'Be sure to add:'],
            ['type' => 'list', 'items' => [
                'Online courses or certifications completed',
                'Industry-relevant side projects or freelance work',
                'Volunteer work that showcases transferable skills',
                'Professional development activities related to your target industry',
            ]],
            ['type' => 'paragraph', 'text' => 'This is especially important if you experienced a career gap and want to show how you invested in your professional growth during that time.'],
        ],
    ],
    [
        'title' => 'Final Quick Checks',
        'content' => [
            ['type' => 'paragraph', 'text' => 'Before sending your refreshed CV, take five minutes for these essential checks:'],
            ['type' => 'list', 'items' => [
                'Verify all dates are current and accurate',
                'Ensure contact information is up to date',
                'Confirm your LinkedIn profile URL is included',
                'Run a quick spell-check',
                'Give the file a professional name (e.g., "Priya_Patel_CV.pdf")',
            ]],
        ],
    ],
    [
        'title' => 'The Power of Strategic Updates',
        'content' => [
            ['type' => 'paragraph', 'text' => 'A complete CV overhaul isn\'t always necessary — especially when opportunities arise quickly. By focusing on these high-impact areas you can create a compelling, current CV in just 30 minutes.'],
            ['type' => 'paragraph', 'text' => 'These targeted updates ensure your CV accurately represents your current capabilities while highlighting the skills and experiences most relevant to your target roles. Sometimes, it\'s these strategic refinements that make the difference between landing an interview and being passed over.'],
        ],
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => $metaDescription,
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-slate-50 text-slate-900">
<?php partial('header'); ?>

<main>
    <article class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[80%] rounded-full bg-sky-500/10 blur-3xl"></div>
            <div class="absolute -bottom-32 right-0 h-64 w-64 rounded-full bg-blue-400/20 blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <header class="space-y-8">
                <div class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                    CV refresh workflow
                </div>
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="text-lg text-slate-200 leading-relaxed max-w-3xl">
                    A recruiter calls with an exciting opportunity and needs your CV today. Instead of scrambling, use this 30-minute checklist to sharpen the sections that matter most and respond with confidence.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/career/how-to-update-your-cv.php" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-slate-100">
                        Need the full deep-dive?
                    </a>
                    <a href="#summary" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Jump to checklist
                    </a>
                </div>
            </header>
        </div>
    </article>

    <section id="summary" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
            <p class="text-base leading-relaxed text-slate-600">
                When time is tight, focus on updates that dial up clarity, relevance, and proof of impact. The sections below are ordered by the influence they have on hiring decisions — start at the top and work down until the clock runs out.
            </p>
        </div>

        <?php foreach ($sections as $section): ?>
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                <h2 class="text-2xl font-semibold text-slate-900"><?php echo e($section['title']); ?></h2>
                <div class="mt-4 space-y-4 text-base leading-relaxed text-slate-600">
                    <?php foreach ($section['content'] as $block): ?>
                        <?php if ($block['type'] === 'paragraph'): ?>
                            <p><?php echo e($block['text']); ?></p>
                        <?php elseif ($block['type'] === 'list'): ?>
                            <ul class="list-disc space-y-2 pl-5">
                                <?php foreach ($block['items'] as $item): ?>
                                    <li><?php echo e($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="rounded-3xl border border-slate-900 bg-slate-950 p-8 text-slate-100 shadow-xl">
            <h2 class="text-2xl font-semibold text-white">Ready to put this checklist to work?</h2>
            <p class="mt-4 text-base leading-relaxed text-slate-100/80">
                Build or refresh your CV in minutes with Simple CV Builder. Duplicate sections, drop in quantified wins, and export polished PDFs without wrestling with layout.
            </p>
            <a href="/" class="mt-6 inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-slate-100">
                Launch Simple CV Builder
            </a>
        </section>
    </section>

    <?php partial('resources-footer-cta'); ?>
</main>

<?php partial('footer'); ?>
<?php partial('auth-modals'); ?>
</body>
</html>
