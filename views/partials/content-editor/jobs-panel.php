<?php
/**
 * Jobs Panel Component
 * Job management interface for content editor
 */

if (!function_exists('getJobApplicationStats')) {
    require_once __DIR__ . '/../../../php/job-applications.php';
}

$userId = getUserId();
$stats = getJobApplicationStats($userId);
?>
<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Job Applications</h1>
            <p class="mt-1 text-sm text-gray-500">Track and manage your job applications</p>
        </div>

        <!-- Statistics: single row, compact, no wrap -->
        <div class="flex flex-nowrap gap-2 overflow-x-auto pb-1 mb-6 scrollbar-thin">
            <button type="button" 
                    onclick="filterJobsByStatus('all')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-blue-300 whitespace-nowrap"
                    data-status="all">
                <span class="text-xs text-gray-500">Total</span> <span class="text-lg font-bold text-gray-900"><?php echo $stats['total']; ?></span>
            </button>
            <button type="button" 
                    onclick="filterJobsByStatus('applied')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-yellow-300 whitespace-nowrap"
                    data-status="applied">
                <span class="text-xs text-gray-500">Applied</span> <span class="text-lg font-bold text-yellow-600"><?php echo $stats['applied']; ?></span>
            </button>
            <button type="button" 
                    onclick="filterJobsByStatus('interviewing')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-purple-300 whitespace-nowrap"
                    data-status="interviewing">
                <span class="text-xs text-gray-500">Interviewing</span> <span class="text-lg font-bold text-purple-600"><?php echo $stats['interviewing']; ?></span>
            </button>
            <button type="button" 
                    onclick="filterJobsByStatus('offered')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-blue-300 whitespace-nowrap"
                    data-status="offered">
                <span class="text-xs text-gray-500">Offered</span> <span class="text-lg font-bold text-blue-600"><?php echo $stats['offered']; ?></span>
            </button>
            <button type="button" 
                    onclick="filterJobsByStatus('accepted')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-green-300 whitespace-nowrap"
                    data-status="accepted">
                <span class="text-xs text-gray-500">Accepted</span> <span class="text-lg font-bold text-green-600"><?php echo $stats['accepted']; ?></span>
            </button>
            <button type="button" 
                    onclick="filterJobsByStatus('rejected')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-red-300 whitespace-nowrap"
                    data-status="rejected">
                <span class="text-xs text-gray-500">Rejected</span> <span class="text-lg font-bold text-red-600"><?php echo $stats['rejected']; ?></span>
            </button>
            <button type="button" 
                    onclick="filterJobsByStatus('all')"
                    class="stat-card flex-shrink-0 bg-white rounded-lg shadow px-3 py-2 hover:shadow-lg transition-shadow cursor-pointer text-left border-2 border-transparent hover:border-indigo-300 whitespace-nowrap"
                    data-filter="interview">
                <span class="text-xs text-gray-500">Interviews</span> <span class="text-lg font-bold text-indigo-600"><?php echo $stats['had_interview']; ?></span>
            </button>
        </div>

        <!-- Actions Bar -->
        <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
            <div class="flex flex-wrap gap-4 flex-1 items-center">
                <select id="jobs-status-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="all">All Status</option>
                    <option value="applied">Applied</option>
                    <option value="interviewing">Interviewing</option>
                    <option value="offered">Offered</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    <option value="withdrawn">Withdrawn</option>
                    <option value="in_progress">In Progress</option>
                </select>
                <input type="text" 
                       id="jobs-search-input" 
                       placeholder="Search by company or job title..."
                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm flex-1 min-w-64">
                <!-- View Toggle: Card vs Table -->
                <div class="flex items-center bg-gray-100 rounded-lg p-1" role="group" aria-label="View">
                    <button type="button" id="jobs-view-toggle-cards" 
                            class="jobs-view-toggle-btn px-2.5 py-1.5 rounded-md text-sm font-medium transition-colors bg-white text-gray-900 shadow-sm"
                            title="Card view">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button type="button" id="jobs-view-toggle-table" 
                            class="jobs-view-toggle-btn px-2.5 py-1.5 rounded-md text-sm font-medium transition-colors text-gray-600 hover:text-gray-900"
                            title="Table view">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
            <button id="jobs-add-application-btn" 
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Application
            </button>
        </div>

        <!-- Applications Container -->
        <div id="jobs-applications-container" class="bg-white rounded-lg shadow">
            <div class="p-6">
                <!-- Cards View -->
                <div id="jobs-applications-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="text-center py-12 text-gray-500 col-span-full">Loading applications...</div>
                </div>
                <!-- Table View -->
                <div id="jobs-applications-table" class="hidden overflow-x-auto">
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
                        <tbody id="jobs-table-body" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">Loading applications...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Job Application Modal (will be loaded dynamically) -->
