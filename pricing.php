<?php
/**
 * Public pricing page – individual plans and organisation CTA.
 * No auth required. Reachable at /pricing (routed in index.php).
 */

require_once __DIR__ . '/php/helpers.php';

$pageTitle = 'Pricing | Simple CV Builder';
$metaDescription = 'Free CV builder and job tracker. Try 7 days for £1.95 or 3 months for £27.88. Find the plan that fits your job search best.';
$canonicalUrl = APP_URL . '/pricing';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle,
        'metaDescription' => $metaDescription,
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" role="main">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Pricing</h1>
                <p class="mt-2 text-lg text-gray-600">
                    Find the plan that fits your job search best. Free, 7-day trial, or 3 months.
                </p>
            </div>
            <?php partial('home-pricing', ['pricingUseRegisterModal' => false]); ?>
        </div>
    </main>

    <?php partial('footer'); ?>

    <?php
    $error = getFlash('error') ?: null;
    $success = getFlash('success') ?: null;
    $needsVerification = getFlash('needs_verification') ?: false;
    $verificationEmail = getFlash('verification_email') ?: null;
    $oldLoginEmail = getFlash('old_login_email') ?: null;
    partial('auth-modals', [
        'error' => $error,
        'success' => $success,
        'needsVerification' => $needsVerification,
        'verificationEmail' => $verificationEmail,
        'oldLoginEmail' => $oldLoginEmail,
    ]);
    ?>
</body>
</html>
