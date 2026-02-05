<?php
/**
 * Helper functions and includes
 */

// Load all core files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/cv-data.php';
require_once __DIR__ . '/subscriptions.php';
require_once __DIR__ . '/stripe.php';
require_once __DIR__ . '/authorisation.php';
require_once __DIR__ . '/invitations.php';
require_once __DIR__ . '/job-applications.php';
require_once __DIR__ . '/ai-service.php';
require_once __DIR__ . '/cv-variants.php';

/**
 * Enforce canonical domain (prevents www/non-www duplicates)
 */
if (!function_exists('enforceCanonicalDomain')) {
function enforceCanonicalDomain() {
    if (PHP_SAPI === 'cli') {
        return;
    }

    $currentHost = $_SERVER['HTTP_HOST'] ?? null;
    if (!$currentHost) {
        return;
    }

    $primaryHost = parse_url(APP_URL, PHP_URL_HOST);
    if (!$primaryHost) {
        return;
    }

    $normalizedCurrent = strtolower($currentHost);
    $normalizedPrimary = strtolower($primaryHost);

    if ($normalizedCurrent === $normalizedPrimary) {
        return;
    }

    $scheme = parse_url(APP_URL, PHP_URL_SCHEME) ?: 'https';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $redirectUrl = $scheme . '://' . $primaryHost . $requestUri;

    header('Location: ' . $redirectUrl, true, 301);
    exit;
}
} // End function_exists check

if (APP_ENV === 'production' && !defined('SKIP_CANONICAL_REDIRECT')) {
    enforceCanonicalDomain();
}

// Set security headers early (before any output)
if (!headers_sent()) {
    setSecurityHeaders();
}

/**
 * Safely write debug log (only in development and if directory exists)
 */
if (!function_exists('debugLog')) {
function debugLog($data) {
    if (!defined('DEBUG') || !DEBUG) {
        return; // Skip in production
    }
    
    $debugLogPath = __DIR__ . '/../.cursor/debug.log';
    $debugLogDir = dirname($debugLogPath);
    
    // Only write if directory exists (development environment)
    if (is_dir($debugLogDir)) {
        @file_put_contents($debugLogPath, json_encode($data) . "\n", FILE_APPEND);
    }
}
} // End function_exists check

/**
 * Render a view/template
 */
if (!function_exists('render')) {
function render($template, $data = []) {
    extract($data);
    $templatePath = __DIR__ . '/../views/' . $template . '.php';

    if (!file_exists($templatePath)) {
        die("Template not found: {$template}");
    }

    ob_start();
    include $templatePath;
    return ob_get_clean();
}
} // End function_exists check

/**
 * Include partial/template
 */
if (!function_exists('partial')) {
function partial($template, $data = []) {
    extract($data);
    $templatePath = __DIR__ . '/../views/partials/' . $template . '.php';

    if (!file_exists($templatePath)) {
        die("Partial not found: {$template}");
    }

    include $templatePath;
}
} // End function_exists check

/**
 * Sanitise job description for safe HTML output (allows tables from Word extraction).
 * Strips all tags except table, tr, td, th, tbody, thead, p, br; removes attributes to prevent XSS.
 */
if (!function_exists('jobDescriptionHtml')) {
function jobDescriptionHtml($html) {
    if ($html === null || $html === '') return '';
    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $allowed = '<table><tbody><thead><tr><td><th><p><br>';
    $html = strip_tags($html, $allowed);
    $html = preg_replace('/<\s*(\w+)\s+[^>]*>/', '<$1>', $html);
    return $html;
}
}

/**
 * Render job description for display. If content contains HTML (e.g. imported tables),
 * output safe HTML; otherwise render as markdown so **bold** etc. work.
 */
if (!function_exists('renderJobDescription')) {
function renderJobDescription($text) {
    if ($text === null || $text === '') return '';
    $trimmed = trim($text);
    // Content that looks like HTML (tables from Word/PDF import) – render as safe HTML
    if (preg_match('/<\s*table[\s>]|<\s*tr\s|<\s*td\s|<\s*th\s/i', $trimmed)) {
        return jobDescriptionHtml($trimmed);
    }
    return renderMarkdown($trimmed);
}
}

