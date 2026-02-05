<?php
/**
 * CV Display Page
 * Handles both /cv/@username and /cv.php (for viewing own CV when logged in)
 */

require_once __DIR__ . '/php/helpers.php';

// Get username or user ID from query parameters
$username = get('username');
$userIdParam = get('userid');
$variantId = get('variant_id'); // Support CV variants

// Determine which profile to load
$profile = null;
$profileUserId = null;
$cvVariant = null;

if ($username) {
    // Load by username (public view)
    $profile = db()->fetchOne(
        "SELECT * FROM profiles WHERE username = ?",
        [$username]
    );

    if ($profile) {
        $profileUserId = $profile['id'];
    }
} elseif ($userIdParam) {
    // Load by user ID (backward compatibility)
    $profile = db()->fetchOne(
        "SELECT * FROM profiles WHERE id = ?",
        [$userIdParam]
    );

    if ($profile) {
        $profileUserId = $profile['id'];
    }
} elseif (isLoggedIn()) {
    // Logged in user viewing their own CV
    $profileUserId = getUserId();
    $profile = db()->fetchOne(
        "SELECT * FROM profiles WHERE id = ?",
        [$profileUserId]
    );
}

// If no profile found, show error
if (!$profile) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CV Not Found</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-16 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">CV Not Found</h1>
            <p class="text-gray-600 mb-8">The CV you're looking for doesn't exist or has been removed.</p>
            <a href="/" class="text-blue-600 hover:text-blue-800">Return to Home</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Check CV visibility/access permissions
$currentUserId = getUserId();
$canView = false;
$cvVisibility = $profile['cv_visibility'] ?? 'public';

// Owner can always view their own CV
if ($currentUserId && $currentUserId === $profileUserId) {
    $canView = true;
}
// Public CVs can be viewed by anyone
elseif ($cvVisibility === 'public') {
    $canView = true;
}
// Organisation-visible CVs require membership check
elseif ($cvVisibility === 'organisation' && $currentUserId && $profile['organisation_id']) {
    // Check if current user is in the same organisation
    $viewerMembership = db()->fetchOne(
        "SELECT id FROM organisation_members
         WHERE user_id = ? AND organisation_id = ? AND is_active = 1",
        [$currentUserId, $profile['organisation_id']]
    );
    $canView = !empty($viewerMembership);
}
// Private CVs can only be viewed by the owner (already checked above)
elseif ($cvVisibility === 'private') {
    $canView = false;
}

// Show access denied if can't view
if (!$canView) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-16 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Access Denied</h1>
            <p class="text-gray-600 mb-8">This CV is not publicly available. Please contact the owner or your organisation administrator for access.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="/?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-blue-600 hover:text-blue-800">Log in to continue</a>
            <?php else: ?>
                <a href="/" class="text-blue-600 hover:text-blue-800">Return to Home</a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Load CV data - either from variant or master CV
$cvData = null;
if ($variantId && isLoggedIn() && $currentUserId === $profileUserId) {
    // Load variant data
    $cvVariant = getCvVariant($variantId, $profileUserId);
    if ($cvVariant) {
        $cvData = loadCvVariantData($variantId);
        // Variant data includes 'variant' key, but we still need profile for display
        // Add profile data to variant data structure
        if ($cvData && isset($cvData['variant'])) {
            $cvData['profile'] = $profile; // Use existing profile data
        } else {
            $cvData = null; // Fallback to master if variant data invalid
        }
    }
}

// Fallback to master CV if variant not found or not specified
if (!$cvData) {
    $cvData = loadCvData($profileUserId);
}

// Format date helper - show only month and year (MM/YYYY)
function formatCvDate($date, $format = null) {
    if (empty($date)) return '';

    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;

    // Format as MM/YYYY (month/year only, matching original implementation)
    // date('m') gives zero-padded month (01-12), date('Y') gives 4-digit year
    return date('m/Y', $timestamp);
}

// Check for custom template (new system: cv_templates table)
require_once __DIR__ . '/php/cv-templates.php';

// Check if a specific template is requested via query parameter
$requestedTemplateId = $_GET['template'] ?? null;
$activeTemplate = null;

