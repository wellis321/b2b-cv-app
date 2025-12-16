<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'AI Prompt Cheat Sheet for Job Applications';
$metaDescription = 'Master AI prompts for CV writing, cover letters, and interview prep. Copy-paste ready prompts to get better results from ChatGPT, Claude, and other AI tools.';

$promptCategories = [
    [
        'title' => 'CV & Resume Prompts',
        'icon' => 'document',
        'prompts' => [
            [
                'title' => 'Improve CV Bullet Points',
                'description' => 'Transform your job responsibilities into compelling, achievement-focused bullet points that highlight your impact and value.',
                'prompt' => 'Rewrite these job responsibilities as achievement-focused bullet points using action verbs. Include metrics where possible:\n\n[Paste your responsibilities here]',
                'example' => 'Rewrite these job responsibilities as achievement-focused bullet points using action verbs. Include metrics where possible:\n\nManaged social media accounts\nHandled customer inquiries\nOrganised team meetings\nCreated marketing materials',
                'tip' => 'Always provide specific examples and numbers for best results.'
            ],
            [
                'title' => 'Optimise for ATS',
                'description' => 'ATS (Applicant Tracking System) is software used by employers to filter and rank CVs. This prompt helps you identify keywords from job descriptions to improve your CV\'s chances of passing through ATS filters.',
                'prompt' => 'Analyse this job description and suggest keywords I should include in my CV:\n\nJob Description: [Paste job description]\n\nMy CV: [Paste relevant section]',
                'example' => 'Analyse this job description and suggest keywords I should include in my CV:\n\nJob Description: We are seeking a Marketing Manager with 5+ years of experience in digital marketing, SEO, content creation, and social media management. Must have experience with Google Analytics and email marketing campaigns.\n\nMy CV: Marketing professional with experience in creating content and managing social media accounts.',
                'tip' => 'Match keywords naturally—don\'t keyword stuff.'
            ],
            [
                'title' => 'Summarise Work Experience',
                'description' => 'Create concise, impactful summaries for each role that quickly communicate your value and achievements.',
                'prompt' => 'Create a concise 2-3 sentence professional summary for this role:\n\nJob Title: [Your title]\nCompany: [Company name]\nDuration: [Start - End]\nKey Responsibilities: [List 3-5 main responsibilities]\nAchievements: [List 2-3 key achievements]',
                'example' => 'Create a concise 2-3 sentence professional summary for this role:\n\nJob Title: Marketing Manager\nCompany: Tech Solutions Ltd\nDuration: January 2020 - Present\nKey Responsibilities: Managed social media campaigns, created content strategy, analysed marketing metrics, coordinated with design team, managed budget\nAchievements: Increased social media engagement by 150%, launched successful product campaign that generated £500K in revenue, reduced marketing costs by 20%',
                'tip' => 'Focus on impact and results, not just duties.'
            ],
            [
                'title' => 'Translate Skills to Keywords',
                'description' => 'Convert your skills into industry-standard terminology that recruiters and ATS systems recognise.',
                'prompt' => 'Suggest industry-standard keywords and phrases for these skills:\n\n[Your skills list]\n\nTarget role: [Job title you\'re applying for]',
                'example' => 'Suggest industry-standard keywords and phrases for these skills:\n\n- Making websites\n- Using Excel\n- Writing blog posts\n- Managing social media\n\nTarget role: Digital Marketing Manager',
                'tip' => 'Use terms recruiters actually search for.'
            ],
        ],
    ],
    [
        'title' => 'Cover Letter Prompts',
        'icon' => 'mail',
        'prompts' => [
            [
                'title' => 'Draft Cover Letter Opening',
                'description' => 'Create an engaging opening that immediately captures the reader\'s attention and demonstrates your genuine interest in the role.',
                'prompt' => 'Write a compelling opening paragraph for a cover letter that:\n- Shows genuine interest in [Company Name]\n- Highlights my relevant experience in [Your field]\n- Mentions [Specific company achievement or value]\n\nMy background: [Brief summary]',
                'example' => 'Write a compelling opening paragraph for a cover letter that:\n- Shows genuine interest in GreenTech Solutions\n- Highlights my relevant experience in sustainable technology\n- Mentions your recent award for innovation in renewable energy\n\nMy background: Marketing professional with 5 years of experience in tech companies, passionate about sustainability and environmental solutions.',
                'tip' => 'Personalise by researching the company first.'
            ],
            [
                'title' => 'Connect Experience to Role',
                'description' => 'Bridge the gap between your past experience and the job requirements, showing how your skills directly apply.',
                'prompt' => 'Help me connect my experience to this job requirement:\n\nJob Requirement: [Paste requirement]\n\nMy Experience: [Describe relevant experience]\n\nWrite 2-3 sentences showing the connection.',
                'example' => 'Help me connect my experience to this job requirement:\n\nJob Requirement: Experience managing cross-functional teams and delivering projects on time and within budget\n\nMy Experience: I managed a team of 5 designers and developers to launch a new website feature. We completed it 2 weeks ahead of schedule and 15% under budget by implementing agile methodologies.\n\nWrite 2-3 sentences showing the connection.',
                'tip' => 'Be specific with examples, not generic.'
            ],
            [
                'title' => 'Address Employment Gaps',
                'description' => 'Turn potential red flags into positive talking points by framing gaps as periods of growth and development.',
                'prompt' => 'Help me address this employment gap positively in a cover letter:\n\nGap Period: [Dates]\nWhat I Did: [Volunteering, courses, projects, etc.]\n\nWrite a brief, positive explanation.',
                'example' => 'Help me address this employment gap positively in a cover letter:\n\nGap Period: March 2022 - September 2022\nWhat I Did: Completed a digital marketing certification course, volunteered as a social media manager for a local charity, and worked on freelance projects for small businesses\n\nWrite a brief, positive explanation.',
                'tip' => 'Focus on growth and learning, not excuses.'
            ],
        ],
    ],
    [
        'title' => 'Interview Preparation Prompts',
        'icon' => 'microphone',
        'prompts' => [
            [
                'title' => 'Generate Practice Questions',
                'description' => 'Prepare for interviews by practicing answers to common questions specific to your target role and industry.',
                'prompt' => 'Generate 10 common interview questions for a [Job Title] role at [Company Type]. Include both behavioural and technical questions.',
                'example' => 'Generate 10 common interview questions for a Software Developer role at a tech startup. Include both behavioural and technical questions.',
                'tip' => 'Practice out loud, not just in your head.'
            ],
            [
                'title' => 'Structure STAR Answers',
                'description' => 'Use the STAR method (Situation, Task, Action, Result) to structure compelling answers to behavioural interview questions.',
                'prompt' => 'Help me structure a STAR (Situation, Task, Action, Result) answer for this question:\n\nQuestion: [Interview question]\n\nMy Experience: [Brief description of relevant situation]',
                'example' => 'Help me structure a STAR (Situation, Task, Action, Result) answer for this question:\n\nQuestion: Tell me about a time you had to deal with a difficult team member.\n\nMy Experience: I had a team member who was consistently missing deadlines and not communicating. I scheduled a one-on-one meeting to understand their challenges, discovered they were overwhelmed with workload, and worked with them to prioritise tasks and set up weekly check-ins. This improved their performance and team morale.',
                'tip' => 'Always quantify results when possible.'
            ],
            [
                'title' => 'Prepare Questions to Ask',
                'description' => 'Demonstrate your interest and engagement by preparing thoughtful questions that show you\'ve researched the role and company.',
                'prompt' => 'Suggest 5 thoughtful questions I should ask the interviewer for a [Job Title] position. Focus on:\n- Team dynamics\n- Growth opportunities\n- Company culture\n- Role expectations',
                'example' => 'Suggest 5 thoughtful questions I should ask the interviewer for a Product Manager position. Focus on:\n- Team dynamics\n- Growth opportunities\n- Company culture\n- Role expectations',
                'tip' => 'Show genuine interest, not just what you want.'
            ],
            [
                'title' => 'Practice Salary Negotiation',
                'description' => 'Prepare confident, professional responses for salary discussions that reflect your value and market research.',
                'prompt' => 'Help me prepare for salary negotiation. My research shows the range is [Range]. My experience level is [Level]. Write a professional response if they offer [Amount].',
                'example' => 'Help me prepare for salary negotiation. My research shows the range is £45,000 - £60,000. My experience level is mid-level with 4 years of experience. Write a professional response if they offer £48,000.',
                'tip' => 'Always negotiate—most employers expect it.'
            ],
        ],
    ],
    [
        'title' => 'Job Search Strategy Prompts',
        'icon' => 'search',
        'prompts' => [
            [
                'title' => 'Analyse Job Description',
                'description' => 'Break down job postings to understand requirements, identify red flags, and determine how to tailor your application effectively.',
                'prompt' => 'Analyse this job description and tell me:\n1. Key skills required\n2. Must-have vs nice-to-have qualifications\n3. Red flags or concerns\n4. How to tailor my application\n\nJob Description: [Paste full description]',
                'example' => 'Analyse this job description and tell me:\n1. Key skills required\n2. Must-have vs nice-to-have qualifications\n3. Red flags or concerns\n4. How to tailor my application\n\nJob Description: We are looking for a Marketing Manager with 5+ years of experience in digital marketing. Must have experience with SEO, content creation, and social media. Experience with paid advertising and email marketing preferred. Bachelor\'s degree required. Must be available to work evenings and weekends as needed.',
                'tip' => 'Save time by filtering out bad fits early.'
            ],
            [
                'title' => 'Research Company Culture',
                'description' => 'Gather insights about company values, culture, and recent developments to personalise your application and interview responses.',
                'prompt' => 'Based on this company\'s website and recent news, summarise:\n- Company values and culture\n- Recent achievements or challenges\n- Growth trajectory\n- What they value in employees\n\nCompany: [Name]\nWebsite: [URL]',
                'example' => 'Based on this company\'s website and recent news, summarise:\n- Company values and culture\n- Recent achievements or challenges\n- Growth trajectory\n- What they value in employees\n\nCompany: InnovateTech Solutions\nWebsite: https://www.innovatetech.com',
                'tip' => 'Use this to personalise your application.'
            ],
            [
                'title' => 'Network Outreach Message',
                'description' => 'Craft professional, authentic messages for networking that clearly communicate your purpose and respect the recipient\'s time.',
                'prompt' => 'Write a professional LinkedIn message to [Name] at [Company] asking for:\n- Brief informational interview\n- Advice on breaking into [Industry/Role]\n- Keep it under 150 words and authentic',
                'example' => 'Write a professional LinkedIn message to Sarah Johnson at TechStart Inc asking for:\n- Brief informational interview\n- Advice on breaking into product management\n- Keep it under 150 words and authentic',
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
            min-height: 120px;
        }
        .copy-button {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            z-index: 10;
        }
        .prompt-content {
            padding-right: 80px;
        }
        .copied {
            background-color: #10b981 !important;
            color: white !important;
            border-color: #10b981 !important;
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

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-6 py-4">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900 mb-1">What is ATS?</h3>
                    <p class="text-sm text-blue-800">
                        <strong>ATS (Applicant Tracking System)</strong> is software used by employers to automatically filter, rank, and manage job applications. Most companies use ATS to scan CVs for keywords, skills, and qualifications before a human recruiter ever sees them. Optimising your CV for ATS helps ensure your application passes through these initial filters and reaches the hiring manager.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="prompts" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        <?php
        // Icon SVG paths mapping
        $iconPaths = [
            'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'microphone' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
            'search' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
            'lightbulb' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
            'copy' => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z'
        ];
        ?>
        <?php foreach ($promptCategories as $category): ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="h-8 w-8 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($iconPaths[$category['icon']]); ?>"/>
                    </svg>
                    <h2 class="text-2xl font-semibold text-slate-900"><?php echo e($category['title']); ?></h2>
                </div>
                <div class="space-y-6">
                    <?php foreach ($category['prompts'] as $prompt): ?>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-2"><?php echo e($prompt['title']); ?></h3>
                            <?php if (!empty($prompt['description'])): ?>
                                <p class="text-sm text-slate-600 mb-4"><?php echo e($prompt['description']); ?></p>
                            <?php endif; ?>
                            <div class="prompt-box relative rounded-lg border border-slate-300 bg-white p-4">
                                <button
                                    class="copy-button rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition-colors"
                                    onclick="copyPrompt(this)"
                                    data-prompt="<?php echo e(htmlspecialchars($prompt['prompt'], ENT_QUOTES)); ?>"
                                    aria-label="Copy prompt"
                                >
                                    <svg class="h-3.5 w-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($iconPaths['copy']); ?>"/>
                                    </svg>
                                    Copy
                                </button>
                                <div class="prompt-content font-mono text-sm text-slate-700 whitespace-pre-wrap"><?php echo e($prompt['prompt']); ?></div>
                            </div>
                            <?php if (!empty($prompt['example'])): ?>
                                <div class="mt-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-slate-700">Example with placeholders filled in:</h4>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        <div class="font-mono text-sm text-slate-600 whitespace-pre-wrap"><?php echo e($prompt['example']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($prompt['tip'])): ?>
                                <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2">
                                    <p class="text-sm text-slate-700 flex items-start gap-2">
                                        <svg class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($iconPaths['lightbulb']); ?>"/>
                                        </svg>
                                        <span><strong class="text-blue-900">Tip:</strong> <?php echo e($prompt['tip']); ?></span>
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
