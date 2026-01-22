# CV Template Security

## Overview

CV templates are now rendered using **Twig templating engine with SandboxExtension**, providing secure template execution without the risks associated with PHP `eval()`.

## Security Implementation

### Twig with SandboxExtension

The application uses Twig 3.x with SandboxExtension to execute user-provided templates securely:

- **No PHP code execution**: Templates use Twig syntax, not PHP
- **Sandboxed environment**: Only explicitly allowed tags, filters, and functions are available
- **Whitelist-based security**: Dangerous operations are blocked by default
- **Compiled templates**: Twig compiles templates to PHP classes (faster and safer)

### Allowed Template Features

**Tags:**
- `{% if %}...{% endif %}` - Conditional statements
- `{% for %}...{% endfor %}` - Loops
- `{% set %}` - Variable assignment

**Filters:**
- `escape` - HTML escaping (equivalent to `e()` function)
- `default` - Default values
- `length` - Array/string length
- `slice` - Array/string slicing
- `join` - Array joining
- `date` - Date formatting
- `upper`, `lower`, `trim` - String manipulation

**Functions:**
- `formatCvDate(date)` - Custom date formatting function (MM/YYYY format)

**Variables:**
- `profile` - User profile data (full_name, email, phone, location, etc.)
- `cvData` - CV sections (work_experience, education, skills, projects, etc.)

### Security Restrictions

The sandbox prevents:
- File system operations
- Database access
- Network requests
- Code execution
- Object method calls
- Object property access
- Access to PHP superglobals
- Any PHP code execution

## Template Syntax Guide

### Output Variables

```twig
{# Simple variable output #}
{{ profile.full_name|escape }}

{# Nested array access #}
{{ cvData.professional_summary.description|escape }}

{# With default value #}
{{ profile.phone|default('Not provided')|escape }}
```

### Conditionals

```twig
{# Check if variable exists #}
{% if profile.email is defined %}
    {{ profile.email|escape }}
{% endif %}

{# Check if array has items #}
{% if cvData.work_experience|length > 0 %}
    {# Show work experience #}
{% endif %}

{# Else statements #}
{% if profile.phone is defined %}
    {{ profile.phone|escape }}
{% else %}
    Not provided
{% endif %}
```

### Loops

```twig
{# Loop through array #}
{% for work in cvData.work_experience %}
    <div>
        <h3>{{ work.position|escape }}</h3>
        <p>{{ work.company_name|escape }}</p>
        <p>{{ formatCvDate(work.start_date) }} - {{ formatCvDate(work.end_date) }}</p>
    </div>
{% endfor %}

{# Loop with existence check #}
{% if cvData.skills|length > 0 %}
    {% for skill in cvData.skills %}
        <span>{{ skill.name|escape }}</span>
    {% endfor %}
{% endif %}
```

### Date Formatting

```twig
{# Format date using custom function #}
{{ formatCvDate(work.start_date) }}

{# Output: MM/YYYY (e.g., "01/2024") #}
```

### Nested Structures

```twig
{# Access nested arrays #}
{% if cvData.professional_summary.strengths|length > 0 %}
    {% for strength in cvData.professional_summary.strengths %}
        <li>{{ strength.name|escape }}</li>
    {% endfor %}
{% endif %}

{# Work experience with responsibility categories #}
{% for work in cvData.work_experience %}
    <h3>{{ work.position|escape }} at {{ work.company_name|escape }}</h3>
    {% if work.responsibility_categories|length > 0 %}
        {% for cat in work.responsibility_categories %}
            <h4>{{ cat.name|escape }}</h4>
            {% if cat.items|length > 0 %}
                <ul>
                    {% for item in cat.items %}
                        <li>{{ item.content|escape }}</li>
                    {% endfor %}
                </ul>
            {% endif %}
        {% endfor %}
    {% endif %}
{% endfor %}
```

## Migration from PHP Templates

If you have existing PHP templates, they can be automatically converted to Twig using the migration script:

```bash
php scripts/migrate-templates-to-twig.php
```

Or test the conversion first:

```bash
php scripts/migrate-templates-to-twig.php --dry-run
```

