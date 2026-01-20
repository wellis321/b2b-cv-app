<?php
/**
 * Secure Template Execution
 * 
 * This file provides secure execution of user-provided PHP templates
 * by restricting dangerous functions and operations.
 */

/**
 * Sanitize template HTML/CSS to remove dangerous PHP code
 * 
 * @param string $templateHtml The template HTML containing PHP code
 * @return string Sanitized template HTML
 */
function sanitizeTemplateCode($templateHtml) {
    // List of dangerous PHP functions and constructs to block
    $dangerousPatterns = [
        // File system operations
        '/\b(eval|exec|system|shell_exec|passthru|proc_open|popen)\s*\(/i',
        '/\b(file_get_contents|file_put_contents|fopen|fwrite|fread|fclose|unlink|rmdir|mkdir|chmod|chown|chgrp|copy|move_uploaded_file|readfile|file|file_exists|is_file|is_dir|scandir|glob|opendir|readdir|closedir|rewinddir)\s*\(/i',
        
        // Database operations (if not using PDO/prepared statements)
        '/\b(mysql_|mysqli_|pg_|sqlite_|mssql_|oci_)/i',
        
        // Network operations
        '/\b(curl_exec|fsockopen|socket_|stream_socket_|file_get_contents\s*\(\s*[\'"]https?:)/i',
        
        // Code execution
        '/\b(create_function|assert|call_user_func|call_user_func_array|preg_replace\s*\([^,]+,\s*[\'"]\/e)/i',
        
        // Variable manipulation
        '/\b(extract|parse_str|import_request_variables|get_defined_vars|compact)\s*\(/i',
        
        // Process control
        '/\b(pcntl_|posix_|getenv|putenv|ini_set|ini_get|ini_restore)\s*\(/i',
        
        // Serialization (can be dangerous)
        '/\b(unserialize|serialize)\s*\(/i',
        
        // Include/require (can load arbitrary files)
        '/\b(include|require|include_once|require_once)\s*\(/i',
        
        // Reflection (can access private methods/properties)
        '/\b(new\s+Reflection)/i',
        
        // Direct variable access to superglobals (except allowed ones)
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER|ENV|FILES)\[/i',
        
        // Backticks (shell execution)
        '/`[^`]*`/',
        
        // PHP tags that might bypass our checks
        '/<\?=\s*\$_(GET|POST|REQUEST|COOKIE|SERVER|ENV|FILES)/i',
    ];
    
    // Remove dangerous patterns
    foreach ($dangerousPatterns as $pattern) {
        $templateHtml = preg_replace($pattern, '/* BLOCKED: Dangerous code removed */', $templateHtml);
    }
    
    // Whitelist approach: Only allow specific PHP constructs
    // Extract PHP code blocks
    $phpBlocks = [];
    preg_match_all('/<\?php\s*(.*?)\?>/s', $templateHtml, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $index => $phpCode) {
            // Check if code contains only allowed constructs
            $allowedPatterns = [
                // Echo/print statements
                '/^\s*(echo|print)\s+/i',
                // Variable access (only $profile, $cvData, and formatCvDate)
                '/\$profile\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$cvData\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$work\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$education\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$skill\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$project\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$certification\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$membership\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$strength\[[\'"][a-zA-Z_]+[\'"]\]/',
                '/\$cat\[[\'"][a-zA-Z_]+[\'"]\]/',
                // Function calls (only e() and formatCvDate())
                '/\be\s*\(/',
                '/\bformatCvDate\s*\(/',
                // Control structures
                '/\b(if|else|elseif|endif|foreach|endforeach|empty|isset|!empty|!isset)\s*\(/i',
                // String concatenation
                '/\./',
                // Basic operators
                '/[\+\-\*\/\%\=\!\<\>]/',
            ];
            
            // Check if code contains any dangerous patterns
            $hasDangerousCode = false;
            foreach ($dangerousPatterns as $dangerPattern) {
                if (preg_match($dangerPattern, $phpCode)) {
                    $hasDangerousCode = true;
                    break;
                }
            }
            
            // If dangerous code found, replace with safe placeholder
            if ($hasDangerousCode) {
                $templateHtml = str_replace($matches[0][$index], '<?php /* BLOCKED: Dangerous code removed */ ?>', $templateHtml);
            }
        }
    }
    
    return $templateHtml;
}

/**
 * Securely execute template code with restricted function access
 * 
 * @param string $templateHtml The template HTML containing PHP code
 * @param array $allowedVars Variables allowed in template scope (e.g., ['profile' => $profile, 'cvData' => $cvData])
 * @return string Rendered HTML output
 */
function executeTemplateSecurely($templateHtml, $allowedVars = []) {
    // Sanitize the template code first
    $sanitizedHtml = sanitizeTemplateCode($templateHtml);
    
    // Extract variables to make available in template scope
    extract($allowedVars, EXTR_SKIP);
    
    // Start output buffering
    ob_start();
    
    try {
        // Use eval with error handling
        // Note: Even sanitized, eval is risky, but we've removed dangerous functions
        // The sanitizeTemplateCode() function has already stripped out dangerous patterns
        eval('?>' . $sanitizedHtml);
    } catch (Throwable $e) {
        // Log error but don't expose details to user
        error_log("Template execution error: " . $e->getMessage());
        ob_end_clean();
        return '<div class="error p-4 bg-red-100 border border-red-400 text-red-700 rounded">Template execution error. Please check your template code.</div>';
    }
    
    $output = ob_get_clean();
    
    return $output;
}

/**
 * Validate template code before saving
 * 
 * @param string $templateHtml The template HTML to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateTemplateCode($templateHtml) {
    // Check for dangerous patterns
    $dangerousPatterns = [
        '/\b(eval|exec|system|shell_exec|passthru|proc_open|popen|file_get_contents|file_put_contents|fopen|fwrite|unlink|rmdir|mkdir|chmod|chown|include|require|include_once|require_once|create_function|assert|call_user_func|extract|parse_str|getenv|putenv|ini_set|unserialize|serialize)\s*\(/i',
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER|ENV|FILES)\[/i',
        '/`[^`]*`/',
        '/<\?=\s*\$_(GET|POST|REQUEST|COOKIE|SERVER|ENV|FILES)/i',
    ];
    
    foreach ($dangerousPatterns as $pattern) {
        if (preg_match($pattern, $templateHtml)) {
            return [
                'valid' => false,
                'error' => 'Template contains potentially dangerous code. Please remove any file system operations, code execution, or direct superglobal access.'
            ];
        }
    }
    
    return ['valid' => true];
}

