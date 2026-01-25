<?php
/**
 * Security functions (CSRF, validation, sanitization)
 */

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF token for forms
 */
function csrfToken() {
    return generateCsrfToken();
}

/**
 * Sanitize input (for display only - do not use before DB storage; it HTML-encodes &, <, etc.)
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    if ($input === null) {
        return null;
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Prepare user input for database storage: trim and strip tags, but do NOT HTML-encode.
 * Use e() when outputting to HTML. Use this when saving to DB so & and similar stay as-is.
 */
function prepareForStorage($input) {
    if ($input === null) {
        return null;
    }
    return strip_tags(trim((string) $input));
}

/**
 * Escape for HTML after fixing text that may have been over-encoded on save (e.g. &amp; in DB).
 * Use when displaying skill names, categories, and similar user text that may have been
 * stored with htmlspecialchars by mistake.
 */
function e_text($string) {
    if ($string === null || $string === '') {
        return '';
    }
    return e(html_entity_decode((string) $string, ENT_QUOTES, 'UTF-8'));
}

/**
 * Check for XSS
 */
function checkForXss($input) {
    $dangerous = ['<script', '</script', 'javascript:', 'onerror=', 'onload=', 'onclick='];
    $lower = strtolower($input);
    foreach ($dangerous as $pattern) {
        if (strpos($lower, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic)
 */
function validatePhone($phone) {
    return preg_match('/^[\d\s\-\+\(\)]+$/', $phone);
}

/**
 * Validate URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Escape for HTML output
 */
function e($string) {
    if ($string === null) {
        return '';
    }
    // Handle arrays - convert to comma-separated string
    if (is_array($string)) {
        // If it's an array of associative arrays with 'name' key, extract names
        if (!empty($string) && isset($string[0]) && is_array($string[0]) && isset($string[0]['name'])) {
            $string = array_map(function($item) {
                return is_array($item) ? ($item['name'] ?? '') : $item;
            }, $string);
        }
        // Filter out any remaining arrays and convert to comma-separated string
        $string = implode(', ', array_filter($string, function($item) {
            return !is_array($item) && $item !== null && $item !== '';
        }));
    }
    // Ensure we have a string before passing to htmlspecialchars
    if (!is_string($string)) {
        $string = (string) $string;
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($url, $statusCode = 302) {
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Rate limiting - check if action is allowed
 * @param string $key Unique identifier (e.g., 'login_' . $ip or 'register_' . $email)
 * @param int $maxAttempts Maximum number of attempts allowed
 * @param int $windowSeconds Time window in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => int]
 */
function checkRateLimit($key, $maxAttempts, $windowSeconds) {
    $cacheDir = sys_get_temp_dir() . '/ratelimit';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . '/' . md5($key) . '.json';
    $now = time();

    // Load existing attempts
    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?: [];
    }

    // Filter out expired attempts
    $data = array_filter($data, function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });

    // Count remaining attempts
    $attemptCount = count($data);
    $remaining = max(0, $maxAttempts - $attemptCount);
    $allowed = $attemptCount < $maxAttempts;

    // Calculate reset time (oldest attempt + window)
    $resetAt = $now + $windowSeconds;
    if (!empty($data)) {
        $oldestAttempt = min($data);
        $resetAt = $oldestAttempt + $windowSeconds;
    }

    // If allowed, record this attempt
    if ($allowed) {
        $data[] = $now;
        file_put_contents($cacheFile, json_encode(array_values($data)), LOCK_EX);
    }

    return [
        'allowed' => $allowed,
        'remaining' => $remaining,
        'reset_at' => $resetAt
    ];
}

/**
 * Get client IP address (handles proxies)
 */
function getClientIp() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    // Optional: require special character (commented out for now)
    // if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    //     $errors[] = 'Password must contain at least one special character';
    // }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Log authentication attempt
 * @param string $type 'login' or 'register'
 * @param string $email Email address
 * @param bool $success Whether attempt was successful
 * @param string|null $reason Reason for failure (if unsuccessful)
 */
function logAuthAttempt($type, $email, $success, $reason = null) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/auth.log';
    $ip = getClientIp();
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    $message = "[{$timestamp}] {$type} {$status} - IP: {$ip} - Email: {$email}";

    if (!$success && $reason) {
        $message .= " - Reason: {$reason}";
    }

    $message .= PHP_EOL;

    @file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Set Content-Type with charset (must be set early, before any output)
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }

    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // XSS protection (legacy, but still useful)
    header('X-XSS-Protection: 1; mode=block');

    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Permissions policy (restrict dangerous features)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // Content Security Policy (basic)
    // Allow connections to localhost for Ollama and other local services
    // Allow cdnjs.cloudflare.com for pdfmake source maps and other CDN resources
    // WebLLM needs to download models from mlc.ai and huggingface.co
    // Also needs to load WASM files and other resources
    // WebLLM requires 'wasm-unsafe-eval' for WebAssembly execution
    // WebLLM uses Web Workers which need blob: URLs
    // WebLLM Web Workers need to fetch from the same domains as the main page
    // Workers inherit connect-src from the page, but we need to ensure all domains are allowed
    // Temporarily allowing all HTTPS connections to debug WebLLM fetch issues
    // TODO: Restrict this after identifying the exact domains needed
    $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:* https:; worker-src 'self' blob: data: https:; child-src 'self' blob: data:; script-src-elem 'self' 'unsafe-inline' 'wasm-unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com;";
    header("Content-Security-Policy: {$csp}");

    // HSTS (only if HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
