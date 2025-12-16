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
            color: #000;
        }

        .cv-container {
            width: 100%;
        }

        /* Header styles */
        .cv-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header-left {
            flex: 1;
        }

        .header-right {
            text-align: right;
            max-width: 200px;
        }

        h1 {
            font-size: 22pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .tagline {
            font-size: 12pt;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .contact-info {
            font-size: 10pt;
            color: #374151;
            line-height: 1.4;
        }

        .profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .qr-code {
            width: 80px;
            height: 80px;
        }

        /* Section styles */
        .cv-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #d1d5db;
        }

        .section-content {
            font-size: 11pt;
            color: #374151;
        }

        /* Work experience */
        .work-item {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .work-header {
            margin-bottom: 4px;
        }

        .job-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
        }

        .company {
            font-size: 11pt;
            color: #374151;
        }

        .work-dates {
            font-size: 10pt;
            color: #6b7280;
            font-style: italic;
        }

        .work-location {
            font-size: 10pt;
            color: #6b7280;
        }

        .work-description {
            margin: 6px 0;
            color: #374151;
        }

        .responsibility-category {
            margin-top: 8px;
        }

        .category-title {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .responsibility-list {
            margin-left: 20px;
            list-style-type: disc;
        }

        .responsibility-list li {
            margin-bottom: 3px;
            color: #374151;
        }

        /* Education */
        .education-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .degree {
            font-size: 11pt;
            font-weight: bold;
            color: #1f2937;
        }

        .institution {
            font-size: 11pt;
            color: #374151;
        }

        /* Skills */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .skill-category {
            margin-bottom: 8px;
        }

        .skill-category-name {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .skill-items {
            color: #374151;
            font-size: 10pt;
        }

        /* Projects */
        .project-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .project-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1f2937;
        }

        /* Bio/Summary */
        .bio {
            color: #374151;
            margin-bottom: 8px;
        }

        .strengths-list {
            margin-left: 20px;
            list-style-type: disc;
        }

        .strengths-list li {
            margin-bottom: 3px;
            color: #374151;
        }

        /* Utility classes */
        .mb-2 { margin-bottom: 4px; }
        .mb-4 { margin-bottom: 8px; }
        .mt-2 { margin-top: 4px; }
        .text-muted { color: #6b7280; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="cv-container">';

    // Header
    $html .= '<div class="cv-header">';
    $html .= '<div class="header-content">';
    $html .= '<div class="header-left">';
    $html .= '<h1>' . htmlspecialchars($profile['full_name'] ?? '') . '</h1>';

    if (!empty($profile['tagline'])) {
        $html .= '<div class="tagline">' . htmlspecialchars($profile['tagline']) . '</div>';
    }

    // Contact info (only if profile section is enabled)
    if (in_array('profile', $sections)) {
        $html .= '<div class="contact-info">';
        if (!empty($profile['email'])) {
            $html .= '<div>' . htmlspecialchars($profile['email']) . '</div>';
        }
        if (!empty($profile['phone'])) {
            $html .= '<div>' . htmlspecialchars($profile['phone']) . '</div>';
        }
        if (!empty($profile['location'])) {
            $html .= '<div>' . htmlspecialchars($profile['location']) . '</div>';
        }
        $html .= '</div>';
    }

    $html .= '</div>'; // header-left

    // Photo and QR code
    if ($includePhoto || $includeQr) {
        $html .= '<div class="header-right">';

        if ($includePhoto && !empty($profile['photo_url'])) {
            $photoUrl = $profile['photo_url'];
            // Convert relative URLs to absolute
            if (strpos($photoUrl, 'http') !== 0) {
                $photoUrl = APP_URL . '/' . ltrim($photoUrl, '/');
            }
            $html .= '<img src="' . htmlspecialchars($photoUrl) . '" class="profile-photo" alt="Profile Photo">';
        }

        if ($includeQr && !empty($qrCodeDataUrl)) {
            $html .= '<img src="' . htmlspecialchars($qrCodeDataUrl) . '" class="qr-code" alt="QR Code">';
        }

        $html .= '</div>'; // header-right
    }

    $html .= '</div>'; // header-content
    $html .= '</div>'; // cv-header

    // Professional Summary
    if (in_array('summary', $sections) && !empty($cvData['professional_summary'])) {
        $summary = $cvData['professional_summary'];
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Professional Summary</h2>';
        $html .= '<div class="section-content">';

        if (!empty($summary['description'])) {
            $html .= '<div class="bio">' . nl2br(htmlspecialchars($summary['description'])) . '</div>';
        }

        if (!empty($summary['strengths']) && is_array($summary['strengths'])) {
            $html .= '<ul class="strengths-list">';
            foreach ($summary['strengths'] as $strength) {
                $html .= '<li>' . htmlspecialchars($strength['strength']) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div></div>';
    }

    // Work Experience
    if (in_array('work', $sections) && !empty($cvData['work_experience'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Work Experience</h2>';
        $html .= '<div class="section-content">';

        foreach ($cvData['work_experience'] as $work) {
            $html .= '<div class="work-item">';
            $html .= '<div class="work-header">';
            $html .= '<div class="job-title">' . htmlspecialchars($work['job_title']) . '</div>';
            $html .= '<div class="company">' . htmlspecialchars($work['company_name']) . '</div>';

            if (empty($work['hide_date'])) {
                $startDate = formatCvDate($work['start_date'], $dateFormat);
                $endDate = $work['current_job'] ? 'Present' : formatCvDate($work['end_date'], $dateFormat);
                $html .= '<div class="work-dates">' . htmlspecialchars($startDate . ' - ' . $endDate) . '</div>';
            }

            if (!empty($work['location'])) {
                $html .= '<div class="work-location">' . htmlspecialchars($work['location']) . '</div>';
            }

            $html .= '</div>'; // work-header

            if (!empty($work['description'])) {
                $html .= '<div class="work-description">' . nl2br(htmlspecialchars($work['description'])) . '</div>';
            }

            // Responsibilities
            if (!empty($work['responsibility_categories'])) {
                foreach ($work['responsibility_categories'] as $category) {
                    if (!empty($category['items'])) {
                        $html .= '<div class="responsibility-category">';
                        if (!empty($category['name'])) {
                            $html .= '<div class="category-title">' . htmlspecialchars($category['name']) . '</div>';
                        }
                        $html .= '<ul class="responsibility-list">';
                        foreach ($category['items'] as $item) {
                            $html .= '<li>' . htmlspecialchars($item['description']) . '</li>';
                        }
                        $html .= '</ul>';
                        $html .= '</div>';
                    }
                }
            }

            $html .= '</div>'; // work-item
        }

        $html .= '</div></div>';
    }

    // Education
    if (in_array('education', $sections) && !empty($cvData['education'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Education</h2>';
        $html .= '<div class="section-content">';

        foreach ($cvData['education'] as $edu) {
            $html .= '<div class="education-item">';
            $html .= '<div class="degree">' . htmlspecialchars($edu['degree']) . '</div>';
            $html .= '<div class="institution">' . htmlspecialchars($edu['institution']) . '</div>';

            $startDate = formatCvDate($edu['start_date'], $dateFormat);
            $endDate = formatCvDate($edu['end_date'], $dateFormat);
            $html .= '<div class="work-dates">' . htmlspecialchars($startDate . ' - ' . $endDate) . '</div>';

            if (!empty($edu['description'])) {
                $html .= '<div class="mt-2">' . nl2br(htmlspecialchars($edu['description'])) . '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    // Skills
    if (in_array('skills', $sections) && !empty($cvData['skills'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Skills</h2>';
        $html .= '<div class="section-content">';

        // Group skills by category
        $skillsByCategory = [];
        foreach ($cvData['skills'] as $skill) {
            $category = $skill['category'] ?: 'General';
            if (!isset($skillsByCategory[$category])) {
                $skillsByCategory[$category] = [];
            }
            $skillsByCategory[$category][] = $skill['name'];
        }

        $html .= '<div class="skills-grid">';
        foreach ($skillsByCategory as $category => $skills) {
            $html .= '<div class="skill-category">';
            $html .= '<div class="skill-category-name">' . htmlspecialchars($category) . '</div>';
            $html .= '<div class="skill-items">' . htmlspecialchars(implode(', ', $skills)) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '</div></div>';
    }

    // Projects
    if (in_array('projects', $sections) && !empty($cvData['projects'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Projects</h2>';
        $html .= '<div class="section-content">';

        foreach ($cvData['projects'] as $project) {
            $html .= '<div class="project-item">';
            $html .= '<div class="project-name">' . htmlspecialchars($project['name']) . '</div>';

            if (!empty($project['role'])) {
                $html .= '<div class="text-muted">' . htmlspecialchars($project['role']) . '</div>';
            }

            $startDate = formatCvDate($project['start_date'], $dateFormat);
            $endDate = formatCvDate($project['end_date'], $dateFormat);
            $html .= '<div class="work-dates">' . htmlspecialchars($startDate . ' - ' . $endDate) . '</div>';

            if (!empty($project['description'])) {
                $html .= '<div class="mt-2">' . nl2br(htmlspecialchars($project['description'])) . '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    // Certifications
    if (in_array('certifications', $sections) && !empty($cvData['certifications'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Certifications</h2>';
        $html .= '<div class="section-content">';

        foreach ($cvData['certifications'] as $cert) {
            $html .= '<div class="mb-4">';
            $html .= '<div class="font-bold">' . htmlspecialchars($cert['name']) . '</div>';
            if (!empty($cert['issuing_organization'])) {
                $html .= '<div>' . htmlspecialchars($cert['issuing_organization']) . '</div>';
            }
            $certDate = formatCvDate($cert['date_obtained'], $dateFormat);
            $html .= '<div class="text-muted">' . htmlspecialchars($certDate) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    // Professional Memberships
    if (in_array('memberships', $sections) && !empty($cvData['memberships'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Professional Memberships</h2>';
        $html .= '<div class="section-content">';

        foreach ($cvData['memberships'] as $membership) {
            $html .= '<div class="mb-4">';
            $html .= '<div class="font-bold">' . htmlspecialchars($membership['organization_name']) . '</div>';
            if (!empty($membership['membership_type'])) {
                $html .= '<div>' . htmlspecialchars($membership['membership_type']) . '</div>';
            }
            $startDate = formatCvDate($membership['start_date'], $dateFormat);
            $endDate = $membership['current_member'] ? 'Present' : formatCvDate($membership['end_date'], $dateFormat);
            $html .= '<div class="text-muted">' . htmlspecialchars($startDate . ' - ' . $endDate) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    // Interests
    if (in_array('interests', $sections) && !empty($cvData['interests'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Interests & Activities</h2>';
        $html .= '<div class="section-content">';

        $interestNames = array_column($cvData['interests'], 'name');
        $html .= '<div>' . htmlspecialchars(implode(', ', $interestNames)) . '</div>';

        $html .= '</div></div>';
    }

    // Qualification Equivalence
    if (in_array('qualifications', $sections) && !empty($cvData['qualification_equivalence'])) {
        $html .= '<div class="cv-section">';
        $html .= '<h2 class="section-title">Professional Qualification Equivalence</h2>';
        $html .= '<div class="section-content">';

        foreach ($cvData['qualification_equivalence'] as $qual) {
            $html .= '<div class="mb-4">';
            $html .= '<div class="font-bold">Level ' . htmlspecialchars($qual['level']) . '</div>';
            if (!empty($qual['description'])) {
                $html .= '<div>' . nl2br(htmlspecialchars($qual['description'])) . '</div>';
            }

            if (!empty($qual['evidence'])) {
                $html .= '<ul class="responsibility-list mt-2">';
                foreach ($qual['evidence'] as $evidence) {
                    $html .= '<li>' . htmlspecialchars($evidence['description']) . '</li>';
                }
                $html .= '</ul>';
            }

            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    $html .= '</div>'; // cv-container
    $html .= '</body></html>';

    return $html;
}

/**
 * Format date helper
 */
function formatCvDate($date, $format = 'dd/mm/yyyy') {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;

    switch ($format) {
        case 'mm/dd/yyyy':
            return date('m/d/Y', $timestamp);
        case 'yyyy-mm-dd':
            return date('Y-m-d', $timestamp);
        case 'dd/mm/yyyy':
        default:
            return date('d/m/Y', $timestamp);
    }
}
