/**
 * Modern Template PDF Builder
 * Two-column sidebar layout for tech and creative industries
 *
 * Design Elements:
 * - Teal/Slate color scheme (#0d9488 accent, #334155 header)
 * - 30% sidebar + 70% main content
 * - Sidebar: Skills, contact info, education
 * - Main: Work experience, projects, summary
 * - Geometric, contemporary feel
 */

import {
    createLineHeader,
    createSideBorderHeader
} from '../builders/section-headers.js'

import {
    buildWorkExperienceSection,
    buildEducationSection,
    buildSkillsGridSection,
    buildProjectsSection,
    buildCertificationsSection,
    buildMembershipsSection,
    buildInterestsSection,
    buildProfessionalSummarySection
} from '../builders/section-builders.js'

import {
    buildDocumentConfig
} from '../builders/style-presets.js'

import {
    decodeHtmlEntities,
    hasVisibleText,
    createDivider
} from '../builders/utils.js'

/**
 * Build modern template PDF document definition
 */
export function buildDocDefinition({ cvData, profile, config, cvUrl, qrCodeImage, templateId }) {
    const template = {
        id: 'modern',
        colors: {
            header: '#0f172a',      // Slate 900
            body: '#334155',        // Slate 700
            accent: '#0d9488',      // Teal 600
            muted: '#64748b',       // Slate 500
            divider: '#e2e8f0',     // Slate 200
            link: '#0891b2'         // Cyan 600
        }
    }

    const sections = config?.sections || {}
    const includePhoto = config?.includePhoto !== false
    const includeQRCode = config?.includeQRCode !== false

    // Build document configuration with modern style
    const docConfig = buildDocumentConfig(template, 'modern', config?.customization || {})

    // SIDEBAR CONTENT (Left - 30%)
    const sidebarContent = []

    // Profile photo (if included)
    if (includePhoto && profile?.photo_base64) {
        sidebarContent.push({
            image: profile.photo_base64,
            width: 90,
            height: 90,
            margin: [0, 0, 0, 16],
            alignment: 'center'
        })
    }

    // Contact Information
    if (sections.profile !== false && profile) {
        const contactItems = []

        if (profile.location) {
            contactItems.push({
                text: [
                    { text: '📍 ', fontSize: 10 },
                    { text: decodeHtmlEntities(profile.location), fontSize: 9 }
                ],
                color: template.colors.body,
                margin: [0, 0, 0, 4]
            })
        }

        if (profile.phone) {
            contactItems.push({
                text: [
                    { text: '📞 ', fontSize: 10 },
                    { text: decodeHtmlEntities(profile.phone), fontSize: 9 }
                ],
                color: template.colors.body,
                margin: [0, 0, 0, 4]
            })
        }

        if (profile.email) {
            contactItems.push({
                text: [
                    { text: '✉ ', fontSize: 10 },
                    { text: decodeHtmlEntities(profile.email), fontSize: 9 }
                ],
                color: template.colors.body,
                margin: [0, 0, 0, 4]
            })
        }

        if (profile.linkedin_url) {
            contactItems.push({
                text: 'LinkedIn',
                link: decodeHtmlEntities(profile.linkedin_url),
                fontSize: 9,
                color: template.colors.link,
                decoration: 'underline',
                margin: [0, 0, 0, 4]
            })
        }

        if (contactItems.length > 0) {
            sidebarContent.push({
                stack: [
                    {
                        text: 'CONTACT',
                        fontSize: 11,
                        bold: true,
                        color: template.colors.accent,
                        margin: [0, 0, 0, 8]
                    },
                    ...contactItems
                ],
                margin: [0, 0, 0, 16]
            })
        }
    }

    // SKILLS (Sidebar)
    if (sections.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        sidebarContent.push({
            text: 'SKILLS',
            fontSize: 11,
            bold: true,
            color: template.colors.accent,
            margin: [0, 0, 0, 8]
        })

        // Group skills by category
        const groupedSkills = {}
        cvData.skills.forEach(skill => {
            const category = skill.category || 'General'
            if (!groupedSkills[category]) {
                groupedSkills[category] = []
            }
            groupedSkills[category].push(skill)
        })

        Object.entries(groupedSkills).forEach(([category, skills]) => {
            sidebarContent.push({
                text: category,
                fontSize: 9,
                bold: true,
                color: template.colors.body,
                margin: [0, 4, 0, 3]
            })

            skills.forEach(skill => {
                // Skill with progress bar visual
                const level = skill.level || 'Proficient'
                sidebarContent.push({
                    stack: [
                        {
                            text: decodeHtmlEntities(skill.name),
                            fontSize: 8.5,
                            color: template.colors.body,
                            margin: [0, 0, 0, 2]
                        },
                        {
                            canvas: [
                                {
                                    type: 'rect',
                                    x: 0,
                                    y: 0,
                                    w: 100,
                                    h: 4,
                                    color: template.colors.divider
                                },
                                {
                                    type: 'rect',
                                    x: 0,
                                    y: 0,
                                    w: getSkillBarWidth(level),
                                    h: 4,
                                    color: template.colors.accent
                                }
                            ],
                            margin: [0, 0, 0, 6]
                        }
                    ]
                })
            })
        })

        sidebarContent.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // EDUCATION (Sidebar)
    if (sections.education !== false && Array.isArray(cvData.education) && cvData.education.length > 0) {
        sidebarContent.push({
            text: 'EDUCATION',
            fontSize: 11,
            bold: true,
            color: template.colors.accent,
            margin: [0, 0, 0, 8]
        })

        cvData.education.forEach((edu, index) => {
            const eduContent = []

            if (edu.degree) {
                eduContent.push({
                    text: decodeHtmlEntities(edu.degree),
                    fontSize: 9,
                    bold: true,
                    color: template.colors.body
                })
            }

            if (edu.institution) {
                eduContent.push({
                    text: decodeHtmlEntities(edu.institution),
                    fontSize: 8.5,
                    color: template.colors.muted,
                    margin: [0, 2, 0, 2]
                })
            }

            if (edu.start_date || edu.end_date) {
                const startYear = edu.start_date ? new Date(edu.start_date).getFullYear() : ''
                const endYear = edu.end_date ? new Date(edu.end_date).getFullYear() : 'Present'
                eduContent.push({
                    text: `${startYear} - ${endYear}`,
                    fontSize: 8,
                    color: template.colors.muted
                })
            }

            sidebarContent.push({
                stack: eduContent,
                margin: [0, 0, 0, index < cvData.education.length - 1 ? 10 : 0]
            })
        })

        sidebarContent.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // CERTIFICATIONS (Sidebar)
    if (sections.certifications !== false && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        sidebarContent.push({
            text: 'CERTIFICATIONS',
            fontSize: 11,
            bold: true,
            color: template.colors.accent,
            margin: [0, 0, 0, 8]
        })

        cvData.certifications.forEach((cert, index) => {
            const certContent = []

            if (cert.name) {
                certContent.push({
                    text: decodeHtmlEntities(cert.name),
                    fontSize: 8.5,
                    bold: true,
                    color: template.colors.body
                })
            }

            if (cert.issuer) {
                certContent.push({
                    text: decodeHtmlEntities(cert.issuer),
                    fontSize: 8,
                    color: template.colors.muted,
                    margin: [0, 2, 0, 0]
                })
            }

            sidebarContent.push({
                stack: certContent,
                margin: [0, 0, 0, index < cvData.certifications.length - 1 ? 8 : 0]
            })
        })

        sidebarContent.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // INTERESTS (Sidebar - compact)
    if (sections.interests !== false && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        sidebarContent.push({
            text: 'INTERESTS',
            fontSize: 11,
            bold: true,
            color: template.colors.accent,
            margin: [0, 0, 0, 8]
        })

        const interestNames = cvData.interests.map(i => decodeHtmlEntities(i.name)).join(', ')
        sidebarContent.push({
            text: interestNames,
            fontSize: 8.5,
            color: template.colors.body,
            lineHeight: 1.4
        })
    }

    // MAIN CONTENT (Right - 70%)
    const mainContent = []

    // NAME & TITLE (Main area header)
    if (sections.profile !== false && profile) {
        if (profile.full_name) {
            mainContent.push({
                text: decodeHtmlEntities(profile.full_name).toUpperCase(),
                fontSize: 24,
                bold: true,
                color: template.colors.header,
                margin: [0, 0, 0, 4]
            })
        }

        if (profile.bio) {
            mainContent.push({
                text: decodeHtmlEntities(profile.bio),
                fontSize: 11,
                color: template.colors.accent,
                italics: true,
                margin: [0, 0, 0, 12]
            })
        }

        mainContent.push(createDivider(template.colors.accent, 2, [0, 0, 0, 16]))
    }

    // PROFESSIONAL SUMMARY
    if (sections.professionalSummary !== false && cvData.professional_summary) {
        mainContent.push(...createSideBorderHeader('Professional Summary', template, { fontSize: 14, borderWidth: 3 }))
        mainContent.push(...buildProfessionalSummarySection(
            cvData.professional_summary,
            template,
            { fontSize: 10.5, showStrengths: true }
        ))
        mainContent.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // WORK EXPERIENCE
    if (sections.workExperience !== false && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        mainContent.push(...createSideBorderHeader('Experience', template, { fontSize: 14, borderWidth: 3 }))
        mainContent.push(...buildWorkExperienceSection(
            cvData.work_experience,
            template,
            {
                showDates: true,
                showDescription: true,
                showResponsibilities: true,
                fontSize: 10.5,
                spacing: 1.0
            }
        ))
        mainContent.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // PROJECTS
    if (sections.projects !== false && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        mainContent.push(...createSideBorderHeader('Projects', template, { fontSize: 14, borderWidth: 3 }))
        mainContent.push(...buildProjectsSection(
            cvData.projects,
            template,
            {
                showDates: true,
                showUrl: true,
                fontSize: 10.5
            }
        ))
        mainContent.push({ text: '', margin: [0, 0, 0, 12] })
    }

    // PROFESSIONAL MEMBERSHIPS
    if (sections.memberships !== false && Array.isArray(cvData.professional_memberships) && cvData.professional_memberships.length > 0) {
        mainContent.push(...createSideBorderHeader('Memberships', template, { fontSize: 14, borderWidth: 3 }))
        mainContent.push(...buildMembershipsSection(
            cvData.professional_memberships,
            template,
            {
                showDates: true,
                fontSize: 10.5
            }
        ))
    }

    // Create two-column layout
    const pageWidth = 595 - 90  // A4 width - margins
    const sidebarWidth = Math.round(pageWidth * 0.30)
    const mainWidth = pageWidth - sidebarWidth - 20  // 20px gap

    const content = [
        {
            columns: [
                {
                    width: sidebarWidth,
                    stack: sidebarContent
                },
                {
                    width: 20,
                    text: ''  // Gap
                },
                {
                    width: mainWidth,
                    stack: mainContent
                }
            ]
        }
    ]

    // QR Code (bottom of sidebar if included)
    if (includeQRCode && qrCodeImage && cvUrl) {
        content.push({
            columns: [
                {
                    width: sidebarWidth,
                    stack: [
                        {
                            image: qrCodeImage,
                            width: 60,
                            height: 60,
                            alignment: 'center',
                            margin: [0, 16, 0, 4]
                        },
                        {
                            text: 'Scan for online CV',
                            fontSize: 7,
                            color: template.colors.muted,
                            alignment: 'center'
                        }
                    ]
                },
                { width: 20, text: '' },
                { width: mainWidth, text: '' }
            ]
        })
    }

    // Footer with page numbers
    const footer = (currentPage, pageCount) => {
        return {
            text: `Page ${currentPage} of ${pageCount}`,
            alignment: 'right',
            fontSize: 8,
            color: template.colors.muted,
            margin: [0, 10, 45, 0]
        }
    }

    // Build final document definition
    return {
        ...docConfig,
        pageMargins: [45, 65, 45, 65],
        content: content,
        footer: footer,
        info: {
            title: `${profile?.full_name || 'CV'} - Resume`,
            author: profile?.full_name || 'CV Builder User',
            subject: 'Professional Resume',
            keywords: 'CV, Resume, Modern'
        }
    }
}

/**
 * Helper: Get skill bar width based on level
 */
function getSkillBarWidth(level) {
    const levelMap = {
        'Expert': 100,
        'Advanced': 85,
        'Proficient': 70,
        'Intermediate': 55,
        'Beginner': 40,
        'Novice': 25
    }
    return levelMap[level] || 70
}
