<?php
// Marketing page for non-logged in users
?>

<!-- Hero Section -->
<div class="relative overflow-hidden bg-white">
    <div class="pt-32 pb-16 sm:pt-36 sm:pb-20 md:pb-32 lg:pt-56 lg:pb-48">
        <div class="relative mx-auto max-w-7xl px-4 sm:static sm:px-6 lg:px-8">
            <div class="sm:max-w-lg relative z-10">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    Free CV Builder UK. Track Applications. Get Hired.
                </h1>
                <p class="mt-4 text-xl text-gray-500">
                    Free CV builder UK with job application tracking and AI cover letters. For job seekers and recruitment agencies—create a shareable CV link, unlock PDF export, and manage your job search in one place.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <a href="/organisations.php" class="inline-block rounded-md border border-transparent bg-blue-600 px-8 py-3 text-center font-medium text-white hover:bg-blue-700">
                        For Organisations
                    </a>
                    <a href="/individual-users.php" class="inline-block rounded-md border border-gray-300 bg-white px-8 py-3 text-center font-medium text-gray-700 hover:bg-gray-50">
                        Individual Users
                    </a>
                </div>
                <p class="mt-6 text-sm text-gray-500">
                    <a href="/#pricing" class="font-medium text-blue-600 hover:text-blue-800">Try 7 days for £1.95</a> — full access, then subscribe or stay free.
                </p>
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

<!-- Browser-Based AI Hero CTA - full width (for everyone) -->
<section class="browser-ai-hero-bg relative overflow-hidden w-full bg-cover bg-center bg-no-repeat py-14 sm:py-18 lg:py-22" style="background-image: linear-gradient(to bottom right, rgba(88, 28, 135, 0.0), rgba(67, 56, 202, 0.78)), url('/static/images/home/AI-in-the-browseer.png');">
    <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-sm mb-6">
                    <svg class="w-9 h-9 sm:w-11 sm:h-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-violet-200 text-sm font-semibold uppercase tracking-wider">Featured</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    AI that runs in your browser
                </h2>
                <p class="mt-4 text-lg sm:text-xl text-white/90 max-w-2xl mx-auto leading-relaxed">
                    No API keys. No cloud signup. No setup. Tailor your CV, assess quality, and generate cover letters—all in your browser. Your data stays on your device. Cloud AI (OpenAI, Anthropic, Gemini) is also available if you prefer.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/browser-ai-check.php" class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3.5 text-base font-semibold text-purple-700 shadow-lg hover:bg-violet-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Check if your browser supports it
                    </a>
                    <?php if (!isLoggedIn()): ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-xl border-2 border-white/80 px-6 py-3.5 text-base font-semibold text-white hover:bg-white/10 transition-colors">
                            Create free account
                        </button>
                    <?php else: ?>
                        <a href="/content-editor.php#ai-tools" class="inline-flex items-center justify-center rounded-xl border-2 border-white/80 px-6 py-3.5 text-base font-semibold text-white hover:bg-white/10 transition-colors">
                            Try AI in the editor
                        </a>
                    <?php endif; ?>
                </div>
                <p class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-1 text-sm text-white/70">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Private — runs locally
                    </span>
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        No API keys required
                    </span>
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Free to use
                    </span>
                </p>
    </div>
</section>

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
        
    </div>
</div>

<!-- Job Application Management Feature (for everyone / individuals) -->
<div class="bg-gradient-to-r from-blue-50 to-green-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left side: Content -->
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-12">
                    <div class="mb-6 flex items-center gap-3 flex-wrap">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                            <svg class="h-7 w-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">Built-in Feature</span>
                        <span class="inline-flex rounded-full bg-green-600 px-3 py-1 text-sm font-semibold text-white">New: One-click save</span>
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
                        <li class="flex items-start">
                            <svg class="mt-1 mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Save jobs in one click</strong>—bookmark or paste a link from any site, add details later. Set priorities and closing-date reminders so you never miss a deadline.</span>
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

