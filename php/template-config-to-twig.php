<?php
/**
 * Template Configuration to Twig Converter
 * Converts visual builder JSON configuration to Twig template code
 */

require_once __DIR__ . '/template-config-schema.php';

/**
 * Convert template configuration to Twig template code
 * 
 * @param array $config Template configuration from visual builder
 * @return array ['html' => string, 'css' => string]
 */
function convertConfigToTwig($config) {
    // Validate config first
    $validation = validateTemplateConfig($config);
    if (!$validation['valid']) {
        throw new Exception('Invalid template configuration: ' . implode(', ', $validation['errors']));
    }
    
    // Get sorted sections
    $sections = $config['sections'];
    usort($sections, function($a, $b) {
        return $a['order'] <=> $b['order'];
    });
    
    // Filter to only enabled sections
    $enabledSections = array_filter($sections, function($section) {
        return $section['enabled'] ?? true;
    });
    
    // Build HTML
    $html = buildTemplateHtml($config, $enabledSections);
    
    // Build CSS
    $css = buildTemplateCss($config);
    
    return [
        'html' => $html,
        'css' => $css
    ];
}

/**
 * Build Twig HTML template from configuration
 * 
 * @param array $config Template configuration
 * @param array $sections Enabled sections in order
 * @return string Twig template HTML
 */
function buildTemplateHtml($config, $sections) {
    $layout = $config['layout'] ?? 'single-column';
    $styling = $config['styling'] ?? [];
    $sectionSettings = $config['sectionSettings'] ?? [];
    
    $html = '';
    
    // Add container based on layout
    if ($layout === 'two-column') {
        $html .= '<div class="grid grid-cols-2 gap-6 max-w-6xl mx-auto">';
        $html .= '<div class="col-span-1 space-y-6">';
        // Add all sections to first column for now (can be enhanced later with column assignment)
        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $sectionHtml = buildSectionHtml($sectionId, $sectionSettings[$sectionId] ?? [], $styling);
            $html .= $sectionHtml;
        }
        $html .= '</div>';
        $html .= '<div class="col-span-1 space-y-6">';
        // Second column - empty for now, can be populated with specific sections later
        $html .= '</div>';
        $html .= '</div>';
    } elseif ($layout === 'sidebar') {
        $html .= '<div class="grid grid-cols-3 gap-6 max-w-6xl mx-auto">';
        $html .= '<div class="col-span-1 bg-gray-50 p-6 space-y-6">';
        // Sidebar sections (profile, skills, etc.)
        $sidebarSections = ['profile', 'skills', 'certifications', 'memberships'];
        foreach ($sections as $section) {
            if (in_array($section['id'], $sidebarSections)) {
                $sectionId = $section['id'];
                $sectionHtml = buildSectionHtml($sectionId, $sectionSettings[$sectionId] ?? [], $styling);
                $html .= $sectionHtml;
            }
        }
        $html .= '</div>';
        $html .= '<div class="col-span-2 space-y-6">';
        // Main content sections
        foreach ($sections as $section) {
            if (!in_array($section['id'], $sidebarSections)) {
                $sectionId = $section['id'];
                $sectionHtml = buildSectionHtml($sectionId, $sectionSettings[$sectionId] ?? [], $styling);
                $html .= $sectionHtml;
            }
        }
        $html .= '</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="max-w-4xl mx-auto space-y-6">';
        // Add each section
        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $sectionHtml = buildSectionHtml($sectionId, $sectionSettings[$sectionId] ?? [], $styling);
            $html .= $sectionHtml;
        }
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Build HTML for a specific section
 * 
 * @param string $sectionId Section identifier
 * @param array $settings Section-specific settings
 * @param array $styling Global styling
 * @return string Section HTML in Twig syntax
 */
function buildSectionHtml($sectionId, $settings, $styling) {
    $colors = $styling['colors'] ?? [];
    $spacing = $styling['spacing'] ?? [];
    $sectionSpacing = $spacing['section'] ?? 24;
    
    $html = '';
    
    switch ($sectionId) {
        case 'profile':
            $html = buildProfileSection($settings, $colors, $sectionSpacing);
            break;
        case 'professional-summary':
            $html = buildProfessionalSummarySection($settings, $colors, $sectionSpacing);
            break;
        case 'work-experience':
            $html = buildWorkExperienceSection($settings, $colors, $sectionSpacing);
            break;
        case 'education':
            $html = buildEducationSection($settings, $colors, $sectionSpacing);
            break;
        case 'skills':
            $html = buildSkillsSection($settings, $colors, $sectionSpacing);
            break;
        case 'projects':
            $html = buildProjectsSection($settings, $colors, $sectionSpacing);
            break;
        case 'certifications':
            $html = buildCertificationsSection($settings, $colors, $sectionSpacing);
            break;
        case 'qualification-equivalence':
            $html = buildQualificationEquivalenceSection($settings, $colors, $sectionSpacing);
            break;
        case 'memberships':
            $html = buildMembershipsSection($settings, $colors, $sectionSpacing);
            break;
        case 'interests':
            $html = buildInterestsSection($settings, $colors, $sectionSpacing);
            break;
        default:
            $html = '';
    }
    
    return $html;
}

