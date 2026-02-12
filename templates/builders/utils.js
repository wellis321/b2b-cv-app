/**
 * Shared utility functions for template builders
 */

/**
 * Decode HTML entities in text
 */
export function decodeHtmlEntities(value) {
    if (value == null) {
        return ''
    }

    if (typeof value !== 'string') {
        value = String(value)
    }

    if (typeof window === 'undefined') {
        return value
    }

    const textarea = window.__pdfTemplateDecoder || document.createElement('textarea')
    if (!window.__pdfTemplateDecoder) {
        window.__pdfTemplateDecoder = textarea
    }

    textarea.innerHTML = value
    return textarea.value
}

/**
 * Convert markdown to plain text (basic conversion)
 */
export function convertMarkdownToPlainText(text) {
    if (!text) return ''
    return String(text)
        .replace(/\*\*(.*?)\*\*/g, '$1')  // Bold
        .replace(/\*(.*?)\*/g, '$1')      // Italic
        .replace(/^•\s+/gm, '• ')          // Bullets
        .replace(/^\-\s+/gm, '- ')         // Dashes
        .replace(/`/g, '')                 // Code
        .replace(/\r?\n/g, '\n')
        .trim()
}

/**
 * Check if text has visible content
 */
export function hasVisibleText(value) {
    if (!value) return false
    const plain = convertMarkdownToPlainText(value)
    return Boolean(plain && plain.trim())
}

/**
 * Format date to MM/YYYY
 */
export function formatDate(dateStr) {
    if (!dateStr) return ''

    const date = new Date(dateStr)
    if (Number.isNaN(date.getTime())) {
        return dateStr
    }

    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${month}/${year}`
}

/**
 * Format date range
 */
export function formatDateRange(startDate, endDate) {
    const start = formatDate(startDate)
    const end = endDate ? formatDate(endDate) : 'Present'
    if (!start) return end
    return `${start} - ${end}`
}

/**
 * Group skills by category
 */
export function groupSkills(skills) {
    const grouped = {}

    if (!Array.isArray(skills)) {
        return grouped
    }

    skills.forEach((skill) => {
        const category = skill.category || 'Other'
        if (!grouped[category]) {
            grouped[category] = []
        }
        grouped[category].push(skill)
    })

    return grouped
}

/**
 * Get template color or fallback
 */
export function getColor(template, colorKey, fallback) {
    return template?.colors?.[colorKey] || fallback
}

/**
 * Apply font size multiplier to base size
 */
export function scaleFontSize(baseSize, multiplier = 1.0) {
    return Math.round(baseSize * multiplier)
}

/**
 * Get font size multiplier from preference
 */
export function getFontSizeMultiplier(size) {
    const multipliers = {
        small: 0.9,
        medium: 1.0,
        large: 1.1
    }
    return multipliers[size] || 1.0
}

/**
 * Get spacing multiplier from preference
 */
export function getSpacingMultiplier(spacing) {
    const multipliers = {
        compact: 0.8,
        normal: 1.0,
        spacious: 1.2
    }
    return multipliers[spacing] || 1.0
}

/**
 * Create a horizontal line/divider
 */
export function createDivider(color = '#d1d5db', width = 1, margin = [0, 4, 0, 6]) {
    return {
        canvas: [
            {
                type: 'line',
                x1: 0,
                y1: 0,
                x2: 515,
                y2: 0,
                lineWidth: width,
                lineColor: color
            }
        ],
        margin: margin
    }
}

/**
 * Truncate text to max length with ellipsis
 */
export function truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) {
        return text
    }
    return text.substring(0, maxLength - 3) + '...'
}

/**
 * Create a bullet list item
 */
export function createBulletItem(text, color = '#374151') {
    return {
        text: text,
        margin: [0, 2, 0, 2],
        color: color
    }
}

/**
 * Validate hex color code
 */
export function isValidHexColor(color) {
    return /^#[0-9A-Fa-f]{6}$/.test(color)
}

/**
 * Lighten a hex color
 */
export function lightenColor(hex, percent) {
    const num = parseInt(hex.replace('#', ''), 16)
    const amt = Math.round(2.55 * percent)
    const R = (num >> 16) + amt
    const G = (num >> 8 & 0x00FF) + amt
    const B = (num & 0x0000FF) + amt
    return '#' + (
        0x1000000 +
        (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
        (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
        (B < 255 ? B < 1 ? 0 : B : 255)
    ).toString(16).slice(1)
}

/**
 * Darken a hex color
 */
export function darkenColor(hex, percent) {
    return lightenColor(hex, -percent)
}
