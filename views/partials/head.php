<?php
$title = $pageTitle ?? 'Simple CV Builder';
$description = $metaDescription ?? 'Build a standout CV online, share it instantly, and unlock premium templates with Simple CV Builder.';
$canonicalUrl = $canonicalUrl ?? (APP_URL . $_SERVER['REQUEST_URI']);
$metaImage = $metaImage ?? (APP_URL . '/static/images/default-profile.svg');
$metaNoindex = $metaNoindex ?? false;
$structuredDataType = $structuredDataType ?? 'default';
$structuredData = $structuredData ?? [];
$breadcrumbs = $breadcrumbs ?? null;
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($title); ?></title>
<meta name="description" content="<?php echo e($description); ?>">
<link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
<?php if ($metaNoindex): ?>
    <meta name="robots" content="noindex, nofollow">
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo e($title); ?>">
<meta property="og:description" content="<?php echo e($description); ?>">
<meta property="og:url" content="<?php echo e($canonicalUrl); ?>">
<meta property="og:image" content="<?php echo e($metaImage); ?>">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($title); ?>">
<meta name="twitter:description" content="<?php echo e($description); ?>">
<meta name="twitter:image" content="<?php echo e($metaImage); ?>">

<!-- Structured Data (JSON-LD) -->
<?php
$schemas = generateStructuredData($structuredDataType, $structuredData);
if ($breadcrumbs) {
    $schemas = array_merge($schemas, generateStructuredData('breadcrumb', ['breadcrumbs' => $breadcrumbs]));
}
outputStructuredData($schemas);
?>

<!-- Favicons -->
<link rel="apple-touch-icon" sizes="180x180" href="/static/images/favicon_io/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/static/images/favicon_io/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/static/images/favicon_io/favicon-16x16.png">
<link rel="manifest" href="/static/images/favicon_io/site.webmanifest">
<link rel="shortcut icon" href="/static/images/favicon_io/favicon.ico">
<style>
/* Accessibility: Skip to main content link */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
.sr-only:focus {
    position: fixed;
    width: auto;
    height: auto;
    padding: 0.5rem 1rem;
    margin: 0;
    overflow: visible;
    clip: auto;
    white-space: normal;
    z-index: 9999;
}
</style>
<script>
(function(){var w=typeof console!=='undefined'&&console.warn;if(w){var o=console.warn;console.warn=function(){var a=arguments[0];if(a&&typeof a==='string'&&a.indexOf('cdn.tailwindcss.com')!==-1&&a.indexOf('should not be used in production')!==-1)return;o.apply(console,arguments);};}})();
</script>
<script src="https://cdn.tailwindcss.com"></script>
<!-- Markdown support -->
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js"></script>