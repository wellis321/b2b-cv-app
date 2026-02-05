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
                    class="md:hidden p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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
                        <a href="/subscription.php" class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $isSubscription ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Plan</a>
                    <?php endif; ?>
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
                    <a href="/job-applications-features.php" class="px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap hidden lg:inline <?php echo $currentPage === 'job-applications-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Job Applications</a>
                    <a href="/job-applications-features.php" class="px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap lg:hidden <?php echo $currentPage === 'job-applications-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">Jobs</a>
                    <a href="/#pricing" class="px-2 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap text-gray-700 hover:bg-blue-50 hover:text-blue-700">Pricing</a>
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
                        <a href="/subscription.php" 
                           class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $isSubscription ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Subscription
                        </a>
                    <?php endif; ?>
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
                    <a href="/job-applications-features.php" 
                       class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'job-applications-features.php' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?> focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Job Applications
                    </a>
                    <a href="/#pricing" 
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
                    menuButton.setAttribute('aria-expanded', 'false');
                    mobileMenu.classList.add('hidden');
                    if (iconOpen && iconClose) {
                        iconOpen.classList.remove('hidden');
                        iconClose.classList.add('hidden');
                    }
                });
            });
        }
    });
</script>
