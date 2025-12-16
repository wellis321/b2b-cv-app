/**
 * Section content builders for consistent CV section rendering
 */

import {
    decodeHtmlEntities,
    convertMarkdownToPlainText,
    hasVisibleText,
    formatDateRange,
    groupSkills,
    getColor,
    createBulletItem
} from './utils.js'

/**
 * Build work experience section
 */
export function buildWorkExperienceSection(experiences, template, options = {}) {
    const {
        showDates = true,
        showDescription = true,
        showResponsibilities = true,
        fontSize = 11,
        spacing = 1.0
    } = options

    if (!Array.isArray(experiences) || experiences.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const mutedColor = getColor(template, 'muted', '#6b7280')
    const accentColor = getColor(template, 'accent', '#2563eb')

    const content = []

    experiences.forEach((exp, index) => {
        const expContent = []

        // Position and Company
        if (exp.position) {
            expContent.push({
                text: decodeHtmlEntities(exp.position),
                style: 'jobPosition',
                fontSize: fontSize + 2,
                bold: true,
                color: bodyColor
            })
        }

        if (exp.company_name) {
            expContent.push({
                text: decodeHtmlEntities(exp.company_name),
                style: 'company',
                fontSize: fontSize,
                color: accentColor,
                margin: [0, 2, 0, 2]
            })
        }

        // Dates
        if (showDates && !exp.hide_date && (exp.start_date || exp.end_date)) {
            expContent.push({
                text: formatDateRange(exp.start_date, exp.end_date),
                style: 'dates',
                fontSize: fontSize - 1,
                color: mutedColor,
                margin: [0, 0, 0, 4]
            })
        }

        // Description
        if (showDescription && hasVisibleText(exp.description)) {
            expContent.push({
                text: convertMarkdownToPlainText(decodeHtmlEntities(exp.description)),
                fontSize: fontSize,
                color: bodyColor,
                margin: [0, 4, 0, 6],
                lineHeight: 1.4
            })
        }

        // Responsibility categories
        if (showResponsibilities && Array.isArray(exp.responsibility_categories)) {
            exp.responsibility_categories.forEach((category) => {
                if (category.name) {
                    expContent.push({
                        text: decodeHtmlEntities(category.name),
                        fontSize: fontSize,
                        bold: true,
                        color: bodyColor,
                        margin: [0, 4, 0, 2]
                    })
                }

                if (Array.isArray(category.items) && category.items.length > 0) {
                    const bulletItems = category.items
                        .filter((item) => hasVisibleText(item.content))
                        .map((item) => convertMarkdownToPlainText(decodeHtmlEntities(item.content)))

                    if (bulletItems.length > 0) {
                        expContent.push({
                            ul: bulletItems,
                            fontSize: fontSize - 0.5,
                            color: bodyColor,
                            margin: [15, 0, 0, 6]
                        })
                    }
                }
            })
        }

        content.push({
            stack: expContent,
            margin: [0, 0, 0, index < experiences.length - 1 ? 10 * spacing : 0]
        })
    })

    return content
}

/**
 * Build education section
 */
export function buildEducationSection(education, template, options = {}) {
    const {
        showDates = true,
        showDescription = true,
        fontSize = 11
    } = options

    if (!Array.isArray(education) || education.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const mutedColor = getColor(template, 'muted', '#6b7280')
    const accentColor = getColor(template, 'accent', '#2563eb')

    const content = []

    education.forEach((edu, index) => {
        const eduContent = []

        // Degree
        if (edu.degree) {
            eduContent.push({
                text: decodeHtmlEntities(edu.degree),
                fontSize: fontSize + 1,
                bold: true,
                color: bodyColor
            })
        }

        // Institution
        if (edu.institution) {
            eduContent.push({
                text: decodeHtmlEntities(edu.institution),
                fontSize: fontSize,
                color: accentColor,
                margin: [0, 2, 0, 2]
            })
        }

        // Field of study
        if (edu.field_of_study) {
            eduContent.push({
                text: decodeHtmlEntities(edu.field_of_study),
                fontSize: fontSize - 0.5,
                color: mutedColor,
                margin: [0, 0, 0, 2]
            })
        }

        // Dates
        if (showDates && (edu.start_date || edu.end_date)) {
            eduContent.push({
                text: formatDateRange(edu.start_date, edu.end_date),
                fontSize: fontSize - 1,
                color: mutedColor,
                margin: [0, 0, 0, 4]
            })
        }

        // Description
        if (showDescription && hasVisibleText(edu.description)) {
            eduContent.push({
                text: convertMarkdownToPlainText(decodeHtmlEntities(edu.description)),
                fontSize: fontSize - 0.5,
                color: bodyColor,
                margin: [0, 4, 0, 0]
            })
        }

        content.push({
            stack: eduContent,
            margin: [0, 0, 0, index < education.length - 1 ? 8 : 0]
        })
    })

    return content
}

/**
 * Build skills section (list format)
 */
export function buildSkillsListSection(skills, template, options = {}) {
    const {
        fontSize = 11,
        showLevel = true,
        groupByCategory = true
    } = options

    if (!Array.isArray(skills) || skills.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const mutedColor = getColor(template, 'muted', '#6b7280')

    const content = []

    if (groupByCategory) {
        const grouped = groupSkills(skills)

        Object.entries(grouped).forEach(([category, categorySkills]) => {
            // Category name
            content.push({
                text: category,
                fontSize: fontSize,
                bold: true,
                color: bodyColor,
                margin: [0, 4, 0, 3]
            })

            // Skills in this category
            const skillTexts = categorySkills.map((skill) => {
                const name = decodeHtmlEntities(skill.name)
                const level = skill.level ? ` (${skill.level})` : ''
                return showLevel && skill.level ? name + level : name
            })

            content.push({
                text: skillTexts.join(', '),
                fontSize: fontSize - 0.5,
                color: bodyColor,
                margin: [0, 0, 0, 6]
            })
        })
    } else {
        // Ungrouped list
        const skillTexts = skills.map((skill) => {
            const name = decodeHtmlEntities(skill.name)
            const level = skill.level ? ` (${skill.level})` : ''
            return showLevel && skill.level ? name + level : name
        })

        content.push({
            text: skillTexts.join(', '),
            fontSize: fontSize,
            color: bodyColor
        })
    }

    return content
}

/**
 * Build skills section (grid format)
 */
export function buildSkillsGridSection(skills, template, options = {}) {
    const {
        fontSize = 11,
        showLevel = true,
        columns = 2
    } = options

    if (!Array.isArray(skills) || skills.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const content = []

    // Create grid
    const rows = []
    for (let i = 0; i < skills.length; i += columns) {
        const rowSkills = skills.slice(i, i + columns)
        const cols = []

        rowSkills.forEach((skill, idx) => {
            const name = decodeHtmlEntities(skill.name)
            const level = skill.level ? ` (${skill.level})` : ''
            const skillText = showLevel && skill.level ? name + level : name

            if (idx > 0) cols.push({ width: 10, text: '' })

            cols.push({
                width: '*',
                text: skillText,
                fontSize: fontSize,
                color: bodyColor
            })
        })

        rows.push({ columns: cols, margin: [0, 2, 0, 2] })
    }

    return rows
}

/**
 * Build projects section
 */
export function buildProjectsSection(projects, template, options = {}) {
    const {
        showDates = true,
        showUrl = true,
        fontSize = 11
    } = options

    if (!Array.isArray(projects) || projects.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const mutedColor = getColor(template, 'muted', '#6b7280')
    const linkColor = getColor(template, 'link', '#2563eb')

    const content = []

    projects.forEach((project, index) => {
        const projectContent = []

        // Title
        if (project.title) {
            projectContent.push({
                text: decodeHtmlEntities(project.title),
                fontSize: fontSize + 1,
                bold: true,
                color: bodyColor
            })
        }

        // Dates
        if (showDates && (project.start_date || project.end_date)) {
            projectContent.push({
                text: formatDateRange(project.start_date, project.end_date),
                fontSize: fontSize - 1,
                color: mutedColor,
                margin: [0, 2, 0, 4]
            })
        }

        // Description
        if (hasVisibleText(project.description)) {
            projectContent.push({
                text: convertMarkdownToPlainText(decodeHtmlEntities(project.description)),
                fontSize: fontSize,
                color: bodyColor,
                margin: [0, 2, 0, 4]
            })
        }

        // URL
        if (showUrl && project.url) {
            projectContent.push({
                text: decodeHtmlEntities(project.url),
                link: decodeHtmlEntities(project.url),
                fontSize: fontSize - 1,
                color: linkColor,
                decoration: 'underline'
            })
        }

        content.push({
            stack: projectContent,
            margin: [0, 0, 0, index < projects.length - 1 ? 8 : 0]
        })
    })

    return content
}

/**
 * Build certifications section
 */
export function buildCertificationsSection(certifications, template, options = {}) {
    const {
        showDates = true,
        fontSize = 11
    } = options

    if (!Array.isArray(certifications) || certifications.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const mutedColor = getColor(template, 'muted', '#6b7280')
    const accentColor = getColor(template, 'accent', '#2563eb')

    const content = []

    certifications.forEach((cert, index) => {
        const certContent = []

        // Name
        if (cert.name) {
            certContent.push({
                text: decodeHtmlEntities(cert.name),
                fontSize: fontSize + 1,
                bold: true,
                color: bodyColor
            })
        }

        // Issuer
        if (cert.issuer) {
            certContent.push({
                text: decodeHtmlEntities(cert.issuer),
                fontSize: fontSize,
                color: accentColor,
                margin: [0, 2, 0, 2]
            })
        }

        // Dates
        if (showDates) {
            const dateText = cert.date_obtained ? `Issued: ${formatDate(cert.date_obtained)}` : ''
            const expiryText = cert.expiry_date ? `Expires: ${formatDate(cert.expiry_date)}` : ''
            const fullDateText = [dateText, expiryText].filter(Boolean).join(' | ')

            if (fullDateText) {
                certContent.push({
                    text: fullDateText,
                    fontSize: fontSize - 1,
                    color: mutedColor
                })
            }
        }

        content.push({
            stack: certContent,
            margin: [0, 0, 0, index < certifications.length - 1 ? 6 : 0]
        })
    })

    return content
}

/**
 * Build professional memberships section
 */
export function buildMembershipsSection(memberships, template, options = {}) {
    const {
        showDates = true,
        fontSize = 11
    } = options

    if (!Array.isArray(memberships) || memberships.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const mutedColor = getColor(template, 'muted', '#6b7280')

    const content = []

    memberships.forEach((membership, index) => {
        const memberContent = []

        // Organization
        if (membership.organisation) {
            memberContent.push({
                text: decodeHtmlEntities(membership.organisation),
                fontSize: fontSize + 1,
                bold: true,
                color: bodyColor
            })
        }

        // Role
        if (membership.role) {
            memberContent.push({
                text: decodeHtmlEntities(membership.role),
                fontSize: fontSize,
                color: mutedColor,
                margin: [0, 2, 0, 2]
            })
        }

        // Dates
        if (showDates && (membership.start_date || membership.end_date)) {
            memberContent.push({
                text: formatDateRange(membership.start_date, membership.end_date),
                fontSize: fontSize - 1,
                color: mutedColor
            })
        }

        content.push({
            stack: memberContent,
            margin: [0, 0, 0, index < memberships.length - 1 ? 6 : 0]
        })
    })

    return content
}

/**
 * Build interests section
 */
export function buildInterestsSection(interests, template, options = {}) {
    const {
        fontSize = 11,
        showDescription = true,
        layout = 'list' // 'list' or 'inline'
    } = options

    if (!Array.isArray(interests) || interests.length === 0) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')

    if (layout === 'inline') {
        // Comma-separated list
        const interestNames = interests
            .filter((i) => i.name)
            .map((i) => decodeHtmlEntities(i.name))
            .join(', ')

        return [{
            text: interestNames,
            fontSize: fontSize,
            color: bodyColor
        }]
    }

    // List format
    const content = []

    interests.forEach((interest, index) => {
        const interestContent = []

        if (interest.name) {
            interestContent.push({
                text: decodeHtmlEntities(interest.name),
                fontSize: fontSize,
                bold: !showDescription,
                color: bodyColor
            })
        }

        if (showDescription && hasVisibleText(interest.description)) {
            interestContent.push({
                text: convertMarkdownToPlainText(decodeHtmlEntities(interest.description)),
                fontSize: fontSize - 0.5,
                color: bodyColor,
                margin: [0, 2, 0, 0]
            })
        }

        content.push({
            stack: interestContent,
            margin: [0, 0, 0, index < interests.length - 1 ? 4 : 0]
        })
    })

    return content
}

/**
 * Build professional summary section
 */
export function buildProfessionalSummarySection(summary, template, options = {}) {
    const {
        fontSize = 11,
        showStrengths = true
    } = options

    if (!summary) {
        return []
    }

    const bodyColor = getColor(template, 'body', '#374151')
    const content = []

    // Description
    if (hasVisibleText(summary.description)) {
        content.push({
            text: convertMarkdownToPlainText(decodeHtmlEntities(summary.description)),
            fontSize: fontSize,
            color: bodyColor,
            lineHeight: 1.5,
            margin: [0, 0, 0, showStrengths && Array.isArray(summary.strengths) ? 8 : 0]
        })
    }

    // Strengths
    if (showStrengths && Array.isArray(summary.strengths) && summary.strengths.length > 0) {
        const strengthTexts = summary.strengths
            .filter((s) => hasVisibleText(s.strength))
            .map((s) => convertMarkdownToPlainText(decodeHtmlEntities(s.strength)))

        if (strengthTexts.length > 0) {
            content.push({
                ul: strengthTexts,
                fontSize: fontSize - 0.5,
                color: bodyColor,
                margin: [15, 0, 0, 0]
            })
        }
    }

    return content
}

/**
 * Helper to format date (imported from utils but wrapped for convenience)
 */
function formatDate(dateStr) {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    if (Number.isNaN(date.getTime())) return dateStr
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${month}/${year}`
}
