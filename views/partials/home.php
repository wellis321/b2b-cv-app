<?php
// Marketing page for non-logged in users
?>

<!-- Hero Section -->
<div class="relative overflow-hidden bg-white">
    <div class="pt-32 pb-80 sm:pt-36 sm:pb-40 lg:pt-56 lg:pb-48">
        <div class="relative mx-auto max-w-7xl px-4 sm:static sm:px-6 lg:px-8">
            <div class="sm:max-w-lg">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    Your CV, Reimagined
                </h1>
                <p class="mt-4 text-xl text-gray-500">
                    Create a professional CV that stands out, updates in real-time, and can be shared as a simple link.
                </p>
                <div class="mt-10">
                    <a href="#auth-section" class="inline-block rounded-md border border-transparent bg-blue-600 px-8 py-3 text-center font-medium text-white hover:bg-blue-700">
                        Start Building Your CV
                    </a>
                </div>
            </div>
            <div>
                <div class="mt-10">
                    <!-- Decorative image grid -->
                    <div aria-hidden="true" class="pointer-events-none lg:absolute lg:inset-y-0 lg:mx-auto lg:w-full lg:max-w-7xl">
                        <div class="absolute transform sm:top-0 sm:left-1/2 sm:translate-x-8 lg:top-1/2 lg:left-1/2 lg:translate-x-8 lg:-translate-y-1/2">
                            <div class="flex items-center space-x-6 lg:space-x-8">
                                <div class="grid flex-shrink-0 grid-cols-1 gap-y-6 lg:gap-y-8">
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-200 to-blue-300"></div>
                                    </div>
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-300 to-blue-400"></div>
                                    </div>
                                </div>
                                <div class="grid flex-shrink-0 grid-cols-1 gap-y-6 lg:gap-y-8">
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-400 to-blue-500"></div>
                                    </div>
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-500 to-blue-600"></div>
                                    </div>
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-600 to-blue-700"></div>
                                    </div>
                                </div>
                                <div class="grid flex-shrink-0 grid-cols-1 gap-y-6 lg:gap-y-8">
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-700 to-blue-800"></div>
                                    </div>
                                    <div class="h-64 w-44 overflow-hidden rounded-lg bg-blue-100 shadow-lg">
                                        <div class="h-full w-full bg-gradient-to-br from-blue-800 to-blue-900"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Section -->
