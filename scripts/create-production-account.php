<?php
/**
 * Create a production account with full subscription
 * 
 * Usage: php scripts/create-production-account.php
 * 
 * This script generates SQL to create an account in the production database.
 * Run the generated SQL in your production database (e.g., via phpMyAdmin or MySQL client).
 */

require_once __DIR__ . '/../php/helpers.php';

// Account details
$email = 'REDACTED_EMAIL';
$password = 'REDACTED_PASSWORD';
$fullName = null; // You can set this if you have a name

// Generate UUID
$userId = generateUuid();

// Hash password
$passwordHash = hashPassword($password);

// Generate username
$username = 'user' . substr(str_replace('-', '', $userId), 0, 8);

// Current timestamp
$now = date('Y-m-d H:i:s');

// Generate SQL
$sql = <<<SQL
-- Create account for {$email}
-- Generated on: {$now}
-- 
-- IMPORTANT: Run this SQL in your PRODUCTION database
-- 
-- To run this:
-- 1. Connect to your production database (e.g., via phpMyAdmin)
-- 2. Select your database
-- 3. Go to the SQL tab
-- 4. Paste this entire SQL block
-- 5. Click "Go" or press Ctrl+Enter

-- Check if account already exists
SET @email = '{$email}';
SET @existing_user = (SELECT id FROM profiles WHERE email = @email LIMIT 1);

-- If account exists, update it; otherwise create new
INSERT INTO profiles (
    id,
    email,
    password_hash,
    full_name,
    username,
    email_verified,
    account_type,
    plan,
    subscription_status,
    subscription_current_period_end,
    created_at,
    updated_at
) VALUES (
    IFNULL(@existing_user, '{$userId}'),
    '{$email}',
    '{$passwordHash}',
    NULL,
    '{$username}',
    1,
    'individual',
    'lifetime',
    'active',
    NULL,
    '{$now}',
    '{$now}'
)
ON DUPLICATE KEY UPDATE
    password_hash = '{$passwordHash}',
    plan = 'lifetime',
    subscription_status = 'active',
    subscription_current_period_end = NULL,
    email_verified = 1,
    updated_at = '{$now}';

-- Display the created/updated account
SELECT 
    id,
    email,
    username,
    plan,
    subscription_status,
    email_verified,
    created_at
FROM profiles 
WHERE email = '{$email}';
SQL;

echo "========================================\n";
echo "Production Account Creation SQL\n";
echo "========================================\n\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n";
echo "User ID: {$userId}\n";
echo "Username: {$username}\n";
echo "Plan: lifetime (full subscription)\n";
echo "\n";
echo "========================================\n";
echo "SQL to run in production database:\n";
echo "========================================\n\n";
echo $sql;
echo "\n\n";
echo "========================================\n";
echo "Instructions:\n";
echo "========================================\n";
echo "1. Copy the SQL above (between the SQL markers)\n";
echo "2. Log into your production database (Hostinger)\n";
echo "3. Open phpMyAdmin or your MySQL client\n";
echo "4. Select your database\n";
echo "5. Go to the SQL tab\n";
echo "6. Paste the SQL and execute it\n";
echo "7. Verify the account was created/updated\n";
echo "\n";
echo "The account will have:\n";
echo "- Full lifetime subscription (all Pro features)\n";
echo "- Email verified (can log in immediately)\n";
echo "- Active subscription status\n";
echo "\n";