if ($requestedTemplateId) {
    // Get the requested template (must belong to the user)
    $activeTemplate = getCvTemplate($requestedTemplateId, $profileUserId);
} else {
    // Get the active template for the user
    $activeTemplate = getActiveCvTemplate($profileUserId);
}

// Fallback to old system (profiles table) for backward compatibility
if (!$activeTemplate && !empty($profile['custom_cv_template_active']) && !empty($profile['custom_cv_template_html'])) {
    $activeTemplate = [
        'template_html' => $profile['custom_cv_template_html'],
        'template_css' => $profile['custom_cv_template_css'] ?? '',
        'template_name' => 'Custom Template'
    ];
}

if ($activeTemplate) {
    // Render custom template using Twig (secure)
    $customHtml = $activeTemplate['template_html'];
    $customCss = $activeTemplate['template_css'] ?? '';
    
    // Use Twig template service for secure rendering
    // Variables profile, cvData, and formatCvDate() function are available in the template
    require_once __DIR__ . '/php/twig-template-service.php';
    $renderedContent = renderTemplate($customHtml, [
        'profile' => $profile,
        'cvData' => $cvData
        // formatCvDate() function is registered in Twig, no need to pass it
    ]);
    
    // Output custom template
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($profile['full_name'] ?? 'CV'); ?> - CV</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <?php if (!empty($customCss)): ?>
            <style><?php echo $customCss; ?></style>
        <?php endif; ?>
        <style>
            @media print {
                .no-print { display: none !important; }
            }
            /* Prevent left column (e.g. interests) content from bleeding into right column */
            .cv-container .grid > *,
            .cv-container [class*="col-span"] {
                min-width: 0;
            }
            .cv-container .grid .space-y-6 > section {
                overflow: hidden;
            }
            /* Keep pre/code from marked.js visible and within column */
            .cv-container .markdown-content pre,
            .cv-container .markdown-content code {
                max-width: 100%;
                overflow-wrap: break-word;
                white-space: pre-wrap;
                word-break: break-word;
            }
            .cv-container .markdown-content pre {
                margin: 0.5em 0;
                padding: 0.5em;
                background: #f3f4f6;
                border-radius: 0.25rem;
                font-size: inherit;
            }
            .cv-container .markdown-content code {
                padding: 0.125em 0.25em;
                background: #f3f4f6;
                border-radius: 0.125rem;
                font-size: 0.875em;
            }
            /* Interests & Activities: same as body text, align with title */
            #cv-interests-section .markdown-content pre,
            #cv-interests-section .markdown-content code,
            #cv-interests-section .markdown-content p,
            #cv-interests-section .markdown-content blockquote {
                font-family: inherit;
                background: transparent;
                padding: 0;
                margin: 0 0 0.5em 0;
                margin-left: 0;
                padding-left: 0;
                border-radius: 0;
                font-size: inherit;
                font-weight: inherit;
                line-height: inherit;
                color: inherit;
                text-align: left;
                text-indent: 0;
                white-space: normal;
            }
            #cv-interests-section .markdown-content blockquote {
                border-left: none;
            }
            #cv-interests-section .markdown-content > *:first-child {
                margin-top: 0;
            }
            #cv-interests-section .markdown-content ul,
            #cv-interests-section .markdown-content ol {
                margin-left: 0;
                padding-left: 1.25em;
            }
            #cv-interests-section .markdown-content {
                margin-left: 0;
                padding-left: 0;
            }
        </style>
    </head>
    <body class="bg-gray-100">
        <?php partial('header'); ?>
        <main id="main-content" role="main">
            <?php if (isLoggedIn() && getUserId() === $profileUserId): ?>
                <div class="no-print bg-white shadow-sm border-b">
                    <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm sm:text-base">
                            <a href="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <?php if (!empty($variantId)): ?>
                                <a href="/content-editor.php#work-experience&variant_id=<?php echo e($variantId); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm sm:text-base transition-colors">
                                    Edit this variant
                                </a>
                            <?php endif; ?>
                            <a href="/cv-template-customizer.php" class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm sm:text-base transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                Customise Template
                            </a>
                            <a href="/preview-cv.php<?php echo !empty($variantId) ? '?variant_id=' . e($variantId) : ''; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 inline-block text-center text-sm sm:text-base">
                                Generate PDF
                            </a>
                            <button onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm sm:text-base">
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
                <div class="bg-white cv-container max-w-6xl mx-auto shadow-md rounded-xl overflow-hidden">
                    <?php echo $renderedContent; ?>
                </div>
            </div>
    </main>
    <?php partial('footer'); ?>
    <script>
    // Enhance markdown rendering with marked.js for better support
    if (typeof marked !== 'undefined') {
        document.querySelectorAll('.markdown-content').forEach(function(el) {
            const originalHtml = el.innerHTML;
            try {
                const rendered = marked.parse(originalHtml, { breaks: true, gfm: true });
                el.innerHTML = rendered;
            } catch (e) {
                // Fallback to original if parsing fails
                console.warn('Markdown parsing failed, using original:', e);
            }
        });
    }
    </script>
