<?php
/**
 * Admin Header Partial
 * Navigation for super admin users
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();
?>
<!-- Skip to main content link (accessibility) -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Skip to main content</a>

<header role="banner" class="sticky top-0 z-40 bg-white/95 shadow backdrop-blur border-b-2 border-green-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-3 md:py-4 gap-1 md:gap-2 lg:gap-4">
            <div class="flex items-center flex-shrink-0 min-w-0">
                <a href="/admin/dashboard.php" class="flex items-center space-x-1 md:space-x-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 rounded">
                    <img src="/static/images/logo/black-logo-300.jpg" alt="Simple CV Builder" class="h-10 md:h-10 lg:h-12 w-auto flex-shrink-0" />
                    <span class="text-sm md:text-lg lg:text-xl xl:text-2xl font-bold text-blue-600 whitespace-nowrap hidden sm:inline">Simple CV Builder</span>
                </a>
            </div>

            <!-- Mobile menu button -->
            <button type="button" 
                    id="mobile-menu-button"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    class="md:hidden p-2 rounded-md text-gray-700 hover:text-green-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Desktop navigation -->
            <nav role="navigation" aria-label="Main navigation" class="hidden md:flex items-center space-x-0.5 md:space-x-1 lg:space-x-2 text-xs md:text-sm flex-shrink-0">
                <a href="/admin/dashboard.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'dashboard.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Dashboard
                </a>

                <a href="/admin/organisations.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'organisations.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Organisations
                </a>

                <a href="/admin/users.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'users.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Users
                </a>

                <a href="/admin/activity.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'activity.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Activity
                </a>

                <a href="/admin/security-logs.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'security-logs.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Security
                </a>

                <a href="/admin/limit-requests.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'limit-requests.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Limits
                </a>
                <a href="/admin/remote-work-stories.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'remote-work-stories.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Stories
                </a>
                <a href="/admin/settings.php"
                   class="px-1.5 py-1.5 md:px-2 md:py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap <?php echo $currentPage === 'settings.php' ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Settings
                </a>

                <div class="relative group flex-shrink-0">
                    <button type="button"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Account menu"
                            class="flex items-center gap-0.5 md:gap-1 text-gray-700 hover:text-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 rounded p-1 transition-colors">
                        <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <svg class="h-3 w-3 md:h-4 md:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div role="menu" class="absolute right-0 top-full hidden min-w-[12rem] rounded-lg border border-gray-200 bg-white py-2 shadow-lg group-hover:block">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($user['full_name'] ?? 'Super Admin'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($user['email'] ?? ''); ?></p>
                        </div>
                        <?php if (isOrganisationMember()): ?>
                            <a href="/agency/dashboard.php" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:bg-gray-50">
                                Agency Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="/profile.php" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:bg-gray-50">
                            My CV
                        </a>
                        <a href="/logout.php" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:bg-gray-50">
                            Sign Out
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Mobile menu -->
        <nav id="mobile-menu" role="navigation" aria-label="Mobile navigation" class="hidden md:hidden pb-4 border-t border-gray-200 mt-4">
            <div class="flex flex-col space-y-2 pt-4">
                <a href="/admin/dashboard.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'dashboard.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Dashboard
                </a>

                <a href="/admin/organisations.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'organisations.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Organisations
                </a>

                <a href="/admin/users.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'users.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Users
                </a>

                <a href="/admin/activity.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'activity.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Activity
                </a>

                <a href="/admin/security-logs.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'security-logs.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Security
                </a>

                <a href="/admin/limit-requests.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'limit-requests.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Limits
                </a>

                <a href="/admin/remote-work-stories.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'remote-work-stories.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Stories
                </a>

                <a href="/admin/settings.php"
                   class="block px-4 py-2 rounded-md text-base font-medium transition-colors <?php echo $currentPage === 'settings.php' ? 'text-green-700 bg-green-100' : 'text-gray-700 hover:bg-green-100 hover:text-green-700'; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                    Settings
                </a>

                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="px-4 py-2">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($user['full_name'] ?? 'Super Admin'); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($user['email'] ?? ''); ?></p>
                    </div>
                    <?php if (isOrganisationMember()): ?>
                        <a href="/agency/dashboard.php" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-100 hover:text-blue-700 rounded-md transition-colors">
                            Agency Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="/profile.php" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-100 hover:text-blue-700 rounded-md transition-colors">
                        My CV
                    </a>
                    <a href="/logout.php" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-red-100 hover:text-red-700 rounded-md transition-colors">
                        Sign Out
                    </a>
                </div>
            </div>
        </nav>
    </div>
</header>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (menuButton && mobileMenu) {
            menuButton.addEventListener('click', function() {
                const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
                menuButton.setAttribute('aria-expanded', !isExpanded);
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>
