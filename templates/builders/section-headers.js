/**
 * Section header builders for different styles
 */

import { getColor, createDivider } from './utils.js'

/**
 * Create a section header with underline
 */
export function createLineHeader(title, template, options = {}) {
    const {
        fontSize = 15,
        bold = true,
        lineWidth = 2,
        margin = [0, 12, 0, 8]
    } = options

    const accentColor = getColor(template, 'accent', '#2563eb')
    const headerColor = getColor(template, 'header', '#1f2937')

    return [
        {
            text: title,
            style: 'subheader',
            fontSize: fontSize,
            bold: bold,
            color: headerColor,
            margin: [0, margin[0], 0, 4]
        },
        createDivider(accentColor, lineWidth, [0, 0, 0, margin[3]])
    ]
}

/**
 * Create a minimal section header
 */
export function createMinimalHeader(title, template, options = {}) {
    const {
        fontSize = 14,
        bold = true,
        margin = [0, 10, 0, 6]
    } = options

    const dividerColor = getColor(template, 'divider', '#d1d5db')
    const headerColor = getColor(template, 'header', '#111827')

    return [
        {
            text: title,
            fontSize: fontSize,
            bold: bold,
            color: headerColor,
            margin: [0, margin[0], 0, 4]
        },
        createDivider(dividerColor, 0.75, [0, 0, 0, margin[3]])
    ]
}

/**
 * Create a bold section header with thick accent bar
 */
export function createBoldHeader(title, template, options = {}) {
    const {
        fontSize = 16,
        bold = true,
        barHeight = 3,
        margin = [0, 12, 0, 8]
    } = options

    const accentColor = getColor(template, 'accent', '#2563eb')
    const headerColor = getColor(template, 'header', '#1f2937')

    return [
        {
            text: title,
            fontSize: fontSize,
            bold: bold,
            color: headerColor,
            margin: [0, margin[0], 0, 2]
        },
        {
            canvas: [
                {
                    type: 'rect',
                    x: 0,
                    y: 0,
                    w: 80,
                    h: barHeight,
                    color: accentColor
                }
            ],
            margin: [0, 0, 0, margin[3]]
        }
    ]
}

/**
 * Create a filled section header with background
 */
export function createFilledHeader(title, template, options = {}) {
    const {
        fontSize = 14,
        bold = true,
        margin = [0, 10, 0, 6],
        padding = 6
    } = options

    const accentColor = getColor(template, 'accent', '#2563eb')

    return {
        table: {
            widths: ['*'],
            body: [
                [
                    {
                        text: title,
                        fontSize: fontSize,
                        bold: bold,
                        color: '#ffffff',
                        margin: [padding, padding - 2, padding, padding - 2]
                    }
                ]
            ]
        },
        layout: {
            fillColor: accentColor,
            hLineWidth: () => 0,
            vLineWidth: () => 0
        },
        margin: [0, margin[0], 0, margin[3]]
    }
}

/**
 * Create a section header with icon/symbol
 */
export function createIconHeader(title, template, symbol = '■', options = {}) {
    const {
        fontSize = 15,
        bold = true,
        symbolSize = 10,
        margin = [0, 12, 0, 8]
    } = options

    const accentColor = getColor(template, 'accent', '#2563eb')
    const headerColor = getColor(template, 'header', '#1f2937')

    return {
        columns: [
            {
                text: symbol,
                fontSize: symbolSize,
                color: accentColor,
                width: 15,
                margin: [0, 2, 0, 0]
            },
            {
                text: title,
                fontSize: fontSize,
                bold: bold,
                color: headerColor,
                width: '*'
            }
        ],
        margin: [0, margin[0], 0, margin[3]]
    }
}

/**
 * Create a section header with side border
 */
export function createSideBorderHeader(title, template, options = {}) {
    const {
        fontSize = 15,
        bold = true,
        borderWidth = 3,
        margin = [0, 12, 0, 8],
        uppercase = true
    } = options

    const accentColor = getColor(template, 'accent', '#2563eb')
    const headerColor = getColor(template, 'header', '#1f2937')

    return [{
        columns: [
            {
                canvas: [
                    {
                        type: 'line',
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: fontSize + 4,
                        lineWidth: borderWidth,
                        lineColor: accentColor
                    }
                ],
                width: borderWidth + 12
            },
            {
                text: uppercase ? title.toUpperCase() : title,
                fontSize: fontSize,
                bold: bold,
                color: headerColor,
                width: '*',
                margin: [0, 0, 0, 0]
            }
        ],
        margin: [0, margin[0], 0, margin[3]]
    }]
}

/**
 * Create a classic section header (centered with borders)
 */
export function createClassicHeader(title, template, options = {}) {
    const {
        fontSize = 13,
        bold = true,
        margin = [0, 10, 0, 6],
        uppercase = true
    } = options

    const dividerColor = getColor(template, 'divider', '#1e3a8a')
    const headerColor = getColor(template, 'header', '#1e3a8a')

    return [
        createDivider(dividerColor, 1, [0, margin[0], 0, 3]),
        {
            text: uppercase ? title.toUpperCase() : title,
            fontSize: fontSize,
            bold: bold,
            color: headerColor,
            alignment: 'center',
            margin: [0, 0, 0, 3]
        },
        createDivider(dividerColor, 1, [0, 0, 0, margin[3]])
    ]
}

/**
 * Get section header builder by style name
 */
export function getSectionHeaderBuilder(style) {
    const builders = {
        line: createLineHeader,
        minimal: createMinimalHeader,
        bold: createBoldHeader,
        filled: createFilledHeader,
        icon: createIconHeader,
        sideBorder: createSideBorderHeader,
        classic: createClassicHeader
    }
    return builders[style] || createLineHeader
}