/**
 * Build Profile section
 */
function buildProfileSection($settings, $colors, $spacing) {
    $showPhoto = $settings['showPhoto'] ?? true;
    $photoPosition = $settings['photoPosition'] ?? 'right';
    $showContact = $settings['showContact'] ?? true;
    $showLocation = $settings['showLocation'] ?? true;
    $showLinkedIn = $settings['showLinkedIn'] ?? true;
    
    $headerColor = $colors['header'] ?? '#1f2937';
    
    $html = '<section class="mb-' . ($spacing / 4) . '" style="background-color: ' . htmlspecialchars($headerColor) . '; color: white; padding: 2rem;">';
    
    if ($showPhoto && $photoPosition === 'right') {
        $html .= '<div class="flex items-center justify-between">';
        $html .= '<div class="flex-1">';
    }
    
    $html .= '<h1 class="text-3xl font-bold mb-2">{{ profile.full_name|escape }}</h1>';
    
    if ($showContact) {
        $html .= '<div class="flex flex-wrap gap-4 mt-2">';
        $html .= '{% if profile.email is defined and profile.email|length > 0 %}';
        $html .= '<span class="text-sm">{{ profile.email|escape }}</span>';
        $html .= '{% endif %}';
        
        if ($showLocation) {
            $html .= '{% if profile.location is defined and profile.location|length > 0 %}';
            $html .= '<span class="text-sm">{{ profile.location|escape }}</span>';
            $html .= '{% endif %}';
        }
        
        $html .= '{% if profile.phone is defined and profile.phone|length > 0 %}';
        $html .= '<span class="text-sm">{{ profile.phone|escape }}</span>';
        $html .= '{% endif %}';
        
        if ($showLinkedIn) {
            $html .= '{% if profile.linkedin_url is defined and profile.linkedin_url|length > 0 %}';
            $html .= '<a href="{{ profile.linkedin_url|escape }}" class="text-sm underline">LinkedIn</a>';
            $html .= '{% endif %}';
        }
        
        $html .= '</div>';
    }
    
    if ($showPhoto && $photoPosition === 'right') {
        $html .= '</div>';
        $html .= '<div class="ml-4">';
        $html .= '{% if profile.photo_url is defined and profile.photo_url|length > 0 %}';
        $html .= '<img src="{{ profile.photo_url|escape }}" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover border-2 border-white">';
        $html .= '{% endif %}';
        $html .= '</div>';
        $html .= '</div>';
    } elseif ($showPhoto) {
        $html .= '<div class="mt-4">';
        $html .= '{% if profile.photo_url is defined and profile.photo_url|length > 0 %}';
        $html .= '<img src="{{ profile.photo_url|escape }}" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover border-2 border-white">';
        $html .= '{% endif %}';
        $html .= '</div>';
    }
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Professional Summary section
 */
function buildProfessionalSummarySection($settings, $colors, $spacing) {
    $showStrengths = $settings['showStrengths'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Professional Summary</h2>';
    
    $html .= '{% if cvData.professional_summary.description is defined and cvData.professional_summary.description|length > 0 %}';
    $html .= '<p class="mb-4" style="color: ' . htmlspecialchars($textColor) . ';">{{ cvData.professional_summary.description|escape }}</p>';
    $html .= '{% endif %}';
    
    if ($showStrengths) {
        $html .= '{% if cvData.professional_summary.strengths is defined and cvData.professional_summary.strengths|length > 0 %}';
        $html .= '<ul class="list-disc list-inside space-y-1">';
        $html .= '{% for strength in cvData.professional_summary.strengths %}';
        $html .= '<li style="color: ' . htmlspecialchars($textColor) . ';">{{ strength.name|escape }}</li>';
        $html .= '{% endfor %}';
        $html .= '</ul>';
        $html .= '{% endif %}';
    }
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Work Experience section
 */
function buildWorkExperienceSection($settings, $colors, $spacing) {
    $showDates = $settings['showDates'] ?? true;
    $showDescription = $settings['showDescription'] ?? true;
    $showResponsibilities = $settings['showResponsibilities'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    $mutedColor = $colors['muted'] ?? '#6b7280';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Work Experience</h2>';
    
    $html .= '{% if cvData.work_experience is defined and cvData.work_experience|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for work in cvData.work_experience %}';
    $html .= '<div class="border-l-4 pl-4" style="border-color: ' . htmlspecialchars($accentColor) . ';">';
    $html .= '<h3 class="text-xl font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';">{{ work.position|escape }}</h3>';
    $html .= '<p class="font-medium mb-2" style="color: ' . htmlspecialchars($mutedColor) . ';">{{ work.company_name|escape }}</p>';
    
    if ($showDates) {
        $html .= '<p class="text-sm mb-2" style="color: ' . htmlspecialchars($mutedColor) . ';">';
        $html .= '{{ formatCvDate(work.start_date) }}';
        $html .= ' - ';
        $html .= '{% if work.end_date is defined and work.end_date|length > 0 %}';
        $html .= '{{ formatCvDate(work.end_date) }}';
        $html .= '{% else %}';
        $html .= 'Present';
        $html .= '{% endif %}';
        $html .= '</p>';
    }
    
    if ($showDescription) {
        $html .= '{% if work.description is defined and work.description|length > 0 %}';
        $html .= '<p class="mb-2" style="color: ' . htmlspecialchars($textColor) . ';">{{ work.description|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    if ($showResponsibilities) {
        $html .= '{% if work.responsibility_categories is defined and work.responsibility_categories|length > 0 %}';
        $html .= '<div class="mt-2 space-y-2">';
        $html .= '{% for cat in work.responsibility_categories %}';
        $html .= '<div>';
        $html .= '<h4 class="font-semibold text-sm" style="color: ' . htmlspecialchars($textColor) . ';">{{ cat.name|escape }}</h4>';
        $html .= '{% if cat.items is defined and cat.items|length > 0 %}';
        $html .= '<ul class="list-disc list-inside ml-2 space-y-1">';
        $html .= '{% for item in cat.items %}';
        $html .= '<li class="text-sm" style="color: ' . htmlspecialchars($textColor) . ';">{{ item.content|escape }}</li>';
        $html .= '{% endfor %}';
        $html .= '</ul>';
        $html .= '{% endif %}';
        $html .= '</div>';
        $html .= '{% endfor %}';
        $html .= '</div>';
        $html .= '{% endif %}';
    }
    
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Education section
 */
function buildEducationSection($settings, $colors, $spacing) {
    $showDates = $settings['showDates'] ?? true;
    $showDescription = $settings['showDescription'] ?? true;
    $showFieldOfStudy = $settings['showFieldOfStudy'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    $mutedColor = $colors['muted'] ?? '#6b7280';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Education</h2>';
    
    $html .= '{% if cvData.education is defined and cvData.education|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for edu in cvData.education %}';
    $html .= '<div>';
    $html .= '<p class="text-xl font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';"><span style="color: ' . htmlspecialchars($mutedColor) . ';">Qual:</span> {{ edu.degree|escape }}</p>';
    $html .= '<p class="font-medium mb-1" style="color: ' . htmlspecialchars($textColor) . ';"><span style="color: ' . htmlspecialchars($mutedColor) . ';">Institution:</span> {{ edu.institution|escape }}</p>';
    
    if ($showFieldOfStudy) {
        $html .= '{% if edu.field_of_study is defined and edu.field_of_study|length > 0 %}';
        $html .= '<p class="text-sm mb-1" style="color: ' . htmlspecialchars($textColor) . ';"><span style="color: ' . htmlspecialchars($mutedColor) . ';">Subject:</span> {{ edu.field_of_study|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    if ($showDates) {
        $html .= '{% if not (edu.hide_date|default(0)) %}';
        $html .= '<p class="text-sm mb-2" style="color: ' . htmlspecialchars($mutedColor) . ';">';
        $html .= '{{ formatCvDate(edu.start_date) }}';
        $html .= '{% if edu.end_date is defined and edu.end_date|length > 0 %}';
        $html .= ' - {{ formatCvDate(edu.end_date) }}';
        $html .= '{% endif %}';
        $html .= '</p>';
        $html .= '{% endif %}';
    }
    
    if ($showDescription) {
        $html .= '{% if edu.description is defined and edu.description|length > 0 %}';
        $html .= '<p class="text-sm" style="color: ' . htmlspecialchars($textColor) . ';">{{ edu.description|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Skills section
 */
function buildSkillsSection($settings, $colors, $spacing) {
    $groupByCategory = $settings['groupByCategory'] ?? false;
    $showLevel = $settings['showLevel'] ?? false;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Skills</h2>';
    
    $html .= '{% if cvData.skills is defined and cvData.skills|length > 0 %}';
    
    if ($groupByCategory) {
        $html .= '<div class="space-y-4">';
        // Group by category logic would go here
        $html .= '{% for skill in cvData.skills %}';
        $html .= '<span class="inline-block bg-gray-100 px-3 py-1 rounded-full mr-2 mb-2" style="color: ' . htmlspecialchars($textColor) . ';">';
        $html .= '{{ skill.name|escape }}';
        if ($showLevel) {
            $html .= '{% if skill.level is defined and skill.level|length > 0 %}';
            $html .= ' <span class="text-xs">({{ skill.level|escape }})</span>';
            $html .= '{% endif %}';
        }
        $html .= '</span>';
        $html .= '{% endfor %}';
        $html .= '</div>';
    } else {
        $html .= '<div class="flex flex-wrap gap-2">';
        $html .= '{% for skill in cvData.skills %}';
        $html .= '<span class="inline-block bg-gray-100 px-3 py-1 rounded-full" style="color: ' . htmlspecialchars($textColor) . ';">{{ skill.name|escape }}</span>';
        $html .= '{% endfor %}';
        $html .= '</div>';
    }
    
    $html .= '{% endif %}';
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Projects section
 */
function buildProjectsSection($settings, $colors, $spacing) {
    $showDates = $settings['showDates'] ?? true;
    $showDescription = $settings['showDescription'] ?? true;
    $showUrl = $settings['showUrl'] ?? true;
    $showImage = $settings['showImage'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    $mutedColor = $colors['muted'] ?? '#6b7280';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Projects</h2>';
    
    $html .= '{% if cvData.projects is defined and cvData.projects|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for project in cvData.projects %}';
    $html .= '<div>';
    $html .= '<h3 class="text-xl font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';">{{ project.title|escape }}</h3>';
    
    if ($showDates) {
        $html .= '<p class="text-sm mb-2" style="color: ' . htmlspecialchars($mutedColor) . ';">';
        $html .= '{{ formatCvDate(project.start_date) }}';
        $html .= '{% if project.end_date is defined and project.end_date|length > 0 %}';
        $html .= ' - {{ formatCvDate(project.end_date) }}';
        $html .= '{% endif %}';
        $html .= '</p>';
    }
    
    if ($showDescription) {
        $html .= '{% if project.description is defined and project.description|length > 0 %}';
        $html .= '<p class="mb-2" style="color: ' . htmlspecialchars($textColor) . ';">{{ project.description|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    if ($showUrl) {
        $html .= '{% if project.url is defined and project.url|length > 0 %}';
        $html .= '<a href="{{ project.url|escape }}" class="text-sm underline" style="color: ' . htmlspecialchars($accentColor) . ';">View Project</a>';
        $html .= '{% endif %}';
    }
    
    if ($showImage) {
        $html .= '{% if project.image_url is defined and project.image_url|length > 0 %}';
        $html .= '<img src="{{ project.image_url|escape }}" alt="{{ project.title|escape }}" class="mt-2 rounded-lg max-w-md">';
        $html .= '{% endif %}';
    }
    
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Certifications section
 */
function buildCertificationsSection($settings, $colors, $spacing) {
    $showDates = $settings['showDates'] ?? true;
    $showIssuer = $settings['showIssuer'] ?? true;
    $showExpiry = $settings['showExpiry'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    $mutedColor = $colors['muted'] ?? '#6b7280';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Certifications</h2>';
    
    $html .= '{% if cvData.certifications is defined and cvData.certifications|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for cert in cvData.certifications %}';
    $html .= '<div>';
    $html .= '<h3 class="text-xl font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';">{{ cert.name|escape }}</h3>';
    
    if ($showIssuer) {
        $html .= '{% if cert.issuer is defined and cert.issuer|length > 0 %}';
        $html .= '<p class="font-medium mb-1" style="color: ' . htmlspecialchars($mutedColor) . ';">{{ cert.issuer|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    if ($showDates) {
        $html .= '{% if cert.date_obtained is defined and cert.date_obtained|length > 0 %}';
        $html .= '<p class="text-sm mb-1" style="color: ' . htmlspecialchars($mutedColor) . ';">Obtained: {{ formatCvDate(cert.date_obtained) }}</p>';
        $html .= '{% endif %}';
    }
    
    if ($showExpiry) {
        $html .= '{% if cert.expiry_date is defined and cert.expiry_date|length > 0 %}';
        $html .= '<p class="text-sm" style="color: ' . htmlspecialchars($mutedColor) . ';">Expires: {{ formatCvDate(cert.expiry_date) }}</p>';
        $html .= '{% endif %}';
    }
    
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Qualification Equivalence section
 */
function buildQualificationEquivalenceSection($settings, $colors, $spacing) {
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    $mutedColor = $colors['muted'] ?? '#6b7280';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Professional Qualification Equivalence</h2>';
    
    $html .= '{% if cvData.qualification_equivalence is defined and cvData.qualification_equivalence|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for qual in cvData.qualification_equivalence %}';
    $html .= '<div>';
    $html .= '<h3 class="text-xl font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';">{{ qual.qualification|escape }}</h3>';
    $html .= '<p class="text-sm" style="color: ' . htmlspecialchars($mutedColor) . ';">{{ qual.equivalent_to|escape }}</p>';
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Memberships section
 */
function buildMembershipsSection($settings, $colors, $spacing) {
    $showDates = $settings['showDates'] ?? true;
    $showOrganisation = $settings['showOrganisation'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    $mutedColor = $colors['muted'] ?? '#6b7280';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Professional Memberships</h2>';
    
    $html .= '{% if cvData.memberships is defined and cvData.memberships|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for mem in cvData.memberships %}';
    $html .= '<div>';
    $html .= '<h3 class="text-xl font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';">{{ mem.name|escape }}</h3>';
    
    if ($showOrganisation) {
        $html .= '{% if mem.organisation is defined and mem.organisation|length > 0 %}';
        $html .= '<p class="font-medium mb-1" style="color: ' . htmlspecialchars($mutedColor) . ';">{{ mem.organisation|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    if ($showDates) {
        $html .= '{% if mem.start_date is defined and mem.start_date|length > 0 %}';
        $html .= '<p class="text-sm" style="color: ' . htmlspecialchars($mutedColor) . ';">Member since: {{ formatCvDate(mem.start_date) }}</p>';
        $html .= '{% endif %}';
    }
    
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build Interests section
 */
function buildInterestsSection($settings, $colors, $spacing) {
    $showDescription = $settings['showDescription'] ?? true;
    $accentColor = $colors['accent'] ?? '#2563eb';
    $textColor = $colors['text'] ?? '#374151';
    
    $html = '<section class="mb-' . ($spacing / 4) . '">';
    $html .= '<h2 class="text-2xl font-bold mb-4" style="color: ' . htmlspecialchars($accentColor) . ';">Interests & Activities</h2>';
    
    $html .= '{% if cvData.interests is defined and cvData.interests|length > 0 %}';
    $html .= '<div class="space-y-' . ($spacing / 3) . '">';
    $html .= '{% for interest in cvData.interests %}';
    $html .= '<div>';
    $html .= '<h3 class="text-lg font-semibold mb-1" style="color: ' . htmlspecialchars($textColor) . ';">{{ interest.name|escape }}</h3>';
    
    if ($showDescription) {
        $html .= '{% if interest.description is defined and interest.description|length > 0 %}';
        $html .= '<p class="text-sm" style="color: ' . htmlspecialchars($textColor) . ';">{{ interest.description|escape }}</p>';
        $html .= '{% endif %}';
    }
    
    $html .= '</div>';
    $html .= '{% endfor %}';
    $html .= '</div>';
    $html .= '{% endif %}';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * Build CSS from configuration
 * 
 * @param array $config Template configuration
 * @return string CSS styles
 */
function buildTemplateCss($config) {
    $styling = $config['styling'] ?? [];
    $colors = $styling['colors'] ?? [];
    $fonts = $styling['fonts'] ?? [];
    
    $css = '';
    
    // Add custom CSS variables if needed
    if (!empty($colors) || !empty($fonts)) {
        $css .= ':root {';
        foreach ($colors as $key => $value) {
            $css .= '  --color-' . $key . ': ' . $value . ';';
        }
        foreach ($fonts as $key => $value) {
            $css .= '  --font-' . $key . ': ' . $value . ';';
        }
        $css .= '}';
    }
    
    // Add print styles
    $css .= '@media print {';
    $css .= '  .no-print { display: none !important; }';
    $css .= '  .page-break { page-break-after: always; }';
    $css .= '}';
    
    return $css;
}

