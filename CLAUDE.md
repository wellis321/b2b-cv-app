# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a CV (resume) builder web application converted from SvelteKit to vanilla PHP. Users can create professional CVs with multiple sections (work experience, education, projects, skills, certifications, memberships, interests, professional summary, and qualification equivalence), preview them online at `/cv/@username`, and export to PDF. The application includes a subscription system with Stripe integration offering free, pro monthly, pro annual, and lifetime plans.

## Architecture

### Request Flow
1. Apache's `.htaccess` handles URL rewriting:
   - Storage files route through `api/storage-proxy.php`
   - CV routes: `/cv/@username` → `cv.php?username=username`
   - All other requests fall through to `index.php`
2. All pages require `php/helpers.php` which loads the core system (config, database, auth, security, storage, utils, cv-data, subscriptions, stripe)
3. Pages use a partial-based template system via `partial()` function
4. API endpoints return JSON responses with proper headers

### Database Layer
- MySQL with PDO (prepared statements)
- Single `Database` class (singleton pattern) in `php/database.php`
- Access via `db()` helper function
- Helper methods: `query()`, `fetchOne()`, `fetchAll()`, `insert()`, `update()`, `delete()`
- Transaction support: `beginTransaction()`, `commit()`, `rollback()`

### Core Files (in `php/`)
- **config.php**: Environment variables, constants, `.env` loader
- **database.php**: Database singleton with PDO wrapper
- **auth.php**: Authentication (register, login, logout, password reset, email verification)
- **security.php**: CSRF tokens, input sanitization, validation, rate limiting
- **storage.php**: File upload handling (profile photos, project images)
- **utils.php**: UUID generation, request helpers (`get()`, `post()`, `isPost()`), redirects
- **helpers.php**: Includes all core files, template rendering (`partial()`, `render()`), flash messages, CV section navigation
- **cv-data.php**: Fetch CV data for display/export
- **subscriptions.php**: Subscription management and feature gating
- **stripe.php**: Stripe API integration

### Authentication & Security
- Session-based authentication (`$_SESSION['user_id']`)
- Use `requireAuth()` at the top of protected pages
- Use `isLoggedIn()` to check auth status
- Use `getCurrentUser()` to get user data
- CSRF protection required for all POST requests (use `csrfToken()` in forms, verify with `verifyCsrfToken()`)
- Rate limiting for auth endpoints (see `security.php`)
- Email verification required before login
- All inputs sanitized via `sanitizeInput()` from `security.php`

### Database Schema (see `database/mysql_schema.sql`)
Main tables:
- `profiles`: User accounts with CV profile data (id is UUID, has username, email, password_hash, email_verified, subscription fields)
- `work_experience`: Job history (has `sort_order` for custom ordering, `hide_date` flag)
- `responsibility_categories` and `responsibility_items`: Nested structure for work responsibilities
- `education`, `projects`, `skills`, `certifications`, `professional_memberships`, `interests`, `professional_summary`, `qualification_levels`
- All related tables use `profile_id` foreign key with CASCADE delete

### Template System
- Views in `views/partials/` directory
- Use `partial('name', ['data' => $value])` to include templates
- Flash messages: `setFlash($key, $value)` and `getFlash($key)`
- Page head: `partial('head', ['pageTitle' => '...', 'metaDescription' => '...', 'canonicalUrl' => '...', 'structuredDataType' => '...'])`
- Common partials: `header`, `footer`, `head`, `dashboard`, `home`, `auth-modals`

### File Storage
- Local filesystem storage in `storage/` directory
- Storage URL routing via `api/storage-proxy.php` (security check before serving)
- Use functions from `php/storage.php`: `uploadFile()`, `deleteFile()`, `getStoragePath()`
- Max file size: 5MB (configurable in `config.php`)
- Allowed image types: JPEG, PNG, GIF, WebP

### Stripe Integration
- Test mode credentials in `.env` file
- Price IDs for different plans in config constants
- Checkout session creation: `api/stripe/create-checkout-session.php`
- Customer portal: `api/stripe/create-portal-session.php`
- Webhook handling: `api/stripe/webhook.php` (updates subscription status)
- Functions in `php/stripe.php` and `php/subscriptions.php`

## Development Workflow

### Environment Setup
1. Create `.env` file with database credentials and Stripe keys (see `php/config.php` for variables)
2. Import database schema: `mysql -u user -p database_name < database/mysql_schema.sql`
3. Apply migrations from `database/` directory in chronological order
4. Create storage directory: `mkdir -p storage/uploads && chmod 755 storage/uploads`
5. Ensure Apache mod_rewrite is enabled

### Running Locally
- Use PHP built-in server: `php -S localhost:8000` (from project root)
- Or configure Apache/Nginx with document root pointing to project directory
- Ensure `.htaccess` rules work (Apache) or configure equivalent Nginx rewrites

### Database Migrations
- Migration files in `database/` with naming: `YYYYMMDD_description.sql`
- Run manually in chronological order: `mysql -u user -p database_name < database/filename.sql`
- No automated migration system - apply manually in order

### Debugging
- Set `APP_ENV=development` in `.env` for error display
- Production mode: errors logged to `logs/php-errors.log`
- Check `DEBUG` constant (true in development) for conditional debug output

### Adding New Pages
1. Create `pagename.php` in root directory
2. Start with `require_once __DIR__ . '/php/helpers.php';`
3. Use `requireAuth()` if page requires login
4. Handle POST requests with CSRF verification at the top
5. Fetch data needed for the page
6. Include head partial with SEO metadata
7. Include header and footer partials
8. Use `partial()` for reusable components

