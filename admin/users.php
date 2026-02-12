<?php
/**
 * Super Admin - Users Management
 * List, search, and manage all users across organisations
 */

require_once __DIR__ . '/../php/helpers.php';

// Require super admin access
requireSuperAdmin();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get filter parameters
$search = sanitizeInput(get('search') ?? '');
$accountTypeFilter = sanitizeInput(get('account_type') ?? '');
$organisationFilter = sanitizeInput(get('organisation_id') ?? '');
$page = max(1, (int)(get('page') ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build filters array
$filters = [
    'limit' => $perPage,
    'offset' => $offset
];

if ($search) {
    $filters['search'] = $search;
}

if ($accountTypeFilter) {
    $filters['account_type'] = $accountTypeFilter;
}

if ($organisationFilter) {
    $filters['organisation_id'] = $organisationFilter;
}

// Get users
$users = getAllUsers($filters);

// Get total count for pagination
$totalFilters = $filters;
unset($totalFilters['limit'], $totalFilters['offset']);
$allUsers = getAllUsers($totalFilters);
$totalUsers = count($allUsers);
$totalPages = ceil($totalUsers / $perPage);

// Get all organisations for filter
$allOrganisations = getAllOrganisations();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Manage Users | Super Admin',
        'metaDescription' => 'Manage all users',
        'canonicalUrl' => APP_URL . '/admin/users.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('admin/header'); ?>

    <main id="main-content" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Users</h1>
                <p class="mt-1 text-sm text-gray-500">Manage all users across all organisations</p>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg p-4 mb-6">
                <form method="GET" action="" id="filter-form" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" id="search" name="search" value="<?php echo e($search); ?>" 
                               placeholder="Search users..." 
                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                    </div>
                    <div>
                        <label for="account_type" class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                        <select name="account_type" id="account_type" 
                                class="block rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                onchange="document.getElementById('filter-form').submit();">
                            <option value="">All Types</option>
                            <option value="individual" <?php echo $accountTypeFilter === 'individual' ? 'selected' : ''; ?>>Individual</option>
                            <option value="candidate" <?php echo $accountTypeFilter === 'candidate' ? 'selected' : ''; ?>>Candidate</option>
                        </select>
                    </div>
                    <div>
                        <label for="organisation_id" class="block text-sm font-medium text-gray-700 mb-2">Organisation</label>
                        <select name="organisation_id" id="organisation_id" 
                                class="block rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                onchange="document.getElementById('filter-form').submit();">
                            <option value="">All Organisations</option>
                            <?php foreach ($allOrganisations as $org): ?>
                                <option value="<?php echo e($org['id']); ?>" <?php echo $organisationFilter === $org['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($org['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg text-base font-bold shadow-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Filter
                        </button>
                    </div>
                    <?php if ($search || $accountTypeFilter || $organisationFilter): ?>
                        <div class="flex items-end">
                            <a href="/admin/users.php" class="px-6 py-3 border-2 border-gray-400 rounded-lg text-base font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-gray-200">
                                Clear
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Super Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <?php
                                // Get subscription info for this user
                                $userSubContext = getUserSubscriptionContext($u['id']);
                                $userExpiryInfo = formatSubscriptionExpiry($userSubContext);
                                $planLabel = subscriptionPlanLabel($userSubContext);
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($u['full_name'] ?? 'N/A'); ?></div>
                                        <div class="text-sm text-gray-500">@<?php echo e($u['username']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo e($u['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $u['account_type'] === 'candidate' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo ucfirst($u['account_type'] ?? 'individual'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $userSubContext['plan'] === 'lifetime' ? 'bg-purple-100 text-purple-800' : ($userSubContext['plan'] === 'free' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo e($planLabel); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($userExpiryInfo['days_remaining'] !== null): ?>
                                            <div class="text-sm <?php echo $userExpiryInfo['status_color'] === 'red' ? 'text-red-600 font-semibold' : ($userExpiryInfo['status_color'] === 'yellow' ? 'text-yellow-600 font-medium' : 'text-gray-900'); ?>">
                                                <?php echo e($userExpiryInfo['formatted_date']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo e($userExpiryInfo['status_text']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400"><?php echo e($userExpiryInfo['formatted_date']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?php echo e($u['organisation_name'] ?? '—'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($u['organisation_role']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <?php echo ucfirst($u['organisation_role']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($u['is_super_admin'])): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Yes
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('j M Y', strtotime($u['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="/admin/users/edit.php?id=<?php echo e($u['id']); ?>" 
                                           class="text-blue-600 hover:text-blue-900 font-medium">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalUsers); ?> of <?php echo $totalUsers; ?> users
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $accountTypeFilter ? '&account_type=' . urlencode($accountTypeFilter) : ''; ?><?php echo $organisationFilter ? '&organisation_id=' . urlencode($organisationFilter) : ''; ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $accountTypeFilter ? '&account_type=' . urlencode($accountTypeFilter) : ''; ?><?php echo $organisationFilter ? '&organisation_id=' . urlencode($organisationFilter) : ''; ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php partial('footer'); ?>
</body>
</html>