</body>
</html>
    <?php
    exit; // Stop here, don't render default template
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($profile['full_name'] ?? 'CV'); ?> - CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
            .cv-container { box-shadow: none; }
        }
        .icon {
            display: inline-block;
            width: 1em;
            height: 1em;
            vertical-align: middle;
            margin-right: 0.25em;
        }
        /* Keep pre/code from marked.js visible and within column (no overflow) */
        .cv-container .markdown-content pre,
        .cv-container .markdown-content code {
            max-width: 100%;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .cv-container .markdown-content pre {
            margin: 0.5em 0;
            padding: 0.5em;
            background: #f3f4f6;
            border-radius: 0.25rem;
            font-size: inherit;
        }
        .cv-container .markdown-content code {
            padding: 0.125em 0.25em;
            background: #f3f4f6;
            border-radius: 0.125rem;
            font-size: 0.875em;
        }
        /* Interests & Activities: render like normal body text, not code; align with title */
        #cv-interests-section .markdown-content pre,
        #cv-interests-section .markdown-content code,
        #cv-interests-section .markdown-content p,
        #cv-interests-section .markdown-content blockquote {
            font-family: inherit;
            background: transparent;
            padding: 0;
            margin: 0 0 0.5em 0;
            margin-left: 0;
            padding-left: 0;
            border-radius: 0;
            font-size: inherit;
            font-weight: inherit;
            line-height: inherit;
            color: inherit;
            text-align: left;
            text-indent: 0;
            white-space: normal;
        }
        #cv-interests-section .markdown-content blockquote {
            border-left: none;
        }
        #cv-interests-section .markdown-content > *:first-child {
            margin-top: 0;
        }
        #cv-interests-section .markdown-content ul,
        #cv-interests-section .markdown-content ol {
            margin-left: 0;
            padding-left: 1.25em;
        }
        #cv-interests-section .markdown-content {
            margin-left: 0;
            padding-left: 0;
        }
    </style>
    </head>
