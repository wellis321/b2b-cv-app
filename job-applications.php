<?php
/**
 * Job Applications Dashboard
 * Track and manage job applications
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

// Redirect to content editor with jobs section (unless redirect=false)
if (get('redirect') !== 'false') {
    $flash = getFlash('error');
    $successFlash = getFlash('success');
    if ($flash) {
        setFlash('error', $flash);
    }
    if ($successFlash) {
        setFlash('success', $successFlash);
    }
    redirect('/content-editor.php#jobs');
    exit;
}

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get statistics
$stats = getJobApplicationStats();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Job Applications | Simple CV Builder',
        'metaDescription' => 'Track and manage your job applications in one place.',
        'canonicalUrl' => APP_URL . '/job-applications.php',
        'metaNoindex' => true,
    ]); ?>
    <!-- PDF Generation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.min.js"></script>
    <!-- Browser AI Service Dependencies -->
    <script src="/js/model-cache-manager.js"></script>
    <script src="/js/browser-ai-service.js"></script>
    <script src="/js/markdown-editor.js"></script>
    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-applied { background-color: #fef3c7; color: #92400e; }
        .status-interviewing { background-color: #e9d5ff; color: #6b21a8; }
        .status-offered { background-color: #dbeafe; color: #1e40af; }
        .status-accepted { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-withdrawn { background-color: #f3f4f6; color: #374151; }
        .status-in_progress { background-color: #fed7aa; color: #9a3412; }
        .status-interested { background-color: #bae6fd; color: #0c4a6e; }
        .job-description-view-content table { border-collapse: collapse; width: 100%; margin: 0.75rem 0; }
        .job-description-view-content td, .job-description-view-content th { border: 1px solid #d1d5db; padding: 0.375rem 0.5rem; text-align: left; vertical-align: top; }
        .job-description-view-content th { background: #f3f4f6; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Job Applications</h1>
                <p class="mt-1 text-sm text-gray-500">Track and manage your job applications</p>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4 lg:grid-cols-7">
                <button type="button" 
                        onclick="JobApplications.filterByStatus('all')"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-blue-300"
                        data-status="all">
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
                </button>
                <button type="button" 
                        onclick="JobApplications.filterByStatus('applied')"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-yellow-300"
                        data-status="applied">
                    <p class="text-sm text-gray-500">Applied</p>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['applied']; ?></p>
                </button>
                <button type="button" 
                        onclick="JobApplications.filterByStatus('interviewing')"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-purple-300"
                        data-status="interviewing">
                    <p class="text-sm text-gray-500">Interviewing</p>
                    <p class="text-2xl font-bold text-purple-600"><?php echo $stats['interviewing']; ?></p>
                </button>
                <button type="button" 
                        onclick="JobApplications.filterByStatus('offered')"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-blue-300"
                        data-status="offered">
                    <p class="text-sm text-gray-500">Offered</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $stats['offered']; ?></p>
                </button>
                <button type="button" 
                        onclick="JobApplications.filterByStatus('accepted')"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-green-300"
                        data-status="accepted">
                    <p class="text-sm text-gray-500">Accepted</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo $stats['accepted']; ?></p>
                </button>
                <button type="button" 
                        onclick="JobApplications.filterByStatus('rejected')"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-red-300"
                        data-status="rejected">
                    <p class="text-sm text-gray-500">Rejected</p>
                    <p class="text-2xl font-bold text-red-600"><?php echo $stats['rejected']; ?></p>
                </button>
                <button type="button" 
                        onclick="JobApplications.filterByInterview()"
                        class="stat-card bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-indigo-300"
                        data-filter="interview">
                    <p class="text-sm text-gray-500">Interviews</p>
                    <p class="text-2xl font-bold text-indigo-600"><?php echo $stats['had_interview']; ?></p>
                </button>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- AI Features Banner -->
            <div class="mb-6 bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-purple-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-base font-semibold text-gray-900 mb-1">AI-Powered CV Tools</h3>
                        <p class="text-sm text-gray-700 mb-3">
                            Upload job description files (PDF, Word, Excel) directly to your applications—the AI will read them when generating CV variants. From the <a href="/content-editor.php#jobs" class="text-purple-600 hover:underline">content editor</a>, open a job and use <strong>Generate AI CV for this job</strong> (one-click) or <strong>Tailor CV for this job…</strong> to choose sections. Here, click &ldquo;Generate AI CV&rdquo; or &ldquo;Assess CV Quality&rdquo; on any job to get started.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <a href="/content-editor.php#jobs" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                Manage Jobs in Editor
                            </a>
                            <a href="/content-editor.php#cv-variants" class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded-lg hover:bg-purple-700 transition-colors">
                                View CV Variants
                            </a>
                            <a href="/cv-variants/rewrite.php" class="inline-flex items-center px-3 py-1.5 border border-purple-600 text-purple-600 text-xs font-medium rounded-lg hover:bg-purple-50 transition-colors">
                                Generate New AI CV
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Applications Interface -->
            <div id="job-applications-app" class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Your Applications</h2>
                        <div class="flex items-center gap-3">
                            <!-- View Toggle -->
                            <div class="flex items-center bg-gray-100 rounded-lg p-1">
                                <button id="view-toggle-cards" 
                                        class="view-toggle-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors bg-white text-gray-900 shadow-sm"
                                        onclick="JobApplications.setView('cards')"
                                        title="Card View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </button>
                                <button id="view-toggle-table" 
                                        class="view-toggle-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-gray-600 hover:text-gray-900"
                                        onclick="JobApplications.setView('table')"
                                        title="Table View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            <button id="add-application-btn" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Add New Application
                            </button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <select id="status-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Status</option>
                            <option value="interested">Interested</option>
                            <option value="in_progress">In Progress</option>
                            <option value="applied">Applied</option>
                            <option value="interviewing">Interviewing</option>
                            <option value="offered">Offered</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                            <option value="withdrawn">Withdrawn</option>
                        </select>
                        <input type="text" 
                               id="search-input" 
                               placeholder="Search by company or job title..."
                               class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1 min-w-64">
                    </div>

                    <!-- Applications List - Cards View -->
                    <div id="applications-container-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="text-center py-12 text-gray-500 col-span-full">
                            <p>Loading applications...</p>
                        </div>
                    </div>

                    <!-- Applications List - Table View -->
                    <div id="applications-container-table" class="hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        Loading applications...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Application View Modal -->
    <div id="application-view-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full p-8">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex-1">
                        <h3 id="view-job-title" class="text-2xl font-bold text-gray-900 mb-2"></h3>
                        <p id="view-company-name" class="text-lg text-gray-600 font-medium"></p>
                    </div>
                    <button id="close-view-modal" class="text-gray-400 hover:text-gray-600 ml-4">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div id="view-content" class="space-y-6">
                    <!-- Content will be populated by JavaScript -->
                </div>
                
                <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                    <button type="button" id="close-view-modal-btn"
                            class="px-6 py-3 border-2 border-gray-400 rounded-lg text-base font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-gray-200">
                        Close
                    </button>
                    <button type="button" id="edit-from-view-btn"
                            class="px-6 py-3 border border-transparent rounded-lg text-base font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-blue-200">
                        Edit Application
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Edit Modal -->
    <div id="application-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modal-title" class="text-xl font-semibold text-gray-900">Add New Application</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="application-form" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="form-company" class="block text-base font-semibold text-gray-900 mb-3">
                                Company Name <span class="text-red-600 font-bold">*</span>
                            </label>
                            <input type="text" id="form-company" name="company_name" required
                                   class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                        </div>
                        <div>
                            <label for="form-job-title" class="block text-base font-semibold text-gray-900 mb-3">
                                Job Title <span class="text-red-600 font-bold">*</span>
                            </label>
                            <input type="text" id="form-job-title" name="job_title" required
                                   class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                        </div>
                    </div>
                    
                    <div>
                        <label for="form-description" class="block text-base font-semibold text-gray-900 mb-3">Job Description</label>
                        <textarea id="form-description" name="job_description" rows="4"
                                  class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="form-status" class="block text-base font-semibold text-gray-900 mb-3">Status</label>
                            <select id="form-status" name="status"
                                    class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <option value="interested">Interested</option>
                                <option value="in_progress">In Progress</option>
                                <option value="applied">Applied</option>
                                <option value="interviewing">Interviewing</option>
                                <option value="offered">Offered</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                        </div>
                        <div>
                            <label for="form-remote" class="block text-base font-semibold text-gray-900 mb-3">Work Arrangement</label>
                            <select id="form-remote" name="remote_type"
                                    class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                                <option value="onsite">Onsite</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="remote">Remote</option>
                            </select>
                        </div>
                        <div>
                            <label for="form-date" class="block text-base font-semibold text-gray-900 mb-3">Application Date</label>
                            <input type="date" id="form-date" name="application_date"
                                   class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="form-location" class="block text-base font-semibold text-gray-900 mb-3">Location</label>
                            <input type="text" id="form-location" name="job_location"
                                   class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                        </div>
                        <div>
                            <label for="form-salary" class="block text-base font-semibold text-gray-900 mb-3">Salary Range</label>
                            <input type="text" id="form-salary" name="salary_range" placeholder="e.g., £30,000 - £40,000"
                                   class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                        </div>
                    </div>
                    
                    <div>
                        <label for="form-url" class="block text-base font-semibold text-gray-900 mb-3">Application URL</label>
                        <input type="url" id="form-url" name="application_url" placeholder="https://..."
                               class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                    </div>
                    
                    <div>
                        <label for="form-notes" class="block text-base font-semibold text-gray-900 mb-3">Notes</label>
                        <p class="text-xs text-gray-500 mb-1">Use the toolbar for formatting: bold, italic, headers, lists, and links</p>
                        <textarea id="form-notes" name="notes" rows="8" data-markdown
                                  class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y min-h-[200px]"
                                  placeholder="Add any additional notes about this application..."></textarea>
                        <p class="mt-2 text-sm text-gray-600 font-medium">You can expand this field by dragging the bottom-right corner if needed.</p>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div>
                        <label class="block text-base font-semibold text-gray-900 mb-3">Files</label>
                        <div id="file-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                            <input type="file" id="file-input" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.jpg,.jpeg,.png" class="hidden">
                            <div class="space-y-2">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-input" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload files</span>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, Word, Excel, Text, Images (MAX. 10MB)</p>
                            </div>
                        </div>
                        <div id="file-list" class="mt-4 space-y-2"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="form-followup" class="block text-base font-semibold text-gray-900 mb-3">Closing Date</label>
                            <input type="date" id="form-followup" name="next_follow_up"
                                   class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                            <p class="mt-2 text-sm text-gray-600 font-medium">The deadline for this job application</p>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="form-interview" name="had_interview"
                                   class="h-5 w-5 rounded border-2 border-gray-400 text-blue-600 focus:ring-4 focus:ring-blue-200 focus:ring-offset-0">
                            <label for="form-interview" class="ml-3 text-base text-gray-700 font-semibold">
                                Had Interview
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" id="cancel-modal"
                                class="px-6 py-3 border-2 border-gray-400 rounded-lg text-base font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 border border-transparent rounded-lg text-base font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Save Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php partial('footer'); ?>
    <script>
    // Enhance markdown rendering for job applications page
    if (typeof marked !== 'undefined') {
        document.querySelectorAll('.markdown-content').forEach(function(el) {
            const originalHtml = el.innerHTML;
            try {
                const rendered = marked.parse(originalHtml, { breaks: true, gfm: true });
                el.innerHTML = rendered;
            } catch (e) {
                console.warn('Markdown parsing failed:', e);
            }
        });
    }
    </script>

    <script>
        // Simple job applications manager
        const JobApplications = {
            applications: [],
            csrfToken: '<?php echo csrfToken(); ?>',
            currentView: 'cards', // 'cards' or 'table'
            
            setView(view) {
                this.currentView = view;
                localStorage.setItem('jobApplicationsView', view);
                
                // Update toggle buttons
                document.querySelectorAll('.view-toggle-btn').forEach(btn => {
                    btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                    btn.classList.add('text-gray-600');
                });
                
                if (view === 'cards') {
                    document.getElementById('view-toggle-cards').classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    document.getElementById('view-toggle-cards').classList.remove('text-gray-600');
                    document.getElementById('applications-container-cards').classList.remove('hidden');
                    document.getElementById('applications-container-table').classList.add('hidden');
                } else {
                    document.getElementById('view-toggle-table').classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    document.getElementById('view-toggle-table').classList.remove('text-gray-600');
                    document.getElementById('applications-container-cards').classList.add('hidden');
                    document.getElementById('applications-container-table').classList.remove('hidden');
                }
                
                this.renderApplications();
            },
            
            async loadApplications() {
                try {
                    const response = await fetch('/api/job-applications.php');
                    const data = await response.json();
                    if (data.applications) {
                        this.applications = data.applications;
                        if (data.csrf_token) {
                            this.csrfToken = data.csrf_token;
                        }
                    } else {
                        this.applications = data;
                    }
                    this.renderApplications();
                } catch (error) {
                    console.error('Error loading applications:', error);
                    document.getElementById('applications-container').innerHTML = 
                        '<div class="text-center py-12 text-red-500">Error loading applications. Please refresh the page.</div>';
                }
            },
            
            currentFilter: 'all',
            filterByInterviewStatus: false,
            
            filterByStatus(status) {
                this.currentFilter = status;
                this.filterByInterviewStatus = false;
                document.getElementById('status-filter').value = status;
                this.updateStatCards();
                this.renderApplications();
            },
            
            filterByInterview() {
                this.currentFilter = 'all';
                this.filterByInterviewStatus = true;
                document.getElementById('status-filter').value = 'all';
                this.updateStatCards();
                this.renderApplications();
            },
            
            updateStatCards() {
                document.querySelectorAll('.stat-card').forEach(card => {
                    const cardStatus = card.getAttribute('data-status');
                    const cardFilter = card.getAttribute('data-filter');
                    
                    if (this.filterByInterviewStatus && cardFilter === 'interview') {
                        card.classList.add('border-indigo-500', 'bg-indigo-50');
                        card.classList.remove('border-transparent');
                    } else if (!this.filterByInterviewStatus && cardStatus === this.currentFilter) {
                        card.classList.add('border-blue-500', 'bg-blue-50');
                        card.classList.remove('border-transparent');
                    } else {
                        card.classList.remove('border-blue-500', 'bg-blue-50', 'border-indigo-500', 'bg-indigo-50');
                        card.classList.add('border-transparent');
                    }
                });
            },
            
            renderApplications() {
                const statusFilter = document.getElementById('status-filter').value;
                const searchTerm = document.getElementById('search-input').value.toLowerCase();
                
                let filtered = this.applications.filter(app => {
                    let matchesStatus = true;
                    if (this.filterByInterviewStatus) {
                        matchesStatus = app.had_interview === true || app.had_interview === 1;
                    } else {
                        matchesStatus = statusFilter === 'all' || app.status === statusFilter;
                    }
                    
                    const matchesSearch = !searchTerm || 
                        app.company_name.toLowerCase().includes(searchTerm) ||
                        app.job_title.toLowerCase().includes(searchTerm);
                    return matchesStatus && matchesSearch;
                });
                
                if (this.currentView === 'cards') {
                    this.renderCards(filtered);
                } else {
                    this.renderTable(filtered);
                }
            },
            
            renderCards(filtered) {
                const container = document.getElementById('applications-container-cards');
                
                if (filtered.length === 0) {
                    container.innerHTML = '<div class="text-center py-12 text-gray-500 col-span-full">No applications found.</div>';
                    return;
                }
                
                container.innerHTML = filtered.map(app => `
                    <div onclick="JobApplications.viewApplication('${app.id}')" 
                         class="border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-blue-300 transition-all bg-white cursor-pointer">
                        <div class="flex flex-col h-full">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">${this.escapeHtml(app.job_title)}</h3>
                                    <p class="text-sm text-gray-600 font-medium">${this.escapeHtml(app.company_name)}</p>
                                </div>
                                <span class="status-badge status-${app.status} ml-2 flex-shrink-0">${this.formatStatus(app.status)}</span>
                            </div>
                            <div class="flex-1 space-y-2 mb-4">
                                ${app.job_location ? `
                                    <p class="text-sm text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        ${this.escapeHtml(app.job_location)}
                                    </p>
                                ` : ''}
                                ${app.salary_range ? `
                                    <p class="text-sm text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        ${this.escapeHtml(app.salary_range)}
                                    </p>
                                ` : ''}
                                ${app.remote_type ? `
                                    <p class="text-sm text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        ${this.escapeHtml(app.remote_type.charAt(0).toUpperCase() + app.remote_type.slice(1))}
                                    </p>
                                ` : ''}
                                <p class="text-xs text-gray-400 flex items-center">
                                    <svg class="w-3 h-3 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Applied: ${new Date(app.application_date).toLocaleDateString()}
                                </p>
                            </div>
                            <div class="flex gap-2 pt-3 border-t border-gray-100" onclick="event.stopPropagation()">
                                <button onclick="JobApplications.viewApplication('${app.id}')" 
                                        class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors">
                                    View
                                </button>
                                <button onclick="JobApplications.deleteApplication('${app.id}')" 
                                        class="flex-1 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            },
            
            renderTable(filtered) {
                const tbody = document.getElementById('table-body');
                
                if (filtered.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No applications found.</td></tr>';
                    return;
                }
                
                tbody.innerHTML = filtered.map(app => `
                    <tr onclick="JobApplications.viewApplication('${app.id}')" 
                        class="hover:bg-blue-50 cursor-pointer transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${this.escapeHtml(app.company_name)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${this.escapeHtml(app.job_title)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="status-badge status-${app.status}">${this.formatStatus(app.status)}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">${app.job_location ? this.escapeHtml(app.job_location) : '—'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">${app.salary_range ? this.escapeHtml(app.salary_range) : '—'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">${new Date(app.application_date).toLocaleDateString()}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                            <button onclick="JobApplications.viewApplication('${app.id}')" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                            <button onclick="JobApplications.deleteApplication('${app.id}')" 
                                    class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                `).join('');
            },
            
            formatStatus(status) {
                const statusMap = {
                    'interested': 'Interested',
                    'in_progress': 'In Progress',
                    'applied': 'Applied',
                    'interviewing': 'Interviewing',
                    'offered': 'Offered',
                    'accepted': 'Accepted',
                    'rejected': 'Rejected',
                    'withdrawn': 'Withdrawn'
                };
                return statusMap[status] || status;
            },
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },
            
            decodeHtmlEntities(text) {
                if (!text) return '';
                // Create a temporary element to decode HTML entities
                const temp = document.createElement('div');
                temp.innerHTML = text;
                return temp.textContent || temp.innerText || '';
            },
            
            async deleteApplication(id) {
                if (!confirm('Are you sure you want to delete this application?')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/api/job-applications.php?id=${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ csrf_token: this.csrfToken })
                    });
                    
                    if (response.ok) {
                        await this.loadApplications();
                    } else {
                        alert('Error deleting application');
                    }
                } catch (error) {
                    console.error('Error deleting application:', error);
                    alert('Error deleting application');
                }
            },
            
            showAddModal() {
                this.currentApplication = null;
                this.showModal();
            },
            
            async viewApplication(id) {
                const app = this.applications.find(a => a.id === id);
                if (!app) return;
                
                this.currentApplication = app;
                this.showViewModal();
                // Load cover letter after modal is shown
                await this.loadCoverLetter(app.id);
            },
            
            showViewModal() {
                const modal = document.getElementById('application-view-modal');
                const app = this.currentApplication;
                
                if (!app) return;
                
                // Set title and company
                document.getElementById('view-job-title').textContent = this.decodeHtmlEntities(app.job_title || '');
                document.getElementById('view-company-name').textContent = this.decodeHtmlEntities(app.company_name || '');
                
                // Build content
                const content = document.getElementById('view-content');
                let html = '';
                
                // Status badge
                html += `
                    <div class="flex items-center gap-3 mb-6">
                        <span class="status-badge status-${app.status} text-sm font-medium px-3 py-1">${this.formatStatus(app.status)}</span>
                        ${app.had_interview ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Interview Completed</span>' : ''}
                    </div>
                `;
                
                // Key information grid
                html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">';
                
                if (app.application_date) {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-500 mb-1">Application Date</p>
                            <p class="text-base font-medium text-gray-900">${new Date(app.application_date).toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                    `;
                }
                
                if (app.job_location) {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-500 mb-1 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Location
                            </p>
                            <p class="text-base font-medium text-gray-900">${this.escapeHtml(app.job_location)}</p>
                        </div>
                    `;
                }
                
                if (app.salary_range) {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-500 mb-1 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Salary Range
                            </p>
                            <p class="text-base font-medium text-gray-900">${this.escapeHtml(app.salary_range)}</p>
                        </div>
                    `;
                }
                
                if (app.remote_type) {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-500 mb-1 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Work Arrangement
                            </p>
                            <p class="text-base font-medium text-gray-900">${this.escapeHtml(app.remote_type.charAt(0).toUpperCase() + app.remote_type.slice(1))}</p>
                        </div>
                    `;
                }
                
                if (app.next_follow_up) {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-500 mb-1 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Closing Date
                            </p>
                            <p class="text-base font-medium text-gray-900">${new Date(app.next_follow_up).toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                    `;
                }
                
                html += '</div>';
                
                // Job Description (render as HTML so tables from Word extraction display correctly)
                if (app.job_description) {
                    html += `
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Job Description</h4>
                            <div class="bg-gray-50 rounded-lg p-4 prose max-w-none text-base text-gray-700 job-description-view-content" id="view-job-description-${this.escapeHtml(app.id)}"></div>
                        </div>
                    `;
                }
                
                // Application URL
                if (app.application_url) {
                    html += `
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Application URL</h4>
                            <a href="${this.escapeHtml(app.application_url)}" target="_blank" rel="noopener noreferrer" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium break-all">
                                ${this.escapeHtml(app.application_url)}
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    `;
                }
                
                // Notes
                if (app.notes) {
                    html += `
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Notes</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-base text-gray-700 whitespace-pre-wrap">${this.decodeHtmlEntities(app.notes)}</p>
                            </div>
                        </div>
                    `;
                }
                
                // Files
                if (app.files && app.files.length > 0) {
                    html += `
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Files (${app.files.length})</h4>
                            <div class="space-y-2">
                    `;
                    app.files.forEach(file => {
                        const fileName = file.custom_name || file.original_name;
                        const fileSize = this.formatFileSize(file.size);
                        const fileIcon = this.getFileIcon(file.mime_type);
                        html += `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    ${fileIcon}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">${this.escapeHtml(fileName)}</p>
                                        <p class="text-xs text-gray-500">${fileSize} • ${file.file_purpose || 'other'}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="${file.url || ('/storage/' + (file.stored_name || ''))}" target="_blank" 
                                       class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors">
                                        Download
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    html += `
                            </div>
                        </div>
                    `;
                }
                
                // Cover Letter Section
                html += `
                    <div class="mb-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Cover Letter</h4>
                        </div>
                        <div id="cover-letter-container-${app.id}" class="space-y-3">
                            <!-- Cover letter content will be loaded here -->
                        </div>
                    </div>
                `;
                
                // AI CV Actions
                if (app.job_description || app.notes) {
                    html += `
                        <div class="mb-6 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">AI CV Tools</h4>
                            <div class="flex flex-wrap gap-3">
                                <button onclick="JobApplications.generateAICV('${app.id}')" 
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Generate AI CV
                                </button>
                                <button onclick="JobApplications.assessCVQuality('${app.id}')" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Assess CV Quality
                                </button>
                                <a href="/content-editor.php#cv-variants" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    Manage CV Variants
                                </a>
                            </div>
                        </div>
                    `;
                }
                
                content.innerHTML = html;
                // Render job description as HTML (tables from Word extraction)
                if (app.job_description) {
                    const descEl = content.querySelector('#view-job-description-' + app.id);
                    if (descEl) descEl.innerHTML = app.job_description;
                }
                modal.classList.remove('hidden');
            },
            
            hideViewModal() {
                document.getElementById('application-view-modal').classList.add('hidden');
            },
            
            async editApplication(id) {
                const app = this.applications.find(a => a.id === id);
                if (!app) return;
                
                // Close view modal if open
                this.hideViewModal();
                
                this.currentApplication = app;
                this.showModal();
            },
            
            showModal() {
                const modal = document.getElementById('application-modal');
                const form = document.getElementById('application-form');
                
                if (this.currentApplication) {
                    // Edit mode
                    document.getElementById('modal-title').textContent = 'Edit Application';
                    document.getElementById('form-company').value = this.decodeHtmlEntities(this.currentApplication.company_name || '');
                    document.getElementById('form-job-title').value = this.decodeHtmlEntities(this.currentApplication.job_title || '');
                    document.getElementById('form-description').value = this.decodeHtmlEntities(this.currentApplication.job_description || '');
                    document.getElementById('form-status').value = this.currentApplication.status || 'applied';
                    document.getElementById('form-location').value = this.decodeHtmlEntities(this.currentApplication.job_location || '');
                    document.getElementById('form-salary').value = this.decodeHtmlEntities(this.currentApplication.salary_range || '');
                    document.getElementById('form-remote').value = this.currentApplication.remote_type || 'onsite';
                    document.getElementById('form-url').value = this.decodeHtmlEntities(this.currentApplication.application_url || '');
                    document.getElementById('form-notes').value = this.decodeHtmlEntities(this.currentApplication.notes || '');
                    document.getElementById('form-date').value = this.currentApplication.application_date ? 
                        new Date(this.currentApplication.application_date).toISOString().split('T')[0] : '';
                    document.getElementById('form-followup').value = this.currentApplication.next_follow_up ? 
                        new Date(this.currentApplication.next_follow_up).toISOString().split('T')[0] : '';
                    document.getElementById('form-interview').checked = this.currentApplication.had_interview || false;
                    
                    // Load files for this application
                    this.loadFiles(this.currentApplication.id);
                } else {
                    // Add mode
                    document.getElementById('modal-title').textContent = 'Add New Application';
                    form.reset();
                    document.getElementById('form-status').value = 'applied';
                    document.getElementById('form-remote').value = 'onsite';
                    document.getElementById('form-date').value = new Date().toISOString().split('T')[0];
                    
                    // Clear files for new application
                    this.currentFiles = [];
                    this.renderFiles();
                }
                
                modal.classList.remove('hidden');
            },
            
            hideModal() {
                document.getElementById('application-modal').classList.add('hidden');
                this.currentApplication = null;
            },
            
            async saveApplication(formData) {
                const data = {
                    company_name: formData.get('company_name'),
                    job_title: formData.get('job_title'),
                    job_description: formData.get('job_description'),
                    application_date: formData.get('application_date'),
                    status: formData.get('status'),
                    salary_range: formData.get('salary_range'),
                    job_location: formData.get('job_location'),
                    remote_type: formData.get('remote_type'),
                    application_url: formData.get('application_url'),
                    notes: formData.get('notes'),
                    next_follow_up: formData.get('next_follow_up') || null,
                    had_interview: formData.get('had_interview') === 'on',
                    csrf_token: this.csrfToken
                };
                
                try {
                    let response;
                    let applicationId;
                    let result;
                    
                    if (this.currentApplication) {
                        // Update
                        applicationId = this.currentApplication.id;
                        response = await fetch(`/api/job-applications.php?id=${applicationId}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        });
                        result = await response.json();
                    } else {
                        // Create
                        response = await fetch('/api/job-applications.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        });
                        result = await response.json();
                        
                        if (result.success && result.id) {
                            applicationId = result.id;
                        }
                    }
                    
                    if (response.ok && result.success) {
                        // If we have files uploaded but no application ID yet, link them now
                        if (applicationId && this.currentFiles.length > 0) {
                            for (const file of this.currentFiles) {
                                if (!file.application_id) {
                                    // File was uploaded before application was created, update it
                                    const updateFormData = new FormData();
                                    updateFormData.append('file_id', file.id);
                                    updateFormData.append('application_id', applicationId);
                                    updateFormData.append('csrf_token', this.csrfToken);
                                    
                                    // Note: We'd need an update endpoint for this, but for now files uploaded
                                    // before application creation will be orphaned. This is acceptable for MVP.
                                }
                            }
                        }
                        
                        this.hideModal();
                        await this.loadApplications();
                    } else {
                        alert(result.error || 'Error saving application');
                    }
                } catch (error) {
                    console.error('Error saving application:', error);
                    alert('Error saving application: ' + (error.message || 'Unknown error'));
                }
            },
            
            async generateAICV(jobApplicationId) {
                if (!confirm('This will generate a new AI-rewritten CV variant for this job application. Continue?')) {
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('csrf_token', this.csrfToken);
                    formData.append('job_application_id', jobApplicationId);
                    
                    const response = await fetch('/api/ai-rewrite-cv.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('CV generated successfully! Redirecting to CV variants...');
                        window.location.href = '/content-editor.php#cv-variants';
                    } else {
                        alert('Error: ' + (result.error || 'Failed to generate CV'));
                    }
                } catch (error) {
                    console.error('Error generating AI CV:', error);
                    alert('An error occurred. Please try again.');
                }
            },
            
            assessCVQuality(jobApplicationId) {
                window.location.href = `/cv-quality.php?job_application_id=${jobApplicationId}`;
            },
            
            // File management functions
            currentFiles: [],
            
            async loadFiles(applicationId) {
                if (!applicationId) {
                    this.currentFiles = [];
                    this.renderFiles();
                    return;
                }
                
                try {
                    const response = await fetch(`/api/job-applications.php?id=${applicationId}`);
                    const data = await response.json();
                    if (data.application && data.application.files) {
                        this.currentFiles = data.application.files;
                    } else {
                        this.currentFiles = [];
                    }
                } catch (error) {
                    console.error('Error loading files:', error);
                    this.currentFiles = [];
                }
                this.renderFiles();
            },
            
            renderFiles() {
                const fileList = document.getElementById('file-list');
                if (!fileList) return;
                
                if (this.currentFiles.length === 0) {
                    fileList.innerHTML = '';
                    return;
                }
                
                fileList.innerHTML = this.currentFiles.map(file => {
                    const fileName = file.custom_name || file.original_name;
                    const fileSize = this.formatFileSize(file.size);
                    const fileIcon = this.getFileIcon(file.mime_type);
                    
                    return `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200" data-file-id="${file.id}">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                ${fileIcon}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">${this.escapeHtml(fileName)}</p>
                                    <p class="text-xs text-gray-500">${fileSize} • ${file.file_purpose || 'other'}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button onclick="JobApplications.extractFileText('${file.id}')" 
                                        class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors"
                                        title="Extract text to job description">
                                    Extract Text
                                </button>
                                <a href="${file.url}" target="_blank" 
                                   class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
                                   title="Download file">
                                    Download
                                </a>
                                <button onclick="JobApplications.deleteFile('${file.id}')" 
                                        class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                                        title="Delete file">
                                    Delete
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            },
            
            getFileIcon(mimeType) {
                if (mimeType.includes('pdf')) {
                    return '<svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6h-4V2H4v16zm-2 1V0h6l4 4h8v15H2v1z"/></svg>';
                } else if (mimeType.includes('word') || mimeType.includes('document')) {
                    return '<svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6h-4V2H4v16zm-2 1V0h6l4 4h8v15H2v1z"/></svg>';
                } else if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
                    return '<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6h-4V2H4v16zm-2 1V0h6l4 4h8v15H2v1z"/></svg>';
                } else if (mimeType.includes('image')) {
                    return '<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                } else {
                    return '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
                }
            },
            
            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            },
            
            async uploadFile(file, applicationId) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('application_id', applicationId || '');
                formData.append('file_purpose', 'other');
                formData.append('csrf_token', this.csrfToken);
                
                try {
                    const response = await fetch('/api/upload-job-application-file.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.currentFiles.push(result.file);
                        this.renderFiles();
                        return { success: true, file: result.file };
                    } else {
                        alert('Error uploading file: ' + (result.error || 'Unknown error'));
                        return { success: false, error: result.error };
                    }
                } catch (error) {
                    console.error('Error uploading file:', error);
                    alert('Error uploading file. Please try again.');
                    return { success: false, error: error.message };
                }
            },
            
            async deleteFile(fileId) {
                if (!confirm('Are you sure you want to delete this file?')) {
                    return;
                }
                
                const formData = new FormData();
                formData.append('file_id', fileId);
                formData.append('csrf_token', this.csrfToken);
                
                try {
                    const response = await fetch('/api/delete-job-application-file.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.currentFiles = this.currentFiles.filter(f => f.id !== fileId);
                        this.renderFiles();
                    } else {
                        alert('Error deleting file: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting file:', error);
                    alert('Error deleting file. Please try again.');
                }
            },
            
            async extractFileText(fileId) {
                if (!confirm('Extract text from this file and populate the job description field? This will replace any existing text.')) {
                    return;
                }
                
                const formData = new FormData();
                formData.append('file_id', fileId);
                formData.append('csrf_token', this.csrfToken);
                
                try {
                    const response = await fetch('/api/extract-job-file-text.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        document.getElementById('form-description').value = result.text;
                        alert('Text extracted successfully!');
                    } else {
                        alert('Error extracting text: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error extracting text:', error);
                    alert('Error extracting text. Please try again.');
                }
            },
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },
            
            // Cover Letter Functions
            async loadCoverLetter(applicationId) {
                try {
                    const response = await fetch(`/api/get-cover-letter.php?application_id=${applicationId}&csrf_token=${this.csrfToken}`);
                    const result = await response.json();
                    
                    const container = document.getElementById(`cover-letter-container-${applicationId}`);
                    if (!container) return;
                    
                    if (result.success && result.cover_letter) {
                        this.renderCoverLetter(applicationId, result.cover_letter);
                    } else {
                        this.renderCoverLetterEmpty(applicationId);
                    }
                } catch (error) {
                    console.error('Error loading cover letter:', error);
                    const container = document.getElementById(`cover-letter-container-${applicationId}`);
                    if (container) {
                        this.renderCoverLetterEmpty(applicationId);
                    }
                }
            },
            
            renderCoverLetter(applicationId, coverLetter) {
                const container = document.getElementById(`cover-letter-container-${applicationId}`);
                if (!container) return;
                
                const isEditing = container.dataset.editing === 'true';
                if (isEditing) return; // Don't overwrite if editing
                
                container.innerHTML = `
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="prose max-w-none">
                            <div class="text-base text-gray-700 whitespace-pre-wrap" id="cover-letter-text-${applicationId}">${this.escapeHtml(coverLetter.cover_letter_text)}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="JobApplications.editCoverLetter('${applicationId}', '${coverLetter.id}')" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        <button onclick="JobApplications.regenerateCoverLetter('${applicationId}')" 
                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Regenerate with AI
                        </button>
                        <button onclick="JobApplications.exportCoverLetterPDF('${coverLetter.id}')" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export PDF
                        </button>
                        <button onclick="JobApplications.deleteCoverLetter('${applicationId}', '${coverLetter.id}')" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                `;
            },
            
            renderCoverLetterEmpty(applicationId) {
                const container = document.getElementById(`cover-letter-container-${applicationId}`);
                if (!container) return;
                
                container.innerHTML = `
                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-600 mb-4">No cover letter generated yet</p>
                        <button onclick="JobApplications.generateCoverLetter('${applicationId}')" 
                                class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate Cover Letter with AI
                        </button>
                    </div>
                `;
            },
            
            async generateCoverLetter(applicationId) {
                
                const container = document.getElementById(`cover-letter-container-${applicationId}`);
                if (!container) return;
                
                // Show loading state
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                loadingOverlay.innerHTML = `
                    <div class="bg-white rounded-lg p-8 max-w-md text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <p class="text-gray-800 font-medium">Generating cover letter with AI...</p>
                        <p class="text-gray-600 text-sm mt-2">This may take 30–90 seconds</p>
                    </div>
                `;
                document.body.appendChild(loadingOverlay);
                
                const timeoutMs = 150000; // 2.5 minutes
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
                
                try {
                    const formData = new FormData();
                    formData.append('job_application_id', applicationId);
                    formData.append('csrf_token', this.csrfToken);
                    
                    const response = await fetch('/api/ai-generate-cover-letter.php', {
                        method: 'POST',
                        body: formData,
                        signal: controller.signal
                    });
                    clearTimeout(timeoutId);
                    
                    const text = await response.text();
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch (e) {
                        if (text.indexOf('Fatal error') !== -1 || text.indexOf('Maximum execution time') !== -1) {
                            throw new Error('The server ran out of time while generating. Try again or use a smaller/faster AI model.');
                        }
                        throw new Error('Invalid response from server. Please try again.');
                    }
                    
                    if (result.success) {
                        // Check if browser AI execution is required
                        if (result.browser_execution) {
                            await this.executeBrowserAICoverLetter(result, applicationId, loadingOverlay);
                        } else {
                            // Server-side generation completed
                            if (document.body.contains(loadingOverlay)) {
                                document.body.removeChild(loadingOverlay);
                            }
                            await this.loadCoverLetter(applicationId);
                            alert('Cover letter generated successfully!');
                        }
                    } else {
                        throw new Error(result.error || 'Failed to generate cover letter');
                    }
                } catch (error) {
                    clearTimeout(timeoutId);
                    console.error('Error generating cover letter:', error);
                    if (document.body.contains(loadingOverlay)) {
                        document.body.removeChild(loadingOverlay);
                    }
                    const msg = error.name === 'AbortError'
                        ? 'The request took too long. The server or AI model may be busy. Try again or use a smaller/faster model.'
                        : error.message;
                    alert('Error generating cover letter: ' + msg);
                    await this.loadCoverLetter(applicationId);
                }
            },
            
            async executeBrowserAICoverLetter(result, applicationId, loadingOverlay) {
                try {
                    // Check if BrowserAIService is available
                    const browserAIServiceAvailable = typeof BrowserAIService !== 'undefined';
                    
                    if (!browserAIServiceAvailable) {
                        throw new Error('Browser AI service not loaded. Please refresh the page.');
                    }
                    
                    // Check browser support
                    const support = BrowserAIService.checkBrowserSupport();
                    
                    if (!support.required) {
                        throw new Error('Browser does not support WebGPU or WebGL. Browser AI requires a modern browser with GPU support.');
                    }
                    
                    // Update loading overlay to show model loading
                    if (loadingOverlay) {
                        loadingOverlay.querySelector('p').textContent = 'Loading AI model. This may take a few minutes on first use...';
                    }
                    
                    // Initialize browser AI
                    const modelType = result.model_type === 'webllm' ? 'webllm' : 'tensorflow';
                    await BrowserAIService.initBrowserAI(modelType, result.model, (progress) => {
                        if (loadingOverlay && progress.message) {
                            loadingOverlay.querySelector('p').textContent = progress.message;
                        }
                    });
                    
                    // Update loading overlay
                    if (loadingOverlay) {
                        loadingOverlay.querySelector('p').textContent = 'Generating cover letter... This may take 30-60 seconds.';
                    }
                    
                    // Generate cover letter using browser AI
                    let coverLetterText = await BrowserAIService.generateText(result.prompt, {
                        temperature: 0.8,
                        maxTokens: 2000
                    });
                    
                    console.log('Browser AI raw response:', coverLetterText);
                    
                    if (!coverLetterText || coverLetterText.trim().length === 0) {
                        throw new Error('Browser AI returned empty response. Please try again.');
                    }
                    
                    // Clean the response (remove markdown, JSON formatting, etc.)
                    let cleanedText = coverLetterText.trim();
                    
                    // Remove JSON wrapping if present
                    if (cleanedText.startsWith('{')) {
                        try {
                            const parsed = JSON.parse(cleanedText);
                            if (parsed.letter) {
                                cleanedText = parsed.letter;
                            } else if (parsed.cover_letter) {
                                cleanedText = parsed.cover_letter;
                            } else if (parsed.text) {
                                cleanedText = parsed.text;
                            } else if (parsed.content) {
                                cleanedText = parsed.content;
                            }
                        } catch (e) {
                            // Not valid JSON, try to extract text between quotes
                            const letterMatch = cleanedText.match(/"letter"\s*:\s*"([^"]*(?:\\.[^"]*)*)"/s);
                            if (letterMatch) {
                                cleanedText = letterMatch[1].replace(/\\n/g, '\n').replace(/\\"/g, '"');
                            }
                        }
                    }
                    
                    // Remove escaped newlines (but keep actual newlines)
                    cleanedText = cleanedText.replace(/\\n/g, '\n');
                    // Remove escaped quotes
                    cleanedText = cleanedText.replace(/\\"/g, '"');
                    // Remove quotation marks around paragraphs (standalone quotes)
                    cleanedText = cleanedText.replace(/^"([^"]+)"$/gm, '$1');
                    // Remove any remaining JSON key patterns at the start
                    cleanedText = cleanedText.replace(/^["']?\w+["']?\s*:\s*/, '');
                    
                    // Final trim
                    cleanedText = cleanedText.trim();
                    
                    console.log('Cleaned cover letter text:', cleanedText.substring(0, 200));
                    
                    if (!cleanedText || cleanedText.length === 0) {
                        throw new Error('Cover letter text is empty after cleaning. Raw response: ' + coverLetterText.substring(0, 100));
                    }
                    
                    // Send the generated cover letter to the server to save
                    console.log('Sending cover letter to server. Length:', cleanedText.length);
                    console.log('First 500 chars:', cleanedText.substring(0, 500));
                    
                    const saveFormData = new FormData();
                    saveFormData.append('job_application_id', applicationId);
                    saveFormData.append('cover_letter_text', cleanedText);
                    saveFormData.append('csrf_token', this.csrfToken);
                    
                    const saveResponse = await fetch('/api/ai-generate-cover-letter.php', {
                        method: 'POST',
                        body: saveFormData
                    });
                    
                    const saveResult = await saveResponse.json();
                    
                    console.log('Server save response:', saveResult);
                    
                    if (!saveResult.success) {
                        throw new Error(saveResult.error || 'Failed to save cover letter');
                    }
                    
                    // Remove loading overlay
                    if (document.body.contains(loadingOverlay)) {
                        document.body.removeChild(loadingOverlay);
                    }
                    
                    // Reload cover letter to display it
                    await this.loadCoverLetter(applicationId);
                    alert('Cover letter generated successfully!');
                    
                } catch (error) {
                    console.error('Error executing browser AI:', error);
                    
                    // Check if error is about WebLLM failing to load
                    const isWebLLMLoadError = error.message && (
                        error.message.includes('Failed to load WebLLM') || 
                        error.message.includes('WebLLM library') ||
                        error.message.includes('Failed to initialize WebLLM')
                    );
                    
                    if (isWebLLMLoadError) {
                        // Browser AI failed - offer to retry with server-side AI
                        if (document.body.contains(loadingOverlay)) {
                            document.body.removeChild(loadingOverlay);
                        }
                        
                        const retryWithServerAI = confirm(
                            'Browser AI is not available (WebLLM library failed to load).\n\n' +
                            'Would you like to generate the cover letter using server-side AI instead?\n\n' +
                            'Note: Server-side AI requires a configured cloud AI service (OpenAI, Anthropic, Gemini, or Grok).\n' +
                            'If you don\'t have one configured, please set it up in Settings → AI Settings.'
                        );
                        
                        if (retryWithServerAI) {
                            // Retry with server-side AI by sending a flag to use default service
                            const formData = new FormData();
                            formData.append('job_application_id', applicationId);
                            formData.append('csrf_token', this.csrfToken);
                            formData.append('force_server_ai', '1'); // Flag to force server-side
                            
                            // Show loading again
                            const retryOverlay = document.createElement('div');
                            retryOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                            retryOverlay.innerHTML = `
                                <div class="bg-white rounded-lg p-8 max-w-md text-center">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                    <p class="text-gray-800 font-medium">Generating cover letter with server AI...</p>
                                    <p class="text-gray-600 text-sm mt-2">This may take 30-60 seconds</p>
                                </div>
                            `;
                            document.body.appendChild(retryOverlay);
                            
                            const retryController = new AbortController();
                            const retryTimeoutId = setTimeout(() => retryController.abort(), 150000);
                            try {
                                const response = await fetch('/api/ai-generate-cover-letter.php', {
                                    method: 'POST',
                                    body: formData,
                                    signal: retryController.signal
                                });
                                clearTimeout(retryTimeoutId);
                                const text = await response.text();
                                let result;
                                try {
                                    result = JSON.parse(text);
                                } catch (e) {
                                    if (text.indexOf('Fatal error') !== -1 || text.indexOf('Maximum execution time') !== -1) {
                                        throw new Error('The server ran out of time. Try again or use a smaller/faster model.');
                                    }
                                    throw new Error('Invalid response from server. Please try again.');
                                }
                                if (document.body.contains(retryOverlay)) {
                                    document.body.removeChild(retryOverlay);
                                }
                                if (result.success && !result.browser_execution) {
                                    await this.loadCoverLetter(applicationId);
                                    alert('Cover letter generated successfully using server AI!');
                                } else if (result.browser_execution) {
                                    alert('Server AI is not configured. Please configure a cloud AI service (OpenAI, Anthropic, Gemini, or Grok) in Settings → AI Settings.');
                                    await this.loadCoverLetter(applicationId);
                                } else {
                                    throw new Error(result.error || 'Failed to generate cover letter');
                                }
                            } catch (retryError) {
                                clearTimeout(retryTimeoutId);
                                if (document.body.contains(retryOverlay)) {
                                    document.body.removeChild(retryOverlay);
                                }
                                const msg = retryError.name === 'AbortError'
                                    ? 'The request took too long. Try again or use a smaller/faster model.'
                                    : retryError.message;
                                alert('Error generating cover letter: ' + msg);
                                await this.loadCoverLetter(applicationId);
                            }
                        } else {
                            // User cancelled retry
                            await this.loadCoverLetter(applicationId);
                        }
                    } else {
                        // Other error - show standard error message
                        if (document.body.contains(loadingOverlay)) {
                            document.body.removeChild(loadingOverlay);
                        }
                        alert('Error generating cover letter: ' + error.message);
                        await this.loadCoverLetter(applicationId);
                    }
                }
            },
            
            editCoverLetter(applicationId, coverLetterId) {
                const container = document.getElementById(`cover-letter-container-${applicationId}`);
                if (!container) return;
                
                const textElement = document.getElementById(`cover-letter-text-${applicationId}`);
                if (!textElement) return;
                
                const currentText = textElement.textContent;
                container.dataset.editing = 'true';
                
                container.innerHTML = `
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <textarea id="cover-letter-edit-${applicationId}" 
                                  rows="15" 
                                  class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-base text-gray-900 shadow-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y">${this.escapeHtml(currentText)}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="JobApplications.saveCoverLetter('${applicationId}', '${coverLetterId}')" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            Save Changes
                        </button>
                        <button onclick="JobApplications.cancelEditCoverLetter('${applicationId}')" 
                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                            Cancel
                        </button>
                    </div>
                `;
            },
            
            async saveCoverLetter(applicationId, coverLetterId) {
                const textarea = document.getElementById(`cover-letter-edit-${applicationId}`);
                if (!textarea) return;
                
                const text = textarea.value.trim();
                
                if (!text) {
                    alert('Cover letter text cannot be empty');
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('cover_letter_id', coverLetterId);
                    formData.append('cover_letter_text', text);
                    formData.append('csrf_token', this.csrfToken);
                    
                    const response = await fetch('/api/update-cover-letter.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Reload cover letter
                        await this.loadCoverLetter(applicationId);
                        alert('Cover letter updated successfully!');
                    } else {
                        throw new Error(result.error || 'Failed to update cover letter');
                    }
                } catch (error) {
                    console.error('Error saving cover letter:', error);
                    alert('Error saving cover letter: ' + error.message);
                }
            },
            
            cancelEditCoverLetter(applicationId) {
                // Reload cover letter to show saved version
                this.loadCoverLetter(applicationId);
            },
            
            async regenerateCoverLetter(applicationId) {
                if (!confirm('Regenerate cover letter? This will replace the current cover letter.')) {
                    return;
                }
                
                await this.generateCoverLetter(applicationId);
            },
            
            async exportCoverLetterPDF(coverLetterId) {
                try {
                    const response = await fetch(`/api/export-cover-letter-pdf.php?cover_letter_id=${coverLetterId}`);
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.error || 'Failed to export cover letter');
                    }
                    
                    const data = result.cover_letter;
                    
                    // Check if pdfMake is available
                    if (typeof pdfMake === 'undefined') {
                        // Fallback: open in new window for print
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Cover Letter - ${this.escapeHtml(data.company_name)}</title>
                                <style>
                                    body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; line-height: 1.6; }
                                    .header { margin-bottom: 30px; }
                                    .date { text-align: right; margin-bottom: 20px; }
                                    .content { white-space: pre-wrap; }
                                </style>
                            </head>
                            <body>
                                <div class="header">
                                    <div class="date">${this.escapeHtml(data.date)}</div>
                                    <div><strong>${this.escapeHtml(data.company_name)}</strong></div>
                                    <div>${this.escapeHtml(data.job_title)}</div>
                                </div>
                                <div class="content">${this.escapeHtml(data.text)}</div>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                        printWindow.print();
                        return;
                    }
                    
                    // Use pdfMake to generate PDF
                    const docDefinition = {
                        pageSize: 'A4',
                        pageMargins: [40, 60, 40, 60],
                        content: [
                            {
                                text: data.date,
                                alignment: 'right',
                                margin: [0, 0, 0, 20]
                            },
                            {
                                text: data.company_name,
                                style: 'company',
                                margin: [0, 0, 0, 5]
                            },
                            {
                                text: data.job_title,
                                style: 'jobTitle',
                                margin: [0, 0, 0, 30]
                            },
                            {
                                text: data.text,
                                style: 'body',
                                margin: [0, 0, 0, 20]
                            }
                        ],
                        styles: {
                            company: {
                                fontSize: 14,
                                bold: true,
                                margin: [0, 0, 0, 5]
                            },
                            jobTitle: {
                                fontSize: 12,
                                margin: [0, 0, 0, 30]
                            },
                            body: {
                                fontSize: 11,
                                lineHeight: 1.6
                            }
                        }
                    };
                    
                    pdfMake.createPdf(docDefinition).download(
                        `Cover_Letter_${data.company_name.replace(/[^a-z0-9_\-]/gi, '_')}.pdf`
                    );
                    
                } catch (error) {
                    console.error('Error exporting PDF:', error);
                    alert('Error exporting PDF: ' + error.message);
                }
            },
            
            async deleteCoverLetter(applicationId, coverLetterId) {
                if (!confirm('Are you sure you want to delete this cover letter? This cannot be undone.')) {
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('cover_letter_id', coverLetterId);
                    formData.append('csrf_token', this.csrfToken);
                    
                    const response = await fetch('/api/delete-cover-letter.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Reload to show empty state
                        await this.loadCoverLetter(applicationId);
                        alert('Cover letter deleted successfully');
                    } else {
                        throw new Error(result.error || 'Failed to delete cover letter');
                    }
                } catch (error) {
                    console.error('Error deleting cover letter:', error);
                    alert('Error deleting cover letter: ' + error.message);
                }
            }
        };
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Restore saved view preference
            const savedView = localStorage.getItem('jobApplicationsView') || 'cards';
            JobApplications.setView(savedView);
            
            JobApplications.loadApplications();
            JobApplications.updateStatCards();
            
            document.getElementById('status-filter').addEventListener('change', () => {
                JobApplications.currentFilter = document.getElementById('status-filter').value;
                JobApplications.filterByInterviewStatus = false;
                JobApplications.updateStatCards();
                JobApplications.renderApplications();
            });
            
            document.getElementById('search-input').addEventListener('input', () => {
                JobApplications.renderApplications();
            });
            
            document.getElementById('add-application-btn').addEventListener('click', () => {
                JobApplications.showAddModal();
            });
            
            document.getElementById('application-form').addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                JobApplications.saveApplication(formData);
            });
            
            document.getElementById('close-modal').addEventListener('click', () => {
                JobApplications.hideModal();
            });
            
            document.getElementById('cancel-modal').addEventListener('click', () => {
                JobApplications.hideModal();
            });
            
            // View modal event listeners
            document.getElementById('close-view-modal').addEventListener('click', () => {
                JobApplications.hideViewModal();
            });
            
            document.getElementById('close-view-modal-btn').addEventListener('click', () => {
                JobApplications.hideViewModal();
            });
            
            document.getElementById('edit-from-view-btn').addEventListener('click', () => {
                if (JobApplications.currentApplication) {
                    JobApplications.editApplication(JobApplications.currentApplication.id);
                }
            });
            
            // Close view modal when clicking outside
            document.getElementById('application-view-modal').addEventListener('click', (e) => {
                if (e.target.id === 'application-view-modal') {
                    JobApplications.hideViewModal();
                }
            });
            
            // File upload handlers
            const fileInput = document.getElementById('file-input');
            const fileUploadArea = document.getElementById('file-upload-area');
            
            if (fileInput && fileUploadArea) {
                // Click to upload
                fileUploadArea.addEventListener('click', (e) => {
                    if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A') {
                        fileInput.click();
                    }
                });
                
                // File input change
                fileInput.addEventListener('change', async (e) => {
                    const files = Array.from(e.target.files);
                    const applicationId = JobApplications.currentApplication?.id || null;
                    
                    for (const file of files) {
                        await JobApplications.uploadFile(file, applicationId);
                    }
                    
                    // Reset input
                    fileInput.value = '';
                });
                
                // Drag and drop
                fileUploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    fileUploadArea.classList.add('border-blue-400', 'bg-blue-50');
                });
                
                fileUploadArea.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    fileUploadArea.classList.remove('border-blue-400', 'bg-blue-50');
                });
                
                fileUploadArea.addEventListener('drop', async (e) => {
                    e.preventDefault();
                    fileUploadArea.classList.remove('border-blue-400', 'bg-blue-50');
                    
                    const files = Array.from(e.dataTransfer.files);
                    const applicationId = JobApplications.currentApplication?.id || null;
                    
                    for (const file of files) {
                        await JobApplications.uploadFile(file, applicationId);
                    }
                });
            }
        });
    </script>
</body>
</html>

