<?php
/**
 * Job Application Tracker Features
 * Documentation and features page for the job application tracking system
 */

require_once __DIR__ . '/php/helpers.php';

$pageTitle = 'Free Job Application Tracker UK | Track Applications Online';
$canonicalUrl = APP_URL . '/job-applications-features.php';
$img = function($id, $w = 800) { return 'https://images.unsplash.com/photo-' . $id . '?w=' . $w . '&q=80'; };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Free job application tracker UK. Save jobs from any website, track application status, set follow-up reminders. Manage your entire job search in one place.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content">
        <!-- Hero Section -->
        <section class="relative min-h-[50vh] flex flex-col justify-center bg-gray-900 text-white overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1521737604893-d128efb9b3fb?w=1920&q=80');" aria-hidden="true"></div>
            <div class="absolute inset-0 bg-gray-900/70" aria-hidden="true"></div>
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <span class="inline-flex items-center rounded-full bg-green-500/90 px-4 py-1.5 text-sm font-semibold text-white shadow-sm">Save jobs in one click</span>
                <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl text-white">
                    Free Job Application Tracker - Manage Applications Online
                </h1>
                <p class="mt-6 text-xl text-gray-200 max-w-2xl mx-auto leading-relaxed">
                    Track and manage all your job applications in one place. <strong class="text-white">Save job listings from any website in one click</strong>—then fill in the details later. Never lose track of where you've applied, set priorities and deadlines, and land your next role.
                </p>
                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="/job-applications.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-blue-700 transition-colors">
                            Open Job Applications
                        </a>
                    <?php else: ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-blue-700 transition-colors">
                            Create Free Account
                        </button>
                    <?php endif; ?>
                    <a href="#save-jobs" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors backdrop-blur-sm">
                        Save jobs from anywhere
                    </a>
                </div>
            </div>
        </section>

        <!-- CV Features Section - Rich Promotion - MOVED TO TOP -->
        <section class="py-24 bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="inline-flex items-center rounded-full bg-indigo-100 text-indigo-800 px-4 py-1.5 text-sm font-semibold mb-4">Core Feature</span>
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Your CV, Your Way
                    </h2>
                    <p class="mt-6 text-xl text-gray-700 max-w-3xl mx-auto leading-relaxed">
                        Build a professional CV that stands out. <strong>Personalise every detail</strong>, choose from templates, share your unique online CV link, and export PDFs with QR codes. Your CV is more than a document—it's your professional presence.
                    </p>
                </div>

                <!-- Unique Online CV - Rich Section -->
                <div class="mb-20">
                    <div class="bg-white rounded-2xl border-2 border-indigo-200 shadow-xl overflow-hidden">
                        <div class="grid lg:grid-cols-2 gap-0">
                            <div class="p-10 lg:p-12">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-indigo-500 text-white">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </div>
                                    <h3 class="text-3xl font-bold text-gray-900">Unique Online CV</h3>
                                </div>
                                <p class="text-lg text-gray-700 mb-6">
                                    Every account gets a unique CV link: <code class="bg-indigo-50 px-3 py-1.5 rounded-lg text-base font-mono text-indigo-700 font-semibold">https://simple-cv-builder.com/cv/@your-username</code>. Share this link anywhere—email signatures, LinkedIn profiles, social media, business cards. Your CV is always up to date; update once and everyone sees the latest version instantly.
                                </p>
                                <div class="space-y-4 mb-8">
                                    <div class="flex items-start gap-3">
                                        <svg class="h-6 w-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-1">Always Current</h4>
                                            <p class="text-sm text-gray-600">No outdated PDFs to resend. Update your CV once, and everyone with your link sees the latest version instantly.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <svg class="h-6 w-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-1">Works Everywhere</h4>
                                            <p class="text-sm text-gray-600">Share via email, LinkedIn, social media, business cards. Add it to your email signature and it's always current.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <svg class="h-6 w-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-1">Mobile-Optimised</h4>
                                            <p class="text-sm text-gray-600">Looks great on any device—phone, tablet, desktop. Employers can view it anywhere, anytime.</p>
                                        </div>
                                    </div>
                                </div>
                                <a href="/online-cv-username.php" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-indigo-700 transition-colors">
                                    Learn about online CVs →
                                </a>
                            </div>
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-10 lg:p-12 flex items-center">
                                <div class="w-full">
                                    <div class="bg-white rounded-xl border-2 border-indigo-200 p-8 shadow-lg">
                                        <p class="text-sm text-gray-600 mb-4 font-medium">Example CV link:</p>
                                        <code class="block text-xl font-mono text-indigo-700 break-all bg-indigo-50 px-4 py-3 rounded-lg">https://simple-cv-builder.com/cv/@simple-cv-example</code>
                                        <a href="/cv/@simple-cv-example" target="_blank" class="mt-6 inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium">
                                            View example CV →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates & Personalisation - Rich Grid -->
                <div class="grid lg:grid-cols-2 gap-8 mb-12">
                    <!-- Professional Templates -->
                    <div class="bg-white rounded-xl border-2 border-pink-200 p-8 shadow-lg">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-pink-500 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Professional Templates</h3>
                        </div>
                        <p class="text-gray-700 mb-6">
                            Choose from professional CV templates designed to impress. Free plan includes the Minimal template. Upgrade to Pro for premium templates with customisable colours—create different versions for different opportunities while maintaining one master CV online.
                        </p>
                        <ul class="space-y-3 text-gray-700 mb-6">
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-pink-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>ATS-friendly:</strong> All templates designed to pass Applicant Tracking Systems</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-pink-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>Customisable colours:</strong> Match your personal brand or industry preferences</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-pink-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>Switch anytime:</strong> Change templates without losing your content</span>
                            </li>
                        </ul>
                        <a href="/cv-templates-feature.php" class="inline-flex items-center gap-2 rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700 transition-colors">
                            Explore templates →
                        </a>
                    </div>

                    <!-- Personalise Everything -->
                    <div class="bg-white rounded-xl border-2 border-purple-200 p-8 shadow-lg">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-purple-500 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Personalise Everything</h3>
                        </div>
                        <p class="text-gray-700 mb-6">
                            Full control over your CV content. With Pro plans, drag-and-drop to reorder work experience, select which sections appear in PDFs, and customise templates. Create different versions for different opportunities while maintaining one master CV online.
                        </p>
                        <ul class="space-y-3 text-gray-700 mb-6">
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>Drag-and-drop reordering:</strong> Put your most relevant experience first</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>Select sections for PDFs:</strong> Choose what appears in each export</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>Customise templates:</strong> Match your style and industry</span>
                            </li>
                        </ul>
                        <a href="/tailor-cv-content.php" class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700 transition-colors">
                            Learn about personalisation →
                        </a>
                    </div>
                </div>

                <!-- QR Codes - Rich Section -->
                <div class="bg-white rounded-xl border-2 border-gray-200 p-10 shadow-lg">
                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gray-600 text-white">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">QR Codes in PDFs</h3>
                            </div>
                            <p class="text-lg text-gray-700 mb-6">
                                Optionally include a QR code in your PDF exports that links back to your online CV. Perfect for printed CVs, networking events, and email attachments. Employers can scan to view your always-current online CV—even if they have an old PDF.
                            </p>
                            <div class="space-y-4 mb-8">
                                <div class="flex items-start gap-3">
                                    <svg class="h-6 w-6 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-1">Your Choice</h4>
                                        <p class="text-sm text-gray-600">Choose to include a QR code when exporting your PDF—just check the option before generating.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <svg class="h-6 w-6 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-1">Bridge Print & Digital</h4>
                                        <p class="text-sm text-gray-600">Best of both worlds—print PDFs for interviews, QR codes link to your online CV.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <svg class="h-6 w-6 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-1">Perfect for Networking</h4>
                                        <p class="text-sm text-gray-600">Print PDFs with QR codes for business cards or handouts at events.</p>
                                    </div>
                                </div>
                            </div>
                            <a href="/qr-codes-pdf.php" class="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition-colors">
                                Learn about QR codes →
                            </a>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 border-2 border-gray-200">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-4 font-medium">Optional QR code in PDFs:</p>
                                <div class="bg-white rounded-lg p-6 inline-block border-2 border-gray-300">
                                    <?php
                                    $exampleCvUrl = 'https://simple-cv-builder.com/cv/@simple-cv-example';
                                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&margin=0&color=374151&bgcolor=ffffff&data=' . urlencode($exampleCvUrl);
                                    ?>
                                    <img src="<?php echo e($qrCodeUrl); ?>" alt="QR Code linking to example CV" class="w-48 h-48 mx-auto mb-4" style="image-rendering: crisp-edges;" width="200" height="200" />
                                    <p class="text-xs text-gray-600 mb-2">Scan to view online CV</p>
                                    <a href="/cv/@simple-cv-example" target="_blank" class="text-xs text-gray-500 hover:text-gray-700 underline">View example CV →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Save Jobs Section - Rich Content -->
        <section id="save-jobs" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch">
                    <div class="flex flex-col">
                        <span class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-3 py-1 text-sm font-semibold mb-4">Most Popular</span>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Save jobs from anywhere
                        </h2>
                        <p class="mt-4 text-lg text-gray-600">
                            See a job you like? Don't lose it. Save job listings to your list in <strong>one click</strong> from any website—Indeed, LinkedIn, company careers pages, job boards. No copy-paste, no leaving the page.
                        </p>
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg mb-6">
                            <p class="text-sm text-blue-800 font-medium mb-2">Two ways to save jobs</p>
                            <p class="text-xs text-blue-700">
                                <strong>Quick add from link:</strong> No extension needed! Go to your job list, click "Quick add from link", paste the job URL, and save. Works in any browser.<br>
                                <strong>Browser extension:</strong> One-click save from any job page without leaving the page. Download the extension and configure with your save token.
                            </p>
                        </div>
                        <div class="mt-8 space-y-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Browser Extension</h3>
                                    <p class="text-sm text-gray-600">Click the extension icon on any job page. The link and title are saved instantly—no new tab, no copy-paste.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Quick Add from Link</h3>
                                    <p class="text-sm text-gray-600">No extension needed! Paste a job URL directly in your job list. Perfect if you don't want to install an extension or use a different browser.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <div>
                                    <h3 class="font-semibold text-gray-900">LinkedIn Integration</h3>
                                    <p class="text-sm text-gray-600">Our extension automatically extracts job titles from LinkedIn job pages—no more generic "LinkedIn" titles.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8">
                            <a href="/save-job-from-anywhere.php" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-green-700 transition-colors">
                                Learn how to save jobs →
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <img src="<?php echo e($img('1586281380349-632531db7ed4', 800)); ?>" alt="Save jobs from any website" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Tracking Features - Grouped -->
        <section id="features" class="bg-gray-50 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Core Tracking Features
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        Everything you need to track and manage your job applications effectively.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <a href="/track-all-applications.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-blue-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-blue-200 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-blue-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-blue-700">Track All Applications</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Keep track of every job application in one centralised dashboard. Never lose track of where you've applied or what stage each application is at.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">Learn more →</span>
                    </a>

                    <a href="/status-tracking.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-purple-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-purple-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-purple-700">Status Tracking</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Monitor your progress from initial interest through to offer. Track statuses like Applied, Interviewing, Offered, Accepted, or Rejected.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-purple-600 group-hover:text-purple-700">Learn more →</span>
                    </a>

                    <a href="/follow-up-dates.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-orange-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-orange-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-orange-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-orange-700">Follow-Up & Closing Dates</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Set a follow-up or closing date for each application. See upcoming dates at a glance so you can plan when to check in with employers or prepare for deadlines.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-orange-600 group-hover:text-orange-700">Learn more →</span>
                    </a>

                    <a href="/interview-tracking.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-violet-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-violet-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-violet-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-violet-700">Interview Tracking</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Track whether you've had an interview for each application and move status through stages. Use notes to record interview feedback and next steps.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-violet-600 group-hover:text-violet-700">Learn more →</span>
                    </a>

                    <a href="/application-notes.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-slate-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-slate-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-slate-700">Application Notes</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Add detailed notes to each application. Record important details about the role, company culture, interview feedback, and next steps.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-slate-600 group-hover:text-slate-700">Learn more →</span>
                    </a>

                    <a href="/search-filter.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-blue-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-blue-200 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-blue-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-blue-700">Quick Search & Filter</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Quickly find applications by company name, job title, or status. Filter your applications to focus on active opportunities or review your history.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">Learn more →</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- AI-Powered Features - Rich Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 px-4 py-1.5 text-sm font-semibold mb-4">100% FREE</span>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        AI-Powered Features
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        Powerful AI features powered by <strong>free Browser AI</strong>—no API keys, no costs, runs directly in your browser. Generate CV variants, extract keywords, create cover letters, and more—all completely free.
                    </p>
                </div>

                <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch mb-12">
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Free Browser AI Integration</h3>
                        <p class="text-gray-600 mb-6">
                            All AI features run directly in your browser using Browser AI technology. No cloud services, no API keys, no configuration required—it works immediately for all users. Generate unlimited CV variants, run quality assessments, extract keywords, and create cover letters—all completely free.
                        </p>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>100% Free Forever</strong> — No API costs, no usage limits, no hidden fees</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>100% Private</strong> — All processing happens locally in your browser</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span><strong>No Setup Required</strong> — Works immediately, no configuration needed</span>
                            </li>
                        </ul>
                        <div class="mt-6">
                            <a href="/browser-ai-free.php" class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-purple-700 transition-colors">
                                Learn about Browser AI →
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <img src="<?php echo e($img('1504384308090-c894fdcc538d', 800)); ?>" alt="Browser AI running in browser" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                    </div>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <a href="/keyword-extraction.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-teal-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-teal-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-teal-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-teal-700">Keyword Extraction</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Extract keywords from job descriptions using free Browser AI. Choose which to emphasise when generating your CV.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-teal-600 group-hover:text-teal-700">Learn more →</span>
                    </a>

                    <a href="/keyword-ai-integration.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 mb-4 group-hover:bg-emerald-200 transition-colors">
                            <svg class="h-6 w-6 text-emerald-600 group-hover:text-emerald-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-emerald-700">AI Keyword Integration</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Keywords are automatically integrated into CV variants using free Browser AI. Improve ATS compatibility at no cost.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-emerald-600 group-hover:text-emerald-700">Learn more →</span>
                    </a>

                    <a href="/cv-variant-linking.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-emerald-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-emerald-700">CV Variant Linking</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Generate job-specific CV variants with free Browser AI. Each variant stays linked to its application forever.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-emerald-600 group-hover:text-emerald-700">Learn more →</span>
                    </a>

                    <a href="/cover-letters-feature.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-rose-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4 group-hover:bg-rose-100 transition-colors">
                            <svg class="h-6 w-6 text-blue-600 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-rose-700">Cover Letters</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Generate tailored cover letters with free Browser AI. Keep them linked to the right job application.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-rose-600 group-hover:text-rose-700">Learn more →</span>
                    </a>

                    <a href="/application-questions-feature.php" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-green-200 transition-all group block">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 mb-4 group-hover:bg-green-200 transition-colors">
                            <svg class="h-6 w-6 text-green-600 group-hover:text-green-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 group-hover:text-green-700">Application Questions</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Get AI-powered help answering application form questions. Generate tailored answers based on the role and your CV.
                        </p>
                        <span class="mt-3 inline-flex items-center text-sm font-medium text-green-600 group-hover:text-green-700">Learn more →</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- File & Content Management - Rich Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        File & Content Management
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        Upload files, extract text, and let AI read everything automatically—no manual copy-paste needed.
                    </p>
                </div>

                <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch mb-12">
                    <div class="order-2 lg:order-1 flex items-center">
                        <img src="<?php echo e($img('1531403009284-440f080d1e12', 800)); ?>" alt="File uploads and AI integration" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                    </div>
                    <div class="order-1 lg:order-2 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Upload Files & AI Reads Automatically</h3>
                        <p class="text-gray-600 mb-6">
                            Upload job description files (PDF, Word, Excel) directly to each application. When you generate an AI CV variant, our free Browser AI automatically reads these files and combines them with any text in the job description field. No need to copy and paste—the AI handles everything.
                        </p>
                        <ul class="space-y-3 text-gray-700 mb-6">
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Supports PDF, Word (.doc, .docx), Excel (.xls, .xlsx), and text files</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>AI automatically reads files when generating CV variants</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Extract text from files with one click to populate job description field</span>
                            </li>
                        </ul>
                        <div class="flex flex-wrap gap-3">
                            <a href="/file-uploads-ai.php" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-amber-700 transition-colors">
                                File Uploads →
                            </a>
                            <a href="/smart-text-extraction.php" class="inline-flex items-center gap-2 rounded-lg border-2 border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                Text Extraction →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Additional Tools - Grouped -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Additional Tools
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        More features to keep your job search organised and effective.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Statistics</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            See total applications and counts by status (Applied, Interviewing, Offered, etc.) and upcoming follow-up dates at a glance.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Salary, Location & Work Arrangement</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Store salary range, job location, and work arrangement (onsite, hybrid, or remote) for each application so you can compare and filter.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 mb-4">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Priority Levels</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Set priority levels (High, Medium, Low) for each application to focus on the most important opportunities first.
                        </p>
                    </div>
                </div>
            </div>
        </section>


        <!-- Benefits Section -->
        <section class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Why Use Job Application Tracker?
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        Stay organised and never miss an opportunity with our comprehensive job application management system.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <a href="/never-miss-follow-up.php" class="text-center hover:opacity-80 transition-opacity group">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 group-hover:bg-blue-200 transition-colors">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-blue-700">Never Miss a Follow-Up</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Set follow-up or closing dates for each application and see upcoming dates at a glance.
                        </p>
                        <span class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">Learn more →</span>
                    </a>

                    <a href="/track-progress.php" class="text-center hover:opacity-80 transition-opacity group">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 group-hover:bg-blue-200 transition-colors">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-blue-700">Track Your Progress</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Visualise your job search progress with statistics and insights into your applications.
                        </p>
                        <span class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">Learn more →</span>
                    </a>

                    <a href="/all-in-one-place.php" class="text-center hover:opacity-80 transition-opacity group">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 group-hover:bg-blue-200 transition-colors">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-blue-700">All in One Place</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Manage your CV and job applications together in a single platform for maximum efficiency.
                        </p>
                        <span class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">Learn more →</span>
                    </a>

                    <a href="/free-with-account.php" class="text-center hover:opacity-80 transition-opacity group">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 group-hover:bg-blue-200 transition-colors">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-blue-700">Free with Every Account</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Job application tracking is included with every Simple CV Builder account at no extra cost.
                        </p>
                        <span class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">Learn more →</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Getting Started Section -->
        <section class="bg-gradient-to-br from-blue-50 to-indigo-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white border border-blue-200 p-8 md:p-12 shadow-xl">
                    <div class="max-w-3xl mx-auto text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Ready to Track Your Job Applications?
                        </h2>
                        <p class="mt-4 text-lg text-gray-600">
                            Start tracking your job applications today. Save jobs in one click from any site, set priorities and deadlines, and get reminders—all included with every account. Upgrade to Pro for unlimited applications and premium CV templates.
                        </p>
                        <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
                            <?php if (isLoggedIn()): ?>
                                <a href="/job-applications.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-blue-700 transition-colors">
                                    Open Job Applications
                                </a>
                                <a href="/pricing" class="inline-flex items-center justify-center rounded-lg border-2 border-blue-600 px-8 py-3 text-base font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                                    View plans & upgrade
                                </a>
                            <?php else: ?>
                                <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-blue-700 transition-colors">
                                    Create Free Account
                                </button>
                                <a href="/pricing" class="inline-flex items-center justify-center rounded-lg border-2 border-blue-600 px-8 py-3 text-base font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
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
    <?php if (!isLoggedIn()): ?>
        <?php partial('auth-modals'); ?>
    <?php endif; ?>
</body>
</html>
