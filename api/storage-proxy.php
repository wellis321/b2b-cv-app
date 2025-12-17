<?php
/**
 * Storage proxy - serve uploaded files
 */

require_once __DIR__ . '/../php/config.php';

// Get the file path from URL parameter
$path = $_GET['path'] ?? '';

// Clean the path - remove any leading slashes and normalize
$path = ltrim($path, '/');
$path = str_replace('..', '', $path); // Remove any directory traversal attempts

// Build full file path
$filePath = STORAGE_PATH . '/' . $path;

// Security check - ensure file is within storage directory
$realStoragePath = realpath(STORAGE_PATH);
if (!$realStoragePath) {
    http_response_code(500);
    die('Storage path not found');
}

// Check if file exists first
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('File not found: ' . htmlspecialchars($path));
}

$realFilePath = realpath($filePath);
if (!$realFilePath) {
    http_response_code(403);
    die('Cannot resolve file path');
}

// Ensure the resolved path is within the storage directory
if (strpos($realFilePath, $realStoragePath) !== 0) {
    http_response_code(403);
    die('Access denied: Path outside storage directory');
}

// Get MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header('Access-Control-Allow-Credentials: true');

// Output file
readfile($filePath);
exit;