### PHP to Twig Conversion Examples

| PHP Syntax | Twig Syntax |
|------------|-------------|
| `<?php echo e($profile['full_name']); ?>` | `{{ profile.full_name\|escape }}` |
| `<?php if (!empty($arr)): ?>` | `{% if arr\|length > 0 %}` |
| `<?php foreach ($arr as $item): ?>` | `{% for item in arr %}` |
| `<?php echo formatCvDate($date); ?>` | `{{ formatCvDate(date) }}` |
| `<?php endif; ?>` | `{% endif %}` |
| `<?php endforeach; ?>` | `{% endfor %}` |

## Template Validation

Templates are validated before saving to ensure:
- Valid Twig syntax
- No PHP code (templates must use Twig syntax)
- Proper structure

Validation errors are displayed to users with clear error messages.

## Usage

Templates are automatically rendered when:
- Saved via `/api/update-cv-template.php` (validates Twig syntax)
- Executed via `cv.php` (using `renderTemplate()` function)

## Available CV Data Structure

### Profile
- `profile.full_name`
- `profile.email`
- `profile.phone`
- `profile.location`
- `profile.linkedin_url`
- `profile.bio`
- `profile.photo_url`

### Professional Summary
- `cvData.professional_summary.description`
- `cvData.professional_summary.strengths` (array)

### Work Experience
- `cvData.work_experience` (array)
  - `work.position`
  - `work.company_name`
  - `work.start_date`
  - `work.end_date`
  - `work.description`
  - `work.responsibility_categories` (array)
    - `cat.name`
    - `cat.items` (array)
      - `item.content`

### Education
- `cvData.education` (array)
  - `edu.degree`
  - `edu.institution`
  - `edu.field_of_study`
  - `edu.start_date`
  - `edu.end_date`

### Skills
- `cvData.skills` (array)
  - `skill.name`
  - `skill.category`
  - `skill.level`

### Projects
- `cvData.projects` (array)
  - `project.title`
  - `project.description`
  - `project.start_date`
  - `project.end_date`
  - `project.url`
  - `project.image_url`

### Certifications
- `cvData.certifications` (array)
  - `cert.name`
  - `cert.issuer`
  - `cert.date_obtained`
  - `cert.expiry_date`

### Professional Memberships
- `cvData.memberships` (array)
  - `mem.name`
  - `mem.organisation`
  - `mem.start_date`

### Interests
- `cvData.interests` (array)
  - `interest.name`
  - `interest.description`

## Best Practices

1. **Always escape output**: Use `|escape` filter for all user-generated content
2. **Check existence**: Use `is defined` or `|length > 0` before accessing variables
3. **Handle empty arrays**: Check array length before looping
4. **Use formatCvDate()**: Always use the custom function for date formatting
5. **Test templates**: Preview templates before activating them

## Security Benefits

✅ **Eliminates eval()**: No PHP code execution  
✅ **Sandboxed execution**: Twig SandboxExtension restricts available operations  
✅ **Whitelist-based**: Only explicitly allowed tags, filters, and functions  
✅ **No code injection**: Template syntax cannot execute arbitrary PHP  
✅ **Compiled templates**: Twig compiles to PHP classes (faster, safer)  
✅ **Type safety**: Twig handles type checking and error handling  

## Troubleshooting

### Template Syntax Errors

If you see "Template syntax error", check:
- All `{% %}` tags are properly closed
- All `{{ }}` expressions are properly formatted
- Variable names use dot notation (not array brackets)
- Filters are separated by `|` (pipe)

### Variable Not Found

If a variable is undefined:
- Check if it exists: `{% if variable is defined %}`
- Check if array has items: `{% if array|length > 0 %}`
- Use default filter: `{{ variable|default('fallback')|escape }}`

### Migration Issues

If migration fails:
- Check the error message for specific syntax issues
- Manually convert problematic PHP code to Twig
- Test the converted template before saving

## Deprecated Code

The old PHP-based template execution system (`php/template-security.php`) is deprecated and will be removed in a future version. All templates should use Twig syntax.
