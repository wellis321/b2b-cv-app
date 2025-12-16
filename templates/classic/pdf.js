/**
 * Classic Template PDF Builder
 * Traditional, ATS-friendly design for academia, government, legal sectors
 *
 * Design Elements:
 * - Navy/Gray color scheme (#1e3a8a header, #475569 body)
 * - Centered header with horizontal rules
 * - Single column layout
 * - Formal contact information
 * - Traditional section dividers
 */

import {
    createClassicHeader
} from '../builders/section-headers.js'

import {
    buildWorkExperienceSection,
    buildEducationSection,
    buildSkillsListSection,
    buildProjectsSection,
    buildCertificationsSection,
    buildMembershipsSection,
    buildInterestsSection,
    buildProfessionalSummarySection
} from '../builders/section-builders.js'

import {
    buildDocumentConfig,
    getPageMargins
} from '../builders/style-presets.js'

import {
    decodeHtmlEntities,
    hasVisibleText,
    createDivider
} from '../builders/utils.js'

/**
 * Build classic template PDF document definition
 */
export function buildDocDefinition({ cvData, profile, config, cvUrl, qrCodeImage, templateId }) {
    const template = {
        id: 'classic',
        colors: {
            header: '#1e3a8a',      // Navy blue
            body: '#475569',        // Slate gray
            accent: '#1e3a8a',      // Navy (same as header)
            muted: '#64748b',       // Light slate
            divider: '#1e3a8a',     // Navy
            link: '#1e40af'         // Slightly lighter navy
        }
    }

    const sections = config?.sections || {}
    const includePhoto = config?.includePhoto !== false
    const includeQRCode = config?.includeQRCode !== false

    // Build document configuration with conservative style
    const docConfig = buildDocumentConfig(template, 'conservative', config?.customization || {})

    const content = []

    // HEADER SECTION (Centered, Formal)
    if (sections.profile !== false) {
        const headerContent = []

        // Name (Large, centered, uppercase)
        if (profile?.full_name) {
            headerContent.push({
                text: decodeHtmlEntities(profile.full_name).toUpperCase(),
                fontSize: 22,
                bold: true,
                color: template.colors.header,
                alignment: 'center',
                margin: [0, 0, 0, 8]
            })
        }

        // Contact Information (Centered, single line)
        const contactParts = []

        if (profile?.location) {
            contactParts.push(decodeHtmlEntities(profile.location))
        }
        if (profile?.phone) {
            contactParts.push(decodeHtmlEntities(profile.phone))
        }
        if (profile?.email) {
            contactParts.push(decodeHtmlEntities(profile.email))
        }

        if (contactParts.length > 0) {
            headerContent.push({
                text: contactParts.join(' • '),
                fontSize: 10,
                color: template.colors.muted,
                alignment: 'center',
                margin: [0, 0, 0, 4]
            })
        }

        // LinkedIn (if provided)
        if (profile?.linkedin_url) {
            headerContent.push({
                text: decodeHtmlEntities(profile.linkedin_url),
                link: decodeHtmlEntities(profile.linkedin_url),
                fontSize: 9,
                color: template.colors.link,
                decoration: 'underline',
                alignment: 'center',
                margin: [0, 0, 0, 4]
            })
        }

        // Bio (if provided, centered)
        if (hasVisibleText(profile?.bio)) {
            headerContent.push({
                text: decodeHtmlEntities(profile.bio),
                fontSize: 10,
                color: template.colors.body,
                alignment: 'center',
                italics: true,
                margin: [0, 6, 0, 0]
            })
        }

        // Divider after header
        headerContent.push(createDivider(template.colors.divider, 1.5, [0, 12, 0, 12]))

        content.push(...headerContent)
    }

    // PROFESSIONAL SUMMARY
    if (sections.professionalSummary !== false && cvData.professional_summary) {
        content.push(...createClassicHeader('Professional Summary', template))
        content.push(...buildProfessionalSummarySection(
            cvData.professional_summary,
            template,
            { fontSize: 11, showStrengths: true }
        ))
    }

    // WORK EXPERIENCE
    if (sections.workExperience !== false && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        content.push(...createClassicHeader('Professional Experience', template))
        content.push(...buildWorkExperienceSection(
            cvData.work_experience,
            template,
            {
                showDates: true,
                showDescription: true,
                showResponsibilities: true,
                fontSize: 11
            }
        ))
    }

    // EDUCATION
    if (sections.education !== false && Array.isArray(cvData.education) && cvData.education.length > 0) {
        content.push(...createClassicHeader('Education', template))
        content.push(...buildEducationSection(
            cvData.education,
            template,
            {
                showDates: true,
                showDescription: true,
                fontSize: 11
            }
        ))
    }

    // SKILLS
    if (sections.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        content.push(...createClassicHeader('Skills & Competencies', template))
        content.push(...buildSkillsListSection(
            cvData.skills,
            template,
            {
                fontSize: 11,
                showLevel: false,  // Classic template doesn't show skill levels
                groupByCategory: true
            }
        ))
    }

    // PROJECTS
    if (sections.projects !== false && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        content.push(...createClassicHeader('Projects', template))
        content.push(...buildProjectsSection(
            cvData.projects,
            template,
            {
                showDates: true,
                showUrl: true,
                fontSize: 11
            }
        ))
    }

    // CERTIFICATIONS
    if (sections.certifications !== false && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        content.push(...createClassicHeader('Certifications', template))
        content.push(...buildCertificationsSection(
            cvData.certifications,
            template,
            {
                showDates: true,
                fontSize: 11
            }
        ))
    }

    // QUALIFICATION EQUIVALENCE (if present)
    if (sections.qualificationEquivalence !== false && Array.isArray(cvData.qualification_equivalence) && cvData.qualification_equivalence.length > 0) {
        content.push(...createClassicHeader('Professional Qualification Equivalence', template))

        cvData.qualification_equivalence.forEach((qual, index) => {
            const qualContent = []

            if (qual.level_name) {
                qualContent.push({
                    text: decodeHtmlEntities(qual.level_name),
                    fontSize: 12,
                    bold: true,
                    color: template.colors.body
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

            // Supporting evidence
            if (Array.isArray(qual.supporting_evidence) && qual.supporting_evidence.length > 0) {
                const evidenceItems = qual.supporting_evidence
                    .filter(e => hasVisibleText(e.evidence))
                    .map(e => decodeHtmlEntities(e.evidence))

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
    }

    // PROFESSIONAL MEMBERSHIPS
    if (sections.memberships !== false && Array.isArray(cvData.professional_memberships) && cvData.professional_memberships.length > 0) {
        content.push(...createClassicHeader('Professional Memberships', template))
        content.push(...buildMembershipsSection(
            cvData.professional_memberships,
            template,
            {
                showDates: true,
                fontSize: 11
            }
        ))
    }

    // INTERESTS & ACTIVITIES
    if (sections.interests !== false && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        content.push(...createClassicHeader('Interests & Activities', template))
        content.push(...buildInterestsSection(
            cvData.interests,
            template,
            {
                fontSize: 11,
                showDescription: false,
                layout: 'inline'  // Comma-separated for classic look
            }
        ))
    }

    // QR CODE (Bottom right, if included and not in header)
    if (includeQRCode && qrCodeImage && cvUrl) {
        content.push({
            columns: [
                { width: '*', text: '' },
                {
                    width: 80,
                    stack: [
                        {
                            image: qrCodeImage,
                            width: 70,
                            height: 70,
                            alignment: 'right'
                        },
                        {
                            text: 'View Online',
                            link: cvUrl,
                            fontSize: 8,
                            color: template.colors.link,
                            alignment: 'center',
                            margin: [0, 4, 0, 0]
                        }
                    ]
                }
            ],
            margin: [0, 16, 0, 0]
        })
    }

    // Footer with page numbers
    const footer = (currentPage, pageCount) => {
        return {
            text: `${profile?.full_name ? decodeHtmlEntities(profile.full_name) + ' - ' : ''}Page ${currentPage} of ${pageCount}`,
            alignment: 'center',
            fontSize: 9,
            color: template.colors.muted,
            margin: [0, 20, 0, 0]
        }
    }

    // Build final document definition
    return {
        ...docConfig,
        pageMargins: [45, 60, 45, 60],  // Slightly tighter margins for classic
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
