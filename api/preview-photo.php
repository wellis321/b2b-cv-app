<?php
/**
 * Serve profile photo as JPEG for PDF generation.
 * Converts any image format to JPEG server-side to avoid client decoding issues.
 */

// Discard any output from included files (PHP deprecation notices, etc.) - must output binary only
ob_start();

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/database.php';
require_once __DIR__ . '/../php/auth.php';

$path = $_GET['path'] ?? '';
$path = ltrim($path, '/');
$path = str_replace('..', '', $path);

$filePath = STORAGE_PATH . '/' . $path;

$realStoragePath = realpath(STORAGE_PATH);
if (!$realStoragePath) {
    ob_end_clean();
    http_response_code(500);
    die('Storage path not found');
}

if (!file_exists($filePath) || !is_file($filePath)) {
    ob_end_clean();
    http_response_code(404);
    die('File not found');
}

$realFilePath = realpath($filePath);
if (!$realFilePath || strpos($realFilePath, $realStoragePath) !== 0) {
    ob_end_clean();
    http_response_code(403);
    die('Access denied');
}

$isPublicFile = (
    strpos($path, 'profiles/') === 0 ||
    strpos($path, 'uploads/profile-photos/') === 0 ||
    strpos($path, 'uploads/projects/') === 0
);

if (!$isPublicFile) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isLoggedIn()) {
        ob_end_clean();
        http_response_code(401);
        die('Authentication required');
    }
}

if (!extension_loaded('gd')) {
    ob_end_clean();
    header('Content-Type: image/jpeg');
    readfile($filePath);
    exit;
}

$imageInfo = @getimagesize($filePath);
if (!$imageInfo) {
    ob_end_clean();
    http_response_code(500);
    die('Invalid image');
}

$mimeType = $imageInfo['mime'];
$sourceWidth = $imageInfo[0];
$sourceHeight = $imageInfo[1];
$maxSize = 400;
$ratio = min($maxSize / max(1, $sourceWidth), $maxSize / max(1, $sourceHeight), 1);
$w = (int)($sourceWidth * $ratio);
$h = (int)($sourceHeight * $ratio);

switch ($mimeType) {
    case 'image/jpeg':
        $img = @imagecreatefromjpeg($filePath);
        break;
    case 'image/png':
        $img = @imagecreatefrompng($filePath);
        break;
    case 'image/gif':
        $img = @imagecreatefromgif($filePath);
        break;
    case 'image/webp':
        $img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : null;
        break;
    default:
        ob_end_clean();
        http_response_code(500);
        die('Unsupported format');
}

if (!$img) {
    ob_end_clean();
    http_response_code(500);
    die('Could not decode image');
}

$out = imagecreatetruecolor($w, $h);
if (!$out) {
    imagedestroy($img);
    ob_end_clean();
    http_response_code(500);
    die('Could not create output');
}

imagecopyresampled($out, $img, 0, 0, 0, 0, $w, $h, $sourceWidth, $sourceHeight);
imagedestroy($img);

ob_end_clean(); // Discard any buffered output from includes
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=3600');
header('X-Content-Type-Options: nosniff');
imagejpeg($out, null, 85);
imagedestroy($out);
exit;
