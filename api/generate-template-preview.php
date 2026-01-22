<?php
/**
 * Generate Template Preview
 * Renders a template from configuration for live preview
 */

require_once __DIR__ . '/../php/helpers.php';

header('Content-Type: text/html; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$user = getCurrentUser();
require_once __DIR__ . '/../php/authorisation.php';
if (!isSuperAdmin($user['id'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['config'])) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

$config = $input['config'];
$cvData = $input['cvData'] ?? [];
$profile = $input['profile'] ?? [];

// Convert config to Twig
require_once __DIR__ . '/../php/template-config-to-twig.php';

try {
    $result = convertConfigToTwig($config);
    $html = $result['html'];
    $css = $result['css'];
    
    // Render with Twig
    require_once __DIR__ . '/../php/twig-template-service.php';
    
    // Format date helper function
    function formatCvDate($date, $format = null) {
        if (empty($date)) return '';
        $timestamp = strtotime($date);
        if ($timestamp === false) return $date;
        return date('m/Y', $timestamp);
    }
    
    $renderedHtml = renderTemplate($html, [
        'profile' => $profile,
        'cvData' => $cvData
    ]);
    
    // Output full HTML page with styles
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Template Preview</title>
        <script>
            // Suppress Tailwind CDN warning for preview
            const originalWarn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) {
                    return; // Suppress Tailwind CDN warning
                }
                originalWarn.apply(console, args);
            };
        </script>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            <?php echo $css; ?>
            body {
                padding: 2rem;
                background: #f3f4f6;
                margin: 0;
                overflow-x: hidden;
            }
            /* Ensure layout only applies within preview */
            .preview-wrapper {
                width: 100%;
                max-width: 100%;
            }
        </style>
    </head>
    <body>
        <div class="preview-wrapper">
            <?php echo $renderedHtml; ?>
        </div>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    http_response_code(500);
    echo '<div class="p-4 bg-red-50 text-red-800">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

