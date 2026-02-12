<!-- Skip to main content link (accessibility) -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Skip to main content</a>

<header role="banner" class="sticky top-0 z-40 bg-white/95 shadow backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-3 md:py-4 gap-1 md:gap-2 lg:gap-4">
            <div class="flex items-center flex-shrink-0 min-w-0">
                <a href="/" class="flex items-center space-x-1 md:space-x-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded">
                    <img src="/static/images/logo/black-logo-300.jpg" alt="Simple CV Builder" class="h-10 md:h-10 lg:h-12 w-auto flex-shrink-0" />
                    <span class="text-sm md:text-lg lg:text-xl xl:text-2xl font-bold text-blue-600 whitespace-nowrap hidden sm:inline">Simple CV Builder</span>
                </a>
            </div>
            
            <!-- Mobile menu button -->
            <button type="button" 
                    id="mobile-menu-button"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    aria-label="Toggle navigation menu"
                    class="md:hidden p-3 min-h-[44px] min-w-[44px] rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 touch-manipulation">
                <svg id="mobile-menu-icon-open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="mobile-menu-icon-close" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Desktop navigation -->
            <nav role="navigation" aria-label="Main navigation" class="hidden md:flex items-center space-x-0.5 md:space-x-1 lg:space-x-2 text-xs md:text-sm flex-shrink-0">
                <?php if (isLoggedIn()): ?>
                    <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    $isProfile = ($currentPage === 'profile.php');
                    $isDashboard = ($currentPage === 'dashboard.php');
                    $isPreview = ($currentPage === 'preview-cv.php');
                    $isSubscription = ($currentPage === 'subscription.php');
                    $user = getCurrentUser();
                    $isSuperAdmin = !empty($user['is_super_admin']);
                    $org = getUserOrganisation();
                    $isOrgMember = !empty($org);
                    ?>
                    <?php if ($isSuperAdmin): ?>
                        <a href="/admin/dashboard.php" class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?>">Admin</a>
                    <?php endif; ?>
                    <?php if ($isOrgMember): ?>
                        <?php
                        $isAgencyPage = strpos($_SERVER['REQUEST_URI'], '/agency/') !== false || ($currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/agency/') !== false);
                        $isCvPage = in_array($currentPage, ['dashboard.php', 'preview-cv.php', 'cv.php', 'cv-variants.php', 'cv-quality.php', 'content-editor.php']) || strpos($_SERVER['REQUEST_URI'], '/cv-variants/') !== false;
                        ?>
                        <?php if ($isAgencyPage): ?>
                            <!-- Switch to Personal CV -->
                            <a href="/dashboard.php" 
                               class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap text-gray-700 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               title="Switch to Personal CV">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="ml-1 hidden sm:inline">My CV</span>
                            </a>
                        <?php else: ?>
                            <!-- Switch to Agency -->
                            <a href="/agency/dashboard.php" 
                               class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap text-gray-700 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               title="Switch to Agency Dashboard">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="ml-1 hidden sm:inline">Agency</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!$isAgencyPage): ?>
                            <div class="relative group inline-flex items-stretch rounded-md">
                                <a href="/content-editor.php" class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-l-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-inset <?php echo $isCvPage ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">My CV</a>
                                <button type="button" aria-haspopup="true" aria-expanded="false" class="inline-flex items-center px-0.5 py-1.5 md:px-1 md:py-2 rounded-r-md border-l border-gray-200 text-xs md:text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $isCvPage ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                                    <svg class="h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                                    <a href="/cv.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'cv.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                        Online CV
                                    </a>
                                    <a href="/preview-cv.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'preview-cv.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                        PDF CV
                                    </a>
                                    <a href="/content-editor.php#jobs" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'content-editor.php' && strpos($_SERVER['REQUEST_URI'], '#jobs') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                        Jobs
                                    </a>
                                    <a href="/save-job-token.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'save-job-token.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                        Get save token
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="relative group inline-flex items-stretch rounded-md">
                            <a href="/profile.php" class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-l-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-inset <?php echo $isProfile ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Profile</a>
                            <button type="button" aria-haspopup="true" aria-expanded="false" class="inline-flex items-center px-0.5 py-1.5 md:px-1 md:py-2 rounded-r-md border-l border-gray-200 text-xs md:text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $isProfile || $currentPage === 'ai-settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                                <svg class="h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                                <a href="/ai-settings.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'ai-settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                    AI Settings
                                </a>
                            </div>
                        </div>
                        <?php if ($isAgencyPage): ?>
                            <div class="relative group">
                                <button type="button"
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo in_array($currentPage, ['candidates.php', 'team.php', 'settings.php']) ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                                    Admin
                                    <svg class="ml-0.5 md:ml-1 h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                                    <a href="/agency/candidates.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'candidates.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                        Candidates
                                    </a>
                                    <?php if (in_array($org['role'], ['owner', 'admin'])): ?>
                                        <a href="/agency/team.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'team.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            Team
                                        </a>
                                        <a href="/agency/settings.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            Settings
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php
                        $isCvPage = in_array($currentPage, ['dashboard.php', 'preview-cv.php', 'cv.php', 'cv-variants.php', 'cv-quality.php', 'content-editor.php']) || strpos($_SERVER['REQUEST_URI'], '/cv-variants/') !== false;
                        ?>
                        <div class="relative group inline-flex items-stretch rounded-md">
                            <a href="/content-editor.php" class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-l-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-inset <?php echo $isCvPage ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">My CV</a>
                            <button type="button" aria-haspopup="true" aria-expanded="false" class="inline-flex items-center px-0.5 py-1.5 md:px-1 md:py-2 rounded-r-md border-l border-gray-200 text-xs md:text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $isCvPage ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                                <svg class="h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                                <a href="/cv.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'cv.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                    Online CV
                                </a>
                                <a href="/preview-cv.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'preview-cv.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                    PDF CV
                                </a>
                                <a href="/content-editor.php#jobs" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'content-editor.php' && strpos($_SERVER['REQUEST_URI'], '#jobs') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                    Jobs
                                </a>
                                <a href="/save-job-token.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'save-job-token.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                    Get save token
                                </a>
                            </div>
                        </div>
                        <div class="relative group inline-flex items-stretch rounded-md">
                            <a href="/profile.php" class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-l-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-inset <?php echo $isProfile ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Profile</a>
                            <button type="button" aria-haspopup="true" aria-expanded="false" class="inline-flex items-center px-0.5 py-1.5 md:px-1 md:py-2 rounded-r-md border-l border-gray-200 text-xs md:text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $isProfile || $currentPage === 'ai-settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                                <svg class="h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                                <a href="/ai-settings.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'ai-settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                    AI Settings
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Subscription link - available to all logged-in users -->
                    <a href="/subscription.php" class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $isSubscription ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Plan</a>
                    <div class="relative group">
                        <button type="button"
                                aria-expanded="false"
                                aria-haspopup="true"
                                class="inline-flex items-center px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo strpos($currentPage, 'resources/') !== false || $currentPage === 'faq.php' || $currentPage === 'ai-cv-assessment.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                            Resources
                            <svg class="ml-0.5 md:ml-1 h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                            <a href="/resources/jobs/" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo strpos($currentPage, 'resources/jobs/') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                Job Resources
                            </a>
                            <a href="/resources/ai/setup-ollama.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo strpos($currentPage, 'setup-ollama.php') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                AI Setup Guide
                            </a>
                            <a href="/ai-settings.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'ai-settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                AI Settings
                            </a>
                            <a href="/ai-cv-assessment.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'ai-cv-assessment.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                AI CV Assessment
                            </a>
                            <a href="/faq.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'faq.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                FAQ
                            </a>
                        </div>
                    </div>
                    <a href="/logout.php" class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap text-gray-700 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Sign Out</a>
                <?php else: ?>
                    <a href="/organisations.php" class="px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'organisations.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Organisations</a>
                    <a href="/individual-users.php" class="px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'individual-users.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Individuals</a>
                    <div class="relative" id="features-menu-container">
                        <button type="button"
                                id="features-menu-button"
                                aria-expanded="false"
                                aria-haspopup="true"
                                class="inline-flex items-center px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo strpos($currentPage, 'job-applications') !== false || strpos($currentPage, 'save-job') !== false || strpos($currentPage, 'status-tracking') !== false || strpos($currentPage, 'follow-up') !== false || strpos($currentPage, 'interview-tracking') !== false || strpos($currentPage, 'application-notes') !== false || strpos($currentPage, 'search-filter') !== false || strpos($currentPage, 'track-all') !== false || strpos($currentPage, 'track-progress') !== false || strpos($currentPage, 'never-miss') !== false || strpos($currentPage, 'all-in-one') !== false || strpos($currentPage, 'free-with') !== false || strpos($currentPage, 'browser-ai') !== false || strpos($currentPage, 'keyword') !== false || strpos($currentPage, 'cv-variant') !== false || strpos($currentPage, 'cover-letters') !== false || strpos($currentPage, 'online-cv') !== false || strpos($currentPage, 'cv-templates') !== false || strpos($currentPage, 'tailor-cv') !== false || strpos($currentPage, 'qr-codes') !== false || strpos($currentPage, 'file-uploads') !== false || strpos($currentPage, 'smart-text') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                            <span class="hidden lg:inline">Features</span>
                            <span class="lg:hidden">Features</span>
                            <svg id="features-menu-icon" class="ml-1 h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <!-- Mega Menu -->
                        <div id="features-mega-menu" role="menu" class="absolute right-0 top-full hidden w-[42rem] rounded-lg border border-gray-200 bg-white shadow-xl z-50 mt-1">
                            <div class="p-4">
                                <!-- Tabs -->
                                <div class="flex space-x-1 border-b border-gray-200 mb-4" role="tablist">
                                    <button type="button" role="tab" aria-selected="true" data-tab="getting-started" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-blue-600 transition-colors tab-button active">
                                        Getting Started
                                    </button>
                                    <button type="button" role="tab" aria-selected="false" data-tab="core-features" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent transition-colors tab-button">
                                        Core Features
                                    </button>
                                    <button type="button" role="tab" aria-selected="false" data-tab="ai-features" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent transition-colors tab-button">
                                        AI Features
                                    </button>
                                    <button type="button" role="tab" aria-selected="false" data-tab="cv-integration" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent transition-colors tab-button">
                                        CV Integration
                                    </button>
                                    <button type="button" role="tab" aria-selected="false" data-tab="file-management" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent transition-colors tab-button">
                                        Files
                                    </button>
                                </div>
                                
                                <!-- Tab Panels -->
                                <div class="tab-panel active" id="getting-started" role="tabpanel">
                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="/all-features.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'all-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                            <span>All Features</span>
                                        </a>
                                        <a href="/job-applications-features.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'job-applications-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            <span>Overview</span>
                                        </a>
                                        <a href="/save-job-from-anywhere.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'save-job-from-anywhere.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>Save Jobs from Anywhere</span>
                                        </a>
                                        <a href="/browser-extension-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'browser-extension-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span>Browser Extension</span>
                                        </a>
                                        <a href="/all-in-one-place.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'all-in-one-place.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            <span>All in One Place</span>
                                        </a>
                                        <a href="/free-with-account.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'free-with-account.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Free with Account</span>
                                        </a>
                                        <a href="/feedback-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'feedback-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            <span>Feedback & Support</span>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="tab-panel hidden" id="core-features" role="tabpanel">
                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="/track-all-applications.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'track-all-applications.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            <span>Track All Applications</span>
                                        </a>
                                        <a href="/status-tracking.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'status-tracking.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Status Tracking</span>
                                        </a>
                                        <a href="/follow-up-dates.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'follow-up-dates.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Follow-Up Dates</span>
                                        </a>
                                        <a href="/never-miss-follow-up.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'never-miss-follow-up.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>Never Miss Follow-Up</span>
                                        </a>
                                        <a href="/interview-tracking.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'interview-tracking.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            <span>Interview Tracking</span>
                                        </a>
                                        <a href="/application-notes.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'application-notes.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Application Notes</span>
                                        </a>
                                        <a href="/search-filter.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'search-filter.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            <span>Search & Filter</span>
                                        </a>
                                        <a href="/track-progress.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'track-progress.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            <span>Track Progress</span>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="tab-panel hidden" id="ai-features" role="tabpanel">
                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="/browser-ai-free.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'browser-ai-free.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                            <span>Browser AI (Free)</span>
                                        </a>
                                        <a href="/keyword-extraction.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'keyword-extraction.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            <span>Keyword Extraction</span>
                                        </a>
                                        <a href="/keyword-ai-integration.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'keyword-ai-integration.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>AI Keyword Integration</span>
                                        </a>
                                        <a href="/cv-variant-linking.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'cv-variant-linking.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            <span>CV Variant Linking</span>
                                        </a>
                                        <a href="/cover-letters-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'cover-letters-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Cover Letters</span>
                                        </a>
                                        <a href="/application-questions-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'application-questions-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Application Questions</span>
                                        </a>
                                        <a href="/ai-cv-generation-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'ai-cv-generation-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>AI CV Generation</span>
                                        </a>
                                        <a href="/cv-quality-assessment-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'cv-quality-assessment-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>CV Quality Assessment</span>
                                        </a>
                                        <a href="/tailor-cv-content.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'tailor-cv-content.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Tailor CV Content</span>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="tab-panel hidden" id="cv-integration" role="tabpanel">
                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="/cv-building-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'cv-building-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>CV Building</span>
                                        </a>
                                        <a href="/online-cv-username.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'online-cv-username.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            <span>Online CV</span>
                                        </a>
                                        <a href="/cv-templates-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'cv-templates-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                            <span>CV Templates</span>
                                        </a>
                                        <a href="/template-customisation-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'template-customisation-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Template Customisation</span>
                                        </a>
                                        <a href="/cv-variants-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'cv-variants-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"/></svg>
                                            <span>CV Variants</span>
                                        </a>
                                        <a href="/pdf-export-feature.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'pdf-export-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <span>PDF Export</span>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="tab-panel hidden" id="file-management" role="tabpanel">
                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="/file-uploads-ai.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'file-uploads-ai.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <span>File Uploads</span>
                                        </a>
                                        <a href="/smart-text-extraction.php" role="menuitem" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $currentPage === 'smart-text-extraction.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Smart Text Extraction</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="/index.php#pricing" class="px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap text-gray-700 hover:bg-blue-50 hover:text-blue-700">Pricing</a>
                    <div class="relative group">
                        <button type="button"
                                aria-expanded="false"
                                aria-haspopup="true"
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo strpos($currentPage, 'resources/') !== false || $currentPage === 'faq.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                            Resources
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div role="menu" class="absolute right-0 top-full hidden min-w-[10rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block z-50">
                            <a href="/resources/jobs/" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo strpos($currentPage, 'resources/jobs/') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                Job Resources
                            </a>
                            <a href="/resources/ai/setup-ollama.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo strpos($currentPage, 'setup-ollama.php') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                AI Setup Guide
                            </a>
                            <a href="/ai-cv-assessment.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'ai-cv-assessment.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                AI CV Assessment
                            </a>
                            <a href="/faq.php" role="menuitem" class="block px-4 py-2 text-sm font-medium transition-colors <?php echo $currentPage === 'faq.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:bg-blue-50">
                                FAQ
                            </a>
                        </div>
                    </div>
                    <button type="button"
                            data-open-login
                            aria-label="Log in"
                            class="rounded-md border border-transparent bg-blue-50 px-2 py-2 text-xs md:text-sm font-medium text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap transition-colors">
                        Log in
                    </button>
                    <button type="button"
                            data-open-register
                            aria-label="Register"
                            class="rounded-md bg-blue-600 px-2 py-2 text-xs md:text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap transition-colors">
                        Register
                    </button>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Mobile menu -->
        <nav id="mobile-menu" role="navigation" aria-label="Mobile navigation" class="hidden md:hidden pb-4 border-t border-gray-200 mt-4">
            <div class="flex flex-col space-y-1 pt-4">
                <?php if (isLoggedIn()): ?>
                    <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    $isProfile = ($currentPage === 'profile.php');
                    $isDashboard = ($currentPage === 'dashboard.php');
                    $isPreview = ($currentPage === 'preview-cv.php');
                    $isSubscription = ($currentPage === 'subscription.php');
                    $user = getCurrentUser();
                    $isSuperAdmin = !empty($user['is_super_admin']);
                    $org = getUserOrganisation();
                    $isOrgMember = !empty($org);
                    ?>
                    <?php if ($isSuperAdmin): ?>
                        <a href="/admin/dashboard.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                            Admin
                        </a>
                    <?php endif; ?>
                    <?php if ($isOrgMember): ?>
                        <?php
                        $isAgencyPage = strpos($_SERVER['REQUEST_URI'], '/agency/') !== false || ($currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/agency/') !== false);
                        ?>
                        <?php if ($isAgencyPage): ?>
                            <!-- Switch to Personal CV -->
                            <a href="/dashboard.php" 
                               class="block px-4 py-2 rounded-md text-base font-medium transition-colors text-gray-700 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <span>Switch to My CV</span>
                                </div>
                            </a>
                        <?php else: ?>
                            <!-- Switch to Agency -->
                            <a href="/agency/dashboard.php" 
                               class="block px-4 py-2 rounded-md text-base font-medium transition-colors text-gray-700 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <span>Switch to Agency</span>
                                </div>
                            </a>
                        <?php endif; ?>
                        <?php
                        $isCvPage = in_array($currentPage, ['dashboard.php', 'preview-cv.php', 'cv.php', 'cv-variants.php', 'cv-quality.php', 'content-editor.php']) || strpos($_SERVER['REQUEST_URI'], '/cv-variants/') !== false;
                        ?>
                        <div class="px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4">
                            My CV
                        </div>
                        <a href="/content-editor.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'content-editor.php' && !$isAgencyPage ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            My CV
                        </a>
                        <a href="/cv.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'cv.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Online CV
                        </a>
                        <a href="/preview-cv.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'preview-cv.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            PDF CV
                        </a>
                        <a href="/content-editor.php#jobs" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'content-editor.php' && strpos($_SERVER['REQUEST_URI'], '#jobs') !== false ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Jobs
                        </a>
                        <a href="/save-job-token.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'save-job-token.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Get save token
                        </a>
                        <?php if ($isAgencyPage): ?>
                            <div class="px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4">
                                Admin
                            </div>
                            <a href="/agency/candidates.php" 
                               class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'candidates.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Candidates
                            </a>
                            <?php if (in_array($org['role'], ['owner', 'admin'])): ?>
                                <a href="/agency/team.php" 
                                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'team.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Team
                                </a>
                                <a href="/agency/settings.php" 
                                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'settings.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Settings
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php
                        $isCvPage = in_array($currentPage, ['dashboard.php', 'preview-cv.php', 'cv.php', 'cv-variants.php', 'cv-quality.php', 'content-editor.php']) || strpos($_SERVER['REQUEST_URI'], '/cv-variants/') !== false;
                        ?>
                        <div class="px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4">
                            My CV
                        </div>
                        <a href="/content-editor.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'content-editor.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            My CV
                        </a>
                        <a href="/cv.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'cv.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Online CV
                        </a>
                        <a href="/preview-cv.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'preview-cv.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            PDF CV
                        </a>
                        <a href="/content-editor.php#jobs" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'content-editor.php' && strpos($_SERVER['REQUEST_URI'], '#jobs') !== false ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Jobs
                        </a>
                        <a href="/save-job-token.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'save-job-token.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Get save token
                        </a>
                    <?php endif; ?>
                    <!-- Subscription link - available to all logged-in users -->
                    <a href="/subscription.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $isSubscription ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Subscription
                    </a>
                    <div class="px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4">
                        Profile
                    </div>
                    <a href="/profile.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'profile.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Profile
                    </a>
                    <a href="/ai-settings.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'ai-settings.php' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        AI Settings
                    </a>
                    <div class="px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4">
                        Resources
                    </div>
                    <a href="/resources/jobs/" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo strpos($currentPage, 'resources/jobs/') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Job Resources
                    </a>
                    <a href="/resources/ai/setup-ollama.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo strpos($currentPage, 'setup-ollama.php') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        AI Setup Guide
                    </a>
                    <a href="/ai-cv-assessment.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'ai-cv-assessment.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        AI CV Assessment
                    </a>
                    <a href="/faq.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'faq.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        FAQ
                    </a>
                    <a href="/logout.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors text-gray-700 hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 border-t border-gray-200 mt-2 pt-4">
                        Sign Out
                    </a>
                <?php else: ?>
                    <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    ?>
                    <a href="/organisations.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'organisations.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Organisations
                    </a>
                    <a href="/individual-users.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'individual-users.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Individuals
                    </a>
                    <?php
                    // Check if current page is a feature page to expand by default
                    $isFeaturePage = in_array($currentPage, [
                        'all-features.php', 'job-applications-features.php', 'save-job-from-anywhere.php',
                        'browser-extension-feature.php', 'cv-building-feature.php', 'cv-templates-feature.php',
                        'ai-cv-generation-feature.php', 'cover-letters-feature.php'
                    ]);
                    ?>
                    <button type="button" 
                            onclick="toggleMobileFeatures()"
                            aria-expanded="<?php echo $isFeaturePage ? 'true' : 'false'; ?>"
                            aria-controls="mobile-features-menu"
                            class="w-full flex items-center justify-between px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md">
                        <span>Features</span>
                        <svg id="mobile-features-icon" class="h-5 w-5 text-gray-500 transition-transform duration-200 <?php echo $isFeaturePage ? 'rotate-180' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="mobile-features-menu" class="<?php echo $isFeaturePage ? '' : 'hidden'; ?>">
                        <a href="/all-features.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'all-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            All Features
                        </a>
                        <a href="/job-applications-features.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'job-applications-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Job Applications Overview
                        </a>
                        <a href="/save-job-from-anywhere.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'save-job-from-anywhere.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Save Jobs from Anywhere
                        </a>
                        <a href="/browser-extension-feature.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'browser-extension-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Browser Extension
                        </a>
                        <a href="/cv-building-feature.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'cv-building-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            CV Building
                        </a>
                        <a href="/cv-templates-feature.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'cv-templates-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            CV Templates
                        </a>
                        <a href="/ai-cv-generation-feature.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'ai-cv-generation-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            AI CV Generation
                        </a>
                        <a href="/cover-letters-feature.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors pl-6 text-sm <?php echo $currentPage === 'cover-letters-feature.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cover Letters
                        </a>
                    </div>
                    <a href="/index.php#pricing" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Pricing
                    </a>
                    <div class="px-4 py-2 text-base font-semibold text-gray-900 border-t border-gray-200 mt-2 pt-4">
                        Resources
                    </div>
                    <a href="/resources/jobs/" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo strpos($currentPage, 'resources/jobs/') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Job Resources
                    </a>
                    <a href="/resources/ai/setup-ollama.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo strpos($currentPage, 'setup-ollama.php') !== false ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        AI Setup Guide
                    </a>
                    <a href="/ai-settings.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'ai-settings.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        AI Settings
                    </a>
                    <a href="/ai-cv-assessment.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'ai-cv-assessment.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        AI CV Assessment
                    </a>
                    <a href="/faq.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'faq.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        FAQ
                    </a>
                    <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                        <button type="button"
                                data-open-login
                                aria-label="Log in"
                                class="w-full rounded-md border border-transparent bg-blue-50 px-4 py-2 text-base font-medium text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Log in
                        </button>
                        <button type="button"
                                data-open-register
                                aria-label="Register"
                                class="w-full rounded-md bg-blue-600 px-4 py-2 text-base font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Register
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('mobile-menu-icon-open');
        const iconClose = document.getElementById('mobile-menu-icon-close');
        
        if (menuButton && mobileMenu) {
            menuButton.addEventListener('click', function() {
                const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
                const newState = !isExpanded;
                
                menuButton.setAttribute('aria-expanded', newState);
                mobileMenu.classList.toggle('hidden');
                
                // Toggle icons
                if (iconOpen && iconClose) {
                    iconOpen.classList.toggle('hidden');
                    iconClose.classList.toggle('hidden');
                }
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!menuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    if (!mobileMenu.classList.contains('hidden')) {
                        menuButton.setAttribute('aria-expanded', 'false');
                        mobileMenu.classList.add('hidden');
                        if (iconOpen && iconClose) {
                            iconOpen.classList.remove('hidden');
                            iconClose.classList.add('hidden');
                        }
                    }
                }
            });
            
            // Close menu when clicking a link
            const mobileLinks = mobileMenu.querySelectorAll('a, button');
            mobileLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    // Don't close menu if clicking Features toggle button
                    if (this.getAttribute('aria-controls') === 'mobile-features-menu') {
                        return;
                    }
                    menuButton.setAttribute('aria-expanded', 'false');
                    mobileMenu.classList.add('hidden');
                    if (iconOpen && iconClose) {
                        iconOpen.classList.remove('hidden');
                        iconClose.classList.add('hidden');
                    }
                });
            });
        }
        
        // Features mega menu toggle
        const featuresMenuButton = document.getElementById('features-menu-button');
        const featuresMegaMenu = document.getElementById('features-mega-menu');
        const featuresMenuIcon = document.getElementById('features-menu-icon');
        const featuresMenuContainer = document.getElementById('features-menu-container');
        
        if (featuresMenuButton && featuresMegaMenu) {
            featuresMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                const isExpanded = featuresMenuButton.getAttribute('aria-expanded') === 'true';
                const newState = !isExpanded;
                
                featuresMenuButton.setAttribute('aria-expanded', newState);
                featuresMegaMenu.classList.toggle('hidden');
                
                // Rotate icon
                if (featuresMenuIcon) {
                    if (newState) {
                        featuresMenuIcon.classList.add('rotate-180');
                    } else {
                        featuresMenuIcon.classList.remove('rotate-180');
                    }
                }
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (featuresMenuContainer && !featuresMenuContainer.contains(event.target)) {
                    if (!featuresMegaMenu.classList.contains('hidden')) {
                        featuresMenuButton.setAttribute('aria-expanded', 'false');
                        featuresMegaMenu.classList.add('hidden');
                        if (featuresMenuIcon) {
                            featuresMenuIcon.classList.remove('rotate-180');
                        }
                    }
                }
            });
            
            // Prevent menu from closing when clicking inside it
            featuresMegaMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Mega menu tab switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanels = document.querySelectorAll('.tab-panel');
        
        tabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const targetTab = button.getAttribute('data-tab');
                
                // Update button states
                tabButtons.forEach(function(btn) {
                    btn.setAttribute('aria-selected', 'false');
                    btn.classList.remove('border-blue-600', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-700');
                });
                button.setAttribute('aria-selected', 'true');
                button.classList.remove('border-transparent', 'text-gray-700');
                button.classList.add('border-blue-600', 'text-blue-600');
                
                // Update panel visibility
                tabPanels.forEach(function(panel) {
                    panel.classList.add('hidden');
                    panel.classList.remove('active');
                });
                const targetPanel = document.getElementById(targetTab);
                if (targetPanel) {
                    targetPanel.classList.remove('hidden');
                    targetPanel.classList.add('active');
                }
            });
            
            // Switch tabs on hover
            button.addEventListener('mouseenter', function() {
                const targetTab = button.getAttribute('data-tab');
                
                // Update button states
                tabButtons.forEach(function(btn) {
                    btn.setAttribute('aria-selected', 'false');
                    btn.classList.remove('border-blue-600', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-700');
                });
                button.setAttribute('aria-selected', 'true');
                button.classList.remove('border-transparent', 'text-gray-700');
                button.classList.add('border-blue-600', 'text-blue-600');
                
                // Update panel visibility
                tabPanels.forEach(function(panel) {
                    panel.classList.add('hidden');
                    panel.classList.remove('active');
                });
                const targetPanel = document.getElementById(targetTab);
                if (targetPanel) {
                    targetPanel.classList.remove('hidden');
                    targetPanel.classList.add('active');
                }
            });
        });
    });
    
    // Mobile Features menu toggle
    function toggleMobileFeatures() {
        const featuresButton = document.querySelector('[aria-controls="mobile-features-menu"]');
        const featuresMenu = document.getElementById('mobile-features-menu');
        const featuresIcon = document.getElementById('mobile-features-icon');
        
        if (featuresButton && featuresMenu && featuresIcon) {
            const isExpanded = featuresButton.getAttribute('aria-expanded') === 'true';
            const newState = !isExpanded;
            
            featuresButton.setAttribute('aria-expanded', newState);
            featuresMenu.classList.toggle('hidden');
            
            // Rotate icon using CSS class
            if (newState) {
                featuresIcon.classList.add('rotate-180');
            } else {
                featuresIcon.classList.remove('rotate-180');
            }
        }
    }
</script>
