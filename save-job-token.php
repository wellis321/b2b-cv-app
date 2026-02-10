<?php
/**
 * Save job extension token – account page.
 * Get or regenerate your scv_ token for the browser extension. Auth required.
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

$pageTitle = 'Save job token | Simple CV Builder';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle,
        'metaDescription' => 'Get your save token for the Simple CV Builder browser extension.',
        'canonicalUrl' => APP_URL . '/save-job-token.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-900">Save job extension token</h1>
                <p class="mt-1 text-sm text-gray-600">Use this token in the browser extension to save jobs from any page. Keep it private; treat it like a password.</p>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Your save token</label>
                    <div id="save-token-card" class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3" data-csrf="<?php echo e(csrfToken()); ?>">
                        <div class="flex flex-wrap items-center gap-2">
                            <code id="save-token-masked" class="text-sm bg-white px-3 py-2 rounded border border-gray-300 font-mono">—</code>
                            <button type="button" id="save-token-copy-btn" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Copy token
                            </button>
                            <button type="button" id="save-token-regenerate-btn" class="inline-flex items-center rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">Regenerate</button>
                        </div>
                        <p class="text-xs text-gray-500">After regenerating, copy the new token and paste it into the extension options. Your old token will stop working.</p>
                        <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-sm text-blue-800 mt-2">
                            <strong>No Save button on this page.</strong> Your token is already saved in your account. Use <strong>Copy token</strong> above, then open the extension options, paste the token there, and click <strong>Save settings</strong> in the extension.
                        </div>
                        <div id="save-token-message" class="hidden text-sm rounded-lg p-3"></div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Two ways to save jobs</h2>
                    <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-700 mb-2"><strong>Option 1: Quick add from link (no extension needed)</strong></p>
                        <p class="text-xs text-gray-600 mb-3">Go to your <a href="/content-editor.php#jobs" class="text-blue-600 hover:underline">job list</a> and click <strong>"Quick add from link"</strong>. Paste the job URL, add optional details, and save. Simple and works in any browser.</p>
                        <p class="text-sm text-gray-700 mb-2"><strong>Option 2: Browser extension (one-click save)</strong></p>
                        <p class="text-xs text-gray-600">Install the extension below to save jobs with one click from any job page without leaving the page or copying URLs.</p>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900 mb-3 mt-6">Using the extension</h2>
                    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-800 font-medium mb-2">
                            <svg class="inline-block w-4 h-4 mr-1.5 align-text-bottom" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Chrome/Chromium browsers only
                        </p>
                        <p class="text-xs text-amber-700">This extension works with Chrome, Edge, and Brave browsers. Firefox support is limited.</p>
                    </div>
                    <div class="mb-4">
                        <a href="/download-extension.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Extension for Chrome
                        </a>
                    </div>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li><strong>Extract the ZIP:</strong> After downloading, extract the ZIP file to a folder on your computer (e.g., <code class="bg-gray-100 px-1 rounded text-xs">Downloads/simple-cv-extension</code>).</li>
                        <li>Open the extension options, enter <strong>this site’s URL</strong> (copy from your address bar), paste the <strong>Save token</strong> above, and click <strong>Save settings</strong>.</li>
                        <li><strong>Install in Chrome:</strong> Open <code class="bg-gray-100 px-1 rounded">chrome://extensions</code> → Enable <strong>Developer mode</strong> (top right) → Click <strong>Load unpacked</strong> → Select the extracted extension folder.</li>
                        <li><strong>Configure:</strong> Open the extension options, enter <strong>this site's URL</strong> (copy from your address bar), paste the <strong>Save token</strong> above, and click <strong>Save settings</strong>.</li>
                        <li><strong>Use it:</strong> On any job page, click the extension icon or right‑click → &quot;Save job to Simple CV Builder&quot;.</li>
                    </ol>
                    <div class="mt-6 p-4 bg-blue-50 border-2 border-blue-300 rounded-lg">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-blue-900 mb-2">Need detailed step-by-step instructions?</p>
                                <a href="/extension-setup-guide.php" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    View Full Setup Guide
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p class="mt-4 text-center">
            <a href="/content-editor.php#jobs" class="text-sm font-medium text-blue-600 hover:text-blue-800">Back to Jobs</a>
            <span class="text-gray-300 mx-2">|</span>
            <a href="/job-applications-features.php" class="text-sm font-medium text-blue-600 hover:text-blue-800">Job tracker features</a>
        </p>
    </main>

    <?php partial('footer'); ?>
    <script>
(function() {
    var card = document.getElementById('save-token-card');
    if (!card) return;
    var csrf = card.getAttribute('data-csrf');
    var maskedEl = document.getElementById('save-token-masked');
    var copyBtn = document.getElementById('save-token-copy-btn');
    var regenBtn = document.getElementById('save-token-regenerate-btn');
    var msgEl = document.getElementById('save-token-message');

    function showMsg(text, isError) {
        msgEl.textContent = text;
        msgEl.className = 'text-sm rounded-lg p-3 ' + (isError ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800');
        msgEl.classList.remove('hidden');
        setTimeout(function() { msgEl.classList.add('hidden'); }, 5000);
    }

    function loadMasked() {
        fetch('/api/job-saver-token.php', { credentials: 'include' })
            .then(function(r) {
                return r.json().then(function(d) {
                    if (!r.ok) {
                        showMsg(d.error || 'Could not load token.', true);
                        return;
                    }
                    if (d.masked) maskedEl.textContent = d.masked;
                });
            })
            .catch(function() { showMsg('Could not load token.', true); });
    }

    copyBtn.addEventListener('click', function() {
        fetch('/api/job-saver-token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ csrf_token: csrf, action: 'copy' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.token) {
                showMsg(d.error || 'Failed.', true);
                return;
            }
            function doCopy(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() { showMsg('Token copied to clipboard.'); }).catch(function() {
                        prompt('Copy this token:', text);
                        showMsg('Paste the token into the extension options.');
                    });
                } else {
                    prompt('Copy this token:', text);
                    showMsg('Paste the token into the extension options.');
                }
            }
            doCopy(d.token);
        })
        .catch(function() { showMsg('Failed to get token.', true); });
    });

    regenBtn.addEventListener('click', function() {
        if (!confirm('Generate a new token? Your current token will stop working. You must paste the new token into the extension.')) return;
        fetch('/api/job-saver-token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ csrf_token: csrf, action: 'regenerate' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.token) {
                showMsg(d.error || 'Failed.', true);
                return;
            }
            maskedEl.textContent = d.masked;
            function doCopy(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() { showMsg('New token generated and copied. Paste it into the extension options.'); }).catch(function() {
                        prompt('Copy your new token (paste into extension options):', text);
                        showMsg('Paste the new token into the extension options.');
                    });
                } else {
                    prompt('Copy your new token (paste into extension options):', text);
                    showMsg('Paste the new token into the extension options.');
                }
            }
            doCopy(d.token);
        })
        .catch(function() { showMsg('Failed to regenerate.', true); });
    });

    loadMasked();
})();
    </script>
</body>
</html>
