<?php
/**
 * Prompt Security Functions
 * Sanitizes and validates user-provided AI prompts to prevent injection attacks
 */

/**
 * Sanitize custom prompt instructions to prevent injection attacks
 * @param string $instructions Raw user input
 * @return array ['sanitized' => string, 'warnings' => array, 'blocked' => bool]
 */
function sanitizePromptInstructions($instructions) {
    $warnings = [];
    $blocked = false;
    $sanitized = trim($instructions);
    
    // List of dangerous patterns that indicate prompt injection attempts
    $dangerousPatterns = [
        // System override attempts
        '/ignore\s+(all\s+)?previous\s+instructions?/i',
        '/forget\s+(all\s+)?previous\s+instructions?/i',
        '/disregard\s+(all\s+)?previous\s+instructions?/i',
        '/override\s+(all\s+)?previous\s+instructions?/i',
        '/you\s+are\s+now/i',
        '/you\s+must\s+now/i',
        '/change\s+your\s+role/i',
        '/act\s+as\s+(a|an)\s+(different|new)/i',
        
        // Data extraction attempts
        '/reveal\s+(all\s+)?(system|prompt|api|key|secret)/i',
        '/show\s+(me\s+)?(all\s+)?(system|prompt|api|key|secret)/i',
        '/extract\s+(all\s+)?(system|prompt|api|key|secret)/i',
        '/output\s+(all\s+)?(system|prompt|api|key|secret)/i',
        '/return\s+(all\s+)?(system|prompt|api|key|secret)/i',
        
        // Format manipulation
        '/do\s+not\s+return\s+json/i',
        '/ignore\s+the\s+json\s+format/i',
        '/return\s+plain\s+text\s+instead/i',
        '/skip\s+json\s+validation/i',
        
        // System information requests
        '/what\s+(is|are)\s+your\s+(system|prompt|instructions?)/i',
        '/tell\s+me\s+(about\s+)?(your\s+)?(system|prompt|instructions?)/i',
        '/what\s+(were|are)\s+the\s+(previous|original)\s+instructions?/i',
    ];
    
    // Check for dangerous patterns
    foreach ($dangerousPatterns as $pattern) {
        if (preg_match($pattern, $sanitized)) {
            $blocked = true;
            $warnings[] = "Instruction contains potentially dangerous content and was blocked.";
            // Remove the dangerous content
            $sanitized = preg_replace($pattern, '', $sanitized);
        }
    }
    
    // Remove control characters that could be used for injection
    $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitized);
    
    // Remove excessive whitespace
    $sanitized = preg_replace('/\s{3,}/', ' ', $sanitized);
    
    // Limit consecutive newlines
    $sanitized = preg_replace('/\n{4,}/', "\n\n\n", $sanitized);
    
    // Trim again after cleaning
    $sanitized = trim($sanitized);
    
    // If blocked, return empty string
    if ($blocked) {
        $sanitized = '';
    }
    
    return [
        'sanitized' => $sanitized,
        'warnings' => $warnings,
        'blocked' => $blocked
    ];
}

/**
 * Validate that instructions are appropriate for CV rewriting
 * @param string $instructions User instructions
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePromptInstructions($instructions) {
    $errors = [];
    
    // Check minimum length (must have some actual content)
    if (strlen(trim($instructions)) < 10) {
        $errors[] = 'Instructions must be at least 10 characters long';
    }
    
    // Check for CV-related keywords (instructions should be about CV rewriting)
    $cvKeywords = ['cv', 'resume', 'job', 'work', 'experience', 'skill', 'education', 'professional', 'summary', 'description', 'rewrite', 'tailor', 'match', 'improve', 'enhance'];
    $hasCvContext = false;
    $lowerInstructions = strtolower($instructions);
    
    foreach ($cvKeywords as $keyword) {
        if (strpos($lowerInstructions, $keyword) !== false) {
            $hasCvContext = true;
            break;
        }
    }
    
    // If instructions are long but have no CV context, flag as suspicious
    if (strlen($instructions) > 100 && !$hasCvContext) {
        $errors[] = 'Instructions should relate to CV rewriting and job applications';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Log suspicious prompt activity for monitoring
 * @param string $userId User ID
 * @param string $instructions Original instructions
 * @param array $sanitizationResult Result from sanitizePromptInstructions
 */
function logPromptSecurityEvent($userId, $instructions, $sanitizationResult) {
    if ($sanitizationResult['blocked'] || !empty($sanitizationResult['warnings'])) {
        error_log(sprintf(
            "[PROMPT_SECURITY] User: %s | Blocked: %s | Warnings: %d | Preview: %s",
            $userId,
            $sanitizationResult['blocked'] ? 'YES' : 'NO',
            count($sanitizationResult['warnings']),
            substr($instructions, 0, 200)
        ));
        
        // Optionally store in database for monitoring
        try {
            // Check if security_logs table exists before trying to insert
            $tableExists = db()->fetchOne("SHOW TABLES LIKE 'security_logs'");
            if ($tableExists) {
                db()->insert('security_logs', [
                    'user_id' => $userId,
                    'event_type' => 'prompt_injection_attempt',
                    'event_data' => json_encode([
                        'blocked' => $sanitizationResult['blocked'],
                        'warnings' => $sanitizationResult['warnings'],
                        'instruction_preview' => substr($instructions, 0, 500)
                    ]),
                    'ip_address' => getClientIp(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (Exception $e) {
            // Table might not exist, just log to error log
            error_log("Could not log to security_logs table: " . $e->getMessage());
        }
    }
}