<!-- See It In Action Section (for everyone) -->
<div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 py-20 sm:py-24">
    <div class="mx-auto max-w-[950px] px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-stretch">
            <div class="text-center lg:text-left flex-1 max-w-2xl mx-auto lg:mx-0 flex flex-col">
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
            <div class="mt-6 lg:mt-0 flex justify-center lg:justify-center items-center">
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

<!-- Professional CV Templates (for everyone) -->
<div class="bg-gradient-to-br from-pink-50 via-rose-50 to-red-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <span class="inline-flex items-center rounded-full bg-pink-100 px-4 py-1 text-sm font-medium text-pink-800 mb-4">
                Professional Designs
            </span>
            <h2 class="text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                Professional CV Templates
            </h2>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                Choose from professional templates with customisable colours. Create a standout CV that matches your industry and personal style.
            </p>
        </div>
        
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="text-center bg-white rounded-xl border-2 border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-500 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Minimal Template</h3>
                <p class="mt-2 text-base text-gray-500">
                    Clean, simple design perfect for traditional industries. ATS-friendly and professional. Available on free plan.
                </p>
                <span class="mt-4 inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Free plan</span>
            </div>
            
            <div class="text-center bg-white rounded-xl border-2 border-blue-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-blue-500 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Professional Blue</h3>
                <p class="mt-2 text-base text-gray-500">
                    Modern, professional design with customisable colours. Perfect for corporate roles and business environments.
                </p>
                <span class="mt-4 inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">Pro plan</span>
            </div>
            
            <div class="text-center bg-white rounded-xl border-2 border-purple-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-purple-500 text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Modern Template</h3>
                <p class="mt-2 text-base text-gray-500">
                    Contemporary design with bold accents. Great for creative industries, tech roles, and modern workplaces.
                </p>
                <span class="mt-4 inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">Pro plan</span>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <p class="text-sm text-gray-600 mb-6">
                <strong>All templates include:</strong> Customisable colours • ATS-friendly formatting • Print-ready PDFs • Mobile-optimised online view
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if (isLoggedIn()): ?>
                    <a href="/content-editor.php" class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-pink-700 transition-colors">
                        Choose your template
                    </a>
                <?php else: ?>
                    <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-pink-700 transition-colors">
                        Create free account
                    </button>
                <?php endif; ?>
                <a href="/cv-templates-feature.php" class="inline-flex items-center justify-center rounded-lg border-2 border-pink-600 bg-white px-6 py-3 text-base font-semibold text-pink-600 hover:bg-pink-50 transition-colors">
                    Learn more →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Standout Features -->
