<?php
/**
 * Extension Setup Guide – Complete guide for installing and using the browser extension
 */

require_once __DIR__ . '/php/helpers.php';

$pageTitle = 'Extension Setup Guide';
$canonicalUrl = APP_URL . '/extension-setup-guide.php';
$img = function($id, $w = 800) { return 'https://images.unsplash.com/photo-' . $id . '?w=' . $w . '&q=80'; };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => 'Complete guide for installing and configuring the Simple CV Builder browser extension to save jobs from any website.',
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-8 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h1 class="text-3xl font-bold text-gray-900">Save Job Browser Extension – Setup Guide</h1>
                <p class="mt-2 text-lg text-gray-600">Complete guide for installing and using the Simple CV Builder browser extension</p>
            </div>

            <div class="p-6 sm:p-8 space-y-8 prose prose-sm max-w-none">
                <!-- How it works -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">How it works</h2>
                    <p class="text-gray-700 mb-4">This extension lets you save the <strong>current tab's URL and title</strong> as a job in your Simple CV Builder job list <strong>without leaving the page</strong>. One click (popup or context menu) and the job is created in your account.</p>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li><strong>You're browsing a job page</strong> (Indeed, LinkedIn, company career pages, or any job listing).</li>
                        <li><strong>Click the extension icon</strong> or right‑click the page → <strong>"Save job to Simple CV Builder"</strong>.</li>
                        <li>The extension sends the page URL and title to Simple CV Builder using your <strong>save token</strong>.</li>
                        <li>The job is added to your list immediately. You can add details later in Job Applications or the content editor.</li>
                    </ol>
                    <p class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800"><strong>No new tab, no copy‑paste.</strong> The job appears in your list as soon as you're logged in on the site.</p>
                </section>

                <!-- Step 1: Get Your Save Token -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Step 1: Get Your Save Token</h2>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li><strong>Log in</strong> to Simple CV Builder.</li>
                        <li>Go to <strong>My CV</strong> → <strong>Get save token</strong> (or visit <a href="/save-job-token.php" class="text-blue-600 hover:underline">/save-job-token.php</a>).</li>
                        <li>Click <strong>Copy token</strong> (or <strong>Regenerate</strong> if you want a new one).</li>
                        <li><strong>Keep this token safe</strong> — you'll paste it into the extension in Step 3.</li>
                    </ol>
                    <?php if (isLoggedIn()): ?>
                        <div class="mt-4">
                            <a href="/save-job-token.php" class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                                Get your save token →
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-800"><strong>Security tip:</strong> Treat your save token like a password. If it's ever exposed, regenerate it immediately.</p>
                    </div>
                </section>

                <!-- Step 2: Download and Install -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Step 2: Download and Install the Extension</h2>
                    
                    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-800 font-medium mb-2">
                            <svg class="inline-block w-4 h-4 mr-1.5 align-text-bottom" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Chrome/Chromium browsers only
                        </p>
                        <p class="text-xs text-amber-700">This extension works with Chrome, Edge, and Brave browsers. Firefox support is limited.</p>
                    </div>

                    <div class="mb-6">
                        <?php if (isLoggedIn()): ?>
                            <a href="/download-extension.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Extension for Chrome
                            </a>
                        <?php else: ?>
                            <p class="text-sm text-gray-600 mb-2">Log in to download the extension</p>
                            <button type="button" data-open-register class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition-colors">
                                Create free account
                            </button>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3 mt-6">For Chrome/Edge/Brave (Chromium-based browsers)</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li><strong>Download the extension:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li><a href="/download-extension.php" class="text-blue-600 hover:underline">Download Extension</a> on Simple CV Builder to download the extension as a ZIP file.</li>
                            </ul>
                            Extract the ZIP file to a location you can remember (e.g., <code class="bg-gray-100 px-1 rounded text-xs">Downloads/simple-cv-extension</code>).
                        </li>
                        <li><strong>Open Chrome Extensions page:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Type <code class="bg-gray-100 px-1 rounded text-xs">chrome://extensions</code> in your address bar and press Enter.</li>
                                <li>Or go to <strong>Menu</strong> (three dots) → <strong>Extensions</strong> → <strong>Manage extensions</strong>.</li>
                            </ul>
                        </li>
                        <li><strong>Enable Developer Mode:</strong> Toggle <strong>Developer mode</strong> ON (top right corner of the extensions page).</li>
                        <li><strong>Load the extension:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Click <strong>Load unpacked</strong>.</li>
                                <li>Navigate to and select the <code class="bg-gray-100 px-1 rounded text-xs">extension</code> folder (the one containing <code class="bg-gray-100 px-1 rounded text-xs">manifest.json</code>).</li>
                                <li>Click <strong>Select Folder</strong>.</li>
                            </ul>
                        </li>
                        <li><strong>Verify installation:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>You should see "Save job to Simple CV Builder" in your extensions list.</li>
                                <li>The extension icon should appear in your browser toolbar.</li>
                            </ul>
                        </li>
                    </ol>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3 mt-6">For Firefox</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li><strong>Download the extension files</strong> (same as Chrome above).</li>
                        <li><strong>Open Firefox Add-ons page:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Type <code class="bg-gray-100 px-1 rounded text-xs">about:debugging</code> in your address bar.</li>
                                <li>Click <strong>This Firefox</strong> in the left sidebar.</li>
                            </ul>
                        </li>
                        <li><strong>Load the extension:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Click <strong>Load Temporary Add-on...</strong>.</li>
                                <li>Navigate to the <code class="bg-gray-100 px-1 rounded text-xs">extension</code> folder and select <code class="bg-gray-100 px-1 rounded text-xs">manifest.json</code>.</li>
                                <li>Click <strong>Open</strong>.</li>
                            </ul>
                        </li>
                    </ol>
                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800"><strong>Note:</strong> Firefox requires the extension to be reloaded each time you restart Firefox. For a permanent installation, you'll need to package it as a <code class="bg-yellow-100 px-1 rounded text-xs">.xpi</code> file or publish it to Firefox Add-ons.</p>
                    </div>
                </section>

                <!-- Step 3: Configure -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Step 3: Configure the Extension</h2>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li><strong>Open extension options:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li><strong>Chrome/Edge:</strong> Click the extension icon in your toolbar → <strong>Options</strong>, or right‑click the extension icon → <strong>Options</strong>.</li>
                                <li><strong>Firefox:</strong> The options page should open automatically, or click the extension icon → <strong>Options</strong>.</li>
                            </ul>
                        </li>
                        <li><strong>Enter your settings:</strong>
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li><strong>Site URL:</strong>
                                    <ul class="list-circle list-inside ml-6 mt-1 space-y-1">
                                        <li>Click <strong>Production</strong> for <code class="bg-gray-100 px-1 rounded text-xs">https://simple-cv-builder.com</code></li>
                                        <li>Or click <strong>Testing</strong> for <code class="bg-gray-100 px-1 rounded text-xs">https://lightcoral-raccoon-941077.hostingersite.com</code></li>
                                        <li>Or enter a custom URL (no trailing slash).</li>
                                    </ul>
                                </li>
                                <li><strong>Save token:</strong> Paste the token you copied in Step 1.</li>
                            </ul>
                        </li>
                        <li><strong>Save:</strong> Click <strong>Save settings</strong>. You should see a confirmation message.</li>
                    </ol>
                </section>

                <!-- Step 4: Use the Extension -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Step 4: Use the Extension</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Method 1: Extension Icon</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Navigate to any job listing page (Indeed, LinkedIn, company careers, etc.).</li>
                        <li>Click the <strong>Simple CV Builder extension icon</strong> in your browser toolbar.</li>
                        <li>Click <strong>Save job</strong> in the popup.</li>
                        <li>The job is added to your list immediately!</li>
                    </ol>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3 mt-6">Method 2: Right-Click Menu</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Navigate to any job listing page.</li>
                        <li><strong>Right‑click</strong> anywhere on the page.</li>
                        <li>Select <strong>"Save job to Simple CV Builder"</strong> from the context menu.</li>
                        <li>The job is added to your list immediately!</li>
                    </ol>

                    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800 font-medium mb-2">After Saving</p>
                        <ul class="list-disc list-inside space-y-1 text-sm text-green-700">
                            <li>The job appears in your <strong>Job Applications</strong> list with the page title and URL.</li>
                            <li>You can add company name, closing date, notes, and other details later.</li>
                            <li>The job is linked to your account via your save token.</li>
                        </ul>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Troubleshooting</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Extension icon not showing</h3>
                            <ul class="list-disc list-inside space-y-1 text-gray-700">
                                <li><strong>Chrome:</strong> Check that the extension is enabled in <code class="bg-gray-100 px-1 rounded text-xs">chrome://extensions</code>.</li>
                                <li><strong>Firefox:</strong> Check <code class="bg-gray-100 px-1 rounded text-xs">about:addons</code> to ensure the extension is installed and enabled.</li>
                                <li>Try refreshing the extensions page or restarting your browser.</li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">"Save job" button doesn't work</h3>
                            <ul class="list-disc list-inside space-y-1 text-gray-700">
                                <li><strong>Check your token:</strong> Make sure you've pasted your save token correctly in the extension options (no extra spaces).</li>
                                <li><strong>Check Site URL:</strong> Ensure the Site URL matches the Simple CV Builder site you're using (production vs testing).</li>
                                <li><strong>Check you're logged in:</strong> The extension requires you to be logged in to Simple CV Builder in another tab.</li>
                                <li><strong>Regenerate token:</strong> If it still doesn't work, regenerate your save token and update it in the extension options.</li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Extension options won't save</h3>
                            <ul class="list-disc list-inside space-y-1 text-gray-700">
                                <li>Make sure you've entered both <strong>Site URL</strong> and <strong>Save token</strong>.</li>
                                <li>Check that the Site URL doesn't have a trailing slash (<code class="bg-gray-100 px-1 rounded text-xs">/</code>).</li>
                                <li>Try closing and reopening the options page.</li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Job not appearing in my list</h3>
                            <ul class="list-disc list-inside space-y-1 text-gray-700">
                                <li><strong>Check you're logged in:</strong> Make sure you're logged in to Simple CV Builder.</li>
                                <li><strong>Check the Site URL:</strong> Ensure the extension is pointing to the correct Simple CV Builder site.</li>
                                <li><strong>Check your token:</strong> Verify your save token is correct and hasn't been regenerated.</li>
                                <li><strong>Refresh your job list:</strong> Try refreshing the Job Applications page.</li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">LinkedIn job titles showing as "LinkedIn"</h3>
                            <p class="text-gray-700">The extension automatically extracts job titles from LinkedIn job pages. If you see "LinkedIn" instead of the job title, the extraction may have failed. You can edit the job title manually in your job list.</p>
                        </div>
                    </div>
                </section>

                <!-- Security & Privacy -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Security & Privacy</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Your save token</strong> is a long random secret tied to your account. Keep it private.</li>
                        <li><strong>The extension only sends:</strong>
                            <ul class="list-circle list-inside ml-6 mt-1 space-y-1">
                                <li>Page URL</li>
                                <li>Page title</li>
                                <li>Optional closing date and priority (if you set them)</li>
                            </ul>
                        </li>
                        <li><strong>The extension does NOT:</strong>
                            <ul class="list-circle list-inside ml-6 mt-1 space-y-1">
                                <li>Read other page content</li>
                                <li>Access your browsing history</li>
                                <li>Send data to third parties</li>
                                <li>Store your password or login credentials</li>
                            </ul>
                        </li>
                    </ul>
                </section>

                <!-- Need Help -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Need Help?</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li>Visit the <a href="/save-job-from-anywhere.php" class="text-blue-600 hover:underline">Save Job From Anywhere</a> feature page.</li>
                        <li>Check your <a href="/save-job-token.php" class="text-blue-600 hover:underline">save token page</a> for setup instructions.</li>
                        <li>Contact support if you continue to have issues.</li>
                    </ul>
                </section>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>
    <?php if (!isLoggedIn()): ?>
        <?php partial('auth-modals'); ?>
    <?php endif; ?>
</body>
</html>
