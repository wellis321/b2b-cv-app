<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'Using AI in Your Job Applications: A Practical Guide';
$metaDescription = 'Learn how to use AI tools like ChatGPT responsibly for CVs, cover letters, and interview prep without losing your authentic voice.';

$sections = [
    [
        'id' => 'intro',
        'title' => 'What Can AI Do for Your Job Search?',
        'image' => 'static/images/resources-images/AI/artificial intelligence-500.jpg',
        'image_alt' => 'Professionals using artificial intelligence tools during a job search',
        'content' => [
            'AI tools like ChatGPT, Claude, and specialised career platforms can assist with drafting CVs, cover letters, and interview preparation. They excel at generating first drafts, suggesting improvements, and helping you articulate your experience more clearly.',
            'Use AI to analyse job descriptions, identify key skills, and highlight relevant keywords. It can also provide alternative phrasing when you are stuck on how to present your achievements.',
        ],
        'extra' => [
            'type' => 'tip',
            'title' => 'Smart usage',
            'body' => 'Treat AI suggestions like a brainstorming partner. Gather ideas quickly, then refine them to match your voice and experience.',
        ],
    ],
    [
        'id' => 'cv-usage',
        'title' => 'The Right Way to Use AI',
        'image' => 'static/images/resources-images/AI/Interview-Preparation-500.png',
        'image_alt' => 'AI powered workspace showing interview preparation prompts',
        'subsections' => [
            [
                'title' => 'For CV and Resume Creation',
                'content' => [
                    'Provide the AI with specific achievements, metrics, and responsibilities so it can suggest impactful bullet points and structure.',
                ],
                'extra' => [
                    'type' => 'tip',
                    'title' => 'Pro Tip',
                    'body' => 'Ask AI to rewrite bullet points using action verbs and quantify achievements—then adjust for accuracy.',
                ],
            ],
            [
                'title' => 'For Cover Letters',
                'content' => [
                    'Give AI context about the company, role, and your interest. Use the draft as a starting point, then personalise it heavily.',
                    'Avoid generic phrasing that sounds “AI-generated”. Recruiters value authenticity.',
                ],
            ],
            [
                'title' => 'For Interview Preparation',
                'content' => [
                    'Use AI to generate practice questions, simulate interviewer Q&A, and structure answers with frameworks like STAR.',
                ],
            ],
        ],
    ],
    [
        'id' => 'pitfalls',
        'title' => 'Common Pitfalls to Avoid',
        'image' => 'static/images/resources-images/AI/pitfalls-500.png',
        'image_alt' => 'Illustration showing dos and donts when using AI tools',
        'doDont' => [
            'do' => [
                'Use AI for drafting and brainstorming.',
                'Edit every AI-generated sentence to reflect your voice.',
                'Verify all facts and metrics.',
                'Leverage AI to uncover overlooked skills.',
            ],
            'dont' => [
                'Copy and paste without review.',
                'Allow AI to fabricate or exaggerate experience.',
                'Submit the same AI text to every employer.',
                'Rely on AI for technical accuracy without your oversight.',
            ],
        ],
    ],
    [
        'id' => 'employer-perspective',
        'title' => 'Employer Perspectives on AI Use',
        'image' => 'static/images/resources-images/AI/Employer-Perspectives-500.png',
        'image_alt' => 'Hiring manager reviewing AI-assisted applications',
        'content' => [
            'Employers accept that candidates use AI but expect honesty and effort. They look for applications that feel personal and accurate.',
        ],
        'extra' => [
            'type' => 'warning',
            'title' => 'Important',
            'body' => 'If an employer asks whether you used AI, be transparent. Demonstrating thoughtful use can work in your favour.',
        ],
    ],
    [
        'id' => 'authenticity',
        'title' => 'Maintaining Authenticity',
        'image' => 'static/images/resources-images/AI/Maintaining-Authenticity-500.png',
        'image_alt' => 'Job applicant adjusting AI generated content to match their voice',
        'content' => [
            'Combine AI efficiency with your unique voice. Use AI for structure and clarity, but write the parts that showcase your motivation and personality.',
        ],
        'extra' => [
            'type' => 'tip',
            'title' => 'Red flag check',
            'body' => 'Watch for overly formal phrases or vague statements. Replace them with natural language and concrete examples.',
        ],
    ],
    [
        'id' => 'future',
        'title' => 'The Future of AI in Job Applications',
        'image' => 'static/images/resources-images/AI/artificial intelligence-500.jpg',
        'image_alt' => 'Future technology concept showing artificial intelligence icons',
        'content' => [
            'AI tools will become more sophisticated, and employers will improve their methods for assessing genuine fit. Staying informed helps you adapt.',
        ],
        'extra' => [
            'type' => 'key',
            'title' => 'Key takeaway',
            'body' => 'AI should amplify your strengths, not replace your effort. Use it to present your genuine qualifications clearly and efficiently.',
        ],
    ],
    [
        'id' => 'practical-steps',
        'title' => 'Practical Steps to Get Started',
        'image' => 'static/images/resources-images/AI/Practical Steps to Get Started-500.png',
        'image_alt' => 'Checklist showing practical steps to incorporate AI into job applications',
        'content' => [
            'Start with one task—e.g., improving CV bullet points. Compare AI suggestions with your originals and create a refined version.',
            'Expand to cover letters or interview prep as you build confidence, always double-checking for accuracy and tone.',
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
        'canonicalUrl' => APP_URL . '/resources/jobs/using-ai-in-job-applications.php',
        'metaImage' => APP_URL . '/static/images/resources-images/AI/artificial intelligence-500.jpg',
        'structuredDataType' => 'article',
        'structuredData' => [
            'title' => $pageTitle,
            'description' => $metaDescription,
            'image' => APP_URL . '/static/images/resources-images/AI/artificial intelligence-500.jpg',
            'datePublished' => '2025-01-01',
            'dateModified' => date('Y-m-d'),
        ],
    ]); ?>
</head>
<body class="bg-slate-50 text-slate-900">
<?php partial('header'); ?>

<main id="main-content" role="main">
    <article class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[80%] rounded-full bg-sky-500/10 blur-3xl"></div>
            <div class="absolute -bottom-32 right-0 h-64 w-64 rounded-full bg-blue-400/20 blur-3xl"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <header class="space-y-8">
                <div class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                    AI & Applications
                </div>
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="text-lg text-slate-200 max-w-3xl leading-relaxed">
                    Generative AI is everywhere—but using it well separates standout applications from forgettable ones. Here’s how to harness AI as a helpful ally without losing your authentic voice.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/jobs/" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-slate-100">
                        Back to job insights
                    </a>
                    <a href="#intro" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Start reading
                    </a>
                </div>
            </header>
        </div>
    </article>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16 text-base leading-relaxed text-slate-700">
        <?php foreach ($sections as $index => $section): ?>
            <?php
            $imagePath = $section['image'] ?? null;
            $encodedImagePath = $imagePath ? '/' . str_replace(' ', '%20', $imagePath) : null;
            $imageAlt = $section['image_alt'] ?? ($section['title'] ?? 'Article illustration');
            $reverseLayout = $index % 2 === 1;
            ?>
            <section id="<?php echo e($section['id']); ?>" class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                <div class="flex flex-col gap-6 <?php echo $reverseLayout ? 'lg:flex-row-reverse' : 'lg:flex-row'; ?> lg:items-center">
                    <?php if ($encodedImagePath): ?>
                        <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm lg:w-5/12">
                            <img src="<?php echo e($encodedImagePath); ?>"
                                 alt="<?php echo e($imageAlt); ?>"
                                 class="h-80 w-full object-cover" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="<?php echo $encodedImagePath ? 'lg:w-7/12' : ''; ?>">
                        <h2 class="text-2xl font-semibold text-slate-900"><?php echo e($section['title']); ?></h2>
                        <div class="mt-4 space-y-5">
                    <?php if (!empty($section['content'])): ?>
                        <?php foreach ($section['content'] as $paragraph): ?>
                            <p><?php echo e($paragraph); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($section['subsections'])): ?>
                        <div class="space-y-6">
                            <?php foreach ($section['subsections'] as $sub): ?>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo e($sub['title']); ?></h3>
                                    <div class="mt-3 space-y-3 text-base text-slate-600">
                                        <?php foreach ($sub['content'] as $paragraph): ?>
                                            <p><?php echo e($paragraph); ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (!empty($sub['extra']) && $sub['extra']['type'] === 'tip'): ?>
                                        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-base text-slate-600">
                                            <strong><?php echo e($sub['extra']['title']); ?>:</strong> <?php echo e($sub['extra']['body']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['doDont'])): ?>
                        <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-5 lg:shadow">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Do</h3>
                                <ul class="mt-3 space-y-2 text-base text-slate-600 list-disc pl-5">
                                    <?php foreach ($section['doDont']['do'] as $item): ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-6 py-5 lg:shadow">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-rose-700">Don’t</h3>
                                <ul class="mt-3 space-y-2 text-base text-slate-600 list-disc pl-5">
                                    <?php foreach ($section['doDont']['dont'] as $item): ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['extra'])): ?>
                        <?php if ($section['extra']['type'] === 'tip'): ?>
                            <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-base text-slate-600">
                                <strong><?php echo e($section['extra']['title']); ?>:</strong> <?php echo e($section['extra']['body']); ?>
                            </div>
                        <?php elseif ($section['extra']['type'] === 'warning'): ?>
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-800">
                                <strong><?php echo e($section['extra']['title']); ?>:</strong> <?php echo e($section['extra']['body']); ?>
                            </div>
                        <?php elseif ($section['extra']['type'] === 'key'): ?>
                            <div class="rounded-2xl border border-purple-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 px-6 py-6 text-base text-white shadow-lg shadow-slate-900/20">
                                <h3 class="text-base font-semibold text-white"><?php echo e($section['extra']['title']); ?></h3>
                                <p class="mt-3 text-slate-100/80">
                                    <?php echo e($section['extra']['body']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </section>

    <section class="bg-white border-y border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid gap-8 lg:grid-cols-2 text-base leading-relaxed text-slate-700">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                <h2 class="text-2xl font-semibold text-slate-900">Refresh your application toolkit</h2>
                <p class="mt-4 text-base text-slate-600">
                    Simple CV Builder helps you turn AI-assisted drafts into polished, ATS-friendly CVs and cover letters. Upgrade to unlock unlimited sections, premium templates, and QR codes.
                </p>
                <a href="/subscription.php" class="mt-6 inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800">
                    View plans
                </a>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                <h2 class="text-2xl font-semibold text-slate-900">AI Prompt Cheat Sheet</h2>
                <p class="mt-4 text-base text-slate-600">
                    Copy-paste ready prompts for ChatGPT, Claude, and other AI tools. Get better results for CV writing, cover letters, and interview prep.
                </p>
                <a href="/resources/jobs/ai-prompt-cheat-sheet.php" class="mt-6 inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800">
                    View Cheat Sheet →
                </a>
            </div>
        </div>
    </section>

    <section class="bg-slate-900 text-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-6 text-base leading-relaxed">
            <h2 class="text-3xl font-semibold">Bring the human + AI combo</h2>
            <p class="text-base text-slate-200">
                AI speeds up the heavy lifting, but your personality, experience, and honesty make applications memorable. Keep refining your process to blend both beautifully.
            </p>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-6">
                <p class="text-base font-semibold text-white">Next resource placeholder</p>
                <p class="mt-3 text-sm text-slate-200">
                    Replace this block with a relevant call-to-action—perhaps an interview prep guide or coaching invitation.
                </p>
            </div>
        </div>
    </section>
</main>

    <?php partial('footer'); ?>
    <?php partial('auth-modals'); ?>
</body>
</html>