<div class="border-t border-gray-200 bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 py-12 sm:py-16" id="standout-features">
    <span id="features" aria-hidden="true"></span>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-4 py-1 text-sm font-medium text-indigo-800 mb-4">
                What Makes Us Different
            </span>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                Features That Set You Apart
            </h2>
            <p class="mt-3 max-w-2xl text-lg text-gray-600 lg:mx-auto">
                Stand out from the crowd with unique features that make your CV more accessible, shareable, and tailored to every opportunity.
            </p>
            <div class="mt-8 mb-12 pt-8 border-t border-indigo-200">
                <a href="/all-features.php" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    View All Features
                </a>
            </div>
        </div>

        <div class="mt-10">
            <dl class="space-y-10 md:grid md:grid-cols-3 md:space-y-0 md:gap-x-8 md:gap-y-10">
                <?php
                $features = [
                    [
                        'title' => 'Your Unique Online CV',
                        'description' => 'Get a shareable CV link like <code class="bg-indigo-50 px-1.5 py-0.5 rounded text-xs font-mono text-indigo-700">/cv/@your-username</code>. Share it anywhere—email signatures, LinkedIn, social media. Update once, everyone sees the latest version instantly. No more sending outdated PDFs.',
                        'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
                        'link' => '/online-cv-username.php',
                        'linkText' => 'Learn about online CVs'
                    ],
                    [
                        'title' => 'QR Codes in Your PDFs',
                        'description' => 'Optionally include a QR code in your PDF exports that links directly to your online CV. Perfect for networking events, job fairs, and printed CVs. Recruiters can scan and instantly access your latest CV—no typing URLs or searching.',
                        'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
                        'link' => '/qr-codes-pdf.php',
                        'linkText' => 'See QR codes in action'
                    ],
                    [
                        'title' => 'CV Variants for Every Job',
                        'description' => 'Create unlimited tailored CV versions for different applications. Use AI to automatically match keywords and tailor content, or manually customise sections. Keep your master CV safe while generating job-specific variants. Link each variant to its application for easy tracking.',
                        'icon' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z',
                        'link' => '/cv-variants.php',
                        'linkText' => 'Explore CV variants'
                    ],
                    [
                        'title' => 'AI Cover Letters',
                        'description' => 'Generate professional, tailored cover letters for each job application using our free Browser AI. No API keys needed—it runs directly in your browser. Link cover letters to specific applications and CV variants for complete application packages.',
                        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        'link' => '/cover-letters-feature.php',
                        'linkText' => 'Create cover letters'
                    ],
                    [
                        'title' => 'Free Browser AI',
                        'description' => 'All AI features run directly in your browser—no cloud services, no API keys, no setup required. Generate CV variants, assess quality, extract keywords, and create cover letters completely free. Your data stays private on your device.',
                        'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                        'link' => '/browser-ai-free.php',
                        'linkText' => 'Learn about Browser AI'
                    ],
                    [
                        'title' => 'Tailor Your CV Content',
                        'description' => 'Control exactly what appears in your PDF and online CV. Reorder sections, show or hide specific entries, and create different versions for different audiences—all from one master CV. Perfect for tailoring to different industries or roles.',
                        'icon' => 'M4 6h16M4 12h16M4 18h16',
                        'link' => '/tailor-cv-content.php',
                        'linkText' => 'Customise your CV'
                    ]
                ];
                foreach ($features as $feature): ?>
                    <div class="relative bg-white rounded-xl border-2 border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <dt>
                            <div class="absolute flex h-12 w-12 items-center justify-center rounded-md bg-indigo-500 text-white -top-6 left-6">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($feature['icon']); ?>" />
                                </svg>
                            </div>
                            <p class="ml-0 text-lg leading-6 font-medium text-gray-900 mb-2"><?php echo e($feature['title']); ?></p>
                        </dt>
                        <dd class="mt-2 ml-0 text-base text-gray-500 mb-4">
                            <?php echo $feature['description']; ?>
                        </dd>
                        <a href="<?php echo e($feature['link']); ?>" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            <?php echo e($feature['linkText']); ?>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</div>

<!-- Who is this for? strip -->
<div class="border-y border-gray-200 bg-gray-50/80 py-6 sm:py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm font-semibold uppercase tracking-wider text-gray-500 mb-4">Who is this for?</p>
        <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 justify-center items-center">
            <a href="/individual-users.php" class="flex items-center gap-3 rounded-xl border-2 border-gray-200 bg-white px-6 py-4 shadow-sm hover:border-blue-300 hover:shadow-md transition-all w-full sm:w-auto max-w-xs sm:max-w-none">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <div class="text-left">
                    <span class="font-semibold text-gray-900">Individual job seekers</span>
                    <p class="text-sm text-gray-600">Build your CV, track applications, use AI tools</p>
                </div>
            </a>
            <a href="/organisations.php" class="flex items-center gap-3 rounded-xl border-2 border-gray-200 bg-white px-6 py-4 shadow-sm hover:border-blue-300 hover:shadow-md transition-all w-full sm:w-auto max-w-xs sm:max-w-none">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <div class="text-left">
                    <span class="font-semibold text-gray-900">Recruitment agencies & organisations</span>
                    <p class="text-sm text-gray-600">Manage candidates, teams, and branded CVs</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Pricing Section -->
<?php partial('home-pricing', ['pricingUseRegisterModal' => true]); ?>

<!-- Testimonials Section -->
<div class="bg-gray-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-lg font-semibold text-blue-600">Testimonials</h2>
            <p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-gray-900 sm:text-4xl">
                What Our Users Say
            </p>
            <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">
                Join job seekers building professional CVs and tracking applications in one place.
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
