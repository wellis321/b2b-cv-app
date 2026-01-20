<?php
// Marketing page for non-logged in users
?>

<!-- Hero Section -->
<div class="relative overflow-hidden bg-white">
    <div class="pt-32 pb-16 sm:pt-36 sm:pb-20 md:pb-32 lg:pt-56 lg:pb-48">
        <div class="relative mx-auto max-w-7xl px-4 sm:static sm:px-6 lg:px-8">
            <div class="sm:max-w-lg relative z-10">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    Professional CV Management for Recruitment Agencies
                </h1>
                <p class="mt-4 text-xl text-gray-500">
                    Streamline your recruitment process with our powerful B2B platform. Manage candidate CVs efficiently, provide professional CV building tools, and deliver exceptional service to your clients.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <a href="/organisations.php" class="inline-block rounded-md border border-transparent bg-blue-600 px-8 py-3 text-center font-medium text-white hover:bg-blue-700">
                        For Organisations
                    </a>
                    <a href="/individual-users.php" class="inline-block rounded-md border border-gray-300 bg-white px-8 py-3 text-center font-medium text-gray-700 hover:bg-gray-50">
                        Individual Users
                    </a>
                </div>
            </div>
            <div>
                <div class="mt-10">
                    <!-- Decorative image grid -->
                    <div aria-hidden="true" class="pointer-events-none hidden md:block lg:absolute lg:inset-y-0 lg:mx-auto lg:w-full lg:max-w-7xl">
                        <div class="absolute transform md:top-0 md:left-1/2 md:translate-x-8 lg:top-1/2 lg:left-1/2 lg:translate-x-8 lg:-translate-y-1/2 z-0">
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
                Powerful CV Management for Recruitment Agencies
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                Manage your candidates' CVs efficiently with our comprehensive platform. Perfect for recruitment agencies, HR departments, and organisations managing multiple candidates.
            </p>
        </div>

        <div class="mt-10">
            <dl class="space-y-10 md:grid md:grid-cols-3 md:space-y-0 md:gap-x-8 md:gap-y-10">
                <?php
                $features = [
                    [
                        'title' => 'Candidate Management',
                        'description' => 'Efficiently manage all your candidates in one place. Invite candidates, track their CV progress, and access their professional profiles instantly.',
                        'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
                    ],
                    [
                        'title' => 'Team Collaboration',
                        'description' => 'Work together with your team. Assign roles, manage permissions, and collaborate on candidate management with multiple team members.',
                        'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
                    ],
                    [
                        'title' => 'Branded Candidate CVs',
                        'description' => 'Customise candidate CVs with your organisation\'s branding. Add your logo, brand colours, and create a professional, consistent experience.',
                        'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
                    ],
                    [
                        'title' => 'Real-Time CV Updates',
                        'description' => 'Candidates can update their CVs in real-time. Changes are instantly reflected, ensuring you always have access to the latest information.',
                        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'
                    ],
                    [
                        'title' => 'Flexible Access Control',
                        'description' => 'Control who can see what. Set CV visibility levels, manage team permissions, and ensure data privacy with granular access controls.',
                        'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'
                    ],
                    [
                        'title' => 'Scalable Plans',
                        'description' => 'Grow with confidence. Request limit increases as your organisation expands, with flexible plans that adapt to your needs.',
                        'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
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

<!-- AI CV Features Section -->
<div class="bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <span class="inline-flex items-center rounded-full bg-purple-100 px-4 py-1 text-sm font-medium text-purple-800 mb-4">
                AI-Powered
            </span>
            <h2 class="text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                AI CV Assistant
            </h2>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                Let artificial intelligence help you create the perfect CV for every job application. Generate tailored CVs, get quality feedback, and improve your chances of landing interviews. All AI features run directly in your browser - no setup, API keys, or cloud services required. Cloud-based AI options available for organisations or users with their own accounts.
            </p>
        </div>
        
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                <div class="flex justify-center mb-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-3">AI CV Rewriting</h3>
                <p class="text-base text-gray-600 text-center mb-4">
                    Paste a job description and our AI will automatically rewrite your CV to match the requirements. Emphasize relevant skills, use keywords naturally, and create a tailored version for each application.
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Automatic keyword optimisation</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Job-specific content tailoring</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Maintains factual accuracy</span>
                    </li>
                </ul>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border border-blue-100">
                <div class="flex justify-center mb-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-3">CV Quality Assessment</h3>
                <p class="text-base text-gray-600 text-center mb-4">
                    Get comprehensive AI-powered feedback on your CV. Receive scores for ATS compatibility, content quality, formatting, and keyword matching with specific improvement recommendations.
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>ATS compatibility scoring</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Actionable improvement suggestions</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Strengths and weaknesses analysis</span>
                    </li>
                </ul>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border border-indigo-100">
                <div class="flex justify-center mb-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-3">CV Variants Management</h3>
                <p class="text-base text-gray-600 text-center mb-4">
                    Create and manage multiple CV versions effortlessly. Keep your master CV safe while generating tailored variants for different job applications. Edit any variant like a normal CV.
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Unlimited CV variants</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Linked to job applications</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Full editing capabilities</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Browser-Based AI Info Box -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-xl p-6 max-w-3xl mx-auto">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold text-blue-900 mb-1">Browser-Based AI - No Setup Required</h4>
                    <p class="text-sm text-blue-800">
                        All AI features run directly in your browser using advanced browser-based AI technology. No cloud services, API keys, or configuration needed - it just works! For organisations or users who prefer cloud-based AI (OpenAI, Anthropic, Gemini, etc.), those options are also available if configured in your account settings.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <?php if (isLoggedIn()): ?>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/cv-variants/rewrite.php" class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate AI CV
                    </a>
                    <a href="/cv-quality.php" class="inline-flex items-center justify-center rounded-lg border-2 border-purple-600 bg-white px-6 py-3 text-base font-semibold text-purple-600 shadow-lg hover:bg-purple-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Assess CV Quality
                    </a>
                    <a href="/ai-cv-assessment.php" class="inline-flex items-center justify-center rounded-lg border-2 border-purple-600 bg-white px-6 py-3 text-base font-semibold text-purple-600 shadow-lg hover:bg-purple-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Learn More
                    </a>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-600 mb-4">
                    <strong>Available to all users:</strong> Create a free account to access AI CV features
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-purple-700 transition-colors">
                        Create Free Account
                    </button>
                    <a href="/ai-cv-assessment.php" class="inline-flex items-center justify-center rounded-lg border-2 border-purple-600 bg-white px-6 py-3 text-base font-semibold text-purple-600 shadow-lg hover:bg-purple-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Learn About AI Assessment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom Homepage Feature Section -->
<div class="bg-white py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-purple-200 bg-white shadow-xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left side: Visual -->
                <div class="flex items-center justify-center bg-gradient-to-br from-purple-500 to-purple-600 px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white/20 shadow-lg backdrop-blur-sm">
                            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Custom Public Homepage</h3>
                        <p class="mt-2 text-purple-100">AI-Powered • Fully Customisable</p>
                    </div>
                </div>
                <!-- Right side: Content -->
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Create Your Unique Public Presence
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Design a fully customised public landing page for your organisation with AI-powered template generation. Showcase your brand and create unique experiences for your clients.
                    </p>
                    <ul class="mt-6 space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>AI-powered template generation from descriptions or reference URLs</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Full HTML/CSS control for complete design freedom</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Dynamic placeholders for organisation data</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Professional branding for your organisation's public page</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Centralised AI Configuration Feature Section -->
<div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-indigo-200 bg-white shadow-xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left side: Content -->
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Centralised AI for Your Entire Organisation
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Configure AI services once at the organisation level, and all your candidates benefit automatically. Streamline AI access, manage costs efficiently, and ensure consistent AI capabilities across your team.
                    </p>
                    <ul class="mt-6 space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Configure once, benefit organisation-wide</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Support for cloud APIs (OpenAI, Anthropic, Gemini, Grok) and local AI</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Centralised cost management and API key security</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>All candidates get automatic access to AI features</span>
                        </li>
                    </ul>
                </div>
                <!-- Right side: Visual -->
                <div class="flex items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-600 px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white/20 shadow-lg backdrop-blur-sm">
                            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Centralised AI</h3>
                        <p class="mt-2 text-indigo-100">Configure Once • Benefit All</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Email Settings Feature Section -->
<div class="bg-white py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left side: Visual -->
                <div class="flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white/20 shadow-lg backdrop-blur-sm">
                            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Professional Email</h3>
                        <p class="mt-2 text-blue-100">Your Brand • Your Domain</p>
                    </div>
                </div>
                <!-- Right side: Content -->
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Send Emails from Your Organisation
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Professional email branding for all your candidate communications. Send invitations and messages from your organisation's email address with a custom display name.
                    </p>
                    <ul class="mt-6 space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Custom "From" email address from your organisation domain</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Custom display name for professional branding</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Improved deliverability and recognition</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Consistent branding across all candidate communications</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Responsive Images Feature Section -->
<div class="bg-gray-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <span class="inline-flex items-center rounded-full bg-green-100 px-4 py-1 text-sm font-medium text-green-800 mb-4">
                Performance & Accessibility
            </span>
            <h2 class="text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                Smart Responsive Images
            </h2>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                All images are automatically optimised for every device. Faster loading, better performance, and improved accessibility - automatically.
            </p>
        </div>
        
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="text-center">
                <div class="flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-blue-500 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Automatic Optimisation</h3>
                <p class="mt-2 text-base text-gray-500">
                    Images are automatically resized into multiple sizes. Your device downloads only what it needs, saving bandwidth and improving speed.
                </p>
            </div>
            
            <div class="text-center">
                <div class="flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-green-500 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Perfect on Every Device</h3>
                <p class="mt-2 text-base text-gray-500">
                    From mobile phones to high-resolution displays, images look crisp and load quickly. No manual resizing needed.
                </p>
            </div>
            
            <div class="text-center">
                <div class="flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-purple-500 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">SEO & Accessibility</h3>
                <p class="mt-2 text-base text-gray-500">
                    All images include proper alt text, semantic HTML, and structured data. Better for search engines and screen readers.
                </p>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <p class="text-sm text-gray-500">
                <strong>How it works:</strong> Upload any image → We automatically create 4 optimised sizes → The browser picks the perfect one for your device
            </p>
        </div>
    </div>
</div>

<!-- See It In Action Section -->
<div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 py-20 sm:py-24">
    <div class="mx-auto max-w-[950px] px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
            <div class="text-center lg:text-left flex-1 max-w-2xl mx-auto lg:mx-0">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    See It In Action
                </h2>
                <p class="mt-4 text-lg text-white">
                    Want to see what your CV could look like? Check out our example CV to explore all the features, templates, and styling options available.
                </p>
                <p class="mt-4 text-base text-white">
                    See how work experience, projects, skills, and certifications come together in a professional, shareable format. No account needed—just click and explore!
                </p>
                <p class="mt-4 text-base text-white">
                    Or scan the QR code with your phone to view it instantly!
                </p>
                <div class="mt-8">
                    <a href="/cv/@simple-cv-example" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-green-600 shadow-lg hover:bg-green-50 transition-colors">
                        View Example CV
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex justify-center lg:justify-center">
                <div class="text-center">
                    <a href="/cv/@simple-cv-example" target="_blank" rel="noopener noreferrer" class="block">
                        <?php
                        $exampleCvUrl = APP_URL . '/cv/@simple-cv-example';
                        // Generate QR code using a service or library - teal/emerald squares to match the gradient background
                        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&margin=0&color=0d9488&bgcolor=ffffff&data=' . urlencode($exampleCvUrl);
                        ?>
                        <div class="bg-white rounded-lg p-4 shadow-xl inline-block">
                            <img src="<?php echo e($qrCodeUrl); ?>" alt="QR Code to view example CV" class="w-48 h-48" style="image-rendering: crisp-edges;">
                        </div>
                    </a>
                    <p class="mt-4 text-sm text-white">Scan to view example CV</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="bg-white py-12 sm:py-16" id="process">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-lg font-semibold text-blue-600">Simple Process</h2>
            <p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                How It Works for Organisations
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
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Set Up Your Organisation</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Get your organisation account created, configure your branding, and invite your team members.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <span class="text-xl font-bold">2</span>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Invite Candidates</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Send invitations to your candidates to create their professional CVs with your branding.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <span class="text-xl font-bold">3</span>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Manage & Share</h3>
                    <p class="mt-2 text-base text-gray-500">
                        View candidate CVs, track progress, download PDFs, and share professional profiles with clients.
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
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Flexible Plans for Organisations</h2>
            <p class="mt-4 text-lg text-gray-300">
                Organisation accounts are managed by system administrators with customisable limits for candidates and team members. Contact support to set up your organisation account.
            </p>
            <p class="mt-2 text-sm text-blue-200">
                <strong>For Organisations:</strong> <a href="/organisations.php" class="underline hover:text-blue-100">View our getting started guide</a> • <strong>Individual Users:</strong> Create a free account below
            </p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
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
                    'button' => ['text' => 'Start for free', 'href' => '#', 'dataOpenRegister' => true],
                ],
                [
                    'label' => 'Lifetime',
                    'price' => 'TBC',
                    'detail' => 'one-time payment',
                    'highlight' => true,
                    'badge' => 'Beta Special',
                    'features' => [
                        'Unlimited sections & entries',
                        'Professional template with colours',
                        'Download print-ready PDFs',
                        'Priority email support',
                        'Lifetime access - no recurring fees',
                    ],
                    'button' => ['text' => 'Create account to purchase', 'href' => '#', 'dataOpenRegister' => true, 'requiresAccount' => true],
                ],
                [
                    'label' => 'Pro Monthly',
                    'price' => 'TBC',
                    'detail' => 'per month',
                    'highlight' => false,
                    'features' => [
                        'Unlimited sections & entries',
                        'Professional template with colours',
                        'Download print-ready PDFs',
                        'Priority email support',
                    ],
                    'button' => ['text' => 'Create account to purchase', 'href' => '#', 'dataOpenRegister' => true, 'requiresAccount' => true],
                ],
                [
                    'label' => 'Pro Annual',
                    'price' => 'TBC',
                    'detail' => 'per year',
                    'highlight' => false,
                    'features' => [
                        'Everything in Pro Monthly',
                        'Best value for serious job seekers',
                        'Annual billing with Stripe',
                        'Priority email support',
                    ],
                    'button' => ['text' => 'Create account to purchase', 'href' => '#', 'dataOpenRegister' => true, 'requiresAccount' => true],
                ],
            ];
            foreach ($pricingCards as $card):
                $classes = $card['highlight']
                    ? 'border-blue-500 ring-1 ring-blue-200 bg-white text-gray-900'
                    : 'border-gray-700 bg-gray-800 text-gray-100';
                ?>
                <div class="flex flex-col rounded-2xl border <?php echo $classes; ?> p-8 shadow-xl relative">
                    <?php if (!empty($card['badge'])): ?>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                                <?php echo e($card['badge']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
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
                        <?php
                        $buttonHref = $card['button']['href'] ?? '#auth-section';
                        $buttonPlan = $card['button']['plan'] ?? null;
                        $requiresAccount = $card['button']['requiresAccount'] ?? false;
                        if ($buttonPlan && $buttonHref === '/subscription.php') {
                            $buttonHref .= '?plan=' . urlencode($buttonPlan);
                        }
                        ?>
                        <?php
                        $hasDataOpenRegister = $card['button']['dataOpenRegister'] ?? false;
                        $buttonTag = $hasDataOpenRegister ? 'button' : 'a';
                        $buttonAttrs = $hasDataOpenRegister 
                            ? 'type="button" data-open-register'
                            : 'href="' . e($buttonHref) . '"';
                        ?>
                        <<?php echo $buttonTag; ?> <?php echo $buttonAttrs; ?>
                           class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition
                           <?php echo $card['highlight']
                               ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white'
                               : 'bg-white text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800'; ?>">
                            <?php echo e($card['button']['text']); ?>
                        </<?php echo $buttonTag; ?>>
                        <?php if ($requiresAccount): ?>
                            <p class="mt-2 text-xs text-center <?php echo $card['highlight'] ? 'text-gray-600' : 'text-gray-400'; ?>">
                                Create a free account first, then upgrade from your dashboard
                            </p>
                        <?php endif; ?>
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

<!-- Job Application Management Feature -->
<div class="bg-gradient-to-r from-blue-50 to-green-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left side: Content -->
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                            <svg class="h-7 w-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">Built-in Feature</span>
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        Land Your Dream Job
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Build your professional CV and track your job applications all in one place. Never lose track of where you've applied, stay on top of follow-ups, and land your next role.
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
                            <span>Monitor your progress from application to offer</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Set follow-up reminders and track interview stages</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>All included with your Simple CV Builder account</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Upload job description files (PDF, Word, Excel) for AI processing</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>AI-powered CV rewriting and quality assessment</span>
                        </li>
                    </ul>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <?php if (isLoggedIn()): ?>
                            <a href="/job-applications.php" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-green-700 transition-colors">
                                Manage Job Applications
                                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        <?php else: ?>
                            <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-green-700 transition-colors">
                                Create Free Account
                                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        <?php endif; ?>
                        <a href="/job-applications-features.php" class="inline-flex items-center justify-center rounded-lg border-2 border-green-600 bg-white px-6 py-3 text-base font-semibold text-green-600 shadow-lg hover:bg-green-50 transition-colors">
                            Learn More
                            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
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
                        <h3 class="text-2xl font-bold text-white">Job Application Tracker</h3>
                        <p class="mt-2 text-green-100">Organise • Track • Follow Up</p>
                        <p class="mt-4 text-sm text-white/90">
                            Included with every account
                        </p>
                    </div>
                </div>
            </div>
        </div>
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
                Create your free account to get started. Add your experience in minutes, and upgrade to a paid plan anytime from your dashboard.
            </p>
            <div class="mt-4 inline-flex items-center rounded-lg bg-blue-50 border border-blue-200 px-4 py-2">
                <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-blue-800">
                    <strong>How it works:</strong> Create your free account → Build your CV → Upgrade to unlock premium features
                </p>
            </div>
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
        } else {
            // Check for register query parameter from footer link
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('register') === '1') {
                openModal('register');
            }
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
