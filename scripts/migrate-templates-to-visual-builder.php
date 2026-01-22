<?php
/**
 * Migrate Templates to Visual Builder Format
 * 
 * This script attempts to convert existing Twig templates to visual builder configuration.
 * Note: This is a best-effort conversion and may not perfectly capture all template details.
 * 
 * Usage:
 *   php scripts/migrate-templates-to-visual-builder.php [--dry-run] [--template-id=ID]
 */

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/cv-templates.php';
require_once __DIR__ . '/../php/template-config-schema.php';

$dryRun = in_array('--dry-run', $argv);
$templateId = null;

// Parse command line arguments
foreach ($argv as $arg) {
    if (strpos($arg, '--template-id=') === 0) {
        $templateId = substr($arg, strlen('--template-id='));
    }
}

echo "Template Migration to Visual Builder\n";
echo "====================================\n\n";

if ($dryRun) {
    echo "DRY RUN MODE - No changes will be saved\n\n";
}

// Get templates to migrate
if ($templateId) {
    $template = getCvTemplate($templateId);
    if (!$template) {
        echo "Error: Template not found: {$templateId}\n";
        exit(1);
    }
    $templates = [$template];
} else {
    // Get all templates that don't have builder_type set or are 'code'
    $templates = db()->fetchAll(
        "SELECT * FROM cv_templates WHERE builder_type IS NULL OR builder_type = 'code'"
    );
}

if (empty($templates)) {
    echo "No templates to migrate.\n";
    exit(0);
}

echo "Found " . count($templates) . " template(s) to migrate.\n\n";

$migrated = 0;
$skipped = 0;

foreach ($templates as $template) {
    echo "Processing: {$template['template_name']} (ID: {$template['id']})\n";
    
    // Skip if already has visual builder config
    if (!empty($template['template_config'])) {
        $config = json_decode($template['template_config'], true);
        if ($config && isset($config['layout'])) {
            echo "  - Already has visual builder config, skipping.\n";
            $skipped++;
            continue;
        }
    }
    
    // Create a basic config from the template
    // This is a simplified conversion - we can't perfectly reverse-engineer from Twig
    $config = getDefaultTemplateConfig();
    
    // Try to detect layout from HTML
    $html = $template['template_html'] ?? '';
    if (strpos($html, 'grid-cols-2') !== false || strpos($html, 'two-column') !== false) {
        $config['layout'] = 'two-column';
    } elseif (strpos($html, 'sidebar') !== false || strpos($html, 'grid-cols-3') !== false) {
        $config['layout'] = 'sidebar';
    } else {
        $config['layout'] = 'single-column';
    }
    
    // Try to detect which sections are present
    $sectionIds = [
        'profile' => ['profile', 'full_name', 'email', 'phone'],
        'professional-summary' => ['professional_summary', 'professional-summary'],
        'work-experience' => ['work_experience', 'work-experience', 'work_experience'],
        'education' => ['education'],
        'skills' => ['skills'],
        'projects' => ['projects'],
        'certifications' => ['certifications'],
        'qualification-equivalence' => ['qualification_equivalence', 'qualification-equivalence'],
        'memberships' => ['memberships'],
        'interests' => ['interests'],
    ];
    
    foreach ($config['sections'] as &$section) {
        $sectionId = $section['id'];
        $keywords = $sectionIds[$sectionId] ?? [];
        $found = false;
        foreach ($keywords as $keyword) {
            if (stripos($html, $keyword) !== false) {
                $found = true;
                break;
            }
        }
        $section['enabled'] = $found;
    }
    
    // Try to extract colors from CSS
    $css = $template['template_css'] ?? '';
    if (preg_match('/--color-header:\s*([^;]+)/', $css, $matches)) {
        $config['styling']['colors']['header'] = trim($matches[1]);
    }
    if (preg_match('/--color-accent:\s*([^;]+)/', $css, $matches)) {
        $config['styling']['colors']['accent'] = trim($matches[1]);
    }
    
    // Save the config
    $configJson = json_encode($config, JSON_PRETTY_PRINT);
    
    if (!$dryRun) {
        try {
            db()->update('cv_templates', [
                'template_config' => $configJson,
                'builder_type' => 'visual',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$template['id']]);
            
            echo "  ✓ Migrated successfully\n";
            $migrated++;
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  [DRY RUN] Would migrate with config:\n";
        echo "    Layout: {$config['layout']}\n";
        echo "    Enabled sections: " . count(array_filter($config['sections'], fn($s) => $s['enabled'])) . "\n";
        $migrated++;
    }
    
    echo "\n";
}

echo "\n";
echo "Migration complete!\n";
echo "  Migrated: {$migrated}\n";
echo "  Skipped: {$skipped}\n";

if ($dryRun) {
    echo "\nThis was a dry run. Run without --dry-run to apply changes.\n";
}


