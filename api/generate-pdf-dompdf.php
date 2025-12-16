<?php
/**
 * IMPROVED Server-side PDF Generation using Dompdf
 * This version properly respects template selection and matches preview styling
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
        $qrCodeDataUrl = post('qrCodeImage');
    }

    // Build HTML content for PDF using the improved template system
    $html = buildTemplatedCvHtml($cvData, $profile, $sections, $includePhoto, $includeQr, $qrCodeDataUrl, $templateId);

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    $options->set('isFontSubsettingEnabled', true);
    $options->set('isPhpEnabled', false);
    $options->set('dpi', 96);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
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
    if (defined('DEBUG') && DEBUG) {
        error_log('PDF Generation Error: ' . $e->getMessage());
        error_log($e->getTraceAsString());
    }
}

/**
 * Get template configuration based on template ID
 */
function getTemplateConfig($templateId) {
    $templates = [
        'professional' => [
            'name' => 'Professional Blue',
            'colors' => [
                'header' => '#1f2937',
                'body' => '#374151',
                'accent' => '#2563eb',
                'muted' => '#6b7280',
                'divider' => '#d1d5db'
            ],
            'fonts' => [
                'heading' => 'Arial, Helvetica, sans-serif',
                'body' => 'Arial, Helvetica, sans-serif'
            ]
        ],
        'minimal' => [
            'name' => 'Minimal',
            'colors' => [
                'header' => '#111827',
                'body' => '#374151',
                'accent' => '#111827',
                'muted' => '#6b7280',
                'divider' => '#e5e7eb'
            ],
            'fonts' => [
                'heading' => 'Arial, Helvetica, sans-serif',
                'body' => 'Arial, Helvetica, sans-serif'
            ]
        ],
        'classic' => [
            'name' => 'Classic',
            'colors' => [
                'header' => '#1e3a8a',
                'body' => '#475569',
                'accent' => '#1e3a8a',
                'muted' => '#64748b',
                'divider' => '#1e3a8a'
            ],
            'fonts' => [
                'heading' => 'Georgia, serif',
                'body' => 'Georgia, serif'
            ]
        ],
        'modern' => [
            'name' => 'Modern',
            'colors' => [
                'header' => '#0f172a',
                'body' => '#334155',
                'accent' => '#0d9488',
                'muted' => '#64748b',
                'divider' => '#0d9488'
            ],
            'fonts' => [
                'heading' => 'Arial, Helvetica, sans-serif',
                'body' => 'Arial, Helvetica, sans-serif'
            ]
        ]
    ];

    return $templates[$templateId] ?? $templates['professional'];
}

/**
 * Build HTML content for the CV using template configuration
 */
