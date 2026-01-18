<?php
/**
 * Authentication functions
 */

require_once __DIR__ . '/database.php';

/**
 * Hash a password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $userId = getUserId();
    return db()->fetchOne(
        "SELECT id, email, full_name, username, is_super_admin FROM profiles WHERE id = ?",
        [$userId]
    );
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Register a new user
 * Note: Password strength should be validated before calling this function
 */
function registerUser($email, $password, $fullName = null) {
    $db = db();

    // Check if email already exists
    $existing = $db->fetchOne(
        "SELECT id FROM profiles WHERE email = ?",
        [$email]
    );

    if ($existing) {
        return ['success' => false, 'error' => 'Email already registered'];
    }

    try {
        $db->beginTransaction();

        // Generate UUID for user ID
        require_once __DIR__ . '/utils.php';
        $userId = generateUuid();

        // Hash password
        $passwordHash = hashPassword($password);

        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Create user record (email not verified yet)
        $db->insert('profiles', [
            'id' => $userId,
            'email' => $email,
            'password_hash' => $passwordHash,
            'full_name' => $fullName,
            'username' => 'user' . substr(str_replace('-', '', $userId), 0, 8),
            'email_verified' => 0,
            'email_verification_token' => $verificationToken,
            'email_verification_expires' => $verificationExpires,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $db->commit();

        // Send verification email
        require_once __DIR__ . '/email.php';
        $emailSent = sendVerificationEmail($email, $fullName, $verificationToken);

        if (!$emailSent && DEBUG) {
            error_log("Warning: Verification email could not be sent to {$email}");
        }

        // Do NOT auto-login - user must verify email first
        return [
            'success' => true,
            'user_id' => $userId,
            'email_sent' => $emailSent,
            'message' => 'Registration successful! Please check your email to verify your account.'
        ];
    } catch (Exception $e) {
        $db->rollback();
        if (DEBUG) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user
 */
function loginUser($email, $password) {
    $user = db()->fetchOne(
        "SELECT id, email, password_hash, email_verified FROM profiles WHERE email = ?",
        [$email]
    );

    if (!$user) {
        return ['success' => false, 'error' => 'Invalid email or password'];
    }

    if (!verifyPassword($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid email or password'];
    }

    // Check if email is verified
    if (empty($user['email_verified']) || $user['email_verified'] == 0) {
        return [
            'success' => false,
            'error' => 'Please verify your email address before logging in. Check your inbox for the verification email.',
            'needs_verification' => true,
            'email' => $email
        ];
    }

    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['logged_in_at'] = time();

    return ['success' => true, 'user_id' => $user['id']];
}

/**
 * Logout user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Get session cookie parameters
    $cookieParams = session_get_cookie_params();
    $cookieName = session_name();
    
    // Delete the session cookie if it exists
    if (isset($_COOKIE[$cookieName])) {
        setcookie(
            $cookieName,
            '',
            time() - 3600,
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Start new session to avoid errors
    session_start();
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Check if user owns a resource (by profile_id)
 */
function ownsResource($table, $resourceId, $profileIdColumn = 'profile_id') {
    if (!isLoggedIn()) {
        return false;
    }

    $userId = getUserId();
    $result = db()->fetchOne(
        "SELECT id FROM {$table} WHERE id = ? AND {$profileIdColumn} = ?",
        [$resourceId, $userId]
    );

    return !empty($result);
}

/**
 * Create a password reset request and send email
 */
function createPasswordResetRequest($email) {
    $db = db();
    $user = $db->fetchOne(
        "SELECT id, email, full_name FROM profiles WHERE email = ?",
        [$email]
    );

    if (!$user) {
        // Avoid disclosing whether the email exists
        return ['success' => true];
    }

    require_once __DIR__ . '/utils.php';
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    try {
        $db->update('profiles', [
            'password_reset_token' => $token,
            'password_reset_expires' => $expiresAt,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);

        require_once __DIR__ . '/email.php';
        $sent = sendPasswordResetEmail($user['email'], $user['full_name'], $token);

        if (!$sent) {
            return ['success' => false, 'error' => 'Unable to send reset email. Please try again later.'];
        }

        return ['success' => true];
    } catch (Exception $e) {
        if (DEBUG) {
            error_log('Password reset request error: ' . $e->getMessage());
        }
        return ['success' => false, 'error' => 'An error occurred. Please try again.'];
    }
}

/**
 * Reset password using token
 */
function resetPasswordWithToken($token, $newPassword) {
    $db = db();
    $user = $db->fetchOne(
        "SELECT id, password_reset_expires FROM profiles WHERE password_reset_token = ?",
        [$token]
    );

    if (!$user) {
        return ['success' => false, 'error' => 'Invalid or expired reset link.'];
    }

    if (empty($user['password_reset_expires']) || strtotime($user['password_reset_expires']) < time()) {
        return ['success' => false, 'error' => 'This reset link has expired. Please request a new one.'];
    }

    $passwordValidation = validatePasswordStrength($newPassword);
    if (!$passwordValidation['valid']) {
        return ['success' => false, 'error' => implode('. ', $passwordValidation['errors'])];
    }

    try {
        $db->update('profiles', [
            'password_hash' => hashPassword($newPassword),
            'password_reset_token' => null,
            'password_reset_expires' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);

        return ['success' => true];
    } catch (Exception $e) {
        if (DEBUG) {
            error_log('Password reset error: ' . $e->getMessage());
        }
        return ['success' => false, 'error' => 'Unable to reset password. Please try again.'];
    }
}

/**
 * Change password for logged-in user
 */
function changePasswordForUser($userId, $currentPassword, $newPassword) {
    $db = db();
    $user = $db->fetchOne(
        "SELECT id, password_hash FROM profiles WHERE id = ?",
        [$userId]
    );

    if (!$user) {
        return ['success' => false, 'error' => 'User not found.'];
    }

    if (!verifyPassword($currentPassword, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Current password is incorrect.'];
    }

    $passwordValidation = validatePasswordStrength($newPassword);
    if (!$passwordValidation['valid']) {
        return ['success' => false, 'error' => implode('. ', $passwordValidation['errors'])];
    }

    try {
        $db->update('profiles', [
            'password_hash' => hashPassword($newPassword),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        return ['success' => true];
    } catch (Exception $e) {
        if (DEBUG) {
            error_log('Change password error: ' . $e->getMessage());
        }
        return ['success' => false, 'error' => 'Unable to change password. Please try again.'];
    }
}

/**
 * Send username reminder email
 */
function sendUsernameReminder($email) {
    $db = db();
    $user = $db->fetchOne(
        "SELECT id, full_name, username FROM profiles WHERE email = ?",
        [$email]
    );

    if (!$user) {
        // Avoid disclosing whether the email exists
        return ['success' => true];
    }

    require_once __DIR__ . '/email.php';
    $sent = sendUsernameReminderEmail($email, $user['full_name'], $user['username']);

    if (!$sent) {
        return ['success' => false, 'error' => 'Unable to send reminder email. Please try again later.'];
    }

    return ['success' => true];
}
