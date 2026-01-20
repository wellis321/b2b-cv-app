<?php
/**
 * Super Admin Dashboard
 * System-wide overview for super administrators
 */

require_once __DIR__ . '/../php/helpers.php';

// Require super admin access
requireSuperAdmin();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get system-wide statistics
$stats = getSystemStatistics();

// Get recent organisations (last 5)
$recentOrganisations = getAllOrganisations(['limit' => 5]);

// Get recent activity (last 10)
$recentActivity = getSystemActivityLog(10);

// Get pending limit increase requests count
$pendingLimitRequests = getAllPendingLimitRequests();
$pendingRequestsCount = count($pendingLimitRequests);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Super Admin Dashboard',
        'metaDescription' => 'System administration dashboard',
        'canonicalUrl' => APP_URL . '/admin/dashboard.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('admin/header'); ?>

    <main id="main-content" class="py-6">
        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dashboard Header -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                        Super Admin Dashboard
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        System-wide overview and management
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Organisations -->
                <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                    <dt>
                        <div class="absolute rounded-md bg-blue-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v3M6.75 21h13.5M18 15v-3a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75v3m4.5 0h.75"/>
                            </svg>
                        </div>
                        <p class="ml-16 truncate text-sm font-medium text-gray-500">Total Organisations</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_organisations']; ?></p>
                        <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                            <div class="text-sm">
                                <a href="/admin/organisations.php" class="font-medium text-blue-600 hover:text-blue-500">View all</a>
                            </div>
                        </div>
                    </dd>
                </div>

                <!-- Active Organisations -->
                <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                    <dt>
                        <div class="absolute rounded-md bg-green-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="ml-16 truncate text-sm font-medium text-gray-500">Active Subscriptions</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['active_subscriptions']; ?></p>
                        <p class="ml-2 flex items-baseline text-sm font-semibold text-gray-500">
                            of <?php echo $stats['total_organisations']; ?>
                        </p>
                    </dd>
                </div>

                <!-- Total Users -->
                <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                    <dt>
                        <div class="absolute rounded-md bg-purple-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <p class="ml-16 truncate text-sm font-medium text-gray-500">Total Users</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_users']; ?></p>
                        <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                            <div class="text-sm">
                                <a href="/admin/users.php" class="font-medium text-blue-600 hover:text-blue-500">View all</a>
                            </div>
                        </div>
                    </dd>
                </div>

                <!-- Candidates -->
                <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                    <dt>
                        <div class="absolute rounded-md bg-yellow-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <p class="ml-16 truncate text-sm font-medium text-gray-500">Candidates</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['candidates']; ?></p>
                        <p class="ml-2 flex items-baseline text-sm font-semibold text-gray-500">
                            <?php echo $stats['individual_users']; ?> individual
                        </p>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Main Content Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-gray-100 rounded-xl p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Recent Organisations -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-lg border border-gray-200">
                        <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Recent Organisations</h2>
                            <a href="/admin/organisations.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all</a>
                        </div>
                        <?php if (empty($recentOrganisations)): ?>
                            <div class="mt-6 text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No organisations yet</h3>
                            </div>
                        <?php else: ?>
                            <ul class="mt-6 divide-y divide-gray-200">
                                <?php foreach ($recentOrganisations as $org): ?>
                                    <li class="py-4">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-medium text-gray-900">
                                                    <?php echo e($org['name']); ?>
                                                </p>
                                                <p class="truncate text-sm text-gray-500">
                                                    <?php echo e($org['slug']); ?>
                                                </p>
                                            </div>
                                            <div class="ml-4 flex items-center space-x-2">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    <?php echo $org['subscription_status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                    <?php echo ucfirst($org['subscription_status'] ?? 'inactive'); ?>
                                                </span>
                                                <a href="/admin/organisations.php?id=<?php echo e($org['id']); ?>"
                                                   class="text-blue-600 hover:text-blue-500">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-lg border border-gray-200">
                        <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Recent Activity</h2>
                            <a href="/admin/activity.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all</a>
                        </div>
                        <?php if (empty($recentActivity)): ?>
                            <div class="mt-6 text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No activity yet</h3>
                            </div>
                        <?php else: ?>
                            <div class="mt-6 flow-root">
                                <ul class="-mb-8">
                                    <?php foreach ($recentActivity as $index => $activity): ?>
                                        <li>
                                            <div class="relative pb-8">
                                                <?php if ($index < count($recentActivity) - 1): ?>
                                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <?php endif; ?>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                <span class="font-medium text-gray-900"><?php echo e($activity['actor_name'] ?? 'System'); ?></span>
                                                                <?php echo e(str_replace('.', ' ', $activity['action'])); ?>
                                                                <?php if ($activity['target_name']): ?>
                                                                    <span class="font-medium text-gray-900"><?php echo e($activity['target_name']); ?></span>
                                                                <?php endif; ?>
                                                                <?php if ($activity['organisation_name']): ?>
                                                                    <span class="text-gray-400">in <?php echo e($activity['organisation_name']); ?></span>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                            <time datetime="<?php echo $activity['created_at']; ?>">
                                                                <?php
                                                                $activityTime = strtotime($activity['created_at']);
                                                                $diff = time() - $activityTime;
                                                                if ($diff < 60) {
                                                                    echo 'Just now';
                                                                } elseif ($diff < 3600) {
                                                                    echo floor($diff / 60) . 'm ago';
                                                                } elseif ($diff < 86400) {
                                                                    echo floor($diff / 3600) . 'h ago';
                                                                } else {
                                                                    echo date('j M', $activityTime);
                                                                }
                                                                ?>
                                                            </time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <a href="/admin/organisations.php"
                   class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-gray-900">Manage Organisations</p>
                        <p class="truncate text-sm text-gray-500">View and edit all</p>
                    </div>
                </a>

                <a href="/admin/users.php"
                   class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-gray-900">Manage Users</p>
                        <p class="truncate text-sm text-gray-500">View all users</p>
                    </div>
                </a>

                <a href="/admin/activity.php"
                   class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-gray-900">Activity Log</p>
                        <p class="truncate text-sm text-gray-500">System-wide activity</p>
                    </div>
                </a>

                <a href="/admin/security-logs.php"
                   class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-gray-900">Security Logs</p>
                        <p class="truncate text-sm text-gray-500">Security events</p>
                    </div>
                </a>

                <a href="/admin/limit-requests.php"
                   class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <div class="flex-shrink-0 relative">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <?php if ($pendingRequestsCount > 0): ?>
                            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                                <?php echo $pendingRequestsCount; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-gray-900">Limit Requests</p>
                        <p class="truncate text-sm text-gray-500">
                            <?php echo $pendingRequestsCount > 0 ? $pendingRequestsCount . ' pending' : 'Review requests'; ?>
                        </p>
                    </div>
                </a>

                <a href="/admin/settings.php"
                   class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-gray-900">System Settings</p>
                        <p class="truncate text-sm text-gray-500">Configuration</p>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>
</body>
</html>