<div class="bg-gray-50 py-12 sm:py-16" id="features">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-lg font-semibold text-blue-600">Simple CV Builder</h2>
            <p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                A better way to showcase your professional journey
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                Build a comprehensive CV that stands out from the crowd with our intuitive tools and unique sharing features.
            </p>
        </div>

        <div class="mt-10">
            <dl class="space-y-10 md:grid md:grid-cols-3 md:space-y-0 md:gap-x-8 md:gap-y-10">
                <?php
                $features = [
                    [
                        'title' => 'Dynamic Online CV',
                        'description' => 'Create a professional CV that updates in real-time and can be shared as a simple link.',
                        'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'
                    ],
                    [
                        'title' => 'Comprehensive Sections',
                        'description' => 'Include everything from work experience to professional memberships in your CV.',
                        'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'
                    ],
                    [
                        'title' => 'Print & Share',
                        'description' => 'Download as PDF or share a unique link with employers and your network.',
                        'icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                    ],
                    [
                        'title' => 'QR Code on PDF',
                        'description' => 'PDF downloads include a QR code that links directly back to your full, beautifully designed digital CV.',
                        'icon' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'
                    ],
                    [
                        'title' => 'Reorder Work Experience',
                        'description' => 'Easily drag and drop to reorder your work experiences to highlight your most relevant roles first.',
                        'icon' => 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4'
                    ],
                    [
                        'title' => 'Customisable CV Content',
                        'description' => 'Select exactly which sections to include in your PDF, giving you full control over what employers see.',
                        'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
                    ]
                ];
                foreach ($features as $feature): ?>
                    <div class="relative">
                        <dt>
                            <div class="absolute flex h-12 w-12 items-center justify-center rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($feature['icon']); ?>" />
                                </svg>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900"><?php echo e($feature['title']); ?></p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500"><?php echo e($feature['description']); ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="bg-white py-12 sm:py-16" id="process">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-lg font-semibold text-blue-600">Simple Process</h2>
            <p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                How Our Simple CV Builder Works
            </p>
        </div>

        <div class="mt-10">
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-lg font-medium text-gray-900">Three simple steps</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-10 sm:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <span class="text-xl font-bold">1</span>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Create Your Profile</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Sign up and fill in your personal information and professional details.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <span class="text-xl font-bold">2</span>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Build Your CV</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Add your work experience, education, skills and other professional achievements.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <span class="text-xl font-bold">3</span>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Share & Download</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Get a unique link to share with employers or download as a professional PDF.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Section -->
<div class="bg-gray-900 py-16 sm:py-24" id="pricing">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl text-center mx-auto">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Flexible pricing that grows with you</h2>
            <p class="mt-4 text-lg text-gray-300">
                Start for free, then upgrade when you need unlimited sections, premium templates, and priority support.
            </p>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            <?php
            $pricingCards = [
                [
                    'label' => 'Free',
                    'price' => '£0',
                    'detail' => 'Forever free',
                    'highlight' => false,
                    'features' => [
                        '1 work experience entry',
                        '1 project showcase',
                        '3 highlighted skills',
                        'Minimal template',
                    ],
                    'button' => ['text' => 'Start for free', 'href' => '#auth-section'],
                ],
                [
                    'label' => 'Pro Monthly',
                    'price' => '£4.99',
                    'detail' => 'per month',
                    'highlight' => true,
                    'features' => [
                        'Unlimited sections & entries',
                        'Professional template with colours',
                        'Download print-ready PDFs',
                        'Priority email support',
                    ],
                    'button' => ['text' => 'View full plans', 'href' => '/subscription.php'],
                ],
                [
                    'label' => 'Pro Annual',
                    'price' => '£29.99',
                    'detail' => 'per year (save over 40%)',
                    'highlight' => false,
                    'features' => [
                        'Everything in Pro Monthly',
                        'Best value for serious job seekers',
                        'Annual billing with Stripe',
                        'Priority email support',
                    ],
                    'button' => ['text' => 'View full plans', 'href' => '/subscription.php'],
                ],
            ];
            foreach ($pricingCards as $card):
                $classes = $card['highlight']
                    ? 'border-blue-500 ring-1 ring-blue-200 bg-white text-gray-900'
                    : 'border-gray-700 bg-gray-800 text-gray-100';
                ?>
                <div class="flex flex-col rounded-2xl border <?php echo $classes; ?> p-8 shadow-xl">
                    <div>
                        <h3 class="text-xl font-semibold"><?php echo e($card['label']); ?></h3>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-3xl font-bold"><?php echo e($card['price']); ?></span>
                            <span class="text-sm text-gray-400"><?php echo e($card['detail']); ?></span>
                        </div>
                    </div>
                    <ul class="mt-8 space-y-3 text-sm flex-1">
                        <?php foreach ($card['features'] as $feature): ?>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo e($feature); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-8">
                        <a href="<?php echo e($card['button']['href']); ?>"
                           class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition
                           <?php echo $card['highlight']
                               ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white'
                               : 'bg-white text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800'; ?>">
                            <?php echo e($card['button']['text']); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="mt-8 text-center text-sm text-gray-400">
            Secure payments powered by Stripe. Cancel anytime from your billing portal.
        </p>
    </div>
</div>

<!-- Testimonials Section -->
<div class="bg-gray-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-lg font-semibold text-blue-600">Testimonials</h2>
            <p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                What Our Users Say
            </p>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-2">
            <?php
            $testimonials = [
                [
                    'quote' => 'This Simple CV builder helped me land my dream job. The online link was a game-changer during my application process.',
                    'author' => 'Alex Johnson',
                    'role' => 'Software Developer'
                ],
                [
                    'quote' => 'I love how easy it is to update my CV in real-time. My profile stays current without having to send new PDFs.',
                    'author' => 'Sarah Williams',
                    'role' => 'Marketing Manager'
                ]
            ];
            foreach ($testimonials as $testimonial): ?>
                <div class="rounded-lg bg-white p-6 shadow-lg">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                        </svg>
                        <div class="ml-4">
                            <p class="text-base font-medium text-gray-900"><?php echo e($testimonial['author']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($testimonial['role']); ?></p>
                        </div>
                    </div>
                    <p class="mt-4 text-base text-gray-500">"<?php echo e($testimonial['quote']); ?>"</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Simple Job Tracker Promotion -->
<div class="bg-gradient-to-r from-blue-50 to-green-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <a href="https://simple-job-tracker.com/" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-xl transition hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <div class="grid lg:grid-cols-2">
                <!-- Left side: Content -->
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                            <svg class="h-7 w-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">Job Search Tools</span>
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Land Your Dream Job
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Now that you've built your professional CV, track your job applications with
                        <strong>Simple Job Tracker</strong>. Never lose track of where you've applied, stay
                        on top of follow-ups, and land your next role.
                    </p>
                    <ul class="mt-6 space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Track all your job applications in one place</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Attach resumes and cover letters to each application</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Monitor your progress from application to offer</span>
                        </li>
                    </ul>
                    <div class="mt-8">
                        <span class="inline-flex items-center rounded-md bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-lg">
                            Visit Simple Job Tracker
                            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Right side: Visual -->
                <div class="flex items-center justify-center bg-gradient-to-br from-green-500 to-green-600 px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white/20 shadow-lg backdrop-blur-sm">
                            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Simple Job Tracker</h3>
                        <p class="mt-2 text-green-100">Organise • Track • Follow Up</p>
                        <div class="mt-6 text-sm text-white">
                            <p>£2.99/month</p>
                            <p>£14.99/year (save over 55%)</p>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Call to Action Section -->
<section class="bg-white" id="auth-section">
    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                Ready to build your professional CV?
            </h2>
            <p class="mt-4 text-lg text-gray-600">
                Start for free, add your experience in minutes, and upgrade whenever you need more power.
            </p>
        </div>

        <?php if (!empty($success) || !empty($error)): ?>
            <?php
            $alertClass = !empty($success)
                ? 'border-green-200 bg-green-50 text-green-800'
                : 'border-red-200 bg-red-50 text-red-800';
            $alertMessage = !empty($success) ? $success : $error;
            ?>
            <div class="mt-8 rounded-lg border <?php echo $alertClass; ?> px-4 py-3 text-sm font-medium">
                <?php echo e($alertMessage); ?>
                <?php if (!empty($error) && !empty($needsVerification) && !empty($verificationEmail)): ?>
                    <span class="mt-2 block font-normal text-sm">
                        Need a new verification email?
                        <a href="/resend-verification.php?email=<?php echo urlencode($verificationEmail); ?>" class="underline">Click here</a>.
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
            <button type="button"
                    data-open-register
                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                Create a free account
            </button>
            <button type="button"
                    data-open-login
                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-6 py-3 text-base font-semibold text-gray-700 hover:border-gray-400 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                Log in
            </button>
        </div>
    </div>
</section>

<!-- Auth Modals -->
<div data-modal="login" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
    <div class="flex min-h-full items-center justify-center px-4 py-10 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" data-close-modal></div>

        <div class="relative inline-block w-full max-w-md transform rounded-2xl bg-white px-6 py-6 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle sm:p-8">
            <button type="button" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600" data-close-modal aria-label="Close">
                <svg class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="mb-4">
                <h3 class="text-2xl font-semibold text-gray-900" id="login-modal-title">Welcome back</h3>
                <p class="mt-1 text-sm text-gray-500">Log in to continue editing and sharing your CV.</p>
            </div>
            <div data-modal-message class="mb-4 hidden rounded-md border px-4 py-3 text-sm font-medium"></div>
            <form method="POST" action="/">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">

                <div class="mb-4">
                    <label for="modal-login-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email"
                           id="modal-login-email"
                           name="email"
                           value="<?php echo isset($oldLoginEmail) ? e($oldLoginEmail) : ''; ?>"
                           required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="modal-login-password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password"
                           id="modal-login-password"
                           name="password"
                           required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Log in
                </button>

                <div class="mt-4 text-right text-xs text-gray-500 space-y-1">
                    <div>
                        <a href="/forgot-password.php" class="text-blue-600 hover:text-blue-800">Forgot your password?</a>
                    </div>
                    <div>
                        <a href="/forgot-username.php" class="text-blue-600 hover:text-blue-800">Forgot your username?</a>
                    </div>
                    <div>
                        <a href="/resend-verification.php" class="text-blue-600 hover:text-blue-800">Resend verification email</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div data-modal="register" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="register-modal-title">
    <div class="flex min-h-full items-center justify-center px-4 py-10 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" data-close-modal></div>

        <div class="relative inline-block w-full max-w-lg transform rounded-2xl bg-white px-6 py-6 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle sm:p-8">
            <button type="button" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600" data-close-modal aria-label="Close">
                <svg class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="mb-4">
                <h3 class="text-2xl font-semibold text-gray-900" id="register-modal-title">Create your free account</h3>
                <p class="mt-1 text-sm text-gray-500">We’ll guide you through building a standout CV in minutes.</p>
            </div>
            <div data-modal-message class="mb-4 hidden rounded-md border px-4 py-3 text-sm font-medium"></div>
            <form method="POST" action="/">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="modal-register-full-name" class="block text-sm font-medium text-gray-700 mb-2">Full name</label>
                        <input type="text"
                               id="modal-register-full-name"
                               name="full_name"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="modal-register-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email"
                               id="modal-register-email"
                               name="email"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="modal-register-password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password"
                               id="modal-register-password"
                               name="password"
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="modal-register-password-confirm" class="block text-sm font-medium text-gray-700 mb-2">Confirm password</label>
                        <input type="password"
                               id="modal-register-password-confirm"
                               name="password_confirm"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    Passwords must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters and include lowercase, uppercase, and a number.
                </p>

                <button type="submit" class="mt-6 w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create your account
                </button>

                <p class="mt-4 text-xs text-gray-500 text-center">
                    By signing up you agree to our <a href="/terms.php" class="text-blue-600 underline hover:text-blue-800">Terms of Service</a> and <a href="/privacy.php" class="text-blue-600 underline hover:text-blue-800">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        const body = document.body;
        const modalMap = new Map();

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            modalMap.set(modal.getAttribute('data-modal'), modal);
        });

        const messageVariants = {
            success: 'border-green-200 bg-green-50 text-green-800',
            error: 'border-red-200 bg-red-50 text-red-800'
        };

        const toggleBodyScroll = (disable) => {
            body.classList.toggle('overflow-hidden', disable);
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            toggleBodyScroll(false);
        };

        const openModal = (modalName, options = {}) => {
            const modal = modalMap.get(modalName);
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            toggleBodyScroll(true);

            const messageBox = modal.querySelector('[data-modal-message]');
            if (messageBox) {
                if (options.message) {
                    const classes = messageVariants[options.variant] || messageVariants.error;
                    messageBox.className = `mb-4 rounded-md border px-4 py-3 text-sm font-medium ${classes}`;
                    messageBox.innerHTML = options.message;
                    messageBox.classList.remove('hidden');
                } else {
                    messageBox.classList.add('hidden');
                    messageBox.innerHTML = '';
                }
            }

            const firstInput = modal.querySelector('input[type="email"], input[type="text"], input[type="password"]');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 80);
            }
        };

        document.querySelectorAll('[data-open-login]').forEach((trigger) => {
            trigger.addEventListener('click', () => openModal('login'));
        });

        document.querySelectorAll('[data-open-register]').forEach((trigger) => {
            trigger.addEventListener('click', () => openModal('register'));
        });

        document.querySelectorAll('[data-close-modal]').forEach((closeTrigger) => {
            closeTrigger.addEventListener('click', (event) => {
                const modal = event.target.closest('[data-modal]');
                closeModal(modal);
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                modalMap.forEach((modal) => {
                    if (!modal.classList.contains('hidden')) {
                        closeModal(modal);
                    }
                });
            }
        });

        const authState = <?php
            $initialModal = null;
            if (!empty($success)) {
                $initialModal = 'login';
            } elseif (!empty($error)) {
                $initialModal = !empty($oldLoginEmail) ? 'login' : 'register';
            }

            $initialMessage = '';
            $initialVariant = null;
            if (!empty($success)) {
                $initialMessage = e($success);
                $initialVariant = 'success';
            } elseif (!empty($error)) {
                $initialMessage = e($error);
                if (!empty($needsVerification) && !empty($verificationEmail)) {
                    $initialMessage .= '<br><span class="font-normal text-xs">Need a new verification email? <a href="/resend-verification.php?email=' . urlencode($verificationEmail) . '" class="underline">Click here</a>.</span>';
                }
                $initialVariant = 'error';
            }

            echo json_encode([
                'open' => $initialModal,
                'message' => $initialMessage,
                'variant' => $initialVariant,
            ]);
        ?>;

        if (authState.open) {
            openModal(authState.open, {
                message: authState.message,
                variant: authState.variant
            });
        }
    })();
</script>

<button type="button"
        data-back-to-top
        aria-label="Back to top"
        class="fixed bottom-6 right-6 z-40 hidden rounded-full bg-blue-600 p-3 text-white shadow-lg transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
    </svg>
</button>

<script>
    (() => {
        const backToTopButton = document.querySelector('[data-back-to-top]');
        if (!backToTopButton) return;

        const toggleButtonVisibility = () => {
            if (window.scrollY > 400) {
                backToTopButton.classList.remove('hidden');
            } else {
                backToTopButton.classList.add('hidden');
            }
        };

        window.addEventListener('scroll', toggleButtonVisibility, { passive: true });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
</script>
