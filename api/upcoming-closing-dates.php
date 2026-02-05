<?php
/**
 * Returns job applications with closing dates approaching (for browser reminders).
 * Only includes jobs in "interested" or "in_progress" (not yet applied / considering).
 * GET: no body. Uses user's reminder preferences (days before: e.g. 7, 3, 1).
 */

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = getUserId();

// User reminder preferences (defaults if columns not present)
$reminderEnabled = true;
$reminderDays = [7, 3, 1];
try {
    $prefs = db()->fetchOne(
        "SELECT closing_date_reminder_enabled, closing_date_reminder_days FROM profiles WHERE id = ?",
        [$userId]
    );
    if ($prefs) {
        if (isset($prefs['closing_date_reminder_enabled'])) {
            $reminderEnabled = (bool) $prefs['closing_date_reminder_enabled'];
        }
        if (!empty($prefs['closing_date_reminder_days'])) {
            $parts = array_map('intval', array_filter(explode(',', $prefs['closing_date_reminder_days'])));
            if (!empty($parts)) {
                $reminderDays = $parts;
            }
        }
    }
} catch (Throwable $e) {
    // Columns may not exist before migration
}

if (!$reminderEnabled) {
    echo json_encode(['reminders' => [], 'enabled' => false]);
    exit;
}

// Statuses that mean "before applied" / considering
$beforeAppliedStatuses = ['interested', 'in_progress'];
$placeholders = implode(',', array_fill(0, count($beforeAppliedStatuses), '?'));
$dayPlaceholders = implode(',', array_fill(0, count($reminderDays), '?'));

$params = array_merge([$userId], $beforeAppliedStatuses, $reminderDays);

$sql = "SELECT id, company_name, job_title, next_follow_up,
        DATEDIFF(DATE(next_follow_up), CURDATE()) AS days_until
        FROM job_applications
        WHERE user_id = ?
        AND status IN ($placeholders)
        AND next_follow_up IS NOT NULL
        AND DATE(next_follow_up) >= CURDATE()
        AND DATEDIFF(DATE(next_follow_up), CURDATE()) IN ($dayPlaceholders)
        ORDER BY next_follow_up ASC";

try {
    $rows = db()->fetchAll($sql, $params);
} catch (Throwable $e) {
    // ENUM may not include 'interested' before migration; return empty
    $rows = [];
}

$reminders = [];
foreach ($rows as $row) {
    $reminders[] = [
        'id' => $row['id'],
        'company_name' => $row['company_name'],
        'job_title' => $row['job_title'],
        'next_follow_up' => $row['next_follow_up'],
        'days_until' => (int) $row['days_until'],
    ];
}

echo json_encode(['reminders' => $reminders, 'enabled' => true]);
