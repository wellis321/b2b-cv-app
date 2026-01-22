<?php
/**
 * Twig Template Service with SandboxExtension
 * 
 * Secure template rendering using Twig with sandbox restrictions
 * Replaces the insecure eval()-based template execution system
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Extension\SandboxExtension;
use Twig\Sandbox\SecurityPolicy;

/**
 * Get or create Twig environment with sandbox restrictions
 * 
 * @return Environment
 */
function getTwigEnvironment() {
    static $twig = null;
    
    if ($twig !== null) {
        return $twig;
    }
    
    // Create loader (we'll use ArrayLoader for dynamic templates)
    $loader = new ArrayLoader();
    
    // Create Twig environment
    $twig = new Environment($loader, [
        'autoescape' => 'html',
        'cache' => false, // Disable cache for dynamic templates
        'debug' => false,
    ]);
    
    // Configure sandbox security policy
    // Only allow safe tags, filters, and functions
    $tags = ['if', 'for', 'set'];
    $filters = ['escape', 'default', 'length', 'slice', 'join', 'date', 'upper', 'lower', 'trim'];
    $methods = []; // No object method calls allowed
    $properties = []; // No object property access allowed
    $functions = ['formatCvDate'];
    
    $policy = new SecurityPolicy($tags, $filters, $methods, $properties, $functions);
    
    // Add sandbox extension
    $twig->addExtension(new SandboxExtension($policy, true));
    
    // Add custom formatCvDate function
    $formatCvDateFunction = new \Twig\TwigFunction('formatCvDate', function ($date, $format = null) {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        
        // Format as MM/YYYY (month/year only, matching original implementation)
        return date('m/Y', $timestamp);
    }, ['is_safe' => ['html']]);
    
    $twig->addFunction($formatCvDateFunction);
    
    return $twig;
}

/**
 * Render a Twig template securely
 * 
 * @param string $templateContent The Twig template content
 * @param array $variables Variables to pass to template (e.g., ['profile' => $profile, 'cvData' => $cvData])
 * @return string Rendered HTML output
 */
function renderTemplate($templateContent, $variables = []) {
    try {
        $twig = getTwigEnvironment();
        
        // Create a unique template name for this template content
        $templateName = 'template_' . md5($templateContent);
        
        // Set the template in the loader
        $loader = $twig->getLoader();
        if ($loader instanceof ArrayLoader) {
            $loader->setTemplate($templateName, $templateContent);
        }
        
        // Render the template
        $output = $twig->render($templateName, $variables);
        
        return $output;
    } catch (\Twig\Error\SyntaxError $e) {
        // Log syntax errors
        error_log("Twig template syntax error: " . $e->getMessage());
        return '<div class="error p-4 bg-red-100 border border-red-400 text-red-700 rounded">Template syntax error. Please check your template code.</div>';
    } catch (\Twig\Error\RuntimeError $e) {
        // Log runtime errors
        error_log("Twig template runtime error: " . $e->getMessage());
        return '<div class="error p-4 bg-red-100 border border-red-400 text-red-700 rounded">Template execution error. Please check your template code.</div>';
    } catch (\Twig\Error\LoaderError $e) {
        // Log loader errors
        error_log("Twig template loader error: " . $e->getMessage());
        return '<div class="error p-4 bg-red-100 border border-red-400 text-red-700 rounded">Template loading error. Please check your template code.</div>';
    } catch (\Throwable $e) {
        // Log any other errors
        error_log("Twig template error: " . $e->getMessage());
        return '<div class="error p-4 bg-red-100 border border-red-400 text-red-700 rounded">Template error. Please check your template code.</div>';
    }
}

/**
 * Validate Twig template syntax
 * 
 * @param string $templateContent The Twig template content to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateTwigTemplate($templateContent) {
    try {
        $twig = getTwigEnvironment();
        
        // Create a unique template name for validation
        $templateName = 'validate_' . md5($templateContent);
        
        // Try to parse the template
        $loader = $twig->getLoader();
        if ($loader instanceof ArrayLoader) {
            $loader->setTemplate($templateName, $templateContent);
        }
        
        // Parse the template (this will throw an exception if syntax is invalid)
        $twig->parse($twig->tokenize(new \Twig\Source($templateContent, $templateName)));
        
        return ['valid' => true, 'error' => null];
    } catch (\Twig\Error\SyntaxError $e) {
        return [
            'valid' => false,
            'error' => 'Template syntax error: ' . $e->getMessage()
        ];
    } catch (\Throwable $e) {
        return [
            'valid' => false,
            'error' => 'Template validation error: ' . $e->getMessage()
        ];
    }
}


