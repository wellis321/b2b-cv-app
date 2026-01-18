<?php
/**
 * Dashboard page - CV sections overview
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

// Note: Candidates and organisation members can access their CV sections via this dashboard
// Organisation members can also access the agency dashboard via the "Agency Dashboard" link in navigation

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Dashboard | Simple CV Builder',
        'metaDescription' => 'Overview of your CV sections, completion status, and quick actions.',
        'canonicalUrl' => APP_URL . '/dashboard.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('dashboard', ['user' => $user, 'error' => $error, 'success' => $success]); ?>
</body>
</html>
