<?php
/**
 * Utility functions
 */

/**
 * Format date according to user preference
 */
function formatDate($date, $format = 'dd/mm/yyyy') {
    if (empty($date)) {
        return '';
    }

    $timestamp = is_numeric($date) ? $date : strtotime($date);
    if ($timestamp === false) {
        return $date;
    }

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

/**
 * Generate UUID (for MySQL compatibility)
 */
function generateUuid() {
    // For MySQL 8.0+, we can use UUID() function
    // For older versions, generate PHP UUID
    if (function_exists('uuid_create')) {
        return uuid_create(UUID_TYPE_RANDOM);
    }

    // Fallback UUID v4 generation
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Get base URL
 */
function baseUrl() {
    return APP_URL;
}

/**
 * Get current URL
 */
function currentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get current base URL (scheme + host, no path)
 * Uses current request when available, falls back to APP_URL
 */
function currentBaseUrl() {
    if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        // Remove port if it's a standard port (80 for http, 443 for https)
        if (($scheme === 'http' && strpos($host, ':80') === strlen($host) - 3) ||
            ($scheme === 'https' && strpos($host, ':443') === strlen($host) - 4)) {
            $host = str_replace([':80', ':443'], '', $host);
        }
        return $scheme . '://' . $host;
    }
    return APP_URL;
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Get POST data
 */
function post($key = null, $default = null) {
    if ($key === null) {
        return $_POST;
    }
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data
 */
function get($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return $_GET[$key] ?? $default;
}

/**
 * Get request input (POST or GET)
 */
function input($key = null, $default = null) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return post($key, $default);
    }
    return get($key, $default);
}
