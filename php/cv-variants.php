<?php
/**
 * CV Variants Management Functions
 * Handle creating, loading, saving, and managing CV variants (job-specific CVs)
 */

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/cv-data.php';

/**
 * Create a new CV variant
 */
function createCvVariant($userId, $sourceVariantId = null, $variantName = 'Untitled Variant', $jobApplicationId = null) {
    if (!$userId) {
        return ['success' => false, 'error' => 'User ID required'];
    }
    
    try {
        $variantId = generateUuid();
        
        // If no source variant, check if user has a master variant
        if (!$sourceVariantId) {
            $masterVariant = db()->fetchOne(
                "SELECT id FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
                [$userId]
            );
            $sourceVariantId = $masterVariant['id'] ?? null;
        }
        
        // Create variant record
        db()->insert('cv_variants', [
            'id' => $variantId,
            'user_id' => $userId,
            'job_application_id' => $jobApplicationId,
            'variant_name' => sanitizeInput($variantName),
            'is_master' => 0, // Use 0 instead of false for MySQL
            'created_from_variant_id' => $sourceVariantId,
            'ai_generated' => 0, // Use 0 instead of false for MySQL
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // If source variant exists, copy its data
        if ($sourceVariantId) {
            copyCvVariant($sourceVariantId, $variantId);
        } else {
            // No source variant - copy from master CV (profile_id)
            copyMasterCvToVariant($userId, $variantId);
        }
        
        return ['success' => true, 'variant_id' => $variantId];
    } catch (Exception $e) {
        error_log("Error creating CV variant: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to create CV variant'];
    }
}

/**
 * Get or create master CV variant for a user
 */
function getOrCreateMasterVariant($userId) {
    $master = db()->fetchOne(
        "SELECT * FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
        [$userId]
    );
    
    if ($master) {
        return $master['id'];
    }
    
    // Create master variant
    try {
        $variantId = generateUuid();
        
        // Check if insert will succeed by checking user exists
        try {
            $userExists = db()->fetchOne("SELECT id FROM profiles WHERE id = ?", [$userId]);
            if (!$userExists) {
                error_log("User does not exist in profiles table: " . $userId);
                return null;
            }
            error_log("User exists: " . $userId . " - proceeding with master variant creation");
        } catch (Exception $e) {
            error_log("Error checking if user exists: " . $e->getMessage());
            // Continue anyway - the insert will fail if user doesn't exist
        }
        
        try {
            $result = db()->insert('cv_variants', [
                'id' => $variantId,
                'user_id' => $userId,
                'job_application_id' => null,
                'variant_name' => 'Master CV',
                'is_master' => 1, // Use 1 instead of true for MySQL
                'created_from_variant_id' => null,
                'ai_generated' => 0, // Use 0 instead of false for MySQL
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("PDO Exception creating master variant: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            // Check if it was created by another process or duplicate key
            $existing = db()->fetchOne(
                "SELECT id FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
                [$userId]
            );
            if ($existing) {
                error_log("Found existing master variant after PDO exception: " . $existing['id']);
                return $existing['id'];
            }
            // Re-throw to be caught by outer catch
            throw $e;
        }
        
        // insert() returns lastInsertId, but for UUID primary keys it returns 0
        // So we check if the record was actually created
        $created = db()->fetchOne(
            "SELECT id FROM cv_variants WHERE id = ?",
            [$variantId]
        );
        
        if (!$created) {
            error_log("Database insert appeared to succeed but record not found. Variant ID: " . $variantId);
            // Check if it was created by another process
            $existing = db()->fetchOne(
                "SELECT id FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
                [$userId]
            );
            if ($existing) {
                error_log("Found existing master variant: " . $existing['id']);
                return $existing['id'];
            }
            throw new Exception("Database insert failed - record not found after insert");
        }
        
        // Copy master CV data to variant (this may fail if user has no CV data yet, which is OK)
        try {
            copyMasterCvToVariant($userId, $variantId);
        } catch (Exception $e) {
            // Log but don't fail - variant can exist without data
            error_log("Warning: Could not copy master CV data to variant: " . $e->getMessage());
        }
        
        return $variantId;
    } catch (PDOException $e) {
        error_log("PDO Exception in getOrCreateMasterVariant: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Error Info: " . print_r($e->errorInfo ?? [], true));
        
        // Try to get existing variant in case it was created by another process
        try {
            $existing = db()->fetchOne(
                "SELECT id FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
                [$userId]
            );
            if ($existing) {
                error_log("Found existing master variant after PDO exception: " . $existing['id']);
                return $existing['id'];
            }
        } catch (Exception $e2) {
            error_log("Error checking for existing variant: " . $e2->getMessage());
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Exception creating master variant: " . $e->getMessage());
        error_log("Exception class: " . get_class($e));
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Try to get existing variant in case it was created by another process
        try {
            $existing = db()->fetchOne(
                "SELECT id FROM cv_variants WHERE user_id = ? AND is_master = TRUE",
                [$userId]
            );
            if ($existing) {
                error_log("Found existing master variant after exception: " . $existing['id']);
                return $existing['id'];
            }
        } catch (Exception $e2) {
            error_log("Error checking for existing variant: " . $e2->getMessage());
        }
        
        return null;
    }
}

/**
 * Get a CV variant by ID
 */
function getCvVariant($variantId, $userId = null) {
    if ($userId) {
        $variant = db()->fetchOne(
            "SELECT * FROM cv_variants WHERE id = ? AND user_id = ?",
            [$variantId, $userId]
        );
    } else {
        $variant = db()->fetchOne(
            "SELECT * FROM cv_variants WHERE id = ?",
            [$variantId]
        );
    }
    
    return $variant ?: null;
}

/**
 * Get all CV variants for a user
 */
/**
 * Suggest a unique variant name based on a base name
 * If the base name exists, appends a number (e.g., "AI-Generated CV (2)")
 */
function suggestUniqueVariantName($userId, $baseName = 'AI-Generated CV') {
    // Check if base name exists
    $existing = db()->fetchOne(
        "SELECT variant_name FROM cv_variants WHERE user_id = ? AND variant_name = ?",
        [$userId, $baseName]
    );
    
    if (!$existing) {
        // Base name doesn't exist, return it
        return $baseName;
    }
    
    // Base name exists, find next available number
    $counter = 2;
    while (true) {
        $suggestedName = $baseName . ' (' . $counter . ')';
        $existing = db()->fetchOne(
            "SELECT variant_name FROM cv_variants WHERE user_id = ? AND variant_name = ?",
            [$userId, $suggestedName]
        );
        
        if (!$existing) {
            // Found a unique name
            return $suggestedName;
        }
        
        $counter++;
        
        // Safety limit to prevent infinite loop
        if ($counter > 1000) {
            // Fallback to timestamp-based name
            return $baseName . ' (' . date('Y-m-d H:i:s') . ')';
        }
    }
}

function getUserCvVariants($userId) {
    return db()->fetchAll(
        "SELECT cv.*, 
                ja.company_name, 
                ja.job_title,
                ja.id as job_application_id
         FROM cv_variants cv
         LEFT JOIN job_applications ja ON cv.job_application_id = ja.id
         WHERE cv.user_id = ?
         ORDER BY cv.is_master DESC, cv.created_at DESC",
        [$userId]
    );
}

/**
 * Load all CV data for a variant
 */
function loadCvVariantData($variantId) {
    $cvData = [
        'variant' => null,
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
    
    // Load variant metadata
    $cvData['variant'] = db()->fetchOne(
        "SELECT * FROM cv_variants WHERE id = ?",
        [$variantId]
    );
    
    if (!$cvData['variant']) {
        return null;
    }
    
    // Load professional summary
    $summary = db()->fetchOne(
        "SELECT * FROM cv_variant_professional_summary WHERE cv_variant_id = ?",
        [$variantId]
    );
    
    if ($summary) {
        $cvData['professional_summary'] = $summary;
        
        // Load strengths
        $cvData['professional_summary']['strengths'] = db()->fetchAll(
            "SELECT * FROM cv_variant_professional_summary_strengths
             WHERE professional_summary_id = ?
             ORDER BY sort_order ASC",
            [$summary['id']]
        );
    }
    
    // Load work experience
    $cvData['work_experience'] = db()->fetchAll(
        "SELECT * FROM cv_variant_work_experience
         WHERE cv_variant_id = ?
         ORDER BY sort_order ASC, start_date DESC",
        [$variantId]
    );
    
    // Load responsibilities for each work experience
    foreach ($cvData['work_experience'] as &$work) {
        $categories = db()->fetchAll(
            "SELECT * FROM cv_variant_responsibility_categories
             WHERE work_experience_id = ?
             ORDER BY sort_order ASC",
            [$work['id']]
        );
        
        foreach ($categories as &$category) {
            $category['items'] = db()->fetchAll(
                "SELECT * FROM cv_variant_responsibility_items
                 WHERE category_id = ?
                 ORDER BY sort_order ASC",
                [$category['id']]
            );
        }
        
        $work['responsibility_categories'] = $categories;
    }
    unset($work, $category);
    
    // Load education
    $cvData['education'] = db()->fetchAll(
        "SELECT * FROM cv_variant_education
         WHERE cv_variant_id = ?
         ORDER BY start_date DESC",
        [$variantId]
    );
    
    // Load skills (decode name/level/category in case they were stored with htmlspecialchars)
    $cvData['skills'] = db()->fetchAll(
        "SELECT * FROM cv_variant_skills
         WHERE cv_variant_id = ?
         ORDER BY category ASC, name ASC",
        [$variantId]
    );
    foreach ($cvData['skills'] as &$s) {
        $s['name'] = html_entity_decode((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $s['level'] = isset($s['level']) && $s['level'] !== '' ? html_entity_decode((string) $s['level'], ENT_QUOTES, 'UTF-8') : $s['level'];
        $s['category'] = isset($s['category']) && $s['category'] !== '' ? html_entity_decode((string) $s['category'], ENT_QUOTES, 'UTF-8') : $s['category'];
    }
    unset($s);

    // Load projects
    $cvData['projects'] = db()->fetchAll(
        "SELECT * FROM cv_variant_projects
         WHERE cv_variant_id = ?
         ORDER BY start_date DESC",
        [$variantId]
    );
    
    // Load certifications
    $cvData['certifications'] = db()->fetchAll(
        "SELECT * FROM cv_variant_certifications
         WHERE cv_variant_id = ?
         ORDER BY date_obtained DESC",
        [$variantId]
    );
    
    // Load memberships
    $cvData['memberships'] = db()->fetchAll(
        "SELECT * FROM cv_variant_memberships
         WHERE cv_variant_id = ?
         ORDER BY start_date DESC",
        [$variantId]
    );
    
    // Load interests
    $cvData['interests'] = db()->fetchAll(
        "SELECT * FROM cv_variant_interests
         WHERE cv_variant_id = ?
         ORDER BY name ASC",
        [$variantId]
    );
    
    // Load qualification equivalence
    $cvData['qualification_equivalence'] = db()->fetchAll(
        "SELECT * FROM cv_variant_qualification_equivalence
         WHERE cv_variant_id = ?
         ORDER BY level ASC",
        [$variantId]
    );
    
    // Load supporting evidence for each qualification
    foreach ($cvData['qualification_equivalence'] as &$qual) {
        $qual['evidence'] = db()->fetchAll(
            "SELECT * FROM cv_variant_supporting_evidence
             WHERE qualification_equivalence_id = ?
             ORDER BY sort_order ASC",
            [$qual['id']]
        );
    }
    unset($qual);
    
    // Check if variant is empty - if so, fall back to master CV (especially for master variants)
    $hasData = false;
    if ($cvData['professional_summary']) $hasData = true;
    if (!empty($cvData['work_experience'])) $hasData = true;
    if (!empty($cvData['education'])) $hasData = true;
    if (!empty($cvData['skills'])) $hasData = true;
    if (!empty($cvData['projects'])) $hasData = true;
    if (!empty($cvData['certifications'])) $hasData = true;
    if (!empty($cvData['memberships'])) $hasData = true;
    if (!empty($cvData['interests'])) $hasData = true;
    if (!empty($cvData['qualification_equivalence'])) $hasData = true;
    
    // If variant is empty and it's a master variant, fall back to master CV data
    if (!$hasData && $cvData['variant'] && !empty($cvData['variant']['is_master'])) {
        require_once __DIR__ . '/cv-data.php';
        $masterData = loadCvData($cvData['variant']['user_id']);
        
        if ($masterData) {
            // Merge master CV data into variant structure
            if (!$cvData['professional_summary'] && $masterData['professional_summary']) {
                $cvData['professional_summary'] = $masterData['professional_summary'];
            }
            if (empty($cvData['work_experience']) && !empty($masterData['work_experience'])) {
                $cvData['work_experience'] = $masterData['work_experience'];
            }
            if (empty($cvData['education']) && !empty($masterData['education'])) {
                $cvData['education'] = $masterData['education'];
            }
            if (empty($cvData['skills']) && !empty($masterData['skills'])) {
                $cvData['skills'] = $masterData['skills'];
            }
            if (empty($cvData['projects']) && !empty($masterData['projects'])) {
                $cvData['projects'] = $masterData['projects'];
            }
            if (empty($cvData['certifications']) && !empty($masterData['certifications'])) {
                $cvData['certifications'] = $masterData['certifications'];
            }
            if (empty($cvData['memberships']) && !empty($masterData['memberships'])) {
                $cvData['memberships'] = $masterData['memberships'];
            }
            if (empty($cvData['interests']) && !empty($masterData['interests'])) {
                $cvData['interests'] = $masterData['interests'];
            }
            if (empty($cvData['qualification_equivalence']) && !empty($masterData['qualification_equivalence'])) {
                $cvData['qualification_equivalence'] = $masterData['qualification_equivalence'];
            }
        }
    }
    
    return $cvData;
}

/**
 * Save CV data to a variant
 */
function saveCvVariantData($variantId, $cvData) {
    try {
        db()->beginTransaction();
        
        // Save professional summary
        if (isset($cvData['professional_summary'])) {
            $summary = $cvData['professional_summary'];
            
            // Check if exists for this variant
            $existing = db()->fetchOne(
                "SELECT id FROM cv_variant_professional_summary WHERE cv_variant_id = ?",
                [$variantId]
            );
            
            if ($existing) {
                // Update existing summary
                db()->update('cv_variant_professional_summary',
                    [
                        'description' => $summary['description'] ?? null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'id = ?',
                    [$existing['id']]
                );
                $summaryId = $existing['id'];
            } else {
                // Insert new summary - always generate new UUID to avoid duplicate key conflicts
                $summaryId = generateUuid();
                db()->insert('cv_variant_professional_summary', [
                    'id' => $summaryId,
                    'cv_variant_id' => $variantId,
                    'description' => $summary['description'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Save strengths
            if (isset($summary['strengths']) && is_array($summary['strengths'])) {
                // Delete existing
                db()->query("DELETE FROM cv_variant_professional_summary_strengths WHERE professional_summary_id = ?", [$summaryId]);
                
                // Insert new
                foreach ($summary['strengths'] as $index => $strength) {
                    if (is_array($strength)) {
                        $strengthText = $strength['strength'] ?? $strength;
                    } else {
                        $strengthText = $strength;
                    }
                    
                    if (!empty($strengthText)) {
                        db()->insert('cv_variant_professional_summary_strengths', [
                            'id' => generateUuid(),
                            'professional_summary_id' => $summaryId,
                            'strength' => sanitizeInput($strengthText),
                            'sort_order' => $index,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
        
        // Save work experience
        if (isset($cvData['work_experience']) && is_array($cvData['work_experience'])) {
            foreach ($cvData['work_experience'] as $work) {
                // Check if work experience already exists for this variant by matching on original_work_experience_id
                // or by matching on the provided ID (if it exists for this variant)
                $originalWorkId = $work['original_work_experience_id'] ?? $work['id'] ?? null;
                $existing = null;
                
                if ($originalWorkId) {
                    // Try to find existing entry by original_work_experience_id first
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_work_experience WHERE cv_variant_id = ? AND (original_work_experience_id = ? OR id = ?)",
                        [$variantId, $originalWorkId, $originalWorkId]
                    );
                }
                
                // If still not found, check by the provided ID (for this variant only)
                if (!$existing && isset($work['id'])) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_work_experience WHERE id = ? AND cv_variant_id = ?",
                        [$work['id'], $variantId]
                    );
                }
                
                $workData = [
                    'company_name' => sanitizeInput($work['company_name'] ?? ''),
                    'position' => sanitizeInput($work['position'] ?? ''),
                    'start_date' => $work['start_date'] ?? date('Y-m-d'),
                    'end_date' => !empty($work['end_date']) ? $work['end_date'] : null,
                    'description' => sanitizeInput($work['description'] ?? null),
                    'sort_order' => (int)($work['sort_order'] ?? 0),
                    'hide_date' => !empty($work['hide_date']) ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    // Update existing work experience
                    db()->update('cv_variant_work_experience', $workData, 'id = ?', [$existing['id']]);
                    $workId = $existing['id'];
                } else {
                    // Insert new work experience - always generate new UUID to avoid duplicate key conflicts
                    $workId = generateUuid();
                    $workData['id'] = $workId;
                    $workData['cv_variant_id'] = $variantId;
                    $workData['original_work_experience_id'] = $originalWorkId;
                    $workData['created_at'] = date('Y-m-d H:i:s');
                    db()->insert('cv_variant_work_experience', $workData);
                }
                
                // Save responsibility categories
                if (isset($work['responsibility_categories']) && is_array($work['responsibility_categories'])) {
                    // Delete existing
                    db()->query("DELETE FROM cv_variant_responsibility_categories WHERE work_experience_id = ?", [$workId]);
                    
                    foreach ($work['responsibility_categories'] as $catIndex => $category) {
                        $catId = generateUuid();
                        db()->insert('cv_variant_responsibility_categories', [
                            'id' => $catId,
                            'work_experience_id' => $workId,
                            'name' => sanitizeInput($category['name'] ?? ''),
                            'sort_order' => $catIndex,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Save items
                        if (isset($category['items']) && is_array($category['items'])) {
                            foreach ($category['items'] as $itemIndex => $item) {
                                db()->insert('cv_variant_responsibility_items', [
                                    'id' => generateUuid(),
                                    'category_id' => $catId,
                                    'content' => sanitizeInput($item['content'] ?? ''),
                                    'sort_order' => $itemIndex,
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        // Save education
        if (isset($cvData['education']) && is_array($cvData['education'])) {
            foreach ($cvData['education'] as $edu) {
                // Check if education already exists for this variant by matching on original_education_id or provided ID
                $originalEduId = $edu['original_education_id'] ?? $edu['id'] ?? null;
                $existing = null;
                
                if ($originalEduId) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_education WHERE cv_variant_id = ? AND (original_education_id = ? OR id = ?)",
                        [$variantId, $originalEduId, $originalEduId]
                    );
                }
                
                $eduData = [
                    'institution' => sanitizeInput($edu['institution'] ?? ''),
                    'degree' => sanitizeInput($edu['degree'] ?? ''),
                    'field_of_study' => sanitizeInput($edu['field_of_study'] ?? null),
                    'start_date' => $edu['start_date'] ?? date('Y-m-d'),
                    'end_date' => !empty($edu['end_date']) ? $edu['end_date'] : null,
                    'hide_date' => !empty($edu['hide_date']) ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    // Update existing education
                    db()->update('cv_variant_education', $eduData, 'id = ?', [$existing['id']]);
                    $eduId = $existing['id'];
                } else {
                    // Insert new education - always generate new UUID to avoid duplicate key conflicts
                    $eduId = generateUuid();
                    $eduData['id'] = $eduId;
                    $eduData['cv_variant_id'] = $variantId;
                    $eduData['original_education_id'] = $edu['original_education_id'] ?? null;
                    $eduData['created_at'] = date('Y-m-d H:i:s');
                    db()->insert('cv_variant_education', $eduData);
                }
            }
        }
        
        // Save skills
        if (isset($cvData['skills']) && is_array($cvData['skills'])) {
            // Delete existing
            db()->query("DELETE FROM cv_variant_skills WHERE cv_variant_id = ?", [$variantId]);
            
            foreach ($cvData['skills'] as $skill) {
                db()->insert('cv_variant_skills', [
                    'id' => generateUuid(),
                    'cv_variant_id' => $variantId,
                    'original_skill_id' => $skill['original_skill_id'] ?? null,
                    'name' => sanitizeInput($skill['name'] ?? ''),
                    'level' => sanitizeInput($skill['level'] ?? null),
                    'category' => sanitizeInput($skill['category'] ?? null),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Save projects
        if (isset($cvData['projects']) && is_array($cvData['projects'])) {
            foreach ($cvData['projects'] as $proj) {
                // Check if project already exists for this variant
                // First try matching by variant project ID (if this is an update to existing variant project)
                $existing = null;
                
                if (!empty($proj['id'])) {
                    // Check if this ID exists for this variant
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_projects WHERE cv_variant_id = ? AND id = ?",
                        [$variantId, $proj['id']]
                    );
                }
                
                // If not found by ID, try matching by original_project_id
                if (!$existing && !empty($proj['original_project_id'])) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_projects WHERE cv_variant_id = ? AND original_project_id = ?",
                        [$variantId, $proj['original_project_id']]
                    );
                }
                
                // If still not found and we have a title, try matching by title (to avoid duplicates)
                if (!$existing && !empty($proj['title'])) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_projects WHERE cv_variant_id = ? AND title = ?",
                        [$variantId, sanitizeInput($proj['title'])]
                    );
                }
                
                $projData = [
                    'title' => sanitizeInput($proj['title'] ?? ''),
                    'description' => sanitizeInput($proj['description'] ?? null),
                    'start_date' => !empty($proj['start_date']) ? $proj['start_date'] : null,
                    'end_date' => !empty($proj['end_date']) ? $proj['end_date'] : null,
                    'url' => sanitizeInput($proj['url'] ?? null),
                    'image_url' => sanitizeInput($proj['image_url'] ?? null),
                    'image_path' => sanitizeInput($proj['image_path'] ?? null),
                    'image_responsive' => !empty($proj['image_responsive']) ? json_encode($proj['image_responsive']) : null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    // Update existing project
                    db()->update('cv_variant_projects', $projData, 'id = ?', [$existing['id']]);
                    $projId = $existing['id'];
                } else {
                    // Insert new project - always generate new UUID to avoid duplicate key conflicts
                    $projId = generateUuid();
                    $projData['id'] = $projId;
                    $projData['cv_variant_id'] = $variantId;
                    $projData['original_project_id'] = $proj['original_project_id'] ?? null;
                    $projData['created_at'] = date('Y-m-d H:i:s');
                    db()->insert('cv_variant_projects', $projData);
                }
            }
        }
        
        // Save certifications
        if (isset($cvData['certifications']) && is_array($cvData['certifications'])) {
            foreach ($cvData['certifications'] as $cert) {
                // Check if certification already exists for this variant by matching on original_certification_id or provided ID
                $originalCertId = $cert['original_certification_id'] ?? $cert['id'] ?? null;
                $existing = null;
                
                if ($originalCertId) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_certifications WHERE cv_variant_id = ? AND (original_certification_id = ? OR id = ?)",
                        [$variantId, $originalCertId, $originalCertId]
                    );
                }
                
                $certData = [
                    'name' => sanitizeInput($cert['name'] ?? ''),
                    'issuer' => sanitizeInput($cert['issuer'] ?? ''),
                    'date_obtained' => $cert['date_obtained'] ?? date('Y-m-d'),
                    'expiry_date' => !empty($cert['expiry_date']) ? $cert['expiry_date'] : null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    // Update existing certification
                    db()->update('cv_variant_certifications', $certData, 'id = ?', [$existing['id']]);
                    $certId = $existing['id'];
                } else {
                    // Insert new certification - always generate new UUID to avoid duplicate key conflicts
                    $certId = generateUuid();
                    $certData['id'] = $certId;
                    $certData['cv_variant_id'] = $variantId;
                    $certData['original_certification_id'] = $cert['original_certification_id'] ?? null;
                    $certData['created_at'] = date('Y-m-d H:i:s');
                    db()->insert('cv_variant_certifications', $certData);
                }
            }
        }
        
        // Save memberships
        if (isset($cvData['memberships']) && is_array($cvData['memberships'])) {
            foreach ($cvData['memberships'] as $mem) {
                // Check if membership already exists for this variant by matching on original_membership_id or provided ID
                $originalMemId = $mem['original_membership_id'] ?? $mem['id'] ?? null;
                $existing = null;
                
                if ($originalMemId) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_memberships WHERE cv_variant_id = ? AND (original_membership_id = ? OR id = ?)",
                        [$variantId, $originalMemId, $originalMemId]
                    );
                }
                
                $memData = [
                    'organisation' => sanitizeInput($mem['organisation'] ?? ''),
                    'role' => sanitizeInput($mem['role'] ?? null),
                    'start_date' => $mem['start_date'] ?? date('Y-m-d'),
                    'end_date' => !empty($mem['end_date']) ? $mem['end_date'] : null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    // Update existing membership
                    db()->update('cv_variant_memberships', $memData, 'id = ?', [$existing['id']]);
                    $memId = $existing['id'];
                } else {
                    // Insert new membership - always generate new UUID to avoid duplicate key conflicts
                    $memId = generateUuid();
                    $memData['id'] = $memId;
                    $memData['cv_variant_id'] = $variantId;
                    $memData['original_membership_id'] = $mem['original_membership_id'] ?? null;
                    $memData['created_at'] = date('Y-m-d H:i:s');
                    db()->insert('cv_variant_memberships', $memData);
                }
            }
        }
        
        // Save interests
        if (isset($cvData['interests']) && is_array($cvData['interests'])) {
            // Delete existing
            db()->query("DELETE FROM cv_variant_interests WHERE cv_variant_id = ?", [$variantId]);
            
            foreach ($cvData['interests'] as $interest) {
                db()->insert('cv_variant_interests', [
                    'id' => generateUuid(),
                    'cv_variant_id' => $variantId,
                    'original_interest_id' => $interest['original_interest_id'] ?? null,
                    'name' => sanitizeInput($interest['name'] ?? ''),
                    'description' => sanitizeInput($interest['description'] ?? null),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Save qualification equivalence
        if (isset($cvData['qualification_equivalence']) && is_array($cvData['qualification_equivalence'])) {
            foreach ($cvData['qualification_equivalence'] as $qual) {
                // Check if qualification already exists for this variant by matching on original_qualification_id or provided ID
                $originalQualId = $qual['original_qualification_id'] ?? $qual['id'] ?? null;
                $existing = null;
                
                if ($originalQualId) {
                    $existing = db()->fetchOne(
                        "SELECT id FROM cv_variant_qualification_equivalence WHERE cv_variant_id = ? AND (original_qualification_id = ? OR id = ?)",
                        [$variantId, $originalQualId, $originalQualId]
                    );
                }
                
                $qualData = [
                    'level' => sanitizeInput($qual['level'] ?? ''),
                    'description' => sanitizeInput($qual['description'] ?? null),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    // Update existing qualification
                    db()->update('cv_variant_qualification_equivalence', $qualData, 'id = ?', [$existing['id']]);
                    $qualId = $existing['id'];
                } else {
                    // Insert new qualification - always generate new UUID to avoid duplicate key conflicts
                    $qualId = generateUuid();
                    $qualData['id'] = $qualId;
                    $qualData['cv_variant_id'] = $variantId;
                    $qualData['original_qualification_id'] = $qual['original_qualification_id'] ?? null;
                    $qualData['created_at'] = date('Y-m-d H:i:s');
                    db()->insert('cv_variant_qualification_equivalence', $qualData);
                }
                
                // Save supporting evidence
                if (isset($qual['evidence']) && is_array($qual['evidence'])) {
                    // Delete existing
                    db()->query("DELETE FROM cv_variant_supporting_evidence WHERE qualification_equivalence_id = ?", [$qualId]);
                    
                    foreach ($qual['evidence'] as $index => $evidence) {
                        db()->insert('cv_variant_supporting_evidence', [
                            'id' => generateUuid(),
                            'qualification_equivalence_id' => $qualId,
                            'content' => sanitizeInput($evidence['content'] ?? ''),
                            'sort_order' => $index,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
        
        // Update variant timestamp
        db()->update('cv_variants',
            ['updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$variantId]
        );
        
        db()->commit();
        return ['success' => true];
    } catch (Exception $e) {
        db()->rollback();
        error_log("Error saving CV variant data: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to save CV variant data: ' . $e->getMessage()];
    }
}

/**
 * Copy CV variant data from one variant to another
 */
function copyCvVariant($sourceVariantId, $targetVariantId) {
    $sourceData = loadCvVariantData($sourceVariantId);
    if (!$sourceData) {
        return false;
    }
    
    // Remove variant metadata
    unset($sourceData['variant']);
    
    return saveCvVariantData($targetVariantId, $sourceData);
}

/**
 * Copy master CV (profile_id based) to a variant
 */
function copyMasterCvToVariant($userId, $variantId) {
    $masterData = loadCvData($userId);
    if (!$masterData) {
        return false;
    }
    
    // Convert master CV data structure to variant structure
    $variantData = [
        'professional_summary' => $masterData['professional_summary'],
        'work_experience' => [],
        'education' => [],
        'skills' => [],
        'projects' => [],
        'certifications' => [],
        'memberships' => [],
        'interests' => [],
        'qualification_equivalence' => []
    ];
    
    // Convert work experience with original IDs
    foreach ($masterData['work_experience'] as $work) {
        $variantWork = $work;
        $variantWork['original_work_experience_id'] = $work['id'];
        unset($variantWork['id']);
        $variantData['work_experience'][] = $variantWork;
    }
    
    // Convert education
    foreach ($masterData['education'] as $edu) {
        $variantEdu = $edu;
        $variantEdu['original_education_id'] = $edu['id'];
        unset($variantEdu['id']);
        $variantData['education'][] = $variantEdu;
    }
    
    // Convert skills
    foreach ($masterData['skills'] as $skill) {
        $variantSkill = $skill;
        $variantSkill['original_skill_id'] = $skill['id'];
        unset($variantSkill['id']);
        $variantData['skills'][] = $variantSkill;
    }
    
    // Convert projects
    foreach ($masterData['projects'] as $proj) {
        $variantProj = $proj;
        $variantProj['original_project_id'] = $proj['id'];
        unset($variantProj['id']);
        $variantData['projects'][] = $variantProj;
    }
    
    // Convert certifications
    foreach ($masterData['certifications'] as $cert) {
        $variantCert = $cert;
        $variantCert['original_certification_id'] = $cert['id'];
        unset($variantCert['id']);
        $variantData['certifications'][] = $variantCert;
    }
    
    // Convert memberships
    foreach ($masterData['memberships'] as $mem) {
        $variantMem = $mem;
        $variantMem['original_membership_id'] = $mem['id'];
        unset($variantMem['id']);
        $variantData['memberships'][] = $variantMem;
    }
    
    // Convert interests
    foreach ($masterData['interests'] as $interest) {
        $variantInterest = $interest;
        $variantInterest['original_interest_id'] = $interest['id'];
        unset($variantInterest['id']);
        $variantData['interests'][] = $variantInterest;
    }
    
    // Convert qualification equivalence
    foreach ($masterData['qualification_equivalence'] as $qual) {
        $variantQual = $qual;
        $variantQual['original_qualification_id'] = $qual['id'];
        unset($variantQual['id']);
        $variantData['qualification_equivalence'][] = $variantQual;
    }
    
    return saveCvVariantData($variantId, $variantData);
}

/**
 * Delete a CV variant
 */
function deleteCvVariant($variantId, $userId = null) {
    // Verify ownership
    if ($userId) {
        $variant = db()->fetchOne(
            "SELECT id, is_master FROM cv_variants WHERE id = ? AND user_id = ?",
            [$variantId, $userId]
        );
        
        if (!$variant) {
            return ['success' => false, 'error' => 'Variant not found or access denied'];
        }
        
        // Prevent deleting master variant
        if ($variant['is_master']) {
            return ['success' => false, 'error' => 'Cannot delete master CV variant'];
        }
    }
    
    try {
        // Cascade delete will handle all related data
        db()->delete('cv_variants', 'id = ?', [$variantId]);
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Error deleting CV variant: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete CV variant'];
    }
}

/**
 * Update variant name
 */
function updateCvVariantName($variantId, $variantName, $userId = null) {
    if ($userId) {
        $variant = db()->fetchOne(
            "SELECT id FROM cv_variants WHERE id = ? AND user_id = ?",
            [$variantId, $userId]
        );
        
        if (!$variant) {
            return ['success' => false, 'error' => 'Variant not found or access denied'];
        }
    }
    
    try {
        db()->update('cv_variants',
            [
                'variant_name' => sanitizeInput($variantName),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$variantId]
        );
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Error updating variant name: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update variant name'];
    }
}

