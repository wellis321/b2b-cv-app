<?php
/**
 * CV Template Guide for Agencies
 * Documentation for creating custom CV templates using Twig
 */

require_once __DIR__ . '/../php/helpers.php';

// Require authentication and admin access
$org = requireOrganisationAccess('admin');

$pageTitle = 'CV Template Guide | ' . e($org['organisation_name']);
$metaDescription = 'Learn how to create custom CV templates for your candidates using Twig syntax.';
$canonicalUrl = APP_URL . '/agency/cv-template-guide.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle,
        'metaDescription' => $metaDescription,
        'canonicalUrl' => $canonicalUrl,
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('agency/header'); ?>

    <main id="main-content" class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <a href="/agency/settings.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium mb-4">
                    ← Back to Settings
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">CV Template Guide</h1>
                <p class="text-lg text-gray-600">
                    Learn how to create custom CV templates for your candidates using secure Twig syntax.
                </p>
            </div>

            <!-- Two Column Layout: Sidebar + Content -->
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sticky Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="sticky top-24">
                        <nav class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wide">Contents</h2>
                            <ul class="space-y-1">
                                <li>
                                    <a href="#overview" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Overview</a>
                                </li>
                                <li>
                                    <a href="#twig-basics" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Twig Basics</a>
                                </li>
                                <li>
                                    <a href="#available-data" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Available Data</a>
                                </li>
                                <li>
                                    <a href="#examples" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Examples</a>
                                </li>
                                <li>
                                    <a href="#filters-functions" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Filters & Functions</a>
                                </li>
                                <li>
                                    <a href="#best-practices" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Best Practices</a>
                                </li>
                                <li>
                                    <a href="#security" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Security</a>
                                </li>
                                <li>
                                    <a href="#troubleshooting" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Troubleshooting</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1 min-w-0 space-y-6">

            <!-- Overview -->
            <section id="overview" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Overview</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">What are Custom CV Templates?</h3>
                        <p>
                            Custom CV templates allow you to create unique, branded CV designs for your candidates. 
                            Templates use <strong>Twig</strong> syntax, a secure templating language that prevents code injection 
                            while providing powerful features for dynamic content.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Why Twig?</h3>
                        <p>
                            Twig is a secure, sandboxed templating engine that:
                        </p>
                        <ul class="list-disc list-inside space-y-1 ml-4 mt-2">
                            <li>Prevents code injection and security vulnerabilities</li>
                            <li>Provides a clean, readable syntax</li>
                            <li>Supports loops, conditionals, and filters</li>
                            <li>Automatically escapes output for security</li>
                            <li>Is widely used and well-documented</li>
                        </ul>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-4">
                        <p class="text-sm text-blue-700">
                            <strong>Note:</strong> CV template customization is currently available to super admins. 
                            Contact support if you need access to create custom templates for your organisation.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Twig Basics -->
            <section id="twig-basics" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Twig Basics</h2>
                
                <div class="space-y-6 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Output Variables</h3>
                        <p class="mb-3">Display data using double curly braces:</p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{{ profile.full_name|escape }}
{{ cvData.professional_summary.description|escape }}</code></pre>
                        <p class="mt-2 text-sm text-gray-600">
                            Always use the <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">|escape</code> filter to prevent XSS attacks.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Conditionals</h3>
                        <p class="mb-3">Check if data exists before displaying:</p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{% if profile.email is defined %}
    {{ profile.email|escape }}
{% endif %}