<div id="jobs-application-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
    <!-- Modal content will be loaded here -->
</div>

<script>
// Initialize jobs panel - adapt JobApplications from job-applications.php
(function() {
    function initJobsPanel() {
        // Check if we're in the content editor jobs panel
        const container = document.getElementById('jobs-applications-container');
        if (!container) return;
        
        // Load job applications via API and render manually
        loadJobsData();
    }
    
    async function loadJobsData() {
        try {
            const response = await fetch('/api/job-applications.php');
            const data = await response.json();
            const applications = data.applications || data;
            const csrfToken = data.csrf_token || '';
            
            renderJobsList(applications, csrfToken);
            setupJobsEventListeners(applications, csrfToken);
        } catch (error) {
            console.error('Error loading jobs:', error);
            var cardsEl = document.getElementById('jobs-applications-cards');
            var tableBody = document.getElementById('jobs-table-body');
            if (cardsEl) cardsEl.innerHTML = '<div class="text-center py-12 text-red-500 col-span-full">Error loading job applications. Please refresh the page.</div>';
            if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Error loading job applications. Please refresh the page.</td></tr>';
        }
    }
    
    function renderJobsList(applications, csrfToken) {
        const cardsEl = document.getElementById('jobs-applications-cards');
        const tableBody = document.getElementById('jobs-table-body');
        if (!cardsEl && !tableBody) return;
        
        const statusFilter = document.getElementById('jobs-status-filter')?.value || 'all';
        const searchTerm = (document.getElementById('jobs-search-input')?.value || '').toLowerCase();
        
        const filtered = applications.filter(app => {
            const matchesStatus = statusFilter === 'all' || app.status === statusFilter;
            const matchesSearch = !searchTerm || 
                (app.company_name || '').toLowerCase().includes(searchTerm) ||
                (app.job_title || '').toLowerCase().includes(searchTerm);
            return matchesStatus && matchesSearch;
        });
        
        const cardsHtml = filtered.length === 0
            ? '<div class="text-center py-12 text-gray-500 col-span-full">No applications found.</div>'
            : filtered.map(app => `
                <div onclick="window.location.hash='#jobs&view=${app.id}'" 
                     class="border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-green-300 transition-all bg-white cursor-pointer">
                    <div class="mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(app.job_title || '')}</h3>
                        <p class="text-sm text-gray-600 font-medium">${escapeHtml(app.company_name || '')}</p>
                    </div>
                    <div class="space-y-2">
                        ${app.job_location ? `<p class="text-sm text-gray-500 flex items-center gap-1.5"><svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>${escapeHtml(app.job_location)}</p>` : ''}
                        ${app.salary_range ? `<p class="text-sm text-gray-500 flex items-center gap-1.5"><svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0-7v1m0-1c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>${escapeHtml(app.salary_range)}</p>` : ''}
                        <p class="text-xs text-gray-400">Applied: ${new Date(app.application_date).toLocaleDateString()}</p>
                    </div>
                </div>
            `).join('');
        const tableRowsHtml = filtered.length === 0
            ? '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No applications found.</td></tr>'
            : filtered.map(app => {
                const appliedDate = new Date(app.application_date).toLocaleDateString();
                const viewHash = '#jobs&view=' + app.id;
                const editHash = '#jobs&edit=' + app.id;
                return '<tr class="hover:bg-gray-50 cursor-pointer" role="button" tabindex="0" onclick="window.location.hash=\'' + viewHash.replace(/'/g, "\\'") + '\'" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();window.location.hash=\'' + viewHash.replace(/'/g, "\\'") + '\'}">' +
                    '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' + escapeHtml(app.company_name || '') + '</td>' +
                    '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' + escapeHtml(app.job_title || '') + '</td>' +
                    '<td class="px-6 py-4 whitespace-nowrap"><span class="status-badge status-' + (app.status || 'applied') + '">' + formatStatus(app.status) + '</span></td>' +
                    '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(app.job_location || '') + '</td>' +
                    '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(app.salary_range || '') + '</td>' +
                    '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + appliedDate + '</td>' +
                    '<td class="px-6 py-4 whitespace-nowrap text-sm" onclick="event.stopPropagation()">' +
                    '<a href="' + viewHash + '" class="text-blue-600 hover:text-blue-800 mr-3">View</a>' +
                    '<a href="' + editHash + '" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>' +
                    '<button type="button" onclick="event.stopPropagation(); deleteJob(\'' + (app.id || '').replace(/'/g, "\\'") + '\', \'' + (csrfToken || '').replace(/'/g, "\\'") + '\'); return false;" class="text-red-600 hover:text-red-800">Delete</button>' +
                    '</td></tr>';
            }).join('');
        if (cardsEl) cardsEl.innerHTML = cardsHtml;
        if (tableBody) tableBody.innerHTML = tableRowsHtml;
    }
    
    function setupJobsEventListeners(applications, csrfToken) {
        const statusFilter = document.getElementById('jobs-status-filter');
        const searchInput = document.getElementById('jobs-search-input');
        const addBtn = document.getElementById('jobs-add-application-btn');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                loadJobsData();
            });
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                loadJobsData();
            });
        }
        
        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var currentHash = window.location.hash.substring(1);
                window.location.hash = '#jobs&add=1';
                // If we're already on jobs section, manually trigger loadSection since hashchange might not fire
                if (currentHash === 'jobs' || currentHash.startsWith('jobs&')) {
                    setTimeout(function() {
                        if (typeof window.contentEditor !== 'undefined' && typeof window.contentEditor.loadSection === 'function') {
                            window.contentEditor.loadSection('jobs');
                        } else {
                            // Fallback: trigger hashchange event
                            window.dispatchEvent(new HashChangeEvent('hashchange', { oldURL: window.location.href, newURL: window.location.href }));
                        }
                    }, 50);
                }
            });
        }
    }
    
    window.filterJobsByStatus = function(status) {
        const filter = document.getElementById('jobs-status-filter');
        if (filter) {
            filter.value = status;
            filter.dispatchEvent(new Event('change'));
        }
    };
    
    function formatStatus(status) {
        const statusMap = {
            'applied': 'Applied',
            'interviewing': 'Interviewing',
            'offered': 'Offered',
            'accepted': 'Accepted',
            'rejected': 'Rejected',
            'withdrawn': 'Withdrawn',
            'in_progress': 'In Progress'
        };
        return statusMap[status] || status;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    window.deleteJob = async function(id, csrfToken) {
        if (!confirm('Are you sure you want to delete this application?')) {
            return;
        }
        
        try {
            const response = await fetch(`/api/job-applications.php?id=${id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: csrfToken })
            });
            
            if (response.ok) {
                loadJobsData();
            } else {
                alert('Error deleting application');
            }
        } catch (error) {
            console.error('Error deleting:', error);
            alert('Error deleting application');
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initJobsPanel);
    } else {
        initJobsPanel();
    }
})();
</script>
