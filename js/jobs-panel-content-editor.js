/**
 * Jobs panel logic for content-editor.php#jobs
 * Loaded when the jobs section is shown; runs init and exposes filterJobsByStatus/deleteJob for inline handlers.
 */
(function() {
    'use strict';

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

    /** Decode HTML entities in a string (for display in textarea). Do not use innerHTML so HTML tags stay as literal text. */
    function decodeHtmlEntitiesInText(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&amp;/g, '&');
    }

    /** If string looks like {"text":"...\" } or { "text": "...\" } strip wrapper and return inner text. */
    function stripJsonTextWrapper(str) {
        if (typeof str !== 'string') return str;
        var t = str.trim();
        if (t.charAt(0) !== '{') return str;
        var prefixes = ['{"text":"', '{ "text": "', '{"text": "', '{ "text":"'];
        var start = -1;
        for (var i = 0; i < prefixes.length; i++) {
            if (t.indexOf(prefixes[i]) === 0) {
                start = prefixes[i].length;
                break;
            }
        }
        if (start === -1) return str;
        var end = -1;
        var suffixes = ['" }', '" }', '"}'];
        for (var j = 0; j < suffixes.length; j++) {
            var pos = t.lastIndexOf(suffixes[j]);
            if (pos > start && (end === -1 || pos > end)) end = pos;
        }
        if (end === -1) return str;
        var inner = t.slice(start, end);
        return inner.replace(/\\n/g, '\n').replace(/\\r/g, '\r').replace(/\\t/g, '\t').replace(/\\"/g, '"').replace(/\\\\/g, '\\');
    }

    var loadJobsData;
    var applicationsCache = [];
    var csrfTokenCache = '';

    var jobsCurrentView = (function() {
        try {
            var saved = localStorage.getItem('jobApplicationsView');
            return (saved === 'table' || saved === 'cards') ? saved : 'cards';
        } catch (e) { return 'cards'; }
    })();

    function setJobsView(view) {
        jobsCurrentView = view === 'table' ? 'table' : 'cards';
        try { localStorage.setItem('jobApplicationsView', jobsCurrentView); } catch (e) {}
        var cardsEl = document.getElementById('jobs-applications-cards');
        var tableEl = document.getElementById('jobs-applications-table');
        var cardsBtn = document.getElementById('jobs-view-toggle-cards');
        var tableBtn = document.getElementById('jobs-view-toggle-table');
        if (cardsEl) cardsEl.classList.toggle('hidden', jobsCurrentView !== 'cards');
        if (tableEl) tableEl.classList.toggle('hidden', jobsCurrentView !== 'table');
        if (cardsBtn) {
            if (jobsCurrentView === 'cards') {
                cardsBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                cardsBtn.classList.remove('text-gray-600');
            } else {
                cardsBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                cardsBtn.classList.add('text-gray-600');
            }
        }
        if (tableBtn) {
            if (jobsCurrentView === 'table') {
                tableBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                tableBtn.classList.remove('text-gray-600');
            } else {
                tableBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                tableBtn.classList.add('text-gray-600');
            }
        }
    }
    window.setJobsView = setJobsView;

    // Expose filterJobsByStatus immediately so stat-button onclick works before loadJobsData completes
    window.filterJobsByStatus = function(status) {
        var filter = document.getElementById('jobs-status-filter');
        if (filter) {
            filter.value = status;
            filter.dispatchEvent(new Event('change'));
        }
    };

    window.deleteJob = function(id, csrfToken) {
        if (!confirm('Are you sure you want to delete this application?')) {
            return;
        }
        var token = csrfToken || csrfTokenCache;
        fetch('/api/job-applications.php?id=' + encodeURIComponent(id), {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: token })
        })
            .then(function(response) {
                return response.json().then(function(data) {
                    if (response.ok && data.success) {
                        // Reload jobs list if loadJobsData exists, otherwise reload page
                        if (typeof loadJobsData === 'function') {
                            loadJobsData();
                        } else {
                            // Fallback: reload the page or navigate back to jobs list
                            window.location.hash = '#jobs';
                            window.location.reload();
                        }
                    } else {
                        alert(data.error || 'Error deleting application');
                    }
                });
            })
            .catch(function(err) {
                console.error('Error deleting:', err);
                alert('Error deleting application. Please try again.');
            });
    };

    function renderJobsList(applications, csrfToken) {
        var cardsContainer = document.getElementById('jobs-applications-cards');
        var tableBody = document.getElementById('jobs-table-body');
        if (!cardsContainer && !tableBody) return;

        var statusFilterEl = document.getElementById('jobs-status-filter');
        var searchInputEl = document.getElementById('jobs-search-input');
        var statusFilter = statusFilterEl ? statusFilterEl.value : 'all';
        var searchTerm = (searchInputEl ? searchInputEl.value : '').toLowerCase();

        var filtered = applications.filter(function(app) {
            var matchesStatus = statusFilter === 'all' || app.status === statusFilter;
            var matchesSearch = !searchTerm ||
                (app.company_name || '').toLowerCase().indexOf(searchTerm) !== -1 ||
                (app.job_title || '').toLowerCase().indexOf(searchTerm) !== -1;
            return matchesStatus && matchesSearch;
        });

        if (cardsContainer) {
            if (filtered.length === 0) {
                cardsContainer.innerHTML = '<div class="text-center py-12 text-gray-500 col-span-full">No applications found.</div>';
            } else {
                cardsContainer.innerHTML = filtered.map(function(app) {
                    var appliedDate = app.application_date ? new Date(app.application_date).toLocaleDateString() : '';
                    return '<div onclick="window.location.hash=\'#jobs&view=' + (app.id || '') + '\'" ' +
                        'class="border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-green-300 transition-all bg-white cursor-pointer">' +
                        '<div class="mb-3">' +
                        '<h3 class="text-lg font-semibold text-gray-900 mb-1">' + escapeHtml(app.job_title || '') + '</h3>' +
                        '<p class="text-sm text-gray-600 font-medium">' + escapeHtml(app.company_name || '') + '</p>' +
                        '</div>' +
                        '<div class="space-y-2">' +
                        (app.job_location ? '<p class="text-sm text-gray-500 flex items-center gap-1.5"><svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' + escapeHtml(app.job_location) + '</p>' : '') +
                        (app.salary_range ? '<p class="text-sm text-gray-500 flex items-center gap-1.5"><svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0-7v1m0-1c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + escapeHtml(app.salary_range) + '</p>' : '') +
                        '<p class="text-xs text-gray-400">Applied: ' + appliedDate + '</p>' +
                        '</div>' +
                        '</div>';
                }).join('');
            }
        }

        if (tableBody) {
            if (filtered.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No applications found.</td></tr>';
            } else {
                tableBody.innerHTML = filtered.map(function(app) {
                    var appliedDate = app.application_date ? new Date(app.application_date).toLocaleDateString() : '';
                    var viewHash = '#jobs&view=' + (app.id || '');
                    var editHash = '#jobs&edit=' + (app.id || '');
                    var safeViewHash = viewHash.replace(/'/g, "\\'");
                    return '<tr class="hover:bg-gray-50 cursor-pointer" role="button" tabindex="0" onclick="window.location.hash=\'' + safeViewHash + '\'" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();window.location.hash=\'' + safeViewHash + '\'}">' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' + escapeHtml(app.company_name || '') + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' + escapeHtml(app.job_title || '') + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap"><span class="status-badge status-' + (app.status || 'applied') + '">' + formatStatus(app.status) + '</span></td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(app.job_location || '') + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(app.salary_range || '') + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + appliedDate + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm" onclick="event.stopPropagation()">' +
                        '<a href="' + viewHash + '" class="text-blue-600 hover:text-blue-800 mr-3">View</a>' +
                        '<a href="' + editHash + '" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>' +
                        '<button type="button" onclick="event.stopPropagation(); deleteJob(\'' + escapeHtml(app.id || '') + '\', \'' + (csrfToken || '').replace(/'/g, "\\'") + '\'); return false;" class="text-red-600 hover:text-red-800">Delete</button>' +
                        '</td></tr>';
                }).join('');
            }
        }

        setJobsView(jobsCurrentView);
    }

    function setupJobsEventListeners(applications, csrfToken) {
        applicationsCache = applications || [];
        csrfTokenCache = csrfToken || '';

        var statusFilter = document.getElementById('jobs-status-filter');
        var searchInput = document.getElementById('jobs-search-input');
        var addBtn = document.getElementById('jobs-add-application-btn');

        if (statusFilter && !statusFilter.dataset.jobsPanelBound) {
            statusFilter.dataset.jobsPanelBound = '1';
            statusFilter.addEventListener('change', function() {
                loadJobsData();
            });
        }
        if (searchInput && !searchInput.dataset.jobsPanelBound) {
            searchInput.dataset.jobsPanelBound = '1';
            searchInput.addEventListener('input', function() {
                loadJobsData();
            });
        }
        if (addBtn && !addBtn.dataset.jobsPanelBound) {
            addBtn.dataset.jobsPanelBound = '1';
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
                            window.dispatchEvent(new HashChangeEvent('hashchange', { oldURL: window.location.href, newURL: window.location.href }));
                        }
                    }, 50);
                }
            });
        }

    }

    loadJobsData = function() {
        var container = document.getElementById('jobs-applications-container');
        if (!container) return;

        fetch('/api/job-applications.php')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                var applications = data.applications || data;
                var csrf = data.csrf_token || '';
                renderJobsList(applications, csrf);
                setupJobsEventListeners(applications, csrf);
            })
            .catch(function(error) {
                console.error('Error loading jobs:', error);
                var cardsEl = document.getElementById('jobs-applications-cards');
                var tableBody = document.getElementById('jobs-table-body');
                if (cardsEl) cardsEl.innerHTML = '<div class="text-center py-12 text-red-500 col-span-full">Error loading job applications. Please refresh the page.</div>';
                if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Error loading job applications. Please refresh the page.</td></tr>';
            });
    };

    function initJobsPanel() {
        var container = document.getElementById('jobs-applications-container');
        if (!container) return;
        setJobsView(jobsCurrentView);
        wireJobsViewToggleButtons();
        loadJobsData();
    }

    function wireJobsViewToggleButtons() {
        var cardsViewBtn = document.getElementById('jobs-view-toggle-cards');
        var tableViewBtn = document.getElementById('jobs-view-toggle-table');
        if (cardsViewBtn && !cardsViewBtn.dataset.jobsViewWired) {
            cardsViewBtn.dataset.jobsViewWired = '1';
            cardsViewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                setJobsView('cards');
            });
        }
        if (tableViewBtn && !tableViewBtn.dataset.jobsViewWired) {
            tableViewBtn.dataset.jobsViewWired = '1';
            tableViewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                setJobsView('table');
            });
        }
    }

    window.initJobsPanelContentEditor = initJobsPanel;

    /**
     * Init job view when shown in content-editor (#jobs&view=id).
     * Loads cover letter, renders empty/content, wires Generate Cover Letter + AI CV Tools.
     */
    window.initJobsView = function(container) {
        if (!container) return;
        var applicationId = container.getAttribute('data-application-id');
        var csrfToken = container.getAttribute('data-csrf');
        if (!applicationId) return;

        function esc(s) {
            if (!s) return '';
            var d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }
        var coverLetterContainerId = 'cover-letter-container-' + applicationId;

        function renderCoverLetterEmpty() {
            var el = document.getElementById(coverLetterContainerId);
            if (!el) return;
            el.innerHTML = '<div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">' +
                '<svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' +
                '<p class="text-gray-600 mb-4">No cover letter generated yet</p>' +
                '<button type="button" data-cover-letter-generate class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">' +
                '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Generate Cover Letter with AI</button></div>';
            var btn = el.querySelector('[data-cover-letter-generate]');
            if (btn) btn.addEventListener('click', function() { doGenerateCoverLetter(); });
        }

        function doExportCoverLetterPdf(coverLetterId) {
            if (!coverLetterId) return;
            fetch('/api/export-cover-letter-pdf.php?cover_letter_id=' + encodeURIComponent(coverLetterId), { credentials: 'include' })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (!result.success || !result.cover_letter) {
                        alert(result.error || 'Could not export');
                        return;
                    }
                    var d = result.cover_letter;
                    var pw = window.open('', '_blank');
                    if (pw) {
                        pw.document.write('<!DOCTYPE html><html><head><title>Cover Letter - ' + esc(d.company_name || '') + '</title><style>body{font-family:Arial,sans-serif;max-width:800px;margin:40px auto;padding:20px;line-height:1.6}.date{text-align:right;margin-bottom:20px}.content{white-space:pre-wrap}</style></head><body><div class="date">' + esc(d.date || '') + '</div><div><strong>' + esc(d.company_name || '') + '</strong></div><div>' + esc(d.job_title || '') + '</div><div class="content">' + esc(d.text || '') + '</div></body></html>');
                        pw.document.close();
                        pw.print();
                    } else {
                        alert('Please allow pop-ups to export the cover letter.');
                    }
                })
                .catch(function() { alert('Could not export cover letter.'); });
        }

        function renderCoverLetterEditMode(coverLetter) {
            var el = document.getElementById(coverLetterContainerId);
            if (!el) return;
            var id = coverLetter.id || '';
            el.innerHTML = '<div class="bg-white border border-gray-200 rounded-lg p-6">' +
                '<label for="cover-letter-edit-ta" class="block text-sm font-medium text-gray-700 mb-2">Edit cover letter</label>' +
                '<textarea id="cover-letter-edit-ta" data-cover-letter-edit-ta rows="16" class="w-full p-3 border border-gray-300 rounded-md text-gray-900 focus:ring-green-500 focus:border-green-500" placeholder="Enter cover letter text"></textarea>' +
                '</div>' +
                '<div class="flex flex-wrap gap-2 mt-3">' +
                '<button type="button" data-cover-letter-save data-cover-letter-id="' + esc(id) + '" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">Save</button>' +
                '<button type="button" data-cover-letter-cancel class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-gray-400 transition-colors">Cancel</button>' +
                '</div>';
            var ta = el.querySelector('[data-cover-letter-edit-ta]');
            if (ta) ta.value = coverLetter.cover_letter_text || '';
            var saveBtn = el.querySelector('[data-cover-letter-save]');
            if (saveBtn) saveBtn.addEventListener('click', function() {
                var text = (el.querySelector('[data-cover-letter-edit-ta]') || {}).value || '';
                var fd = new FormData();
                fd.append('cover_letter_id', saveBtn.getAttribute('data-cover-letter-id'));
                fd.append('cover_letter_text', text);
                fd.append('csrf_token', csrfToken || '');
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving…';
                fetch('/api/update-cover-letter.php', { method: 'POST', body: fd, credentials: 'include' })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result.success) {
                            loadCoverLetter();
                        } else {
                            alert(result.error || 'Could not save cover letter');
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'Save';
                        }
                    })
                    .catch(function() {
                        alert('Could not save. Please try again.');
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Save';
                    });
            });
            var cancelBtn = el.querySelector('[data-cover-letter-cancel]');
            if (cancelBtn) cancelBtn.addEventListener('click', function() { loadCoverLetter(); });

            var scrollToEdit = function() {
                el.scrollIntoView({ block: 'start', behavior: 'auto' });
            };
            scrollToEdit();
            requestAnimationFrame(scrollToEdit);
            setTimeout(scrollToEdit, 50);
        }

        function renderCoverLetter(coverLetter) {
            var el = document.getElementById(coverLetterContainerId);
            if (!el) return;
            var id = coverLetter.id || '';
            el.innerHTML = '<div class="bg-white border border-gray-200 rounded-lg p-6"><div class="prose max-w-none">' +
                '<div class="text-base text-gray-700 whitespace-pre-wrap">' + esc(coverLetter.cover_letter_text || '') + '</div></div></div>' +
                '<div id="cover-letter-actions" class="mt-3 pt-3 border-t border-gray-200 scroll-mt-6" role="group" aria-labelledby="cover-letter-actions-heading">' +
                '<p id="cover-letter-actions-heading" class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Cover letter actions</p>' +
                '<div class="flex flex-wrap gap-2">' +
                '<button type="button" data-cover-letter-edit data-cover-letter-id="' + esc(id) + '" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-green-700 bg-green-50 rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>' +
                '<button type="button" data-cover-letter-regenerate class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Regenerate with AI</button>' +
                '<button type="button" data-cover-letter-export-pdf data-cover-letter-id="' + esc(id) + '" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-gray-400 transition-colors">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Export PDF</button>' +
                '<button type="button" data-cover-letter-delete data-cover-letter-id="' + esc(id) + '" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-md border border-red-200 hover:bg-red-100 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete cover letter</button>' +
                '</div></div>';
            var editBtn = el.querySelector('[data-cover-letter-edit]');
            if (editBtn) editBtn.addEventListener('click', function() { renderCoverLetterEditMode(coverLetter); });
            var btn = el.querySelector('[data-cover-letter-regenerate]');
            if (btn) btn.addEventListener('click', function() { doGenerateCoverLetter(); });
            var exportBtn = el.querySelector('[data-cover-letter-export-pdf]');
            if (exportBtn) exportBtn.addEventListener('click', function() {
                doExportCoverLetterPdf(exportBtn.getAttribute('data-cover-letter-id'));
            });
            var deleteBtn = el.querySelector('[data-cover-letter-delete]');
            if (deleteBtn) deleteBtn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to delete this cover letter? This cannot be undone.')) return;
                var coverLetterId = deleteBtn.getAttribute('data-cover-letter-id');
                if (!coverLetterId) return;
                var fd = new FormData();
                fd.append('csrf_token', csrfToken || '');
                fd.append('cover_letter_id', coverLetterId);
                fetch('/api/delete-cover-letter.php', { method: 'POST', body: fd, credentials: 'include' })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result.success) {
                            renderCoverLetterEmpty();
                        } else {
                            alert(result.error || 'Could not delete cover letter');
                        }
                    })
                    .catch(function() { alert('Could not delete cover letter. Please try again.'); });
            });
        }

        function loadCoverLetter() {
            fetch('/api/get-cover-letter.php?application_id=' + encodeURIComponent(applicationId) + '&csrf_token=' + encodeURIComponent(csrfToken || ''), { credentials: 'include' })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success && result.cover_letter) {
                        renderCoverLetter(result.cover_letter);
                    } else {
                        renderCoverLetterEmpty();
                    }
                })
                .catch(function() { renderCoverLetterEmpty(); });
        }

        function runBrowserAICoverLetter(result, overlay) {
            return (function run() {
                var browserAIServiceAvailable = typeof BrowserAIService !== 'undefined';
                if (!browserAIServiceAvailable) {
                    if (overlay.parentNode) document.body.removeChild(overlay);
                    alert('Browser AI service not loaded. Please refresh the page.');
                    loadCoverLetter();
                    return Promise.resolve();
                }
                var support = BrowserAIService.checkBrowserSupport();
                if (!support.required) {
                    if (overlay.parentNode) document.body.removeChild(overlay);
                    alert('Browser does not support WebGPU or WebGL. Browser AI requires a modern browser with GPU support.');
                    loadCoverLetter();
                    return Promise.resolve();
                }
                var msgEl = overlay.querySelector('p');
                if (msgEl) msgEl.textContent = 'Loading AI model. This may take a few minutes on first use...';
                var modelType = (result.model_type === 'webllm') ? 'webllm' : 'tensorflow';
                return BrowserAIService.initBrowserAI(modelType, result.model, function(progress) {
                    if (msgEl && progress && progress.message) msgEl.textContent = progress.message;
                }).then(function() {
                    if (msgEl) msgEl.textContent = 'Generating cover letter... This may take 30-60 seconds.';
                    return BrowserAIService.generateText(result.prompt, { temperature: 0.8, maxTokens: 2000 });
                }).then(function(coverLetterText) {
                    if (!coverLetterText || !coverLetterText.trim()) {
                        throw new Error('Browser AI returned empty response. Please try again.');
                    }
                    var cleanedText = coverLetterText.trim();
                    if (cleanedText.charAt(0) === '{') {
                        try {
                            var parsed = JSON.parse(cleanedText);
                            if (parsed.letter) cleanedText = parsed.letter;
                            else if (parsed.cover_letter) cleanedText = parsed.cover_letter;
                            else if (parsed.text) cleanedText = parsed.text;
                            else if (parsed.content) cleanedText = parsed.content;
                        } catch (e) {
                            var letterMatch = cleanedText.match(/"letter"\s*:\s*"([^"]*(?:\\.[^"]*)*)"/s);
                            if (letterMatch) cleanedText = letterMatch[1].replace(/\\n/g, '\n').replace(/\\"/g, '"');
                        }
                    }
                    cleanedText = cleanedText.replace(/\\n/g, '\n').replace(/\\"/g, '"');
                    cleanedText = cleanedText.replace(/^"([^"]+)"$/gm, '$1');
                    cleanedText = cleanedText.replace(/^["']?\w+["']?\s*:\s*/, '');
                    cleanedText = cleanedText.trim();
                    if (!cleanedText) throw new Error('Cover letter text is empty after cleaning.');
                    var saveFd = new FormData();
                    saveFd.append('job_application_id', applicationId);
                    saveFd.append('cover_letter_text', cleanedText);
                    saveFd.append('csrf_token', csrfToken || '');
                    return fetch('/api/ai-generate-cover-letter.php', { method: 'POST', body: saveFd, credentials: 'include' }).then(function(r) { return r.json(); });
                }).then(function(saveResult) {
                    if (overlay.parentNode) document.body.removeChild(overlay);
                    if (saveResult.success) {
                        loadCoverLetter();
                        alert('Cover letter generated successfully!');
                    } else {
                        alert(saveResult.error || 'Failed to save cover letter');
                        loadCoverLetter();
                    }
                }).catch(function(err) {
                    if (overlay.parentNode) document.body.removeChild(overlay);
                    alert('Error generating cover letter: ' + (err.message || String(err)));
                    loadCoverLetter();
                });
            })();
        }

        function doGenerateCoverLetter() {
            var overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = '<div class="bg-white rounded-lg p-8 max-w-md text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div><p class="text-gray-800 font-medium">Generating cover letter with AI...</p><p class="text-gray-600 text-sm mt-2">This may take 30–90 seconds</p></div>';
            document.body.appendChild(overlay);
            var formData = new FormData();
            formData.append('job_application_id', applicationId);
            formData.append('csrf_token', csrfToken || '');
            var timeoutMs = 150000; // 2.5 minutes
            var timeoutId = setTimeout(function() {
                timeoutId = null;
                if (overlay.parentNode) document.body.removeChild(overlay);
                alert('The request took too long. The server or AI model may be busy. Try again, or if you use a local model (e.g. Ollama), try a smaller model or wait and retry.');
                loadCoverLetter();
            }, timeoutMs);
            fetch('/api/ai-generate-cover-letter.php', { method: 'POST', body: formData, credentials: 'include' })
                .then(function(r) {
                    var contentType = (r.headers.get('Content-Type') || '').toLowerCase();
                    return r.text().then(function(text) {
                        if (timeoutId) { clearTimeout(timeoutId); timeoutId = null; }
                        if (contentType.indexOf('application/json') !== -1) {
                            try { return { ok: r.ok, body: JSON.parse(text) }; } catch (e) { return { ok: false, body: null, raw: text }; }
                        }
                        return { ok: false, body: null, raw: text };
                    });
                })
                .then(function(data) {
                    if (data.body && data.body.success && !data.body.browser_execution) {
                        if (overlay.parentNode) document.body.removeChild(overlay);
                        loadCoverLetter();
                        alert('Cover letter generated successfully!');
                    } else if (data.body && data.body.success && data.body.browser_execution) {
                        if (timeoutId) clearTimeout(timeoutId);
                        runBrowserAICoverLetter(data.body, overlay);
                    } else {
                        if (overlay.parentNode) document.body.removeChild(overlay);
                        var msg = (data.body && data.body.error) ? data.body.error : null;
                        if (!msg && data.raw && (data.raw.indexOf('Fatal error') !== -1 || data.raw.indexOf('Maximum execution time') !== -1)) {
                            msg = 'The server ran out of time while generating. Try again or use a smaller/faster AI model.';
                        }
                        alert(msg || 'Could not generate cover letter. Please try again.');
                        loadCoverLetter();
                    }
                })
                .catch(function(err) {
                    if (timeoutId) clearTimeout(timeoutId);
                    if (overlay.parentNode) document.body.removeChild(overlay);
                    alert('Could not generate cover letter. Check your connection and try again. If the problem continues, the server or AI service may be unavailable.');
                    loadCoverLetter();
                });
        }

        loadCoverLetter();

        var genBtn = container.querySelector('[data-cover-letter-generate]');
        if (genBtn) genBtn.addEventListener('click', function(e) { e.preventDefault(); doGenerateCoverLetter(); });

        if (!container.dataset.jobsViewDelegated) {
            container.dataset.jobsViewDelegated = '1';
            container.addEventListener('click', function(e) {
                var del = e.target.closest('[data-jobs-delete]');
                var back = e.target.closest('[data-jobs-back]');
                var editLink = e.target.closest('[data-jobs-edit]');
                if (del) {
                    e.preventDefault();
                    if (!confirm('Are you sure you want to delete this application?')) return;
                    var id = del.getAttribute('data-job-id');
                    var tok = del.getAttribute('data-csrf');
                    fetch('/api/job-applications.php?id=' + encodeURIComponent(id), {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ csrf_token: tok }),
                        credentials: 'include'
                    }).then(function(r) {
                        if (r.ok) window.location.hash = '#jobs';
                        else alert('Could not delete. Please try again.');
                    }).catch(function() { alert('Could not delete. Please try again.'); });
                } else if (back) {
                    e.preventDefault();
                    window.location.hash = '#jobs';
                } else if (editLink) {
                    e.preventDefault();
                    var id = editLink.getAttribute('data-edit-id');
                    if (id) window.location.hash = '#jobs&edit=' + id;
                }
            });
        }

        var aiCvBtn = container.querySelector('[data-ai-cv-generate]');
        if (aiCvBtn) {
            aiCvBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('This will generate a new AI-rewritten CV variant for this job application. Continue?')) return;
                var fd = new FormData();
                fd.append('csrf_token', csrfToken || '');
                fd.append('job_application_id', applicationId);
                fetch('/api/ai-rewrite-cv.php', { method: 'POST', body: fd, credentials: 'include' })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result.success) {
                            alert('CV generated successfully!');
                            window.location.hash = '#cv-variants';
                            if (typeof window.contentEditor !== 'undefined' && typeof window.contentEditor.loadSection === 'function') {
                                setTimeout(function() { window.contentEditor.loadSection('cv-variants'); }, 300);
                            }
                        } else {
                            alert('Error: ' + (result.error || 'Failed to generate CV'));
                        }
                    })
                    .catch(function() { alert('An error occurred. Please try again.'); });
            });
        }
    };

    /**
     * Init job edit form when shown in content-editor (#jobs&edit=id).
     * Reads data-initial-job from container, fills form, binds submit/file handlers.
     */
    window.initJobsEditForm = function(container) {
        if (!container) return;
        var raw = container.getAttribute('data-initial-job');
        var jobData = {};
        try {
            if (raw) jobData = JSON.parse(raw);
        } catch (e) {
            console.error('jobs edit: invalid data-initial-job', e);
        }
        var applicationId = container.getAttribute('data-application-id');
        var csrfToken = container.getAttribute('data-csrf');

        function decodeHtmlEntities(str) {
            if (!str) return '';
            var text = document.createElement('textarea');
            text.innerHTML = str;
            return text.value;
        }
        function g(id) { return document.getElementById(id); }
        if (g('form-company')) {
            g('form-company').value = decodeHtmlEntities(jobData.company_name || '');
            g('form-job-title').value = decodeHtmlEntities(jobData.job_title || '');
            if (g('form-description')) g('form-description').value = decodeHtmlEntities(jobData.job_description || '');
            g('form-status').value = jobData.status || 'applied';
            g('form-location').value = decodeHtmlEntities(jobData.job_location || '');
            g('form-salary').value = decodeHtmlEntities(jobData.salary_range || '');
            g('form-remote').value = jobData.remote_type || 'onsite';
            g('form-url').value = decodeHtmlEntities(jobData.application_url || '');
            g('form-notes').value = decodeHtmlEntities(jobData.notes || '');
            g('form-date').value = (jobData.application_date || '').toString().split(' ')[0];
            g('form-followup').value = (jobData.next_follow_up || '').toString().split(' ')[0];
            g('form-interview').checked = !!jobData.had_interview;
        }

        // Initialize markdown editors after form is populated
        setTimeout(function() {
            if (typeof window.MarkdownEditor !== 'undefined' && window.MarkdownEditor.initAll) {
                window.MarkdownEditor.initAll();
            } else {
                // If MarkdownEditor not loaded yet, try again after a delay
                setTimeout(function() {
                    if (typeof window.MarkdownEditor !== 'undefined' && window.MarkdownEditor.initAll) {
                        window.MarkdownEditor.initAll();
                    }
                }, 200);
            }
        }, 150);

        var currentFiles = [];
        function escapeHtmlSafe(t) {
            if (!t) return '';
            var d = document.createElement('div');
            d.textContent = t;
            return d.innerHTML;
        }
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024, s = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + s[i];
        }
        function renderFiles() {
            var list = document.getElementById('file-list');
            if (!list) return;
            if (currentFiles.length === 0) { list.innerHTML = ''; return; }
            list.innerHTML = currentFiles.map(function(file) {
                var name = file.custom_name || file.original_name;
                var size = formatFileSize(file.size || 0);
                var url = file.url || '#';
                return '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200" data-file-id="' + escapeHtmlSafe(file.id) + '">' +
                    '<div class="flex items-center space-x-3 flex-1 min-w-0"><div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900 truncate">' + escapeHtmlSafe(name) + '</p><p class="text-xs text-gray-500">' + escapeHtmlSafe(size) + '</p></div></div>' +
                    '<div class="flex items-center space-x-2 ml-4">' +
                    '<button type="button" data-file-extract data-file-id="' + escapeHtmlSafe(file.id) + '" class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded">Extract Text</button>' +
                    '<a href="' + escapeHtmlSafe(url) + '" target="_blank" rel="noopener" class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded">Download</a>' +
                    '<button type="button" data-file-delete data-file-id="' + escapeHtmlSafe(file.id) + '" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded">Delete</button></div></div>';
            }).join('');
        }
        // One-time event delegation on file-list so Extract/Delete work after any re-render
        var fileListEl = container.querySelector('#file-list') || document.getElementById('file-list');
        if (fileListEl && !fileListEl.dataset.jobsFileListBound) {
            fileListEl.dataset.jobsFileListBound = '1';
            fileListEl.addEventListener('click', function(e) {
                var extractBtn = e.target.closest('[data-file-extract]');
                var deleteBtn = e.target.closest('[data-file-delete]');
                if (deleteBtn) {
                    var id = deleteBtn.getAttribute('data-file-id');
                    if (!confirm('Delete this file?')) return;
                    var fd = new FormData();
                    fd.append('file_id', id);
                    fd.append('csrf_token', csrfToken);
                    fetch('/api/delete-job-application-file.php', { method: 'POST', body: fd, credentials: 'include' })
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d.success) { currentFiles = currentFiles.filter(function(f) { return f.id !== id; }); renderFiles(); }
                            else alert(d.error || 'Could not delete');
                        })
                        .catch(function() { alert('Could not delete file.'); });
                    return;
                }
                if (extractBtn) {
                    var id = extractBtn.getAttribute('data-file-id');
                    if (!confirm('Extract text into job description? This will replace current text.')) return;
                    var originalLabel = extractBtn.textContent;
                    extractBtn.disabled = true;
                    extractBtn.innerHTML = '<svg class="animate-spin h-3.5 w-3.5 inline-block mr-1.5 -mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Extracting…';
                    var fd = new FormData();
                    fd.append('file_id', id);
                    fd.append('csrf_token', csrfToken);
                    var formatCheck = document.getElementById('format-extract-with-ai');
                    if (formatCheck && formatCheck.checked) fd.append('format_with_ai', '1');
                    fetch('/api/extract-job-file-text.php', { method: 'POST', body: fd, credentials: 'include' })
                        .then(function(r) {
                            return r.json().then(function(d) {
                                var text = d && (d.text != null) ? d.text : '';
                                if (typeof text === 'string' && text.trim().charAt(0) === '{') {
                                    try {
                                        var parsed = JSON.parse(text);
                                        if (parsed && typeof parsed.text === 'string') text = parsed.text;
                                    } catch (e) {
                                        text = stripJsonTextWrapper(text);
                                    }
                                }
                                if (r.ok && text) {
                                    text = decodeHtmlEntitiesInText(text);
                                    var el = g('form-description');
                                    if (el) {
                                        el.value = text;
                                        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                    }
                                    showExtractSuccessMessage();
                                } else {
                                    alert(d && d.error ? d.error : 'Could not extract text.');
                                }
                            });
                        })
                        .catch(function() { alert('Could not extract text. Check the file type is supported (PDF, Word, Excel, text) and try again.'); })
                        .finally(function() {
                            extractBtn.disabled = false;
                            extractBtn.textContent = originalLabel;
                        });
                }
            });
        }
        function showExtractSuccessMessage() {
            var label = document.querySelector('label[for="form-description"]');
            var existing = document.getElementById('extract-success-msg');
            if (existing) existing.remove();
            var msg = document.createElement('p');
            msg.id = 'extract-success-msg';
            msg.setAttribute('role', 'status');
            msg.className = 'mt-2 text-sm font-medium text-green-600';
            msg.textContent = 'Text extracted and added to job description.';
            if (label && label.parentNode) {
                label.parentNode.insertBefore(msg, label.nextSibling);
            } else {
                var desc = g('form-description');
                if (desc && desc.parentNode) desc.parentNode.insertBefore(msg, desc);
            }
            setTimeout(function() {
                if (msg.parentNode) msg.parentNode.removeChild(msg);
            }, 4000);
        }
        if (applicationId) {
            fetch('/api/job-applications.php?id=' + encodeURIComponent(applicationId), { credentials: 'include' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    currentFiles = Array.isArray(data.files) ? data.files : [];
                    renderFiles();
                })
                .catch(function() { currentFiles = []; renderFiles(); });
        }

        var form = document.getElementById('application-form');
        var fileInput = document.getElementById('file-input');
        var uploadArea = document.getElementById('file-upload-area');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var editableDesc = g('job-description-editable');
                if (editableDesc) {
                    var hidden = g('form-description-hidden');
                    if (hidden) hidden.value = editableDesc.innerHTML;
                }
                var fd = new FormData(form);
                var payload = {
                    company_name: fd.get('company_name'),
                    job_title: fd.get('job_title'),
                    job_description: fd.get('job_description'),
                    application_date: fd.get('application_date'),
                    status: fd.get('status'),
                    salary_range: fd.get('salary_range'),
                    job_location: fd.get('job_location'),
                    remote_type: fd.get('remote_type'),
                    application_url: fd.get('application_url'),
                    notes: fd.get('notes'),
                    next_follow_up: fd.get('next_follow_up') || null,
                    had_interview: fd.get('had_interview') === 'on',
                    csrf_token: csrfToken
                };
                var btn = form.querySelector('button[type="submit"]');
                if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
                fetch('/api/job-applications.php?id=' + encodeURIComponent(applicationId), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    credentials: 'include'
                })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success) {
                        window.location.hash = '#jobs&view=' + applicationId;
                    } else {
                        alert(result.error || 'Could not save');
                        if (btn) { btn.disabled = false; btn.textContent = 'Save Application'; }
                    }
                })
                .catch(function() {
                    alert('Could not save. Please try again.');
                    if (btn) { btn.disabled = false; btn.textContent = 'Save Application'; }
                });
            });
        }
        if (fileInput && uploadArea) {
            uploadArea.addEventListener('click', function() { fileInput.click(); });
            fileInput.addEventListener('change', function() {
                var files = Array.from(fileInput.files || []);
                fileInput.value = '';
                files.forEach(function(file) {
                    var formData = new FormData();
                    formData.append('file', file);
                    formData.append('application_id', applicationId || '');
                    formData.append('file_purpose', 'other');
                    formData.append('csrf_token', csrfToken);
                    fetch('/api/upload-job-application-file.php', { method: 'POST', body: formData, credentials: 'include' })
                        .then(function(r) { return r.json(); })
                        .then(function(result) {
                            if (result.success && result.file) {
                                currentFiles.push(result.file);
                                renderFiles();
                            } else alert(result.error || 'Upload failed');
                        })
                        .catch(function() { alert('Upload failed.'); });
                });
            });
        }
        var backEl = container.querySelector('[data-jobs-back-to-view]');
        if (backEl) {
            backEl.addEventListener('click', function(ev) {
                ev.preventDefault();
                window.location.hash = '#jobs&view=' + applicationId;
            });
        }
    };

    /**
     * Init job add form when shown in content-editor (#jobs&add=1).
     * Binds submit/file handlers for creating new application.
     */
    window.initJobsAddForm = function(container) {
        if (!container) return;
        var csrfToken = container.getAttribute('data-csrf');

        // Initialize markdown editors
        setTimeout(function() {
            if (typeof window.MarkdownEditor !== 'undefined' && window.MarkdownEditor.initAll) {
                window.MarkdownEditor.initAll();
            } else {
                // If MarkdownEditor not loaded yet, try again after a delay
                setTimeout(function() {
                    if (typeof window.MarkdownEditor !== 'undefined' && window.MarkdownEditor.initAll) {
                        window.MarkdownEditor.initAll();
                    }
                }, 200);
            }
        }, 150);

        var currentFiles = [];
        function escapeHtmlSafe(t) {
            if (!t) return '';
            var d = document.createElement('div');
            d.textContent = t;
            return d.innerHTML;
        }
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024, s = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + s[i];
        }
        function renderFiles() {
            var list = document.getElementById('file-list');
            if (!list) return;
            if (currentFiles.length === 0) { list.innerHTML = ''; return; }
            list.innerHTML = currentFiles.map(function(file) {
                var name = file.custom_name || file.original_name;
                var size = formatFileSize(file.size || 0);
                var url = file.url || '#';
                return '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200" data-file-id="' + escapeHtmlSafe(file.id) + '">' +
                    '<div class="flex items-center space-x-3 flex-1 min-w-0"><div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900 truncate">' + escapeHtmlSafe(name) + '</p><p class="text-xs text-gray-500">' + escapeHtmlSafe(size) + '</p></div></div>' +
                    '<div class="flex items-center space-x-2 ml-4">' +
                    '<button type="button" data-file-extract data-file-id="' + escapeHtmlSafe(file.id) + '" class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded">Extract Text</button>' +
                    '<a href="' + escapeHtmlSafe(url) + '" target="_blank" rel="noopener" class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded">Download</a>' +
                    '<button type="button" data-file-delete data-file-id="' + escapeHtmlSafe(file.id) + '" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded">Delete</button></div></div>';
            }).join('');
        }
        // One-time event delegation on file-list so Extract/Delete work after any re-render
        var fileListElAdd = container.querySelector('#file-list') || document.getElementById('file-list');
        if (fileListElAdd && !fileListElAdd.dataset.jobsFileListBound) {
            fileListElAdd.dataset.jobsFileListBound = '1';
            fileListElAdd.addEventListener('click', function(e) {
                var extractBtn = e.target.closest('[data-file-extract]');
                var deleteBtn = e.target.closest('[data-file-delete]');
                if (deleteBtn) {
                    var id = deleteBtn.getAttribute('data-file-id');
                    if (!confirm('Delete this file?')) return;
                    var fd = new FormData();
                    fd.append('file_id', id);
                    fd.append('csrf_token', csrfToken);
                    fetch('/api/delete-job-application-file.php', { method: 'POST', body: fd, credentials: 'include' })
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d.success) { currentFiles = currentFiles.filter(function(f) { return f.id !== id; }); renderFiles(); }
                            else alert(d.error || 'Could not delete');
                        })
                        .catch(function() { alert('Could not delete file.'); });
                    return;
                }
                if (extractBtn) {
                    var id = extractBtn.getAttribute('data-file-id');
                    if (!confirm('Extract text into job description? This will replace current text.')) return;
                    var originalLabelAdd = extractBtn.textContent;
                    extractBtn.disabled = true;
                    extractBtn.innerHTML = '<svg class="animate-spin h-3.5 w-3.5 inline-block mr-1.5 -mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Extracting…';
                    var fd = new FormData();
                    fd.append('file_id', id);
                    fd.append('csrf_token', csrfToken);
                    var formatCheck = document.getElementById('format-extract-with-ai');
                    if (formatCheck && formatCheck.checked) fd.append('format_with_ai', '1');
                    fetch('/api/extract-job-file-text.php', { method: 'POST', body: fd, credentials: 'include' })
                        .then(function(r) {
                            return r.json().then(function(d) {
                                var text = d && (d.text != null) ? d.text : '';
                                if (typeof text === 'string' && text.trim().charAt(0) === '{') {
                                    try {
                                        var parsed = JSON.parse(text);
                                        if (parsed && typeof parsed.text === 'string') text = parsed.text;
                                    } catch (e) {
                                        text = stripJsonTextWrapper(text);
                                    }
                                }
                                if (r.ok && text) {
                                    text = decodeHtmlEntitiesInText(text);
                                    var descEl = document.getElementById('form-description');
                                    if (descEl) {
                                        descEl.value = text;
                                        descEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                    }
                                    showExtractSuccessMessageAdd();
                                } else {
                                    alert(d && d.error ? d.error : 'Could not extract text.');
                                }
                            });
                        })
                        .catch(function() { alert('Could not extract text. Check the file type is supported (PDF, Word, Excel, text) and try again.'); })
                        .finally(function() {
                            extractBtn.disabled = false;
                            extractBtn.textContent = originalLabelAdd;
                        });
                }
            });
        }
        function showExtractSuccessMessageAdd() {
            var existing = document.getElementById('extract-success-msg');
            if (existing) existing.remove();
            var msg = document.createElement('p');
            msg.id = 'extract-success-msg';
            msg.setAttribute('role', 'status');
            msg.className = 'mt-2 text-sm font-medium text-green-600';
            msg.textContent = 'Text extracted and added to job description.';
            var label = document.querySelector('label[for="form-description"]');
            if (label && label.parentNode) {
                label.parentNode.insertBefore(msg, label.nextSibling);
            } else {
                var desc = document.getElementById('form-description');
                if (desc && desc.parentNode) desc.parentNode.insertBefore(msg, desc);
            }
            setTimeout(function() {
                if (msg.parentNode) msg.parentNode.removeChild(msg);
            }, 4000);
        }

        var form = document.getElementById('application-form');
        var fileInput = document.getElementById('file-input');
        var uploadArea = document.getElementById('file-upload-area');
        var pendingFiles = []; // Store files to upload after application is created
        
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var fd = new FormData(form);
                var payload = {
                    company_name: fd.get('company_name'),
                    job_title: fd.get('job_title'),
                    job_description: fd.get('job_description'),
                    application_date: fd.get('application_date'),
                    status: fd.get('status'),
                    salary_range: fd.get('salary_range'),
                    job_location: fd.get('job_location'),
                    remote_type: fd.get('remote_type'),
                    application_url: fd.get('application_url'),
                    notes: fd.get('notes'),
                    next_follow_up: fd.get('next_follow_up') || null,
                    had_interview: fd.get('had_interview') === 'on',
                    csrf_token: csrfToken
                };
                var btn = form.querySelector('button[type="submit"]');
                if (btn) { btn.disabled = true; btn.textContent = 'Creating…'; }
                
                // Create application first
                fetch('/api/job-applications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    credentials: 'include'
                })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success && result.id) {
                        var newApplicationId = result.id;
                        // Upload any pending files
                        if (pendingFiles.length > 0) {
                            var uploadPromises = pendingFiles.map(function(file) {
                                var formData = new FormData();
                                formData.append('file', file);
                                formData.append('application_id', newApplicationId);
                                formData.append('file_purpose', 'other');
                                formData.append('csrf_token', csrfToken);
                                return fetch('/api/upload-job-application-file.php', { method: 'POST', body: formData, credentials: 'include' });
                            });
                            Promise.all(uploadPromises).then(function() {
                                window.location.hash = '#jobs&view=' + newApplicationId;
                            }).catch(function() {
                                // Files failed but application created - still redirect
                                window.location.hash = '#jobs&view=' + newApplicationId;
                            });
                        } else {
                            window.location.hash = '#jobs&view=' + newApplicationId;
                        }
                    } else {
                        alert(result.error || 'Could not create application');
                        if (btn) { btn.disabled = false; btn.textContent = 'Add Application'; }
                    }
                })
                .catch(function() {
                    alert('Could not create application. Please try again.');
                    if (btn) { btn.disabled = false; btn.textContent = 'Add Application'; }
                });
            });
        }
        
        if (fileInput && uploadArea) {
            uploadArea.addEventListener('click', function() { fileInput.click(); });
            fileInput.addEventListener('change', function() {
                var files = Array.from(fileInput.files || []);
                fileInput.value = '';
                // Store files to upload after application is created
                pendingFiles = pendingFiles.concat(files);
                // Show preview of pending files
                renderPendingFiles();
            });
        }
        
        function renderPendingFiles() {
            var list = document.getElementById('file-list');
            if (!list) return;
            if (pendingFiles.length === 0) { list.innerHTML = ''; return; }
            list.innerHTML = pendingFiles.map(function(file, idx) {
                var size = formatFileSize(file.size || 0);
                return '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200" data-pending-file-idx="' + idx + '">' +
                    '<div class="flex items-center space-x-3 flex-1 min-w-0"><div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900 truncate">' + escapeHtmlSafe(file.name) + '</p><p class="text-xs text-gray-500">' + escapeHtmlSafe(size) + ' (will upload after saving)</p></div></div>' +
                    '<div class="flex items-center space-x-2 ml-4">' +
                    '<button type="button" data-pending-file-remove data-pending-file-idx="' + idx + '" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded">Remove</button></div></div>';
            }).join('');
            list.querySelectorAll('[data-pending-file-remove]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var idx = parseInt(btn.getAttribute('data-pending-file-idx'));
                    pendingFiles.splice(idx, 1);
                    renderPendingFiles();
                });
            });
        }
        
        var backEl = container.querySelector('[data-jobs-back-to-list]');
        if (backEl) {
            backEl.addEventListener('click', function(ev) {
                ev.preventDefault();
                window.location.hash = '#jobs';
            });
        }
    };

    initJobsPanel();
})();
