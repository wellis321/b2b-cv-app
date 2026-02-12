/**
 * Structured Template PDF Builder
 * Clean professional layout: centered header, light blue accents,
 * Skills grouped by category (3-col grid per category), Career Highlights,
 * Professional Experience with shaded job headers
 */

import {
    buildWorkExperienceSection,
    buildEducationSection,
    buildSkillsListSection,
    buildCertificationsSection,
    buildMembershipsSection,
    buildInterestsSection,
    buildProfessionalSummarySection,
    buildProjectsSection
} from '../builders/section-builders.js'
import { groupSkills } from '../builders/utils.js'

import {
    buildDocumentConfig,
    getPageMargins
} from '../builders/style-presets.js'

import {
    decodeHtmlEntities,
    hasVisibleText,
    createDivider,
    formatDateRange,
    getColor
} from '../builders/utils.js'

/**
 * Create structured section header (left-aligned, bold uppercase, light blue underline)
 */
function createStructuredHeader(title, template) {
    const accentColor = getColor(template, 'accent', '#0ea5e9')
    const headerColor = getColor(template, 'header', '#1e3a8a')

    return [
        {
            text: title.toUpperCase(),
            fontSize: 13,
            bold: true,
            color: headerColor,
            margin: [0, 4, 0, 4]
        },
        createDivider(accentColor, 2, [0, 0, 0, 8])
    ]
}

/**
 * Build structured template PDF document definition
 */
