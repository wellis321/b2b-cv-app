/**
 * Typography and style presets for templates
 */

import { getColor, scaleFontSize, getFontSizeMultiplier } from './utils.js'

/**
 * Conservative/Traditional style preset
 */
export function getConservativeStyles(template, customization = {}) {
    const fontMultiplier = getFontSizeMultiplier(customization.fontSize || 'medium')

    return {
        header: {
            fontSize: scaleFontSize(22, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#1f2937')
        },
        tagline: {
            fontSize: scaleFontSize(12, fontMultiplier),
            color: getColor(template, 'muted', '#6b7280')
        },
        subheader: {
            fontSize: scaleFontSize(14, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#1f2937')
        },
        paragraph: {
            fontSize: scaleFontSize(11, fontMultiplier),
            lineHeight: 1.5,
            color: getColor(template, 'body', '#374151')
        },
        small: {
            fontSize: scaleFontSize(10, fontMultiplier),
            color: getColor(template, 'muted', '#6b7280')
        }
    }
}

/**
 * Modern/Contemporary style preset
 */
export function getModernStyles(template, customization = {}) {
    const fontMultiplier = getFontSizeMultiplier(customization.fontSize || 'medium')

    return {
        header: {
            fontSize: scaleFontSize(24, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#0f172a')
        },
        tagline: {
            fontSize: scaleFontSize(11, fontMultiplier),
            color: getColor(template, 'accent', '#0d9488'),
            italics: true
        },
        subheader: {
            fontSize: scaleFontSize(15, fontMultiplier),
            bold: true,
            color: getColor(template, 'accent', '#0d9488')
        },
        paragraph: {
            fontSize: scaleFontSize(10.5, fontMultiplier),
            lineHeight: 1.4,
            color: getColor(template, 'body', '#334155')
        },
        small: {
            fontSize: scaleFontSize(9.5, fontMultiplier),
            color: getColor(template, 'muted', '#64748b')
        }
    }
}

/**
 * Compact style preset (maximum information density)
 */
export function getCompactStyles(template, customization = {}) {
    const fontMultiplier = getFontSizeMultiplier(customization.fontSize || 'medium')

    return {
        header: {
            fontSize: scaleFontSize(20, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#1f2937')
        },
        tagline: {
            fontSize: scaleFontSize(10, fontMultiplier),
            color: getColor(template, 'muted', '#6b7280')
        },
        subheader: {
            fontSize: scaleFontSize(13, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#374151')
        },
        paragraph: {
            fontSize: scaleFontSize(10, fontMultiplier),
            lineHeight: 1.3,
            color: getColor(template, 'body', '#374151')
        },
        small: {
            fontSize: scaleFontSize(9, fontMultiplier),
            color: getColor(template, 'muted', '#6b7280')
        }
    }
}

/**
 * Spacious/Luxury style preset
 */
export function getSpaciousStyles(template, customization = {}) {
    const fontMultiplier = getFontSizeMultiplier(customization.fontSize || 'medium')

    return {
        header: {
            fontSize: scaleFontSize(26, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#172554')
        },
        tagline: {
            fontSize: scaleFontSize(13, fontMultiplier),
            color: getColor(template, 'muted', '#64748b')
        },
        subheader: {
            fontSize: scaleFontSize(16, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#172554')
        },
        paragraph: {
            fontSize: scaleFontSize(11.5, fontMultiplier),
            lineHeight: 1.6,
            color: getColor(template, 'body', '#334155')
        },
        small: {
            fontSize: scaleFontSize(10.5, fontMultiplier),
            color: getColor(template, 'muted', '#64748b')
        }
    }
}

/**
 * Creative style preset
 */
export function getCreativeStyles(template, customization = {}) {
    const fontMultiplier = getFontSizeMultiplier(customization.fontSize || 'medium')

    return {
        header: {
            fontSize: scaleFontSize(28, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#3b0764')
        },
        tagline: {
            fontSize: scaleFontSize(12, fontMultiplier),
            color: getColor(template, 'accent', '#7c3aed'),
            italics: true
        },
        subheader: {
            fontSize: scaleFontSize(14, fontMultiplier),
            bold: true,
            color: getColor(template, 'accent', '#7c3aed')
        },
        paragraph: {
            fontSize: scaleFontSize(11, fontMultiplier),
            lineHeight: 1.5,
            color: getColor(template, 'body', '#334155')
        },
        small: {
            fontSize: scaleFontSize(10, fontMultiplier),
            color: getColor(template, 'muted', '#64748b')
        }
    }
}

/**
 * Technical style preset (developer-focused)
 */
export function getTechnicalStyles(template, customization = {}) {
    const fontMultiplier = getFontSizeMultiplier(customization.fontSize || 'medium')

    return {
        header: {
            fontSize: scaleFontSize(22, fontMultiplier),
            bold: true,
            color: getColor(template, 'header', '#0f172a')
        },
        tagline: {
            fontSize: scaleFontSize(10.5, fontMultiplier),
            color: getColor(template, 'accent', '#06b6d4')
        },
        subheader: {
            fontSize: scaleFontSize(14, fontMultiplier),
            bold: true,
            color: getColor(template, 'accent', '#06b6d4')
        },
        paragraph: {
            fontSize: scaleFontSize(10.5, fontMultiplier),
            lineHeight: 1.4,
            color: getColor(template, 'body', '#334155')
        },
        small: {
            fontSize: scaleFontSize(9.5, fontMultiplier),
            color: getColor(template, 'muted', '#64748b')
        },
        code: {
            fontSize: scaleFontSize(10, fontMultiplier),
            font: 'Courier',
            color: getColor(template, 'accent', '#06b6d4')
        }
    }
}

/**
 * Get default pdfmake styles object for a template
 */
export function getDefaultPdfStyles(template, preset = 'conservative', customization = {}) {
    const presets = {
        conservative: getConservativeStyles,
        modern: getModernStyles,
        compact: getCompactStyles,
        spacious: getSpaciousStyles,
        creative: getCreativeStyles,
        technical: getTechnicalStyles
    }

    const styleGetter = presets[preset] || getConservativeStyles
    return styleGetter(template, customization)
}

/**
 * Get page margins based on style preset
 */
export function getPageMargins(preset = 'conservative', customization = {}) {
    const margins = {
        conservative: [40, 60, 40, 60],
        modern: [45, 65, 45, 65],
        compact: [35, 50, 35, 50],
        spacious: [50, 70, 50, 70],
        creative: [40, 60, 40, 60],
        technical: [40, 55, 40, 55]
    }

    return margins[preset] || margins.conservative
}

/**
 * Get line height based on style preset
 */
export function getLineHeight(preset = 'conservative') {
    const lineHeights = {
        conservative: 1.5,
        modern: 1.4,
        compact: 1.3,
        spacious: 1.6,
        creative: 1.5,
        technical: 1.4
    }

    return lineHeights[preset] || 1.44
}

/**
 * Get section spacing based on style preset
 */
export function getSectionSpacing(preset = 'conservative') {
    const spacing = {
        conservative: { top: 12, bottom: 8 },
        modern: { top: 14, bottom: 10 },
        compact: { top: 10, bottom: 6 },
        spacious: { top: 16, bottom: 12 },
        creative: { top: 12, bottom: 8 },
        technical: { top: 10, bottom: 6 }
    }

    return spacing[preset] || spacing.conservative
}

/**
 * Build complete pdfmake document config with styles
 */
export function buildDocumentConfig(template, preset = 'conservative', customization = {}) {
    return {
        pageSize: 'A4',
        pageMargins: getPageMargins(preset, customization),
        defaultStyle: {
            font: 'Roboto',
            fontSize: scaleFontSize(11, getFontSizeMultiplier(customization.fontSize || 'medium')),
            color: getColor(template, 'body', '#374151'),
            lineHeight: getLineHeight(preset)
        },
        styles: getDefaultPdfStyles(template, preset, customization)
    }
}