### Adding New API Endpoints
1. Create file in `api/` directory (e.g., `api/something.php`)
2. Start with `require_once __DIR__ . '/../php/helpers.php';`
3. Check authentication: `if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); http_response_code(401); exit; }`
4. Verify HTTP method (typically POST)
5. Verify CSRF token for state-changing operations
6. Sanitize inputs
7. Perform database operations
8. Return JSON with `header('Content-Type: application/json')` and `echo json_encode($response)`

### Adding Database Tables
1. Create migration SQL file in `database/` with timestamp prefix
2. Include foreign keys with CASCADE delete where appropriate
3. Add indexes for commonly queried columns
4. Use VARCHAR(36) for UUID primary keys
5. Use TIMESTAMP with DEFAULT CURRENT_TIMESTAMP and ON UPDATE CURRENT_TIMESTAMP for audit fields

## Common Patterns

### Standard Page Structure
```php
<?php
require_once __DIR__ . '/php/helpers.php';
requireAuth(); // if login required

// Handle POST
if (isPost()) {
    if (!verifyCsrfToken(post(CSRF_TOKEN_NAME))) {
        setFlash('error', 'Invalid security token');
        redirect('/current-page.php');
    }
    // Process form...
}

// Fetch data
$userId = getUserId();
$data = db()->fetchAll("SELECT * FROM table WHERE profile_id = ?", [$userId]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', ['pageTitle' => 'Page Title']); ?>
</head>
<body>
    <?php partial('header'); ?>
    <main>
        <!-- Page content -->
    </main>
    <?php partial('footer'); ?>
</body>
</html>
```

### Standard API Endpoint Structure
```php
<?php
require_once __DIR__ . '/../php/helpers.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$token = post(CSRF_TOKEN_NAME);
if (!verifyCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

try {
    $userId = getUserId();
    $data = sanitizeInput(post('data'));

    // Database operations...

    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    if (DEBUG) {
        error_log('API Error: ' . $e->getMessage());
    }
}
```

### Database Operations
```php
// Fetch single row
$user = db()->fetchOne("SELECT * FROM profiles WHERE id = ?", [$userId]);

// Fetch all rows
$items = db()->fetchAll("SELECT * FROM table WHERE profile_id = ? ORDER BY created_at DESC", [$userId]);

// Insert
$id = db()->insert('table_name', [
    'id' => generateUuid(),
    'profile_id' => $userId,
    'field' => $value,
    'created_at' => date('Y-m-d H:i:s')
]);

// Update
db()->update('table_name',
    ['field' => $value, 'updated_at' => date('Y-m-d H:i:s')],
    'id = ?',
    [$id]
);

// Delete
db()->delete('table_name', 'id = ? AND profile_id = ?', [$id, $userId]);

// Transaction
db()->beginTransaction();
try {
    // Multiple operations...
    db()->commit();
} catch (Exception $e) {
    db()->rollback();
    throw $e;
}
```

## Important Notes

### Migrated from SvelteKit
- This codebase was converted from a SvelteKit application (see `docs/CONVERSION_SUMMARY.md`)
- The Svelte standards in `.cursor/rules/svelte_standards.mdc` are NOT applicable - this is a PHP application
- The application overview in `.cursor/rules/overview.mdc` describes the CV section requirements

### Security Considerations
- Never trust user input - always use `sanitizeInput()`
- Always use prepared statements (PDO handles this via `db()` methods)
- Verify resource ownership before updates/deletes: check `profile_id` matches `getUserId()`
- Use `ownsResource($table, $resourceId)` helper to verify ownership
- Rate limiting is enabled for auth endpoints (login, register)
- Email verification required before login
- CSRF tokens required for all state-changing operations

### Subscription & Feature Gating
- Use `hasActiveSubscription()` to check if user has any active paid plan
- Use `isFeatureAvailable($feature)` to check specific feature access
- Use `canAccessPremiumTemplates()`, `canAccessAdvancedExport()`, etc.
- Subscription status updated via Stripe webhooks

### CV Sections
The application supports these CV sections (see `getCvSections()` in `helpers.php`):
1. Professional Summary
2. Work Experience (with nested responsibility categories/items)
3. Education
4. Projects
5. Skills
6. Certifications
7. Professional Qualification Equivalence
8. Professional Memberships
9. Interests & Activities

### URL Routing
- Home: `/` or `/index.php`
- Dashboard: `/dashboard.php`
- Public CV: `/cv/@username` (username must match `^[a-z0-9][a-z0-9\-_]+$`)
- Legacy CV route: `/cv/{uuid}` (for backward compatibility)
- Profile editing: `/profile.php`
- Section editing: `/work-experience.php`, `/education.php`, etc.
- Auth pages: `/forgot-password.php`, `/reset-password.php`, `/verify-email.php`, `/resend-verification.php`

### Date Handling
- User date format preference stored in `profiles.date_format_preference` (default: 'dd/mm/yyyy')
- Database stores dates as DATE type (YYYY-MM-DD)
- Display formatting handled in templates based on user preference

### Error Handling
- Production: errors logged to `logs/php-errors.log`, generic messages shown to users
- Development: detailed errors displayed (set `APP_ENV=development`)
- Use try-catch for database operations and API endpoints
- Return user-friendly error messages, log detailed errors

### Documentation
Extensive documentation in `docs/`:
- Security audits and recommendations
- Production readiness checklists
- Stripe setup instructions
- Database import guide
- SEO improvements
- Analytics setup

### Note on Testing
- No automated test suite currently exists
- Manual testing required for changes
- Test in both development and production environments
- Verify CSRF protection, authentication, and authorization for all endpoints
