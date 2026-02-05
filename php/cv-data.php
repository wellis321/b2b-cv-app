<?php
/**
 * CV Data Loading Functions
 * Shared functions for loading CV data across different pages
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';

/**
 * Load all CV data for a user
 */
function loadCvData($userId) {
    $cvData = [
        'profile' => null,
        'professional_summary' => null,
        'work_experience' => [],
        'education' => [],
        'skills' => [],
        'projects' => [],
        'certifications' => [],
        'memberships' => [],
        'interests' => [],
        'qualification_equivalence' => []
    ];

    // Load profile
    $cvData['profile'] = db()->fetchOne(
        "SELECT * FROM profiles WHERE id = ?",
        [$userId]
    );

    // Load professional summary
    $summary = db()->fetchOne(
        "SELECT * FROM professional_summary WHERE profile_id = ?",
        [$userId]
    );

    if ($summary) {
        $cvData['professional_summary'] = $summary;
        if (isset($cvData['professional_summary']['description']) && $cvData['professional_summary']['description'] !== '') {
            $cvData['professional_summary']['description'] = decodeHtmlEntities($cvData['professional_summary']['description']);
        }
        // Load strengths
        $cvData['professional_summary']['strengths'] = db()->fetchAll(
            "SELECT * FROM professional_summary_strengths
             WHERE professional_summary_id = ?
             ORDER BY sort_order ASC",
            [$summary['id']]
        );
        foreach ($cvData['professional_summary']['strengths'] as &$st) {
            $st['strength'] = decodeHtmlEntities($st['strength'] ?? '');
        }
        unset($st);
    }

    // Load work experience
    $cvData['work_experience'] = db()->fetchAll(
        "SELECT * FROM work_experience
         WHERE profile_id = ?
         ORDER BY sort_order ASC, start_date DESC",
        [$userId]
    );

    // Load responsibilities for each work experience; decode position/company/description (may be stored with htmlspecialchars or multiple encoding)
    foreach ($cvData['work_experience'] as &$work) {
        $work['position'] = decodeHtmlEntities($work['position'] ?? '');
        $work['company_name'] = decodeHtmlEntities($work['company_name'] ?? '');
        if (isset($work['description']) && $work['description'] !== '') {
            $work['description'] = decodeHtmlEntities($work['description']);
        }
        $categories = db()->fetchAll(
            "SELECT * FROM responsibility_categories
             WHERE work_experience_id = ?
             ORDER BY sort_order ASC",
            [$work['id']]
        );

        foreach ($categories as &$category) {
            $category['name'] = decodeHtmlEntities($category['name'] ?? '');
            $category['items'] = db()->fetchAll(
                "SELECT * FROM responsibility_items
                 WHERE category_id = ?
                 ORDER BY sort_order ASC",
                [$category['id']]
            );
            foreach ($category['items'] as &$item) {
                $item['content'] = decodeHtmlEntities($item['content'] ?? '');
            }
            unset($item);
        }
        unset($category);
        $work['responsibility_categories'] = $categories;
    }
    unset($work, $category);

    // Load education (decode text fields)
    $cvData['education'] = db()->fetchAll(
        "SELECT * FROM education
         WHERE profile_id = ?
         ORDER BY start_date DESC",
        [$userId]
    );
    foreach ($cvData['education'] as &$edu) {
        $edu['institution'] = decodeHtmlEntities($edu['institution'] ?? '');
        $edu['degree'] = decodeHtmlEntities($edu['degree'] ?? '');
        $edu['field_of_study'] = decodeHtmlEntities($edu['field_of_study'] ?? '');
        if (isset($edu['description']) && $edu['description'] !== '') {
            $edu['description'] = decodeHtmlEntities($edu['description']);
        }
    }
    unset($edu);

    // Load skills (decode name/level/category in case they were stored with htmlspecialchars or multiple encoding)
    $cvData['skills'] = db()->fetchAll(
        "SELECT * FROM skills
         WHERE profile_id = ?
         ORDER BY category ASC, name ASC",
        [$userId]
    );
    foreach ($cvData['skills'] as &$s) {
        $s['name'] = decodeHtmlEntities($s['name'] ?? '');
        $s['level'] = isset($s['level']) && $s['level'] !== '' ? decodeHtmlEntities($s['level']) : $s['level'];
        $s['category'] = isset($s['category']) && $s['category'] !== '' ? decodeHtmlEntities($s['category']) : $s['category'];
    }
    unset($s);

    // Load projects (decode text fields)
    $cvData['projects'] = db()->fetchAll(
        "SELECT * FROM projects
         WHERE profile_id = ?
         ORDER BY start_date DESC",
        [$userId]
    );
    foreach ($cvData['projects'] as &$proj) {
        $proj['title'] = decodeHtmlEntities($proj['title'] ?? '');
        if (isset($proj['description']) && $proj['description'] !== '') {
            $proj['description'] = decodeHtmlEntities($proj['description']);
        }
        if (isset($proj['url']) && $proj['url'] !== '') {
            $proj['url'] = decodeHtmlEntities($proj['url']);
        }
    }
    unset($proj);

    // Load certifications (decode text fields)
    $cvData['certifications'] = db()->fetchAll(
        "SELECT * FROM certifications
         WHERE profile_id = ?
         ORDER BY date_obtained DESC",
        [$userId]
    );
    foreach ($cvData['certifications'] as &$cert) {
        $cert['name'] = decodeHtmlEntities($cert['name'] ?? '');
        $cert['issuer'] = decodeHtmlEntities($cert['issuer'] ?? '');
        if (isset($cert['description']) && $cert['description'] !== '') {
            $cert['description'] = decodeHtmlEntities($cert['description']);
        }
    }
    unset($cert);

    // Load memberships (decode text fields)
    $cvData['memberships'] = db()->fetchAll(
        "SELECT * FROM professional_memberships
         WHERE profile_id = ?
         ORDER BY start_date DESC",
        [$userId]
    );
    foreach ($cvData['memberships'] as &$mem) {
        $mem['organisation'] = decodeHtmlEntities($mem['organisation'] ?? '');
        $mem['role'] = isset($mem['role']) && $mem['role'] !== '' ? decodeHtmlEntities($mem['role']) : $mem['role'];
    }
    unset($mem);

    // Load interests (decode text fields)
    $cvData['interests'] = db()->fetchAll(
        "SELECT * FROM interests
         WHERE profile_id = ?
         ORDER BY name ASC",
        [$userId]
    );
    foreach ($cvData['interests'] as &$int) {
        $int['name'] = decodeHtmlEntities(trim($int['name'] ?? ''));
        if (isset($int['description']) && $int['description'] !== '') {
            $int['description'] = decodeHtmlEntities(trim($int['description']));
        }
    }
    unset($int);

    // Load qualification equivalence (decode level, description, and evidence content)
    $cvData['qualification_equivalence'] = db()->fetchAll(
        "SELECT * FROM professional_qualification_equivalence
         WHERE profile_id = ?
         ORDER BY level ASC",
        [$userId]
    );
    foreach ($cvData['qualification_equivalence'] as &$qual) {
        $qual['level'] = decodeHtmlEntities($qual['level'] ?? '');
        if (isset($qual['description']) && $qual['description'] !== '') {
            $qual['description'] = decodeHtmlEntities($qual['description']);
        }
        $qual['evidence'] = db()->fetchAll(
            "SELECT * FROM supporting_evidence
             WHERE qualification_equivalence_id = ?
             ORDER BY sort_order ASC",
            [$qual['id']]
        );
        foreach ($qual['evidence'] as &$ev) {
            $ev['content'] = decodeHtmlEntities($ev['content'] ?? '');
        }
        unset($ev);
    }
    unset($qual);

    return $cvData;
}