function buildTemplatedCvHtml($cvData, $profile, $sections, $includePhoto, $includeQr, $qrCodeDataUrl, $templateId) {
    $template = getTemplateConfig($templateId);
    $colors = $template['colors'];
    $fonts = $template['fonts'];

    // Start HTML with template-specific styling
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 40px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: ' . $fonts['body'] . ';
            font-size: 11pt;
            line-height: 1.5;
            color: ' . $colors['body'] . ';
        }

        .header-container {
            margin-bottom: 24px;
        }

        .header-flex {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 100%;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            white-space: nowrap;
            padding-left: 16px;
        }

        h1 {
            font-size: 28pt;
            font-weight: 700;
            color: ' . $colors['header'] . ';
            margin-bottom: 8px;
            font-family: ' . $fonts['heading'] . ';
        }

        h2.section-heading {
            font-size: 16pt;
            font-weight: 700;
            color: ' . $colors['header'] . ';
            margin-top: 20px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid ' . $colors['divider'] . ';
            font-family: ' . $fonts['heading'] . ';
        }

        h3.job-title {
            font-size: 13pt;
            font-weight: 600;
            color: ' . $colors['header'] . ';
            margin-bottom: 4px;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d1d5db;
        }

        .contact-info {
            font-size: 10pt;
            color: ' . $colors['body'] . ';
            margin-bottom: 4px;
        }

        .contact-separator {
            color: ' . $colors['muted'] . ';
        }

        .bio {
            font-size: 11pt;
            color: ' . $colors['body'] . ';
            margin-top: 8px;
            line-height: 1.5;
        }

        .linkedin-link {
            color: ' . $colors['accent'] . ';
            text-decoration: underline;
        }

        .section {
            margin-bottom: 20px;
        }

        .job-entry {
            margin-bottom: 16px;
        }

        .job-header {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .job-header-left {
            display: table-cell;
            vertical-align: top;
            width: 100%;
        }

        .job-header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            white-space: nowrap;
            padding-left: 16px;
        }

        .company-name {
            font-size: 11pt;
            color: ' . $colors['body'] . ';
            margin-bottom: 2px;
        }

        .job-dates {
            font-size: 10pt;
            color: ' . $colors['muted'] . ';
            white-space: nowrap;
        }

        .description {
            font-size: 10pt;
            color: ' . $colors['body'] . ';
            line-height: 1.6;
            margin-top: 6px;
        }

        .responsibilities {
            margin-top: 6px;
            margin-left: 18px;
        }

        .responsibilities li {
            font-size: 10pt;
            color: ' . $colors['body'] . ';
            line-height: 1.5;
            margin-bottom: 4px;
        }

        .skills-container {
            margin-bottom: 16px;
        }

        .skills-category {
            font-size: 11pt;
            font-weight: 600;
            color: ' . $colors['header'] . ';
            margin-bottom: 6px;
        }

        .skills-list {
            font-size: 10pt;
            color: ' . $colors['body'] . ';
            line-height: 1.5;
        }

        .skill-badge {
            display: inline-block;
            background-color: #f3f4f6;
            padding: 3px 8px;
            margin-right: 6px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 9pt;
        }

        .project-entry {
            margin-bottom: 12px;
        }

        .project-title {
            font-size: 12pt;
            font-weight: 600;
            color: ' . $colors['header'] . ';
        }

        .project-url {
            font-size: 10pt;
            color: ' . $colors['accent'] . ';
            text-decoration: underline;
        }

        .cert-entry {
            margin-bottom: 12px;
        }

        .cert-name {
            font-size: 11pt;
            font-weight: 600;
            color: ' . $colors['header'] . ';
        }

        .cert-issuer {
            font-size: 10pt;
            color: ' . $colors['body'] . ';
        }

        .cert-dates {
            font-size: 9pt;
            color: ' . $colors['muted'] . ';
            margin-top: 2px;
        }

        .membership-entry {
            margin-bottom: 12px;
        }

        .interests-list {
            font-size: 10pt;
            color: ' . $colors['body'] . ';
        }

        .interest-badge {
            display: inline-block;
            background-color: #f3f4f6;
            padding: 4px 10px;
            margin-right: 8px;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .qr-container {
            margin-top: 32px;
            text-align: right;
        }

        .qr-image {
            width: 100px;
            height: 100px;
        }

        .qr-text {
            font-size: 9pt;
            color: ' . $colors['muted'] . ';
            margin-top: 4px;
        }
    </style>
</head>
<body>';

    // Header Section
    if (in_array('profile', $sections)) {
        $html .= '<div class="header-container">';
        $html .= '<div class="header-flex">';
        $html .= '<div class="header-left">';
        $html .= '<h1>' . htmlspecialchars($profile['full_name'] ?? 'Your Name') . '</h1>';

        // Location
        if (!empty($profile['location'])) {
            $html .= '<p class="contact-info">' . htmlspecialchars($profile['location']) . '</p>';
        }

        // Contact info
        $contactParts = [];
        if (!empty($profile['email'])) {
            $contactParts[] = htmlspecialchars($profile['email']);
        }
        if (!empty($profile['phone'])) {
            $contactParts[] = htmlspecialchars($profile['phone']);
        }
        if (count($contactParts) > 0) {
            $html .= '<p class="contact-info">' . implode(' <span class="contact-separator">|</span> ', $contactParts) . '</p>';
        }

        // LinkedIn
        if (!empty($profile['linkedin_url'])) {
            $html .= '<p class="contact-info"><a href="' . htmlspecialchars($profile['linkedin_url']) . '" class="linkedin-link">LinkedIn Profile</a></p>';
        }

        // Bio
        if (!empty($profile['bio'])) {
            $html .= '<p class="bio">' . htmlspecialchars($profile['bio']) . '</p>';
        }

        $html .= '</div>'; // header-left

        // Photo
        if ($includePhoto && !empty($profile['photo_url'])) {
            $html .= '<div class="header-right">';
            $html .= '<img src="' . htmlspecialchars($profile['photo_url']) . '" alt="Profile Photo" class="profile-photo">';
            $html .= '</div>';
        }

        $html .= '</div>'; // header-flex
        $html .= '</div>'; // header-container
    }

    // Professional Summary
    if (in_array('summary', $sections) && !empty($cvData['professional_summary'])) {
        $summary = $cvData['professional_summary'];
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Professional Summary</h2>';

        if (!empty($summary['description'])) {
            $html .= '<p class="description">' . nl2br(htmlspecialchars($summary['description'])) . '</p>';
        }

        if (!empty($summary['strengths']) && is_array($summary['strengths'])) {
            $html .= '<div class="responsibilities"><ul>';
            foreach ($summary['strengths'] as $strength) {
                $strengthText = is_array($strength) ? ($strength['strength'] ?? '') : $strength;
                if (!empty($strengthText)) {
                    $html .= '<li>' . htmlspecialchars($strengthText) . '</li>';
                }
            }
            $html .= '</ul></div>';
        }

        $html .= '</div>';
    }

    // Work Experience
    if (in_array('work', $sections) && !empty($cvData['work_experience'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Work Experience</h2>';

        foreach ($cvData['work_experience'] as $work) {
            $html .= '<div class="job-entry">';
            $html .= '<div class="job-header">';
            $html .= '<div class="job-header-left">';
            $html .= '<h3 class="job-title">' . htmlspecialchars($work['job_title'] ?? '') . '</h3>';
            $html .= '<p class="company-name">' . htmlspecialchars($work['company'] ?? '') . '</p>';
            $html .= '</div>';
            $html .= '<div class="job-header-right">';
            $startDate = formatCvDateAsMonthYear($work['start_date'] ?? '');
            $endDate = !empty($work['current']) ? 'Present' : formatCvDateAsMonthYear($work['end_date'] ?? '');
            $html .= '<p class="job-dates">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</p>';
            $html .= '</div>';
            $html .= '</div>';

            if (!empty($work['description'])) {
                $html .= '<p class="description">' . nl2br(htmlspecialchars($work['description'])) . '</p>';
            }

            if (!empty($work['responsibilities']) && is_array($work['responsibilities'])) {
                $html .= '<div class="responsibilities"><ul>';
                foreach ($work['responsibilities'] as $resp) {
                    $respText = is_array($resp) ? ($resp['description'] ?? $resp['responsibility'] ?? '') : $resp;
                    if (!empty($respText)) {
                        $html .= '<li>' . htmlspecialchars($respText) . '</li>';
                    }
                }
                $html .= '</ul></div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Education
    if (in_array('education', $sections) && !empty($cvData['education'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Education</h2>';

        foreach ($cvData['education'] as $edu) {
            $html .= '<div class="job-entry">';
            $html .= '<div class="job-header">';
            $html .= '<div class="job-header-left">';
            $html .= '<h3 class="job-title">' . htmlspecialchars($edu['degree'] ?? $edu['course'] ?? '') . '</h3>';
            $html .= '<p class="company-name">' . htmlspecialchars($edu['institution'] ?? '') . '</p>';
            if (!empty($edu['field_of_study'])) {
                $html .= '<p class="description">' . htmlspecialchars($edu['field_of_study']) . '</p>';
            }
            $html .= '</div>';
            $html .= '<div class="job-header-right">';
            $startDate = formatCvDateAsMonthYear($edu['start_date'] ?? '');
            $endDate = !empty($edu['end_date']) ? formatCvDateAsMonthYear($edu['end_date']) : 'Present';
            $html .= '<p class="job-dates">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Skills
    if (in_array('skills', $sections) && !empty($cvData['skills'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Skills</h2>';

        // Group skills by category
        $grouped = [];
        foreach ($cvData['skills'] as $skill) {
            $category = $skill['category'] ?: 'Other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $skill;
        }

        foreach ($grouped as $category => $skills) {
            $html .= '<div class="skills-container">';
            $html .= '<p class="skills-category">' . htmlspecialchars($category) . ':</p>';
            $html .= '<div class="skills-list">';
            foreach ($skills as $skill) {
                $skillText = htmlspecialchars($skill['name']);
                if (!empty($skill['level'])) {
                    $skillText .= ' (' . htmlspecialchars($skill['level']) . ')';
                }
                $html .= '<span class="skill-badge">' . $skillText . '</span>';
            }
            $html .= '</div></div>';
        }

        $html .= '</div>';
    }

    // Projects
    if (in_array('projects', $sections) && !empty($cvData['projects'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Projects</h2>';

        foreach ($cvData['projects'] as $project) {
            $html .= '<div class="project-entry">';
            $html .= '<h3 class="project-title">' . htmlspecialchars($project['title'] ?? $project['name'] ?? '') . '</h3>';

            if (!empty($project['start_date'])) {
                $startDate = formatCvDateAsMonthYear($project['start_date']);
                $endDate = !empty($project['end_date']) ? formatCvDateAsMonthYear($project['end_date']) : '';
                $html .= '<p class="job-dates">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</p>';
            }

            if (!empty($project['description'])) {
                $html .= '<p class="description">' . nl2br(htmlspecialchars($project['description'])) . '</p>';
            }

            if (!empty($project['url'])) {
                $html .= '<p><a href="' . htmlspecialchars($project['url']) . '" class="project-url">' . htmlspecialchars($project['url']) . '</a></p>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Certifications
    if (in_array('certifications', $sections) && !empty($cvData['certifications'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Certifications</h2>';

        foreach ($cvData['certifications'] as $cert) {
            $html .= '<div class="cert-entry">';
            $html .= '<h3 class="cert-name">' . htmlspecialchars($cert['name']) . '</h3>';

            if (!empty($cert['issuer']) || !empty($cert['issuing_organization'])) {
                $html .= '<p class="cert-issuer">' . htmlspecialchars($cert['issuer'] ?? $cert['issuing_organization'] ?? '') . '</p>';
            }

            $dates = [];
            if (!empty($cert['date_obtained'])) {
                $dates[] = 'Obtained: ' . formatCvDateAsMonthYear($cert['date_obtained']);
            }
            if (!empty($cert['expiry_date'])) {
                $dates[] = 'Expires: ' . formatCvDateAsMonthYear($cert['expiry_date']);
            }
            if (!empty($dates)) {
                $html .= '<p class="cert-dates">' . htmlspecialchars(implode(' | ', $dates)) . '</p>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Professional Memberships
    if (in_array('memberships', $sections) && !empty($cvData['memberships'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Professional Memberships</h2>';

        foreach ($cvData['memberships'] as $membership) {
            $html .= '<div class="membership-entry">';
            $html .= '<div class="job-header">';
            $html .= '<div class="job-header-left">';
            $html .= '<h3 class="job-title">' . htmlspecialchars($membership['organisation'] ?? $membership['organization_name'] ?? '') . '</h3>';

            if (!empty($membership['role'])) {
                $html .= '<p class="company-name">' . htmlspecialchars($membership['role']) . '</p>';
            }
            $html .= '</div>';
            $html .= '<div class="job-header-right">';
            $startDate = formatCvDateAsMonthYear($membership['start_date'] ?? '');
            $endDate = !empty($membership['current_member']) ? 'Present' : formatCvDateAsMonthYear($membership['end_date'] ?? '');
            $html .= '<p class="job-dates">' . htmlspecialchars($startDate) . ($endDate ? ' - ' . htmlspecialchars($endDate) : '') . '</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Interests & Activities
    if (in_array('interests', $sections) && !empty($cvData['interests'])) {
        $html .= '<div class="section">';
        $html .= '<h2 class="section-heading">Interests & Activities</h2>';
        $html .= '<div class="interests-list">';
        foreach ($cvData['interests'] as $interest) {
            $html .= '<span class="interest-badge">' . htmlspecialchars($interest['name']) . '</span>';
        }
        $html .= '</div></div>';
    }

    // QR Code
    if ($includeQr && !empty($qrCodeDataUrl)) {
        $html .= '<div class="qr-container">';
        $html .= '<img src="' . htmlspecialchars($qrCodeDataUrl) . '" alt="QR Code" class="qr-image">';
        $html .= '<p class="qr-text">Scan to view my CV online</p>';
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