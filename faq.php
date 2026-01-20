<?php
/**
 * FAQ Page with FAQPage Schema
 */

require_once __DIR__ . '/php/helpers.php';

$faqs = [
    [
        'question' => 'What is Simple CV Builder?',
        'answer' => 'Simple CV Builder is an online platform that helps individuals create professional CVs that update in real-time and can be shared as a simple link. It\'s also designed for organisations like recruitment agencies and HR departments to manage multiple candidate CVs efficiently. You can build comprehensive CVs with sections for work experience, education, projects, skills, certifications, and more. The platform includes integrated job application tracking and supports both individual users and organisation accounts.'
    ],
    [
        'question' => 'Is Simple CV Builder free to use?',
        'answer' => 'Yes! Simple CV Builder offers a free plan that allows you to create a basic CV with essential sections. You can share your CV with a unique link and download it as a PDF. Premium plans are available for unlimited entries and additional features.'
    ],
    [
        'question' => 'How do I create my CV?',
        'answer' => 'After registering for a free account, you can start building your CV by filling in your profile information and adding sections like work experience, education, skills, and more. Your CV updates in real-time as you make changes, and you can preview it at any time.'
    ],
    [
        'question' => 'Can I share my CV online?',
        'answer' => 'Yes! Each CV gets a unique URL that you can share with employers, recruiters, or anyone else. Your CV is accessible online and updates automatically whenever you make changes. You can also control privacy settings for your CV.'
    ],
    [
        'question' => 'Can I download my CV as a PDF?',
        'answer' => 'Absolutely! You can download your CV as a PDF at any time. The PDF includes a QR code that links directly back to your online CV, making it easy for employers to access the latest version of your CV.'
    ],
    [
        'question' => 'What sections can I include in my CV?',
        'answer' => 'You can include professional summary, work experience, education, projects, skills, certifications, professional qualification equivalence, professional memberships, and interests & activities. Each section can be customised to showcase your unique background.'
    ],
    [
        'question' => 'What\'s the difference between the free and premium plans?',
        'answer' => 'The free plan allows you to create a basic CV with limited entries per section. Premium plans offer unlimited entries, priority support, and access to premium CV templates. You can upgrade or downgrade your plan at any time.'
    ],
    [
        'question' => 'How do I update my CV?',
        'answer' => 'Simply log in to your account and navigate to the section you want to update. You can add, edit, or delete entries at any time. Changes are saved automatically and reflected immediately on your public CV link.'
    ],
    [
        'question' => 'Is my data secure and private?',
        'answer' => 'Yes, we take your privacy seriously. Your data is encrypted and stored securely. You control who can see your CV through privacy settings. We never share your personal information with third parties. Read our Privacy Policy for more details.'
    ],
    [
        'question' => 'Can I customise the appearance of my CV?',
        'answer' => 'Yes! You can customise your CV header colours, choose whether to display your photo, and control various display options. Premium plans offer additional customisation options and template choices.'
    ],
    [
        'question' => 'Do I need to verify my email address?',
        'answer' => 'Yes, email verification is required to ensure account security and enable important features like password reset. You\'ll receive a verification email after registration. If you don\'t receive it, you can request a new verification email.'
    ],
    [
        'question' => 'What if I forget my password?',
        'answer' => 'You can reset your password using the "Forgot Password" link on the login page. Enter your email address and you\'ll receive instructions to reset your password securely.'
    ],
    [
        'question' => 'Can I delete my account?',
        'answer' => 'Yes, you can delete your account at any time from your profile settings. This will permanently remove all your data and CV information. Please note that this action cannot be undone.'
    ],
    [
        'question' => 'How do I contact support?',
        'answer' => 'You can contact our support team by emailing noreply@simple-job-tracker.com. Premium plan subscribers receive priority support. We typically respond within 24-48 hours.'
    ],
    [
        'question' => 'Can I use Simple CV Builder on mobile devices?',
        'answer' => 'Yes! Simple CV Builder is fully responsive and works on smartphones, tablets, and desktop computers. You can create, edit, and view your CV from any device with an internet connection.'
    ],
    [
        'question' => 'What is the job application tracker?',
        'answer' => 'The job application tracker is a built-in feature that helps you manage your entire job search from one place. You can track every job application you make, monitor your progress through the application process, set follow-up reminders, attach files like your CV and cover letters, and view statistics about your job search. It\'s fully integrated with your CV builder account and is available to all users, including those on the free plan.'
    ],
    [
        'question' => 'How do I track my job applications?',
        'answer' => 'After logging in, navigate to the "Job Applications" section in your dashboard. You can add new applications by clicking "Add New Application" and entering details like company name, job title, application date, and status. You can update the status as you progress through the process - from "Interested" to "Applied", "Interviewing", "Offered", or "Accepted". You can also add notes, set follow-up reminders, and attach files to each application.'
    ],
    [
        'question' => 'What application statuses can I use?',
        'answer' => 'You can track applications through several statuses: "Interested" for jobs you\'re considering, "Applied" for applications you\'ve submitted, "Interviewing" when you\'re in the interview stage, "Offered" when you receive a job offer, "Accepted" when you accept an offer, "Rejected" for applications that were declined, and "Withdrawn" for applications you\'ve withdrawn. This helps you see at a glance where you are with each application.'
    ],
    [
        'question' => 'Can I attach files to my job applications?',
        'answer' => 'Yes! You can attach files like your CV, cover letter, portfolio pieces, or any other documents related to each specific application. This keeps everything organised in one place, so you don\'t have to search through your email or computer files to find the documents you sent for each application.'
    ],
    [
        'question' => 'Is the job application tracker included in the free plan?',
        'answer' => 'Yes! The job application tracker is available to all users, including those on the free plan. We believe that everyone should have access to tools that help them succeed in their career journey, regardless of which plan they choose.'
    ],
    [
        'question' => 'Can I set reminders for job applications?',
        'answer' => 'Absolutely! You can set follow-up reminders for each application to ensure you never miss an important date. This is particularly useful when you\'re managing multiple applications and need to remember when to follow up with employers or prepare for interviews.'
    ],
    [
        'question' => 'How does the job application tracker integrate with my CV?',
        'answer' => 'The job application tracker is fully integrated with your CV builder. When you\'re tracking an application, you can easily access your CV to share it, download it as a PDF, or make updates. You can also attach your CV and cover letter directly to each application record. This integration means everything you need for your job search is in one place, making the process more organised and less stressful.'
    ],
    // AI CV Features FAQs
    [
        'question' => 'What are AI CV features?',
        'answer' => 'AI CV features use artificial intelligence to help you create better CVs. You can generate job-specific CV variants automatically by pasting a job description, and get AI-powered quality assessments with scores and improvement suggestions. These features help you tailor your CV to each job application and identify areas for improvement.'
    ],
    [
        'question' => 'How does AI CV rewriting work?',
        'answer' => 'AI CV rewriting analyzes a job description and your current CV, then automatically rewrites relevant sections (like your professional summary, work experience descriptions, and skills) to better match the job requirements. The AI emphasizes relevant keywords, highlights matching experiences, and maintains factual accuracy. You can review and edit the AI-generated content before saving it as a new CV variant.'
    ],
    [
        'question' => 'What is a CV variant?',
        'answer' => 'A CV variant is a customised version of your CV tailored for a specific job application. You can create multiple variants - one for each job you\'re applying to. Your master CV remains unchanged, and each variant can be edited independently. Variants are automatically linked to job applications in your tracker, making it easy to see which CV you used for each application.'
    ],
    [
        'question' => 'How does CV quality assessment work?',
        'answer' => 'CV quality assessment uses browser-based AI to analyse your CV and provide scores for overall quality, ATS compatibility, content quality, formatting, and keyword matching (when a job description is provided). The AI runs directly in your browser - no cloud services needed. You\'ll receive specific strengths, weaknesses, and actionable recommendations to improve your CV. You can assess any CV variant or your master CV at any time. Organisations or users with cloud AI accounts can also use cloud-based AI if configured.'
    ],
    [
        'question' => 'Is AI CV rewriting accurate?',
        'answer' => 'The AI maintains factual accuracy - it doesn\'t invent experiences, dates, or qualifications. However, it may need refinement to match your personal writing style. Always review AI-generated content before using it. The AI focuses on rewording and emphasizing relevant information rather than creating new content. All AI features run in your browser - no cloud services required. Cloud-based AI options are available for organisations or users who have configured them.'
    ],
    [
        'question' => 'Are AI CV features free?',
        'answer' => 'Yes! AI CV features are available to all users, including those on the free plan. All AI features run directly in your browser using browser-based AI - no cloud services, API keys, or setup required. You can generate unlimited CV variants and run quality assessments as often as you like. For organisations or users who prefer cloud-based AI (OpenAI, Anthropic, Gemini, etc.), those options are also available if configured.'
    ],
    [
        'question' => 'How do I generate an AI CV for a job application?',
        'answer' => 'Go to your Job Applications page and open any job application that has a job description. Click the "Generate AI CV" button. The AI will analyse the job description and your master CV, then create a tailored variant. You can also go to CV Variants and click "Generate AI CV" to create a variant from any job description.'
    ],
    [
        'question' => 'Can I edit AI-generated CV variants?',
        'answer' => 'Absolutely! AI-generated CV variants can be edited just like your master CV. You can modify any section, add or remove content, and customise it to your preferences. The AI provides a starting point, but you have full control over the final content.'
    ],
    [
        'question' => 'Can I upload files with my job applications?',
        'answer' => 'Yes! You can upload files (PDF, Word documents, Excel spreadsheets, text files, and images) directly to each job application. This is perfect for storing job descriptions, application materials, or any related documents. The AI can automatically read these files when generating CV variants, so you don\'t need to copy and paste job descriptions manually.'
    ],
    [
        'question' => 'How does file upload work with AI CV generation?',
        'answer' => 'When you upload a job description file (like a PDF or Word document) to a job application, the AI can automatically extract and read the content when generating your CV variant. This means you can simply upload the job posting file and click "Generate AI CV" - no need to copy and paste the job description. The AI will use the file content along with any text you\'ve entered in the job description field.'
    ],
    [
        'question' => 'What file types can I upload?',
        'answer' => 'You can upload PDF files, Word documents (.doc, .docx), Excel spreadsheets (.xls, .xlsx), plain text files (.txt), CSV files, and images (JPEG, PNG). Maximum file size is 10MB per file. You can upload multiple files per job application.'
    ],
    [
        'question' => 'Can I extract text from uploaded files?',
        'answer' => 'Yes! For uploaded files like PDFs, Word documents, and text files, you can click the "Extract Text" button to automatically populate the job description field. This is useful if you want to see or edit the extracted text before using it with AI features.'
    ],
    // Organisation FAQs
    [
        'question' => 'How do I get an organisation account?',
        'answer' => 'To create an organisation account, fill out the request form on our <a href="/organisations.php" class="text-blue-600 hover:text-blue-800 underline">For Organisations</a> page. You\'ll need to provide your organisation name, primary contact details, expected number of candidates and team members, organisation type, and any specific requirements. Once we receive your request, we\'ll review it and set up your organisation account. You\'ll then receive an invitation email with instructions to access your dashboard.'
    ],
    [
        'question' => 'What types of organisations can use Simple CV Builder?',
        'answer' => 'Simple CV Builder is designed for recruitment agencies, HR departments, employment agencies, talent acquisition teams, outplacement services, and any organisation that needs to manage multiple candidate CVs. If you manage candidates and need a centralised platform for CV management, we can help.'
    ],
    [
        'question' => 'How do I invite candidates to create their CV?',
        'answer' => 'As an organisation admin or recruiter, you can invite candidates by navigating to the "Candidates" section in your dashboard and clicking "Invite Candidate". Enter the candidate\'s email address, optionally add a personal message, and send the invitation. The candidate will receive an email with a link to create their account and start building their CV. Once they\'ve created their CV, you can view it directly from your Candidates page.'
    ],
    [
        'question' => 'Can candidates see other candidates\' CVs?',
        'answer' => 'No. Candidates can only see and edit their own CV. Privacy and security are important to us, so each candidate\'s CV is private to them. Only your organisation\'s team members can view candidate CVs, and this access is based on their role permissions (Owner, Admin, Recruiter, or Viewer).'
    ],
    [
        'question' => 'What are the different team roles and what can they do?',
        'answer' => 'Organisations have four role levels: <strong>Owner</strong> has full access including organisation settings, billing, and team management; <strong>Admin</strong> can manage candidates, team members, and organisation settings (except billing); <strong>Recruiter</strong> can invite and manage their own candidates and view all candidates; <strong>Viewer</strong> has read-only access to view candidates and their CVs. Role permissions are clearly defined to ensure appropriate access control.'
    ],
    [
        'question' => 'How do I invite team members to my organisation?',
        'answer' => 'Owners and Admins can invite team members by going to the "Team" section in your dashboard and clicking "Invite Team Member". Enter their email address, select their role (Admin, Recruiter, or Viewer), optionally add a personal message, and send the invitation. The team member will receive an email invitation to join your organisation. Note: Only Owners can invite Admins, and you must not have reached your team member limit.'
    ],
    [
        'question' => 'What happens if I reach my candidate or team member limit?',
        'answer' => 'You\'ll see notifications when you\'re approaching your limits. If you need to increase your limits, you can request a limit increase from the Settings page. Simply select whether you need to increase candidate or team member limits, enter your requested new limit, optionally provide a reason, and submit your request. A super admin will review your request and approve or deny it. You\'ll be notified once a decision has been made.'
    ],
    [
        'question' => 'Can I customise my organisation\'s branding?',
        'answer' => 'Yes! Owners and Admins can customise your organisation\'s branding from the Settings page. You can upload your organisation logo (JPEG, PNG, GIF, or WebP, max 2MB), set your primary and secondary brand colours, and configure other organisation settings. These branding elements can appear in your candidates\' CVs when they use your organisation\'s templates.'
    ],
    [
        'question' => 'Can I change my organisation\'s name or settings?',
        'answer' => 'Yes! Owners and Admins can update organisation settings including the organisation name, URL slug, logo, branding colours, and candidate settings. You can also enable or disable candidate self-registration, set default CV visibility, and require approval for self-registered candidates. All of these can be managed from the Settings page.'
    ],
    [
        'question' => 'How do I transfer ownership of my organisation?',
        'answer' => 'Organisation owners can transfer ownership to another admin from the Settings page. This is an important action that gives the new owner full control over the organisation, including billing and settings. This action cannot be undone, so make sure you trust the new owner and that they\'re ready to take on this responsibility.'
    ],
    [
        'question' => 'What is candidate self-registration?',
        'answer' => 'Candidate self-registration allows candidates to register themselves using your organisation\'s registration link, rather than requiring you to invite them individually. You can enable or disable this feature in your organisation settings. If enabled, you can also choose to require approval for self-registered candidates before they can complete their CV, giving you control over who has access.'
    ],
    [
        'question' => 'How do I contact support for organisation-related issues?',
        'answer' => 'For organisation account setup, limit increase requests, or organisation-specific support, please email <a href="mailto:noreply@simple-job-tracker.com" class="text-blue-600 hover:text-blue-800 underline">noreply@simple-job-tracker.com</a>. Include your organisation name in your email for faster assistance. We typically respond within 24-48 hours.'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Frequently Asked Questions | Simple CV Builder',
        'metaDescription' => 'Find answers to common questions about Simple CV Builder, including how to create your CV, pricing, features, and more.',
        'canonicalUrl' => APP_URL . '/faq.php',
        'structuredDataType' => 'faq',
        'structuredData' => ['faqs' => $faqs],
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" role="main">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h1>
                <p class="text-lg text-gray-600">
                    Everything you need to know about Simple CV Builder
                </p>
            </div>

            <div class="space-y-6">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <button
                            type="button"
                            class="w-full px-6 py-5 text-left flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                            aria-expanded="false"
                            aria-controls="faq-answer-<?php echo $index; ?>"
                            data-faq-toggle
                            data-target="faq-answer-<?php echo $index; ?>"
                        >
                            <h2 class="text-lg font-semibold text-gray-900 pr-8">
                                <?php echo e($faq['question']); ?>
                            </h2>
                            <svg
                                class="flex-shrink-0 h-5 w-5 text-gray-500 transform transition-transform duration-300 ease-in-out"
                                data-faq-icon
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            id="faq-answer-<?php echo $index; ?>"
                            class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out"
                            data-faq-content
                        >
                            <div class="px-6 pt-0 pb-6">
                                <div class="text-gray-700 leading-relaxed">
                                    <?php echo $faq['answer']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Still have questions?</h3>
                <p class="text-gray-700 mb-4">
                    Can't find the answer you're looking for? We're here to help!
                </p>
                <a
                    href="mailto:noreply@simple-job-tracker.com"
                    class="inline-block bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Contact Support
                </a>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <?php
    // Include auth modals for login/register functionality
    $error = getFlash('error') ?: null;
    $success = getFlash('success') ?: null;
    $needsVerification = getFlash('needs_verification') ?: false;
    $verificationEmail = getFlash('verification_email') ?: null;
    $oldLoginEmail = getFlash('old_login_email') ?: null;
    partial('auth-modals', [
        'error' => $error,
        'success' => $success,
        'needsVerification' => $needsVerification,
        'verificationEmail' => $verificationEmail,
        'oldLoginEmail' => $oldLoginEmail,
    ]);
    ?>

    <style>
        .faq-content {
            max-height: 0;
            opacity: 0;
            transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out, padding-top 0.3s ease-in-out, padding-bottom 0.3s ease-in-out;
            overflow: hidden;
        }
        .faq-content.open {
            max-height: 2000px; /* Large enough for longest FAQ answer */
            opacity: 1;
            padding-top: 0.5rem;
        }
        .faq-content > div {
            transition: opacity 0.2s ease-in-out 0.1s;
        }
        .faq-content:not(.open) > div {
            opacity: 0;
        }
        .faq-content.open > div {
            opacity: 1;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqButtons = document.querySelectorAll('[data-faq-toggle]');

            faqButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const target = document.getElementById(targetId);
                    const icon = this.querySelector('[data-faq-icon]');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';

                    // Close all other FAQs with smooth animation
                    faqButtons.forEach(function(otherButton) {
                        if (otherButton !== button) {
                            const otherTargetId = otherButton.getAttribute('data-target');
                            const otherTarget = document.getElementById(otherTargetId);
                            const otherIcon = otherButton.querySelector('[data-faq-icon]');

                            if (otherTarget && otherTarget.classList.contains('open')) {
                                otherTarget.classList.remove('open');
                                otherButton.setAttribute('aria-expanded', 'false');
                                if (otherIcon) {
                                    otherIcon.classList.remove('rotate-180');
                                }
                            }
                        }
                    });

                    // Toggle current FAQ with smooth animation
                    if (target) {
                        if (isExpanded) {
                            target.classList.remove('open');
                            this.setAttribute('aria-expanded', 'false');
                            if (icon) {
                                icon.classList.remove('rotate-180');
                            }
                        } else {
                            target.classList.add('open');
                            this.setAttribute('aria-expanded', 'true');
                            if (icon) {
                                icon.classList.add('rotate-180');
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
