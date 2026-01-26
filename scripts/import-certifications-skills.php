<?php
/**
 * Import certifications and skills from an export JSON file into a user's profile (by email).
 * Run on PRODUCTION. Ensures .env points to the production database.
 *
 * Usage: php scripts/import-certifications-skills.php <target_email> <path-to-export.json> [--dry-run]
 * Example: php scripts/import-certifications-skills.php williamjamesellis@outlook.com /path/to/certifications-skills-williamjamesellis-outlook-com-2025-01-25-120000.json
 *
 * --dry-run  Print what would be inserted without writing to the database.
 */

require_once __DIR__ . '/../php/helpers.php';

if ($argc < 3) {
    echo "Usage: php scripts/import-certifications-skills.php <target_email> <path-to-export.json> [--dry-run]\n";
    echo "Example: php scripts/import-certifications-skills.php williamjamesellis@outlook.com /path/to/export.json\n";
    exit(1);
}

$email = trim($argv[1]);
$path  = $argv[2];
$dryRun = in_array('--dry-run', array_slice($argv, 3), true);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Please provide a valid email address.\n";
    exit(1);
}

if (!is_readable($path)) {
    echo "Error: Cannot read file: {$path}\n";
    exit(1);
}

$raw = file_get_contents($path);
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error: Invalid JSON in export file.\n";
    exit(1);
}

if (empty($data['certifications']) && empty($data['skills'])) {
    echo "Error: Export contains no certifications or skills.\n";
    exit(1);
}

$certifications = $data['certifications'] ?? [];
$skills         = $data['skills'] ?? [];

if (!is_array($certifications) || !is_array($skills)) {
    echo "Error: Export format invalid (certifications and skills must be arrays).\n";
    exit(1);
}

$profile = db()->fetchOne("SELECT id FROM profiles WHERE email = ?", [$email]);
if (!$profile) {
    echo "Error: No profile found for email: {$email}. Create the account first.\n";
    exit(1);
}

$profileId = $profile['id'];

if ($dryRun) {
    echo "[DRY RUN] Would import for: {$email}\n";
    echo "  Certifications: " . count($certifications) . "\n";
    echo "  Skills: " . count($skills) . "\n";
    exit(0);
}

$now = date('Y-m-d H:i:s');
$ok  = true;

try {
    db()->beginTransaction();

    foreach ($certifications as $c) {
        $name = isset($c['name']) ? trim((string) $c['name']) : '';
        $issuer = isset($c['issuer']) ? trim((string) $c['issuer']) : '';
        if ($name === '' || $issuer === '') {
            continue;
        }
        $dateObtained = isset($c['date_obtained']) && $c['date_obtained'] !== null && $c['date_obtained'] !== ''
            ? $c['date_obtained']
            : null;
        $expiryDate = isset($c['expiry_date']) && $c['expiry_date'] !== null && $c['expiry_date'] !== ''
            ? $c['expiry_date']
            : null;

        db()->insert('certifications', [
            'id'            => generateUuid(),
            'profile_id'    => $profileId,
            'name'          => $name,
            'issuer'        => $issuer,
            'date_obtained' => $dateObtained,
            'expiry_date'   => $expiryDate,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    }

    foreach ($skills as $s) {
        $name = isset($s['name']) ? trim((string) $s['name']) : '';
        if ($name === '') {
            continue;
        }
        $level    = isset($s['level']) && $s['level'] !== null && $s['level'] !== '' ? trim((string) $s['level']) : null;
        $category = isset($s['category']) && $s['category'] !== null && $s['category'] !== '' ? trim((string) $s['category']) : null;

        db()->insert('skills', [
            'id'         => generateUuid(),
            'profile_id' => $profileId,
            'name'       => $name,
            'level'      => $level,
            'category'   => $category,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    db()->commit();
} catch (Exception $e) {
    db()->rollBack();
    echo "Error during import: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Imported for: {$email}\n";
echo "  Certifications: " . count($certifications) . "\n";
echo "  Skills: " . count($skills) . "\n";