{% if cvData.work_experience|length > 0 %}
    {# Show work experience #}
{% endif %}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Loops</h3>
                        <p class="mb-3">Iterate through arrays:</p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{% for work in cvData.work_experience %}
    <div>
        <h3>{{ work.position|escape }}</h3>
        <p>{{ work.company_name|escape }}</p>
    </div>
{% endfor %}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Array Access</h3>
                        <p class="mb-3">Use dot notation for nested data:</p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{# Instead of PHP: $profile['full_name'] #}
{{ profile.full_name|escape }}

{# Nested access #}
{{ cvData.professional_summary.description|escape }}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Available Data -->
            <section id="available-data" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Available Data</h2>
                
                <div class="space-y-6 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Profile Data</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>profile.full_name
profile.email
profile.phone
profile.location
profile.linkedin_url
profile.bio
profile.photo_url</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Professional Summary</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>cvData.professional_summary.description
cvData.professional_summary.strengths (array)
  - strength.name</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Work Experience</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>cvData.work_experience (array)
  - work.position
  - work.company_name
  - work.start_date
  - work.end_date
  - work.description
  - work.responsibility_categories (array)
    - cat.name
    - cat.items (array)
      - item.content</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Education</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>cvData.education (array)
  - edu.degree
  - edu.institution
  - edu.field_of_study
  - edu.start_date
  - edu.end_date</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Skills</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>cvData.skills (array)
  - skill.name
  - skill.category
  - skill.level</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Projects</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>cvData.projects (array)
  - project.title
  - project.description
  - project.start_date
  - project.end_date
  - project.url
  - project.image_url</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Other Sections</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>cvData.certifications (array)
cvData.memberships (array)
cvData.interests (array)</code></pre>
                    </div>
                </div>
            </section>

            <!-- Examples -->
            <section id="examples" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Examples</h2>
                
                <div class="space-y-6 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Basic Profile Header</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code><div class="bg-blue-600 text-white p-8">
    <h1 class="text-3xl font-bold">{{ profile.full_name|escape }}</h1>
    {% if profile.email is defined %}
        <p class="mt-2">{{ profile.email|escape }}</p>
    {% endif %}
    {% if profile.phone is defined %}
        <p>{{ profile.phone|escape }}</p>
    {% endif %}
    {% if profile.location is defined %}
        <p>{{ profile.location|escape }}</p>
    {% endif %}
</div></code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Work Experience Section</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{% if cvData.work_experience|length > 0 %}
    <section class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Work Experience</h2>
        {% for work in cvData.work_experience %}
            <div class="mb-6 pb-6 border-b">
                <h3 class="text-xl font-semibold">{{ work.position|escape }}</h3>
                <p class="text-gray-600">{{ work.company_name|escape }}</p>
                <p class="text-sm text-gray-500">
                    {{ formatCvDate(work.start_date) }} - 
                    {% if work.end_date %}
                        {{ formatCvDate(work.end_date) }}
                    {% else %}
                        Present
                    {% endif %}
                </p>
                {% if work.description %}
                    <p class="mt-2">{{ work.description|escape }}</p>
                {% endif %}
            </div>
        {% endfor %}
    </section>
{% endif %}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Skills with Categories</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{% if cvData.skills|length > 0 %}
    <section class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Skills</h2>
        {% for skill in cvData.skills %}
            <span class="inline-block bg-gray-200 px-3 py-1 rounded-full mr-2 mb-2">
                {{ skill.name|escape }}
            </span>
        {% endfor %}
    </section>
{% endif %}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Professional Summary with Strengths</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"><code>{% if cvData.professional_summary.description %}
    <section class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Professional Summary</h2>
        <p>{{ cvData.professional_summary.description|escape }}</p>
        
        {% if cvData.professional_summary.strengths|length > 0 %}
            <ul class="mt-4 list-disc list-inside">
                {% for strength in cvData.professional_summary.strengths %}
                    <li>{{ strength.name|escape }}</li>
                {% endfor %}
            </ul>
        {% endif %}
    </section>
{% endif %}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Filters & Functions -->
            <section id="filters-functions" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Available Filters & Functions</h2>
                
                <div class="space-y-6 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Filters</h3>
                        <div class="space-y-2">
                            <div>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">|escape</code>
                                <span class="ml-2">HTML escaping (always use for user content)</span>
                            </div>
                            <div>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">|default('value')</code>
                                <span class="ml-2">Default value if variable is empty</span>
                            </div>
                            <div>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">|length</code>
                                <span class="ml-2">Get array or string length</span>
                            </div>
                            <div>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">|upper</code>
                                <span class="ml-2">Convert to uppercase</span>
                            </div>
                            <div>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">|lower</code>
                                <span class="ml-2">Convert to lowercase</span>
                            </div>
                            <div>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">|trim</code>
                                <span class="ml-2">Remove whitespace</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Functions</h3>
                        <div>
                            <code class="bg-gray-100 px-2 py-1 rounded text-sm">formatCvDate(date)</code>
                            <span class="ml-2">Format date as MM/YYYY (e.g., "01/2024")</span>
                            <pre class="bg-gray-100 p-3 rounded-lg mt-2 text-sm"><code>{{ formatCvDate(work.start_date) }}</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Best Practices -->
            <section id="best-practices" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Best Practices</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">1. Always Escape Output</h3>
                        <p>Use the <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">|escape</code> filter to prevent XSS attacks:</p>
                        <pre class="bg-gray-100 p-3 rounded-lg mt-2"><code>{# Good #}
{{ profile.full_name|escape }}

{# Bad - security risk #}
{{ profile.full_name }}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">2. Check Before Accessing</h3>
                        <p>Always check if data exists before using it:</p>
                        <pre class="bg-gray-100 p-3 rounded-lg mt-2"><code>{# Good #}
{% if profile.email is defined %}
    {{ profile.email|escape }}
{% endif %}

{# Check array length #}
{% if cvData.skills|length > 0 %}
    {# Show skills #}
{% endif %}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">3. Use Semantic HTML</h3>
                        <p>Use proper HTML5 semantic elements for better accessibility:</p>
                        <pre class="bg-gray-100 p-3 rounded-lg mt-2"><code><section>
    <h2>Work Experience</h2>
    <article>
        <h3>Job Title</h3>
        <p>Company Name</p>
    </article>
</section></code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">4. Mobile Responsive</h3>
                        <p>Use Tailwind CSS classes for responsive design:</p>
                        <pre class="bg-gray-100 p-3 rounded-lg mt-2"><code><div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {# Content adapts to screen size #}
</div></code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">5. Print-Friendly</h3>
                        <p>Consider print styles for PDF generation:</p>
                        <pre class="bg-gray-100 p-3 rounded-lg mt-2"><code><style>
@media print {
    .no-print { display: none; }
    .page-break { page-break-after: always; }
}
</style></code></pre>
                    </div>
                </div>
            </section>

            <!-- Security -->
            <section id="security" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Security</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div class="bg-green-50 border-l-4 border-green-400 p-4">
                        <h3 class="text-lg font-semibold text-green-900 mb-2">Why Twig is Secure</h3>
                        <ul class="list-disc list-inside space-y-1 text-green-800">
                            <li><strong>Sandboxed execution:</strong> Only safe operations are allowed</li>
                            <li><strong>No PHP code:</strong> Templates cannot execute arbitrary PHP</li>
                            <li><strong>Automatic escaping:</strong> Output is escaped by default</li>
                            <li><strong>Whitelist-based:</strong> Only approved tags, filters, and functions</li>
                            <li><strong>No file access:</strong> Cannot read or write files</li>
                            <li><strong>No database access:</strong> Cannot execute SQL queries</li>
                            <li><strong>No network access:</strong> Cannot make HTTP requests</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">What's Blocked</h3>
                        <p>The Twig sandbox prevents:</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>File system operations</li>
                            <li>Database queries</li>
                            <li>Network requests</li>
                            <li>Code execution</li>
                            <li>Object method calls</li>
                            <li>Access to PHP superglobals</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Troubleshooting -->
            <section id="troubleshooting" class="bg-white rounded-lg shadow p-6 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Troubleshooting</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Template Syntax Errors</h3>
                        <p>If you see "Template syntax error":</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Check that all <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{% %}</code> tags are properly closed</li>
                            <li>Ensure all <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{ }}</code> expressions are properly formatted</li>
                            <li>Verify variable names use dot notation (not brackets)</li>
                            <li>Check that filters are separated by <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">|</code> (pipe)</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Variable Not Found</h3>
                        <p>If a variable is undefined:</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Check if it exists: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{% if variable is defined %}</code></li>
                            <li>Check if array has items: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{% if array|length > 0 %}</code></li>
                            <li>Use default filter: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{ variable|default('fallback')|escape }}</code></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Common Mistakes</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-semibold">Using PHP syntax instead of Twig:</p>
                                <pre class="bg-red-50 p-2 rounded text-sm"><code><?php echo htmlspecialchars('{# Wrong #}
<?php echo $profile[\'full_name\']; ?>'); ?></code></pre>
                                <pre class="bg-green-50 p-2 rounded text-sm mt-1"><code>{# Correct #}
{{ profile.full_name|escape }}</code></pre>
                            </div>
                            <div>
                                <p class="font-semibold">Forgetting to escape output:</p>
                                <pre class="bg-red-50 p-2 rounded text-sm"><code>{# Wrong - security risk #}
{{ profile.full_name }}</code></pre>
                                <pre class="bg-green-50 p-2 rounded text-sm mt-1"><code>{# Correct #}
{{ profile.full_name|escape }}</code></pre>
                            </div>
                            <div>
                                <p class="font-semibold">Using array brackets instead of dot notation:</p>
                                <pre class="bg-red-50 p-2 rounded text-sm"><code>{# Wrong #}
{{ profile['full_name']|escape }}</code></pre>
                                <pre class="bg-green-50 p-2 rounded text-sm mt-1"><code>{# Correct #}
{{ profile.full_name|escape }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Additional Resources -->
            <section class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-3">Additional Resources</h2>
                <ul class="space-y-2 text-gray-700">
                    <li>
                        <a href="/docs/TEMPLATE_SECURITY.md" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                            Full Template Security Documentation
                        </a>
                    </li>
                    <li>
                        <a href="https://twig.symfony.com/doc/" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                            Official Twig Documentation
                        </a>
                    </li>
                    <li>
                        <a href="/agency/settings.php" class="text-blue-600 hover:text-blue-800 underline">
                            Back to Organisation Settings
                        </a>
                    </li>
                </ul>
            </section>

                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>
</body>
</html>

