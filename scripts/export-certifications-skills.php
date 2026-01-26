<?php
/**
 * Export certifications and skills for a user (by email) to a JSON file.
 * Run on LOCAL to export. Transfer the file securely to production, then run
 * import-certifications-skills.php on production.
 *
 * Usage: php scripts/export-certifications-skills.php <email>
 * Example: php scripts/export-certifications-skills.php williamjamesellis@outlook.com
 *
 * Output: scripts/exports/certifications-skills-{timestamp}.json
 * The export contains NO profile ids or credentials – safe to transfer.
 */

require_once __DIR__ . '/../php/helpers.php';

if ($argc < 2) {
    echo "Usage: php scripts/export-certifications-skills.php <email>\n";
    echo "Example: php scripts/export-certifications-skills.php williamjamesellis@outlook.com\n";
    exit(1);
}

$email = trim($argv[1]);
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Please provide a valid email address.\n";
    exit(1);
}

$profile = db()->fetchOne("SELECT id FROM profiles WHERE email = ?", [$email]);
if (!$profile) {
    echo "Error: No profile found for email: {$email}\n";
    exit(1);
}

$profileId = $profile['id'];

$certifications = db()->fetchAll(
    "SELECT name, issuer, date_obtained, expiry_date FROM certifications WHERE profile_id = ? ORDER BY date_obtained DESC, name ASC",
    [$profileId]
);

$skills = db()->fetchAll(
    "SELECT name, level, category FROM skills WHERE profile_id = ? ORDER BY category ASC, name ASC",
    [$profileId]
);

// Normalise dates for JSON (null or Y-m-d)
foreach ($certifications as &$c) {
    $c['date_obtained'] = $c['date_obtained'] ?: null;
    $c['expiry_date']   = $c['expiry_date'] ?: null;
}
unset($c);

$payload = [
    'version'       => 1,
    'exported_at'   => date('c'),
    'source_email'  => $email,
    'certifications' => $certifications,
    'skills'        => $skills,
];

$exportDir = __DIR__ . '/exports';
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

$safe = preg_replace('/[^a-z0-9]/i', '-', $email);
$safe = trim($safe, '-') ?: 'user';
$file = $exportDir . '/certifications-skills-' . $safe . '-' . date('Y-m-d-His') . '.json';

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    echo "Error: Failed to encode export data.\n";
    exit(1);
}

if (file_put_contents($file, $json) === false) {
    echo "Error: Could not write file: {$file}\n";
    exit(1);
}

echo "Exported for: {$email}\n";
echo "  Certifications: " . count($certifications) . "\n";
echo "  Skills: " . count($skills) . "\n";
echo "  File: {$file}\n";
echo "\nNext: transfer this file securely to production (e.g. SCP/SFTP), then run:\n";
echo "  php scripts/import-certifications-skills.php {$email} /path/to/certifications-skills-{$safe}-*.json\n";
