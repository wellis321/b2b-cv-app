<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'AI Prompt Cheat Sheet for Job Applications';
$metaDescription = 'Master AI prompts for CV writing, cover letters, and interview prep. Copy-paste ready prompts to get better results from ChatGPT, Claude, and other AI tools.';

$promptCategories = [
    [
        'title' => 'CV & Resume Prompts',
        'icon' => '📄',
        'prompts' => [
            [
                'title' => 'Improve CV Bullet Points',
                'prompt' => 'Rewrite these job responsibilities as achievement-focused bullet points using action verbs. Include metrics where possible:\n\n[Paste your responsibilities here]',
                'tip' => 'Always provide specific examples and numbers for best results.'
            ],
            [
                'title' => 'Optimize for ATS',
                'prompt' => 'Analyze this job description and suggest keywords I should include in my CV:\n\nJob Description: [Paste job description]\n\nMy CV: [Paste relevant section]',
                'tip' => 'Match keywords naturally—don\'t keyword stuff.'
            ],
            [
                'title' => 'Summarize Work Experience',
                'prompt' => 'Create a concise 2-3 sentence professional summary for this role:\n\nJob Title: [Your title]\nCompany: [Company name]\nDuration: [Start - End]\nKey Responsibilities: [List 3-5 main responsibilities]\nAchievements: [List 2-3 key achievements]',
                'tip' => 'Focus on impact and results, not just duties.'
            ],
            [
                'title' => 'Translate Skills to Keywords',
                'prompt' => 'Suggest industry-standard keywords and phrases for these skills:\n\n[Your skills list]\n\nTarget role: [Job title you\'re applying for]',
                'tip' => 'Use terms recruiters actually search for.'
            ],
        ],
    ],
    [
        'title' => 'Cover Letter Prompts',
        'icon' => '✉️',
        'prompts' => [
            [
                'title' => 'Draft Cover Letter Opening',
                'prompt' => 'Write a compelling opening paragraph for a cover letter that:\n- Shows genuine interest in [Company Name]\n- Highlights my relevant experience in [Your field]\n- Mentions [Specific company achievement or value]\n\nMy background: [Brief summary]',
                'tip' => 'Personalize by researching the company first.'
            ],
            [
                'title' => 'Connect Experience to Role',
                'prompt' => 'Help me connect my experience to this job requirement:\n\nJob Requirement: [Paste requirement]\n\nMy Experience: [Describe relevant experience]\n\nWrite 2-3 sentences showing the connection.',
                'tip' => 'Be specific with examples, not generic.'
            ],
            [
                'title' => 'Address Employment Gaps',
                'prompt' => 'Help me address this employment gap positively in a cover letter:\n\nGap Period: [Dates]\nWhat I Did: [Volunteering, courses, projects, etc.]\n\nWrite a brief, positive explanation.',
                'tip' => 'Focus on growth and learning, not excuses.'
            ],
        ],
    ],
    [
        'title' => 'Interview Preparation Prompts',
        'icon' => '🎤',
        'prompts' => [
            [
                'title' => 'Generate Practice Questions',
                'prompt' => 'Generate 10 common interview questions for a [Job Title] role at [Company Type]. Include both behavioral and technical questions.',
                'tip' => 'Practice out loud, not just in your head.'
            ],
            [
                'title' => 'Structure STAR Answers',
                'prompt' => 'Help me structure a STAR (Situation, Task, Action, Result) answer for this question:\n\nQuestion: [Interview question]\n\nMy Experience: [Brief description of relevant situation]',
                'tip' => 'Always quantify results when possible.'
            ],
            [
                'title' => 'Prepare Questions to Ask',
                'prompt' => 'Suggest 5 thoughtful questions I should ask the interviewer for a [Job Title] position. Focus on:\n- Team dynamics\n- Growth opportunities\n- Company culture\n- Role expectations',
                'tip' => 'Show genuine interest, not just what you want.'
            ],
            [
                'title' => 'Practice Salary Negotiation',
                'prompt' => 'Help me prepare for salary negotiation. My research shows the range is [Range]. My experience level is [Level]. Write a professional response if they offer [Amount].',
                'tip' => 'Always negotiate—most employers expect it.'
            ],
        ],
    ],
    [
        'title' => 'Job Search Strategy Prompts',
        'icon' => '🔍',
        'prompts' => [
            [
                'title' => 'Analyze Job Description',
                'prompt' => 'Analyze this job description and tell me:\n1. Key skills required\n2. Must-have vs nice-to-have qualifications\n3. Red flags or concerns\n4. How to tailor my application\n\nJob Description: [Paste full description]',
                'tip' => 'Save time by filtering out bad fits early.'
            ],
            [
                'title' => 'Research Company Culture',
                'prompt' => 'Based on this company\'s website and recent news, summarize:\n- Company values and culture\n- Recent achievements or challenges\n- Growth trajectory\n- What they value in employees\n\nCompany: [Name]\nWebsite: [URL]',
                'tip' => 'Use this to personalize your application.'
            ],
            [
                'title' => 'Network Outreach Message',
                'prompt' => 'Write a professional LinkedIn message to [Name] at [Company] asking for:\n- Brief informational interview\n- Advice on breaking into [Industry/Role]\n- Keep it under 150 words and authentic',
                'tip' => 'Be specific about what you want, not vague.'
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
        'metaDescription' => $metaDescription,
        'canonicalUrl' => APP_URL . '/resources/jobs/ai-prompt-cheat-sheet.php',
        'structuredDataType' => 'article',
        'structuredData' => [
            'title' => $pageTitle,
            'description' => $metaDescription,
            'datePublished' => '2025-01-01',
            'dateModified' => date('Y-m-d'),
        ],
    ]); ?>
    <style>
        .prompt-box {
            position: relative;
        }
        .copy-button {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .prompt-box:hover .copy-button {
            opacity: 1;
        }
        .copied {
            background-color: #10b981 !important;
            color: white !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
<?php partial('header'); ?>

<main id="main-content" role="main">
    <article class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[80%] rounded-full bg-purple-500/10 blur-3xl"></div>
            <div class="absolute -bottom-32 right-0 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <header class="space-y-8">
                <div class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                    AI Resources
                </div>
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="text-lg text-slate-200 max-w-3xl leading-relaxed">
                    Copy-paste ready prompts for ChatGPT, Claude, and other AI tools. Get better results for CV writing, cover letters, and interview prep.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/jobs/using-ai-in-job-applications.php" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-slate-100">
                        ← Back to AI guide
                    </a>
                    <a href="#prompts" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Jump to prompts
                    </a>
                </div>
            </header>
        </div>
    </article>

    <section id="prompts" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-12">
        <?php foreach ($promptCategories as $category): ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl"><?php echo e($category['icon']); ?></span>
                    <h2 class="text-2xl font-semibold text-slate-900"><?php echo e($category['title']); ?></h2>
                </div>
                <div class="space-y-6">
                    <?php foreach ($category['prompts'] as $prompt): ?>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-3"><?php echo e($prompt['title']); ?></h3>
                            <div class="prompt-box relative rounded-lg border border-slate-300 bg-white p-4 font-mono text-sm text-slate-700 whitespace-pre-wrap">
                                <button
                                    class="copy-button rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    onclick="copyPrompt(this)"
                                    data-prompt="<?php echo e(htmlspecialchars($prompt['prompt'], ENT_QUOTES)); ?>"
                                >
                                    Copy
                                </button>
                                <?php echo e($prompt['prompt']); ?>
                            </div>
                            <?php if (!empty($prompt['tip'])): ?>
                                <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2">
                                    <p class="text-sm text-slate-700">
                                        <strong class="text-blue-900">💡 Tip:</strong> <?php echo e($prompt['tip']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="bg-white border-y border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="rounded-2xl border-2 border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 px-8 py-8 shadow-lg">
                <div class="flex flex-col items-center text-center">
                    <svg class="h-12 w-12 text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h2 class="text-2xl font-semibold text-slate-900 mb-3">Turn AI Drafts Into Polished CVs</h2>
                    <p class="text-base text-slate-700 max-w-2xl mb-6">
                        Use Simple CV Builder to transform your AI-assisted drafts into professional, ATS-friendly CVs. Create your free account and start building today.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <?php if (isLoggedIn()): ?>
                            <a href="/dashboard.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-md hover:bg-blue-700 transition-colors">
                                Build Your CV
                            </a>
                            <a href="/subscription.php" class="inline-flex items-center justify-center rounded-lg border-2 border-blue-600 px-6 py-3 text-base font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                                Upgrade to Pro
                            </a>
                        <?php else: ?>
                            <a href="/#auth-section" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-md hover:bg-blue-700 transition-colors">
                                Create Free Account
                            </a>
                            <a href="/#pricing" class="inline-flex items-center justify-center rounded-lg border-2 border-blue-600 px-6 py-3 text-base font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                                View Pricing
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php partial('footer'); ?>
<?php partial('auth-modals'); ?>

<script>
function copyPrompt(button) {
    const prompt = button.getAttribute('data-prompt');
    navigator.clipboard.writeText(prompt).then(() => {
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('copied');
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('copied');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy. Please select and copy manually.');
    });
}
</script>
</body>
</html>
