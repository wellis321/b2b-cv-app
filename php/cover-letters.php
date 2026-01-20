<?php
/**
 * Cover Letter Management Functions
 * Handle creating, loading, updating, and deleting cover letters
 */

require_once __DIR__ . '/utils.php';

/**
 * Get a cover letter by ID
 */
function getCoverLetter($coverLetterId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return null;
    }
    
    return db()->fetchOne(
        "SELECT * FROM cover_letters WHERE id = ? AND user_id = ?",
        [$coverLetterId, $userId]
    );
}

/**
 * Get cover letter for a specific job application
 */
function getCoverLetterByApplication($applicationId, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return null;
    }
    
    return db()->fetchOne(
        "SELECT cl.* FROM cover_letters cl
         INNER JOIN job_applications ja ON cl.job_application_id = ja.id
         WHERE cl.job_application_id = ? AND cl.user_id = ? AND ja.user_id = ?",
        [$applicationId, $userId, $userId]
    );
}

/**
 * Create a new cover letter
 */
function createCoverLetter($userId, $applicationId, $coverLetterText) {
    // Verify the job application belongs to the user
    $application = db()->fetchOne(
        "SELECT id FROM job_applications WHERE id = ? AND user_id = ?",
        [$applicationId, $userId]
    );
    
    if (!$application) {
        return ['success' => false, 'error' => 'Job application not found'];
    }
    
    // Check if cover letter already exists for this application
    $existing = getCoverLetterByApplication($applicationId, $userId);
    if ($existing) {
        // Update existing instead of creating new
        return updateCoverLetter($existing['id'], $userId, $coverLetterText);
    }
    
    try {
        $coverLetterId = generateUuid();
        
        db()->insert('cover_letters', [
            'id' => $coverLetterId,
            'user_id' => $userId,
            'job_application_id' => $applicationId,
            'cover_letter_text' => $coverLetterText,
            'generated_at' => date('Y-m-d H:i:s'),
            'last_edited_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => true, 'cover_letter_id' => $coverLetterId];
    } catch (Exception $e) {
        error_log("Error creating cover letter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to create cover letter'];
    }
}

/**
 * Update an existing cover letter
 */
function updateCoverLetter($coverLetterId, $userId, $coverLetterText) {
    // Verify ownership
    $existing = db()->fetchOne(
        "SELECT id FROM cover_letters WHERE id = ? AND user_id = ?",
        [$coverLetterId, $userId]
    );
    
    if (!$existing) {
        return ['success' => false, 'error' => 'Cover letter not found'];
    }
    
    try {
        db()->update('cover_letters', [
            'cover_letter_text' => $coverLetterText,
            'last_edited_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ? AND user_id = ?', [$coverLetterId, $userId]);
        
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Error updating cover letter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update cover letter'];
    }
}

/**
 * Delete a cover letter
 */
function deleteCoverLetter($coverLetterId, $userId) {
    // Verify ownership
    $existing = db()->fetchOne(
        "SELECT id FROM cover_letters WHERE id = ? AND user_id = ?",
        [$coverLetterId, $userId]
    );
    
    if (!$existing) {
        return ['success' => false, 'error' => 'Cover letter not found'];
    }
    
    try {
        db()->delete('cover_letters', 'id = ? AND user_id = ?', [$coverLetterId, $userId]);
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Error deleting cover letter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete cover letter'];
    }
}

