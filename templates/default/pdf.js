const DEFAULT_TEMPLATE_ID = 'professional'

const ACCENT_COLOR = '#3498db'

const pdfTemplates = {
    professional: {
        id: 'professional',
        name: 'Professional Blue',
        description: 'Structured business layout with blue accent lines and refined typography.',
        pageMargins: [30, 40, 30, 40],
        colors: {
            header: '#2c3e50',
            body: '#374151',
            accent: ACCENT_COLOR,
            muted: '#6b7280',
            divider: ACCENT_COLOR,
            link: ACCENT_COLOR
        },
        sectionHeaderStyle: 'line',
        sectionHeaderLineWidth: 2
    },
    minimal: {
        id: 'minimal',
        name: 'Minimal',
        description: 'Clean monochrome layout with subtle dividers and generous spacing.',
        pageMargins: [35, 40, 35, 40],
        colors: {
            header: '#111827',
            body: '#374151',
            accent: '#111827',
            muted: '#6b7280',
            divider: '#d1d5db',
            link: '#1f2937'
        },
        sectionHeaderStyle: 'minimal',
        sectionHeaderLineWidth: 0.75
    }
}

function getTemplate(templateId) {
    return pdfTemplates[templateId] || pdfTemplates[DEFAULT_TEMPLATE_ID]
}

