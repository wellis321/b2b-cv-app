<?php
/**
 * Job Application Management Functions
 * Integrated from job-tracker app
 */

require_once __DIR__ . '/utils.php';

/**
 * Get all job applications for a user
 */
function getUserJobApplications($userId = null, $filters = []) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return [];
    }
    
    $params = [$userId];
    $sql = "SELECT 
                ja.*,
                COALESCE(
                    JSON_ARRAYAGG(
                        CASE WHEN jaf.id IS NOT NULL THEN
                            JSON_OBJECT(
                                'id', jaf.id,
                                'original_name', jaf.original_name,
                                'custom_name', jaf.custom_name,
                                'file_name', jaf.file_name,
                                'mime_type', jaf.mime_type,
                                'size', jaf.size,
                                'file_purpose', jaf.file_purpose,
                                'uploaded_at', jaf.uploaded_at
                            )
                        END
                    ),
                    JSON_ARRAY()
                ) as files
            FROM job_applications ja
            LEFT JOIN job_application_files jaf ON ja.id = jaf.application_id
            WHERE ja.user_id = ?";
    
    // Add filters
    if (!empty($filters['status'])) {
        $sql .= " AND ja.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (ja.company_name LIKE ? OR ja.job_title LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " GROUP BY ja.id ORDER BY ja.created_at DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];
    }
    
    $rows = db()->fetchAll($sql, $params);
    
    $applications = [];
    foreach ($rows as $row) {
        $application = [
            'id' => $row['id'],
            'company_name' => $row['company_name'],
            'job_title' => $row['job_title'],
            'job_description' => $row['job_description'],
            'application_date' => $row['application_date'],
            'status' => $row['status'],
            'salary_range' => $row['salary_range'],
            'job_location' => $row['job_location'],
            'remote_type' => $row['remote_type'],
            'application_url' => $row['application_url'],
            'notes' => $row['notes'],
            'next_follow_up' => $row['next_follow_up'],
            'had_interview' => (bool)$row['had_interview'],
            'priority' => isset($row['priority']) ? $row['priority'] : null,
            'extracted_keywords' => $row['extracted_keywords'] ?? null,
            'selected_keywords' => $row['selected_keywords'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
        
        // Parse files JSON
        $files = json_decode($row['files'], true);
        if (!is_array($files)) {
            $files = [];
        }
        $application['files'] = array_filter($files, function($file) {
            return $file !== null && isset($file['id']);
        });
        
        // Add URLs to files
        foreach ($application['files'] as &$file) {
            if (isset($file['stored_name'])) {
                $file['url'] = STORAGE_URL . '/' . $file['stored_name'];
            }
        }
        
        $applications[] = $application;
    }
    
    return $applications;
}

/**
 * Get a single job application by ID
 */
function getJobApplication($applicationId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return null;
    }
    
    $row = db()->fetchOne(
        "SELECT 
            ja.*,
            COALESCE(
                JSON_ARRAYAGG(
                    CASE WHEN jaf.id IS NOT NULL THEN
                        JSON_OBJECT(
                            'id', jaf.id,
                            'original_name', jaf.original_name,
                            'custom_name', jaf.custom_name,
                            'file_name', jaf.file_name,
                            'mime_type', jaf.mime_type,
                            'size', jaf.size,
                            'file_purpose', jaf.file_purpose,
                            'uploaded_at', jaf.uploaded_at
                        )
                    END
                ),
                JSON_ARRAY()
            ) as files
         FROM job_applications ja
         LEFT JOIN job_application_files jaf ON ja.id = jaf.application_id
         WHERE ja.id = ? AND ja.user_id = ?
         GROUP BY ja.id",
        [$applicationId, $userId]
    );
    
    if (!$row) {
        return null;
    }
    
    $application = [
        'id' => $row['id'],
        'company_name' => $row['company_name'],
        'job_title' => $row['job_title'],
        'job_description' => $row['job_description'],
        'application_date' => $row['application_date'],
        'status' => $row['status'],
        'salary_range' => $row['salary_range'],
        'job_location' => $row['job_location'],
        'remote_type' => $row['remote_type'],
        'application_url' => $row['application_url'],
        'notes' => $row['notes'],
        'next_follow_up' => $row['next_follow_up'],
        'had_interview' => (bool)$row['had_interview'],
        'priority' => isset($row['priority']) ? $row['priority'] : null,
        'extracted_keywords' => $row['extracted_keywords'] ?? null,
        'selected_keywords' => $row['selected_keywords'] ?? null,
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
    
    // Parse files JSON
    $files = json_decode($row['files'], true);
    if (!is_array($files)) {
        $files = [];
    }
    $application['files'] = array_filter($files, function($file) {
        return $file !== null && isset($file['id']);
    });
    
    return $application;
}

/**
 * Derive a short job title from URL when title is missing (e.g. for quick-add).
 */
function deriveJobTitleFromUrl($url) {
    if (empty($url)) {
        return 'Untitled job';
    }
    $host = parse_url($url, PHP_URL_HOST);
    if ($host) {
        $host = preg_replace('/^www\./', '', $host);
        return 'Job from ' . $host;
    }
    return 'Untitled job';
}

/**
 * Get all application questions for a job
 */
function getJobApplicationQuestions($applicationId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    if (!$userId) {
        return [];
    }
    $rows = db()->fetchAll(
        "SELECT id, job_application_id, question_text, answer_text, sort_order, created_at, updated_at
         FROM job_application_questions
         WHERE job_application_id = ? AND user_id = ?
         ORDER BY sort_order ASC, created_at ASC",
        [$applicationId, $userId]
    );
    if ($rows) {
        foreach ($rows as &$r) {
            $r['answer_instructions'] = null;
        }
        unset($r);
        try {
            $withInst = db()->fetchAll(
                "SELECT id, answer_instructions FROM job_application_questions WHERE job_application_id = ? AND user_id = ?",
                [$applicationId, $userId]
            );
            if ($withInst) {
                $byId = array_column($withInst, 'answer_instructions', 'id');
                foreach ($rows as &$r) {
                    $r['answer_instructions'] = $byId[$r['id']] ?? null;
                }
                unset($r);
            }
        } catch (Exception $e) {
            // Column answer_instructions may not exist before migration 20250203
        }
    }
    return $rows ?: [];
}

/**
 * Add an application question
 * @param string|null $answerInstructions Optional stipulations (e.g. "Max 100 words", "Use bullet points")
 */
function addJobApplicationQuestion($applicationId, $userId, $questionText, $sortOrder = 0, $answerInstructions = null) {
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    $job = getJobApplication($applicationId, $userId);
    if (!$job) {
        return ['success' => false, 'error' => 'Application not found'];
    }
    $id = generateUuid();
    db()->insert('job_application_questions', [
        'id' => $id,
        'job_application_id' => $applicationId,
        'user_id' => $userId,
        'question_text' => $questionText,
        'answer_text' => null,
        'sort_order' => (int) $sortOrder,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    if ($answerInstructions !== null && trim($answerInstructions) !== '') {
        try {
            db()->update(
                'job_application_questions',
                ['answer_instructions' => trim($answerInstructions), 'updated_at' => date('Y-m-d H:i:s')],
                'id = ? AND user_id = ?',
                [$id, $userId]
            );
        } catch (Exception $e) {
            // answer_instructions column may not exist before migration 20250203
        }
    }
    return ['success' => true, 'id' => $id];
}

/**
 * Update an application question's answer
 */
function updateJobApplicationQuestionAnswer($questionId, $userId, $answerText) {
    return updateJobApplicationQuestionFields($questionId, $userId, ['answer_text' => $answerText]);
}

/**
 * Update one or more fields on an application question (answer_text, answer_instructions)
 */
function updateJobApplicationQuestionFields($questionId, $userId, $fields) {
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    $row = db()->fetchOne(
        "SELECT id FROM job_application_questions WHERE id = ? AND user_id = ?",
        [$questionId, $userId]
    );
    if (!$row) {
        return ['success' => false, 'error' => 'Question not found'];
    }
    $allowed = ['answer_text', 'answer_instructions'];
    $update = ['updated_at' => date('Y-m-d H:i:s')];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $fields)) {
            $update[$key] = $fields[$key];
        }
    }
    try {
        db()->update(
            'job_application_questions',
            $update,
            'id = ? AND user_id = ?',
            [$questionId, $userId]
        );
    } catch (Exception $e) {
        if (isset($update['answer_instructions'])) {
            unset($update['answer_instructions']);
            db()->update(
                'job_application_questions',
                $update,
                'id = ? AND user_id = ?',
                [$questionId, $userId]
            );
        } else {
            throw $e;
        }
    }
    return ['success' => true];
}

