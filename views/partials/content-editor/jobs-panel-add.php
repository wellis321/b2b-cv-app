<?php
/**
 * Add new job application inline in content-editor (#jobs&add=1)
 * Same form design as edit form but without pre-filled data
 */
$csrf = csrfToken();
?>
<div class="p-6 max-w-3xl mx-auto" data-jobs-add-form data-csrf="<?php echo e($csrf); ?>">
    <div class="mb-4">
        <a href="#jobs" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900" data-jobs-back-to-list>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to list
        </a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Add new application</h1>

        <form id="application-form" class="space-y-6" data-jobs-add-form-el>
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
                <p class="text-xs text-gray-500 mb-1">Use the toolbar for formatting: bold, italic, headers, lists, and links</p>
                <textarea id="form-description" name="job_description" rows="4" data-markdown
                          class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none resize-y"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
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
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="form-date" class="block text-base font-semibold text-gray-900 mb-3">Application Date</label>
                    <input type="date" id="form-date" name="application_date"
                           class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                </div>
                <div>
                    <label for="form-followup" class="block text-base font-semibold text-gray-900 mb-3">Follow-up / closing date</label>
                    <input type="date" id="form-followup" name="next_follow_up"
                           class="block w-full rounded-lg border-2 border-gray-400 bg-white px-4 py-3 text-base font-medium text-gray-900 shadow-sm transition-colors focus:border-blue-600 focus:ring-4 focus:ring-blue-200 focus:outline-none">
                    <p class="mt-2 text-sm text-gray-600 font-medium">Deadline or when you want to follow up</p>
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
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="file-input" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload files</span>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, Word, Excel, Text, Images (MAX. 10MB)</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <input type="checkbox" id="format-extract-with-ai" name="format_extract_with_ai" value="1" checked
                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="format-extract-with-ai" class="ml-2 text-sm text-gray-700">Format with AI when extracting (clearer sections and paragraphs)</label>
                </div>
                <div id="file-list" class="mt-4 space-y-2"></div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center">
                    <input type="checkbox" id="form-interview" name="had_interview"
                           class="h-5 w-5 rounded border-2 border-gray-400 text-blue-600 focus:ring-4 focus:ring-blue-200 focus:ring-offset-0">
                    <label for="form-interview" class="ml-3 text-base text-gray-700 font-semibold">
                        Had Interview
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="#jobs" class="px-6 py-3 border-2 border-gray-400 rounded-lg text-base font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-lg transition-colors focus:outline-none focus:ring-4 focus:ring-gray-200 inline-block">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">
                    Add Application
                </button>
            </div>
        </form>
    </div>
</div>