function decodeHtmlEntities(value) {
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

function convertMarkdownToPlainText(text) {
    if (!text) return ''
    return String(text)
        .replace(/\*\*(.*?)\*\*/g, '$1')
        .replace(/\*(.*?)\*/g, '$1')
        .replace(/^•\s+/gm, '• ')
        .replace(/^\-\s+/gm, '- ')
        .replace(/`/g, '')
        .replace(/\r?\n/g, '\n')
        .trim()
}

function hasVisibleText(value) {
    if (!value) return false
    const plain = convertMarkdownToPlainText(value)
    return Boolean(plain && plain.trim())
}

function formatDate(dateStr) {
    if (!dateStr) return ''

    const date = new Date(dateStr)
    if (Number.isNaN(date.getTime())) {
        return dateStr
    }

    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${month}/${year}`
}

function formatDateRange(startDate, endDate) {
    const start = formatDate(startDate)
    const end = endDate ? formatDate(endDate) : 'Present'

    if (start && end) {
        return `${start} - ${end}`
    }
    if (start) {
        return start
    }
    if (end && !endDate) {
        return end
    }
    return ''
}

function createSectionHeader(title, template = {}) {
    const palette = template.colors || {}
    const variant = template.sectionHeaderStyle || 'line'
    const pageMargins = Array.isArray(template.pageMargins) ? template.pageMargins : [40, 60, 40, 60]
    const pageWidth = 595.28 // A4 width in points
    const availableWidth = pageWidth - (pageMargins[0] || 0) - (pageMargins[2] || 0)
    const lineWidth = template.sectionHeaderLineWidth ?? (variant === 'minimal' ? 0.75 : 2)
    const lineColor = template.sectionHeaderLineColor || palette.divider || ACCENT_COLOR

    return {
        margin: variant === 'minimal' ? [0, 8, 0, 6] : [0, 6, 0, 8],
        table: {
            widths: ['*'],
            body: [
                [
                    {
                        text: title,
                        style: 'subheader',
                        margin: [0, 0, 0, variant === 'minimal' ? 4 : 4],
                        border: [false, false, false, false]
                    }
                ]
            ]
        },
        layout: {
            defaultBorder: false,
            hLineWidth: (i, node) => {
                if (variant === 'line' || variant === 'minimal') {
                    return i === node.table.body.length ? lineWidth : 0
                }
                return 0
            },
            hLineColor: () => lineColor,
            vLineWidth: () => 0,
            paddingLeft: () => 0,
            paddingRight: () => 0,
            paddingTop: () => 0,
            paddingBottom: () => (variant === 'minimal' ? 4 : 6)
        },
        width: availableWidth
    }
}

function groupSkills(skills) {
    const grouped = new Map()

    skills.forEach((skill) => {
        const category = skill.category || 'Other'
        if (!grouped.has(category)) {
            grouped.set(category, [])
        }
        grouped.get(category).push(skill)
    })

    return grouped
}

function buildHeader(content, profile, config, palette, template, cvUrl, qrCodeImage) {
    const profileEnabled = config.sections ? config.sections.profile !== false : true
    if (!profileEnabled) {
        return false
    }

    const headerStack = []

    headerStack.push({ text: decodeHtmlEntities(profile.full_name || 'Your Name'), style: 'header' })

    if (profile.bio && String(profile.bio).trim()) {
        headerStack.push({ text: decodeHtmlEntities(profile.bio), style: 'tagline' })
    }

    const contactLines = []
    if (profile.location) {
        contactLines.push(decodeHtmlEntities(profile.location))
    }
    if (profile.email) {
        contactLines.push(decodeHtmlEntities(profile.email))
    }
    if (profile.phone) {
        contactLines.push(decodeHtmlEntities(profile.phone))
    }

    contactLines.forEach((line) => {
        headerStack.push({ text: line, style: 'contactInfo' })
    })

    const headerColumns = [
        {
            width: '*',
            stack: headerStack
        }
    ]

    const headerImages = []
    const includePhoto = config.includePhoto !== false

    if (includePhoto && (profile.photo_base64 || profile.photo_url)) {
        headerImages.push({
            width: 'auto',
            image: profile.photo_base64 || profile.photo_url,
            fit: [70, 70],
            alignment: 'right',
            margin: [0, 0, 5, 0]
        })
    }

    let qrInHeader = false
    if (config.includeQRCode && qrCodeImage) {
        console.log('Adding QR code to header')
        headerImages.push({
            width: 'auto',
            image: qrCodeImage,
            fit: [70, 70],
            alignment: 'right',
            margin: [0, 0, 0, 0],
            link: cvUrl
        })
        qrInHeader = true
    } else {
        console.log('QR code NOT added to header. includeQRCode:', config.includeQRCode, 'qrCodeImage:', !!qrCodeImage)
    }

    if (headerImages.length) {
        headerColumns.push(...headerImages)
    }

    content.push({ columns: headerColumns })

    if (profile.linkedin_url) {
        content.push({
            text: 'LinkedIn Profile',
            style: 'linkedIn',
            link: profile.linkedin_url,
            margin: [0, 10, 0, 0]
        })
    }

    const pageMargins = template.pageMargins || [40, 60, 40, 60]
    const pageWidth = 595.28
    const availableWidth = pageWidth - (pageMargins[0] || 0) - (pageMargins[2] || 0)
    const dividerColor = palette.divider || ACCENT_COLOR

    content.push({
        table: {
            widths: ['*'],
            body: [
                [
                    {
                        text: '',
                        border: [false, false, false, false],
                        margin: [0, 0, 0, 0]
                    }
                ]
            ]
        },
        layout: {
            defaultBorder: false,
            hLineWidth: (i, node) => (i === node.table.body.length ? 2 : 0),
            hLineColor: () => dividerColor,
            vLineWidth: () => 0,
            paddingLeft: () => 0,
            paddingRight: () => 0,
            paddingTop: () => 0,
            paddingBottom: () => 0,
            hLineDistance: () => 0
        },
        width: availableWidth,
        margin: [0, 6, 0, 8]
    })

    return qrInHeader
}

function pushParagraphs(content, value, palette) {
    if (!value) {
        return
    }

    const plain = convertMarkdownToPlainText(value)
    if (!plain) {
        return
    }

    const paragraphs = plain.split('\n').filter((line) => line.trim())
    paragraphs.forEach((paragraph, index) => {
        content.push({
            text: decodeHtmlEntities(paragraph.trim()),
            style: 'paragraph',
            margin: [0, index === 0 ? 2 : 2, 0, index === paragraphs.length - 1 ? 4 : 1],
            color: palette.body || '#374151'
        })
    })
}

function buildProfessionalDocDefinition({ cvData = {}, profile = {}, config = {}, cvUrl, qrCodeImage, templateId = DEFAULT_TEMPLATE_ID }) {
    const template = getTemplate(templateId)
    const palette = template.colors || {}

    console.log('PDF Builder - includeQRCode:', config.includeQRCode, 'qrCodeImage provided:', !!qrCodeImage, 'cvUrl:', cvUrl)

    const styles = {
        header: { fontSize: 22, bold: true, color: palette.header || '#2c3e50', margin: [0, 0, 0, 4] },
        tagline: { fontSize: 12, color: palette.accent || ACCENT_COLOR, margin: [0, 0, 0, 6] },
        contactInfo: { fontSize: 10, color: palette.body || '#374151', margin: [0, 1, 0, 1] },
        linkedIn: { fontSize: 10, color: palette.link || ACCENT_COLOR, decoration: 'underline', margin: [0, 4, 0, 0] },
        subheader: { fontSize: 15, bold: true, color: palette.header || '#2c3e50', margin: [0, 0, 0, 0] },
        sectionDivider: { margin: [0, 0, 0, 0] },
        jobPosition: { fontSize: 13, bold: true, color: palette.header || '#2c3e50', margin: [0, 0, 0, 2] },
        company: { fontSize: 11, italics: true, color: palette.body || '#374151', margin: [0, 2, 0, 3] },
        dates: { fontSize: 10, color: palette.muted || '#6b7280' },
        paragraph: { fontSize: 11, color: palette.body || '#374151', lineHeight: 1.3 },
        bulletList: { fontSize: 11, color: palette.body || '#374151', margin: [0, 1, 0, 4] },
        skillsCategory: { fontSize: 12, bold: true, color: palette.header || '#2c3e50', margin: [0, 4, 0, 2] },
        skillsList: { fontSize: 10.5, color: palette.body || '#374151', margin: [0, 0, 0, 3] },
        footer: { fontSize: 9, color: palette.muted || '#9ca3af', margin: [0, 10, 0, 0] },
        link: { fontSize: 11, color: palette.link || ACCENT_COLOR, decoration: 'underline' }
    }

    const content = []

    const docDefinition = {
        pageSize: 'A4',
        pageMargins: template.pageMargins || [30, 40, 30, 40],
        defaultStyle: { font: 'Roboto', fontSize: 11, color: palette.body || '#374151', lineHeight: 1.3 },
        footer: (currentPage, pageCount) => ({ text: `${currentPage} / ${pageCount}`, alignment: 'center', style: 'footer' }),
        content,
        styles
    }

    const sections = config.sections || {}

    const qrInHeader = buildHeader(content, profile, config, palette, template, cvUrl, qrCodeImage)
    const summary = cvData.professional_summary || cvData.summary
    const summaryEnabled = sections.professionalSummary ?? sections.summary ?? true
    if (summaryEnabled && summary) {
        const hasSummaryText = hasVisibleText(summary.description)
        const strengths = Array.isArray(summary.strengths)
            ? summary.strengths
                .map((item) => decodeHtmlEntities(item.strength || item))
                .filter(Boolean)
            : []
        const hasStrengths = strengths.length > 0

        if (hasSummaryText || hasStrengths) {
            content.push(createSectionHeader('Professional Summary', template))

            if (hasSummaryText) {
                pushParagraphs(content, summary.description, palette)
            }

            if (hasStrengths) {
                content.push({ ul: strengths, style: 'bulletList' })
            }
        }
    }

    const experiences = Array.isArray(cvData.work_experience) ? cvData.work_experience : []
    if ((sections.workExperience ?? sections.work ?? true) && experiences.length) {
        content.push(createSectionHeader('Work Experience', template))

        experiences.forEach((role) => {
            if (role.position) {
                const columns = [
                    { width: '*', text: decodeHtmlEntities(role.position), style: 'jobPosition' }
                ]

                if (!role.hide_date) {
                    const range = formatDateRange(role.start_date, role.end_date)
                    if (range) {
                        columns.push({ width: 'auto', text: range, style: 'dates' })
                    }
                }

                content.push({ columns })
            }

            if (role.company_name) {
                content.push({ text: decodeHtmlEntities(role.company_name), style: 'company' })
            }

            pushParagraphs(content, role.description, palette)

            if (Array.isArray(role.responsibility_categories)) {
                role.responsibility_categories.forEach((category) => {
                    if (!category || !category.items || !category.items.length) {
                        return
                    }

                    if (category.name) {
                        content.push({ text: `${decodeHtmlEntities(category.name)}:`, bold: true, margin: [0, 4, 0, 2] })
                    }

                    const items = category.items
                        .map((item) => decodeHtmlEntities(item.content || item))
                        .filter(Boolean)

                    if (items.length) {
                        content.push({ ul: items, style: 'bulletList' })
                    }
                })
            }

            content.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const qualifications = Array.isArray(cvData.qualification_equivalence) ? cvData.qualification_equivalence : []
    if ((sections.qualificationEquivalence ?? false) && qualifications.length) {
        content.push(createSectionHeader('Professional Qualification Equivalence', template))

        qualifications.forEach((qual) => {
            if (qual.level) {
                content.push({ text: decodeHtmlEntities(qual.level), style: 'jobPosition' })
            }

            if (qual.description) {
                pushParagraphs(content, qual.description, palette)
            }

            if (Array.isArray(qual.evidence) && qual.evidence.length) {
                const evidenceItems = qual.evidence
                    .map((item) => decodeHtmlEntities(item.content || item))
                    .filter(Boolean)

                if (evidenceItems.length) {
                    content.push({ ul: evidenceItems, style: 'bulletList' })
                }
            }

            content.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const projects = Array.isArray(cvData.projects) ? cvData.projects : []
    if ((sections.projects ?? true) && projects.length) {
        content.push(createSectionHeader('Projects', template))

        projects.forEach((project) => {
            if (project.title) {
                const columns = [
                    { width: '*', text: decodeHtmlEntities(project.title), style: 'jobPosition' }
                ]

                const range = formatDateRange(project.start_date, project.end_date)
                if (range) {
                    columns.push({ width: 'auto', text: range, style: 'dates' })
                }

                content.push({ columns })
            }

            pushParagraphs(content, project.description, palette)

            if (project.url) {
                content.push({ text: decodeHtmlEntities(project.url), style: 'link', link: project.url, margin: [0, 0, 0, 4] })
            }

            content.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const skills = Array.isArray(cvData.skills) ? cvData.skills : []
    if ((sections.skills ?? true) && skills.length) {
        content.push(createSectionHeader('Skills', template))

        const grouped = groupSkills(skills)
        grouped.forEach((skillItems, category) => {
            if (category && (category !== 'Other' || grouped.size > 1)) {
                content.push({ text: decodeHtmlEntities(category), style: 'skillsCategory' })
            }

            const text = skillItems
                .map((skill) => {
                    const name = decodeHtmlEntities(skill.name)
                    return skill.level ? `${name} (${decodeHtmlEntities(skill.level)})` : name
                })
                .filter(Boolean)
                .join(', ')

            if (text) {
                content.push({ text, style: 'skillsList' })
            }
        })
    }

    const education = Array.isArray(cvData.education) ? cvData.education : []
    if ((sections.education ?? true) && education.length) {
        content.push(createSectionHeader('Education', template))

        education.forEach((entry) => {
            const columns = []

            if (entry.degree || entry.course || entry.institution) {
                const degreeText = entry.degree || entry.course
                const stack = []
                if (degreeText) {
                    stack.push({ text: decodeHtmlEntities(degreeText), style: 'jobPosition' })
                }
                if (entry.institution) {
                    stack.push({ text: decodeHtmlEntities(entry.institution), style: 'company' })
                }
                if (entry.field_of_study) {
                    stack.push({ text: decodeHtmlEntities(entry.field_of_study), style: 'paragraph', margin: [0, 2, 0, 2] })
                }

                columns.push({ width: '*', stack })
            }

            const range = formatDateRange(entry.start_date, entry.end_date)
            if (range) {
                columns.push({ width: 'auto', text: range, style: 'dates' })
            }

            if (columns.length) {
                content.push({ columns })
            }

            pushParagraphs(content, entry.description, palette)
            content.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const certifications = Array.isArray(cvData.certifications) ? cvData.certifications : []
    if ((sections.certifications ?? true) && certifications.length) {
        content.push(createSectionHeader('Certifications', template))

        certifications.forEach((cert) => {
            if (cert.name) {
                const columns = [
                    { width: '*', text: decodeHtmlEntities(cert.name), style: 'jobPosition' }
                ]

                const issued = formatDate(cert.date_obtained || cert.date_issued)
                const expires = formatDate(cert.expiry_date)
                const details = []
                if (issued) {
                    details.push(`Issued ${issued}`)
                }
                if (expires) {
                    details.push(`Expires ${expires}`)
                }

                if (details.length) {
                    columns.push({ width: 'auto', text: details.join(' · '), style: 'dates' })
                }

                content.push({ columns })
            }

            if (cert.issuer) {
                content.push({ text: decodeHtmlEntities(cert.issuer), style: 'company' })
            }

            pushParagraphs(content, cert.description, palette)

            if (cert.url) {
                content.push({ text: decodeHtmlEntities(cert.url), style: 'link', link: cert.url, margin: [0, 0, 0, 6] })
            }

            content.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const memberships = Array.isArray(cvData.memberships) ? cvData.memberships : []
    if ((sections.memberships ?? true) && memberships.length) {
        content.push(createSectionHeader('Professional Memberships', template))

        memberships.forEach((membership) => {
            if (membership.organisation) {
                content.push({ text: decodeHtmlEntities(membership.organisation), style: 'jobPosition' })
            }

            if (membership.role) {
                content.push({ text: decodeHtmlEntities(membership.role), style: 'company' })
            }

            const range = formatDateRange(membership.start_date, membership.end_date)
            if (range) {
                content.push({ text: range, style: 'dates' })
            }

            pushParagraphs(content, membership.description, palette)
            content.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const interests = Array.isArray(cvData.interests) ? cvData.interests : []
    if ((sections.interests ?? true) && interests.length) {
        content.push(createSectionHeader('Interests & Activities', template))

        const interestItems = interests
            .map((interest) => {
                const name = decodeHtmlEntities(interest.name || '')
                const description = interest.description ? ` — ${decodeHtmlEntities(interest.description)}` : ''
                return name ? `${name}${description}` : null
            })
            .filter(Boolean)

        if (interestItems.length) {
            content.push({ ul: interestItems, style: 'bulletList' })
        }
    }

    if (config.includeQRCode && qrCodeImage && !qrInHeader) {
        console.log('Adding QR code to footer')
        content.push({
            columns: [
                { width: '*', text: '' },
                { width: 100, image: qrCodeImage, alignment: 'right', fit: [80, 80], link: cvUrl, margin: [0, 18, 0, 0] }
            ]
        })
        content.push({ text: 'View my full CV online', alignment: 'right', fontSize: 9, color: palette.muted || '#6b7280', margin: [0, 6, 0, 0] })
    } else {
        console.log('QR code NOT added to footer. includeQRCode:', config.includeQRCode, 'qrCodeImage:', !!qrCodeImage, 'qrInHeader:', qrInHeader)
    }

    return docDefinition
}

export function buildDocDefinition(options = {}) {
    return buildProfessionalDocDefinition(options)
}

export { convertMarkdownToPlainText, formatDate }
