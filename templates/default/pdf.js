const DEFAULT_TEMPLATE_ID = 'professional'

const ACCENT_COLOR = '#3498db'

const pdfTemplates = {
    professional: {
        id: 'professional',
        name: 'Professional Blue',
        description: 'Structured business layout with blue accent lines and refined typography.',
        pageMargins: [50, 50, 50, 50],
        colors: {
            header: '#2c3e50',
            body: '#374151',
            accent: ACCENT_COLOR,
            muted: '#6b7280',
            divider: ACCENT_COLOR,
            link: ACCENT_COLOR,
            tagline: '#1d4ed8'
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
    const lineColor = template.sectionHeaderLineColor || palette.divider || ACCENT_COLOR

    // Use a simple text block with underline instead of a table; tables with custom
    // layout inside column stacks caused overlapping/merged headings in pdfmake.
    return {
        text: title,
        style: 'subheader',
        margin: variant === 'minimal' ? [0, 8, 0, 6] : [0, 6, 0, 8],
        decoration: 'underline',
        decorationColor: lineColor
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
    const includeQRCode = config.includeQRCode === true
    const isValidDataUrl = (s) => typeof s === 'string' && s.length > 100 && /^data:image\//i.test(s)

    // Profile photo in header top-right when included and available
    if (includePhoto && profile.photo_base64 && isValidDataUrl(profile.photo_base64)) {
        headerImages.push({
            width: 'auto',
            image: profile.photo_base64,
            fit: [70, 70],
            alignment: 'right',
            margin: [0, 0, 5, 0]
        })
    }

    // When no photo in header: put QR code in that same top-right slot when requested
    let qrInHeader = false
    if (headerImages.length === 0 && includeQRCode && cvUrl && qrCodeImage && isValidDataUrl(qrCodeImage)) {
        headerImages.push({
            width: 'auto',
            image: qrCodeImage,
            fit: [50, 50],
            alignment: 'right',
            margin: [0, 0, 5, 0]
        })
        qrInHeader = true
    }

    if (headerImages.length) {
        headerColumns.push(...headerImages)
    }

    // When there is no image, give the text column an explicit width so it uses the full
    // content area (pdfmake '*' can fail to expand and cause "squashed to the left").
    const pageMarginsForHeader = template.pageMargins || [50, 50, 50, 50]
    const availableWidthForHeader = 595.28 - (pageMarginsForHeader[0] || 0) - (pageMarginsForHeader[2] || 0)
    if (headerImages.length === 0 && headerColumns[0]) {
        headerColumns[0].width = availableWidthForHeader
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

    const pageMargins = template.pageMargins || [50, 50, 50, 50]
    const pageWidth = 595.28
    const availableWidth = pageWidth - (pageMargins[0] || 0) - (pageMargins[2] || 0)
    const dividerColor = palette.divider || ACCENT_COLOR
    const headerLineWidth = template.sectionHeaderLineWidth ?? 2

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
            hLineWidth: (i, node) => (i === node.table.body.length ? headerLineWidth : 0),
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

    // Split on double newlines (paragraphs); turn single newlines into spaces
    // so we avoid "one word per line" when the source has newlines between words.
    const paragraphs = plain
        .split(/\n\s*\n/)
        .map((p) => p.replace(/\r?\n/g, ' ').trim())
        .filter(Boolean)
    paragraphs.forEach((paragraph, index) => {
        content.push({
            text: decodeHtmlEntities(paragraph),
            style: 'paragraph',
            margin: [0, index === 0 ? 2 : 2, 0, index === paragraphs.length - 1 ? 4 : 1],
            color: palette.body || '#374151'
        })
    })
}

function buildProfessionalDocDefinition({ cvData = {}, profile = {}, config = {}, cvUrl, qrCodeImage, templateId = DEFAULT_TEMPLATE_ID }) {
    const template = getTemplate(templateId)
    const palette = template.colors || {}

    const styles = {
        header: { fontSize: 22, bold: true, color: palette.header || '#2c3e50', margin: [0, 0, 0, 4] },
        tagline: { fontSize: 13, bold: true, color: palette.tagline || palette.accent || ACCENT_COLOR, margin: [0, 0, 0, 6] },
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
        pageMargins: template.pageMargins || [50, 50, 50, 50],
        defaultStyle: { font: 'Roboto', fontSize: 11, color: palette.body || '#374151', lineHeight: 1.3 },
        footer: (currentPage, pageCount) => {
            const p = (typeof pageCount === 'number' && !Number.isNaN(pageCount)) ? pageCount : 1
            const c = (typeof currentPage === 'number' && !Number.isNaN(currentPage)) ? currentPage : 1
            return { text: `${c} / ${p}`, alignment: 'center', style: 'footer' }
        },
        content,
        styles,
        pageBreakBefore: function (currentNode, followingNodesOnPage, nodesOnNextPage, previousNodesOnPage) {
            // Don't force page breaks before section headers if they have content following
            if (currentNode.style === 'subheader' && followingNodesOnPage.length > 2) {
                return false;
            }
            return false;
        }
    }

    const sections = config.sections || {}

    const qrInHeader = buildHeader(content, profile, config, palette, template, cvUrl, qrCodeImage)

    const isMinimal = templateId === 'minimal'
    // Section blocks: for minimal we merge into one column in order; for professional we split left/right
    const summaryBlocks = []
    const workBlocks = []
    const educationBlocks = []
    const certBlocks = []
    const skillsBlocks = []
    const projectsBlocks = []
    const qualBlocks = []
    const membershipsBlocks = []
    const interestsBlocks = []

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
            summaryBlocks.push(createSectionHeader('Professional Summary', template))

            if (hasSummaryText) {
                pushParagraphs(summaryBlocks, summary.description, palette)
            }

            if (hasStrengths) {
                summaryBlocks.push({ text: strengths.map((s) => '• ' + s).join('\n'), style: 'bulletList' })
            }
        }
    }

    const experiences = Array.isArray(cvData.work_experience) ? cvData.work_experience : []
    if ((sections.workExperience ?? sections.work ?? true) && experiences.length) {
        workBlocks.push(createSectionHeader('Work Experience', template))

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

                workBlocks.push({ columns })
            }

            if (role.company_name) {
                workBlocks.push({ text: decodeHtmlEntities(role.company_name), style: 'company' })
            }

            pushParagraphs(workBlocks, role.description, palette)

            if (Array.isArray(role.responsibility_categories)) {
                role.responsibility_categories.forEach((category) => {
                    if (!category || !category.items || !category.items.length) {
                        return
                    }

                    const items = category.items
                        .map((item) => decodeHtmlEntities(item.content || item))
                        .filter(Boolean)
                    if (!items.length) return

                    const name = decodeHtmlEntities(category.name || '')
                    const itemsJoined = items.join(' ')

                    if (name) {
                        workBlocks.push({
                            text: [
                                { text: name + ': ', bold: true },
                                { text: itemsJoined }
                            ],
                            style: 'paragraph',
                            margin: [0, 2, 0, 5]
                        })
                    } else {
                        workBlocks.push({ text: itemsJoined, style: 'paragraph', margin: [0, 2, 0, 5] })
                    }
                })
            }

            workBlocks.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const projects = Array.isArray(cvData.projects) ? cvData.projects : []
    if ((sections.projects ?? true) && projects.length) {
        projectsBlocks.push(createSectionHeader('Projects', template))

        projects.forEach((project) => {
            if (project.title) {
                const columns = [
                    { width: '*', text: decodeHtmlEntities(project.title), style: 'jobPosition' }
                ]

                const range = formatDateRange(project.start_date, project.end_date)
                if (range) {
                    columns.push({ width: 'auto', text: range, style: 'dates' })
                }

                projectsBlocks.push({ columns })
            }

            pushParagraphs(projectsBlocks, project.description, palette)

            if (project.url) {
                projectsBlocks.push({ text: decodeHtmlEntities(project.url), style: 'link', link: project.url, margin: [0, 0, 0, 4] })
            }

            projectsBlocks.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const qualifications = Array.isArray(cvData.qualification_equivalence) ? cvData.qualification_equivalence : []
    if ((sections.qualificationEquivalence ?? false) && qualifications.length) {
        qualBlocks.push(createSectionHeader('Professional Qualification Equivalence', template))

        qualifications.forEach((qual) => {
            if (qual.level) {
                qualBlocks.push({ text: decodeHtmlEntities(qual.level), style: 'jobPosition' })
            }

            if (qual.description) {
                pushParagraphs(qualBlocks, qual.description, palette)
            }

            if (Array.isArray(qual.evidence) && qual.evidence.length) {
                const evidenceItems = qual.evidence
                    .map((item) => decodeHtmlEntities(item.content || item))
                    .filter(Boolean)

                if (evidenceItems.length) {
                    qualBlocks.push({ text: evidenceItems.map((i) => '• ' + i).join('\n'), style: 'bulletList' })
                }
            }

            qualBlocks.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const certifications = Array.isArray(cvData.certifications) ? cvData.certifications : []
    if ((sections.certifications ?? true) && certifications.length) {
        certBlocks.push(createSectionHeader('Certifications', template))

        certifications.forEach((cert) => {
            if (cert.name) {
                const cols = [
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
                    cols.push({ width: 'auto', text: details.join(' · '), style: 'dates' })
                }

                certBlocks.push({ columns: cols, margin: [0, 0, 0, 2] })
            }

            if (cert.issuer) {
                certBlocks.push({
                    text: decodeHtmlEntities(cert.issuer),
                    style: 'company',
                    margin: [0, 2, 0, 3]
                })
            }

            pushParagraphs(certBlocks, cert.description, palette)

            if (cert.url) {
                certBlocks.push({ text: decodeHtmlEntities(cert.url), style: 'link', link: cert.url, margin: [0, 0, 0, 6] })
            }

            certBlocks.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const education = Array.isArray(cvData.education) ? cvData.education : []
    if ((sections.education ?? true) && education.length) {
        educationBlocks.push(createSectionHeader('Education', template))

        education.forEach((entry) => {
            const columns = []

            if (entry.degree || entry.course || entry.institution) {
                const degreeText = entry.degree || entry.course
                const stack = []
                if (degreeText) {
                    stack.push({ text: 'Qual: ' + decodeHtmlEntities(degreeText), style: 'jobPosition' })
                }
                if (entry.institution) {
                    stack.push({ text: 'Institution: ' + decodeHtmlEntities(entry.institution), style: 'company' })
                }
                if (entry.field_of_study) {
                    stack.push({ text: 'Subject: ' + decodeHtmlEntities(entry.field_of_study), style: 'paragraph', margin: [0, 2, 0, 2] })
                }

                columns.push({ width: '*', stack })
            }

            if (!entry.hide_date) {
                const range = formatDateRange(entry.start_date, entry.end_date)
                if (range) {
                    columns.push({ width: 'auto', text: range, style: 'dates' })
                }
            }

            if (columns.length) {
                educationBlocks.push({ columns })
            }

            pushParagraphs(educationBlocks, entry.description, palette)
            educationBlocks.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const skills = Array.isArray(cvData.skills) ? cvData.skills : []
    if ((sections.skills ?? true) && skills.length) {
        skillsBlocks.push(createSectionHeader('Skills', template))

        const grouped = groupSkills(skills)
        grouped.forEach((skillItems, category) => {
            if (category && (category !== 'Other' || grouped.size > 1)) {
                skillsBlocks.push({ text: decodeHtmlEntities(category), style: 'skillsCategory' })
            }

            const text = skillItems
                .map((skill) => {
                    const name = decodeHtmlEntities(skill.name)
                    return skill.level ? `${name} (${decodeHtmlEntities(skill.level)})` : name
                })
                .filter(Boolean)
                .join(', ')

            if (text) {
                skillsBlocks.push({ text, style: 'skillsList' })
            }
        })
    }

    const memberships = Array.isArray(cvData.memberships) ? cvData.memberships : []
    if ((sections.memberships ?? true) && memberships.length) {
        membershipsBlocks.push(createSectionHeader('Professional Memberships', template))

        memberships.forEach((membership) => {
            if (membership.organisation) {
                membershipsBlocks.push({ text: decodeHtmlEntities(membership.organisation), style: 'jobPosition' })
            }

            if (membership.role) {
                membershipsBlocks.push({ text: decodeHtmlEntities(membership.role), style: 'company' })
            }

            const range = formatDateRange(membership.start_date, membership.end_date)
            if (range) {
                membershipsBlocks.push({ text: range, style: 'dates' })
            }

            pushParagraphs(membershipsBlocks, membership.description, palette)
            membershipsBlocks.push({ text: '', margin: [0, 0, 0, 6] })
        })
    }

    const interests = Array.isArray(cvData.interests) ? cvData.interests : []
    if ((sections.interests ?? true) && interests.length) {
        interestsBlocks.push(createSectionHeader('Interests & Activities', template))

        interests.forEach((interest) => {
            const name = decodeHtmlEntities(interest.name || '')
            const desc = interest.description ? ` — ${decodeHtmlEntities(interest.description)}` : ''
            if (!name && !desc.trim()) return
            interestsBlocks.push({
                text: [
                    { text: name, bold: true },
                    { text: desc }
                ],
                style: 'paragraph',
                margin: [0, 0, 0, 5]
            })
        })
    }

    // Minimal = one column; Professional Blue = two columns
    if (isMinimal) {
        const mainCol = [].concat(
            summaryBlocks, workBlocks, educationBlocks, certBlocks, skillsBlocks,
            projectsBlocks, qualBlocks, membershipsBlocks, interestsBlocks
        )
        content.push({ stack: mainCol })
    } else {
        const leftCol = [].concat(certBlocks, educationBlocks, skillsBlocks, interestsBlocks)
        const rightCol = [].concat(summaryBlocks, workBlocks, projectsBlocks, qualBlocks, membershipsBlocks)

        // Explicit pt widths so the two-column body uses the full content area.
        // pdfmake's '*'/'2*' can fail to expand in some builds, causing "squashed to the left".
        const pageMargins = template.pageMargins || [50, 50, 50, 50]
        const pageWidth = 595.28 // A4
        const bodyWidth = pageWidth - (pageMargins[0] || 0) - (pageMargins[2] || 0)
        const columnGap = 10
        const leftWidth = Math.round((bodyWidth - columnGap) / 3)
        const rightWidth = Math.round((bodyWidth - columnGap) * 2 / 3)

        content.push({
            columns: [
                { width: leftWidth, stack: leftCol },
                { width: rightWidth, stack: rightCol }
            ],
            columnGap
        })
    }

    // When QR requested: use link-only footer (embedding the QR image causes pdfmake "unsupported number: NaN")
    if (config.includeQRCode && cvUrl) {
        content.push({ text: 'View my full CV online', alignment: 'right', fontSize: 9, color: palette.muted || '#6b7280', margin: [0, 18, 0, 0], link: cvUrl })
    }

    return docDefinition
}

export function buildDocDefinition(options = {}) {
    return buildProfessionalDocDefinition(options)
}

export { convertMarkdownToPlainText, formatDate }