/**
 * Render markdown to HTML (basic server-side conversion, enhanced client-side with marked.js)
 * This provides a fallback; marked.js will enhance it client-side for better rendering.
 */
if (!function_exists('renderMarkdown')) {
function renderMarkdown($markdown) {
    if ($markdown === null || $markdown === '') return '';
    
    // Decode HTML entities
    $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Escape HTML to prevent XSS, then convert markdown syntax
    $text = htmlspecialchars($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Convert bold **text** to <strong>text</strong>
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    
    // Convert italic *text* to <em>text</em> (but not if it's part of **text**)
    $text = preg_replace('/(?<!\*)\*([^*]+?)\*(?!\*)/', '<em>$1</em>', $text);
    
    // Convert headers
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);
    
    // Convert links [text](url)
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
    
    // Convert underline tags (if they were in original)
    $text = preg_replace('/&lt;u&gt;(.*?)&lt;\/u&gt;/', '<u>$1</u>', $text);
    
    // Convert line breaks (preserve formatting)
    $text = nl2br($text);
    
    // Allow safe HTML tags only
    $allowed = '<h1><h2><h3><strong><em><u><a><ul><ol><li><br><p>';
    $text = strip_tags($text, $allowed);
    
    return $text;
}
}

/**
 * Strip markdown syntax for plain text (useful for AI processing)
 */
if (!function_exists('stripMarkdown')) {
function stripMarkdown($markdown) {
    if ($markdown === null || $markdown === '') return '';
    
    $text = $markdown;
    
    // Remove headers
    $text = preg_replace('/^#{1,6}\s+(.*?)$/m', '$1', $text);
    
    // Remove bold/italic
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);
    
    // Remove underline tags
    $text = preg_replace('/<u>(.*?)<\/u>/', '$1', $text);
    
    // Remove links (keep text)
    $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
    
    // Remove list markers
    $text = preg_replace('/^[-*+]\s+/m', '', $text);
    $text = preg_replace('/^\d+\.\s+/m', '', $text);
    
    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return trim($text);
}
}

/**
 * Get flash message
 */
if (!function_exists('getFlash')) {
function getFlash($key) {
    $message = $_SESSION['flash_' . $key] ?? null;
    unset($_SESSION['flash_' . $key]);
    return $message;
}
} // End function_exists check

/**
 * Set flash message
 */
if (!function_exists('setFlash')) {
function setFlash($key, $message) {
    $_SESSION['flash_' . $key] = $message;
}
} // End function_exists check

/**
 * Output view
 */
if (!function_exists('view')) {
function view($template, $data = []) {
    echo render($template, $data);
}
} // End function_exists check

/**
 * Include layout with content
 */
if (!function_exists('layout')) {
function layout($layout, $content, $data = []) {
    $data['content'] = $content;
    echo render('layouts/' . $layout, $data);
}
} // End function_exists check

/**
 * Get CV sections in order
 */
if (!function_exists('getCvSections')) {
function getCvSections() {
    return [
        ['id' => 'professional-summary', 'name' => 'Professional Summary', 'path' => '/content-editor.php#professional-summary'],
        ['id' => 'work-experience', 'name' => 'Work Experience', 'path' => '/content-editor.php#work-experience'],
        ['id' => 'education', 'name' => 'Education', 'path' => '/content-editor.php#education'],
        ['id' => 'projects', 'name' => 'Projects', 'path' => '/content-editor.php#projects'],
        ['id' => 'skills', 'name' => 'Skills', 'path' => '/content-editor.php#skills'],
        ['id' => 'certifications', 'name' => 'Certifications', 'path' => '/content-editor.php#certifications'],
        ['id' => 'qualification-equivalence', 'name' => 'Professional Qualification Equivalence', 'path' => '/content-editor.php#qualification-equivalence'],
        ['id' => 'memberships', 'name' => 'Professional Memberships', 'path' => '/content-editor.php#memberships'],
        ['id' => 'interests', 'name' => 'Interests & Activities', 'path' => '/content-editor.php#interests'],
    ];
}
} // End function_exists check

/**
 * Get section navigation (previous and next sections)
 */
