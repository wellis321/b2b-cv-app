/**
 * Layout builders for different column configurations
 */

/**
 * Single column layout (standard)
 */
export function createSingleColumnLayout(content, template, options = {}) {
    const {
        pageMargins = [40, 60, 40, 60]
    } = options

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: content
    }
}

/**
 * Two-column layout with sidebar
 * @param {Array} sidebar - Content for sidebar (typically left)
 * @param {Array} main - Content for main area (typically right)
 * @param {Object} template - Template configuration
 * @param {number} sidebarRatio - Sidebar width as ratio (0.0-1.0), default 0.3 (30%)
 */
export function createTwoColumnSidebarLayout(sidebar, main, template, sidebarRatio = 0.3, options = {}) {
    const {
        pageMargins = [40, 60, 40, 60],
        gap = 20,
        sidebarBackground = null
    } = options

    // Calculate widths based on page width (A4 = 595pt - margins)
    const pageWidth = 595 - pageMargins[0] - pageMargins[2]
    const sidebarWidth = Math.round(pageWidth * sidebarRatio)
    const mainWidth = pageWidth - sidebarWidth - gap

    const columns = [
        {
            width: sidebarWidth,
            stack: sidebar
        },
        {
            width: gap,
            text: '' // Gap
        },
        {
            width: mainWidth,
            stack: main
        }
    ]

    // Add background to sidebar if specified
    if (sidebarBackground) {
        return {
            pageSize: 'A4',
            pageMargins: pageMargins,
            background: (currentPage) => {
                return {
                    canvas: [
                        {
                            type: 'rect',
                            x: pageMargins[0],
                            y: 0,
                            w: sidebarWidth,
                            h: 842, // A4 height
                            color: sidebarBackground
                        }
                    ]
                }
            },
            content: [
                {
                    columns: columns
                }
            ]
        }
    }

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: [
            {
                columns: columns
            }
        ]
    }
}

/**
 * Two-column balanced layout (50/50 split)
 */
export function createTwoColumnBalancedLayout(leftContent, rightContent, template, options = {}) {
    const {
        pageMargins = [40, 60, 40, 60],
        gap = 20
    } = options

    const pageWidth = 595 - pageMargins[0] - pageMargins[2]
    const columnWidth = Math.round((pageWidth - gap) / 2)

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: [
            {
                columns: [
                    {
                        width: columnWidth,
                        stack: leftContent
                    },
                    {
                        width: gap,
                        text: ''
                    },
                    {
                        width: columnWidth,
                        stack: rightContent
                    }
                ]
            }
        ]
    }
}

/**
 * Asymmetric two-column layout (custom ratio)
 */
export function createAsymmetricLayout(primaryContent, secondaryContent, template, primaryRatio = 0.65, options = {}) {
    const {
        pageMargins = [40, 60, 40, 60],
        gap = 20,
        primaryOnLeft = true
    } = options

    const pageWidth = 595 - pageMargins[0] - pageMargins[2]
    const primaryWidth = Math.round(pageWidth * primaryRatio)
    const secondaryWidth = pageWidth - primaryWidth - gap

    const leftContent = primaryOnLeft ? primaryContent : secondaryContent
    const rightContent = primaryOnLeft ? secondaryContent : primaryContent
    const leftWidth = primaryOnLeft ? primaryWidth : secondaryWidth
    const rightWidth = primaryOnLeft ? secondaryWidth : primaryWidth

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: [
            {
                columns: [
                    {
                        width: leftWidth,
                        stack: leftContent
                    },
                    {
                        width: gap,
                        text: ''
                    },
                    {
                        width: rightWidth,
                        stack: rightContent
                    }
                ]
            }
        ]
    }
}

/**
 * Three-column layout
 */
export function createThreeColumnLayout(leftContent, centerContent, rightContent, template, options = {}) {
    const {
        pageMargins = [40, 60, 40, 60],
        gap = 15,
        ratios = [0.3, 0.4, 0.3] // left, center, right
    } = options

    const pageWidth = 595 - pageMargins[0] - pageMargins[2]
    const totalGap = gap * 2
    const availableWidth = pageWidth - totalGap

    const leftWidth = Math.round(availableWidth * ratios[0])
    const centerWidth = Math.round(availableWidth * ratios[1])
    const rightWidth = availableWidth - leftWidth - centerWidth

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: [
            {
                columns: [
                    {
                        width: leftWidth,
                        stack: leftContent
                    },
                    {
                        width: gap,
                        text: ''
                    },
                    {
                        width: centerWidth,
                        stack: centerContent
                    },
                    {
                        width: gap,
                        text: ''
                    },
                    {
                        width: rightWidth,
                        stack: rightContent
                    }
                ]
            }
        ]
    }
}

/**
 * Header-focused layout (large header section + content)
 */
export function createHeaderFocusedLayout(headerContent, bodyContent, template, options = {}) {
    const {
        pageMargins = [40, 80, 40, 60],
        headerMargin = [0, 0, 0, 20],
        headerBackground = null
    } = options

    const content = [
        {
            stack: headerContent,
            margin: headerMargin
        },
        ...bodyContent
    ]

    if (headerBackground) {
        return {
            pageSize: 'A4',
            pageMargins: pageMargins,
            background: (currentPage) => {
                if (currentPage === 1) {
                    return {
                        canvas: [
                            {
                                type: 'rect',
                                x: 0,
                                y: 0,
                                w: 595, // A4 width
                                h: 150,
                                color: headerBackground
                            }
                        ]
                    }
                }
                return null
            },
            content: content
        }
    }

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: content
    }
}

/**
 * Grid layout helper (for skills, interests, etc.)
 */
export function createGridLayout(items, template, columns = 2, options = {}) {
    const {
        gap = 10,
        itemMargin = [0, 3, 0, 3]
    } = options

    if (!Array.isArray(items) || items.length === 0) {
        return []
    }

    const rows = []
    for (let i = 0; i < items.length; i += columns) {
        const rowItems = items.slice(i, i + columns)
        const columnDefs = []

        rowItems.forEach((item, idx) => {
            if (idx > 0) {
                columnDefs.push({ width: gap, text: '' })
            }
            columnDefs.push({
                width: '*',
                stack: Array.isArray(item) ? item : [item],
                margin: itemMargin
            })
        })

        // Fill remaining columns
        while (columnDefs.length < columns * 2 - 1) {
            columnDefs.push({ width: gap, text: '' })
            columnDefs.push({ width: '*', text: '' })
        }

        rows.push({
            columns: columnDefs
        })
    }

    return rows
}

/**
 * Compact layout (reduced spacing for maximum information density)
 */
export function createCompactLayout(content, template, options = {}) {
    const {
        pageMargins = [35, 50, 35, 50]
    } = options

    return {
        pageSize: 'A4',
        pageMargins: pageMargins,
        content: content,
        defaultStyle: {
            lineHeight: 1.3 // Tighter line height
        }
    }
}
