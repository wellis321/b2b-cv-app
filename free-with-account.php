<?php
/**
 * Free with Every Account – feature page
 * Describes that job application tracking is included at no extra cost.
 */

require_once __DIR__ . '/php/helpers.php';

$pageTitle = 'Free with Every Account';
$canonicalUrl = APP_URL . '/free-with-account.php';
$img = function($id, $w = 800) { return 'https://images.unsplash.com/photo-' . $id . '?w=' . $w . '&q=80'; };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Job application tracking is included with every Simple CV Builder account at no extra cost. Track applications, generate CV variants, and manage your job search—all free.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content">
        <!-- Hero -->
        <section class="relative min-h-[50vh] flex flex-col justify-center bg-gradient-to-br from-purple-900 via-pink-900 to-purple-900 text-white overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('https://images.unsplash.com/photo-1450101491212-3f7e0d4dff11?w=1920&q=80');" aria-hidden="true"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/80 via-pink-900/80 to-purple-900/80" aria-hidden="true"></div>
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 text-center">
                <span class="inline-flex items-center rounded-full bg-purple-500/90 px-4 py-1.5 text-sm font-semibold text-white shadow-sm">No extra cost</span>
                <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl text-white">
                    Free with Every Account
                </h1>
                <p class="mt-6 text-xl text-purple-100 max-w-2xl mx-auto leading-relaxed">
                    Job application tracking is included with <strong class="text-white">every Simple CV Builder account</strong> at no extra cost. Track applications, generate CV variants, and manage your job search—all included.
                </p>
                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="/content-editor.php#jobs" class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-purple-700 transition-colors">
                            Open job list
                        </a>
                    <?php else: ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-purple-700 transition-colors">
                            Create free account
                        </button>
                    <?php endif; ?>
                    <a href="#whats-included" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors backdrop-blur-sm">
                        What's included
                    </a>
                </div>
            </div>
        </section>

        <!-- Why Free -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Why job tracking is included
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        Your CV and job applications go hand in hand. That's why we've included comprehensive job application tracking with every account—no separate subscription, no hidden fees.
                    </p>
                </div>

                <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch">
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">CV and applications belong together</h3>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            When you're building your CV, you're likely also applying for jobs. When you're applying for jobs, you need to tailor your CV. These two activities are so closely connected that it makes sense to manage them in the same platform.
                        </p>
                        <p class="text-gray-600 leading-relaxed">
                            That's why we've built job application tracking right into Simple CV Builder. You don't need a separate tool or an additional subscription—everything you need for your job search is included with your account, whether you're on the free plan or a paid plan.
                        </p>
                    </div>
                    <div class="flex items-center">
                        <img src="<?php echo e($img('1531403009284-440f080d1e12', 800)); ?>" alt="Integrated CV and job application platform" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                    </div>
                </div>
            </div>
        </section>

        <!-- What's Included -->
        <section id="whats-included" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        What's included with every account
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        All core job application tracking features are available to everyone, regardless of your plan level.
                    </p>
                </div>

                <div class="space-y-20">
                    <!-- Core tracking -->
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch">
                        <div class="lg:order-2 flex flex-col">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Core tracking features</h3>
                            <p class="text-gray-600 leading-relaxed mb-4">
                                Save jobs from any website, track application statuses, set follow-up dates, add notes, and search and filter your applications—all included with every account. These core features help you stay organised and never miss an opportunity.
                            </p>
                            <p class="text-gray-600 leading-relaxed">
                                Whether you're on the free plan or a paid plan, you get the same powerful tracking tools. The difference between plans is in the number of applications you can track and additional features like PDF exports and premium templates, not in the core tracking functionality.
                            </p>
                        </div>
                        <div class="lg:order-1 flex items-center">
                            <img src="<?php echo e($img('1586281380349-632531db7ed4', 800)); ?>" alt="Core job tracking features" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                        </div>
                    </div>

                    <!-- AI features -->
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch">
                        <div class="flex flex-col">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Free AI features</h3>
                            <p class="text-gray-600 leading-relaxed mb-4">
                                All AI features are powered by free Browser AI, which runs directly in your browser with no API costs. Generate CV variants, extract keywords, create cover letters, and run quality assessments—all completely free, regardless of your plan.
                            </p>
                            <p class="text-gray-600 leading-relaxed">
                                There are no usage limits, no per-request fees, and no hidden costs. The AI features work the same for everyone, whether you're on the free plan or a paid plan. This means you can generate unlimited CV variants and cover letters without worrying about costs.
                            </p>
                        </div>
                        <div class="flex items-center">
                            <img src="<?php echo e($img('1504384308090-c894fdcc538d', 800)); ?>" alt="Free Browser AI features" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-stretch">
                        <div class="lg:order-2 flex flex-col">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Progress statistics</h3>
                            <p class="text-gray-600 leading-relaxed mb-4">
                                See your total applications, counts by status, and upcoming follow-up dates—all included with every account. These statistics help you understand your job search progress and make informed decisions about where to focus your efforts.
                            </p>
                            <p class="text-gray-600 leading-relaxed">
                                Progress tracking isn't a premium feature—it's available to everyone. You can see how many applications you've submitted, how many are at each stage, and what needs your attention, all without upgrading your plan.
                            </p>
                        </div>
                        <div class="lg:order-1 flex items-center">
                            <img src="<?php echo e($img('1557804506-669a67965ba0', 800)); ?>" alt="Progress statistics dashboard" class="w-full rounded-xl border border-gray-200 shadow-lg object-cover aspect-video" width="800" height="450" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Plan Differences -->
        <section class="py-16 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl text-center mb-12">
                    What differs between plans?
                </h2>
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-8">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        The core job application tracking features are the same for everyone. What differs between plans is:
                    </p>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start gap-3">
                            <svg class="h-6 w-6 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>Number of applications:</strong> Free plan includes a limited number of applications; paid plans include more or unlimited applications.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-6 w-6 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>PDF exports:</strong> All plans include PDF export. Pro plans add premium templates, selective section exports, and QR codes in the PDF.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-6 w-6 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>CV templates:</strong> Free plan includes basic templates; paid plans include premium templates with customisable colours.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-6 w-6 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>CV customisation:</strong> Paid plans include advanced customisation options like drag-and-drop reordering and selective section inclusion.</span>
                        </li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed mt-6">
                        But the core tracking features—saving jobs, tracking statuses, setting dates, adding notes, and using AI features—are available to everyone at no extra cost.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 bg-gradient-to-br from-purple-600 to-pink-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
                <h2 class="text-2xl font-bold sm:text-3xl">
                    Start tracking applications today
                </h2>
                <p class="mt-4 text-purple-100 max-w-xl mx-auto">
                    Job application tracking is included with every account at no extra cost. Create your free account and start tracking your applications immediately.
                </p>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="/content-editor.php#jobs" class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-3 text-base font-semibold text-purple-600 shadow-lg hover:bg-purple-50 transition-colors">
                            Open job list
                        </a>
                        <a href="/pricing" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors">
                            View plans
                        </a>
                    <?php else: ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-3 text-base font-semibold text-purple-600 shadow-lg hover:bg-purple-50 transition-colors">
                            Create free account
                        </button>
                        <a href="/pricing" class="inline-flex items-center justify-center rounded-lg border-2 border-white/80 bg-white/10 px-8 py-3 text-base font-semibold text-white hover:bg-white/20 transition-colors">
                            View plans
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