export function buildDocDefinition({ cvData, profile, config, cvUrl, qrCodeImage, templateId }) {
    const template = {
        id: 'structured',
        colors: {
            header: '#1e3a8a',
            body: '#374151',
            accent: '#0ea5e9',
            muted: '#64748b',
            divider: '#7dd3fc',
            link: '#0284c7',
            skillBg: '#e0f2fe'
        }
    }

    const sections = config?.sections || {}
    const includeQRCode = config?.includeQRCode !== false

    const docConfig = buildDocumentConfig(template, 'conservative', config?.customization || {})

    const content = []

    // HEADER SECTION (Centered)
    if (sections.profile !== false) {
        const headerContent = []

        if (profile?.full_name) {
            headerContent.push({
                text: decodeHtmlEntities(profile.full_name).toUpperCase(),
                fontSize: 22,
                bold: true,
                color: template.colors.header,
                alignment: 'center',
                margin: [0, 0, 0, 6]
            })
        }

        if (hasVisibleText(profile?.bio)) {
            const bioShort = profile.bio.length > 100 ? profile.bio.substring(0, 100) + '...' : profile.bio
            headerContent.push({
                text: decodeHtmlEntities(bioShort),
                fontSize: 10,
                color: template.colors.muted,
                alignment: 'center',
                margin: [0, 0, 0, 4]
            })
        }

        const contactParts = []
        if (profile?.location) contactParts.push(decodeHtmlEntities(profile.location))
        if (profile?.email) contactParts.push(decodeHtmlEntities(profile.email))
        if (profile?.phone) contactParts.push(decodeHtmlEntities(profile.phone))
        if (profile?.linkedin_url) contactParts.push(decodeHtmlEntities(profile.linkedin_url))

        if (contactParts.length > 0) {
            headerContent.push({
                text: contactParts.join(' | '),
                fontSize: 10,
                color: template.colors.muted,
                alignment: 'center',
                margin: [0, 0, 0, 12]
            })
        }

        headerContent.push(createDivider(template.colors.divider, 1, [0, 0, 0, 16]))

        // QR in header when chosen (replaces footer placement)
        if (includeQRCode && cvUrl) {
            content.push({
                columns: [
                    { width: '*', stack: headerContent },
                    {
                        width: 80,
                        stack: [
                            { qr: cvUrl, fit: 70, alignment: 'right' },
                            { text: 'View Online', link: cvUrl, fontSize: 8, color: template.colors.link, alignment: 'center', margin: [0, 4, 0, 0] }
                        ]
                    }
                ],
                columnGap: 16
            })
        } else {
            content.push(...headerContent)
        }
    }

    // PROFESSIONAL SUMMARY
    if (sections.professionalSummary !== false && cvData.professional_summary) {
        content.push(...createStructuredHeader('Professional Summary', template))
        content.push(...buildProfessionalSummarySection(
            cvData.professional_summary,
            template,
            { fontSize: 11, showStrengths: false }
        ))
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // AREAS OF EXPERTISE (category names only, no individual skills)
    if (sections.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        const grouped = groupSkills(cvData.skills)
        const categories = Object.keys(grouped)
        if (categories.length > 0) {
            content.push(...createStructuredHeader('Areas of Expertise', template))
            const colWidth = 170
            const rows = []
            for (let i = 0; i < categories.length; i += 3) {
                const rowCats = categories.slice(i, i + 3)
                const cols = rowCats.map(cat => ({
                    text: decodeHtmlEntities(cat),
                    fontSize: 10,
                    color: template.colors.body,
                    fillColor: template.colors.skillBg,
                    margin: [6, 4, 6, 4]
                }))
                while (cols.length < 3) cols.push({ text: '', fillColor: template.colors.skillBg, margin: [6, 4, 6, 4] })
                rows.push(cols)
            }
            content.push({
                table: {
                    widths: [colWidth, colWidth, colWidth],
                    body: rows
                },
                layout: { hLineWidth: () => 0, vLineWidth: () => 0 },
                margin: [0, 0, 0, 16]
            })
        }
    }

    // CAREER HIGHLIGHTS (from professional summary strengths)
    if (sections.professionalSummary !== false && Array.isArray(cvData.professional_summary?.strengths) && cvData.professional_summary.strengths.length > 0) {
        content.push(...createStructuredHeader('Career Highlights', template))

        cvData.professional_summary.strengths.slice(0, 5).forEach(s => {
            if (hasVisibleText(s.strength)) {
                content.push({
                    text: decodeHtmlEntities(s.strength),
                    fontSize: 11,
                    bold: true,
                    color: template.colors.body,
                    margin: [0, 0, 0, 4]
                })
            }
        })
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // PROFESSIONAL EXPERIENCE (job headers with light blue background)
    if (sections.workExperience !== false && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        content.push(...createStructuredHeader('Professional Experience', template))

        cvData.work_experience.forEach((exp, index) => {
            const headerStack = []
            if (exp.position) {
                headerStack.push({
                    text: decodeHtmlEntities(exp.position),
                    fontSize: 12,
                    bold: true,
                    color: template.colors.body,
                    margin: [0, 0, 0, 2]
                })
            }
            const orgLine = []
            if (exp.company_name) orgLine.push(decodeHtmlEntities(exp.company_name))
            const dateRange = formatDateRange(exp.start_date, exp.end_date)
            if (dateRange) orgLine.push(dateRange)
            if (orgLine.length > 0) {
                headerStack.push({
                    text: orgLine.join(' | '),
                    fontSize: 11,
                    bold: true,
                    color: template.colors.body,
                    margin: [0, 0, 0, 0]
                })
            }

            content.push({
                table: {
                    widths: ['*'],
                    body: [[{
                        stack: headerStack,
                        fillColor: template.colors.skillBg,
                        margin: [10, 6, 10, 6]
                    }]]
                },
                layout: { hLineWidth: () => 0, vLineWidth: () => 0 },
                margin: [0, 0, 0, 4]
            })

            if (hasVisibleText(exp.description)) {
                content.push({
                    text: decodeHtmlEntities(exp.description),
                    fontSize: 10,
                    color: template.colors.muted,
                    margin: [0, 2, 0, 6]
                })
            }

            if (Array.isArray(exp.responsibility_categories)) {
                exp.responsibility_categories.forEach(cat => {
                    if (Array.isArray(cat.items) && cat.items.length > 0) {
                        const bulletItems = cat.items
                            .filter(item => hasVisibleText(item.content))
                            .map(item => decodeHtmlEntities(item.content))
                        if (bulletItems.length > 0) {
                            content.push({
                                ul: bulletItems,
                                fontSize: 10,
                                color: template.colors.body,
                                margin: [15, 0, 0, 8]
                            })
                        }
                    }
                })
            }

            if (index < cvData.work_experience.length - 1) {
                content.push({ text: '', margin: [0, 0, 0, 8] })
            }
        })

        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // EDUCATION
    if (sections.education !== false && Array.isArray(cvData.education) && cvData.education.length > 0) {
        content.push(...createStructuredHeader('Education', template))

        cvData.education.forEach(edu => {
            const parts = []
            if (edu.degree) parts.push(decodeHtmlEntities(edu.degree))
            if (edu.institution) parts.push(decodeHtmlEntities(edu.institution))
            if (profile?.location) parts.push(decodeHtmlEntities(profile.location))
            if (edu.end_date) parts.push(new Date(edu.end_date).getFullYear().toString())

            content.push({
                text: parts.join(' | '),
                fontSize: 11,
                bold: true,
                color: template.colors.body,
                margin: [0, 0, 0, 4]
            })
        })
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // ADDITIONAL TRAINING & CERTIFICATES
    if (sections.certifications !== false && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        content.push(...createStructuredHeader('Additional Training & Certificates', template))

        cvData.certifications.forEach(cert => {
            const parts = []
            if (cert.name) parts.push(decodeHtmlEntities(cert.name))
            if (cert.issuer) parts.push(decodeHtmlEntities(cert.issuer))
            if (cert.date_obtained) parts.push(new Date(cert.date_obtained).getFullYear().toString())

            content.push({
                text: parts.join(' | '),
                fontSize: 11,
                bold: true,
                color: template.colors.body,
                margin: [0, 0, 0, 4]
            })
        })
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // SKILLS (grouped by category, below certificates)
    if (sections.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        content.push(...createStructuredHeader('Skills', template))
        content.push(...buildSkillsListSection(
            cvData.skills,
            template,
            { fontSize: 11, showLevel: true, groupByCategory: true }
        ))
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // PROJECTS
    if (sections.projects !== false && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        content.push(...createStructuredHeader('Projects', template))
        content.push(...buildProjectsSection(
            cvData.projects,
            template,
            { showDates: true, showUrl: true, fontSize: 11 }
        ))
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // PROFESSIONAL QUALIFICATION EQUIVALENCE
    if (sections.qualificationEquivalence !== false && Array.isArray(cvData.qualification_equivalence) && cvData.qualification_equivalence.length > 0) {
        content.push(...createStructuredHeader('Professional Qualification Equivalence', template))

        cvData.qualification_equivalence.forEach((qual, index) => {
            const qualContent = []
            if (qual.level) {
                qualContent.push({
                    text: decodeHtmlEntities(qual.level),
                    fontSize: 12,
                    bold: true,
                    color: template.colors.body,
                    margin: [0, 0, 0, 2]
                })
            }
            if (hasVisibleText(qual.description)) {
                qualContent.push({
                    text: decodeHtmlEntities(qual.description),
                    fontSize: 11,
                    color: template.colors.body,
                    margin: [0, 2, 0, 4]
                })
            }
            if (Array.isArray(qual.evidence) && qual.evidence.length > 0) {
                const evidenceItems = qual.evidence
                    .map(e => e.content || e.evidence)
                    .filter(Boolean)
                    .map(e => decodeHtmlEntities(e))
                if (evidenceItems.length > 0) {
                    qualContent.push({
                        ul: evidenceItems,
                        fontSize: 10,
                        color: template.colors.body,
                        margin: [15, 0, 0, 0]
                    })
                }
            }
            content.push({
                stack: qualContent,
                margin: [0, 0, 0, index < cvData.qualification_equivalence.length - 1 ? 8 : 0]
            })
        })
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // PROFESSIONAL MEMBERSHIPS
    const memberships = cvData.memberships || cvData.professional_memberships || []
    if (sections.memberships !== false && Array.isArray(memberships) && memberships.length > 0) {
        content.push(...createStructuredHeader('Professional Memberships', template))
        content.push(...buildMembershipsSection(
            memberships,
            template,
            { showDates: true, fontSize: 11 }
        ))
        content.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // INTERESTS & ACTIVITIES (3-column grid with name and description)
    if (sections.interests !== false && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        content.push(...createStructuredHeader('Interests & Activities', template))
        const colWidth = 170
        const rows = []
        for (let i = 0; i < cvData.interests.length; i += 3) {
            const rowInterests = cvData.interests.slice(i, i + 3)
            const cols = rowInterests.map((interest) => {
                const stack = []
                if (interest.name) {
                    stack.push({
                        text: decodeHtmlEntities(interest.name),
                        fontSize: 10,
                        bold: true,
                        color: template.colors.body,
                        margin: [0, 0, 0, 2]
                    })
                }
                if (hasVisibleText(interest.description)) {
                    stack.push({
                        text: decodeHtmlEntities(interest.description),
                        fontSize: 9,
                        color: template.colors.muted,
                        margin: [0, 0, 0, 0]
                    })
                }
                return {
                    stack: stack.length ? stack : [{ text: '' }],
                    fillColor: template.colors.skillBg,
                    margin: [6, 4, 6, 4]
                }
            })
            while (cols.length < 3) {
                cols.push({ text: '', fillColor: template.colors.skillBg, margin: [6, 4, 6, 4] })
            }
            rows.push(cols)
        }
        content.push({
            table: {
                widths: [colWidth, colWidth, colWidth],
                body: rows
            },
            layout: { hLineWidth: () => 0, vLineWidth: () => 0 },
            margin: [0, 0, 0, 12]
        })
    }

    const footer = (currentPage, pageCount) => ({
        text: `${profile?.full_name ? decodeHtmlEntities(profile.full_name) + ' - ' : ''}Page ${currentPage} of ${pageCount}`,
        alignment: 'center',
        fontSize: 9,
        color: template.colors.muted,
        margin: [0, 20, 0, 0]
    })

    return {
        ...docConfig,
        pageMargins: [45, 55, 45, 55],
        content: content,
        footer: footer,
        info: {
            title: `${profile?.full_name || 'CV'} - Curriculum Vitae`,
            author: profile?.full_name || 'CV Builder User',
            subject: 'Curriculum Vitae',
            keywords: 'CV, Resume, Curriculum Vitae'
        }
    }
}
