/**
 * Classic Template HTML Preview Renderer
 * Matches the PDF classic template design
 */

/**
 * Render classic template preview
 */
export function render(container, { cvData, profile, sections, includePhoto, includeQr, template }) {
    if (!container) {
        console.error('Preview container not found')
        return
    }

    // Template colors
    const colors = template?.colors || {
        header: '#1e3a8a',
        body: '#475569',
        accent: '#1e3a8a',
        muted: '#64748b',
        divider: '#1e3a8a',
        link: '#1e40af'
    }

    let html = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; background: white;">'

    // HEADER (Centered, Formal)
    if (sections?.profile !== false && profile) {
        html += '<div style="text-align: center; margin-bottom: 30px;">'

        // Name
        if (profile.full_name) {
            html += `<h1 style="font-size: 28px; font-weight: bold; color: ${colors.header}; margin: 0 0 12px 0; letter-spacing: 2px; text-transform: uppercase;">${escapeHtml(profile.full_name)}</h1>`
        }

        // Contact info (single line)
        const contactParts = []
        if (profile.location) contactParts.push(escapeHtml(profile.location))
        if (profile.phone) contactParts.push(escapeHtml(profile.phone))
        if (profile.email) contactParts.push(escapeHtml(profile.email))

        if (contactParts.length > 0) {
            html += `<p style="font-size: 13px; color: ${colors.muted}; margin: 0 0 6px 0;">${contactParts.join(' • ')}</p>`
        }

        // LinkedIn
        if (profile.linkedin_url) {
            html += `<p style="margin: 0 0 8px 0;"><a href="${escapeHtml(profile.linkedin_url)}" style="font-size: 12px; color: ${colors.link}; text-decoration: underline;">${escapeHtml(profile.linkedin_url)}</a></p>`
        }

        // Bio
        if (profile.bio) {
            html += `<p style="font-size: 13px; color: ${colors.body}; font-style: italic; margin: 12px 60px 0 60px; line-height: 1.5;">${escapeHtml(profile.bio)}</p>`
        }

        // Divider
        html += `<hr style="border: none; border-top: 2px solid ${colors.divider}; margin: 24px 0;">`

        html += '</div>'
    }

    // PROFESSIONAL SUMMARY
    if (sections?.summary !== false && cvData.professional_summary) {
        html += renderSection('Professional Summary', colors)

        if (cvData.professional_summary.description) {
            html += `<p style="font-size: 14px; color: ${colors.body}; line-height: 1.6; margin: 0 0 12px 0;">${escapeHtml(cvData.professional_summary.description)}</p>`
        }

        if (Array.isArray(cvData.professional_summary.strengths) && cvData.professional_summary.strengths.length > 0) {
            html += '<ul style="margin: 0; padding-left: 20px; color: ' + colors.body + ';">'
            cvData.professional_summary.strengths.forEach(s => {
                if (s.strength) {
                    html += `<li style="font-size: 13px; margin-bottom: 4px; line-height: 1.5;">${escapeHtml(s.strength)}</li>`
                }
            })
            html += '</ul>'
        }

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // WORK EXPERIENCE
    if (sections?.work !== false && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        html += renderSection('Professional Experience', colors)

        cvData.work_experience.forEach(exp => {
            html += '<div style="margin-bottom: 20px;">'

            if (exp.position) {
                html += `<h3 style="font-size: 15px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(exp.position)}</h3>`
            }

            if (exp.company_name) {
                html += `<p style="font-size: 14px; color: ${colors.accent}; margin: 0 0 4px 0;">${escapeHtml(exp.company_name)}</p>`
            }

            if (!exp.hide_date && (exp.start_date || exp.end_date)) {
                const dateRange = formatDateRange(exp.start_date, exp.end_date)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 8px 0;">${dateRange}</p>`
            }

            if (exp.description) {
                html += `<p style="font-size: 14px; color: ${colors.body}; line-height: 1.5; margin: 0 0 8px 0;">${escapeHtml(exp.description)}</p>`
            }

            // Responsibilities
            if (Array.isArray(exp.responsibility_categories)) {
                exp.responsibility_categories.forEach(cat => {
                    if (cat.name) {
                        html += `<p style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin: 8px 0 4px 0;">${escapeHtml(cat.name)}</p>`
                    }

                    if (Array.isArray(cat.items) && cat.items.length > 0) {
                        html += '<ul style="margin: 0 0 8px 0; padding-left: 20px;">'
                        cat.items.forEach(item => {
                            if (item.content) {
                                html += `<li style="font-size: 13px; color: ${colors.body}; margin-bottom: 3px; line-height: 1.4;">${escapeHtml(item.content)}</li>`
                            }
                        })
                        html += '</ul>'
                    }
                })
            }

            html += '</div>'
        })

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // EDUCATION
    if (sections?.education !== false && Array.isArray(cvData.education) && cvData.education.length > 0) {
        html += renderSection('Education', colors)

        cvData.education.forEach(edu => {
            html += '<div style="margin-bottom: 16px;">'

            if (edu.degree) {
                html += `<h3 style="font-size: 14px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(edu.degree)}</h3>`
            }

            if (edu.institution) {
                html += `<p style="font-size: 14px; color: ${colors.accent}; margin: 0 0 4px 0;">${escapeHtml(edu.institution)}</p>`
            }

            if (edu.field_of_study) {
                html += `<p style="font-size: 13px; color: ${colors.muted}; margin: 0 0 4px 0;">${escapeHtml(edu.field_of_study)}</p>`
            }

            if (edu.start_date || edu.end_date) {
                const dateRange = formatDateRange(edu.start_date, edu.end_date)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0;">${dateRange}</p>`
            }

            html += '</div>'
        })

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // SKILLS
    if (sections?.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        html += renderSection('Skills & Competencies', colors)

        const grouped = groupSkills(cvData.skills)

        Object.entries(grouped).forEach(([category, skills]) => {
            html += `<p style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin: 8px 0 4px 0;">${escapeHtml(category)}</p>`

            const skillNames = skills.map(s => escapeHtml(s.name)).join(', ')
            html += `<p style="font-size: 13px; color: ${colors.body}; margin: 0 0 8px 0; line-height: 1.5;">${skillNames}</p>`
        })

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // PROJECTS
    if (sections?.projects !== false && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        html += renderSection('Projects', colors)

        cvData.projects.forEach(project => {
            html += '<div style="margin-bottom: 16px;">'

            if (project.title) {
                html += `<h3 style="font-size: 14px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(project.title)}</h3>`
            }

            if (project.start_date || project.end_date) {
                const dateRange = formatDateRange(project.start_date, project.end_date)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 6px 0;">${dateRange}</p>`
            }

            if (project.description) {
                html += `<p style="font-size: 13px; color: ${colors.body}; margin: 0 0 6px 0; line-height: 1.5;">${escapeHtml(project.description)}</p>`
            }

            if (project.url) {
                html += `<p style="margin: 0;"><a href="${escapeHtml(project.url)}" style="font-size: 12px; color: ${colors.link}; text-decoration: underline;">${escapeHtml(project.url)}</a></p>`
            }

            html += '</div>'
        })

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // CERTIFICATIONS
    if (sections?.certifications !== false && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        html += renderSection('Certifications', colors)

        cvData.certifications.forEach(cert => {
            html += '<div style="margin-bottom: 12px;">'

            if (cert.name) {
                html += `<h3 style="font-size: 14px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(cert.name)}</h3>`
            }

            if (cert.issuer) {
                html += `<p style="font-size: 13px; color: ${colors.accent}; margin: 0 0 4px 0;">${escapeHtml(cert.issuer)}</p>`
            }

            if (cert.date_obtained || cert.expiry_date) {
                const parts = []
                if (cert.date_obtained) parts.push(`Issued: ${formatDate(cert.date_obtained)}`)
                if (cert.expiry_date) parts.push(`Expires: ${formatDate(cert.expiry_date)}`)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0;">${parts.join(' | ')}</p>`
            }

            html += '</div>'
        })

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // PROFESSIONAL MEMBERSHIPS
    if (sections?.memberships !== false && Array.isArray(cvData.professional_memberships) && cvData.professional_memberships.length > 0) {
        html += renderSection('Professional Memberships', colors)

        cvData.professional_memberships.forEach(membership => {
            html += '<div style="margin-bottom: 12px;">'

            if (membership.organisation) {
                html += `<h3 style="font-size: 14px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(membership.organisation)}</h3>`
            }

            if (membership.role) {
                html += `<p style="font-size: 13px; color: ${colors.muted}; margin: 0 0 4px 0;">${escapeHtml(membership.role)}</p>`
            }

            if (membership.start_date || membership.end_date) {
                const dateRange = formatDateRange(membership.start_date, membership.end_date)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0;">${dateRange}</p>`
            }

            html += '</div>'
        })

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    // INTERESTS
    if (sections?.interests !== false && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        html += renderSection('Interests & Activities', colors)

        const interestNames = cvData.interests.map(i => escapeHtml(i.name)).join(', ')
        html += `<p style="font-size: 14px; color: ${colors.body}; line-height: 1.5;">${interestNames}</p>`

        html += '<div style="margin-bottom: 24px;"></div>'
    }

    html += '</div>'

    container.innerHTML = html
}

/**
 * Render section header (classic centered style with borders)
 */
function renderSection(title, colors) {
    return `
        <div style="margin: 24px 0 16px 0;">
            <hr style="border: none; border-top: 1px solid ${colors.divider}; margin: 0 0 8px 0;">
            <h2 style="font-size: 15px; font-weight: bold; color: ${colors.header}; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px 0;">${title}</h2>
            <hr style="border: none; border-top: 1px solid ${colors.divider}; margin: 0 0 12px 0;">
        </div>
    `
}

/**
 * Helper functions
 */
function escapeHtml(text) {
    if (!text) return ''
    const div = document.createElement('div')
    div.textContent = text
    return div.innerHTML
}

function formatDate(dateStr) {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return dateStr
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${month}/${year}`
}

function formatDateRange(startDate, endDate) {
    const start = formatDate(startDate)
    const end = endDate ? formatDate(endDate) : 'Present'
    if (!start) return end
    return `${start} - ${end}`
}

function groupSkills(skills) {
    const grouped = {}
    if (!Array.isArray(skills)) return grouped

    skills.forEach(skill => {
        const category = skill.category || 'Other'
        if (!grouped[category]) {
            grouped[category] = []
        }
        grouped[category].push(skill)
    })

    return grouped
}