/**
 * Delete an application question
 */
function deleteJobApplicationQuestion($questionId, $userId) {
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    $row = db()->fetchOne(
        "SELECT id FROM job_application_questions WHERE id = ? AND user_id = ?",
        [$questionId, $userId]
    );
    if (!$row) {
        return ['success' => false, 'error' => 'Question not found'];
    }
    db()->delete('job_application_questions', 'id = ? AND user_id = ?', [$questionId, $userId]);
    return ['success' => true];
}

/**
 * Create a new job application
 */
function createJobApplication($data, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    
    $quickAdd = !empty($data['quick_add']);
    $hasUrl = !empty(trim((string)($data['application_url'] ?? '')));
    $hasTitle = !empty(trim((string)($data['job_title'] ?? '')));
    $companyEmpty = empty(trim((string)($data['company_name'] ?? '')));
    
    // Quick-add: require at least URL or title; company optional (default "—")
    if ($quickAdd || ($companyEmpty && ($hasUrl || $hasTitle))) {
        if (!$hasUrl && !$hasTitle) {
            return ['success' => false, 'error' => 'Job URL or job title is required'];
        }
        $data['company_name'] = trim((string)($data['company_name'] ?? '') ?: '—');
        $data['job_title'] = $hasTitle ? trim($data['job_title']) : deriveJobTitleFromUrl($data['application_url'] ?? '');
        if (!isset($data['status'])) {
            $data['status'] = 'interested';
        }
    } else {
        // Normal create: require company and title
        if (empty($data['company_name']) || empty($data['job_title'])) {
            return ['success' => false, 'error' => 'Company name and job title are required'];
        }
    }
    
    // Validate status
    $validStatuses = ['interested', 'in_progress', 'applied', 'interviewing', 'offered', 'rejected', 'accepted', 'withdrawn'];
    $status = in_array($data['status'] ?? 'applied', $validStatuses) ? ($data['status'] ?? 'applied') : 'applied';
    
    // Validate remote_type
    $validRemoteTypes = ['onsite', 'hybrid', 'remote'];
    $remoteType = in_array($data['remote_type'] ?? 'onsite', $validRemoteTypes) ? ($data['remote_type'] ?? 'onsite') : 'onsite';
    
    // Validate priority (optional)
    $validPriorities = ['low', 'medium', 'high'];
    $priority = null;
    if (isset($data['priority']) && $data['priority'] !== '' && in_array($data['priority'], $validPriorities, true)) {
        $priority = $data['priority'];
    }
    
    // Enforce job application limit for free plan
    if (function_exists('getUserSubscriptionContext') && function_exists('planCanAddEntry')) {
        $context = getUserSubscriptionContext($userId);
        if (!planCanAddEntry($context, 'job_applications', $userId)) {
            $limit = isset($context['config']['limits']['job_applications']) ? (int) $context['config']['limits']['job_applications'] : 5;
            return ['success' => false, 'error' => 'Your plan allows up to ' . $limit . ' job applications. Upgrade to Pro for unlimited applications.'];
        }
    }
    
    try {
        $applicationId = generateUuid();
        
        // application_date: only set when user has actually applied; leave null for quick-add/saved links
        $applicationDate = null;
        if (!empty($data['application_date'])) {
            $applicationDate = strlen($data['application_date']) === 10
                ? $data['application_date'] . ' 00:00:00'
                : $data['application_date'];
        } elseif (!$quickAdd) {
            // Non quick-add create: default to today
            $applicationDate = date('Y-m-d H:i:s');
        }
        
        // next_follow_up: support date-only (Y-m-d) for quick-add
        $nextFollowUp = null;
        if (!empty($data['next_follow_up'])) {
            $nextFollowUp = $data['next_follow_up'];
            if (strlen($nextFollowUp) === 10) {
                $nextFollowUp = $nextFollowUp . ' 00:00:00';
            }
        }
        
        // Auto-update status: if application_date is set and status is "interested" or "in_progress", change to "applied"
        if (!empty($data['application_date']) && in_array($status, ['interested', 'in_progress'])) {
            $status = 'applied';
        }
        
        $insertData = [
            'id' => $applicationId,
            'user_id' => $userId,
            'company_name' => sanitizeInput($data['company_name']),
            'job_title' => sanitizeInput($data['job_title']),
            'job_description' => prepareJobDescriptionForStorage($data['job_description'] ?? null),
            'application_date' => $applicationDate,
            'status' => $status,
            'salary_range' => sanitizeInput($data['salary_range'] ?? null),
            'job_location' => sanitizeInput($data['job_location'] ?? null),
            'remote_type' => $remoteType,
            'application_url' => sanitizeInput($data['application_url'] ?? null),
            'notes' => sanitizeInput($data['notes'] ?? null),
            'next_follow_up' => $nextFollowUp,
            'had_interview' => !empty($data['had_interview']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($priority !== null) {
            $insertData['priority'] = $priority;
        }
        db()->insert('job_applications', $insertData);
        
        // Log activity if user is in an organisation
        $org = null;
        if (function_exists('getUserOrganisation')) {
            $org = getUserOrganisation();
        }
        if ($org) {
            if (function_exists('logActivity')) {
                logActivity('job_application.created', null, [
                    'application_id' => $applicationId,
                    'company_name' => $data['company_name'],
                    'job_title' => $data['job_title']
                ], $org['organisation_id']);
            }
        }
        
        return ['success' => true, 'id' => $applicationId];
    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to create job application'];
    }
}

/**
 * Update a job application
 */
function updateJobApplication($applicationId, $data, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    
    // Verify ownership
    $existing = db()->fetchOne(
        "SELECT id FROM job_applications WHERE id = ? AND user_id = ?",
        [$applicationId, $userId]
    );
    
    if (!$existing) {
        return ['success' => false, 'error' => 'Application not found'];
    }
    
    // Validate status if provided
    $updateData = [];
    if (isset($data['status'])) {
        $validStatuses = ['interested', 'in_progress', 'applied', 'interviewing', 'offered', 'rejected', 'accepted', 'withdrawn'];
        if (in_array($data['status'], $validStatuses)) {
            $updateData['status'] = $data['status'];
        }
    }
    
    // Validate remote_type if provided
    if (isset($data['remote_type'])) {
        $validRemoteTypes = ['onsite', 'hybrid', 'remote'];
        if (in_array($data['remote_type'], $validRemoteTypes)) {
            $updateData['remote_type'] = $data['remote_type'];
        }
    }
    
    // Validate priority if provided
    if (array_key_exists('priority', $data)) {
        $validPriorities = ['low', 'medium', 'high'];
        if ($data['priority'] === '' || $data['priority'] === null) {
            $updateData['priority'] = null;
        } elseif (in_array($data['priority'], $validPriorities, true)) {
            $updateData['priority'] = $data['priority'];
        }
    }
    
    // Update other fields
    $allowedFields = ['company_name', 'job_title', 'job_description', 'application_date', 
                      'salary_range', 'job_location', 'application_url', 'notes', 
                      'next_follow_up', 'had_interview'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            if ($field === 'had_interview') {
                $updateData[$field] = $data[$field] ? 1 : 0;
            } elseif ($field === 'application_date' || $field === 'next_follow_up') {
                $val = $data[$field];
                if (!empty($val) && strlen($val) === 10) {
                    $val = $val . ' 00:00:00';
                }
                $updateData[$field] = !empty($data[$field]) ? $val : null;
            } elseif ($field === 'job_description') {
                $updateData[$field] = prepareJobDescriptionForStorage($data[$field]);
            } else {
                $updateData[$field] = sanitizeInput($data[$field]);
            }
        }
    }
    
    // Auto-update status: if application_date is being set and current status is "interested" or "in_progress", change to "applied"
    if (isset($updateData['application_date']) && !empty($updateData['application_date'])) {
        $currentStatus = $existing['status'] ?? null;
        if (in_array($currentStatus, ['interested', 'in_progress'])) {
            // Only auto-update if status wasn't explicitly changed in this update
            if (!isset($updateData['status'])) {
                $updateData['status'] = 'applied';
            }
        }
    }
    
    if (empty($updateData)) {
        return ['success' => false, 'error' => 'No valid fields to update'];
    }
    
    $updateData['updated_at'] = date('Y-m-d H:i:s');
    
    try {
        db()->update('job_applications', $updateData, 'id = ?', [$applicationId]);
        
        // Log activity if user is in an organisation
        $org = getUserOrganisation();
        if ($org) {
            logActivity('job_application.updated', null, [
                'application_id' => $applicationId
            ], $org['organisation_id']);
        }
        
        return ['success' => true];
    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to update job application'];
    }
}

/**
 * Delete a job application
 */
function deleteJobApplication($applicationId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    
    // Verify ownership
    $existing = db()->fetchOne(
        "SELECT id FROM job_applications WHERE id = ? AND user_id = ?",
        [$applicationId, $userId]
    );
    
    if (!$existing) {
        return ['success' => false, 'error' => 'Application not found'];
    }
    
    try {
        // Delete associated files first (handled by foreign key CASCADE, but we'll clean up storage)
        $files = db()->fetchAll(
            "SELECT stored_name FROM job_application_files WHERE application_id = ?",
            [$applicationId]
        );
        
        foreach ($files as $file) {
            $filePath = STORAGE_PATH . '/job-applications/' . $file['stored_name'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        // Delete the application (files will be deleted by CASCADE)
        db()->delete('job_applications', 'id = ?', [$applicationId]);
        
        // Log activity if user is in an organisation
        $org = getUserOrganisation();
        if ($org) {
            logActivity('job_application.deleted', null, [
                'application_id' => $applicationId
            ], $org['organisation_id']);
        }
        
        return ['success' => true];
    } catch (Exception $e) {
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Failed to delete job application'];
    }
}

/**
 * Get job application statistics for a user
 */
function getJobApplicationStats($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return [];
    }
    
    $stats = db()->fetchOne(
        "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as applied,
            SUM(CASE WHEN status = 'interviewing' THEN 1 ELSE 0 END) as interviewing,
            SUM(CASE WHEN status = 'offered' THEN 1 ELSE 0 END) as offered,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN had_interview = 1 THEN 1 ELSE 0 END) as had_interview,
            SUM(CASE WHEN next_follow_up IS NOT NULL AND next_follow_up <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as upcoming_followups
         FROM job_applications
         WHERE user_id = ?",
        [$userId]
    );
    
    return $stats ?: [
        'total' => 0,
        'applied' => 0,
        'interviewing' => 0,
        'offered' => 0,
        'accepted' => 0,
        'rejected' => 0,
        'had_interview' => 0,
        'upcoming_followups' => 0
    ];
}

/**
 * Get files for a job application
 */
function getJobApplicationFiles($applicationId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return [];
    }
    
    // Verify application ownership
    $application = db()->fetchOne(
        "SELECT id FROM job_applications WHERE id = ? AND user_id = ?",
        [$applicationId, $userId]
    );
    
    if (!$application) {
        return [];
    }
    
    $files = db()->fetchAll(
        "SELECT * FROM job_application_files WHERE application_id = ? AND user_id = ? ORDER BY uploaded_at DESC",
        [$applicationId, $userId]
    );
    
    // Add full URL to each file
    foreach ($files as &$file) {
        $file['url'] = STORAGE_URL . '/' . $file['stored_name'];
    }
    
    return $files;
}

/**
 * Get a single file by ID
 */
function getJobApplicationFile($fileId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return null;
    }
    
    $file = db()->fetchOne(
        "SELECT * FROM job_application_files WHERE id = ? AND user_id = ?",
        [$fileId, $userId]
    );
    
    if ($file) {
        $file['url'] = STORAGE_URL . '/' . $file['stored_name'];
    }
    
    return $file;
}