/**
 * Load CV data for PDF export with filtered skills based on user's template selection
 * 
 * @param string $userId User ID
 * @param string $templateId Template ID (optional, if not provided, returns all skills)
 * @return array CV data with filtered skills
 */
function loadCvDataForPdf($userId, $templateId = null) {
    // Load all CV data first
    $cvData = loadCvData($userId);
    
    // If template ID is provided, filter skills based on user's selection
    if (!empty($templateId)) {
        try {
            $selection = db()->fetchOne(
                "SELECT selected_skill_ids FROM user_template_skill_selections 
                 WHERE user_id = ? AND template_id = ?",
                [$userId, $templateId]
            );
            
            if ($selection && !empty($selection['selected_skill_ids'])) {
                $selectedSkillIds = json_decode($selection['selected_skill_ids'], true);
                if (is_array($selectedSkillIds) && !empty($selectedSkillIds)) {
                    // Filter skills to only include selected ones
                    $cvData['skills'] = array_filter($cvData['skills'], function($skill) use ($selectedSkillIds) {
                        return in_array($skill['id'], $selectedSkillIds);
                    });
                    // Re-index array to remove gaps
                    $cvData['skills'] = array_values($cvData['skills']);
                } else {
                    // Empty selection means no skills in PDF
                    $cvData['skills'] = [];
                }
            }
            // If no selection exists, show all skills (default behavior)
        } catch (Exception $e) {
            error_log("Error loading PDF skill selection: " . $e->getMessage());
            // On error, fall back to showing all skills
        }
    }
    
    return $cvData;
}
