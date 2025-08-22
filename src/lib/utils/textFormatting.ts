/**
 * Utility functions for formatting text content
 */

/**
 * Formats description text with proper line breaks by converting newlines to paragraphs
 * @param description - The text description that may contain newline characters
 * @returns Array of paragraphs (each paragraph is a string)
 */
export function formatDescription(description: string): string[] {
    if (!description) return [];

    // Split by newlines but keep empty lines for paragraph detection
    const lines = description.split('\n');

    // Group consecutive lines into paragraphs
    const paragraphs: string[] = [];
    let currentParagraph: string[] = [];

    for (const line of lines) {
        if (line.trim() === '') {
            // Empty line indicates paragraph break
            if (currentParagraph.length > 0) {
                paragraphs.push(currentParagraph.join(' '));
                currentParagraph = [];
            }
        } else {
            currentParagraph.push(line.trim());
        }
    }

    // Add the last paragraph if there is one
    if (currentParagraph.length > 0) {
        paragraphs.push(currentParagraph.join(' '));
    }

    return paragraphs;
}

/**
 * Formats description text with HTML line breaks (use with caution)
 * @param description - The text description that may contain newline characters
 * @returns HTML string with <br> tags for line breaks
 */
export function formatDescriptionWithHtml(description: string): string {
    if (!description) return '';
    // Convert newline characters to HTML line breaks
    return description.replace(/\n/g, '<br>');
}

/**
 * Formats description text with CSS white-space preservation
 * @param description - The text description that may contain newline characters
 * @returns The original text (to be used with CSS white-space: pre-line)
 */
export function formatDescriptionWithCss(description: string): string {
    if (!description) return '';
    // Return the original text - use CSS white-space: pre-line to preserve formatting
    return description;
}

// Function to render formatted text with basic markdown-like syntax
export function renderFormattedText(text: string): string {
    if (!text) return '';

    // Convert markdown-like syntax to HTML
    return text
        // Bold text: **text** -> <strong>text</strong>
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Italic text: *text* -> <em>text</em>
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Bullet points: • text -> <li>text</li>
        .replace(/^•\s+(.*)$/gm, '<li>$1</li>')
        // Dash points: - text -> <li>text</li>
        .replace(/^-\s+(.*)$/gm, '<li>$1</li>')
        // Convert line breaks to <br> tags
        .replace(/\n/g, '<br>');
}

// Function to format description with proper HTML rendering
export function formatDescriptionWithFormatting(description: string): string[] {
    if (!description) return [];

    // Split by newlines but keep empty lines for paragraph detection
    const lines = description.split('\n');

    // Group consecutive lines into paragraphs
    const paragraphs: string[] = [];
    let currentParagraph: string[] = [];

    for (const line of lines) {
        if (line.trim() === '') {
            // Empty line indicates paragraph break
            if (currentParagraph.length > 0) {
                const paragraphText = currentParagraph.join(' ');
                // Check if this paragraph contains list items
                if (currentParagraph.some(line => line.trim().match(/^[•-]\s+/))) {
                    // Format as a list
                    const listItems = currentParagraph
                        .filter(line => line.trim().match(/^[•-]\s+/))
                        .map(line => line.trim().replace(/^[•-]\s+/, ''));

                    paragraphs.push(`<ul class="list-disc list-inside space-y-1">${listItems.map(item => `<li>${item}</li>`).join('')}</ul>`);
                } else {
                    // Format as regular paragraph with inline formatting
                    paragraphs.push(renderFormattedText(paragraphText));
                }
                currentParagraph = [];
            }
        } else {
            currentParagraph.push(line.trim());
        }
    }

    // Add the last paragraph if there is one
    if (currentParagraph.length > 0) {
        const paragraphText = currentParagraph.join(' ');
        // Check if this paragraph contains list items
        if (currentParagraph.some(line => line.trim().match(/^[•-]\s+/))) {
            // Format as a list
            const listItems = currentParagraph
                .filter(line => line.trim().match(/^[•-]\s+/))
                .map(line => line.trim().replace(/^[•-]\s+/, ''));

            paragraphs.push(`<ul class="list-disc list-inside space-y-1">${listItems.map(item => `<li>${item}</li>`).join('')}</ul>`);
        } else {
            // Format as regular paragraph with inline formatting
            paragraphs.push(renderFormattedText(paragraphText));
        }
    }

    return paragraphs;
}