if (!function_exists('getSectionNavigation')) {
function getSectionNavigation($currentSectionId) {
    $sections = getCvSections();
    $currentIndex = -1;

    foreach ($sections as $index => $section) {
        if ($section['id'] === $currentSectionId) {
            $currentIndex = $index;
            break;
        }
    }

    if ($currentIndex === -1) {
        return ['previous' => null, 'current' => null, 'next' => null];
    }

    $previous = ($currentIndex > 0) ? $sections[$currentIndex - 1] : null;
    $current = $sections[$currentIndex];
    $next = ($currentIndex < count($sections) - 1) ? $sections[$currentIndex + 1] : null;

    return ['previous' => $previous, 'current' => $current, 'next' => $next];
}
} // End function_exists check

/**
 * Generate JSON-LD structured data
 */
if (!function_exists('generateStructuredData')) {
function generateStructuredData($type = 'default', $data = []) {
    $schemas = [];

    // Organization schema (always included)
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Simple CV Builder',
        'url' => APP_URL,
        'logo' => APP_URL . '/static/favicon.png',
        'description' => 'Build a standout CV online, share it instantly, and unlock premium templates with Simple CV Builder.',
        'sameAs' => [
            // Add social media URLs if available
        ]
    ];

    // WebSite schema (always included)
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'Simple CV Builder',
        'url' => APP_URL,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => APP_URL . '/?s={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];

    // SoftwareApplication schema (for homepage)
    if ($type === 'homepage' || $type === 'default') {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Simple CV Builder',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web Browser',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'GBP',
                'description' => 'Free CV builder with premium templates available'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.5',
                'ratingCount' => '100'
            ]
        ];
    }

    // Article schema (for resource/blog pages)
    if ($type === 'article' && !empty($data)) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? APP_URL . '/static/images/default-profile.svg',
            'datePublished' => $data['datePublished'] ?? date('Y-m-d'),
            'dateModified' => $data['dateModified'] ?? date('Y-m-d'),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Simple CV Builder'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Simple CV Builder',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => APP_URL . '/static/favicon.png'
                ]
            ]
        ];
    }

    // BreadcrumbList schema (if breadcrumbs provided)
    if ($type === 'breadcrumb' && !empty($data['breadcrumbs'])) {
        $itemListElement = [];
        $position = 1;
        foreach ($data['breadcrumbs'] as $crumb) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
                'item' => $crumb['url']
            ];
        }

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement
        ];
    }

    // FAQPage schema (if FAQs provided)
    if ($type === 'faq' && !empty($data['faqs'])) {
        $mainEntity = [];
        foreach ($data['faqs'] as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity
        ];
    }

    return $schemas;
}
} // End function_exists check

/**
 * Output JSON-LD structured data script tags
 */
if (!function_exists('outputStructuredData')) {
function outputStructuredData($schemas) {
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "\n" . '</script>' . "\n";
    }
}
} // End function_exists check

/**
 * Generate responsive image srcset and sizes attributes
 * @param string|array $imageData - Either a JSON string or array of responsive image data
 * @param string $fallbackUrl - Fallback URL if responsive data is not available
 * @param string $context - Context for sizing: 'list' (projects list), 'cv' (CV page), 'full' (full width)
 * @return array - ['srcset' => string, 'sizes' => string, 'src' => string]
 */
