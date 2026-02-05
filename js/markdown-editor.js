/**
 * Simple Markdown Editor Toolbar
 * Adds formatting buttons above textareas or contenteditable elements.
 * Supports tables; works in both markdown (textarea) and WYSIWYG (contenteditable) mode.
 */

(function() {
    'use strict';

    function isContentEditable(el) {
        return el && (el.getAttribute('contenteditable') === 'true' || el.isContentEditable);
    }

    function getDefaultTableHtml() {
        return '<table><thead><tr><th>Heading 1</th><th>Heading 2</th></tr></thead><tbody><tr><td> </td><td> </td></tr><tr><td> </td><td> </td></tr></tbody></table>';
    }

    /**
     * Initialize toolbar for a textarea or contenteditable
     * @param {HTMLTextAreaElement|HTMLElement} element - Textarea or contenteditable to enhance
     */
    function initMarkdownEditor(element) {
        if (!element || !element.parentNode) return;
        if (element.dataset.markdownInitialized === 'true') return;

        const isWysiwyg = isContentEditable(element);

        const toolbar = document.createElement('div');
        toolbar.className = 'markdown-toolbar flex flex-wrap gap-1 p-2 bg-gray-50 border border-gray-300 border-b-0 rounded-t-md';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Text formatting');

        function runAction(fn) {
            return function() {
                fn();
                element.focus();
            };
        }

        const buttons = isWysiwyg ? [
            { icon: '<strong>B</strong>', title: 'Bold', action: () => document.execCommand('bold', false, null) },
            { icon: '<em>I</em>', title: 'Italic', action: () => document.execCommand('italic', false, null) },
            { icon: '<u>U</u>', title: 'Underline', action: () => document.execCommand('underline', false, null) },
            { icon: 'H', title: 'Heading', action: () => document.execCommand('formatBlock', false, 'h2') },
            { icon: '⊞', title: 'Insert table', action: () => insertHtmlInEditable(element, getDefaultTableHtml()) },
            { icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>', title: 'Link', action: () => insertLinkWysiwyg(element) }
        ] : [
            { icon: '<strong>B</strong>', title: 'Bold', action: () => wrapSelection(element, '**', '**') },
            { icon: '<em>I</em>', title: 'Italic', action: () => wrapSelection(element, '*', '*') },
            { icon: '<u>U</u>', title: 'Underline', action: () => wrapSelection(element, '<u>', '</u>') },
            { icon: 'H', title: 'Heading', action: () => wrapSelection(element, '## ', '', true) },
            { icon: '•', title: 'Bullet List', action: () => wrapSelection(element, '- ', '', true) },
            { icon: '1.', title: 'Numbered List', action: () => wrapSelection(element, '1. ', '', true) },
            { icon: '⊞', title: 'Insert table', action: () => insertTableTextarea(element) },
            { icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>', title: 'Link', action: () => insertLink(element) }
        ];

        buttons.forEach(function(btn) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'px-2 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1';
            button.innerHTML = btn.icon;
            button.title = btn.title;
            button.setAttribute('aria-label', btn.title);
            button.addEventListener('click', function(e) {
                e.preventDefault();
                runAction(btn.action)();
            });
            toolbar.appendChild(button);
        });

        element.parentNode.insertBefore(toolbar, element);
        element.classList.add('markdown-textarea');
        element.style.borderTopLeftRadius = '0';
        element.style.borderTopRightRadius = '0';
        element.dataset.markdownInitialized = 'true';
    }

    function insertHtmlInEditable(editable, html) {
        editable.focus();
        if (document.execCommand('insertHTML', false, html)) return;
        var sel = window.getSelection();
        var range = sel && sel.rangeCount ? sel.getRangeAt(0) : null;
        if (!range) return;
        var frag = editable.ownerDocument.createContextualFragment(html);
        range.deleteContents();
        range.insertNode(frag);
        range.collapse(false);
        sel.removeAllRanges();
        sel.addRange(range);
    }

    function insertLinkWysiwyg(editable) {
        var url = window.prompt('Link URL:', 'https://');
        if (url == null || url === '') return;
        var html = '<a href="' + url.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener">' + (window.getSelection() ? window.getSelection().toString() : 'link') + '</a>';
        insertHtmlInEditable(editable, html);
    }

    function insertTableTextarea(textarea) {
        var html = getDefaultTableHtml();
        var start = textarea.selectionStart;
        var before = textarea.value.substring(0, start);
        var after = textarea.value.substring(start);
        var newline = before.length > 0 && before[before.length - 1] !== '\n' ? '\n' : '';
        textarea.value = before + newline + html + after;
        textarea.selectionStart = textarea.selectionEnd = start + newline.length + html.length;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Wrap selected text with markdown syntax
     */
    function wrapSelection(textarea, prefix, suffix, newline) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        const before = textarea.value.substring(0, start);
        const after = textarea.value.substring(end);

        let replacement;
        if (newline) {
            // For headers and lists, add newline before if needed
            const needsNewline = before.length > 0 && before[before.length - 1] !== '\n';
            replacement = (needsNewline ? '\n' : '') + prefix + selectedText + suffix;
        } else {
            replacement = prefix + selectedText + suffix;
        }

        textarea.value = before + replacement + after;
        textarea.selectionStart = start + replacement.length;
        textarea.selectionEnd = textarea.selectionStart;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Insert a link
     */
    function insertLink(textarea) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        const before = textarea.value.substring(0, start);
        const after = textarea.value.substring(end);

        const linkText = selectedText || 'link text';
        const replacement = '[' + linkText + '](https://example.com)';

        textarea.value = before + replacement + after;
        const newStart = start + replacement.length;
        // Select the URL part for easy editing
        if (!selectedText) {
            textarea.selectionStart = start + linkText.length + 3; // After "[link text]("
            textarea.selectionEnd = textarea.selectionStart + 19; // Select "https://example.com"
        } else {
            textarea.selectionStart = newStart;
            textarea.selectionEnd = newStart;
        }
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Initialize all markdown editors on the page (textareas and contenteditable with data-markdown)
     */
    function initAllMarkdownEditors() {
        const elements = document.querySelectorAll('textarea[data-markdown], [data-markdown][contenteditable="true"]');
        elements.forEach(function(el) {
            if (el.dataset.markdownInitialized === 'true') return;
            initMarkdownEditor(el);
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllMarkdownEditors);
    } else {
        initAllMarkdownEditors();
    }

    // Export for manual initialization
    window.MarkdownEditor = {
        init: initMarkdownEditor,
        initAll: initAllMarkdownEditors
    };
})();