<body class="bg-gray-100">
    <?php partial('header'); ?>
    <main id="main-content" role="main">
    <!-- Header with actions (hidden on print) -->
    <?php if (isLoggedIn() && getUserId() === $profileUserId): ?>
        <div class="no-print bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm sm:text-base">
                    <a href="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <?php if (!empty($variantId)): ?>
                        <a href="/content-editor.php#work-experience&variant_id=<?php echo e($variantId); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm sm:text-base transition-colors">
                            Edit this variant
                        </a>
                    <?php endif; ?>
                    <a href="/profile.php" class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm sm:text-base transition-colors">
                        Edit Profile
                    </a>
                    <a href="/preview-cv.php<?php echo !empty($variantId) ? '?variant_id=' . e($variantId) : ''; ?>" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm sm:text-base transition-colors">
                        Generate PDF
                    </a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm sm:text-base transition-colors">
                        Print
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CV Container - Full Width with Padding -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="bg-white cv-container max-w-6xl mx-auto shadow-md rounded-xl overflow-hidden">
            <!-- CV Header with Gradient -->
            <div style="background: linear-gradient(to right, <?php echo e($profile['cv_header_from_color'] ?? '#4338ca'); ?>, <?php echo e($profile['cv_header_to_color'] ?? '#7e22ce'); ?>);" class="text-white p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl sm:text-4xl font-bold leading-tight break-words">
                            <?php echo e($profile['full_name'] ?? 'Your Name'); ?>
                        </h1>
                        <?php if (!empty($profile['location'])): ?>
                            <p class="text-white/90 mt-3 flex items-center text-sm sm:text-base gap-2">
                                <svg class="icon mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                <?php echo e($profile['location']); ?>
                            </p>
                        <?php endif; ?>
                        <div class="flex flex-wrap gap-3 mt-4 text-xs sm:text-sm">
                            <?php if (!empty($profile['email'])): ?>
                                <a href="mailto:<?php echo e($profile['email']); ?>" class="text-white/90 hover:text-white flex items-center">
                                    <svg class="icon mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                    <?php echo e($profile['email']); ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($profile['phone'])): ?>
                                <span class="text-white/90 flex items-center">
                                    <svg class="icon mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                    </svg>
                                    <?php echo e($profile['phone']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($profile['linkedin_url'])): ?>
                                <a href="<?php echo e($profile['linkedin_url']); ?>" target="_blank" class="text-white/90 hover:text-white flex items-center">
                                    <svg class="icon mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd"></path>
                                    </svg>
                                    LinkedIn
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($profile['bio'])): ?>
                            <div class="mt-4 pt-4 border-t border-white/20 text-sm sm:text-base">
                                <p class="text-white/90 leading-relaxed"><?php echo nl2br(e(html_entity_decode($profile['bio'], ENT_QUOTES, 'UTF-8'))); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($profile['photo_url']) && (!isset($profile['show_photo']) || $profile['show_photo'] == 1)): ?>
                        <?php
                        // Get responsive image attributes for profile photo (context: 'cv' for CV page)
                        $photoResponsiveData = isset($profile['photo_responsive']) ? $profile['photo_responsive'] : null;
                        $photoImgAttrs = getResponsiveImageAttributes($photoResponsiveData, $profile['photo_url'], 'cv');
                        ?>
                        <img src="<?php echo e($photoImgAttrs['src']); ?>"
                             <?php if (!empty($photoImgAttrs['srcset'])): ?>
                                 srcset="<?php echo e($photoImgAttrs['srcset']); ?>"
                                 sizes="<?php echo e($photoImgAttrs['sizes']); ?>"
                             <?php endif; ?>
                             alt="<?php echo e($profile['full_name'] ?? 'Profile'); ?>"
                             class="w-32 h-32 sm:w-40 sm:h-40 lg:w-48 lg:h-48 rounded-full object-cover border-4 border-white/20 mx-auto lg:mx-0"
                             loading="lazy"
                             width="192"
                             height="192">
                    <?php endif; ?>
                </div>
            </div>

            <!-- CV Content -->
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    <!-- Left Column (Narrower) - Certifications, Education, Skills, Interests -->
                    <div class="lg:col-span-1 min-w-0 overflow-hidden space-y-6 order-2 lg:order-1">
                        <!-- Certifications -->
                        <?php if (!empty($cvData['certifications'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Certifications
                                </h2>
                                <?php foreach ($cvData['certifications'] as $cert): ?>
                                    <div class="mb-3">
                                        <h3 class="font-semibold text-gray-900 text-sm"><?php echo e($cert['name']); ?></h3>
                                        <p class="text-gray-700 text-sm"><?php echo e($cert['issuer']); ?></p>
                                        <p class="text-gray-600 text-xs mt-1">
                                            <?php echo formatCvDate($cert['date_obtained']); ?>
                                            <?php if (!empty($cert['expiry_date'])): ?>
                                                <br>Expires: <?php echo formatCvDate($cert['expiry_date']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Education -->
                        <?php if (!empty($cvData['education'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Education
                                </h2>
                                <?php foreach ($cvData['education'] as $edu): ?>
                                    <div class="mb-4">
                                        <p class="font-semibold text-gray-900 text-sm"><span class="text-gray-500 font-normal">Qual:</span> <?php echo e($edu['degree']); ?></p>
                                        <p class="text-gray-700 text-sm"><span class="text-gray-500 font-normal">Institution:</span> <?php echo e($edu['institution']); ?></p>
                                        <?php if (!empty($edu['field_of_study'])): ?>
                                            <p class="text-gray-600 text-sm"><span class="text-gray-500 font-normal">Subject:</span> <?php echo e($edu['field_of_study']); ?></p>
                                        <?php endif; ?>
                                        <?php if (empty($edu['hide_date'])): ?>
                                        <p class="text-gray-600 text-xs mt-1">
                                            <?php echo formatCvDate($edu['start_date']); ?>
                                            <?php if (!empty($edu['end_date'])): ?>
                                                - <?php echo formatCvDate($edu['end_date']); ?>
                                            <?php else: ?>
                                                - Present
                                            <?php endif; ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Skills -->
                        <?php if (!empty($cvData['skills'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Skills
                                </h2>
                                <?php
                                $skillsByCategory = [];
                                foreach ($cvData['skills'] as $skill) {
                                    $category = $skill['category'] ?? 'Other';
                                    if (!isset($skillsByCategory[$category])) {
                                        $skillsByCategory[$category] = [];
                                    }
                                    $skillsByCategory[$category][] = $skill;
                                }
                                ?>
                                <?php foreach ($skillsByCategory as $category => $skills): ?>
                                    <div class="mb-3">
                                        <h3 class="font-semibold text-gray-800 text-sm mb-1"><?php echo e($category); ?>:</h3>
                                        <div class="flex flex-wrap gap-1.5">
                                            <?php foreach ($skills as $skill): ?>
                                                <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-700 text-xs">
                                                    <?php echo e($skill['name']); ?>
                                                    <?php if (!empty($skill['level'])): ?>
                                                        <span class="text-gray-500">(<?php echo e($skill['level']); ?>)</span>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Interests & Activities -->
                        <?php if (!empty($cvData['interests'])): ?>
                            <section id="cv-interests-section">
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Interests & Activities
                                </h2>
                                <div class="space-y-3">
                                    <?php foreach ($cvData['interests'] as $interest): ?>
                                        <div class="min-w-0 rounded-lg border border-gray-200 bg-white/70 p-4 shadow-sm">
                                            <h3 class="text-sm font-semibold text-gray-800">
                                                <?php echo e($interest['name']); ?>
                                            </h3>
                                            <?php if (!empty($interest['description'])): ?>
                                                <div class="mt-2 text-sm text-gray-600 leading-relaxed break-words markdown-content">
                                                    <?php echo renderMarkdown(trim($interest['description'] ?? '')); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column (Wider) - Summary, Work Experience, Projects, Memberships -->
                    <div class="lg:col-span-2 min-w-0 space-y-6 order-1 lg:order-2">
                        <!-- Professional Summary -->
                        <?php if (!empty($cvData['professional_summary'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Professional Summary
                                </h2>
                                <?php if (!empty($cvData['professional_summary']['description'])): ?>
                                    <div class="text-gray-700 mb-3 text-sm leading-relaxed markdown-content"><?php echo renderMarkdown($cvData['professional_summary']['description'] ?? ''); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($cvData['professional_summary']['strengths'])): ?>
                                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">Key Strengths:</h3>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        <?php foreach ($cvData['professional_summary']['strengths'] as $strength): ?>
                                            <li class="text-gray-700"><?php echo e(html_entity_decode($strength['strength'], ENT_QUOTES, 'UTF-8')); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Work Experience -->
                        <?php if (!empty($cvData['work_experience'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Work Experience
                                </h2>
                                <?php foreach ($cvData['work_experience'] as $work): ?>
                                    <div class="mb-6">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-2">
                                            <div class="min-w-0">
                                                <h3 class="text-lg font-semibold text-gray-900"><?php echo e(html_entity_decode($work['position'], ENT_QUOTES, 'UTF-8')); ?></h3>
                                                <p class="text-base text-gray-700"><?php echo e(html_entity_decode($work['company_name'], ENT_QUOTES, 'UTF-8')); ?></p>
                                            </div>
                                            <?php if (!$work['hide_date']): ?>
                                                <div class="text-gray-600 text-sm sm:text-right whitespace-nowrap flex-shrink-0">
                                                    <?php echo formatCvDate($work['start_date']); ?>
                                                    <?php if (!empty($work['end_date'])): ?>
                                                        - <?php echo formatCvDate($work['end_date']); ?>
                                                    <?php else: ?>
                                                        - Present
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($work['description'])): ?>
                                            <div class="text-gray-700 mb-3 text-sm leading-relaxed markdown-content"><?php echo renderMarkdown($work['description'] ?? ''); ?></div>
                                        <?php endif; ?>

                                        <!-- Responsibilities -->
                                        <?php if (!empty($work['responsibility_categories'])): ?>
                                            <?php $toggleId = 'responsibilities-' . $work['id']; ?>
                                            <button
                                                type="button"
                                                class="inline-flex w-full sm:w-auto items-center justify-center rounded bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                                data-toggle="collapse"
                                                data-target="<?php echo e($toggleId); ?>"
                                                data-view-label="View Responsibilities"
                                                data-hide-label="Hide Responsibilities"
                                                aria-expanded="false"
                                            >
                                                <svg class="mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path class="icon-plus" fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                                    <path class="icon-minus hidden" fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="toggle-label">View Responsibilities</span>
                                            </button>

                                            <div id="<?php echo e($toggleId); ?>" class="mt-3 hidden space-y-4 border-l-2 border-indigo-100 pl-4 text-sm text-gray-700 print:block">
                                                <?php foreach ($work['responsibility_categories'] as $category): ?>
                                                    <?php if (!empty($category['items'])): ?>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-800 mb-1 text-sm"><?php echo e($category['name']); ?></h4>
                                                            <ul class="list-disc space-y-1 pl-5">
                                                                <?php foreach ($category['items'] as $item): ?>
                                                                    <li><?php echo e($item['content']); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($work !== end($cvData['work_experience'])): ?>
                                        <hr class="my-3 border-gray-200">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Projects -->
                        <?php if (!empty($cvData['projects'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Projects
                                </h2>
                                <?php foreach ($cvData['projects'] as $project): ?>
                                    <div class="mb-4">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-1">
                                            <?php
                                            $projectUrl = !empty($project['url']) ? html_entity_decode($project['url'], ENT_QUOTES, 'UTF-8') : '';
                                            ?>
                                            <h3 class="text-lg font-semibold text-gray-900 flex items-center min-w-0">
                                                <?php if (!empty($projectUrl)): ?>
                                                    <a href="<?php echo e($projectUrl); ?>" target="_blank" class="inline-flex items-center text-blue-700 hover:text-blue-900">
                                                        <span><?php echo e($project['title']); ?></span>
                                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                    </a>
                                                <?php else: ?>
                                                    <span><?php echo e($project['title']); ?></span>
                                                <?php endif; ?>
                                            </h3>
                                            <?php if (!empty($project['start_date'])): ?>
                                                <div class="text-gray-600 text-sm whitespace-nowrap flex-shrink-0 sm:text-right">
                                                    <?php echo formatCvDate($project['start_date']); ?>
                                                    <?php if (!empty($project['end_date'])): ?>
                                                        - <?php echo formatCvDate($project['end_date']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($project['description'])): ?>
                                            <div class="text-gray-700 text-sm leading-relaxed markdown-content"><?php echo renderMarkdown($project['description'] ?? ''); ?></div>
                                        <?php endif; ?>
                                        <?php
                                        $projectImagePath = isset($project['image_path']) ? html_entity_decode($project['image_path'], ENT_QUOTES, 'UTF-8') : null;
                                        $projectImageUrlRaw = isset($project['image_url']) ? html_entity_decode($project['image_url'], ENT_QUOTES, 'UTF-8') : '';
                                        $projectImageUrl = '';

                                        if (!empty($projectImageUrlRaw)) {
                                            $projectImageUrl = $projectImageUrlRaw;
                                        } elseif (!empty($projectImagePath)) {
                                            $projectImageUrl = '/api/storage-proxy?path=' . urlencode($projectImagePath);
                                        }
                                        
                                        // Get responsive image attributes (context: 'cv' for CV page)
                                        $responsiveData = isset($project['image_responsive']) ? $project['image_responsive'] : null;
                                        // #region agent log
                                        debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:497','message'=>'Project image data check','data'=>['projectId'=>$project['id']??'unknown','projectTitle'=>$project['title']??'unknown','hasImageUrl'=>!empty($projectImageUrl),'imageUrl'=>$projectImageUrl,'hasImagePath'=>!empty($projectImagePath),'imagePath'=>$projectImagePath,'hasResponsiveData'=>!empty($responsiveData),'responsiveDataType'=>gettype($responsiveData),'responsiveDataPreview'=>is_string($responsiveData)?substr($responsiveData,0,200):'not string'],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C1']);
                                        // #endregion
                                        $imgAttrs = getResponsiveImageAttributes($responsiveData, $projectImageUrl, 'cv');
                                        // #region agent log
                                        debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:500','message'=>'Image attributes generated','data'=>['hasSrcset'=>!empty($imgAttrs['srcset']),'srcsetLength'=>strlen($imgAttrs['srcset']??''),'srcsetPreview'=>substr($imgAttrs['srcset']??'',0,200),'sizes'=>$imgAttrs['sizes']??'','src'=>$imgAttrs['src']??''],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C1']);
                                        // #endregion
                                        ?>
                                        <?php 
                                        // #region agent log
                                        debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:506','message'=>'About to render image','data'=>['hasSrc'=>!empty($imgAttrs['src']),'hasSrcset'=>!empty($imgAttrs['srcset']),'srcsetLength'=>strlen($imgAttrs['srcset']??''),'srcsetValue'=>$imgAttrs['srcset']??'EMPTY','projectTitle'=>$project['title']??'unknown'],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C7']);
                                        // #endregion
                                        if (!empty($imgAttrs['src'])): ?>
                                            <div class="mt-3">
                                                <?php if (!empty($projectUrl)): ?>
                                                    <a href="<?php echo e($projectUrl); ?>" target="_blank" aria-label="View <?php echo e($project['title']); ?> project">
                                                        <img 
                                                            src="<?php echo e($imgAttrs['src']); ?>" 
                                                            <?php if (!empty($imgAttrs['srcset'])): ?>
                                                                srcset="<?php echo e($imgAttrs['srcset']); ?>"
                                                                sizes="<?php echo e($imgAttrs['sizes']); ?>"
                                                                <?php 
                                                                // #region agent log
                                                                debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:515','message'=>'Rendering srcset in HTML','data'=>['srcsetValue'=>$imgAttrs['srcset'],'sizesValue'=>$imgAttrs['sizes']],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C7']);
                                                                // #endregion
                                                                ?>
                                                            <?php else: ?>
                                                                <?php 
                                                                // #region agent log
                                                                debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:520','message'=>'srcset is empty - not rendering','data'=>['imgAttrsKeys'=>array_keys($imgAttrs),'srcsetValue'=>$imgAttrs['srcset']??'NULL'],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C7']);
                                                                // #endregion
                                                                ?>
                                                            <?php endif; ?>
                                                            alt="<?php echo e($project['title']); ?> - Project image"
                                                            class="w-full rounded-md border border-gray-200"
                                                            loading="lazy"
                                                            width="800"
                                                            height="600"
                                                            decoding="async">
                                                    </a>
                                                <?php else: ?>
                                                    <img 
                                                        src="<?php echo e($imgAttrs['src']); ?>" 
                                                        <?php if (!empty($imgAttrs['srcset'])): ?>
                                                            srcset="<?php echo e($imgAttrs['srcset']); ?>"
                                                            sizes="<?php echo e($imgAttrs['sizes']); ?>"
                                                            <?php 
                                                            // #region agent log
                                                            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:531','message'=>'Rendering srcset in HTML (no link)','data'=>['srcsetValue'=>$imgAttrs['srcset'],'sizesValue'=>$imgAttrs['sizes']],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C7']);
                                                            // #endregion
                                                            ?>
                                                        <?php else: ?>
                                                            <?php 
                                                            // #region agent log
                                                            debugLog(['id'=>'log_'.time().'_'.uniqid(),'timestamp'=>time()*1000,'location'=>'cv.php:536','message'=>'srcset is empty - not rendering (no link)','data'=>['imgAttrsKeys'=>array_keys($imgAttrs),'srcsetValue'=>$imgAttrs['srcset']??'NULL'],'sessionId'=>'debug-session','runId'=>'run2','hypothesisId'=>'C7']);
                                                            // #endregion
                                                            ?>
                                                        <?php endif; ?>
                                                        alt="<?php echo e($project['title']); ?> - Project image"
                                                        class="w-full rounded-md border border-gray-200"
                                                        loading="lazy"
                                                        width="800"
                                                        height="600"
                                                        decoding="async">
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Professional Qualification Equivalence -->
                        <?php if (!empty($cvData['qualification_equivalence'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Professional Qualification Equivalence
                                </h2>
                                <?php foreach ($cvData['qualification_equivalence'] as $qual): ?>
                                    <div class="mb-4">
                                        <h3 class="font-semibold text-gray-900 text-sm mb-1"><?php echo e($qual['level']); ?></h3>
                                        <?php if (!empty($qual['description'])): ?>
                                            <div class="text-gray-700 text-sm leading-relaxed markdown-content"><?php echo renderMarkdown($qual['description'] ?? ''); ?></div>
                                        <?php endif; ?>
                                            <?php if (!empty($qual['evidence'])): ?>
                                                <?php $evidenceId = 'evidence-' . $qual['id']; ?>
                                                <button
                                                    type="button"
                                                    class="mt-2 inline-flex w-full sm:w-auto items-center justify-center rounded bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                                    data-toggle="collapse"
                                                    data-target="<?php echo e($evidenceId); ?>"
                                                    data-view-label="View Supporting Evidence"
                                                    data-hide-label="Hide Supporting Evidence"
                                                    aria-expanded="false"
                                                >
                                                    <svg class="mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path class="icon-plus" fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                                        <path class="icon-minus hidden" fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="toggle-label">View Supporting Evidence</span>
                                                </button>
                                                <div id="<?php echo e($evidenceId); ?>" class="mt-2 hidden rounded-md bg-gray-50 p-4 text-sm text-gray-700 print:block">
                                                    <ul class="list-disc space-y-1 pl-5">
                                                        <?php foreach ($qual['evidence'] as $evidence): ?>
                                                            <li><?php echo e($evidence['content']); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Professional Memberships -->
                        <?php if (!empty($cvData['memberships'])): ?>
                            <section>
                                <h2 class="text-xl font-bold text-gray-900 mb-3 border-b-2 border-gray-300 pb-2">
                                    Professional Memberships
                                </h2>
                                <?php foreach ($cvData['memberships'] as $membership): ?>
                                    <div class="mb-3">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <h3 class="font-semibold text-gray-900 text-sm"><?php echo e($membership['organisation']); ?></h3>
                                                <?php if (!empty($membership['role'])): ?>
                                                    <p class="text-gray-700 text-sm"><?php echo e($membership['role']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-gray-600 text-sm sm:text-right whitespace-nowrap flex-shrink-0">
                                                <?php echo formatCvDate($membership['start_date']); ?>
                                                <?php if (!empty($membership['end_date'])): ?>
                                                    - <?php echo formatCvDate($membership['end_date']); ?>
                                                <?php else: ?>
                                                    - Present
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>

    <?php partial('footer'); ?>
    
    <?php partial('auth-modals'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButtons = document.querySelectorAll('[data-toggle="collapse"]');
        toggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const target = document.getElementById(targetId);
                if (!target) {
                    return;
                }

                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const nextState = !isExpanded;
                button.setAttribute('aria-expanded', nextState ? 'true' : 'false');

                if (nextState) {
                    target.classList.remove('hidden');
                } else {
                    target.classList.add('hidden');
                }

                const plusIcon = button.querySelector('.icon-plus');
                const minusIcon = button.querySelector('.icon-minus');
                if (plusIcon && minusIcon) {
                    plusIcon.classList.toggle('hidden', nextState);
                    minusIcon.classList.toggle('hidden', !nextState);
                }

                const label = button.querySelector('.toggle-label');
                const viewText = button.getAttribute('data-view-label') || 'View';
                const hideText = button.getAttribute('data-hide-label') || 'Hide';
                if (label) {
                    label.textContent = nextState ? hideText : viewText;
                }
            });
        });
    });
</script>

</body>
</html>