if (!function_exists('getResponsiveImageAttributes')) {
function getResponsiveImageAttributes($imageData, $fallbackUrl = '', $context = 'full') {
    $responsive = [];
    
    // Parse JSON if string
    if (is_string($imageData) && !empty($imageData)) {
        $decoded = json_decode($imageData, true);
        // Check if JSON decode failed or returned null/empty/invalid
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded) || !is_array($decoded)) {
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:317','message'=>'JSON decode failed or empty','data'=>['jsonLength'=>strlen($imageData),'jsonError'=>json_last_error_msg(),'preview'=>substr($imageData,0,100),'decodedIsArray'=>is_array($decoded),'decodedCount'=>is_array($decoded)?count($decoded):0],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C5']);
            // #endregion
            $responsive = []; // Treat as empty, will trigger on-the-fly generation
        } else {
            $responsive = $decoded;
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:325','message'=>'Parsed responsive JSON','data'=>['jsonLength'=>strlen($imageData),'parsedCount'=>count($responsive),'keys'=>array_keys($responsive)],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C4']);
            // #endregion
        }
    } elseif (is_array($imageData)) {
        $responsive = $imageData;
    }
    
    // If no responsive data, check if responsive images exist in file system
    if (empty($responsive) && !empty($fallbackUrl)) {
        // Check if fallback URL points to storage (local or production)
        // Extract path after /storage/ (e.g., /storage/projects/userid/file.jpg -> projects/userid/file.jpg)
        if (preg_match('#/storage/(.+)#', $fallbackUrl, $matches)) {
            $relativePath = $matches[1]; // e.g., "projects/userid/file.jpg"
            $originalFileName = basename($relativePath);
            $fullPath = STORAGE_PATH . '/' . $relativePath; // STORAGE_PATH already ends with /storage, so this is correct
            
            // Extract base name and extension
            $pathInfo = pathinfo($originalFileName);
            $baseName = $pathInfo['filename'];
            $ext = $pathInfo['extension'] ?? 'jpg';
            
            // Extract bucket and userId from relative path (e.g., "projects/userid/file.jpg")
            $pathParts = explode('/', $relativePath);
            $bucket = $pathParts[0] ?? 'projects';
            $userId = $pathParts[1] ?? '';
            
            // Check if URL is production (different domain) or local
            $isProductionUrl = (strpos($fallbackUrl, 'https://') === 0 || strpos($fallbackUrl, 'http://') === 0) && strpos($fallbackUrl, APP_URL) === false;
            
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:333','message'=>'Checking for existing responsive images','data'=>['fallbackUrl'=>$fallbackUrl,'relativePath'=>$relativePath,'fullPath'=>$fullPath,'fileExists'=>file_exists($fullPath),'isProductionUrl'=>$isProductionUrl,'storagePath'=>STORAGE_PATH],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C4']);
            // #endregion
            
            // If original file exists locally, check for responsive versions
            if (file_exists($fullPath)) {
                require_once __DIR__ . '/storage.php';
                
                $storageDir = STORAGE_PATH . '/' . $bucket . '/' . $userId;
                
                // Define responsive sizes
                $sizes = [
                    'thumb' => ['width' => 150, 'height' => 150],
                    'small' => ['width' => 400, 'height' => 400],
                    'medium' => ['width' => 800, 'height' => 800],
                    'large' => ['width' => 1200, 'height' => 1200]
                ];
                
                $foundResponsive = [];
                foreach ($sizes as $sizeName => $dimensions) {
                    $resizedFileName = $baseName . '_' . $sizeName . '.' . $ext;
                    $resizedPath = $storageDir . '/' . $resizedFileName;
                    
                    // Check if responsive image already exists
                    if (file_exists($resizedPath)) {
                        $foundResponsive[$sizeName] = [
                            'path' => $bucket . '/' . $userId . '/' . $resizedFileName,
                            'width' => $dimensions['width'],
                            'height' => $dimensions['height']
                        ];
                    } elseif (extension_loaded('gd')) {
                        // Generate if doesn't exist and GD is available
                        if (resizeImage($fullPath, $resizedPath, $dimensions['width'], $dimensions['height'])) {
                            $foundResponsive[$sizeName] = [
                                'path' => $bucket . '/' . $userId . '/' . $resizedFileName,
                                'width' => $dimensions['width'],
                                'height' => $dimensions['height']
                            ];
                        }
                    }
                }
                
                if (!empty($foundResponsive)) {
                    // #region agent log
                    debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:380','message'=>'Found/generated responsive images locally','data'=>['foundCount'=>count($foundResponsive),'sizes'=>array_keys($foundResponsive)],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C4']);
                    // #endregion
                    $responsive = $foundResponsive;
                }
            } elseif ($isProductionUrl) {
                // For production URLs, generate responsive URLs based on naming convention
                // Even if files don't exist locally, we can still generate the URLs
                // The browser will handle 404s gracefully
                $baseUrl = preg_replace('#(/storage/.+)/([^/]+)$#', '$1', $fallbackUrl);
                
                // Define responsive sizes
                $sizes = [
                    'thumb' => ['width' => 150, 'height' => 150],
                    'small' => ['width' => 400, 'height' => 400],
                    'medium' => ['width' => 800, 'height' => 800],
                    'large' => ['width' => 1200, 'height' => 1200]
                ];
                
                $foundResponsive = [];
                foreach ($sizes as $sizeName => $dimensions) {
                    $resizedFileName = $baseName . '_' . $sizeName . '.' . $ext;
                    $responsiveUrl = $baseUrl . '/' . $resizedFileName;
                    
                    // Store as URL format (not path) for production URLs
                    $foundResponsive[$sizeName] = [
                        'url' => $responsiveUrl,
                        'width' => $dimensions['width'],
                        'height' => $dimensions['height']
                    ];
                }
                
                if (!empty($foundResponsive)) {
                    // #region agent log
                    debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:410','message'=>'Generated responsive URLs for production','data'=>['foundCount'=>count($foundResponsive),'sizes'=>array_keys($foundResponsive),'baseUrl'=>$baseUrl],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C6']);
                    // #endregion
                    $responsive = $foundResponsive;
                }
            }
        }
        
        // If still no responsive data, return fallback
        if (empty($responsive)) {
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:417','message'=>'No responsive data - using fallback','data'=>['fallbackUrl'=>$fallbackUrl],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C4']);
            // #endregion
            return [
                'srcset' => '',
                'sizes' => '',
                'src' => $fallbackUrl
            ];
        }
    } elseif (empty($responsive)) {
        // #region agent log
        debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:390','message'=>'No responsive data and no fallback - returning empty','data'=>['fallbackUrl'=>$fallbackUrl],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C4']);
        // #endregion
        return [
            'srcset' => '',
            'sizes' => '',
            'src' => $fallbackUrl
        ];
    }
    
    // Build srcset (small to large)
    $srcsetParts = [];
    $sizes = [];
    
    // Helper function to normalize URL (convert localhost URLs to current domain, preserve production URLs)
    $normalizeUrl = function($url, $path = null) {
        if ($path) {
            // New format: Build URL from path using current APP_URL
            $normalized = STORAGE_URL . '/' . $path;
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:468','message'=>'Normalizing URL from path','data'=>['path'=>$path,'normalized'=>$normalized,'storageUrl'=>STORAGE_URL],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C2']);
            // #endregion
            return $normalized;
        }
        if (empty($url)) {
            return null;
        }
        // Check if URL is a production URL (different domain from APP_URL)
        $isProductionUrl = (strpos($url, 'https://') === 0 || strpos($url, 'http://') === 0) && strpos($url, APP_URL) === false;
        if ($isProductionUrl) {
            // Preserve production URLs as-is
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:478','message'=>'Preserving production URL','data'=>['url'=>$url,'appUrl'=>APP_URL],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C2']);
            // #endregion
            return $url;
        }
        // Old format: If URL contains localhost or different domain, extract path and rebuild
        if (preg_match('#https?://[^/]+(/storage/.+)#', $url, $matches)) {
            // Extract the path part and rebuild with current STORAGE_URL
            $normalized = STORAGE_URL . $matches[1];
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:486','message'=>'Normalizing URL from old format','data'=>['originalUrl'=>$url,'extractedPath'=>$matches[1],'normalized'=>$normalized,'storageUrl'=>STORAGE_URL],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C2']);
            // #endregion
            return $normalized;
        }
        // Already correct domain
        // #region agent log
        debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:492','message'=>'URL already normalized','data'=>['url'=>$url],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C2']);
        // #endregion
        return $url;
    };
    
    // Order: thumb, small, medium, large
    $order = ['thumb', 'small', 'medium', 'large'];
    foreach ($order as $size) {
        if (isset($responsive[$size])) {
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:369','message'=>'Processing responsive size','data'=>['size'=>$size,'hasUrl'=>isset($responsive[$size]['url']),'hasPath'=>isset($responsive[$size]['path']),'url'=>$responsive[$size]['url']??null,'path'=>$responsive[$size]['path']??null,'width'=>$responsive[$size]['width']??0],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C3']);
            // #endregion
            // Support both old format (with 'url') and new format (with 'path' only)
            $imageUrl = $normalizeUrl(
                $responsive[$size]['url'] ?? null,
                $responsive[$size]['path'] ?? null
            );
            
            if ($imageUrl) {
                $width = $responsive[$size]['width'] ?? 0;
                if ($width > 0) {
                    $srcsetParts[] = $imageUrl . ' ' . $width . 'w';
                    // #region agent log
                    debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:382','message'=>'Added to srcset','data'=>['size'=>$size,'imageUrl'=>$imageUrl,'width'=>$width],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C3']);
                    // #endregion
                } else {
                    // #region agent log
                    debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:387','message'=>'Skipped - no width','data'=>['size'=>$size],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C3']);
                    // #endregion
                }
            } else {
                // #region agent log
                debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:391','message'=>'Skipped - no URL','data'=>['size'=>$size],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C3']);
                // #endregion
            }
        } else {
            // #region agent log
            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:395','message'=>'Size not found in responsive data','data'=>['size'=>$size,'availableKeys'=>array_keys($responsive)],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C3']);
            // #endregion
        }
    }
    
    // Use the largest available image as fallback
    $fallback = $fallbackUrl;
    if (!empty($responsive['large'])) {
        $fallback = $normalizeUrl(
            $responsive['large']['url'] ?? null,
            $responsive['large']['path'] ?? null
        ) ?: $fallback;
    } elseif (!empty($responsive['medium'])) {
        $fallback = $normalizeUrl(
            $responsive['medium']['url'] ?? null,
            $responsive['medium']['path'] ?? null
        ) ?: $fallback;
    } elseif (!empty($responsive['small'])) {
        $fallback = $normalizeUrl(
            $responsive['small']['url'] ?? null,
            $responsive['small']['path'] ?? null
        ) ?: $fallback;
    } elseif (!empty($responsive['thumb'])) {
        $fallback = $normalizeUrl(
            $responsive['thumb']['url'] ?? null,
            $responsive['thumb']['path'] ?? null
        ) ?: $fallback;
    }
    
    $srcset = implode(', ', $srcsetParts);
    
    // Context-aware sizes attribute
    // For 'list': Image is md:w-48 (192px) on medium+ screens, full width on mobile
    // For 'cv': Image is w-full (100vw) on all screens
    // For 'full': Default to full width with reasonable max
    switch ($context) {
        case 'list':
            // Projects list: 192px on md+ screens, 100vw on mobile
            // Account for 2x displays: 192px * 2 = 384px, so small (400w) is appropriate
            $sizesAttr = '(max-width: 768px) 100vw, 192px';
            break;
        case 'cv':
            // CV page: Container is max-w-6xl (1152px) with px-4 sm:px-6 lg:px-8 padding
            // Project images are in the right column (lg:col-span-2, takes 2/3 width on large screens)
            // Actual rendered widths at different breakpoints:
            // - Mobile (< 640px): Container is ~calc(100vw - 2rem), image is w-full → small (400w) or medium (800w)
            // - Tablet (640-1024px): Container is ~calc(100vw - 3rem), image is w-full → medium (800w) or large (1200w)
            // - Desktop (> 1024px): Container is max-w-6xl, right column is 2/3 width (~700px) → large (1200w)
            // More specific sizes help browser choose appropriate image when viewport changes
            $sizesAttr = '(max-width: 640px) 100vw, (max-width: 1024px) 100vw, (max-width: 1280px) 700px, 700px';
            break;
        default:
            // Default: Full width with reasonable max
            $sizesAttr = '(max-width: 640px) 100vw, (max-width: 1024px) 100vw, 800px';
    }
    
    // #region agent log
    debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'helpers.php:365','message'=>'getResponsiveImageAttributes result','data'=>['srcset'=>$srcset,'sizes'=>$sizesAttr,'context'=>$context,'fallback'=>$fallback,'responsiveKeys'=>array_keys($responsive)],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A5']);
    // #endregion
    
    return [
        'srcset' => $srcset,
        'sizes' => $sizesAttr,
        'src' => $fallback
    ];
}
} // End function_exists check
