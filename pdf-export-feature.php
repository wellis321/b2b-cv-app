<?php
/**
 * PDF Export – feature page
 * Describes PDF export capabilities including selective sections and QR codes.
 */

require_once __DIR__ . '/php/helpers.php';

$pageTitle = 'PDF Export';
$canonicalUrl = APP_URL . '/pdf-export-feature.php';
$img = function($id, $w = 800) { return 'https://images.unsplash.com/photo-' . $id . '?w=' . $w . '&q=80'; };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Export your CV as a professional PDF document. Choose which sections to include, add QR codes, and create print-ready documents.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content">
        <!-- Hero -->
        <section class="relative min-h-[50vh] flex flex-col justify-center bg-gradient-to-br from-red-600 via-rose-600 to-pink-600 text-white overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('https://images.unsplash.com/photo-1481627834810-7b0dc0a24339?w=1920&q=80');" aria-hidden="true"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-red-600/90 via-rose-600/90 to-pink-600/90" aria-hidden="true"></div>
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 text-center">
                <span class="inline-flex items-center rounded-full bg-white/20 backdrop-blur-sm px-4 py-1.5 text-sm font-semibold text-white shadow-sm border border-white/30">Pro Feature</span>
                <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl text-white">
                    PDF Export
                </h1>
                <p class="mt-6 text-xl text-rose-50 max-w-2xl mx-auto leading-relaxed">
                    Export your CV as a professional PDF document. <strong class="text-white">Choose which sections to include</strong>, add QR codes linking back to your online CV, and create print-ready documents.
                </p>
                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="/cv.php" class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-3 text-base font-semibold text-red-600 shadow-lg hover:bg-rose-50 transition-colors">
                            Export Your CV
                        </a>
                    <?php else: ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-3 text-base font-semibold text-red-600 shadow-lg hover:bg-rose-50 transition-colors">
                            Create Free Account
                        </button>
                    <?php endif; ?>
                    <a href="#features" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors backdrop-blur-sm">
                        Learn More
                    </a>
                </div>
            </div>
        </section>

        <!-- Key Features -->
        <section id="features" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Professional PDF exports
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        Create print-ready PDF documents with full control over content and formatting.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-xl border-2 border-red-200 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-500 text-white mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Selective Section Export</h3>
                        <p class="text-sm text-gray-600">Choose exactly which sections to include in your PDF. Toggle sections on or off to create customised versions for different purposes.</p>
                    </div>

                    <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-xl border-2 border-rose-200 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-rose-500 text-white mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">QR Codes in PDF</h3>
                        <p class="text-sm text-gray-600">Add QR codes to your PDF exports that link directly back to your online CV. Recipients can scan to view the latest version instantly.</p>
                    </div>

                    <div class="bg-gradient-to-br from-pink-50 to-red-50 rounded-xl border-2 border-pink-200 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-pink-500 text-white mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Print-Ready Format</h3>
                        <p class="text-sm text-gray-600">PDFs are formatted for standard A4 paper size and look professional when printed. Perfect for job fairs, interviews, and applications.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl text-center mb-12">
                    How PDF export works
                </h2>
                <div class="space-y-12">
                    <div class="flex flex-col md:flex-row md:items-center md:gap-12">
                        <div class="md:w-1/2">
                            <span class="inline-block rounded-full bg-red-100 text-red-800 px-3 py-1 text-sm font-semibold">Step 1</span>
                            <h3 class="mt-4 text-2xl font-bold text-gray-900">Choose sections to include</h3>
                            <p class="mt-3 text-gray-600">
                                When exporting to PDF, select which sections you want to include. Toggle sections like Personal Profile, Work Experience, Education, Projects, Skills, and more on or off to create customised PDFs.
                            </p>
                        </div>
                        <div class="mt-8 md:mt-0 md:w-1/2">
                            <img src="<?php echo e($img('1504384308090-c894fdcc538d', 600)); ?>" alt="Selecting sections for PDF export" class="w-full rounded-xl border border-gray-200 shadow-sm object-cover aspect-video" width="600" height="340" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row-reverse md:items-center md:gap-12">
                        <div class="md:w-1/2">
                            <span class="inline-block rounded-full bg-red-100 text-red-800 px-3 py-1 text-sm font-semibold">Step 2</span>
                            <h3 class="mt-4 text-2xl font-bold text-gray-900">Add QR code (optional)</h3>
                            <p class="mt-3 text-gray-600">
                                Optionally include a QR code in your PDF that links to your online CV. Recipients can scan the code to view your always-up-to-date CV online, ensuring they never see an outdated version.
                            </p>
                        </div>
                        <div class="mt-8 md:mt-0 md:w-1/2">
                            <img src="<?php echo e($img('1531403009284-440f080d1e12', 600)); ?>" alt="QR code in PDF" class="w-full rounded-xl border border-gray-200 shadow-sm object-cover aspect-video" width="600" height="340" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:gap-12">
                        <div class="md:w-1/2">
                            <span class="inline-block rounded-full bg-red-100 text-red-800 px-3 py-1 text-sm font-semibold">Step 3</span>
                            <h3 class="mt-4 text-2xl font-bold text-gray-900">Download and share</h3>
                            <p class="mt-3 text-gray-600">
                                Generate your PDF and download it instantly. Share via email, upload to job boards, print for interviews, or attach to applications. Your PDF maintains professional formatting and looks great on any device.
                            </p>
                        </div>
                        <div class="mt-8 md:mt-0 md:w-1/2">
                            <img src="<?php echo e($img('1586281380349-632531db7ed4', 600)); ?>" alt="Downloading PDF" class="w-full rounded-xl border border-gray-200 shadow-sm object-cover aspect-video" width="600" height="340" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Why export to PDF?
                    </h2>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="bg-red-50 border-2 border-red-200 rounded-xl p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-500 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Upload to job boards</h3>
                        </div>
                        <p class="text-gray-700">
                            Many job boards require PDF uploads. Export your CV as a PDF and upload it directly to Indeed, LinkedIn, Reed, and other platforms.
                        </p>
                    </div>

                    <div class="bg-rose-50 border-2 border-rose-200 rounded-xl p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-rose-500 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Email attachments</h3>
                        </div>
                        <p class="text-gray-700">
                            Attach your CV PDF to email applications. PDFs maintain formatting across all devices and email clients, ensuring employers see your CV exactly as intended.
                        </p>
                    </div>

                    <div class="bg-pink-50 border-2 border-pink-200 rounded-xl p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-pink-500 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Print for interviews</h3>
                        </div>
                        <p class="text-gray-700">
                            Print your CV PDF for in-person interviews, job fairs, or networking events. PDFs are formatted for standard A4 paper and look professional when printed.
                        </p>
                    </div>

                    <div class="bg-red-50 border-2 border-red-200 rounded-xl p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-500 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">QR code advantage</h3>
                        </div>
                        <p class="text-gray-700">
                            Include a QR code in your PDF that links to your online CV. Recipients can scan to view the latest version, ensuring they always see your most up-to-date information.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Explore All Features -->
        <section class="py-16 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                    Explore All Features
                </h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                    This is just one of many features we offer. Discover everything Simple CV Builder can do for your job search and career development.
                </p>
                <div class="mt-8">
                    <a href="/all-features.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        View All Features
                    </a>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 bg-gradient-to-br from-red-600 to-rose-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
                <h2 class="text-2xl font-bold sm:text-3xl">
                    Export your CV to PDF
                </h2>
                <p class="mt-4 text-red-100 max-w-xl mx-auto">
                    PDF export is available on all plans. Pro plans add premium templates, selective section exports, and QR codes in the PDF.
                </p>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="/subscription.php" class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-3 text-base font-semibold text-red-600 shadow-lg hover:bg-red-50 transition-colors">
                            Upgrade to Pro
                        </a>
                        <a href="/all-features.php" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors">
                            All Features
                        </a>
                    <?php else: ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-3 text-base font-semibold text-red-600 shadow-lg hover:bg-red-50 transition-colors">
                            Create Free Account
                        </button>
                        <a href="/subscription.php" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors">
                            View Pricing
                        </a>
                    <?php endif; ?>
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
