<?php
/**
 * Custom Homepage Guide
 * Documentation for creating custom homepages for organisations
 */

require_once __DIR__ . '/../php/helpers.php';

// Require authentication and admin access
$org = requireOrganisationAccess('admin');

$pageTitle = 'Custom Homepage Guide | ' . e($org['organisation_name']);
$metaDescription = 'Learn how to create and customise your organisation\'s public homepage.';
$canonicalUrl = APP_URL . '/agency/custom-homepage-guide.php';

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
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Custom Homepage Guide</h1>
                <p class="text-lg text-gray-600">
                    Learn how to create a fully customised homepage for your organisation's public page.
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
                                    <a href="#getting-started" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Getting Started</a>
                                </li>
                                <li>
                                    <a href="#placeholders" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Using Placeholders</a>
                                </li>
                                <li>
                                    <a href="#html-basics" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">HTML Basics</a>
                                </li>
                                <li>
                                    <a href="#css-styling" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">CSS Styling</a>
                                </li>
                                <li>
                                    <a href="#examples" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Examples</a>
                                </li>
                                <li>
                                    <a href="#best-practices" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Best Practices</a>
                                </li>
                                <li>
                                    <a href="#troubleshooting" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-md transition-colors">Troubleshooting</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1 min-w-0">

            <!-- Getting Started -->
            <section id="getting-started" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Getting Started</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">What is a Custom Homepage?</h3>
                        <p>
                            A custom homepage allows you to create a unique landing page for your organisation's public URL at 
                            <code class="bg-gray-100 px-2 py-1 rounded text-sm">/agency/<?php echo e($org['slug']); ?></code>. 
                            Instead of using the default template, you can design your own page using HTML and CSS.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">How to Enable</h3>
                        <ol class="list-decimal list-inside space-y-2 ml-4">
                            <li>Go to <strong>Settings → Custom Homepage</strong></li>
                            <li>Check the <strong>"Enable Custom Homepage"</strong> checkbox</li>
                            <li>Enter your custom HTML in the <strong>"Custom HTML"</strong> textarea</li>
                            <li>Enter your custom CSS in the <strong>"Custom CSS"</strong> textarea (optional)</li>
                            <li>Click <strong>"Save Custom Homepage"</strong></li>
                            <li>Click the preview link to see your changes live</li>
                        </ol>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700">
                            <strong>Tip:</strong> You can toggle between custom and default homepage anytime. If you disable the custom homepage or leave the HTML empty, the default template will be shown.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Using Placeholders -->
            <section id="placeholders" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Using Placeholders</h2>
                
                <p class="text-gray-700 mb-4">
                    Placeholders are special codes that get replaced with actual values from your organisation. Use them in your HTML to display dynamic content.
                </p>

                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placeholder</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Example Output</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{organisation_name}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">Your organisation's name</td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($org['organisation_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{organisation_slug}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">URL-friendly identifier</td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($org['slug']); ?></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{logo_url}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">Your organisation's logo URL (empty if not set)</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php 
                                    $orgData = getOrganisationById($org['organisation_id']);
                                    echo !empty($orgData['logo_url']) ? 'https://example.com/logo.png' : '(empty)';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{primary_colour}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">Primary brand colour (hex code)</td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($orgData['primary_colour'] ?? '#4338ca'); ?></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{secondary_colour}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">Secondary brand colour (hex code)</td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($orgData['secondary_colour'] ?? '#7e22ce'); ?></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{candidate_count}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">Number of candidates (formatted with commas)</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php 
                                    $count = db()->fetchOne("SELECT COUNT(*) as count FROM profiles WHERE organisation_id = ? AND account_type = 'candidate'", [$org['organisation_id']])['count'] ?? 0;
                                    echo number_format($count);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono bg-gray-50"><code>{{public_url}}</code></td>
                                <td class="px-4 py-3 text-sm text-gray-700">Full URL to your public page</td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo APP_URL; ?>/agency/<?php echo e($org['slug']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700">
                        <strong>Important:</strong> Placeholders must use double curly braces exactly as shown (e.g., <code class="bg-white px-1 py-0.5 rounded text-xs">{{organisation_name}}</code>). 
                        They are case-sensitive and must match exactly.
                    </p>
                </div>
            </section>

            <!-- HTML Basics -->
            <section id="html-basics" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">HTML Basics</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">What HTML Should I Write?</h3>
                        <p>
                            Write the HTML content that should appear inside the <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;main&gt;</code> tag of your page. 
                            Don't include <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;html&gt;</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;head&gt;</code>, or <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;body&gt;</code> tags - these are already provided.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Basic Structure</h3>
                        <p class="mb-2">A typical custom homepage HTML might look like this:</p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;div class="hero-section"&gt;
  &lt;h1&gt;{{organisation_name}}&lt;/h1&gt;
  &lt;p&gt;Welcome to our organisation...&lt;/p&gt;
&lt;/div&gt;

&lt;div class="features"&gt;
  &lt;h2&gt;What We Offer&lt;/h2&gt;
  &lt;ul&gt;
    &lt;li&gt;Feature 1&lt;/li&gt;
    &lt;li&gt;Feature 2&lt;/li&gt;
  &lt;/ul&gt;
&lt;/div&gt;</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Using Placeholders in HTML</h3>
                        <p class="mb-2">Placeholders can be used anywhere in your HTML:</p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;h1&gt;{{organisation_name}}&lt;/h1&gt;
&lt;img src="{{logo_url}}" alt="{{organisation_name}}"&gt;
&lt;div style="color: {{primary_colour}}"&gt;Coloured text&lt;/div&gt;
&lt;p&gt;We manage {{candidate_count}} candidates.&lt;/p&gt;</code></pre>
                    </div>
                </div>
            </section>

            <!-- CSS Styling -->
            <section id="css-styling" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">CSS Styling</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Adding Styles</h3>
                        <p>
                            Enter your CSS in the <strong>"Custom CSS"</strong> textarea. Your CSS will be added to a <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;style&gt;</code> tag in the page head, 
                            so you can style any elements in your HTML.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Example CSS</h3>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>.hero-section {
  background: linear-gradient(135deg, {{primary_colour}} 0%, {{secondary_colour}} 100%);
  padding: 4rem 2rem;
  color: white;
  text-align: center;
}

.features {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  padding: 3rem 2rem;
}

.features h2 {
  grid-column: 1 / -1;
  font-size: 2rem;
  margin-bottom: 1rem;
}</code></pre>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700">
                            <strong>Tip:</strong> You can use placeholders in CSS too! For example, use <code class="bg-white px-1 py-0.5 rounded text-xs">{{primary_colour}}</code> in your CSS to match your brand colours.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Examples -->
            <section id="examples" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Complete Examples</h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Example 1: Simple Hero Section</h3>
                        
                        <p class="text-sm text-gray-600 mb-2"><strong>HTML:</strong></p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm mb-4"><code>&lt;div class="hero"&gt;
  &lt;h1&gt;Welcome to {{organisation_name}}&lt;/h1&gt;
  &lt;p class="tagline"&gt;Professional CV Management Services&lt;/p&gt;
  &lt;a href="/?register=1" class="cta-button"&gt;Get Started&lt;/a&gt;
&lt;/div&gt;</code></pre>

                        <p class="text-sm text-gray-600 mb-2"><strong>CSS:</strong></p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>.hero {
  background: linear-gradient(135deg, {{primary_colour}}, {{secondary_colour}});
  padding: 6rem 2rem;
  text-align: center;
  color: white;
}

.hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.tagline {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.9;
}

.cta-button {
  display: inline-block;
  padding: 1rem 2rem;
  background: white;
  color: {{primary_colour}};
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: bold;
}</code></pre>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Example 2: Features Grid</h3>
                        
                        <p class="text-sm text-gray-600 mb-2"><strong>HTML:</strong></p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm mb-4"><code>&lt;div class="features-grid"&gt;
  &lt;div class="feature"&gt;
    &lt;h3&gt;CV Management&lt;/h3&gt;
    &lt;p&gt;Professional CV creation and management&lt;/p&gt;
  &lt;/div&gt;
  &lt;div class="feature"&gt;
    &lt;h3&gt;{{candidate_count}} Candidates&lt;/h3&gt;
    &lt;p&gt;Currently managed in our system&lt;/p&gt;
  &lt;/div&gt;
  &lt;div class="feature"&gt;
    &lt;h3&gt;Real-Time Updates&lt;/h3&gt;
    &lt;p&gt;Instant CV updates for all candidates&lt;/p&gt;
  &lt;/div&gt;
&lt;/div&gt;</code></pre>

                        <p class="text-sm text-gray-600 mb-2"><strong>CSS:</strong></p>
                        <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  padding: 4rem 2rem;
}