/**
 * Extract text from a job application file
 */
function extractJobApplicationFileText($fileId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }
    
    $file = getJobApplicationFile($fileId, $userId);
    if (!$file) {
        return ['success' => false, 'error' => 'File not found'];
    }
    
    require_once __DIR__ . '/document-extractor.php';
    
    $filePath = STORAGE_PATH . '/' . $file['stored_name'];
    if (!file_exists($filePath)) {
        return ['success' => false, 'error' => 'File not found on disk'];
    }
    
    return extractDocumentText($filePath, $file['mime_type'] ?? '', $file['original_name'] ?? '');
}

/**
 * Get all files for a job application that can be used for AI CV generation
 * Returns files with extracted text content
 */
function getJobApplicationFilesForAI($applicationId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return [];
    }
    
    $files = getJobApplicationFiles($applicationId, $userId);
    $filesWithText = [];
    
    require_once __DIR__ . '/document-extractor.php';
    
    foreach ($files as $file) {
        $filePath = STORAGE_PATH . '/' . $file['stored_name'];
        if (file_exists($filePath)) {
            $extractionResult = extractDocumentText($filePath, $file['mime_type'] ?? '', $file['original_name'] ?? '');
            if ($extractionResult['success'] && !empty($extractionResult['text'])) {
                $filesWithText[] = [
                    'file' => $file,
                    'text' => $extractionResult['text']
                ];
            }
        }
    }
    
    return $filesWithText;
}

