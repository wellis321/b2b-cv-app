<?php
/**
 * Export CV Data for a Specific User
 * Exports all CV data for a user by email address to SQL INSERT statements
 * for import into Hostinger database
 */

require_once __DIR__ . '/../php/helpers.php';

// Get email from command line or use default
$email = $argv[1] ?? 'williamjamesellis@outlook.com';

echo "Exporting CV data for: $email\n";

// Find user by email
$user = db()->fetchOne(
    "SELECT * FROM profiles WHERE email = ?",
    [$email]
);

if (!$user) {
    die("Error: User with email '$email' not found in database.\n");
}

$userId = $user['id'];
$outputFile = __DIR__ . '/../database/user_cv_export_' . date('Y-m-d_His') . '.sql';

echo "User ID: $userId\n";
echo "Output file: $outputFile\n\n";

// Open output file
$fp = fopen($outputFile, 'w');
if (!$fp) {
    die("Cannot create output file: $outputFile\n");
}

// Write header
fwrite($fp, "-- CV Data Export for User: $email\n");
fwrite($fp, "-- User ID: $userId\n");
fwrite($fp, "-- Exported: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- This file contains INSERT statements for all CV data\n");
fwrite($fp, "-- Make sure tables already exist before importing this data\n");
fwrite($fp, "\n");
fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($fp, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
fwrite($fp, "\n");

try {
    $db = db();
    
    // List of tables to export (in dependency order)
    // For multi-level joins, use 'join_chain' with array of [table, field] pairs
    $tables = [
        'profiles' => ['profile_id' => 'id'],
        'professional_summary' => ['profile_id' => 'profile_id'],
        'professional_summary_strengths' => ['profile_id' => 'professional_summary_id', 'join_table' => 'professional_summary', 'join_field' => 'id'],
        'work_experience' => ['profile_id' => 'profile_id'],
        'responsibility_categories' => ['profile_id' => 'work_experience_id', 'join_table' => 'work_experience', 'join_field' => 'id'],
        'responsibility_items' => [
            'profile_id' => 'category_id',
            'join_chain' => [
                ['responsibility_categories', 'id', 'work_experience_id'],
                ['work_experience', 'id', 'profile_id']
            ]
        ],
        'education' => ['profile_id' => 'profile_id'],
        'skills' => ['profile_id' => 'profile_id'],
        'projects' => ['profile_id' => 'profile_id'],
        'certifications' => ['profile_id' => 'profile_id'],
        'professional_memberships' => ['profile_id' => 'profile_id'],
        'interests' => ['profile_id' => 'profile_id'],
        'professional_qualification_equivalence' => ['profile_id' => 'profile_id'],
        'supporting_evidence' => ['profile_id' => 'qualification_equivalence_id', 'join_table' => 'professional_qualification_equivalence', 'join_field' => 'id'],
    ];
    
    $exportedCount = 0;
    
    foreach ($tables as $tableName => $config) {
        $profileField = $config['profile_id'];
        
        // Check if table exists
        $tableExists = $db->fetchOne("SHOW TABLES LIKE '$tableName'");
        if (!$tableExists) {
            echo "⚠ Skipping table '$tableName' (does not exist)\n";
            continue;
        }
        
        // Build query based on table type
        if (isset($config['join_chain'])) {
            // Multi-level join (e.g., responsibility_items -> categories -> work_experience -> profile)
            // Format: [['table', 'join_field'], ['table', 'join_field', 'foreign_field'], ...]
            $joinChain = $config['join_chain'];
            $query = "SELECT t.* FROM `$tableName` t";
            $prevAlias = 't';
            $prevField = $profileField;
            
            foreach ($joinChain as $index => $join) {
                $joinTable = $join[0];
                $joinField = $join[1];  // Field in join table to match on
                $foreignField = $join[2] ?? $joinField;  // Field in previous table (defaults to join_field)
                $nextAlias = 'j' . ($index + 1);
                
                // Join: previous_alias.foreign_field = join_table.join_field
                $query .= " INNER JOIN `$joinTable` $nextAlias ON $prevAlias.$prevField = $nextAlias.$joinField";
                
                $prevAlias = $nextAlias;
                $prevField = $foreignField;
            }
            
            // Final WHERE clause uses the last join's profile_id
            $finalAlias = 'j' . count($joinChain);
            $query .= " WHERE $finalAlias.profile_id = ?";
        } elseif (isset($config['join_table'])) {
            // Single-level join
            $joinTable = $config['join_table'];
            $joinField = $config['join_field'];
            $query = "SELECT t.* FROM `$tableName` t
                      INNER JOIN `$joinTable` j ON t.$profileField = j.$joinField
                      WHERE j.profile_id = ?";
        } else {
            // Direct relationship to profile
            $query = "SELECT * FROM `$tableName` WHERE $profileField = ?";
        }
        
        $rows = $db->fetchAll($query, [$userId]);
        
        if (empty($rows)) {
            echo "○ No data in table: $tableName\n";
            continue;
        }
        
        fwrite($fp, "-- =====================================================\n");
        fwrite($fp, "-- Data for table: $tableName ($profileField)\n");
        fwrite($fp, "-- Records: " . count($rows) . "\n");
        fwrite($fp, "-- =====================================================\n");
        fwrite($fp, "\n");
        
        // Get column names
        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';
        
        // Generate INSERT statements
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $col) {
                $value = $row[$col];
                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_numeric($value) && !is_string($value)) {
                    $values[] = $value;
                } else {
                    // Escape special characters for SQL
                    $value = str_replace('\\', '\\\\', $value);
                    $value = str_replace("'", "\\'", $value);
                    $value = str_replace("\n", "\\n", $value);
                    $value = str_replace("\r", "\\r", $value);
                    $value = str_replace("\0", "\\0", $value);
                    $values[] = "'$value'";
                }
            }
            
            $valuesList = implode(', ', $values);
            fwrite($fp, "INSERT IGNORE INTO `$tableName` ($columnList) VALUES ($valuesList);\n");
        }
        
        fwrite($fp, "\n");
        echo "✓ Exported " . count($rows) . " record(s) from: $tableName\n";
        $exportedCount += count($rows);
    }
    
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
    fwrite($fp, "\n");
    fwrite($fp, "-- Export complete. Total records exported: $exportedCount\n");
    
    fclose($fp);
    
    echo "\n";
    echo "✓ Export complete!\n";
    echo "✓ File: $outputFile\n";
    echo "✓ Total records exported: $exportedCount\n";
    echo "\n";
    echo "To import into Hostinger:\n";
    echo "1. Open phpMyAdmin on Hostinger\n";
    echo "2. Select your database\n";
    echo "3. Go to the SQL tab\n";
    echo "4. Copy and paste the contents of: $outputFile\n";
    echo "5. Click 'Go' to execute\n";
    echo "\n";
    echo "Note: Make sure the user account already exists in Hostinger database\n";
    echo "      or update the profile INSERT statement with the correct user ID.\n";
    
} catch (Exception $e) {
    fclose($fp);
    die("Error: " . $e->getMessage() . "\n");
}

