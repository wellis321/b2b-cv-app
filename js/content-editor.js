/**
 * Content Editor JavaScript Module
 * Handles dynamic form loading, AJAX submissions, and section navigation
 */

(function() {
    'use strict';

    const data = window.contentEditorData || {};
    let currentSectionId = data.currentSectionId || 'professional-summary';
    let currentGuidance = null;
    let isLoadingSection = false;
    let formHandlersInitialized = false;

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeEditor();
    });

    function initializeEditor() {
        // Check for hash on initial load
        const hash = window.location.hash.substring(1);
        if (hash) {
            const hashParts = hash.split('&');
            const sectionFromHash = hashParts[0];
            // Allow CV sections, jobs, ai-tools, and cv-variants
            const validSections = ['jobs', 'ai-tools', 'cv-variants'];
            const isValidCvSection = data.sections && data.sections.some(s => s.id === sectionFromHash);
            if (sectionFromHash && (isValidCvSection || validSections.includes(sectionFromHash))) {
                currentSectionId = sectionFromHash;
            }
        }
        
        // Update sidebar to reflect current section
        updateSidebarActiveState(currentSectionId);
        const initialHash = window.location.hash.substring(1);
        const initialVariantId = getHashParam(initialHash, 'variant_id');
        if (initialVariantId) {
            updateSidebarLinkHrefs(initialVariantId);
        }
        
        // Handle initial section load BEFORE manipulating hash
        // This ensures view/edit/add parameters are preserved
        loadSection(currentSectionId);
        loadGuidance(currentSectionId);
        
        // Prevent default hash scrolling on page load (after section is loaded)
        // This prevents browser from scrolling to hash element
        if (window.location.hash) {
            const hash = window.location.hash;
            // Only manipulate hash if it's not a content-editor hash (doesn't start with #jobs, #ai-tools, #cv-variants, or section IDs)
            const hashValue = hash.substring(1);
            const isContentEditorHash = data.sections?.some(s => s.id === hashValue.split('&')[0]) || 
                                       ['jobs', 'ai-tools', 'cv-variants'].includes(hashValue.split('&')[0]);
            if (!isContentEditorHash) {
                window.history.replaceState(null, null, ' ');
                setTimeout(() => {
                    window.history.replaceState(null, null, hash);
                }, 0);
            }
        }

        // Handle hash changes (browser back/forward, or in-place view/edit change e.g. #jobs&view=id)
        window.addEventListener('hashchange', handleHashChange);
        
        // Handle sidebar clicks
        document.querySelectorAll('.section-nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.dataset.sectionId;
                if (sectionId) {
                    navigateToSection(sectionId);
                }
            });
        });

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.fixed.top-20').forEach(el => {
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            });
        }, 5000);
    }

    function handleHashChange(e) {
        const hash = window.location.hash.substring(1);
        const oldHash = (e && e.oldURL && e.oldURL.indexOf('#') >= 0) ? (e.oldURL.split('#')[1] || '') : '';
        
        if (!hash) return;
        
        const hashParts = hash.split('&');
        const sectionId = hashParts[0];
        
        if (sectionId && sectionId !== currentSectionId) {
            currentSectionId = sectionId;
            updateSidebarActiveState(sectionId);
            const variantId = getHashParam(hash, 'variant_id');
            updateSidebarLinkHrefs(variantId);
            loadSection(sectionId);
            loadGuidance(sectionId);
        } else if (sectionId === currentSectionId) {
            // Same section - check if edit, view, add, or variant_id parameter changed
            const getParam = (h, name) => (h && h.includes('&' + name + '=')) ? ((h.split('&').find(p => p.startsWith(name + '=')) || '').replace(name + '=', '') || null) : null;
            const currEdit = getParam(hash, 'edit');
            const currView = getParam(hash, 'view');
            const currAdd = getParam(hash, 'add');
            const currVariant = getParam(hash, 'variant_id');
            const prevEdit = getParam(oldHash, 'edit');
            const prevView = getParam(oldHash, 'view');
            const prevAdd = getParam(oldHash, 'add');
            const prevVariant = getParam(oldHash, 'variant_id');
            const currCreate = getParam(hash, 'create');
            const prevCreate = getParam(oldHash, 'create');
            if (currEdit !== prevEdit || (sectionId === 'jobs' && (currView !== prevView || currAdd !== prevAdd)) || ((sectionId === 'ai-tools' || sectionId === 'cv-variants') && currVariant !== prevVariant) || (sectionId === 'cv-variants' && currCreate !== prevCreate)) {
                loadSection(sectionId);
            }
        }
    }

    function getHashParam(hash, name) {
        if (!hash || !hash.includes('&' + name + '=')) return null;
        const part = hash.split('&').find(p => p.startsWith(name + '='));
        return part ? part.replace(name + '=', '') : null;
    }

    function navigateToSection(sectionId) {
        if (!sectionId) return;
        
        const mainElement = document.getElementById('main-content') || document.querySelector('main');
        const currentHash = window.location.hash.substring(1);
        const variantId = getHashParam(currentHash, 'variant_id');
        // Preserve variant_id when switching sections so "Edit variant" keeps context
        let newHash = sectionId;
        if (variantId) {
            newHash = sectionId + '&variant_id=' + encodeURIComponent(variantId);
        }
        
        currentSectionId = sectionId;
        
        // Set hash to newHash BEFORE loadSection so the fetch URL includes variant_id (and edit/view/add)
        window.history.replaceState(null, '', '#' + newHash);
        
        // Update sidebar active state and link hrefs (so sidebar shows correct targets)
        updateSidebarActiveState(sectionId);
        updateSidebarLinkHrefs(variantId);
        
        // Load section content; loadSection reads window.location.hash so variant_id is passed
        loadSection(sectionId);
        
        setTimeout(() => {
            if (mainElement) mainElement.scrollTop = 0;
            window.scrollTo(0, 0);
            if (document.documentElement) document.documentElement.scrollTop = 0;
            if (document.body) document.body.scrollTop = 0;
        }, 10);
        
        // Load guidance
        loadGuidance(sectionId);
    }

    function updateSidebarActiveState(sectionId) {
        document.querySelectorAll('.section-nav-item').forEach(item => {
            const itemSectionId = item.dataset.sectionId;
            const svg = item.querySelector('svg');
            const path = svg ? svg.querySelector('path') : null;
            
            if (itemSectionId === sectionId) {
                // Active section: colored background and text based on section type
                if (sectionId === 'jobs') {
                    item.classList.add('bg-green-50', 'text-green-700');
                    item.classList.remove('text-gray-700', 'hover:bg-gray-50', 'bg-blue-50', 'text-blue-700', 'bg-purple-50', 'text-purple-700');
                    if (svg) {
                        svg.classList.remove('text-gray-400', 'text-blue-600', 'text-purple-600');
                        svg.classList.add('text-green-600');
                    }
                } else if (sectionId === 'ai-tools') {
                    item.classList.add('bg-purple-50', 'text-purple-700');
                    item.classList.remove('text-gray-700', 'hover:bg-gray-50', 'bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700');
                    if (svg) {
                        svg.classList.remove('text-gray-400', 'text-blue-600', 'text-green-600');
                        svg.classList.add('text-purple-600');
                    }
                } else {
                    // CV sections: blue
                    item.classList.add('bg-blue-50', 'text-blue-700');
                    item.classList.remove('text-gray-700', 'hover:bg-gray-50', 'bg-green-50', 'text-green-700', 'bg-purple-50', 'text-purple-700');
                    if (svg) {
                        svg.classList.remove('text-gray-400', 'text-green-600', 'text-purple-600');
                        svg.classList.add('text-blue-600');
                    }
                }
                if (path) {
                    // Right-pointing arrow for active section
                    path.setAttribute('d', 'M9 5l7 7-7 7');
                }
            } else {
                // Inactive section: gray text, down-pointing arrow
                item.classList.remove('bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700', 'bg-purple-50', 'text-purple-700');
                item.classList.add('text-gray-700', 'hover:bg-gray-50');
                if (svg) {
                    svg.classList.remove('text-blue-600', 'text-green-600', 'text-purple-600');
                    svg.classList.add('text-gray-400');
                }
                if (path) {
                    // Down-pointing arrow for inactive section
                    path.setAttribute('d', 'M19 9l-7 7-7-7');
                }
            }
        });
    }

    function updateSidebarLinkHrefs(variantId) {
        document.querySelectorAll('.section-nav-item').forEach(item => {
            const sectionId = item.dataset.sectionId;
            if (sectionId) {
                item.setAttribute('href', variantId ? '#' + sectionId + '&variant_id=' + encodeURIComponent(variantId) : '#' + sectionId);
            }
        });
    }

    function loadSection(sectionId) {
        
        // Prevent concurrent loads
        if (isLoadingSection) {
            return;
        }
        
        const contentArea = document.getElementById('section-content');
        if (!contentArea) return;

        isLoadingSection = true;

        // Show loading state
        contentArea.innerHTML = `
            <div class="max-w-3xl mx-auto">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-500">Loading section...</p>
                </div>
            </div>
        `;

        // Check for edit/view/add/variant_id parameters in hash
        const hash = window.location.hash.substring(1);
        const editParam = hash.includes('&edit=') ? '&' + hash.split('&').find(p => p.startsWith('edit=')) : '';
        const viewParam = (sectionId === 'jobs' && hash.includes('&view=')) ? '&' + hash.split('&').find(p => p.startsWith('view=')) : '';
        const addParam = (sectionId === 'jobs' && hash.includes('&add=')) ? '&' + hash.split('&').find(p => p.startsWith('add=')) : '';
        const createParam = (sectionId === 'cv-variants' && hash.includes('&create=')) ? '&' + hash.split('&').find(p => p.startsWith('create=')) : '';
        const jobParam = (sectionId === 'cv-variants' && hash.includes('&job=')) ? '&' + hash.split('&').find(p => p.startsWith('job=')) : '';
        const variantParam = hash.includes('&variant_id=') ? '&' + hash.split('&').find(p => p.startsWith('variant_id=')) : '';
        
        
        // Fetch section form via AJAX
        fetch(`/api/content-editor/get-section-form.php?section_id=${encodeURIComponent(sectionId)}${editParam}${viewParam}${addParam}${createParam}${jobParam}${variantParam}`, {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            const mainElement = document.getElementById('main-content') || contentArea.closest('main');
            const savedScrollTop = mainElement && editParam ? mainElement.scrollTop : null;
            
            contentArea.innerHTML = html;
            const scrollTopBefore = mainElement ? mainElement.scrollTop : null;

            // When NOT editing, scroll middle pane and window to top so user sees section from the start
            if (!editParam && !addParam) {
                if (mainElement) mainElement.scrollTop = 0;
                window.scrollTo(0, 0);
                if (document.documentElement) document.documentElement.scrollTop = 0;
                if (document.body) document.body.scrollTop = 0;
            }
            // When loading jobs edit or add form, scroll main content to top so user isn't left at the foot
            if (sectionId === 'jobs' && (editParam || addParam) && mainElement) {
                mainElement.scrollTop = 0;
            }
            
            // Initialize markdown editors for dynamically loaded forms
            setTimeout(() => {
                if (typeof window.MarkdownEditor !== 'undefined' && window.MarkdownEditor.initAll) {
                    window.MarkdownEditor.initAll();
                } else {
                    // Retry if not loaded yet
                    setTimeout(() => {
                        if (typeof window.MarkdownEditor !== 'undefined' && window.MarkdownEditor.initAll) {
                            window.MarkdownEditor.initAll();
                        }
                    }, 200);
                }
            }, 100);
            
            // Initialize form handlers (only for CV sections, not jobs/ai-tools/cv-variants)
            if (sectionId !== 'jobs' && sectionId !== 'ai-tools' && sectionId !== 'cv-variants') {
                initializeFormHandlers(sectionId);
                
                // Initialize responsibilities editor if present (for work experience)
                setTimeout(() => {
                    initializeResponsibilitiesEditor();
                }, 100);
                
                // Initialize project image upload handlers if present
                if (sectionId === 'projects') {
                    setTimeout(() => {
                        initializeProjectImageHandlers();
                    }, 100);
                }
                // Initialize work experience reorder (drag-and-drop) when list is shown
                if (sectionId === 'work-experience') {
                    setTimeout(() => {
                        initializeWorkExperienceReorder(contentArea);
                    }, 150);
                }
            } else if (sectionId === 'cv-variants') {
                // Extract and execute inline scripts for CV variants panel (list or create form)
                // Inline scripts don't execute when inserted via innerHTML, so we need to extract and run them
                setTimeout(() => {
                    const scripts = contentArea.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        if (oldScript.src) return;
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                }, 150);
            } else if (sectionId === 'jobs') {
                // Load jobs panel script; then run init for list (initJobsPanelContentEditor), edit form (initJobsEditForm), add form (initJobsAddForm), or view (initJobsView)
                const runJobsInit = function() {
                    // Extract and execute inline scripts first (for keyword extraction, etc.)
                    const scripts = contentArea.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        // Skip external scripts (those with src attribute)
                        if (oldScript.src) return;
                        const newScript = document.createElement('script');
                        // Copy attributes
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        // Copy script content
                        newScript.textContent = oldScript.textContent;
                        // Replace old script with new one (this will execute it)
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                    
                    // Then run the appropriate init function
                    const editForm = contentArea.querySelector('[data-jobs-edit-form]');
                    const addForm = contentArea.querySelector('[data-jobs-add-form]');
                    const viewContainer = contentArea.querySelector('[data-jobs-view-container]');
                    if (editForm && typeof window.initJobsEditForm === 'function') {
                        window.initJobsEditForm(editForm);
                    } else if (addForm && typeof window.initJobsAddForm === 'function') {
                        window.initJobsAddForm(addForm);
                    } else if (viewContainer && typeof window.initJobsView === 'function') {
                        window.initJobsView(viewContainer);
                    } else if (typeof window.initJobsPanelContentEditor === 'function') {
                        window.initJobsPanelContentEditor();
                    }
                };
                setTimeout(function() {
                    const existing = document.querySelector('script[data-jobs-panel-loaded]');
                    if (existing) {
                        runJobsInit();
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = '/js/jobs-panel-content-editor.js?v=' + (Date.now ? Date.now() : '1');
                    script.setAttribute('data-jobs-panel-loaded', '1');
                    script.onload = runJobsInit;
                    script.onerror = function() {
                        const container = document.getElementById('jobs-applications-container');
                        if (container) {
                            container.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load jobs panel. Please refresh the page.</div>';
                        }
                    };
                    document.head.appendChild(script);
                }, 100);
            } else if (sectionId === 'ai-tools') {
                // Initialize AI tools panel - extract and execute inline scripts
                // Inline scripts don't execute when inserted via innerHTML, so we need to extract and run them
                setTimeout(() => {
                    const aiToolsPanel = contentArea.querySelector('[data-ai-tools-panel]');
                    if (aiToolsPanel) {
                        // Find and execute any script tags in the loaded HTML
                        const scripts = contentArea.querySelectorAll('script');
                        scripts.forEach(oldScript => {
                            const newScript = document.createElement('script');
                            // Copy attributes
                            Array.from(oldScript.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            // Copy script content
                            newScript.textContent = oldScript.textContent;
                            // Replace old script with new one (this will execute it)
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                        
                        // Also dispatch event to trigger initialization if needed
                        aiToolsPanel.dispatchEvent(new Event('ai-tools-loaded'));
                    }
                }, 150);
            }
            
            // Scroll behavior: only scroll when editing, preserve scroll position when switching sections
            // mainElement is already declared above
            
            
            if (editParam) {
                // In edit mode - scroll to show the section heading (h1 "Work Experience" or similar)
                setTimeout(() => {
                    // Find the main section heading (h1) - this is the "Work Experience" title
                    let scrollTarget = contentArea.querySelector('h1');
                    
                    // Fallback: find the form header (h2 "Edit Work Experience")
                    if (!scrollTarget) {
                        const formContainer = contentArea.querySelector('form[data-section-form]')?.closest('.bg-white.shadow.rounded-lg');
                        if (formContainer) {
                            scrollTarget = formContainer.querySelector('h2');
                        }
                    }
                    
                    // Final fallback: the form itself
                    if (!scrollTarget) {
                        scrollTarget = contentArea.querySelector('form[data-section-form]');
                    }
                    
                    
                    if (scrollTarget && mainElement) {
                        // First, scroll the element into view
                        scrollTarget.scrollIntoView({ 
                            behavior: 'smooth',
                            block: 'start',
                            inline: 'nearest'
                        });
                        
                        // Then adjust the scroll position upward by 70px after a short delay
                        // This ensures the h1 heading is positioned higher with some padding
                        setTimeout(() => {
                            const currentScroll = mainElement.scrollTop;
                            const adjustedScroll = Math.max(0, currentScroll - 70);
                            mainElement.scrollTo({ 
                                top: adjustedScroll, 
                                behavior: 'smooth' 
                            });
                            
                            setTimeout(() => {
                            }, 100);
                        }, 300); // Wait for scrollIntoView to complete
                    }
                }, 200); // Wait for responsibilities editor and DOM to be ready
            } else {
                // When NOT editing (sidebar or hash section switch) - show middle pane and window from top for better UX
                var scrollToTop = function() {
                    if (mainElement) mainElement.scrollTop = 0;
                    window.scrollTo(0, 0);
                    if (document.documentElement) document.documentElement.scrollTop = 0;
                    if (document.body) document.body.scrollTop = 0;
                };
                scrollToTop();
                requestAnimationFrame(scrollToTop);
                setTimeout(scrollToTop, 100);
                setTimeout(scrollToTop, 250);
            }
            
            // Don't call loadSectionData here - the form partial already contains the current data
            // Only reload data after saves/deletes
            isLoadingSection = false;
        })
        .catch(error => {
            console.error('Error loading section:', error);
            contentArea.innerHTML = `
                <div class="max-w-3xl mx-auto">
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <p class="text-sm font-medium text-red-800">Error loading section. Please refresh the page.</p>
                    </div>
                </div>
            `;
            isLoadingSection = false;
        });
    }

    function loadGuidance(sectionId) {
        fetch(`/api/content-editor/get-guidance.php?section_id=${encodeURIComponent(sectionId)}`, {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.guidance) {
                updateGuidancePanel(data.guidance);
            }
        })
        .catch(error => {
            console.error('Error loading guidance:', error);
        });
    }

    function updateGuidancePanel(guidance) {
        const guidancePanel = document.querySelector('.content-editor-sidebar:last-child');
        if (!guidancePanel || !guidance) return;

        // Build the guidance HTML dynamically
        let html = `
            <div class="bg-white border-l border-gray-200 h-full overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Suggestions</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-2">${escapeHtml(guidance.title || '')}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">${escapeHtml(guidance.description || '')}</p>
                    </div>
        `;

        // Add tips
        if (guidance.tips && guidance.tips.length > 0) {
            html += `
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Tips</h4>
                    <ul class="space-y-2">
            `;
            guidance.tips.forEach(tip => {
                html += `
                    <li class="text-sm text-gray-600 flex items-start">
                        <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>${escapeHtml(tip).replace(/\n/g, '<br>')}</span>
                    </li>
                `;
            });
            html += `
                    </ul>
                </div>
            `;
        }

        // Add examples
        if (guidance.examples && guidance.examples.length > 0) {
            html += `
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Examples</h4>
                    <div class="space-y-3">
            `;
            guidance.examples.forEach(example => {
                html += `
                    <div class="bg-gray-50 p-3 rounded-md text-sm text-gray-700">
                        ${escapeHtml(example).replace(/\n/g, '<br>')}
                    </div>
                `;
            });
            html += `
                    </div>
                </div>
            `;
        }

        // Add common mistakes
        if (guidance.common_mistakes && guidance.common_mistakes.length > 0) {
            html += `
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Common Mistakes to Avoid</h4>
                    <ul class="space-y-2">
            `;
            guidance.common_mistakes.forEach(mistake => {
                html += `
                    <li class="text-sm text-gray-600 flex items-start">
                        <svg class="w-4 h-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span>${escapeHtml(mistake)}</span>
                    </li>
                `;
            });
            html += `
                    </ul>
                </div>
            `;
        }

        // CV Preview (always show so user can refresh preview)
        html += `
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">CV Preview</h4>
                <p class="text-sm text-gray-600 mb-3">See your changes after saving sections.</p>
                <button type="button" id="content-editor-update-preview" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                    Update Preview
                </button>
                <div id="content-editor-cv-preview" class="mt-4 overflow-auto border border-gray-200 rounded-md bg-white min-h-[200px]" style="max-height: 400px;"></div>
            </div>
        `;

        html += `
                </div>
            </div>
        `;

        // Update the guidance panel content
        guidancePanel.innerHTML = html;

        // Re-attach Update Preview button listener (element was replaced when guidance updated)
        const previewBtn = document.getElementById('content-editor-update-preview');
        if (previewBtn && typeof window.contentEditorRefreshPreview === 'function') {
            previewBtn.addEventListener('click', window.contentEditorRefreshPreview);
        }
    }

    function initializeFormHandlers(sectionId) {
        // Handle form submissions - use event delegation to avoid duplicate listeners
        const contentArea = document.getElementById('section-content');
        if (!contentArea) return;
        
        // Only attach listeners once to prevent multiple handlers firing
        if (formHandlersInitialized) {
            return;
        }
        
        formHandlersInitialized = true;
        
        
        // Handle form submissions - derive sectionId from form context
        contentArea.addEventListener('submit', function(e) {
            const form = e.target.closest('form[data-section-form]');
            if (form) {
                e.preventDefault();
                // Derive sectionId from current hash or form context
                const hash = window.location.hash.substring(1);
                const sectionFromHash = hash ? hash.split('&')[0] : currentSectionId;
                handleFormSubmit(form, sectionFromHash);
            }
        });
        
        // Handle button clicks with delegation - derive sectionId from button context
        contentArea.addEventListener('click', function(e) {
            const target = e.target.closest('[data-action]');
            if (!target) return;
            
            const action = target.dataset.action;
            const entryId = target.dataset.entryId;
            
            // Derive sectionId from button's parent list ID (e.g., "work-experience-entries-list" -> "work-experience")
            const entriesList = target.closest('[id$="-entries-list"]');
            let actualSectionId = currentSectionId; // fallback to current section
            
            if (entriesList && entriesList.id) {
                // Extract section ID from entries list ID (e.g., "work-experience-entries-list" -> "work-experience")
                const listId = entriesList.id;
                const match = listId.match(/^(.+)-entries-list$/);
                if (match) {
                    actualSectionId = match[1];
                }
            } else {
                // Fallback: derive from current hash
                const hash = window.location.hash.substring(1);
                if (hash) {
                    actualSectionId = hash.split('&')[0];
                }
            }
            
            const entryType = target.dataset.entryType || actualSectionId;
            
            
            if (action === 'delete') {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this item?')) {
                    deleteEntry(entryId, entryType, actualSectionId);
                }
            } else if (action === 'edit') {
                e.preventDefault();
                editEntry(entryId, actualSectionId);
            } else if (action === 'add') {
                e.preventDefault();
                showAddForm(actualSectionId);
            } else if (action === 'cancel') {
                e.preventDefault();
                cancelEdit(actualSectionId);
            }
        });

    }

    function handleFormSubmit(form, sectionId) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton ? submitButton.textContent : 'Save';
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';
        }

        fetch('/api/content-editor/save-section.php', {
            method: 'POST',
            body: formData,
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message || 'Saved successfully');
                if (typeof window.contentEditorRefreshPreview === 'function') {
                    window.contentEditorRefreshPreview();
                }
                // Only reload if it's a create action (to show new entry in list)
                // For updates, reload to refresh the form and responsibilities
                if (form.dataset.formType === 'create' || form.dataset.formType === 'add' || form.dataset.formType === 'add_strength') {
                    setTimeout(() => {
                        loadSection(sectionId);
                    }, 500);
                } else {
                    // For updates, clear edit parameter and reload
                    const hash = window.location.hash.substring(1);
                    const sectionFromHash = hash.split('&')[0];
                    window.history.replaceState(null, '', '#' + sectionFromHash);
                    setTimeout(() => {
                        loadSection(sectionId);
                    }, 500);
                }
            } else {
                showNotification('error', data.error || 'Failed to save');
            }
        })
        .catch(error => {
            console.error('Error saving:', error);
            showNotification('error', 'An error occurred while saving');
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    }

    function loadSectionData(sectionId) {
        // This function is kept for potential future use but not called automatically
        // to prevent infinite reload loops. The form partial already contains current data.
        return Promise.resolve();
    }

    function updateEntryList(sectionId, entries) {
        // Don't reload here - this causes infinite loops
        // The form partial already contains the entry list
        // Only reload after explicit user actions (save/delete)
    }

    function deleteEntry(entryId, entryType, sectionId) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('entry_id', entryId);
        formData.append('section_id', entryType);
        formData.append(data.csrfTokenName, data.csrfToken);

        fetch('/api/content-editor/save-section.php', {
            method: 'POST',
            body: formData,
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Deleted successfully');
                if (typeof window.contentEditorRefreshPreview === 'function') {
                    window.contentEditorRefreshPreview();
                }
                // Reload section to refresh entry list
                setTimeout(() => {
                    loadSection(sectionId);
                }, 500);
            } else {
                showNotification('error', data.error || 'Failed to delete');
            }
        })
        .catch(error => {
            console.error('Error deleting:', error);
            showNotification('error', 'An error occurred while deleting');
        });
    }

    function editEntry(entryId, sectionId) {
        
        const currentHash = window.location.hash.substring(1);
        const variantId = getHashParam(currentHash, 'variant_id');
        let newHash = sectionId + '&edit=' + entryId;
        if (variantId) {
            newHash += '&variant_id=' + encodeURIComponent(variantId);
        }
        currentSectionId = sectionId;
        
        
        // Use replaceState to prevent scrolling
        window.history.replaceState(null, '', '#' + newHash);
        loadSection(sectionId);
    }

    function showAddForm(sectionId) {
        // Show add form (usually already visible, but ensure it's shown)
        const addForm = document.getElementById(`${sectionId}-add-form`);
        if (addForm) {
            addForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            addForm.querySelector('input, textarea, select')?.focus();
        }
    }

    function cancelEdit(sectionId) {
        // Remove edit parameter from hash and reload
        window.history.replaceState(null, '', `#${sectionId}`);
        loadSection(sectionId);
    }

    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `fixed top-20 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full`;
        notification.innerHTML = `
            <div class="bg-${type === 'success' ? 'green' : 'red'}-50 border border-${type === 'success' ? 'green' : 'red'}-200 rounded-md p-4 shadow-lg">
                <p class="text-sm font-medium text-${type === 'success' ? 'green' : 'red'}-800">${escapeHtml(message)}</p>
            </div>
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transition = 'opacity 0.3s';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Normalize suggested_replacement format based on section type.
     * Handles cases where AI returns nested objects instead of expected format.
     */
    function normalizeSuggestedReplacement(replacement, sectionId) {
        if (!replacement) return null;
        
        // If it's already a string, return as-is
        if (typeof replacement === 'string') {
            return replacement;
        }
        
        // Handle nested object formats (AI sometimes returns these)
        if (typeof replacement === 'object' && replacement !== null) {
            // For professional-summary, extract text from nested structure
            if (sectionId === 'professional-summary') {
                // If it's an object with professional_summary key, extract and join text
                if (replacement.professional_summary) {
                    const summary = replacement.professional_summary;
                    if (typeof summary === 'string') {
                        return summary;
                    }
                    // If it's an object with text fields, join them
                    if (typeof summary === 'object') {
                        const parts = [];
                        // Common field names AI might use
                        ['text', 'summary', 'content', 'past_15_years', 'specialist_skills', 'recent_focus', 'achievements', 'target_roles'].forEach(field => {
                            if (summary[field] && typeof summary[field] === 'string') {
                                parts.push(summary[field]);
                            }
                        });
                        if (parts.length > 0) {
                            return parts.join(' ');
                        }
                    }
                }
                // If it's an array, join the strings
                if (Array.isArray(replacement)) {
                    return replacement.map(item => typeof item === 'string' ? item : JSON.stringify(item)).join(' ');
                }
                // Last resort: stringify the object
                return JSON.stringify(replacement, null, 2);
            }
            
            // For work-experience, single-entry suggested replacement (title, company, description, responsibility_categories)
            if (sectionId === 'work-experience') {
                if (replacement && typeof replacement === 'object' && !Array.isArray(replacement)) {
                    const title = replacement.title || replacement.position || '';
                    const company = replacement.company || replacement.company_name || '';
                    const desc = replacement.description || '';
                    const cats = replacement.responsibility_categories || [];
                    let out = title && company ? `${title} at ${company}` : (title || company || '');
                    if (desc) {
                        out += (out ? '\n\n' : '') + desc;
                    }
                    if (Array.isArray(cats) && cats.length > 0) {
                        out += (out ? '\n\n' : '') + 'Key responsibilities:';
                        cats.forEach(function (cat) {
                            const name = cat.name || cat.category || 'Responsibilities';
                            const items = cat.items || [];
                            out += '\n\n' + name + ':';
                            items.forEach(function (item) {
                                const c = (item && typeof item === 'object' && item.content) ? item.content : (typeof item === 'string' ? item : '');
                                if (c) out += '\n  • ' + c;
                            });
                        });
                    }
                    return out.trim() || JSON.stringify(replacement, null, 2);
                }
                return JSON.stringify(replacement, null, 2);
            }

            // For qualification-equivalence, single-entry suggested replacement (object with level, description)
            if (sectionId === 'qualification-equivalence') {
                if (replacement && typeof replacement === 'object' && !Array.isArray(replacement)) {
                    const level = replacement.level || '';
                    const desc = replacement.description || '';
                    let out = level ? `Level: ${level}` : '';
                    if (desc) {
                        out += (out ? '\n\n' : '') + desc;
                    }
                    return out.trim() || JSON.stringify(replacement, null, 2);
                }
                return JSON.stringify(replacement, null, 2);
            }

            // For interests, single-entry suggested replacement (object with name, description)
            if (sectionId === 'interests') {
                if (replacement && typeof replacement === 'object' && !Array.isArray(replacement)) {
                    const name = replacement.name || '';
                    const desc = replacement.description || '';
                    let out = name ? `Name: ${name}` : '';
                    if (desc) {
                        out += (out ? '\n\n' : '') + desc;
                    }
                    return out.trim() || JSON.stringify(replacement, null, 2);
                }
                return JSON.stringify(replacement, null, 2);
            }

            // For projects, single-entry suggested replacement (object with title, description, url, dates)
            if (sectionId === 'projects') {
                if (replacement && typeof replacement === 'object' && !Array.isArray(replacement)) {
                    const title = replacement.title || '';
                    const desc = replacement.description || '';
                    const url = replacement.url || '';
                    const start = replacement.start_date || '';
                    const end = replacement.end_date || '';
                    let out = title ? `Title: ${title}` : '';
                    if (start || end) {
                        out += (out ? '\n\n' : '') + 'Dates: ' + (start || '?') + ' - ' + (end || 'Present');
                    }
                    if (desc) {
                        out += (out ? '\n\n' : '') + desc;
                    }
                    if (url) {
                        out += (out ? '\n\n' : '') + 'URL: ' + url;
                    }
                    return out.trim() || JSON.stringify(replacement, null, 2);
                }
                return JSON.stringify(replacement, null, 2);
            }

            // For skills, format grouped by category
            if (sectionId === 'skills') {
                if (Array.isArray(replacement)) {
                    // Group skills by category
                    const grouped = {};
                    replacement.forEach(skill => {
                        // Handle both 'skill'/'proficiency' and 'name'/'level' field names
                        const name = skill.name || skill.skill || 'Unknown';
                        const level = skill.level || skill.proficiency || '';
                        const category = skill.category || 'Other';
                        
                        if (!grouped[category]) {
                            grouped[category] = [];
                        }
                        grouped[category].push({ name, level });
                    });
                    
                    // Format as readable text grouped by category
                    let formatted = '';
                    Object.keys(grouped).sort().forEach(category => {
                        formatted += `\n${category}:\n`;
                        grouped[category].forEach(skill => {
                            formatted += `  • ${skill.name}`;
                            if (skill.level) {
                                formatted += ` (${skill.level})`;
                            }
                            formatted += '\n';
                        });
                    });
                    return formatted.trim();
                }
                // If not an array, stringify
                return JSON.stringify(replacement, null, 2);
            }
            
            // For other sections, return as JSON string for now
            return JSON.stringify(replacement, null, 2);
        }
        
        return String(replacement);
    }

    /**
     * Parse JSON from AI output: strip markdown fences, extract one {...} object,
     * repair common JSON issues, then parse. Throws on failure after logging details.
     */
    function parseAssessmentJsonFromAI(raw) {
        let text = String(raw || '').trim();
        
        // Log raw input for debugging
        console.log('parseAssessmentJsonFromAI: Raw input length:', text.length);
        
        // Strip model special tokens (e.g. <|start_header_id|>assistant<|end_header_id|>) that break JSON
        text = text.replace(/<\|[^]*?\|>/g, '');
        
        // Strip markdown code fences (```json ... ``` or ``` ... ```)
        const codeBlock = text.match(/```(?:json)?\s*([\s\S]*?)```/);
        if (codeBlock) {
            text = codeBlock[1].trim();
            console.log('parseAssessmentJsonFromAI: Stripped markdown fences, length:', text.length);
        }
        
        // Extract first balanced {...} block (avoids grabbing extra text after })
        let jsonStr = text;
        const start = text.indexOf('{');
        if (start >= 0) {
            let depth = 0, end = -1;
            for (let i = start; i < text.length; i++) {
                const ch = text[i];
                if (ch === '{') depth++;
                else if (ch === '}') { depth--; if (depth === 0) { end = i; break; } }
            }
            if (end >= 0) {
                jsonStr = text.slice(start, end + 1);
                console.log('parseAssessmentJsonFromAI: Extracted JSON object, length:', jsonStr.length);
            }
        }
        
        // Strip any remaining model tokens inside the JSON (can appear in string values)
        jsonStr = jsonStr.replace(/<\|[^]*?\|>/g, '');
        
        // Repair common LLM JSON issues
        // 1. Trailing commas before ] or }
        jsonStr = jsonStr.replace(/,(\s*[}\]])/g, '$1');
        // 2. Fix missing colons: "key" "value" -> "key": "value"
        jsonStr = jsonStr.replace(/"\s*"\s*"/g, '": "');
        // 3. Fix missing colons: "key" [ -> "key": [
        jsonStr = jsonStr.replace(/"\s*(\[)/g, '": $1');
        // 4. Fix missing colons: "key" { -> "key": {
        jsonStr = jsonStr.replace(/"\s*(\{)/g, '": $1');
        // 5. Replace unescaped newlines in string values (control chars) with space to avoid parse errors
        jsonStr = jsonStr.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"/g, function (m) {
            return m.replace(/\r?\n/g, ' ');
        });
        
        try {
            const parsed = JSON.parse(jsonStr);
            console.log('parseAssessmentJsonFromAI: Successfully parsed JSON');
            return parsed;
        } catch (e) {
            // Extract error position if available
            const errorMatch = e.message.match(/position (\d+)/);
            const errorPos = errorMatch ? parseInt(errorMatch[1]) : -1;
            
            // Log detailed error info
            console.error('parseAssessmentJsonFromAI: Parse error at position', errorPos);
            console.error('parseAssessmentJsonFromAI: Error message:', e.message);
            
            if (errorPos >= 0 && errorPos < jsonStr.length) {
                // Show context around error (100 chars before and after)
                const contextStart = Math.max(0, errorPos - 100);
                const contextEnd = Math.min(jsonStr.length, errorPos + 100);
                const context = jsonStr.slice(contextStart, contextEnd);
                const markerPos = errorPos - contextStart;
                const markedContext = context.slice(0, markerPos) + '>>>ERROR HERE<<<' + context.slice(markerPos);
                console.error('parseAssessmentJsonFromAI: Context around error:\n', markedContext);
            } else {
                // Show first 500 and last 500 chars if we can't find error position
                console.error('parseAssessmentJsonFromAI: First 500 chars:', jsonStr.slice(0, 500));
                console.error('parseAssessmentJsonFromAI: Last 500 chars:', jsonStr.slice(-500));
            }
            
            // Try one more repair: remove any lines that look like they're outside JSON structure
            // This handles cases where the model adds explanatory text after the JSON
            let repaired = jsonStr;
            try {
                // Try to find and extract just the JSON part more aggressively
                const jsonMatch = repaired.match(/\{[\s\S]*\}/);
                if (jsonMatch && jsonMatch[0] !== repaired) {
                    repaired = jsonMatch[0];
                    console.log('parseAssessmentJsonFromAI: Trying repaired version (extracted from match)');
                    return JSON.parse(repaired);
                }
            } catch (e2) {
                console.error('parseAssessmentJsonFromAI: Repair attempt also failed');
            }
            
            throw new Error('AI returned invalid JSON. Please try again.');
        }
    }
    
    // Assess section function - available globally
    window.assessSection = async function(sectionId) {
        try {
            // Show loading in guidance panel
            const guidancePanel = document.querySelector('.content-editor-sidebar:last-child');
            if (guidancePanel) {
                guidancePanel.innerHTML = `
                    <div class="p-6">
                        <div class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
                            <p class="mt-4 text-gray-600">Assessing section...</p>
                        </div>
                    </div>
                `;
            }
            
            // Call API to assess section
            const formData = new FormData();
            formData.append('section_id', sectionId);
            formData.append(data.csrfTokenName, data.csrfToken);
            // When assessing work-experience while editing a single entry, pass its id so AI focuses on that entry only
            if (sectionId === 'work-experience' && window.location.hash && window.location.hash.includes('&edit=')) {
                const hash = window.location.hash.replace(/^#/, '');
                const editPart = hash.split('&').find(p => p.startsWith('edit='));
                if (editPart) {
                    const workExperienceId = editPart.slice(5);
                    if (workExperienceId) {
                        formData.append('work_experience_id', workExperienceId);
                    }
                }
            }
            // When assessing qualification-equivalence while editing a single entry, pass its id so AI focuses on that entry only
            if (sectionId === 'qualification-equivalence' && window.location.hash && window.location.hash.includes('&edit=')) {
                const hash = window.location.hash.replace(/^#/, '');
                const editPart = hash.split('&').find(p => p.startsWith('edit='));
                if (editPart) {
                    const qualificationEquivalenceId = editPart.slice(5);
                    if (qualificationEquivalenceId) {
                        formData.append('qualification_equivalence_id', qualificationEquivalenceId);
                    }
                }
            }
            // When on a single interest edit page (#interests&edit=<id>), pass interest_id so AI assesses that one entry only.
            // When on the list/add view (#interests, no edit), do not pass it — assess all interests overall.
            if (sectionId === 'interests' && window.location.hash && window.location.hash.includes('&edit=')) {
                const hash = window.location.hash.replace(/^#/, '');
                const editPart = hash.split('&').find(p => p.startsWith('edit='));
                if (editPart) {
                    const interestId = editPart.slice(5);
                    if (interestId) {
                        formData.append('interest_id', interestId);
                    }
                }
            }
            // When assessing projects while editing a single entry, pass its id so AI focuses on that entry only.
            if (sectionId === 'projects' && window.location.hash && window.location.hash.includes('&edit=')) {
                const hash = window.location.hash.replace(/^#/, '');
                const editPart = hash.split('&').find(p => p.startsWith('edit='));
                if (editPart) {
                    const projectId = editPart.slice(5);
                    if (projectId) {
                        formData.append('project_id', projectId);
                    }
                }
            }
            
            
            const response = await fetch('/api/content-editor/assess-section.php', {
                method: 'POST',
                body: formData,
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
            }
            
            const result = await response.json();
            
            // Handle browser execution required case
            if (result.browser_execution) {
                // Browser AI execution - execute client-side
                await executeBrowserAISectionAssessment(result, sectionId, guidancePanel);
                return;
            }
            
            if (result.success && result.assessment) {
                // Display recommendations in guidance panel
                if (guidancePanel) {
                    const assessment = result.assessment;
                    let html = `
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">AI Recommendations: ${escapeHtml(sectionId.replace('-', ' '))}</h3>
                            <div class="space-y-4">
                    `;
                    
                    if (assessment.strengths && assessment.strengths.length > 0) {
                        html += `
                            <div>
                                <h4 class="text-sm font-semibold text-green-700 mb-2">Strengths</h4>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                        `;
                        assessment.strengths.forEach(strength => {
                            html += `<li>${escapeHtml(strength)}</li>`;
                        });
                        html += `</ul></div>`;
                    }
                    
                    if (assessment.weaknesses && assessment.weaknesses.length > 0) {
                        html += `
                            <div>
                                <h4 class="text-sm font-semibold text-red-700 mb-2">Areas for Improvement</h4>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                        `;
                        assessment.weaknesses.forEach(weakness => {
                            html += `<li>${escapeHtml(weakness)}</li>`;
                        });
                        html += `</ul></div>`;
                    }
                    
                    if (assessment.recommendations && assessment.recommendations.length > 0) {
                        html += `
                            <div>
                                <h4 class="text-sm font-semibold text-blue-700 mb-2">Recommendations</h4>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                        `;
                        assessment.recommendations.forEach(rec => {
                            html += `<li>${escapeHtml(rec)}</li>`;
                        });
                        html += `</ul></div>`;
                    }
                    
                    if (assessment.suggested_replacement) {
                        const normalizedReplacement = normalizeSuggestedReplacement(assessment.suggested_replacement, sectionId);
                        html += `
                            <div class="mt-4 pt-4 border-t">
                                <h4 class="text-sm font-semibold text-purple-700 mb-2">Suggested Replacement</h4>
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                    <pre class="whitespace-pre-wrap text-sm text-gray-800 font-mono">${escapeHtml(normalizedReplacement)}</pre>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">You can copy this improved version and use it to update your section.</p>
                            </div>
                        `;
                    }
                    
                    html += `
                            </div>
                            <div class="mt-6 pt-4 border-t">
                                <a href="/cv-quality.php" class="text-sm text-purple-600 hover:text-purple-700">View Full CV Assessment →</a>
                            </div>
                        </div>
                    `;
                    
                    guidancePanel.innerHTML = html;
                }
                
                showNotification('success', 'Section assessment completed');
            } else {
                throw new Error(result.error || 'Assessment failed');
            }
        } catch (error) {
            console.error('Error assessing section:', error);
            showNotification('error', 'Failed to assess section: ' + error.message);
            
            // Restore guidance panel
            loadGuidance(sectionId);
        }
    };
    
    /**
     * Execute browser AI for section-specific assessment
     */
    async function executeBrowserAISectionAssessment(result, sectionId, guidancePanel) {
        try {
            // Check browser support
            if (typeof BrowserAIService === 'undefined') {
                throw new Error('Browser AI service not loaded');
            }
            
            const support = BrowserAIService.checkBrowserSupport();
            if (!support.required) {
                throw new Error('Browser does not support WebGPU or WebGL. Browser AI requires a modern browser with GPU support.');
            }
            
            // Show loading in guidance panel
            if (guidancePanel) {
                guidancePanel.innerHTML = `
                    <div class="p-6">
                        <div class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
                            <p class="mt-4 text-gray-600">Loading AI model. This may take a few minutes on first use...</p>
                        </div>
                    </div>
                `;
            }
            
            // Initialize browser AI
            const modelType = result.model_type === 'webllm' ? 'webllm' : 'tensorflow';
            await BrowserAIService.initBrowserAI(modelType, result.model, (progress) => {
                if (guidancePanel && progress.message) {
                    guidancePanel.querySelector('p').textContent = progress.message;
                }
            });
            
            // Use prompt from backend (it's already section-specific)
            const prompt = result.prompt || '';
            if (!prompt) {
                throw new Error('No prompt provided for browser AI execution');
            }
            
            // Update loading overlay
            if (guidancePanel) {
                guidancePanel.querySelector('p').textContent = 'Assessing section... This may take 30-60 seconds.';
            }
            
            // Generate assessment using browser AI
            const assessmentText = await BrowserAIService.generateText(prompt, {
                temperature: 0.3,
                maxTokens: 2000
            });
            
            // Parse assessment JSON with defensive extraction and repair
            const assessment = parseAssessmentJsonFromAI(assessmentText);
            
            // Cleanup
            await BrowserAIService.cleanup();
            
            // Display assessment in guidance panel
            if (guidancePanel) {
                let html = `
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">AI Recommendations: ${escapeHtml(sectionId.replace('-', ' '))}</h3>
                        <div class="space-y-4">
                `;
                
                if (assessment.strengths && assessment.strengths.length > 0) {
                    html += `
                        <div>
                            <h4 class="text-sm font-semibold text-green-700 mb-2">Strengths</h4>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                    `;
                    assessment.strengths.forEach(strength => {
                        html += `<li>${escapeHtml(strength)}</li>`;
                    });
                    html += `</ul></div>`;
                }
                
                if (assessment.weaknesses && assessment.weaknesses.length > 0) {
                    html += `
                        <div>
                            <h4 class="text-sm font-semibold text-red-700 mb-2">Areas for Improvement</h4>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                    `;
                    assessment.weaknesses.forEach(weakness => {
                        html += `<li>${escapeHtml(weakness)}</li>`;
                    });
                    html += `</ul></div>`;
                }
                
                if (assessment.recommendations && assessment.recommendations.length > 0) {
                    html += `
                        <div>
                            <h4 class="text-sm font-semibold text-blue-700 mb-2">Recommendations</h4>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                    `;
                    assessment.recommendations.forEach(rec => {
                        html += `<li>${escapeHtml(rec)}</li>`;
                    });
                    html += `</ul></div>`;
                }
                
                if (assessment.suggested_replacement) {
                    const normalizedReplacement = normalizeSuggestedReplacement(assessment.suggested_replacement, sectionId);
                    html += `
                        <div class="mt-4 pt-4 border-t">
                            <h4 class="text-sm font-semibold text-purple-700 mb-2">Suggested Replacement</h4>
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <pre class="whitespace-pre-wrap text-sm text-gray-800 font-mono">${escapeHtml(normalizedReplacement)}</pre>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">You can copy this improved version and use it to update your section.</p>
                        </div>
                    `;
                }
                
                html += `
                        </div>
                        <div class="mt-6 pt-4 border-t">
                            <a href="/cv-quality.php" class="text-sm text-purple-600 hover:text-purple-700">View Full CV Assessment →</a>
                        </div>
                    </div>
                `;
                
                guidancePanel.innerHTML = html;
            }
            
            showNotification('success', 'Section assessment completed');
        } catch (error) {
            console.error('Browser AI execution error:', error);
            showNotification('error', 'Browser AI Error: ' + error.message);
            
            // Restore guidance panel
            loadGuidance(sectionId);
        }
    }

    function initializeResponsibilitiesEditor() {
        // Check if responsibilities editor container exists
        const editorContainer = document.querySelector('[id^="responsibilities-editor-"]');
        if (!editorContainer) return;
        
        const workExperienceId = editorContainer.dataset.workExperienceId;
        if (!workExperienceId) {
            console.error('Work experience ID not found in editor container');
            return;
        }
        
        // Check if script is already loaded and function is available
        if (typeof window.initResponsibilitiesEditor !== 'undefined') {
            // Function is available, load data immediately
            loadResponsibilitiesData(workExperienceId, editorContainer);
            return;
        }
        
        // Check if script tag already exists
        let script = document.querySelector('script[src*="work-experience-responsibilities.js"]');
        if (script) {
            // Script tag exists, wait for it to load
            const checkFunction = setInterval(() => {
                if (typeof window.initResponsibilitiesEditor !== 'undefined') {
                    clearInterval(checkFunction);
                    loadResponsibilitiesData(workExperienceId, editorContainer);
                }
            }, 50);
            
            // Timeout after 5 seconds
            setTimeout(() => {
                clearInterval(checkFunction);
                if (typeof window.initResponsibilitiesEditor === 'undefined') {
                    console.error('Responsibilities editor script failed to load');
                    editorContainer.innerHTML = '<p class="text-red-500 p-4">Error loading responsibilities editor. Please refresh the page.</p>';
                }
            }, 5000);
        } else {
            // Load the script dynamically
            script = document.createElement('script');
            script.src = '/js/work-experience-responsibilities.js?v=' + Date.now();
            script.onload = function() {
                setTimeout(() => {
                    if (typeof window.initResponsibilitiesEditor !== 'undefined') {
                        loadResponsibilitiesData(workExperienceId, editorContainer);
                    } else {
                        console.error('Script loaded but initResponsibilitiesEditor function not found');
                        editorContainer.innerHTML = '<p class="text-red-500 p-4">Error initializing responsibilities editor. Please refresh the page.</p>';
                    }
                }, 100);
            };
            script.onerror = function() {
                console.error('Failed to load responsibilities editor script');
                editorContainer.innerHTML = '<p class="text-red-500 p-4">Error loading responsibilities editor. Please refresh the page.</p>';
            };
            document.head.appendChild(script);
        }
    }
    
    function loadResponsibilitiesData(workExperienceId, editorContainer) {
        // Show loading state
        editorContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-500">Loading responsibilities...</p>
            </div>
        `;
        
        const hash = (window.location.hash || '').replace(/^#/, '');
        const variantPart = hash.split('&').find(p => p.startsWith('variant_id='));
        const variantId = variantPart ? variantPart.slice(11) : null;
        let url = `/api/responsibilities.php?work_experience_id=${encodeURIComponent(workExperienceId)}&action=get`;
        if (variantId) {
            url += '&variant_id=' + encodeURIComponent(variantId);
        }
        
        fetch(url, {
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (typeof window.initResponsibilitiesEditor === 'undefined') {
                console.error('initResponsibilitiesEditor function not available after script load');
                editorContainer.innerHTML = '<p class="text-red-500 p-4">Error initializing responsibilities editor. Please refresh the page.</p>';
                return;
            }
            
            if (data.success) {
                window.initResponsibilitiesEditor(workExperienceId, data.categories || [], editorContainer, variantId);
            } else {
                window.initResponsibilitiesEditor(workExperienceId, [], editorContainer, variantId);
            }
        })
        .catch(error => {
            console.error('Error loading responsibilities:', error);
            editorContainer.innerHTML = '<p class="text-red-500 p-4">Error loading responsibilities. Please refresh the page.</p>';
        });
    }

    function initializeProjectImageHandlers() {
        const projectImagePreview = document.getElementById('project-image-preview');
        const projectImageInput = document.getElementById('project_image');
        const projectImageStatus = document.getElementById('project-image-status');
        const projectImageClear = document.getElementById('project-image-clear');
        const projectImageUrlInput = document.getElementById('image_url');
        const projectImagePathInput = document.getElementById('image_path');
        const projectImageResponsiveInput = document.getElementById('image_responsive');
        
        if (!projectImageInput || !projectImagePreview) return;
        
        function showProjectImageStatus(message, type) {
            if (!projectImageStatus) return;
            const classes = {
                success: 'border-green-200 text-green-700 bg-green-50',
                error: 'border-red-200 text-red-700 bg-red-50',
                info: 'border-blue-200 text-blue-700 bg-blue-50'
            };
            projectImageStatus.className = 'mt-2 rounded-md border px-3 py-2 text-sm ' + (classes[type] || classes.info);
            projectImageStatus.textContent = message;
            projectImageStatus.classList.remove('hidden');
        }
        
        function setProjectImagePreview(src) {
            if (src) {
                projectImagePreview.innerHTML = '<img src="' + src + '" alt="Project Image" class="w-32 h-32 object-cover rounded-md border border-gray-200">';
                projectImagePreview.className = 'w-32 h-32 rounded-md border border-gray-200';
                if (projectImageClear) projectImageClear.classList.remove('hidden');
            } else {
                projectImagePreview.innerHTML = 'No image';
                projectImagePreview.className = 'w-32 h-32 rounded-md border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 text-sm';
                if (projectImageClear) projectImageClear.classList.add('hidden');
            }
        }
        
        function resetProjectImagePreview() {
            setProjectImagePreview('');
            if (projectImageUrlInput) projectImageUrlInput.value = '';
            if (projectImagePathInput) projectImagePathInput.value = '';
            if (projectImageResponsiveInput) projectImageResponsiveInput.value = '';
        }
        
        let isUploading = false;
        
        function handleProjectImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            if (isUploading) {
                showProjectImageStatus('Upload already in progress. Please wait...', 'info');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                showProjectImageStatus('File too large. Maximum size is 5MB.', 'error');
                projectImageInput.value = '';
                return;
            }
            
            if (!file.type.match('image.*')) {
                showProjectImageStatus('Please choose an image file.', 'error');
                projectImageInput.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                setProjectImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
            
            const formData = new FormData();
            formData.append('project_image', file);
            formData.append(data.csrfTokenName, data.csrfToken);
            
            isUploading = true;
            showProjectImageStatus('Uploading image...', 'info');
            
            fetch('/api/upload-project-image.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(result => {
                isUploading = false;
                if (result.success) {
                    showProjectImageStatus('Image uploaded successfully', 'success');
                    if (projectImageUrlInput) projectImageUrlInput.value = result.url || '';
                    if (projectImagePathInput) projectImagePathInput.value = result.path || '';
                    if (projectImageResponsiveInput && result.responsive) {
                        projectImageResponsiveInput.value = JSON.stringify(result.responsive);
                    }
                    if (result.url) {
                        setProjectImagePreview(result.url);
                    }
                    setTimeout(() => {
                        if (projectImageStatus) projectImageStatus.classList.add('hidden');
                    }, 3000);
                } else {
                    showProjectImageStatus(result.error || 'Upload failed', 'error');
                    resetProjectImagePreview();
                }
            })
            .catch(error => {
                isUploading = false;
                console.error('Upload error:', error);
                showProjectImageStatus('Upload failed. Please try again.', 'error');
                resetProjectImagePreview();
            });
        }
        
        function clearProjectImage() {
            if (confirm('Remove this image?')) {
                resetProjectImagePreview();
                if (projectImageInput) projectImageInput.value = '';
                if (projectImageStatus) projectImageStatus.classList.add('hidden');
            }
        }
        
        if (projectImageInput) {
            projectImageInput.addEventListener('change', handleProjectImageUpload);
        }
        if (projectImageClear) {
            projectImageClear.addEventListener('click', clearProjectImage);
        }
    }

    function initializeWorkExperienceReorder(container) {
        const list = container.querySelector('#work-experiences-list');
        const toggleBtn = container.querySelector('#toggle-reorder-btn');
        const resetBtn = container.querySelector('#reset-reorder-btn');
        const reorderInfo = container.querySelector('#reorder-info');
        if (!list || !toggleBtn) return;

        let isReordering = false;
        let draggedElement = null;

        function getCsrfToken() {
            const input = container.querySelector('input[name="csrf_token"]');
            return input ? input.value : '';
        }

        function saveOrder() {
            const items = list.querySelectorAll('.work-experience-item');
            const orderedIds = Array.from(items).map(function(item) { return item.getAttribute('data-id'); });

            const formData = new FormData();
            formData.append('csrf_token', getCsrfToken());
            formData.append('action', 'reorder');
            formData.append('ordered_ids', JSON.stringify(orderedIds));

            fetch('/api/reorder-work-experience.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    let msg = container.querySelector('.work-experience-reorder-success');
                    if (!msg) {
                        msg = document.createElement('div');
                        msg.className = 'work-experience-reorder-success mb-4 rounded-md bg-green-50 p-4 text-green-700 text-sm';
                        const parent = container.querySelector('#work-experience-entries-list');
                        if (parent && parent.firstChild) parent.insertBefore(msg, parent.firstChild);
                        else if (parent) parent.appendChild(msg);
                    }
                    msg.textContent = 'Order updated successfully.';
                    msg.classList.remove('hidden');
                    setTimeout(function() { msg.classList.add('hidden'); }, 3000);
                } else {
                    alert('Failed to save order. Please try again.');
                }
            })
            .catch(function() {
                alert('Failed to save order. Please try again.');
            });
        }

        function resetToDateOrder() {
            if (!confirm('Reset order to date-based sorting (newest first)?')) return;
            const formData = new FormData();
            formData.append('csrf_token', getCsrfToken());
            formData.append('action', 'reset');
            fetch('/api/reorder-work-experience.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    loadSection('work-experience');
                } else {
                    alert('Failed to reset order. Please try again.');
                }
            })
            .catch(function() {
                alert('Failed to reset order. Please try again.');
            });
        }

        function handleDragStart(e) {
            draggedElement = this;
            this.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.getAttribute('data-id'));
        }

        function handleDragOver(e) {
            if (e.preventDefault) e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (this !== draggedElement && this.classList.contains('work-experience-item')) {
                this.classList.add('border-blue-500', 'bg-blue-50');
            }
            return false;
        }

        function handleDragLeave(e) {
            if (this !== draggedElement && this.classList.contains('work-experience-item')) {
                this.classList.remove('border-blue-500', 'bg-blue-50');
            }
        }

        function handleDrop(e) {
            if (e.stopPropagation) e.stopPropagation();
            if (draggedElement !== this && this.classList.contains('work-experience-item')) {
                const items = Array.from(list.querySelectorAll('.work-experience-item'));
                const draggedIndex = items.indexOf(draggedElement);
                const targetIndex = items.indexOf(this);
                if (draggedIndex < targetIndex) {
                    list.insertBefore(draggedElement, this.nextSibling);
                } else {
                    list.insertBefore(draggedElement, this);
                }
                saveOrder();
            }
            this.classList.remove('border-blue-500', 'bg-blue-50');
            return false;
        }

        function handleDragEnd(e) {
            this.classList.remove('opacity-50');
            draggedElement = null;
            list.querySelectorAll('.work-experience-item').forEach(function(el) {
                el.classList.remove('border-blue-500', 'bg-blue-50');
            });
        }

        function toggleReorderMode() {
            isReordering = !isReordering;
            const items = list.querySelectorAll('.work-experience-item');
            const dragHandles = list.querySelectorAll('.drag-handle');

            if (isReordering) {
                toggleBtn.textContent = 'Done reordering';
                toggleBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                toggleBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                if (reorderInfo) reorderInfo.classList.remove('hidden');
                items.forEach(function(item) {
                    item.setAttribute('draggable', 'true');
                    item.classList.add('cursor-move', 'border-2', 'border-blue-300');
                    var h = item.querySelector('.drag-handle');
                    if (h) h.classList.remove('hidden');
                    item.addEventListener('dragstart', handleDragStart);
                    item.addEventListener('dragover', handleDragOver);
                    item.addEventListener('dragleave', handleDragLeave);
                    item.addEventListener('drop', handleDrop);
                    item.addEventListener('dragend', handleDragEnd);
                });
            } else {
                toggleBtn.textContent = 'Reorder experiences';
                toggleBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                toggleBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                if (reorderInfo) reorderInfo.classList.add('hidden');
                items.forEach(function(item) {
                    item.setAttribute('draggable', 'false');
                    item.classList.remove('cursor-move', 'border-2', 'border-blue-300', 'border-blue-500', 'bg-blue-50');
                    var h = item.querySelector('.drag-handle');
                    if (h) h.classList.add('hidden');
                    item.removeEventListener('dragstart', handleDragStart);
                    item.removeEventListener('dragover', handleDragOver);
                    item.removeEventListener('dragleave', handleDragLeave);
                    item.removeEventListener('drop', handleDrop);
                    item.removeEventListener('dragend', handleDragEnd);
                });
            }
        }

        toggleBtn.addEventListener('click', toggleReorderMode);
        if (resetBtn) resetBtn.addEventListener('click', resetToDateOrder);
    }

    // Export for global access if needed
    window.contentEditor = {
        navigateToSection,
        loadSection,
        loadSectionData
    };
})();
