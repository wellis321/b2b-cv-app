<?php
/**
 * Main configuration file
 */

// Load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove inline comments (everything after # that's not in quotes)
                // Simple approach: find # that's not inside quotes
                $inQuotes = false;
                $quoteChar = null;
                $commentPos = false;
                for ($i = 0; $i < strlen($value); $i++) {
                    $char = $value[$i];
                    if (($char === '"' || $char === "'") && ($i === 0 || $value[$i-1] !== '\\')) {
                        if (!$inQuotes) {
                            $inQuotes = true;
                            $quoteChar = $char;
                        } elseif ($char === $quoteChar) {
                            $inQuotes = false;
                            $quoteChar = null;
                        }
                    } elseif ($char === '#' && !$inQuotes) {
                        $commentPos = $i;
                        break;
                    }
                }
                if ($commentPos !== false) {
                    $value = trim(substr($value, 0, $commentPos));
                }

                // Remove quotes if present (both single and double)
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }

            // Set environment variable if not already set
            // Note: Empty values are allowed (for passwords that might be empty)
            if (!empty($key)) {
                // Use $_ENV for better compatibility
                $shouldOverride = !array_key_exists($key, $_ENV) || $_ENV[$key] === '';
                if ($shouldOverride) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
}

// Load .env file from project root
// Try multiple possible locations
$possiblePaths = [
    __DIR__ . '/../.env',           // Project root (relative to php/)
    __DIR__ . '/../../.env',        // One level up (if php/ is nested)
    $_SERVER['DOCUMENT_ROOT'] . '/.env',  // Document root
];

$envPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $envPath = $path;
        break;
    }
}

if ($envPath) {
    loadEnv($envPath);
} else {
    // Log warning if in debug mode
    if (DEBUG) {
        error_log("Warning: .env file not found. Tried: " . implode(', ', $possiblePaths));
    }
}

// Helper to get env var with fallback
function env($key, $default = '') {
    // Try $_ENV first (more reliable)
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    // Then try getenv()
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    // Return default
    return $default;
}

// Database configuration (reads from .env file)
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'cv_app'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// Application configuration
// Auto-detect APP_URL if not set, including port number
$defaultAppUrl = 'http://localhost';
if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST']; // Includes port if present (e.g., localhost:8000)
    $defaultAppUrl = $scheme . '://' . $host;
}
define('APP_URL', env('APP_URL', $defaultAppUrl));
define('APP_ENV', env('APP_ENV', 'development'));
define('DEBUG', APP_ENV === 'development');

// Subscription and billing configuration
define('DEFAULT_PLAN', 'free');
define('STRIPE_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY', ''));
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', ''));
define('STRIPE_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET', ''));
define('STRIPE_PRICE_PRO_MONTHLY', env('STRIPE_PRICE_PRO_MONTHLY', ''));
define('STRIPE_PRICE_PRO_ANNUAL', env('STRIPE_PRICE_PRO_ANNUAL', ''));
define('STRIPE_PRICE_LIFETIME', env('STRIPE_PRICE_LIFETIME', ''));

// Organisation subscription prices (B2B)
define('STRIPE_PRICE_AGENCY_BASIC', env('STRIPE_PRICE_AGENCY_BASIC', ''));
define('STRIPE_PRICE_AGENCY_PRO', env('STRIPE_PRICE_AGENCY_PRO', ''));
define('STRIPE_PRICE_AGENCY_ENTERPRISE', env('STRIPE_PRICE_AGENCY_ENTERPRISE', ''));

// Set Content-Type header FIRST, before any output or errors
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// Error reporting (CRITICAL: disable display in production)
error_reporting(E_ALL);
if (DEBUG) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    // Ensure logs directory exists
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/php-errors.log');
}

// Session configuration
define('SESSION_LIFETIME', 86400 * 7); // 7 days
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_ENV === 'production' ? 1 : 0);
if (PHP_VERSION_ID >= 70300) {
    ini_set('session.cookie_samesite', 'Lax');
}

// File storage configuration
define('STORAGE_PATH', __DIR__ . '/../storage');
define('STORAGE_URL', APP_URL . '/storage');
define('STORAGE_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Document file configuration
define('DOCUMENT_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
    'text/plain',
    'text/csv',
    'image/jpeg',
    'image/png'
]);
define('ALLOWED_DOCUMENT_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'jpg', 'jpeg', 'png']);

// Security configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// AI Service Configuration
define('AI_SERVICE', env('AI_SERVICE', 'ollama')); // 'ollama', 'openai', 'anthropic'
define('OLLAMA_BASE_URL', env('OLLAMA_BASE_URL', 'http://localhost:11434'));
define('OLLAMA_MODEL', env('OLLAMA_MODEL', 'llama3:latest')); // Default model - change to match your installed Ollama model
define('OPENAI_API_KEY', env('OPENAI_API_KEY', ''));
define('OPENAI_MODEL', env('OPENAI_MODEL', 'gpt-4-turbo-preview'));
define('ANTHROPIC_API_KEY', env('ANTHROPIC_API_KEY', ''));
define('ANTHROPIC_MODEL', env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'));

// CV Template Limits
define('MAX_CV_TEMPLATES_PER_USER', 10); // Maximum number of templates a user can create
define('MAX_TEMPLATE_SIZE_KB', 500); // Maximum size per template (HTML + CSS) in KB

// Timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