.feature {
  padding: 2rem;
  border: 2px solid {{primary_colour}}20;
  border-radius: 0.5rem;
  background: white;
}

.feature h3 {
  color: {{primary_colour}};
  margin-bottom: 1rem;
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Best Practices -->
            <section id="best-practices" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Best Practices</h2>
                
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">✓ Do's</h3>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Test your homepage on different screen sizes (mobile, tablet, desktop)</li>
                            <li>Use placeholders to keep content dynamic</li>
                            <li>Keep HTML/CSS organized and commented</li>
                            <li>Use semantic HTML elements (<code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;header&gt;</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;section&gt;</code>, etc.)</li>
                            <li>Preview your changes before enabling publicly</li>
                            <li>Keep file sizes reasonable (HTML max 500KB, CSS max 100KB)</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">✗ Don'ts</h3>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Don't include <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;html&gt;</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;head&gt;</code>, or <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">&lt;body&gt;</code> tags</li>
                            <li>Don't use inline JavaScript or external scripts (security restrictions)</li>
                            <li>Don't use placeholders inside CSS selectors (only in CSS property values)</li>
                            <li>Don't forget to test with different placeholder values</li>
                            <li>Don't use very large images or media files (link to external resources instead)</li>
                        </ul>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700">
                            <strong>Pro Tip:</strong> Start with the default template as a reference. Open your public page, view the page source, and see how the default homepage is structured. Then customise it to match your needs.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Troubleshooting -->
            <section id="troubleshooting" class="bg-white rounded-lg shadow p-6 mb-8 scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Troubleshooting</h2>
                
                <div class="space-y-4">
                    <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Placeholders Not Replacing</h3>
                        <p class="text-sm text-gray-700">
                            Make sure placeholders use exactly two curly braces on each side: <code class="bg-white px-1 py-0.5 rounded text-xs">{{placeholder}}</code>. 
                            They are case-sensitive. Check spelling and spacing.
                        </p>
                    </div>

                    <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Styles Not Applying</h3>
                        <p class="text-sm text-gray-700">
                            Make sure your CSS selectors match your HTML elements. Use browser developer tools (F12) to inspect elements and check if styles are being applied.
                        </p>
                    </div>

                    <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Page Looks Broken</h3>
                        <p class="text-sm text-gray-700">
                            Check for unclosed HTML tags, missing quotes around attribute values, and syntax errors. Validate your HTML and CSS using online validators.
                        </p>
                    </div>

                    <div class="border-l-4 border-blue-400 bg-blue-50 p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Still Need Help?</h3>
                        <p class="text-sm text-gray-700">
                            If you're having trouble, try disabling the custom homepage to see the default template, then gradually add your customisations. 
                            You can also contact support for assistance.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Quick Links -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="/agency/settings.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        Back to Settings →
                    </a>
                    <a href="/agency/<?php echo e($org['slug']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 border-2 border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 text-sm font-medium">
                        Preview Public Page →
                    </a>
                </div>
            </div>
                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <style>
        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }
        
        /* Active sidebar link highlighting */
        nav a.active {
            background-color: #eff6ff;
            color: #2563eb;
            font-weight: 500;
        }
    </style>
    <script>
        // Smooth scrolling for sidebar navigation links
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('aside nav a[href^="#"]');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        // Calculate offset for header (96px = top-24)
                        const headerOffset = 96;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });

        // Highlight active sidebar link on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('[id^="getting-started"], [id^="placeholders"], [id^="html-basics"], [id^="css-styling"], [id^="examples"], [id^="best-practices"], [id^="troubleshooting"]');
            const navLinks = document.querySelectorAll('aside nav a');
            
            function updateActiveLink() {
                let current = '';
                const scrollPosition = window.scrollY + 150; // Offset for header
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                        current = section.getAttribute('id');
                    }
                });
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            }
            
            window.addEventListener('scroll', updateActiveLink);
            updateActiveLink(); // Initial call
        });
    </script>
</body>
</html>

