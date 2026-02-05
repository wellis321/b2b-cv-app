<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
            <div>
                <a href="/" class="inline-flex items-center space-x-3 mb-4">
                    <img src="/static/images/logo/black-logo-300.jpg" alt="Simple CV Builder" class="h-12 w-auto" />
                    <span class="text-2xl font-bold text-blue-600">Simple CV Builder</span>
                </a>
                <p class="text-sm text-gray-600">
                    Create a professional CV that stands out, updates in real-time, and can be shared as a simple link.
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="/" class="text-gray-600 hover:text-blue-600">Home</a></li>
                    <li><a href="/resources/jobs/" class="text-gray-600 hover:text-blue-600">Job Market Insights</a></li>
                    <li><a href="/resources/career/" class="text-gray-600 hover:text-blue-600">Career Advice Hub</a></li>
                    <li><a href="/resources/extra-income/" class="text-gray-600 hover:text-blue-600">Extra Income Ideas</a></li>
                    <li><a href="/resources/jobs/remote-jobs-begginers.php#remote-work-story-form" class="text-gray-600 hover:text-blue-600">Share Your Remote Work Story</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Info</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="/organisations.php" class="text-gray-600 hover:text-blue-600">For Organisations</a></li>
                    <li><a href="/individual-users.php" class="text-gray-600 hover:text-blue-600">For Individuals</a></li>
                    <li><a href="/faq.php" class="text-gray-600 hover:text-blue-600">FAQ</a></li>
                    <li><a href="/browser-ai-check.php" class="text-gray-600 hover:text-blue-600">Browser AI Check</a></li>
                    <li><a href="/job-applications-features.php" class="text-green-600 hover:text-green-700 font-medium">Job Application Tracker</a></li>
                    <li><a href="/privacy.php" class="text-gray-600 hover:text-blue-600">Privacy Policy</a></li>
                    <li><a href="/terms.php" class="text-gray-600 hover:text-blue-600">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Ready to create section -->
        <div class="mb-10 rounded-2xl border-2 border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 px-6 py-6 shadow-sm">
            <div class="flex flex-col items-center text-center max-w-2xl mx-auto">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Ready to create a standout CV?</h3>
                <p class="text-sm text-gray-700 mb-4">
                    Use Simple CV Builder to showcase your skills, highlight your experience, and export print-ready PDFs. Paid plans unlock unlimited sections, premium templates, and QR-code enabled CVs.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="/?register=1" data-open-register class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition-colors">
                        Create Free Account
                    </a>
                    <a href="/#pricing" class="inline-flex items-center justify-center rounded-lg border-2 border-blue-600 px-5 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                        Compare Plans
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Bottom section with logo and copyright -->
        <div class="pt-8 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="flex items-center">
                <img src="/static/images/logo/black-logo-300.jpg" alt="Simple CV Builder" class="h-6 w-auto opacity-50" />
            </a>
            <p class="text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> Simple CV Builder. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<div
    data-cookie-banner
    class="fixed inset-x-4 bottom-4 z-50 hidden max-w-lg rounded-2xl border border-gray-200 bg-white p-5 shadow-lg sm:left-1/2 sm:right-auto sm:-translate-x-1/2">
    <div class="flex items-start gap-4">
        <div class="mt-1 hidden flex-shrink-0 rounded-full bg-blue-100 p-2 text-blue-600 sm:block">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1 text-sm text-gray-700">
            <p class="font-semibold text-gray-900">We use cookies</p>
            <p class="mt-1 leading-relaxed">
                We use cookies to keep you signed in and remember your preferences. By clicking “Accept”, you agree to our use of cookies as described in our
                <a href="/privacy.php" class="text-blue-600 underline">Privacy Policy</a>.
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <button
                type="button"
                data-cookie-decline
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:text-gray-800">
                Decline
            </button>
            <button
                type="button"
                data-cookie-accept
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Accept
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        const storageKey = 'cv_app_cookie_consent_v1';
        const banner = document.querySelector('[data-cookie-banner]');
        if (!banner) {
            return;
        }

        const accepted = localStorage.getItem(storageKey);
        if (!accepted) {
            banner.classList.remove('hidden');
        }

        const closeBanner = (value) => {
            localStorage.setItem(storageKey, value);
            banner.classList.add('hidden');
        };

        const acceptBtn = banner.querySelector('[data-cookie-accept]');
        const declineBtn = banner.querySelector('[data-cookie-decline]');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => closeBanner('accepted'));
        }
        if (declineBtn) {
            declineBtn.addEventListener('click', () => closeBanner('declined'));
        }
    })();
</script>

<script>
    // Handle registration link in footer - works on all pages
    (function() {
        document.querySelectorAll('footer [data-open-register]').forEach((link) => {
            link.addEventListener('click', function(e) {
                // Check if modal system exists on current page
                const registerModal = document.querySelector('[data-modal="register"]');
                
                if (registerModal) {
                    // Modal exists on this page, try to open it
                    e.preventDefault();
                    
                    // Try to find and call openModal function
                    const modalMap = new Map();
                    document.querySelectorAll('[data-modal]').forEach((modal) => {
                        modalMap.set(modal.getAttribute('data-modal'), modal);
                    });
                    
                    const modal = modalMap.get('register');
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('overflow-hidden');
                        
                        // Focus first input
                        const firstInput = modal.querySelector('input[type="email"], input[type="text"]');
                        if (firstInput) {
                            setTimeout(() => firstInput.focus(), 100);
                        }
                    }
                }
                // If modal doesn't exist, let the href="/?register=1" handle the redirect
            });
        });
    })();
</script>

<script>
// Enhance markdown rendering with marked.js on all pages
(function() {
    if (typeof marked !== 'undefined') {
        // Wait for DOM to be ready
        function enhanceMarkdown() {
            document.querySelectorAll('.markdown-content').forEach(function(el) {
                // Skip if already enhanced
                if (el.dataset.markdownEnhanced === 'true') {
                    return;
                }
                
                const originalHtml = el.innerHTML;
                try {
                    // Parse markdown and render
                    const rendered = marked.parse(originalHtml, { 
                        breaks: true, 
                        gfm: true,
                        headerIds: false,
                        mangle: false
                    });
                    el.innerHTML = rendered;
                    el.dataset.markdownEnhanced = 'true';
                } catch (e) {
                    // Fallback to original if parsing fails
                    console.warn('Markdown parsing failed:', e);
                }
            });
        }
        
        // Run on load and after a short delay for dynamically loaded content
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enhanceMarkdown);
        } else {
            enhanceMarkdown();
        }
        
        // Re-run after a delay for content loaded via AJAX
        setTimeout(enhanceMarkdown, 500);
    }
})();
</script>
<script src="/js/closing-date-reminders.js"></script>
