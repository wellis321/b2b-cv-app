/**
 * Modern Template HTML Preview Renderer
 * Two-column sidebar layout matching PDF design
 */

/**
 * Render modern template preview
 */
export function render(container, { cvData, profile, sections, includePhoto, includeQr, template }) {
    if (!container) {
        console.error('Preview container not found')
        return
    }

    // Template colors
    const colors = template?.colors || {
        header: '#0f172a',
        body: '#334155',
        accent: '#0d9488',
        muted: '#64748b',
        divider: '#e2e8f0',
        link: '#0891b2'
    }

    let html = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 1000px; margin: 0 auto; padding: 40px; background: white;">'

    // Two-column layout
    html += '<div style="display: grid; grid-template-columns: 30% 70%; gap: 30px;">'

    // ===== SIDEBAR (Left 30%) =====
    html += '<div style="padding-right: 20px;">'

    // Profile photo
    if (includePhoto && profile?.photo_url) {
        html += `<div style="text-align: center; margin-bottom: 24px;">
            <img src="${escapeHtml(profile.photo_url)}" alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid ${colors.accent};">
        </div>`
    }

    // Contact Information
    if (sections?.profile !== false && profile) {
        html += `<div style="margin-bottom: 24px;">
            <h3 style="font-size: 13px; font-weight: bold; color: ${colors.accent}; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0;">Contact</h3>`

        if (profile.location) {
            html += `<p style="font-size: 11px; color: ${colors.body}; margin: 0 0 6px 0;">📍 ${escapeHtml(profile.location)}</p>`
        }
        if (profile.phone) {
            html += `<p style="font-size: 11px; color: ${colors.body}; margin: 0 0 6px 0;">📞 ${escapeHtml(profile.phone)}</p>`
        }
        if (profile.email) {
            html += `<p style="font-size: 11px; color: ${colors.body}; margin: 0 0 6px 0;">✉ ${escapeHtml(profile.email)}</p>`
        }
        if (profile.linkedin_url) {
            html += `<p style="margin: 0;"><a href="${escapeHtml(profile.linkedin_url)}" style="font-size: 11px; color: ${colors.link}; text-decoration: underline;">LinkedIn</a></p>`
        }

        html += '</div>'
    }

    // Skills
    if (sections?.skills !== false && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        html += `<div style="margin-bottom: 24px;">
            <h3 style="font-size: 13px; font-weight: bold; color: ${colors.accent}; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0;">Skills</h3>`

        const grouped = groupSkills(cvData.skills)
        Object.entries(grouped).forEach(([category, skills]) => {
            html += `<p style="font-size: 10px; font-weight: bold; color: ${colors.body}; margin: 8px 0 6px 0;">${escapeHtml(category)}</p>`

            skills.forEach(skill => {
                const level = skill.level || 'Proficient'
                const barWidth = getSkillBarWidth(level)

                html += `<div style="margin-bottom: 8px;">
                    <p style="font-size: 10px; color: ${colors.body}; margin: 0 0 3px 0;">${escapeHtml(skill.name)}</p>
                    <div style="width: 100%; height: 5px; background: ${colors.divider}; border-radius: 2px; overflow: hidden;">
                        <div style="width: ${barWidth}%; height: 100%; background: ${colors.accent};"></div>
                    </div>
                </div>`
            })
        })

        html += '</div>'
    }

    // Education
    if (sections?.education !== false && Array.isArray(cvData.education) && cvData.education.length > 0) {
        html += `<div style="margin-bottom: 24px;">
            <h3 style="font-size: 13px; font-weight: bold; color: ${colors.accent}; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0;">Education</h3>`

        cvData.education.forEach(edu => {
            html += '<div style="margin-bottom: 16px;">'

            if (edu.degree) {
                html += `<p style="font-size: 11px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(edu.degree)}</p>`
            }
            if (edu.institution) {
                html += `<p style="font-size: 10px; color: ${colors.muted}; margin: 0 0 4px 0;">${escapeHtml(edu.institution)}</p>`
            }
            if (edu.start_date || edu.end_date) {
                const startYear = edu.start_date ? new Date(edu.start_date).getFullYear() : ''
                const endYear = edu.end_date ? new Date(edu.end_date).getFullYear() : 'Present'
                html += `<p style="font-size: 9px; color: ${colors.muted}; margin: 0;">${startYear} - ${endYear}</p>`
            }

            html += '</div>'
        })

        html += '</div>'
    }

    // Certifications
    if (sections?.certifications !== false && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        html += `<div style="margin-bottom: 24px;">
            <h3 style="font-size: 13px; font-weight: bold; color: ${colors.accent}; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0;">Certifications</h3>`

        cvData.certifications.forEach(cert => {
            html += '<div style="margin-bottom: 12px;">'
            if (cert.name) {
                html += `<p style="font-size: 10px; font-weight: bold; color: ${colors.body}; margin: 0 0 3px 0;">${escapeHtml(cert.name)}</p>`
            }
            if (cert.issuer) {
                html += `<p style="font-size: 9px; color: ${colors.muted}; margin: 0;">${escapeHtml(cert.issuer)}</p>`
            }
            html += '</div>'
        })

        html += '</div>'
    }

    // Interests
    if (sections?.interests !== false && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        html += `<div style="margin-bottom: 24px;">
            <h3 style="font-size: 13px; font-weight: bold; color: ${colors.accent}; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0;">Interests</h3>`

        const interestNames = cvData.interests.map(i => escapeHtml(i.name)).join(', ')
        html += `<p style="font-size: 10px; color: ${colors.body}; line-height: 1.5;">${interestNames}</p>`

        html += '</div>'
    }

    html += '</div>' // End sidebar

    // ===== MAIN CONTENT (Right 70%) =====
    html += '<div>'

    // Name & Title
    if (sections?.profile !== false && profile) {
        if (profile.full_name) {
            html += `<h1 style="font-size: 32px; font-weight: bold; color: ${colors.header}; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 1px;">${escapeHtml(profile.full_name)}</h1>`
        }
        if (profile.bio) {
            html += `<p style="font-size: 14px; color: ${colors.accent}; font-style: italic; margin: 0 0 16px 0;">${escapeHtml(profile.bio)}</p>`
        }
        html += `<hr style="border: none; border-top: 3px solid ${colors.accent}; margin: 0 0 24px 0;">`
    }

    // Professional Summary
    if (sections?.summary !== false && cvData.professional_summary) {
        html += renderMainSection('Professional Summary', colors)

        if (cvData.professional_summary.description) {
            html += `<p style="font-size: 13px; color: ${colors.body}; line-height: 1.6; margin: 0 0 12px 0;">${escapeHtml(cvData.professional_summary.description)}</p>`
        }

        if (Array.isArray(cvData.professional_summary.strengths) && cvData.professional_summary.strengths.length > 0) {
            html += '<ul style="margin: 0 0 24px 0; padding-left: 20px;">'
            cvData.professional_summary.strengths.forEach(s => {
                if (s.strength) {
                    html += `<li style="font-size: 12px; color: ${colors.body}; margin-bottom: 4px; line-height: 1.5;">${escapeHtml(s.strength)}</li>`
                }
            })
            html += '</ul>'
        }
    }

    // Work Experience
    if (sections?.work !== false && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        html += renderMainSection('Experience', colors)

        cvData.work_experience.forEach(exp => {
            html += '<div style="margin-bottom: 24px;">'

            if (exp.position) {
                html += `<h3 style="font-size: 15px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(exp.position)}</h3>`
            }

            if (exp.company_name) {
                html += `<p style="font-size: 13px; color: ${colors.accent}; font-weight: 600; margin: 0 0 4px 0;">${escapeHtml(exp.company_name)}</p>`
            }

            if (!exp.hide_date && (exp.start_date || exp.end_date)) {
                const dateRange = formatDateRange(exp.start_date, exp.end_date)
                html += `<p style="font-size: 11px; color: ${colors.muted}; margin: 0 0 10px 0;">${dateRange}</p>`
            }

            if (exp.description) {
                html += `<p style="font-size: 13px; color: ${colors.body}; line-height: 1.5; margin: 0 0 10px 0;">${escapeHtml(exp.description)}</p>`
            }

            // Responsibilities
            if (Array.isArray(exp.responsibility_categories)) {
                exp.responsibility_categories.forEach(cat => {
                    if (cat.name) {
                        html += `<p style="font-size: 12px; font-weight: bold; color: ${colors.body}; margin: 10px 0 6px 0;">${escapeHtml(cat.name)}</p>`
                    }

                    if (Array.isArray(cat.items) && cat.items.length > 0) {
                        html += '<ul style="margin: 0 0 10px 0; padding-left: 20px;">'
                        cat.items.forEach(item => {
                            if (item.content) {
                                html += `<li style="font-size: 12px; color: ${colors.body}; margin-bottom: 4px; line-height: 1.4;">${escapeHtml(item.content)}</li>`
                            }
                        })
                        html += '</ul>'
                    }
                })
            }

            html += '</div>'
        })
    }

    // Projects
    if (sections?.projects !== false && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        html += renderMainSection('Projects', colors)

        cvData.projects.forEach(project => {
            html += '<div style="margin-bottom: 20px;">'

            if (project.title) {
                html += `<h3 style="font-size: 14px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(project.title)}</h3>`
            }

            if (project.start_date || project.end_date) {
                const dateRange = formatDateRange(project.start_date, project.end_date)
                html += `<p style="font-size: 11px; color: ${colors.muted}; margin: 0 0 8px 0;">${dateRange}</p>`
            }

            if (project.description) {
                html += `<p style="font-size: 12px; color: ${colors.body}; margin: 0 0 8px 0; line-height: 1.5;">${escapeHtml(project.description)}</p>`
            }

            if (project.url) {
                html += `<p style="margin: 0;"><a href="${escapeHtml(project.url)}" style="font-size: 11px; color: ${colors.link}; text-decoration: underline;">${escapeHtml(project.url)}</a></p>`
            }

            html += '</div>'
        })
    }

    // Professional Memberships
    if (sections?.memberships !== false && Array.isArray(cvData.professional_memberships) && cvData.professional_memberships.length > 0) {
        html += renderMainSection('Memberships', colors)

        cvData.professional_memberships.forEach(membership => {
            html += '<div style="margin-bottom: 16px;">'

            if (membership.organisation) {
                html += `<h3 style="font-size: 13px; font-weight: bold; color: ${colors.body}; margin: 0 0 4px 0;">${escapeHtml(membership.organisation)}</h3>`
            }

            if (membership.role) {
                html += `<p style="font-size: 12px; color: ${colors.muted}; margin: 0 0 4px 0;">${escapeHtml(membership.role)}</p>`
            }

            if (membership.start_date || membership.end_date) {
                const dateRange = formatDateRange(membership.start_date, membership.end_date)
                html += `<p style="font-size: 11px; color: ${colors.muted}; margin: 0;">${dateRange}</p>`
            }

            html += '</div>'
        })
    }

    html += '</div>' // End main content
    html += '</div>' // End grid
    html += '</div>' // End container

    container.innerHTML = html
}

/**
 * Render main section header (with left border)
 */
function renderMainSection(title, colors) {
    return `
        <div style="margin: 0 0 16px 0; padding-left: 12px; border-left: 4px solid ${colors.accent};">
            <h2 style="font-size: 16px; font-weight: bold; color: ${colors.header}; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">${title}</h2>
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

function formatDateRange(startDate, endDate) {
    const formatDate = (dateStr) => {
        if (!dateStr) return ''
        const date = new Date(dateStr)
        if (isNaN(date.getTime())) return dateStr
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const year = date.getFullYear()
        return `${month}/${year}`
    }

    const start = formatDate(startDate)
    const end = endDate ? formatDate(endDate) : 'Present'
    if (!start) return end
    return `${start} - ${end}`
}

function groupSkills(skills) {
    const grouped = {}
    if (!Array.isArray(skills)) return grouped

    skills.forEach(skill => {
        const category = skill.category || 'General'
        if (!grouped[category]) {
            grouped[category] = []
        }
        grouped[category].push(skill)
    })

    return grouped
}

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
