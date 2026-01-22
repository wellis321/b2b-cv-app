<?php
/**
 * Update Custom CV Template
 */

define('SKIP_CANONICAL_REDIRECT', true);
require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/cv-templates.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit;
}

$user = getCurrentUser();
$templateId = $_POST['template_id'] ?? null;
$templateHtml = $_POST['template_html'] ?? null;
$templateCss = $_POST['template_css'] ?? null;
$templateName = $_POST['template_name'] ?? null;
$templateDescription = $_POST['template_description'] ?? null;

if (!$templateId) {
    setFlash('error', 'Template ID required');
    redirect('/cv-template-customizer.php');
    exit;
}

// Validate HTML/CSS size (similar to agency homepage limits)
if ($templateHtml !== null && strlen($templateHtml) > 500000) {
    setFlash('error', 'Template HTML is too large. Maximum 500KB allowed.');
    redirect('/cv-template-customizer.php');
    exit;
}

if ($templateCss !== null && strlen($templateCss) > 100000) {
    setFlash('error', 'Template CSS is too large. Maximum 100KB allowed.');
    redirect('/cv-template-customizer.php');
    exit;
}

// Security validation: Validate Twig template syntax before saving
require_once __DIR__ . '/../php/twig-template-service.php';
if ($templateHtml !== null) {
    $validation = validateTwigTemplate($templateHtml);
    if (!$validation['valid']) {
        setFlash('error', $validation['error']);
        redirect('/cv-template-customizer.php');
        exit;
    }
}

$result = updateCvTemplate($templateId, $user['id'], $templateName, $templateHtml, $templateCss, $templateDescription);

if ($result['success']) {
    setFlash('success', 'Template updated successfully');
} else {
    setFlash('error', $result['error'] ?? 'Failed to update template');
}

redirect('/cv-template-customizer.php');

