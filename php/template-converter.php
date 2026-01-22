<?php
/**
 * PHP to Twig Template Converter
 * 
 * Converts PHP template syntax to Twig syntax for migration
 */

/**
 * Convert PHP template syntax to Twig syntax
 * 
 * @param string $phpTemplate The PHP template content
 * @return string Twig template content
 */
function convertPhpToTwig($phpTemplate) {
    $twig = $phpTemplate;
    
    // 1. Convert PHP echo statements with e() function
    // <?php echo e($var); ?> -> {{ var|escape }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+e\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*;\s*\?>/',
        function($matches) {
            return '{{ ' . $matches[1] . '|escape }}';
        },
        $twig
    );
    
    // 2. Convert PHP echo statements with array access
    // <?php echo e($profile['key']); ?> -> {{ profile.key|escape }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+e\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([a-zA-Z_][a-zA-Z0-9_]*)\2\]\s*\)\s*;\s*\?>/',
        function($matches) {
            return '{{ ' . $matches[1] . '.' . $matches[3] . '|escape }}';
        },
        $twig
    );
    
    // 3. Convert nested array access
    // <?php echo e($cvData['work_experience'][0]['company_name']); ?> -> {{ cvData.work_experience[0].company_name|escape }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+e\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)(\[[^\]]+\])+\s*\)\s*;\s*\?>/',
        function($matches) {
            $var = $matches[1];
            $access = $matches[0];
            // Extract all array accesses
            preg_match_all('/\[([^\]]+)\]/', $access, $keys);
            $path = $var;
            foreach ($keys[1] as $key) {
                $key = trim($key, '\'"');
                if (is_numeric($key)) {
                    $path .= '[' . $key . ']';
                } else {
                    $path .= '.' . $key;
                }
            }
            return '{{ ' . $path . '|escape }}';
        },
        $twig
    );
    
    // 4. Convert formatCvDate function calls
    // <?php echo formatCvDate($date); ?> -> {{ formatCvDate(date) }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+formatCvDate\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*;\s*\?>/',
        function($matches) {
            return '{{ formatCvDate(' . $matches[1] . ') }}';
        },
        $twig
    );
    
    // 5. Convert formatCvDate with array access
    // <?php echo formatCvDate($work['start_date']); ?> -> {{ formatCvDate(work.start_date) }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+formatCvDate\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([a-zA-Z_][a-zA-Z0-9_]*)\2\]\s*\)\s*;\s*\?>/',
        function($matches) {
            return '{{ formatCvDate(' . $matches[1] . '.' . $matches[3] . ') }}';
        },
        $twig
    );
    
    // 6. Convert if statements
    // <?php if (!empty($var)): ?> -> {% if var|length > 0 %}
    $twig = preg_replace_callback(
        '/<\?php\s*if\s*\(\s*!\s*empty\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*\)\s*:\s*\?>/',
        function($matches) {
            return '{% if ' . $matches[1] . '|length > 0 %}';
        },
        $twig
    );
    
    // 7. Convert if statements with array access
    // <?php if (!empty($cvData['memberships'])): ?> -> {% if cvData.memberships|length > 0 %}
    $twig = preg_replace_callback(
        '/<\?php\s*if\s*\(\s*!\s*empty\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([a-zA-Z_][a-zA-Z0-9_]*)\2\]\s*\)\s*\)\s*:\s*\?>/',
        function($matches) {
            return '{% if ' . $matches[1] . '.' . $matches[3] . '|length > 0 %}';
        },
        $twig
    );
    
    // 8. Convert isset checks
    // <?php if (isset($var['key'])): ?> -> {% if var.key is defined %}
    $twig = preg_replace_callback(
        '/<\?php\s*if\s*\(\s*isset\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([a-zA-Z_][a-zA-Z0-9_]*)\2\]\s*\)\s*\)\s*:\s*\?>/',
        function($matches) {
            return '{% if ' . $matches[1] . '.' . $matches[3] . ' is defined %}';
        },
        $twig
    );
    
    // 9. Convert foreach loops
    // <?php foreach ($arr as $item): ?> -> {% for item in arr %}
    $twig = preg_replace_callback(
        '/<\?php\s*foreach\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s+as\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*:\s*\?>/',
        function($matches) {
            return '{% for ' . $matches[2] . ' in ' . $matches[1] . ' %}';
        },
        $twig
    );
    
    // 10. Convert foreach loops with array access
    // <?php foreach ($cvData['work_experience'] as $work): ?> -> {% for work in cvData.work_experience %}
    $twig = preg_replace_callback(
        '/<\?php\s*foreach\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([a-zA-Z_][a-zA-Z0-9_]*)\2\]\s+as\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*:\s*\?>/',
        function($matches) {
            return '{% for ' . $matches[4] . ' in ' . $matches[1] . '.' . $matches[3] . ' %}';
        },
        $twig
    );
    
    // 11. Convert endforeach
    // <?php endforeach; ?> -> {% endfor %}
    $twig = preg_replace('/<\?php\s*endforeach\s*;\s*\?>/', '{% endfor %}', $twig);
    
    // 12. Convert endif
    // <?php endif; ?> -> {% endif %}
    $twig = preg_replace('/<\?php\s*endif\s*;\s*\?>/', '{% endif %}', $twig);
    
    // 13. Convert else
    // <?php else: ?> -> {% else %}
    $twig = preg_replace('/<\?php\s*else\s*:\s*\?>/', '{% else %}', $twig);
    
    // 14. Convert elseif
    // <?php elseif (!empty($var)): ?> -> {% elseif var|length > 0 %}
    $twig = preg_replace_callback(
        '/<\?php\s*elseif\s*\(\s*!\s*empty\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*\)\s*:\s*\?>/',
        function($matches) {
            return '{% elseif ' . $matches[1] . '|length > 0 %}';
        },
        $twig
    );
    
    // 15. Convert simple echo without e() (should use escape filter)
    // <?php echo $var; ?> -> {{ var|escape }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*;\s*\?>/',
        function($matches) {
            return '{{ ' . $matches[1] . '|escape }}';
        },
        $twig
    );
    
    // 16. Convert array access in echo without e()
    // <?php echo $profile['key']; ?> -> {{ profile.key|escape }}
    $twig = preg_replace_callback(
        '/<\?php\s*echo\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([a-zA-Z_][a-zA-Z0-9_]*)\2\]\s*;\s*\?>/',
        function($matches) {
            return '{{ ' . $matches[1] . '.' . $matches[3] . '|escape }}';
        },
        $twig
    );
    
    // 17. Remove any remaining PHP tags that weren't converted
    // This catches edge cases and outputs a warning
    $remainingPhpTags = preg_match_all('/<\?php[^?]*\?>/', $twig, $matches);
    if ($remainingPhpTags > 0) {
        error_log("Warning: Some PHP tags could not be converted to Twig: " . count($matches[0]) . " instances found");
    }
    
    return $twig;
}

/**
 * Convert a template and validate the result
 * 
 * @param string $phpTemplate The PHP template content
 * @return array ['success' => bool, 'twigTemplate' => string, 'errors' => array]
 */
function convertAndValidateTemplate($phpTemplate) {
    $twigTemplate = convertPhpToTwig($phpTemplate);
    
    require_once __DIR__ . '/twig-template-service.php';
    $validation = validateTwigTemplate($twigTemplate);
    
    return [
        'success' => $validation['valid'],
        'twigTemplate' => $twigTemplate,
        'errors' => $validation['valid'] ? [] : [$validation['error']]
    ];
}


