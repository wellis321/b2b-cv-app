<?php
/**
 * Server-side PDF Generation using Dompdf
 * Generates high-quality PDFs from HTML preview
 */

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/cv-data.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$token = post(CSRF_TOKEN_NAME);
if (!verifyCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

try {
    $userId = getUserId();

    // Get parameters from request
    $sections = json_decode(post('sections', '[]'), true);
    $includePhoto = post('includePhoto', '1') === '1';
    $includeQr = post('includeQr', '1') === '1';
    $templateId = post('templateId', 'professional');

    // Load CV data
    $cvData = loadCvData($userId);
    $profile = $cvData['profile'];

    if (!$profile) {
        http_response_code(404);
        echo json_encode(['error' => 'Profile not found']);
        exit;
    }

    // Build the CV URL for QR code
    $cvUrl = APP_URL . '/cv/@' . $profile['username'];

    // Generate QR code if needed
    $qrCodeDataUrl = null;
    if ($includeQr) {
        // We'll need to generate this server-side or pass it from client
        // For now, we'll expect it to be passed from the client
        $qrCodeDataUrl = post('qrCodeImage');
    }

    // Build HTML content for PDF
    $html = buildCvHtml($cvData, $profile, $sections, $includePhoto, $includeQr, $qrCodeDataUrl, $templateId);

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Allow loading external resources (images)
    $options->set('defaultFont', 'Arial');
    $options->set('isFontSubsettingEnabled', true);
    $options->set('isPhpEnabled', false); // Security: disable PHP in PDF

    $dompdf = new Dompdf($options);

    // Load HTML
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render PDF
    $dompdf->render();

    // Generate filename
    $fileName = sanitizeInput($profile['full_name'] ?? 'CV');
    $fileName = preg_replace('/[^a-z0-9_\-]/i', '_', $fileName);
    $fileName .= '_CV.pdf';

    // Output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    echo $dompdf->output();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate PDF: ' . $e->getMessage()]);
    if (DEBUG) {
        error_log('PDF Generation Error: ' . $e->getMessage());
        error_log($e->getTraceAsString());
    }
}

/**
 * Build HTML content for the CV
 */
function buildCvHtml($cvData, $profile, $sections, $includePhoto, $includeQr, $qrCodeDataUrl, $templateId) {
    $dateFormat = $profile['date_format_preference'] ?? 'dd/mm/yyyy';

    // Get template colors
    $templateColors = getTemplateColors($templateId);
    $headingColor = $templateColors['header'];
    $bodyColor = $templateColors['body'];
    $accentColor = $templateColors['accent'];
    $mutedColor = $templateColors['muted'];

    // Start HTML
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #374151;
        }

        /* Tailwind-like utility classes */
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-5 { margin-bottom: 1.25rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-8 { margin-top: 2rem; }

        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-base { font-size: 1rem; line-height: 1.5rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }

        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }

        .leading-relaxed { line-height: 1.625; }

        /* Flexbox alternative for Dompdf - using tables */
        .flex {
            display: table;
            width: 100%;
        }
        .flex.items-start,
        .flex.items-center,
        .flex.justify-between {
            display: table;
            width: 100%;
        }
        .flex > div:first-child {
            display: table-cell;
            vertical-align: top;
        }
        .flex > div:last-child {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            white-space: nowrap;
        }
        .flex-1 {
            display: table-cell;
            width: 100%;
        }
        .flex-wrap {
            display: block;
        }
        .gap-1\.5 > * { margin-right: 0.375rem; margin-bottom: 0.375rem; }
        .gap-2 > * { margin-right: 0.5rem; margin-bottom: 0.5rem; }
        .gap-4 > * { margin-right: 1rem; }

        .whitespace-nowrap { white-space: nowrap; }

        .rounded { border-radius: 0.25rem; }
        .rounded-full { border-radius: 9999px; }

        .border-2 { border-width: 2px; }
        .border-gray-300 { border-color: #d1d5db; }

        .bg-gray-100 { background-color: #f3f4f6; }

        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .py-0\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }

        .w-32 { width: 8rem; }
        .h-32 { height: 8rem; }

        .object-cover { object-fit: cover; }

        .text-right { text-align: right; }

        .list-disc { list-style-type: disc; }
        .list-inside { list-style-position: inside; }
        .space-y-1 > * + * { margin-top: 0.25rem; }

        section {
            page-break-inside: avoid;
        }

        h1, h2, h3 {
            page-break-after: avoid;
        }

        a {
            text-decoration: underline;
        }
    </style>
</head>
<body>';

    // Profile/Header section
    if (in_array('profile', $sections)) {
        $html .= '<div class="mb-6">';
        $html .= '<div class="flex items-start justify-between gap-4">';
        $html .= '<div class="flex-1">';
        $html .= '<h1 class="text-3xl font-bold mb-2" style="color:' . $headingColor . ';">' . htmlspecialchars($profile['full_name'] ?? 'Your Name') . '</h1>';

        if (!empty($profile['location'])) {
            $html .= '<p class="text-sm mb-1" style="color:' . $bodyColor . ';">' . htmlspecialchars($profile['location']) . '</p>';
        }

        $contactBits = [];
        if (!empty($profile['email'])) {
            $contactBits[] = '<span style="color:' . $bodyColor . ';">' . htmlspecialchars($profile['email']) . '</span>';
        }
        if (!empty($profile['phone'])) {
            $contactBits[] = '<span style="color:' . $bodyColor . ';">' . htmlspecialchars($profile['phone']) . '</span>';
        }
        if (!empty($profile['linkedin_url'])) {
            $contactBits[] = '<a href="' . htmlspecialchars($profile['linkedin_url']) . '" style="color:' . $accentColor . '; text-decoration: underline;">LinkedIn</a>';
        }

        if (count($contactBits) > 0) {
            $html .= '<p class="text-xs mt-2" style="color:' . $bodyColor . ';">' . implode(' <span style="color:' . $mutedColor . ';">|</span> ', $contactBits) . '</p>';
        }

        if (!empty($profile['bio'])) {
            $html .= '<p class="text-sm mt-3" style="color:' . $bodyColor . ';">' . htmlspecialchars($profile['bio']) . '</p>';
        }

        $html .= '</div>'; // flex-1

        // Photo and QR code
        if ($includePhoto && !empty($profile['photo_url'])) {
            $photoUrl = $profile['photo_url'];
            // Convert relative URLs to absolute
            if (strpos($photoUrl, 'http') !== 0) {
                $photoUrl = APP_URL . '/' . ltrim($photoUrl, '/');
            }
            $html .= '<img src="' . htmlspecialchars($photoUrl) . '" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover border-2 border-gray-300">';
        }

        $html .= '</div>'; // flex
        $html .= '</div>'; // mb-6
    }

    // Professional Summary
    if (in_array('summary', $sections) && !empty($cvData['professional_summary'])) {
        $summary = $cvData['professional_summary'];
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Professional Summary</h2>';

        if (!empty($summary['description'])) {
            $html .= '<p class="text-sm leading-relaxed mb-3" style="color:' . $bodyColor . ';">' . htmlspecialchars($summary['description']) . '</p>';
        }

        if (!empty($summary['strengths']) && is_array($summary['strengths'])) {
            $html .= '<h3 class="font-semibold text-sm mb-2" style="color:' . $headingColor . ';">Key Strengths:</h3>';
            $html .= '<ul class="list-disc list-inside space-y-1 text-sm" style="color:' . $bodyColor . ';">';
            foreach ($summary['strengths'] as $strength) {
                $html .= '<li>' . htmlspecialchars($strength['strength']) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</section>';
    }

    // Work Experience
    if (in_array('work', $sections) && !empty($cvData['work_experience'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Work Experience</h2>';

        foreach ($cvData['work_experience'] as $work) {
            $html .= '<div class="mb-5">';
            $html .= '<div class="flex justify-between items-start gap-4 mb-2">';
            $html .= '<div>';
            $html .= '<h3 class="text-lg font-semibold" style="color:' . $headingColor . ';">' . htmlspecialchars($work['position'] ?? $work['job_title'] ?? '') . '</h3>';
            $html .= '<p class="text-sm" style="color:' . $bodyColor . ';">' . htmlspecialchars($work['company_name'] ?? '') . '</p>';
            $html .= '</div>';

            if (empty($work['hide_date'])) {
                $startDate = formatCvDateAsMonthYear($work['start_date'] ?? '');
                $endDate = !empty($work['current_job']) ? 'Present' : formatCvDateAsMonthYear($work['end_date'] ?? '');
                if ($startDate || $endDate) {
                    $html .= '<div class="text-sm whitespace-nowrap" style="color:' . $mutedColor . ';">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</div>';
                }
            }

            $html .= '</div>';

            if (!empty($work['description'])) {
                $html .= '<p class="text-sm leading-relaxed mb-3" style="color:' . $bodyColor . ';">' . htmlspecialchars($work['description']) . '</p>';
            }

            // Responsibilities
            if (!empty($work['responsibility_categories']) && is_array($work['responsibility_categories'])) {
                foreach ($work['responsibility_categories'] as $category) {
                    if (!empty($category['items']) && is_array($category['items'])) {
                        $html .= '<div class="mb-3">';
                        if (!empty($category['name'])) {
                            $html .= '<h4 class="font-semibold text-sm mb-1" style="color:' . $headingColor . ';">' . htmlspecialchars($category['name']) . ':</h4>';
                        }
                        $html .= '<ul class="list-disc list-inside space-y-1 text-sm" style="color:' . $bodyColor . ';">';
                        foreach ($category['items'] as $item) {
                            $html .= '<li>' . htmlspecialchars($item['content'] ?? $item['description'] ?? '') . '</li>';
                        }
                        $html .= '</ul></div>';
                    }
                }
            }

            $html .= '</div>';
        }

        $html .= '</section>';
    }

    // Education
    if (in_array('education', $sections) && !empty($cvData['education'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Education</h2>';

        foreach ($cvData['education'] as $edu) {
            $html .= '<div class="mb-4">';
            $html .= '<h3 class="font-semibold text-base" style="color:' . $headingColor . ';">' . htmlspecialchars($edu['degree'] ?? '') . '</h3>';
            $html .= '<p class="text-sm" style="color:' . $bodyColor . ';">' . htmlspecialchars($edu['institution'] ?? '') . '</p>';

            if (!empty($edu['field_of_study'])) {
                $html .= '<p class="text-sm" style="color:' . $mutedColor . ';">' . htmlspecialchars($edu['field_of_study']) . '</p>';
            }

            $startDate = formatCvDateAsMonthYear($edu['start_date'] ?? '');
            $endDate = !empty($edu['end_date']) ? formatCvDateAsMonthYear($edu['end_date']) : 'Present';
            $html .= '<p class="text-xs mt-1" style="color:' . $mutedColor . ';">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</p>';

            $html .= '</div>';
        }

        $html .= '</section>';
    }

    // Skills
    if (in_array('skills', $sections) && !empty($cvData['skills'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Skills</h2>';

        // Group skills by category
        $grouped = [];
        foreach ($cvData['skills'] as $skill) {
            $key = $skill['category'] ?: 'Other';
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $skill;
        }

        foreach ($grouped as $key => $skills) {
            $html .= '<div class="mb-3">';
            $html .= '<h3 class="font-semibold text-sm mb-1" style="color:' . $headingColor . ';">' . htmlspecialchars($key) . ':</h3>';
            $html .= '<div class="flex flex-wrap gap-1.5 text-xs" style="color:' . $bodyColor . ';">';
            foreach ($skills as $skill) {
                $skillText = htmlspecialchars($skill['name']);
                if (!empty($skill['level'])) {
                    $skillText .= ' (' . htmlspecialchars($skill['level']) . ')';
                }
                $html .= '<span class="px-2 py-0.5 rounded bg-gray-100">' . $skillText . '</span>';
            }
            $html .= '</div></div>';
        }

        $html .= '</section>';
    }

    // Projects
    if (in_array('projects', $sections) && !empty($cvData['projects'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Projects</h2>';

        foreach ($cvData['projects'] as $project) {
            $html .= '<div class="mb-4">';
            $html .= '<div class="flex justify-between items-start mb-1">';
            $html .= '<h3 class="text-lg font-semibold" style="color:' . $headingColor . ';">' . htmlspecialchars($project['title'] ?? $project['name'] ?? '') . '</h3>';
            if (!empty($project['url'])) {
                $html .= '<a href="' . htmlspecialchars($project['url']) . '" class="text-sm" style="color:' . $accentColor . ';">View →</a>';
            }
            $html .= '</div>';

            if (!empty($project['start_date'])) {
                $startDate = formatCvDateAsMonthYear($project['start_date']);
                $endDate = !empty($project['end_date']) ? formatCvDateAsMonthYear($project['end_date']) : '';
                $html .= '<div class="text-sm mb-2" style="color:' . $mutedColor . ';">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</div>';
            }

            if (!empty($project['description'])) {
                $html .= '<p class="text-sm leading-relaxed" style="color:' . $bodyColor . ';">' . htmlspecialchars($project['description']) . '</p>';
            }

            $html .= '</div>';
        }

        $html .= '</section>';
    }

    // Certifications
    if (in_array('certifications', $sections) && !empty($cvData['certifications'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Certifications</h2>';

        foreach ($cvData['certifications'] as $cert) {
            $html .= '<div class="mb-3">';
            $html .= '<h3 class="font-semibold text-sm" style="color:' . $headingColor . ';">' . htmlspecialchars($cert['name']) . '</h3>';
            $html .= '<p class="text-sm" style="color:' . $bodyColor . ';">' . htmlspecialchars($cert['issuer'] ?? $cert['issuing_organization'] ?? '') . '</p>';

            $obtained = !empty($cert['date_obtained']) ? 'Obtained: ' . formatCvDateAsMonthYear($cert['date_obtained']) : '';
            $expires = !empty($cert['expiry_date']) ? ' | Expires: ' . formatCvDateAsMonthYear($cert['expiry_date']) : '';
            if ($obtained || $expires) {
                $html .= '<p class="text-xs mt-1" style="color:' . $mutedColor . ';">' . htmlspecialchars($obtained . $expires) . '</p>';
            }

            $html .= '</div>';
        }

        $html .= '</section>';
    }

    // Professional Memberships
    if (in_array('memberships', $sections) && !empty($cvData['memberships'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Professional Memberships</h2>';

        foreach ($cvData['memberships'] as $membership) {
            $html .= '<div class="mb-3">';
            $html .= '<div class="flex justify-between items-start gap-4">';
            $html .= '<div>';
            $html .= '<h3 class="font-semibold text-sm" style="color:' . $headingColor . ';">' . htmlspecialchars($membership['organisation'] ?? $membership['organization_name'] ?? '') . '</h3>';
            if (!empty($membership['role'])) {
                $html .= '<p class="text-sm" style="color:' . $bodyColor . ';">' . htmlspecialchars($membership['role']) . '</p>';
            }
            $html .= '</div>';

            $startDate = formatCvDateAsMonthYear($membership['start_date'] ?? '');
            $endDate = !empty($membership['current_member']) ? 'Present' : formatCvDateAsMonthYear($membership['end_date'] ?? '');
            $html .= '<div class="text-sm" style="color:' . $mutedColor . ';">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</div>';

            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</section>';
    }

    // Interests
    if (in_array('interests', $sections) && !empty($cvData['interests'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Interests & Activities</h2>';
        $html .= '<div class="flex flex-wrap gap-2 text-sm" style="color:' . $bodyColor . ';">';
        foreach ($cvData['interests'] as $interest) {
            $html .= '<span class="px-2 py-1 rounded bg-gray-100">' . htmlspecialchars($interest['name']) . '</span>';
        }
        $html .= '</div></section>';
    }

    // Qualification Equivalence
    if (in_array('qualifications', $sections) && !empty($cvData['qualification_equivalence'])) {
        $html .= '<section class="mb-6">';
        $html .= '<h2 class="text-xl font-bold mb-3" style="color:' . $headingColor . ';">Professional Qualification Equivalence</h2>';

        foreach ($cvData['qualification_equivalence'] as $qual) {
            $html .= '<div class="mb-4">';
            $html .= '<h3 class="font-semibold text-sm" style="color:' . $headingColor . ';">Level ' . htmlspecialchars($qual['level']) . '</h3>';
            if (!empty($qual['description'])) {
                $html .= '<p class="text-sm" style="color:' . $bodyColor . ';">' . htmlspecialchars($qual['description']) . '</p>';
            }

            if (!empty($qual['evidence']) && is_array($qual['evidence'])) {
                $html .= '<ul class="list-disc list-inside space-y-1 text-sm mt-2" style="color:' . $bodyColor . ';">';
                foreach ($qual['evidence'] as $evidence) {
                    $html .= '<li>' . htmlspecialchars($evidence['description']) . '</li>';
                }
                $html .= '</ul>';
            }

            $html .= '</div>';
        }

        $html .= '</section>';
    }

    // QR Code
    if ($includeQr && !empty($qrCodeDataUrl)) {
        $html .= '<div class="mt-8 text-right">';
        $html .= '<img src="' . htmlspecialchars($qrCodeDataUrl) . '" alt="QR Code" style="width: 100px; height: 100px; display: inline-block;">';
        $html .= '<p class="text-xs" style="color:' . $mutedColor . ';">Scan to view my CV online</p>';
        $html .= '</div>';
    }

    $html .= '</body></html>';

    return $html;
}

/**
 * Format date as MM/YYYY (matches preview template)
 */
function formatCvDateAsMonthYear($dateStr) {
    if (empty($dateStr)) return '';
    $timestamp = strtotime($dateStr);
    if ($timestamp === false) return $dateStr;

    return date('m/Y', $timestamp);
}

/**
 * Get template color palette
 */
function getTemplateColors($templateId) {
    $templates = [
        'professional' => [
            'header' => '#1f2937',
            'body' => '#374151',
            'accent' => '#2563eb',
            'muted' => '#6b7280'
        ],
        'minimal' => [
            'header' => '#111827',
            'body' => '#374151',
            'accent' => '#111827',
            'muted' => '#6b7280'
        ],
        'classic' => [
            'header' => '#1e3a8a',
            'body' => '#475569',
            'accent' => '#1e3a8a',
            'muted' => '#64748b'
        ],
        'modern' => [
            'header' => '#0f172a',
            'body' => '#334155',
            'accent' => '#0d9488',
            'muted' => '#64748b'
        ]
    ];

    return $templates[$templateId] ?? $templates['professional'];
}
