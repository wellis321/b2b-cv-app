<?php
/**
 * Template Configuration Schema
 * Defines the structure for visual template builder configuration
 */

/**
 * Get default template configuration
 * 
 * @return array Default template configuration
 */
function getDefaultTemplateConfig() {
    return [
        'layout' => 'single-column',
        'sections' => [
            ['id' => 'profile', 'enabled' => true, 'order' => 0],
            ['id' => 'professional-summary', 'enabled' => true, 'order' => 1],
            ['id' => 'work-experience', 'enabled' => true, 'order' => 2],
            ['id' => 'education', 'enabled' => true, 'order' => 3],
            ['id' => 'skills', 'enabled' => true, 'order' => 4],
            ['id' => 'projects', 'enabled' => true, 'order' => 5],
            ['id' => 'certifications', 'enabled' => true, 'order' => 6],
            ['id' => 'qualification-equivalence', 'enabled' => false, 'order' => 7],
            ['id' => 'memberships', 'enabled' => true, 'order' => 8],
            ['id' => 'interests', 'enabled' => true, 'order' => 9],
        ],
        'styling' => [
            'colors' => [
                'header' => '#1f2937',
                'accent' => '#2563eb',
                'text' => '#374151',
                'muted' => '#6b7280',
                'background' => '#ffffff',
                'border' => '#e5e7eb',
            ],
            'fonts' => [
                'heading' => 'Arial, sans-serif',
                'body' => 'Arial, sans-serif',
            ],
            'spacing' => [
                'section' => 24,
                'item' => 12,
                'paragraph' => 8,
            ],
        ],
        'sectionSettings' => [
            'profile' => [
                'showPhoto' => true,
                'photoPosition' => 'right',
                'showContact' => true,
                'showLocation' => true,
                'showLinkedIn' => true,
            ],
            'professional-summary' => [
                'showStrengths' => true,
            ],
            'work-experience' => [
                'showDates' => true,
                'showDescription' => true,
                'showResponsibilities' => true,
                'dateFormat' => 'MM/YYYY',
            ],
            'education' => [
                'showDates' => true,
                'showDescription' => true,
                'showFieldOfStudy' => true,
            ],
            'skills' => [
                'groupByCategory' => false,
                'showLevel' => false,
                'maxSkills' => null, // Optional: maximum number of skills to display in PDF
                'skillSectionTitle' => null, // Optional: custom section title (e.g., "Areas of Expertise")
                'skillLayout' => 'list', // 'list' | 'grid' | 'columns'
                'skillColumns' => null, // Optional: number of columns for grid/column layouts (e.g., 4)
                'skillRows' => null, // Optional: number of rows for grid layouts (e.g., 3)
            ],
            'projects' => [
                'showDates' => true,
                'showDescription' => true,
                'showUrl' => true,
                'showImage' => true,
            ],
            'certifications' => [
                'showDates' => true,
                'showIssuer' => true,
                'showExpiry' => true,
            ],
            'memberships' => [
                'showDates' => true,
                'showOrganisation' => true,
            ],
            'interests' => [
                'showDescription' => true,
            ],
        ],
    ];
}

/**
 * Get available CV sections
 * 
 * @return array List of available sections with metadata
 */
function getAvailableCvSections() {
    return [
        [
            'id' => 'profile',
            'name' => 'Personal Profile',
            'description' => 'Name, contact information, and photo',
            'icon' => 'user',
        ],
        [
            'id' => 'professional-summary',
            'name' => 'Professional Summary',
            'description' => 'Overview and key strengths',
            'icon' => 'document-text',
        ],
        [
            'id' => 'work-experience',
            'name' => 'Work Experience',
            'description' => 'Employment history and responsibilities',
            'icon' => 'briefcase',
        ],
        [
            'id' => 'education',
            'name' => 'Education',
            'description' => 'Educational qualifications',
            'icon' => 'academic-cap',
        ],
        [
            'id' => 'skills',
            'name' => 'Skills',
            'description' => 'Professional skills and competencies',
            'icon' => 'star',
        ],
        [
            'id' => 'projects',
            'name' => 'Projects',
            'description' => 'Notable projects and achievements',
            'icon' => 'folder',
        ],
        [
            'id' => 'certifications',
            'name' => 'Certifications',
            'description' => 'Professional certifications',
            'icon' => 'badge-check',
        ],
        [
            'id' => 'qualification-equivalence',
            'name' => 'Qualification Equivalence',
            'description' => 'International qualification alignment',
            'icon' => 'globe',
        ],
        [
            'id' => 'memberships',
            'name' => 'Professional Memberships',
            'description' => 'Professional organisations',
            'icon' => 'user-group',
        ],
        [
            'id' => 'interests',
            'name' => 'Interests & Activities',
            'description' => 'Hobbies and personal interests',
            'icon' => 'heart',
        ],
    ];
}

/**
 * Validate template configuration
 * 
 * @param array $config Template configuration
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateTemplateConfig($config) {
    $errors = [];
    
    // Validate layout
    $validLayouts = ['single-column', 'two-column', 'sidebar'];
    if (!isset($config['layout']) || !in_array($config['layout'], $validLayouts)) {
        $errors[] = 'Invalid layout. Must be one of: ' . implode(', ', $validLayouts);
    }
    
    // Validate sections
    if (!isset($config['sections']) || !is_array($config['sections'])) {
        $errors[] = 'Sections must be an array';
    } else {
        $availableSectionIds = array_column(getAvailableCvSections(), 'id');
        foreach ($config['sections'] as $section) {
            if (!isset($section['id']) || !in_array($section['id'], $availableSectionIds)) {
                $errors[] = 'Invalid section ID: ' . ($section['id'] ?? 'unknown');
            }
            if (!isset($section['order']) || !is_numeric($section['order'])) {
                $errors[] = 'Section order must be numeric';
            }
        }
    }
    
    // Validate styling
    if (isset($config['styling'])) {
        if (isset($config['styling']['colors'])) {
            $requiredColors = ['header', 'accent', 'text', 'background'];
            foreach ($requiredColors as $color) {
                if (!isset($config['styling']['colors'][$color])) {
                    $errors[] = "Missing required color: {$color}";
                } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $config['styling']['colors'][$color])) {
                    $errors[] = "Invalid color format for {$color}. Must be hex color (e.g., #1f2937)";
                }
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}


