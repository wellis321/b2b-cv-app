<?php
/**
 * Export Database Data Script
 * Exports all data from the local database to SQL INSERT statements
 * for import into phpMyAdmin on Hostinger
 */

require_once __DIR__ . '/../php/helpers.php';

$outputFile = __DIR__ . '/../database/data_export_for_hostinger.sql';

// Open output file
$fp = fopen($outputFile, 'w');
if (!$fp) {
    die("Cannot create output file: $outputFile\n");
}

// Write header
fwrite($fp, "-- Database Data Export for Hostinger\n");
fwrite($fp, "-- Exported: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- This file contains only INSERT statements (no table structures)\n");
fwrite($fp, "-- Make sure tables already exist before importing this data\n");
fwrite($fp, "\n");
fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($fp, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
fwrite($fp, "\n");

try {
    $db = db();
    $pdo = $db->getConnection();
    
    // Get all tables
    $tables = $db->fetchAll("SHOW TABLES");
    $tableKey = "Tables_in_" . DB_NAME;
    
    $exportedTables = [];
    
    foreach ($tables as $tableRow) {
        $tableName = $tableRow[$tableKey];
        
        // Skip if no data
        $count = $db->fetchOne("SELECT COUNT(*) as cnt FROM `$tableName`")['cnt'] ?? 0;
        if ($count == 0) {
            continue;
        }
        
        fwrite($fp, "-- =====================================================\n");
        fwrite($fp, "-- Data for table: $tableName\n");
        fwrite($fp, "-- =====================================================\n");
        fwrite($fp, "\n");
        
        // Get all data from table
        $rows = $db->fetchAll("SELECT * FROM `$tableName`");
        
        if (empty($rows)) {
            fwrite($fp, "-- No data in table\n\n");
            continue;
        }
        
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
        $exportedTables[] = $tableName;
    }
    
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
    fwrite($fp, "\n");
    fwrite($fp, "-- Export complete. Exported " . count($exportedTables) . " table(s) with data.\n");
    fwrite($fp, "-- Tables exported: " . implode(', ', $exportedTables) . "\n");
    
    fclose($fp);
    
    echo "✓ Data export complete!\n";
    echo "✓ File: $outputFile\n";
    echo "✓ Tables exported: " . count($exportedTables) . "\n";
    echo "✓ Tables: " . implode(', ', $exportedTables) . "\n";
    
} catch (Exception $e) {
    fclose($fp);
    die("Error: " . $e->getMessage() . "\n");
}

