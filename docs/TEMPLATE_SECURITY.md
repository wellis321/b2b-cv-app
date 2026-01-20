# CV Template Security

## Security Issue

**CRITICAL**: Allowing users to upload and edit PHP code in CV templates is a significant security risk if not properly sanitized.

### Original Risk

The original implementation used `eval()` directly on user-provided code without sanitization:

```php
eval('?>' . $customHtml);
```

This allowed users to execute **any PHP code**, including:
- File system operations (read/write/delete files)
- Database access
- System commands
- Access to environment variables
- Access to other users' data
- Code injection attacks

### Security Measures Implemented

1. **Pre-execution Sanitization** (`sanitizeTemplateCode()`)
   - Strips dangerous PHP functions (eval, exec, system, file operations, etc.)
   - Blocks direct superglobal access (`$_GET`, `$_POST`, etc.)
   - Removes shell execution (backticks)
   - Blocks include/require statements

2. **Pre-save Validation** (`validateTemplateCode()`)
   - Validates templates before saving to database
   - Blocks dangerous patterns at upload time
   - Provides clear error messages to users

3. **Whitelist Approach**
   - Only allows specific PHP constructs:
     - Variable access: `$profile`, `$cvData`, `$work`, `$education`, etc.
     - Safe functions: `e()`, `formatCvDate()`
     - Control structures: `if`, `foreach`, `empty`, `isset`
     - Basic operators and string concatenation

### Remaining Risks

⚠️ **Important**: Even with sanitization, using `eval()` is inherently risky. The current implementation:

- ✅ Blocks most dangerous functions
- ✅ Validates before saving
- ✅ Uses whitelist approach
- ⚠️ Still uses `eval()` (inherently risky)
- ⚠️ Complex obfuscation could potentially bypass checks

### Recommendations

1. **Current Implementation**: Acceptable for CV templates where users need PHP for dynamic content
   - Sanitization significantly reduces attack surface
   - Validation prevents most dangerous code from being saved
   - Suitable for trusted users (logged-in users only)

2. **Future Improvements**:
   - Consider migrating to a template engine (Twig, Smarty) with built-in security
   - Implement additional sandboxing (disable_functions in php.ini)
   - Add rate limiting on template updates
   - Consider server-side template compilation

3. **Monitoring**:
   - Monitor error logs for template execution errors
   - Watch for suspicious patterns in saved templates
   - Regular security audits

### Usage

Templates are automatically sanitized when:
- Saved via `/api/update-cv-template.php`
- Executed via `cv.php` (using `executeTemplateSecurely()`)

Users will see an error message if they try to save dangerous code.

### Allowed Template Code Examples

✅ **Safe**:
```php
<?php echo e($profile['full_name']); ?>
<?php if (!empty($cvData['work_experience'])): ?>
    <?php foreach ($cvData['work_experience'] as $work): ?>
        <p><?php echo e($work['company_name']); ?></p>
    <?php endforeach; ?>
<?php endif; ?>
```

❌ **Blocked**:
```php
<?php system('rm -rf /'); ?>
<?php file_get_contents('/etc/passwd'); ?>
<?php eval($_GET['code']); ?>
<?php include($_POST['file']); ?>
```

