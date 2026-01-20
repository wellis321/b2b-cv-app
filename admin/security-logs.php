<?php
/**
 * Super Admin - Security Logs
 * View security events including prompt injection attempts
 */

require_once __DIR__ . '/../php/helpers.php';

// Require super admin access
requireSuperAdmin();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get filter parameters
$userFilter = sanitizeInput(get('user_id') ?? '');
$eventTypeFilter = sanitizeInput(get('event_type') ?? '');
$dateFrom = sanitizeInput(get('date_from') ?? '');
$dateTo = sanitizeInput(get('date_to') ?? '');
$page = max(1, (int)(get('page') ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Check if security_logs table exists
$tableExists = db()->fetchOne("SHOW TABLES LIKE 'security_logs'");

if (!$tableExists) {
    $error = 'Security logs table does not exist. Please run the migration: database/20250131_add_security_logs.sql';
    $securityLogs = [];
    $totalLogs = 0;
    $totalPages = 0;
} else {
    // Build query with filters
    $whereConditions = [];
    $params = [];
    
    if ($userFilter) {
        $whereConditions[] = "sl.user_id = ?";
        $params[] = $userFilter;
    }
    
    if ($eventTypeFilter) {
        $whereConditions[] = "sl.event_type = ?";
        $params[] = $eventTypeFilter;
    }
    
    if ($dateFrom) {
        $whereConditions[] = "DATE(sl.created_at) >= ?";
        $params[] = $dateFrom;
    }
    
    if ($dateTo) {
        $whereConditions[] = "DATE(sl.created_at) <= ?";
        $params[] = $dateTo;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM security_logs sl $whereClause";
    $totalLogs = db()->fetchOne($countQuery, $params)['total'] ?? 0;
    $totalPages = ceil($totalLogs / $perPage);
    
    // Get security logs with user information
    $query = "SELECT sl.*, p.email, p.full_name, p.username 
              FROM security_logs sl 
              LEFT JOIN profiles p ON sl.user_id = p.id 
              $whereClause 
              ORDER BY sl.created_at DESC 
              LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $securityLogs = db()->fetchAll($query, $params);
}

// Get all unique event types for filter
$eventTypes = [];
if ($tableExists) {
    $eventTypeRows = db()->fetchAll("SELECT DISTINCT event_type FROM security_logs ORDER BY event_type ASC");
    foreach ($eventTypeRows as $row) {
        $eventTypes[] = $row['event_type'];
    }
}

// Get statistics
$stats = [
    'total_events' => 0,
    'blocked_attempts' => 0,
    'warnings' => 0,
    'today_events' => 0,
    'this_week_events' => 0,
];

if ($tableExists) {
    $stats['total_events'] = db()->fetchOne("SELECT COUNT(*) as cnt FROM security_logs")['cnt'] ?? 0;
    
    // Get blocked attempts (where blocked = true in JSON)
    $blockedResult = db()->fetchOne("SELECT COUNT(*) as cnt FROM security_logs WHERE event_type = 'prompt_injection_attempt' AND JSON_EXTRACT(event_data, '$.blocked') = 1");
    $stats['blocked_attempts'] = $blockedResult['cnt'] ?? 0;
    
    // Get warnings (where blocked = false but has warnings)
    $warningsResult = db()->fetchOne("SELECT COUNT(*) as cnt FROM security_logs WHERE event_type = 'prompt_injection_attempt' AND (JSON_EXTRACT(event_data, '$.blocked') = 0 OR JSON_EXTRACT(event_data, '$.blocked') IS NULL) AND JSON_LENGTH(JSON_EXTRACT(event_data, '$.warnings')) > 0");
    $stats['warnings'] = $warningsResult['cnt'] ?? 0;
    
    $stats['today_events'] = db()->fetchOne("SELECT COUNT(*) as cnt FROM security_logs WHERE DATE(created_at) = CURDATE()")['cnt'] ?? 0;
    $stats['this_week_events'] = db()->fetchOne("SELECT COUNT(*) as cnt FROM security_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Security Logs | Super Admin',
        'metaDescription' => 'Security events and prompt injection monitoring',
        'canonicalUrl' => APP_URL . '/admin/security-logs.php',
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
                <h1 class="text-2xl font-bold text-gray-900">Security Logs</h1>
                <p class="mt-1 text-sm text-gray-500">Monitor security events including prompt injection attempts</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Events</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><?php echo number_format($stats['total_events']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Blocked Attempts</dt>
                                    <dd class="text-lg font-semibold text-red-600"><?php echo number_format($stats['blocked_attempts']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Warnings</dt>
                                    <dd class="text-lg font-semibold text-yellow-600"><?php echo number_format($stats['warnings']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Today</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><?php echo number_format($stats['today_events']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">This Week</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><?php echo number_format($stats['this_week_events']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-lg rounded-xl border-2 border-gray-200 p-6 mb-6">
                <form method="GET" action="" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                            <input type="text" id="user_id" name="user_id" value="<?php echo e($userFilter); ?>" 
                                   placeholder="User UUID" 
                                   class="block w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                            <select id="event_type" name="event_type" class="block w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Event Types</option>
                                <?php foreach ($eventTypes as $eventType): ?>
                                    <option value="<?php echo e($eventType); ?>" <?php echo $eventTypeFilter === $eventType ? 'selected' : ''; ?>>
                                        <?php echo e(ucfirst(str_replace('_', ' ', $eventType))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo e($dateFrom); ?>" 
                                   class="block w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo e($dateTo); ?>" 
                                   class="block w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 transition-colors">
                            Filter
                        </button>
                        <?php if ($userFilter || $eventTypeFilter || $dateFrom || $dateTo): ?>
                            <a href="/admin/security-logs.php" class="px-6 py-2 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 transition-colors">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Security Logs Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <?php if (empty($securityLogs)): ?>
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No security events found</h3>
                        <p class="mt-1 text-sm text-gray-500"><?php echo $tableExists ? 'Try adjusting your filters.' : 'Security logs table does not exist. Run the migration to enable logging.'; ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($securityLogs as $log): ?>
                                    <?php
                                    $eventData = json_decode($log['event_data'] ?? '{}', true);
                                    $isBlocked = $eventData['blocked'] ?? false;
                                    $warnings = $eventData['warnings'] ?? [];
                                    $instructionPreview = $eventData['instruction_preview'] ?? '';
                                    ?>
                                    <tr class="<?php echo $isBlocked ? 'bg-red-50' : (!empty($warnings) ? 'bg-yellow-50' : ''); ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php
                                            $logTime = strtotime($log['created_at']);
                                            echo date('j M Y, H:i:s', $logTime);
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if ($log['user_id']): ?>
                                                <div>
                                                    <div class="font-medium text-gray-900">
                                                        <?php echo e($log['full_name'] ?? $log['username'] ?? 'Unknown'); ?>
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        <?php echo e($log['email'] ?? $log['user_id']); ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $isBlocked ? 'bg-red-100 text-red-800' : (!empty($warnings) ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $log['event_type']))); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php if ($log['event_type'] === 'prompt_injection_attempt'): ?>
                                                <div class="space-y-1">
                                                    <?php if ($isBlocked): ?>
                                                        <div class="text-red-600 font-semibold">⚠️ Blocked</div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($warnings)): ?>
                                                        <div class="text-yellow-600">
                                                            <strong>Warnings:</strong> <?php echo count($warnings); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($instructionPreview): ?>
                                                        <div class="text-xs text-gray-600 mt-2">
                                                            <strong>Preview:</strong> 
                                                            <code class="bg-gray-100 px-1 py-0.5 rounded"><?php echo e(substr($instructionPreview, 0, 150)); ?><?php echo strlen($instructionPreview) > 150 ? '...' : ''; ?></code>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-xs text-gray-500">
                                                    <?php
                                                    $details = is_array($eventData) ? $eventData : [];
                                                    if (!empty($details)) {
                                                        echo '<pre class="whitespace-pre-wrap">' . e(json_encode($details, JSON_PRETTY_PRINT)) . '</pre>';
                                                    } else {
                                                        echo '—';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e($log['ip_address'] ?? '—'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalLogs); ?> of <?php echo number_format($totalLogs); ?> events
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $userFilter ? '&user_id=' . urlencode($userFilter) : ''; ?><?php echo $eventTypeFilter ? '&event_type=' . urlencode($eventTypeFilter) : ''; ?><?php echo $dateFrom ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo $dateTo ? '&date_to=' . urlencode($dateTo) : ''; ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $userFilter ? '&user_id=' . urlencode($userFilter) : ''; ?><?php echo $eventTypeFilter ? '&event_type=' . urlencode($eventTypeFilter) : ''; ?><?php echo $dateFrom ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo $dateTo ? '&date_to=' . urlencode($dateTo) : ''; ?>" 
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

