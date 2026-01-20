<?php
/**
 * AI CV Assessment Features Page
 * Promotional page showcasing AI-powered CV quality assessment features
 */

require_once __DIR__ . '/php/helpers.php';

$pageTitle = 'AI CV Quality Assessment';
$canonicalUrl = APP_URL . '/ai-cv-assessment.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Get AI-powered feedback on your CV quality. Detect date discrepancies, formatting issues, and receive actionable improvement suggestions with AI-generated content.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content">
        <!-- Hero Section -->
        <section class="bg-green-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                        AI-Powered CV Quality Assessment
                    </h1>
                    <p class="mt-4 text-xl text-green-100 max-w-3xl mx-auto">
                        Get comprehensive, actionable feedback on your CV. Detect issues, receive AI-generated improvements, and create CVs that stand out to employers and ATS systems. Powered by browser-based AI - no setup required, works instantly in your browser.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <?php if (isLoggedIn()): ?>
                            <a href="/cv-quality.php" class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-green-600 shadow-lg hover:bg-green-50 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Assess Your CV Now
                            </a>
                            <a href="/cv-variants.php" class="inline-flex items-center justify-center rounded-lg border-2 border-white px-6 py-3 text-base font-semibold text-white hover:bg-white hover:text-green-600 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Generate AI CV
                            </a>
                        <?php else: ?>
                            <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-green-600 shadow-lg hover:bg-green-50 transition-colors">
                                Get Started Free
                            </button>
                            <a href="/cv-quality.php" class="inline-flex items-center justify-center rounded-lg border-2 border-white px-6 py-3 text-base font-semibold text-white hover:bg-white hover:text-green-600 transition-colors">
                                Learn More
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- What It Does Section -->
        <section class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        What Our AI Assessment Does
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Our AI-powered assessment analyses your CV across multiple dimensions, providing detailed feedback and actionable improvements to help you create a standout CV. All AI features run directly in your browser - no cloud services or API keys needed. For organisations or users with cloud AI accounts, advanced cloud-based options are also available.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Date Analysis -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-8 border border-blue-200">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Date Discrepancy Detection</h3>
                        <p class="text-gray-700 mb-4">
                            Automatically identifies gaps between employment periods, missing dates, and overlapping dates that need explanation.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Detects employment gaps automatically</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Flags missing start or end dates</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Suggests how to address gaps</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Formatting Analysis -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-8 border border-green-200">
                        <div class="flex items-center justify-center w-16 h-16 bg-green-600 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Formatting Issue Detection</h3>
                        <p class="text-gray-700 mb-4">
                            Identifies inconsistencies in formatting, structure, and presentation that could impact readability and ATS compatibility.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Inconsistent date formatting</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Job title formatting inconsistencies</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Structure and readability issues</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Content Suggestions -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-8 border border-purple-200">
                        <div class="flex items-center justify-center w-16 h-16 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">AI-Generated Improvements</h3>
                        <p class="text-gray-700 mb-4">
                            Receive actual improved versions of your content with quantifiable achievements, better descriptions, and enhanced professional summaries.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Improved professional summaries</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Enhanced work descriptions with metrics</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>One-click apply improvements</span>
                            </li>
                        </ul>
                    </div>

                    <!-- ATS Compatibility -->
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-8 border border-indigo-200">
                        <div class="flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">ATS Compatibility Scoring</h3>
                        <p class="text-gray-700 mb-4">
                            Evaluates how well Applicant Tracking Systems can parse and understand your CV, with specific recommendations for improvement.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-indigo-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Formatting compatibility checks</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-indigo-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Structure and keyword optimisation</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-indigo-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Standard section compliance</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Content Quality -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-8 border border-yellow-200">
                        <div class="flex items-center justify-center w-16 h-16 bg-yellow-600 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Content Quality Analysis</h3>
                        <p class="text-gray-700 mb-4">
                            Evaluates the substance, impact, and effectiveness of your CV content with specific recommendations for improvement.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Quantifiable achievements analysis</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Relevance and impact assessment</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Specific improvement suggestions</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Keyword Matching -->
                    <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-8 border border-pink-200">
                        <div class="flex items-center justify-center w-16 h-16 bg-pink-600 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Keyword Matching</h3>
                        <p class="text-gray-700 mb-4">
                            When you provide a job description, the AI analyses how well your CV matches the requirements and suggests improvements.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-pink-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Keyword alignment scoring</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-pink-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Skills and qualification matching</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-pink-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Tailored improvement recommendations</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        How It Works
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Get comprehensive CV feedback in just a few simple steps
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 rounded-lg text-white text-2xl font-bold mx-auto mb-4">
                            1
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Select Your CV</h3>
                        <p class="text-gray-600">
                            Choose which CV variant to assess - your master CV or any AI-generated variant. You can also assess against a specific job description for targeted feedback.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 rounded-lg text-white text-2xl font-bold mx-auto mb-4">
                            2
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">AI Analysis</h3>
                        <p class="text-gray-600">
                            Our AI analyses your CV across multiple dimensions: ATS compatibility, content quality, formatting, date consistency, and keyword matching (if job description provided).
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 rounded-lg text-white text-2xl font-bold mx-auto mb-4">
                            3
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Get Results & Improve</h3>
                        <p class="text-gray-600">
                            Receive detailed scores, specific recommendations with examples, and AI-generated improvements you can apply with one click. Review, apply, and enhance your CV instantly.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="bg-gradient-to-br from-purple-50 to-indigo-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Why Use AI CV Assessment?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Get professional, comprehensive feedback that helps you create CVs that stand out
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Save Time</h3>
                        <p class="text-gray-600 text-sm">
                            Get comprehensive feedback in seconds instead of hours of manual review. AI-generated improvements can be applied instantly.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">ATS Optimised</h3>
                        <p class="text-gray-600 text-sm">
                            Ensure your CV passes through Applicant Tracking Systems with specific recommendations for ATS compatibility improvements.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Professional Quality</h3>
                        <p class="text-gray-600 text-sm">
                            Receive feedback that matches professional CV review standards, helping you create CVs that impress employers.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Catch Issues Early</h3>
                        <p class="text-gray-600 text-sm">
                            Automatically detect date discrepancies, formatting inconsistencies, and missing information before you submit your CV.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Job-Specific Feedback</h3>
                        <p class="text-gray-600 text-sm">
                            When you provide a job description, get targeted feedback on keyword matching and alignment with specific role requirements.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-600 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Continuous Improvement</h3>
                        <p class="text-gray-600 text-sm">
                            Run assessments multiple times as you improve your CV. Track your progress and see scores improve with each iteration.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-green-700 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    Ready to Improve Your CV?
                </h2>
                <p class="text-xl text-green-100 mb-8 max-w-2xl mx-auto">
                    Get comprehensive AI-powered feedback on your CV quality, detect issues automatically, and receive actionable improvements you can apply instantly.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="/cv-quality.php" class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-green-600 shadow-lg hover:bg-green-50 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Assess Your CV Now
                        </a>
                        <a href="/cv-variants.php" class="inline-flex items-center justify-center rounded-lg border-2 border-white px-6 py-3 text-base font-semibold text-white hover:bg-white hover:text-green-600 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate AI CV
                        </a>
                    <?php else: ?>
                        <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-green-600 shadow-lg hover:bg-green-50 transition-colors">
                            Create Free Account
                        </button>
                        <a href="/ai-settings.php" class="inline-flex items-center justify-center rounded-lg border-2 border-white px-6 py-3 text-base font-semibold text-white hover:bg-white hover:text-green-600 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            AI Settings
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php partial('footer'); ?>
</body>
</html>

