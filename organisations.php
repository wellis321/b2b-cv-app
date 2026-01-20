<?php
/**
 * For Organisations - Documentation and Guide
 * Information for recruitment agencies and organisations
 */

require_once __DIR__ . '/php/helpers.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'For Organisations | Simple CV Builder',
        'metaDescription' => 'Learn how recruitment agencies and organisations can use Simple CV Builder to manage candidate CVs efficiently.',
        'canonicalUrl' => APP_URL . '/organisations.php',
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" role="main">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                        For Recruitment Agencies & Organisations
                    </h1>
                    <p class="mt-4 text-xl text-blue-100 max-w-3xl mx-auto">
                        Manage your candidates' CVs efficiently with our powerful B2B platform. Streamline your recruitment process and provide professional CV management for your candidates.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="bg-white border-b border-gray-200 sticky top-16 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex space-x-8 overflow-x-auto py-4" aria-label="Documentation sections">
                    <a href="#getting-started" class="whitespace-nowrap text-sm font-medium text-gray-700 hover:text-blue-600">Getting Started</a>
                    <a href="#managing-candidates" class="whitespace-nowrap text-sm font-medium text-gray-700 hover:text-blue-600">Managing Candidates</a>
                    <a href="#team-management" class="whitespace-nowrap text-sm font-medium text-gray-700 hover:text-blue-600">Team Management</a>
                    <a href="#settings" class="whitespace-nowrap text-sm font-medium text-gray-700 hover:text-blue-600">Settings & Configuration</a>
                    <a href="#limits" class="whitespace-nowrap text-sm font-medium text-gray-700 hover:text-blue-600">Limits & Upgrades</a>
                    <a href="#faq" class="whitespace-nowrap text-sm font-medium text-gray-700 hover:text-blue-600">FAQ</a>
                </nav>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Getting Started -->
            <section id="getting-started" class="mb-16 scroll-mt-64">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Getting Started</h2>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">1. Create Your Organisation Account</h3>
                        <p class="text-gray-700 mb-6">
                            To get started, you'll need to have an organisation account created. Fill out the form below with your organisation details and we'll set up your account. Once your organisation is set up, you'll receive an invitation to join.
                        </p>
                        
                        <form id="organisation-request-form" class="space-y-4">
                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                            
                            <div>
                                <label for="organisation_name" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Organisation Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="organisation_name" name="organisation_name" required
                                       class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                       placeholder="Enter your organisation's full legal or trading name">
                            </div>

                            <div>
                                <label for="contact_name" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Primary Contact Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="contact_name" name="contact_name" required
                                       class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                       placeholder="The name of the person who will be the organisation owner/admin">
                            </div>

                            <div>
                                <label for="contact_email" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Contact Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="contact_email" name="contact_email" required
                                       class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                       placeholder="This will be used for the owner/admin account">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="expected_candidates" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Expected Number of Candidates <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="expected_candidates" name="expected_candidates" required min="1"
                                           class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                           placeholder="e.g., 50">
                                </div>

                                <div>
                                    <label for="expected_team_members" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Expected Number of Team Members <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="expected_team_members" name="expected_team_members" required min="1"
                                           class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                           placeholder="e.g., 5">
                                </div>
                            </div>

                            <div>
                                <label for="organisation_type" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Organisation Type
                                </label>
                                <select id="organisation_type" name="organisation_type"
                                        class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                    <option value="">Select organisation type...</option>
                                    <option value="Recruitment Agency">Recruitment Agency</option>
                                    <option value="HR Department">HR Department</option>
                                    <option value="Employment Agency">Employment Agency</option>
                                    <option value="Talent Acquisition">Talent Acquisition</option>
                                    <option value="Outplacement Service">Outplacement Service</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="additional_requirements" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Additional Requirements or Notes
                                </label>
                                <textarea id="additional_requirements" name="additional_requirements" rows="4"
                                          class="w-full rounded-lg border-2 border-gray-300 px-4 py-2 text-base focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none"
                                          placeholder="Any specific needs, customisations, or additional information..."></textarea>
                            </div>

                            <div id="form-message" class="hidden rounded-lg p-4 text-sm font-medium"></div>

                            <button type="submit" 
                                    class="w-full md:w-auto bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold text-base hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Submit Request
                            </button>
                        </form>

                        <p class="text-gray-600 text-sm bg-blue-50 border-l-4 border-blue-500 p-4 rounded mt-6">
                            <strong>Note:</strong> Once we receive your request, we'll review it and set up your organisation account. You'll then receive an invitation email at the contact email address you provided with instructions to access your dashboard.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">2. Accept Your Team Invitation</h3>
                        <p class="text-gray-700 mb-4">
                            When you're invited to join an organisation, you'll receive an email invitation. Click the link in the email to:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-4">
                            <li>Set up your account password</li>
                            <li>Complete your profile information</li>
                            <li>Access the organisation dashboard</li>
                        </ol>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">3. Access the Agency Dashboard</h3>
                        <p class="text-gray-700 mb-4">
                            Once logged in, you'll be automatically redirected to the Agency Dashboard. Here you can:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li>View organisation statistics and usage</li>
                            <li>See recent candidates and activity</li>
                            <li>Quick access to all organisation features</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Managing Candidates -->
            <section id="managing-candidates" class="mb-16 scroll-mt-64">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Managing Candidates</h2>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Inviting Candidates</h3>
                        <p class="text-gray-700 mb-4">
                            To invite a candidate to create their CV:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-4 mb-4">
                            <li>Navigate to <strong>Candidates</strong> in the main menu</li>
                            <li>Click <strong>"Invite Candidate"</strong></li>
                            <li>Enter the candidate's email address</li>
                            <li>Optionally add a personal message</li>
                            <li>Click <strong>"Send Invitation"</strong></li>
                        </ol>
                        <p class="text-gray-600 text-sm">
                            The candidate will receive an email with a link to create their account and start building their CV.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Viewing Candidate CVs</h3>
                        <p class="text-gray-700 mb-4">
                            Once a candidate has created their CV, you can:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li>View their CV directly from the Candidates page</li>
                            <li>Access their online CV link to view the live version</li>
                            <li>Download their CV as a PDF</li>
                            <li>See which CV variant they're using (candidates can create multiple CV versions for different applications)</li>
                            <li>Check CV visibility settings (private, organisation-only, or public)</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Candidate Self-Registration</h3>
                        <p class="text-gray-700 mb-4">
                            If enabled in your organisation settings, candidates can register themselves using your organisation's registration link. You can:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li>Enable/disable self-registration in Settings</li>
                            <li>Require approval for self-registered candidates</li>
                            <li>Set default CV visibility for new candidates</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Searching and Filtering</h3>
                        <p class="text-gray-700 mb-4">
                            The Candidates page includes powerful search and filter options:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li>Search by candidate name or email</li>
                            <li>Filter by recruiter (if you're a recruiter, see only your candidates)</li>
                            <li>View all candidates (for admins and owners)</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Team Management -->
            <section id="team-management" class="mb-16 scroll-mt-64">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Team Management</h2>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Team Roles</h3>
                        <p class="text-gray-700 mb-4">
                            Your organisation supports different roles with varying permissions:
                        </p>
                        <div class="space-y-3">
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h4 class="font-semibold text-gray-900">Owner</h4>
                                <p class="text-sm text-gray-600">Full access to all features, including organisation settings, billing, and team management.</p>
                            </div>
                            <div class="border-l-4 border-green-500 pl-4">
                                <h4 class="font-semibold text-gray-900">Admin</h4>
                                <p class="text-sm text-gray-600">Can manage candidates, team members, and organisation settings (except billing).</p>
                            </div>
                            <div class="border-l-4 border-yellow-500 pl-4">
                                <h4 class="font-semibold text-gray-900">Recruiter</h4>
                                <p class="text-sm text-gray-600">Can invite and manage their own candidates, view all candidates.</p>
                            </div>
                            <div class="border-l-4 border-gray-400 pl-4">
                                <h4 class="font-semibold text-gray-900">Viewer</h4>
                                <p class="text-sm text-gray-600">Read-only access to view candidates and their CVs.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Inviting Team Members</h3>
                        <p class="text-gray-700 mb-4">
                            Owners and Admins can invite team members:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-4">
                            <li>Go to <strong>Team</strong> in the main menu</li>
                            <li>Click <strong>"Invite Team Member"</strong></li>
                            <li>Enter the team member's email address</li>
                            <li>Select their role (Admin, Recruiter, or Viewer)</li>
                            <li>Optionally add a personal message</li>
                            <li>Click <strong>"Send Invitation"</strong></li>
                        </ol>
                        <p class="text-gray-600 text-sm mt-4">
                            <strong>Note:</strong> Only Owners can invite Admins. Make sure you haven't reached your team member limit.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Managing Team Members</h3>
                        <p class="text-gray-700 mb-4">
                            From the Team page, you can:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li>View all team members and their roles</li>
                            <li>See when team members joined</li>
                            <li>Change team member roles (Owners and Admins)</li>
                            <li>Remove team members (Owners and Admins)</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Settings & Configuration -->
            <section id="settings" class="mb-16 scroll-mt-64">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Settings & Configuration</h2>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">General Settings</h3>
                        <p class="text-gray-700 mb-4">
                            Configure your organisation's basic information:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>Organisation Name:</strong> Your organisation's display name</li>
                            <li><strong>URL Slug:</strong> A unique identifier for your organisation's URL</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Branding</h3>
                        <p class="text-gray-700 mb-4">
                            Customise your organisation's branding:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>Logo:</strong> Upload your organisation logo (JPEG, PNG, GIF, or WebP, max 2MB)</li>
                            <li><strong>Primary Colour:</strong> Main brand colour used in candidate CVs</li>
                            <li><strong>Secondary Colour:</strong> Secondary brand colour for accents</li>
                        </ul>
                        <p class="text-gray-600 text-sm mt-4">
                            These branding elements will appear in your candidates' CVs when they use your organisation's templates.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Candidate Settings</h3>
                        <p class="text-gray-700 mb-4">
                            Control how candidates interact with your organisation:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>Default CV Visibility:</strong> Set the default privacy level for new candidate CVs (Private, Organisation, or Public)</li>
                            <li><strong>Allow Candidate Self-Registration:</strong> Enable candidates to register themselves</li>
                            <li><strong>Require Candidate Approval:</strong> Require approval for self-registered candidates before they can complete their CV</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Custom Public Homepage</h3>
                        <p class="text-gray-700 mb-4">
                            Create a fully customised public landing page for your organisation at <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">/agency/your-slug</code>:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>AI-Powered Template Generation:</strong> Describe your vision or link to a reference website, and AI will generate a custom HTML/CSS template</li>
                            <li><strong>Full HTML/CSS Control:</strong> Write your own custom HTML and CSS for complete design freedom</li>
                            <li><strong>Dynamic Placeholders:</strong> Use placeholders like <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{organisation_name}}</code> and <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{candidate_count}}</code> for dynamic content</li>
                            <li><strong>Professional Branding:</strong> Showcase your organisation with a unique public presence</li>
                        </ul>
                        <p class="text-gray-600 text-sm mt-4">
                            Access this feature in Settings → Custom Homepage. <a href="/agency/custom-homepage-guide.php" class="text-blue-600 hover:text-blue-800 underline">View the full guide</a> for detailed instructions.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Organisation AI Settings</h3>
                        <p class="text-gray-700 mb-4">
                            All candidates get browser-based AI by default - no configuration needed! For organisations who want to use cloud-based AI services, you can configure them at the organisation level:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>Browser-Based AI (Default):</strong> All candidates automatically get AI features running in their browser - no setup, API keys, or configuration required</li>
                            <li><strong>Cloud-Based AI (Optional):</strong> Configure cloud AI services (OpenAI, Anthropic, Gemini, Grok) at the organisation level for all candidates</li>
                            <li><strong>Centralised Configuration:</strong> Set up cloud AI services once for your entire organisation</li>
                            <li><strong>Cost Management:</strong> Centralise AI API keys and settings to manage costs efficiently when using cloud services</li>
                            <li><strong>Automatic Access:</strong> All candidates automatically get access to AI features using your organisation's configuration (browser-based by default, cloud-based if configured)</li>
                            <li><strong>Priority System:</strong> Organisation cloud AI settings serve as defaults; individual candidates can override if needed</li>
                        </ul>
                        <p class="text-gray-600 text-sm mt-4">
                            Access this feature in Settings → Organisation AI Settings. Browser-based AI works immediately - cloud AI configuration is optional.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Email Settings</h3>
                        <p class="text-gray-700 mb-4">
                            Customise email communications from your organisation:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>Custom From Address:</strong> Send emails from your organisation's email address instead of the default</li>
                            <li><strong>Custom From Name:</strong> Set a display name for your emails (e.g., "Acme Recruiting Team")</li>
                            <li><strong>Professional Branding:</strong> All invitations and communications will appear from your organisation</li>
                            <li><strong>Better Deliverability:</strong> Emails from your domain improve deliverability and recognition</li>
                        </ul>
                        <p class="text-gray-600 text-sm mt-4">
                            Access this feature in Settings → Email Settings. Configure your organisation's email address and display name.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Pending Candidates Tracking</h3>
                        <p class="text-gray-700 mb-4">
                            Track and manage candidate invitations in one place:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>View Pending Invitations:</strong> See all candidates who have been invited but haven't accepted yet</li>
                            <li><strong>Expiration Dates:</strong> Track when invitations expire to follow up as needed</li>
                            <li><strong>Unified View:</strong> Filter candidates by status including "Pending" alongside Draft, Complete, Published, and Archived</li>
                            <li><strong>Easy Management:</strong> Quickly identify candidates who need follow-up</li>
                        </ul>
                        <p class="text-gray-600 text-sm mt-4">
                            Use the "Pending" filter on the Candidates page to view all pending invitations.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Limits & Upgrades -->
            <section id="limits" class="mb-16 scroll-mt-64">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Limits & Upgrades</h2>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Understanding Your Limits</h3>
                        <p class="text-gray-700 mb-4">
                            Your organisation plan includes limits for:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li><strong>Maximum Candidates:</strong> The total number of candidates you can manage</li>
                            <li><strong>Maximum Team Members:</strong> The total number of team members in your organisation</li>
                        </ul>
                        <p class="text-gray-600 text-sm mt-4">
                            You can view your current usage on the Agency Dashboard and in Settings.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Requesting Limit Increases</h3>
                        <p class="text-gray-700 mb-4">
                            If you need to increase your limits:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-4">
                            <li>Go to <strong>Settings</strong> → <strong>Request Limit Increase</strong></li>
                            <li>Select whether you need to increase candidate or team member limits</li>
                            <li>Enter your requested new limit</li>
                            <li>Optionally provide a reason for the increase</li>
                            <li>Submit your request</li>
                        </ol>
                        <p class="text-gray-600 text-sm mt-4">
                            A super admin will review your request and approve or deny it. You'll be notified once a decision has been made.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Viewing Request Status</h3>
                        <p class="text-gray-700 mb-4">
                            In the Settings page, you can:
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            <li>See all pending limit increase requests</li>
                            <li>View your request history</li>
                            <li>Check the status of previous requests (Approved, Denied, or Pending)</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section id="faq" class="mb-16 scroll-mt-64">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I get an organisation account?</h3>
                        <p class="text-gray-700 mb-3">
                            To create an organisation account, please email us at <a href="mailto:noreply@simple-job-tracker.com" class="text-blue-600 hover:text-blue-800 underline font-medium">noreply@simple-job-tracker.com</a> with details about your organisation.
                        </p>
                        <p class="text-gray-700">
                            Please include: Organisation name, primary contact name and email, expected number of candidates and team members, organisation type, and any specific requirements. Once we receive your request, we'll set up your organisation account and send you an invitation email.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I change my organisation's name or branding?</h3>
                        <p class="text-gray-700">
                            Yes! Owners and Admins can update organisation settings, including name, logo, and branding colours, from the Settings page.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">What happens if I reach my candidate limit?</h3>
                        <p class="text-gray-700">
                            You'll see a notification when you're approaching your limit. You can request a limit increase from the Settings page, or contact support for assistance.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Can candidates see other candidates' CVs?</h3>
                        <p class="text-gray-700">
                            No. Candidates can only see and edit their own CV. Only your organisation's team members can view candidate CVs based on their role permissions.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I transfer ownership of my organisation?</h3>
                        <p class="text-gray-700">
                            Organisation owners can transfer ownership to another admin from the Settings page. This action cannot be undone, so make sure you trust the new owner.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Who can I contact for support?</h3>
                        <p class="text-gray-700">
                            For organisation account setup or support, please email <a href="mailto:noreply@simple-job-tracker.com" class="text-blue-600 hover:text-blue-800 underline font-medium">noreply@simple-job-tracker.com</a>. Include your organisation name for faster assistance.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Call to Action -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-8 text-center text-white">
                <h2 class="text-2xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-blue-100 mb-6">
                    If you already have an organisation account, log in to access your dashboard.
                </p>
                <?php if (isLoggedIn()): ?>
                    <a href="/agency/dashboard.php" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-md font-semibold hover:bg-blue-50">
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" data-open-login class="inline-block bg-white text-blue-600 px-6 py-3 rounded-md font-semibold hover:bg-blue-50">
                            Log In
                        </button>
                        <button type="button" data-open-register class="inline-block border-2 border-white text-white px-6 py-3 rounded-md font-semibold hover:bg-white/10">
                            Register
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>
    <?php partial('auth-modals'); ?>

    <script>
        // Smooth scroll for anchor links with offset for sticky header
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerOffset = 150; // Header (64px) + sticky nav (~56px) + extra spacing (30px)
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Handle organisation request form submission
        const form = document.getElementById('organisation-request-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitButton = form.querySelector('button[type="submit"]');
                const messageDiv = document.getElementById('form-message');
                const formData = new FormData(form);
                
                // Disable submit button
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                messageDiv.classList.add('hidden');
                
                try {
                    const response = await fetch('/api/organisation-request.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        messageDiv.className = 'rounded-lg p-4 text-sm font-medium bg-green-50 text-green-800 border border-green-200';
                        messageDiv.textContent = data.message;
                        messageDiv.classList.remove('hidden');
                        form.reset();
                        
                        // Scroll to message
                        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        messageDiv.className = 'rounded-lg p-4 text-sm font-medium bg-red-50 text-red-800 border border-red-200';
                        messageDiv.textContent = data.error || 'An error occurred. Please try again.';
                        messageDiv.classList.remove('hidden');
                    }
                } catch (error) {
                    messageDiv.className = 'rounded-lg p-4 text-sm font-medium bg-red-50 text-red-800 border border-red-200';
                    messageDiv.textContent = 'An error occurred. Please try again or contact us directly at noreply@simple-job-tracker.com';
                    messageDiv.classList.remove('hidden');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Request';
                }
            });
        }
    </script>
</body>
</html>

