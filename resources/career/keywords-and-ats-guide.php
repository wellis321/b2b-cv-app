<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = 'CV Keywords and ATS: A Complete Guide';
$metaDescription = 'Learn how Applicant Tracking Systems (ATS) work, why keywords matter in your CV, and how to optimise your CV to pass ATS screening and reach human recruiters.';
$canonicalUrl = APP_URL . '/resources/career/keywords-and-ats-guide.php';

$sections = [
    [
        'id' => 'what-is-ats',
        'title' => 'What is an Applicant Tracking System (ATS)?',
        'image' => 'static/images/resources-images/placeholder-ats-system.png',
        'image_alt' => 'Illustration showing an Applicant Tracking System processing CVs',
        'content' => [
            'An Applicant Tracking System, or ATS, is software used by employers to manage and filter job applications. When you submit your CV online, it often goes through an ATS before a human recruiter ever sees it.',
            'ATS software helps employers handle the high volume of applications they receive. For popular positions, companies might receive hundreds or even thousands of CVs. Manually reviewing each one would be impossible, so ATS systems automate the initial screening process.',
            'The ATS scans your CV for specific information: your skills, work experience, education, and other relevant details. It then ranks your application based on how well your CV matches the job requirements. Only the highest-ranking CVs typically make it through to human review.',
            'Understanding how ATS works is crucial because research shows that up to 75% of CVs are rejected by ATS before a human ever sees them. If your CV isn\'t optimised for ATS, you might be missing out on opportunities even if you\'re perfectly qualified for the role.',
        ],
    ],
    [
        'id' => 'how-ats-works',
        'title' => 'How Does an ATS Work?',
        'image' => 'static/images/resources-images/placeholder-ats-process.png',
        'image_alt' => 'Diagram showing how ATS systems parse and rank CVs',
        'content' => [
            'When you submit your CV through an online application system, the ATS performs several key functions:',
        ],
        'subsections' => [
            [
                'title' => '1. Parsing and Extraction',
                'content' => [
                    'The ATS reads your CV and extracts information into structured fields. It identifies your name, contact details, work history, education, skills, and other relevant data. This is why using standard section headings (like "Work Experience" or "Education") is important – the ATS needs to recognise where to find information.',
                ],
            ],
            [
                'title' => '2. Keyword Matching',
                'content' => [
                    'The ATS compares keywords in your CV against keywords in the job description. It looks for matches in skills, qualifications, job titles, and responsibilities. The more relevant keywords you include, the higher your CV scores.',
                    'This is where strategic keyword usage becomes critical. The ATS doesn\'t understand context or nuance – it simply counts matches. If the job description mentions "project management" and "budget management" but your CV uses different terminology, you might score lower even if you have relevant experience.',
                ],
            ],
            [
                'title' => '3. Ranking and Filtering',
                'content' => [
                    'Based on keyword matches and other factors, the ATS ranks your CV against other applicants. Recruiters typically review only the top-ranked CVs, often just the top 10-20% of applications.',
                    'Some ATS systems also use filters to automatically reject CVs that don\'t meet minimum requirements, such as specific qualifications, years of experience, or required skills.',
                ],
            ],
        ],
    ],
    [
        'id' => 'why-keywords-matter',
        'title' => 'Why Keywords Matter in Your CV',
        'image' => 'static/images/resources-images/placeholder-keywords.png',
        'image_alt' => 'Visual representation of keywords being highlighted in a CV',
        'content' => [
            'Keywords are specific words or phrases that appear in job descriptions and that recruiters use to identify qualified candidates. They represent the skills, qualifications, tools, technologies, and experience that employers are looking for.',
            'Keywords matter because they\'re the primary way ATS systems determine if your CV is relevant to a position. When an ATS scans your CV, it\'s essentially asking: "Does this person have the skills and experience mentioned in the job description?"',
            'Using the right keywords helps your CV:',
        ],
        'list' => [
            'Pass ATS screening and reach human recruiters',
            'Rank higher in ATS scoring systems',
            'Demonstrate alignment with job requirements',
            'Show industry knowledge and terminology',
            'Stand out from other applicants who haven\'t optimised their CVs',
        ],
        'tip' => [
            'title' => 'Important Note',
            'body' => 'Keywords should be used naturally throughout your CV, not just stuffed into a skills section. The best keyword optimisation integrates relevant terms into your work experience descriptions, professional summary, and other sections.',
        ],
    ],
    [
        'id' => 'finding-keywords',
        'title' => 'How to Find the Right Keywords',
        'image' => 'static/images/resources-images/placeholder-finding-keywords.png',
        'image_alt' => 'Person analyzing a job description to identify keywords',
        'content' => [
            'The best source for keywords is the job description itself. Employers tell you exactly what they\'re looking for – you just need to listen carefully and incorporate their language into your CV.',
        ],
        'subsections' => [
            [
                'title' => '1. Analyse the Job Description',
                'content' => [
                    'Read the job description carefully and identify:',
                ],
                'list' => [
                    'Required skills (both technical and soft skills)',
                    'Preferred qualifications or certifications',
                    'Software, tools, or technologies mentioned',
                    'Job titles or role types',
                    'Industry-specific terminology',
                    'Key responsibilities or tasks',
                ],
            ],
            [
                'title' => '2. Look for Patterns',
                'content' => [
                    'Pay attention to words that appear multiple times – these are likely high-priority keywords. Also note any specific phrases or terminology that\'s unique to the industry or role.',
                ],
            ],
            [
                'title' => '3. Research Similar Roles',
                'content' => [
                    'Look at other job postings for similar positions to identify common keywords across the industry. This helps you understand standard terminology and ensures you\'re using language that recruiters expect.',
                ],
            ],
            [
                'title' => '4. Use Industry Resources',
                'content' => [
                    'Professional associations, industry publications, and job boards often publish lists of common keywords for specific fields. These can be valuable resources for understanding what terms are important in your industry.',
                ],
            ],
        ],
    ],
    [
        'id' => 'optimising-your-cv',
        'title' => 'How to Optimise Your CV with Keywords',
        'image' => 'static/images/resources-images/placeholder-cv-optimisation.png',
        'image_alt' => 'CV being optimized with keywords throughout different sections',
        'content' => [
            'Once you\'ve identified relevant keywords, the next step is incorporating them naturally throughout your CV. Remember: the goal is to use keywords in a way that feels authentic and demonstrates your actual experience.',
        ],
        'subsections' => [
            [
                'title' => '1. Professional Summary',
                'content' => [
                    'Include 3-5 relevant keywords in your professional summary. This section appears at the top of your CV and is often heavily weighted by ATS systems. Use keywords that represent your core competencies and align with your target roles.',
                ],
                'example' => [
                    'title' => 'Example',
                    'body' => 'Instead of: "Experienced professional seeking new opportunities"<br>Use: "Project manager with 8 years of experience in agile methodologies, budget management, and cross-functional team leadership"',
                ],
            ],
            [
                'title' => '2. Work Experience Descriptions',
                'content' => [
                    'Naturally incorporate keywords into your bullet points. Describe your achievements using the same terminology found in job descriptions. This shows both ATS systems and human recruiters that your experience aligns with their needs.',
                ],
                'example' => [
                    'title' => 'Example',
                    'body' => 'Instead of: "Managed projects and worked with teams"<br>Use: "Led agile software development projects, coordinating cross-functional teams of 12+ developers and stakeholders to deliver products on time and within budget"',
                ],
            ],
            [
                'title' => '3. Skills Section',
                'content' => [
                    'Create a dedicated skills section that includes both technical and soft skills. Use exact terminology from job descriptions where possible. Group skills by category (Technical Skills, Software, Certifications) for better ATS parsing.',
                ],
            ],
            [
                'title' => '4. Job Titles',
                'content' => [
                    'If your official job title doesn\'t match common industry terminology, consider adding a parenthetical clarification. For example: "Business Analyst (Data Analyst)" or "Software Developer (Full Stack Developer)". This helps ATS systems recognise your role even if your company uses non-standard titles.',
                ],
            ],
            [
                'title' => '5. Education and Certifications',
                'content' => [
                    'Include relevant qualifications using standard terminology. If you have certifications, use their full official names as they appear on certificates, as ATS systems often search for exact certification names.',
                ],
            ],
        ],
    ],
    [
        'id' => 'common-mistakes',
        'title' => 'Common Keyword Mistakes to Avoid',
        'image' => 'static/images/resources-images/placeholder-mistakes.png',
        'image_alt' => 'Illustration showing common CV keyword mistakes',
        'content' => [
            'While keywords are important, there are several mistakes that can actually hurt your chances:',
        ],
        'subsections' => [
            [
                'title' => 'Keyword Stuffing',
                'content' => [
                    'Repeating keywords excessively or unnaturally throughout your CV is called "keyword stuffing." This makes your CV read poorly and can actually trigger ATS filters designed to catch spam. Use keywords naturally and in context.',
                ],
                'warning' => [
                    'title' => 'What Not to Do',
                    'body' => 'Don\'t write: "Project manager project management project manager with project management experience in project management." Instead, vary your language while still including relevant keywords.',
                ],
            ],
            [
                'title' => 'Using Irrelevant Keywords',
                'content' => [
                    'Including keywords that don\'t match your actual experience is misleading and can backfire during interviews. Only include keywords that accurately represent your skills and experience.',
                ],
            ],
            [
                'title' => 'Ignoring Synonyms',
                'content' => [
                    'Job descriptions might use different terms for the same concept. For example, one might say "customer service" while another says "client relations." Include both terms if they\'re relevant to show you understand the full scope of the role.',
                ],
            ],
            [
                'title' => 'Forgetting About Formatting',
                'content' => [
                    'Some ATS systems struggle with complex formatting, tables, graphics, or unusual fonts. Stick to standard formatting and simple layouts to ensure the ATS can properly parse your keywords.',
                ],
            ],
        ],
    ],
    [
        'id' => 'ats-friendly-formatting',
        'title' => 'ATS-Friendly CV Formatting',
        'image' => 'static/images/resources-images/placeholder-formatting.png',
        'image_alt' => 'Comparison of ATS-friendly and unfriendly CV formatting',
        'content' => [
            'Beyond keywords, your CV\'s format also affects how well ATS systems can read and parse it. Follow these formatting guidelines:',
        ],
        'list' => [
            'Use standard section headings: "Work Experience," "Education," "Skills," "Certifications"',
            'Avoid complex tables, graphics, or images that ATS systems can\'t read',
            'Use simple, professional fonts (Arial, Calibri, Times New Roman)',
            'Save your CV as a .docx or .pdf file (check the job posting for preferences)',
            'Use standard date formats (e.g., "January 2020 – Present" or "2020-01 – 2023-12")',
            'Avoid headers and footers, as some ATS systems ignore them',
            'Don\'t use text boxes or columns, as they can confuse ATS parsing',
            'Ensure consistent formatting throughout your CV',
        ],
        'tip' => [
            'title' => 'Pro Tip',
            'body' => 'Many CV builders, including this platform, automatically format your CV in an ATS-friendly way. However, if you\'re creating your CV manually, test it by copying and pasting the text into a plain text editor – if it\'s still readable, it\'s likely ATS-friendly.',
        ],
    ],
    [
        'id' => 'tailoring-for-each-role',
        'title' => 'Tailoring Keywords for Each Application',
        'image' => 'static/images/resources-images/placeholder-tailoring.png',
        'image_alt' => 'CV being customized for different job applications',
        'content' => [
            'While it\'s tempting to create one CV and send it to multiple employers, tailoring your CV for each specific role significantly improves your chances of passing ATS screening.',
            'Each job description is unique, and the keywords that matter for one role might be less important for another. By customising your CV for each application, you ensure that:',
        ],
        'list' => [
            'You include the most relevant keywords for that specific role',
            'Your CV scores higher in ATS ranking systems',
            'You demonstrate genuine interest in the specific position',
            'Your experience is presented in the most relevant way',
        ],
        'content_after' => [
            'The good news is that tailoring doesn\'t require a complete rewrite. Focus on:',
        ],
        'list_after' => [
            'Updating your professional summary with role-specific keywords',
            'Reordering or emphasising relevant work experience',
            'Adjusting your skills section to highlight the most relevant abilities',
            'Using terminology from the job description throughout your CV',
        ],
    ],
    [
        'id' => 'measuring-success',
        'title' => 'How to Know If Your CV is ATS-Optimised',
        'image' => 'static/images/resources-images/placeholder-success.png',
        'image_alt' => 'CV passing ATS screening and reaching recruiters',
        'content' => [
            'While you can\'t always know exactly how an ATS will score your CV, there are several indicators that suggest your CV is well-optimised:',
        ],
        'list' => [
            'Your CV includes most keywords from the job description',
            'You\'re getting interviews for roles you\'re qualified for',
            'Your CV uses standard section headings and formatting',
            'Keywords appear naturally throughout your content, not just in one section',
            'You\'ve tailored your CV for each specific application',
        ],
        'content_after' => [
            'Remember: ATS optimisation is just the first step. Your CV still needs to impress human recruiters once it passes the initial screening. Focus on creating content that works for both ATS systems and human readers.',
        ],
    ],
    [
        'id' => 'summary',
        'title' => 'Key Takeaways',
        'content' => [
            'Understanding ATS and keyword optimisation is essential for modern job searching. Here\'s what to remember:',
        ],
        'list' => [
            'ATS systems filter CVs before human review, so optimisation matters',
            'Keywords from job descriptions are your best guide for what to include',
            'Use keywords naturally throughout your CV, not just in one section',
            'Tailor your CV for each application to maximise keyword relevance',
            'Maintain ATS-friendly formatting while keeping your CV readable for humans',
            'Focus on accuracy – only include keywords that match your actual experience',
        ],
        'content_after' => [
            'By following these guidelines, you\'ll significantly improve your chances of passing ATS screening and reaching the interview stage. Remember, the goal isn\'t to trick the system – it\'s to help the ATS understand how well your qualifications match the role.',
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
<body class="bg-slate-50">
    <?php partial('header'); ?>

    <article class="relative isolate overflow-hidden bg-gradient-to-b from-indigo-50 via-white to-white px-6 pt-16 pb-24 sm:px-6 sm:pt-24 sm:pb-32 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl">
                <?php echo e($pageTitle); ?>
            </h1>
            <p class="mt-6 text-lg leading-8 text-slate-600">
                <?php echo e($metaDescription); ?>
            </p>
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
                        <?php
                        // For static article images, generate responsive URLs based on naming convention
                        // Only include srcset entries for variants that actually exist
                        $imageBasePath = dirname($encodedImagePath);
                        $imageFileName = basename($encodedImagePath);
                        $pathInfo = pathinfo($imageFileName);
                        $baseName = $pathInfo['filename'];
                        $ext = $pathInfo['extension'] ?? 'png';
                        
                        // Get full path to original image for checking variant existence
                        $originalFullPath = $_SERVER['DOCUMENT_ROOT'] . str_replace('%20', ' ', $encodedImagePath);
                        
                        // Generate responsive image URLs (only for variants that exist)
                        $responsiveSizes = [
                            'thumb' => ['width' => 150, 'height' => 150],
                            'small' => ['width' => 400, 'height' => 400],
                            'medium' => ['width' => 800, 'height' => 800],
                            'large' => ['width' => 1200, 'height' => 1200]
                        ];
                        
                        $srcsetParts = [];
                        foreach ($responsiveSizes as $sizeName => $dimensions) {
                            $responsiveFileName = $baseName . '_' . $sizeName . '.' . $ext;
                            $responsiveFullPath = dirname($originalFullPath) . '/' . str_replace('%20', ' ', $responsiveFileName);
                            $responsivePath = $imageBasePath . '/' . $responsiveFileName;
                            
                            // Only add to srcset if the file actually exists
                            if (file_exists($responsiveFullPath)) {
                                $srcsetParts[] = $responsivePath . ' ' . $dimensions['width'] . 'w';
                            }
                        }
                        $srcset = implode(', ', $srcsetParts);
                        $sizesAttr = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 800px';
                        ?>
                        <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm lg:w-5/12">
                            <img src="<?php echo e($encodedImagePath); ?>"
                                 <?php if (!empty($srcset)): ?>
                                     srcset="<?php echo e($srcset); ?>"
                                     sizes="<?php echo e($sizesAttr); ?>"
                                 <?php endif; ?>
                                 alt="<?php echo e($imageAlt); ?>"
                                 class="h-80 w-full object-cover" 
                                 loading="lazy"
                                 width="800"
                                 height="600">
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

                    <?php if (!empty($section['list'])): ?>
                        <ul class="mt-4 space-y-2 list-disc pl-6">
                            <?php foreach ($section['list'] as $item): ?>
                                <li><?php echo e($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($section['tip'])): ?>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">
                            <strong><?php echo e($section['tip']['title']); ?>:</strong> <?php echo e($section['tip']['body']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['warning'])): ?>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-800">
                            <strong><?php echo e($section['warning']['title']); ?>:</strong> <?php echo e($section['warning']['body']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['example'])): ?>
                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-base text-blue-800">
                            <strong><?php echo e($section['example']['title']); ?>:</strong><br>
                            <span class="text-sm"><?php echo $section['example']['body']; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['subsections'])): ?>
                        <div class="mt-6 space-y-8">
                            <?php foreach ($section['subsections'] as $subsection): ?>
                                <div>
                                    <h3 class="text-xl font-semibold text-slate-900 mb-3"><?php echo e($subsection['title']); ?></h3>
                                    <?php if (!empty($subsection['content'])): ?>
                                        <?php foreach ($subsection['content'] as $paragraph): ?>
                                            <p class="mb-3"><?php echo e($paragraph); ?></p>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($subsection['list'])): ?>
                                        <ul class="mt-3 space-y-2 list-disc pl-6">
                                            <?php foreach ($subsection['list'] as $item): ?>
                                                <li><?php echo e($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <?php if (!empty($subsection['tip'])): ?>
                                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">
                                            <strong><?php echo e($subsection['tip']['title']); ?>:</strong> <?php echo e($subsection['tip']['body']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($subsection['warning'])): ?>
                                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-800">
                                            <strong><?php echo e($subsection['warning']['title']); ?>:</strong> <?php echo e($subsection['warning']['body']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($subsection['example'])): ?>
                                        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-base text-blue-800">
                                            <strong><?php echo e($subsection['example']['title']); ?>:</strong><br>
                                            <span class="text-sm"><?php echo $subsection['example']['body']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['content_after'])): ?>
                        <div class="mt-4">
                            <?php foreach ($section['content_after'] as $paragraph): ?>
                                <p><?php echo e($paragraph); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['list_after'])): ?>
                        <ul class="mt-4 space-y-2 list-disc pl-6">
                            <?php foreach ($section['list_after'] as $item): ?>
                                <li><?php echo e($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </section>

    <?php partial('footer'); ?>
</body>
</html>

