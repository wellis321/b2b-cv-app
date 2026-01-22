<?php
/**
 * Migrate CV Templates from PHP to Twig
 * 
 * This script converts all existing PHP templates in the database to Twig syntax
 * 
 * Usage: php scripts/migrate-templates-to-twig.php [--dry-run] [--force]
 */

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/template-converter.php';
require_once __DIR__ . '/../php/twig-template-service.php';

$dryRun = in_array('--dry-run', $argv);
$force = in_array('--force', $argv);

echo "========================================\n";
echo "CV Template Migration: PHP to Twig\n";
echo "========================================\n\n";

if ($dryRun) {
    echo "DRY RUN MODE - No changes will be saved\n\n";
}

// Get all templates from database
$templates = db()->fetchAll("SELECT id, user_id, template_name, template_html, template_css FROM cv_templates");

if (empty($templates)) {
    echo "No templates found in database.\n";
    exit(0);
}

echo "Found " . count($templates) . " template(s) to migrate.\n\n";

$successCount = 0;
$errorCount = 0;
$skippedCount = 0;

foreach ($templates as $template) {
    echo "Processing: {$template['template_name']} (ID: {$template['id']})\n";
    
    $phpTemplate = $template['template_html'];
    
    // Check if template is already Twig (has {{ or {% but no <?php)
    $hasTwig = preg_match('/\{\{|\{%/', $phpTemplate);
    $hasPhp = preg_match('/<\?php/', $phpTemplate);
    
    if ($hasTwig && !$hasPhp) {
        echo "  ✓ Already using Twig syntax, skipping...\n\n";
        $skippedCount++;
        continue;
    }
    
    if (!$hasPhp) {
        echo "  ⚠ No PHP code found, skipping...\n\n";
        $skippedCount++;
        continue;
    }
    
    // Convert PHP to Twig
    echo "  Converting PHP to Twig...\n";
    $conversionResult = convertAndValidateTemplate($phpTemplate);
    
    if (!$conversionResult['success']) {
        echo "  ✗ Conversion failed:\n";
        foreach ($conversionResult['errors'] as $error) {
            echo "    - $error\n";
        }
        echo "\n";
        $errorCount++;
        continue;
    }
    
    $twigTemplate = $conversionResult['twigTemplate'];
    
    // Show preview of conversion
    $phpLines = substr_count($phpTemplate, "\n") + 1;
    $twigLines = substr_count($twigTemplate, "\n") + 1;
    echo "  ✓ Conversion successful (PHP: {$phpLines} lines → Twig: {$twigLines} lines)\n";
    
    // Show sample of converted template
    $sample = substr($twigTemplate, 0, 200);
    echo "  Preview: " . str_replace(["\n", "\r"], " ", $sample) . "...\n";
    
    if (!$dryRun) {
        // Update template in database
        try {
            db()->update('cv_templates', [
                'template_html' => $twigTemplate,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$template['id']]);
            
            echo "  ✓ Template updated in database\n\n";
            $successCount++;
        } catch (Exception $e) {
            echo "  ✗ Database update failed: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    } else {
        echo "  [DRY RUN] Would update template in database\n\n";
        $successCount++;
    }
}

echo "========================================\n";
echo "Migration Summary\n";
echo "========================================\n";
echo "Successfully migrated: {$successCount}\n";
echo "Errors: {$errorCount}\n";
echo "Skipped: {$skippedCount}\n";
echo "Total processed: " . count($templates) . "\n\n";

if ($dryRun) {
    echo "This was a dry run. Run without --dry-run to apply changes.\n";
} else {
    echo "Migration complete!\n";
}


