/**
 * Structured Template HTML Preview Renderer
 * Based on clean professional layout: centered header, light blue accents,
 * Skills grouped by category (3-col grid per category), Career Highlights, Professional Experience with shaded headers
 */

/**
 * Render structured template preview
 */
export function render(container, { cvData, profile, sections, includePhoto, includeQr, template }) {
    if (!container) {
        console.error('Preview container not found')
        return
    }

    const colors = template?.colors || {
        header: '#1e3a8a',
        body: '#374151',
        accent: '#0ea5e9',
        muted: '#64748b',
        divider: '#7dd3fc',
        link: '#0284c7'
    }

    let html = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; background: white; border: 1px solid #1e40af;">'

    // HEADER (Centered: Name, Titles, Contact)
    if (sections?.profile !== false && profile) {
        html += '<div style="text-align: center; margin-bottom: 24px;">'

        if (profile.full_name) {
            html += `<h1 style="font-size: 26px; font-weight: bold; color: ${colors.header}; margin: 0 0 8px 0; letter-spacing: 1px; text-transform: uppercase;">${escapeHtml(profile.full_name)}</h1>`
        }

        if (profile.bio) {
            const bioShort = profile.bio.length > 80 ? profile.bio.substring(0, 80) + '...' : profile.bio
            html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 8px 0;">${escapeHtml(bioShort)}</p>`
        }

        const contactParts = []
        if (profile.location) contactParts.push(escapeHtml(profile.location))
        if (profile.email) contactParts.push(escapeHtml(profile.email))
        if (profile.phone) contactParts.push(escapeHtml(profile.phone))
        if (profile.linkedin_url) contactParts.push(`<a href="${escapeHtml(profile.linkedin_url)}" style="color: ${colors.link}; text-decoration: underline;">LinkedIn</a>`)

        if (contactParts.length > 0) {
            html += `<p style="font-size: 11px; color: ${colors.muted}; margin: 0;">${contactParts.join(' | ')}</p>`
        }

        html += `<hr style="border: none; border-top: 1px solid ${colors.divider}; margin: 20px 0;">`
        html += '</div>'
    }

    // PROFESSIONAL SUMMARY
    if (sections?.summary !== false && cvData.professional_summary?.description) {
        html += renderStructuredSection('Professional Summary', colors)
        html += `<p style="font-size: 13px; color: ${colors.body}; line-height: 1.6; margin: 0 0 20px 0;">${escapeHtml(cvData.professional_summary.description)}</p>`
    }

    // AREAS OF EXPERTISE (category names only, no individual skills)
    if (sections?.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        const grouped = groupSkills(cvData.skills)
        const categories = Object.keys(grouped)
        if (categories.length > 0) {
            html += renderStructuredSection('Areas of Expertise', colors)
            html += '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px 16px; margin-bottom: 20px;">'
            categories.forEach(cat => {
                html += `<div style="background: #e0f2fe; padding: 6px 10px; font-size: 12px; color: ${colors.body};">${escapeHtml(cat)}</div>`
            })
            html += '</div>'
        }
    }

    // CAREER HIGHLIGHTS (from professional summary strengths)
    if (sections?.summary !== false && Array.isArray(cvData.professional_summary?.strengths) && cvData.professional_summary.strengths.length > 0) {
        html += renderStructuredSection('Career Highlights', colors)
        cvData.professional_summary.strengths.slice(0, 5).forEach(s => {
            if (s.strength) {
                html += `<p style="font-size: 13px; color: ${colors.body}; margin: 0 0 6px 0;"><strong>${escapeHtml(s.strength)}</strong></p>`
            }
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // PROFESSIONAL EXPERIENCE (job headers with light blue background)
    if (sections?.work !== false && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        html += renderStructuredSection('Professional Experience', colors)

        cvData.work_experience.forEach(exp => {
            const orgLine = []
            if (exp.company_name) orgLine.push(escapeHtml(exp.company_name))
            const dateRange = formatDateRange(exp.start_date, exp.end_date)
            if (dateRange) orgLine.push(dateRange)

            html += '<div style="margin-bottom: 16px;">'
            html += '<div style="background: #e0f2fe; padding: 8px 12px; margin-bottom: 6px;">'
            if (exp.position) {
                html += `<div style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin-bottom: 4px;">${escapeHtml(exp.position)}</div>`
            }
            if (orgLine.length > 0) {
                html += `<div style="font-size: 12px; font-weight: bold; color: ${colors.body};">${orgLine.join(' | ')}</div>`
            }
            html += '</div>'

            if (exp.description) {
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 8px 0;">${escapeHtml(exp.description)}</p>`
            }

            if (Array.isArray(exp.responsibility_categories)) {
                exp.responsibility_categories.forEach(cat => {
                    if (Array.isArray(cat.items) && cat.items.length > 0) {
                        html += '<ul style="margin: 0 0 8px 0; padding-left: 20px;">'
                        cat.items.forEach(item => {
                            if (item.content) {
                                html += `<li style="font-size: 12px; color: ${colors.body}; margin-bottom: 2px; line-height: 1.4;">${escapeHtml(item.content)}</li>`
                            }
                        })
                        html += '</ul>'
                    }
                })
            }
            html += '</div>'
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // EDUCATION
    if (sections?.education !== false && Array.isArray(cvData.education) && cvData.education.length > 0) {
        html += renderStructuredSection('Education', colors)
        cvData.education.forEach(edu => {
            const parts = []
            if (edu.degree) parts.push(escapeHtml(edu.degree))
            if (edu.institution) parts.push(escapeHtml(edu.institution))
            if (profile?.location) parts.push(escapeHtml(profile.location))
            if (edu.end_date) parts.push(new Date(edu.end_date).getFullYear())
            html += `<p style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${parts.join(' | ')}</p>`
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // ADDITIONAL TRAINING & CERTIFICATES
    if (sections?.certifications !== false && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        html += renderStructuredSection('Additional Training & Certificates', colors)
        cvData.certifications.forEach(cert => {
            const parts = []
            if (cert.name) parts.push(escapeHtml(cert.name))
            if (cert.issuer) parts.push(escapeHtml(cert.issuer))
            if (cert.date_obtained) parts.push(new Date(cert.date_obtained).getFullYear())
            html += `<p style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${parts.join(' | ')}</p>`
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // SKILLS (grouped by category, below certificates)
    if (sections?.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        html += renderStructuredSection('Skills', colors)
        const grouped = groupSkills(cvData.skills)
        Object.entries(grouped).forEach(([category, skills]) => {
            html += `<div style="margin-bottom: 12px;">`
            html += `<div style="font-size: 12px; font-weight: bold; color: ${colors.body}; margin-bottom: 4px;">${escapeHtml(category)}</div>`
            const skillNames = skills.map(s => escapeHtml(s.name) + (s.level ? ` (${escapeHtml(s.level)})` : '')).join(', ')
            html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0;">${skillNames}</p>`
            html += '</div>'
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // PROJECTS
    if (sections?.projects !== false && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        html += renderStructuredSection('Projects', colors)
        cvData.projects.forEach(project => {
            html += '<div style="margin-bottom: 16px;">'
            if (project.title) {
                html += `<div style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin-bottom: 4px;">${escapeHtml(project.title)}</div>`
            }
            if (project.start_date || project.end_date) {
                const dateRange = formatDateRange(project.start_date, project.end_date)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 6px 0;">${dateRange}</p>`
            }
            if (project.description) {
                html += `<p style="font-size: 12px; color: ${colors.body}; line-height: 1.5; margin: 0 0 6px 0;">${escapeHtml(project.description)}</p>`
            }
            if (project.url) {
                html += `<p style="margin: 0;"><a href="${escapeHtml(project.url)}" style="font-size: 12px; color: ${colors.link}; text-decoration: underline;">${escapeHtml(project.url)}</a></p>`
            }
            html += '</div>'
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // PROFESSIONAL QUALIFICATION EQUIVALENCE
    if (sections?.qualificationEquivalence !== false && Array.isArray(cvData.qualification_equivalence) && cvData.qualification_equivalence.length > 0) {
        html += renderStructuredSection('Professional Qualification Equivalence', colors)
        cvData.qualification_equivalence.forEach(q => {
            html += '<div style="margin-bottom: 12px;">'
            if (q.level) {
                html += `<div style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin-bottom: 4px;">${escapeHtml(q.level)}</div>`
            }
            if (q.description) {
                html += `<p style="font-size: 12px; color: ${colors.body}; line-height: 1.5; margin: 0 0 6px 0;">${escapeHtml(q.description)}</p>`
            }
            if (Array.isArray(q.evidence) && q.evidence.length > 0) {
                html += '<ul style="margin: 0 0 8px 0; padding-left: 20px;">'
                q.evidence.forEach(e => {
                    const content = e.content || e.evidence || ''
                    if (content) html += `<li style="font-size: 12px; color: ${colors.body}; margin-bottom: 2px;">${escapeHtml(content)}</li>`
                })
                html += '</ul>'
            }
            html += '</div>'
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // PROFESSIONAL MEMBERSHIPS
    const memberships = cvData.memberships || cvData.professional_memberships || []
    if (sections?.memberships !== false && Array.isArray(memberships) && memberships.length > 0) {
        html += renderStructuredSection('Professional Memberships', colors)
        memberships.forEach(m => {
            html += '<div style="margin-bottom: 12px;">'
            if (m.organisation) {
                html += `<div style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin-bottom: 4px;">${escapeHtml(m.organisation)}</div>`
            }
            if (m.role) {
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 4px 0;">${escapeHtml(m.role)}</p>`
            }
            if (m.start_date || m.end_date) {
                const dateRange = formatDateRange(m.start_date, m.end_date)
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0;">${dateRange}</p>`
            }
            html += '</div>'
        })
        html += '<div style="margin-bottom: 20px;"></div>'
    }

    // INTERESTS & ACTIVITIES (3-column grid with name and description)
    if (sections?.interests !== false && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        html += renderStructuredSection('Interests & Activities', colors)
        html += '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px 16px; margin-bottom: 20px;">'
        cvData.interests.forEach(i => {
            html += '<div style="background: #e0f2fe; padding: 8px 10px; border-radius: 4px;">'
            if (i.name) {
                html += `<div style="font-size: 12px; font-weight: bold; color: ${colors.body}; margin-bottom: 4px;">${escapeHtml(i.name)}</div>`
            }
            if (i.description) {
                html += `<div style="font-size: 11px; color: ${colors.muted}; line-height: 1.4;">${escapeHtml(i.description)}</div>`
            }
            html += '</div>'
        })
        html += '</div>'
    }

    html += '</div>'
    container.innerHTML = html
}

function renderStructuredSection(title, colors) {
    return `
        <div style="margin: 20px 0 12px 0;">
            <h2 style="font-size: 13px; font-weight: bold; color: ${colors.header}; text-transform: uppercase; margin: 0 0 6px 0;">${title}</h2>
            <div style="border-bottom: 2px solid ${colors.divider}; margin-bottom: 10px;"></div>
        </div>
    `
}

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
        if (!grouped[category]) grouped[category] = []
        grouped[category].push(skill)
    })
    return grouped
}
