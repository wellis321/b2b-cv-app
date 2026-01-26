/**
 * Escapes HTML special characters.
 * @param {string} value
 * @returns {string}
 */
function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

/**
 * Formats a date string as MM/YYYY.
 * @param {string} dateStr
 * @returns {string}
 */
function formatCvPreviewDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return dateStr;
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${month}/${year}`;
}

/**
 * Renders the preview for the "Professional Blue" template.
 * Layout matches cv.php: gradient header, then two-column grid (left: Certifications, Education, Skills, Interests; right: Summary, Work, Projects, Qual Equiv, Memberships).
 * @param {HTMLElement} container
 * @param {object} context
 * @param {object} context.cvData
 * @param {object} context.profile
 * @param {object} context.sections
 * @param {boolean} context.includePhoto
 * @param {boolean} context.includeQr
 * @param {object} context.template
 */
export function render(container, { cvData, profile, sections, includePhoto, includeQr, template }) {
    if (!container) {
        console.error('Preview container not provided');
        return;
    }

    const palette = (template && template.colors) || {};
    const headingColor = palette.header || '#1f2937';
    const bodyColor = palette.body || '#374151';
    const accentColor = palette.accent || '#2563eb';
    const mutedColor = palette.muted || '#6b7280';

    const sectionBorder = (palette.accent && palette.accent !== (palette.header || ''))
        ? `2px solid ${accentColor}`
        : `${template?.sectionDivider?.width ?? 1}px solid ${template?.sectionDivider?.color || palette.divider || '#d1d5db'}`;
    const addSectionHeading = (title) => `<h2 class="text-xl font-bold mb-3 pb-2" style="color:${headingColor};border-bottom:${sectionBorder};">${title}</h2>`;

    const fromColor = profile.cv_header_from_color || '#4338ca';
    const toColor = profile.cv_header_to_color || '#7e22ce';
    const isMinimal = !!(template && template.id === 'minimal');

    let html = '';

    // --- Header ---
    // Minimal: light background, dark text, no gradient. Professional Blue: gradient like cv.php.
    if (sections.profile) {
        if (isMinimal) {
            html += `<div class="bg-white border-b border-gray-200 p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl sm:text-4xl font-bold leading-tight break-words" style="color:${headingColor}">${escapeHtml(profile.full_name || 'Your Name')}</h1>`;
            if (profile.location) {
                html += `<p class="mt-3 text-sm sm:text-base" style="color:${mutedColor}">${escapeHtml(profile.location)}</p>`;
            }
            const contactBits = [];
            if (profile.email) contactBits.push(`<a href="mailto:${escapeHtml(profile.email)}" style="color:${bodyColor}">${escapeHtml(profile.email)}</a>`);
            if (profile.phone) contactBits.push(`<span style="color:${bodyColor}">${escapeHtml(profile.phone)}</span>`);
            if (profile.linkedin_url) contactBits.push(`<a href="${escapeHtml(profile.linkedin_url)}" target="_blank" style="color:${bodyColor}">LinkedIn</a>`);
            if (contactBits.length > 0) {
                html += `<div class="flex flex-wrap gap-3 mt-4 text-xs sm:text-sm">${contactBits.join('')}</div>`;
            }
            if (profile.bio && String(profile.bio).trim()) {
                html += `<div class="mt-4 pt-4 border-t border-gray-200 text-sm sm:text-base"><p style="color:${bodyColor}" class="leading-relaxed">${escapeHtml(profile.bio)}</p></div>`;
            }
            html += `</div>`;
            if (includePhoto && profile.photo_url) {
                html += `<img src="${escapeHtml(profile.photo_url)}" alt="Profile" class="w-32 h-32 sm:w-40 sm:h-40 lg:w-48 lg:h-48 rounded-full object-cover border border-gray-200 mx-auto lg:mx-0">`;
            }
            html += `</div></div>`;
        } else {
            html += `<div style="background:linear-gradient(to right, ${escapeHtml(fromColor)}, ${escapeHtml(toColor)});" class="text-white p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl sm:text-4xl font-bold leading-tight break-words">${escapeHtml(profile.full_name || 'Your Name')}</h1>`;
            if (profile.location) {
                html += `<p class="text-white/90 mt-3 text-sm sm:text-base">${escapeHtml(profile.location)}</p>`;
            }
            const contactBits = [];
            if (profile.email) contactBits.push(`<a href="mailto:${escapeHtml(profile.email)}" class="text-white/90 hover:text-white">${escapeHtml(profile.email)}</a>`);
            if (profile.phone) contactBits.push(`<span class="text-white/90">${escapeHtml(profile.phone)}</span>`);
            if (profile.linkedin_url) contactBits.push(`<a href="${escapeHtml(profile.linkedin_url)}" target="_blank" class="text-white/90 hover:text-white">LinkedIn</a>`);
            if (contactBits.length > 0) {
                html += `<div class="flex flex-wrap gap-3 mt-4 text-xs sm:text-sm">${contactBits.join('')}</div>`;
            }
            if (profile.bio && String(profile.bio).trim()) {
                html += `<div class="mt-4 pt-4 border-t border-white/20 text-sm sm:text-base"><p class="text-white/90 leading-relaxed">${escapeHtml(profile.bio)}</p></div>`;
            }
            html += `</div>`;
            if (includePhoto && profile.photo_url) {
                html += `<img src="${escapeHtml(profile.photo_url)}" alt="Profile" class="w-32 h-32 sm:w-40 sm:h-40 lg:w-48 lg:h-48 rounded-full object-cover border-4 border-white/20 mx-auto lg:mx-0">`;
            }
            html += `</div></div>`;
        }
    }

    // --- Body: Minimal = one column; Professional Blue = two-column grid ---
    // Build each section into a variable, then assemble (one-col for minimal, two-col for professional)
    let certHtml = '', educationHtml = '', skillsHtml = '', interestsHtml = '';
    let summaryHtml = '', workHtml = '', projectsHtml = '', qualHtml = '', membershipsHtml = '';

    // Certifications
    if (sections.certifications && Array.isArray(cvData.certifications) && cvData.certifications.length > 0) {
        certHtml = '<section>' + addSectionHeading('Certifications');
        cvData.certifications.forEach((item) => {
            certHtml += `<div class="mb-3"><h3 class="font-semibold text-gray-900 text-sm">${escapeHtml(item.name)}</h3><p class="text-gray-700 text-sm">${escapeHtml(item.issuer || '')}</p><p class="text-gray-600 text-xs mt-1">${item.date_obtained ? formatCvPreviewDate(item.date_obtained) : ''}${item.expiry_date ? '<br>Expires: ' + formatCvPreviewDate(item.expiry_date) : ''}</p></div>`;
        });
        certHtml += '</section>';
    }

    // Education (Qual:, Institution:, Subject:)
    if (sections.education && Array.isArray(cvData.education) && cvData.education.length > 0) {
        educationHtml = '<section>' + addSectionHeading('Education');
        cvData.education.forEach((item) => {
            educationHtml += `<div class="mb-4"><p class="font-semibold text-gray-900 text-sm"><span class="text-gray-500 font-normal">Qual:</span> ${escapeHtml(item.degree || '')}</p><p class="text-gray-700 text-sm"><span class="text-gray-500 font-normal">Institution:</span> ${escapeHtml(item.institution || '')}</p>`;
            if (item.field_of_study) educationHtml += `<p class="text-gray-600 text-sm"><span class="text-gray-500 font-normal">Subject:</span> ${escapeHtml(item.field_of_study)}</p>`;
            if (!item.hide_date) educationHtml += `<p class="text-gray-600 text-xs mt-1">${formatCvPreviewDate(item.start_date)}${item.end_date ? ' - ' + formatCvPreviewDate(item.end_date) : ' - Present'}</p>`;
            educationHtml += '</div>';
        });
        educationHtml += '</section>';
    }

    // Skills (grouped by category)
    if (sections.skills && Array.isArray(cvData.skills) && cvData.skills.length > 0) {
        skillsHtml = '<section>' + addSectionHeading('Skills');
        const grouped = {};
        cvData.skills.forEach((s) => { const k = s.category || 'Other'; if (!grouped[k]) grouped[k] = []; grouped[k].push(s); });
        Object.keys(grouped).forEach((key) => {
            skillsHtml += `<div class="mb-3"><h3 class="font-semibold text-gray-800 text-sm mb-1">${escapeHtml(key)}:</h3><div class="flex flex-wrap gap-1.5">`;
            grouped[key].forEach((skill) => {
                skillsHtml += `<span class="bg-gray-100 px-2 py-0.5 rounded text-gray-700 text-xs">${escapeHtml(skill.name)}${skill.level ? ` <span class="text-gray-500">(${escapeHtml(skill.level)})</span>` : ''}</span>`;
            });
            skillsHtml += '</div></div>';
        });
        skillsHtml += '</section>';
    }

    // Interests (card style)
    if (sections.interests && Array.isArray(cvData.interests) && cvData.interests.length > 0) {
        interestsHtml = '<section>' + addSectionHeading('Interests & Activities') + '<div class="space-y-3">';
        cvData.interests.forEach((interest) => {
            interestsHtml += `<div class="rounded-lg border border-gray-200 bg-white/70 p-4 shadow-sm"><h3 class="text-sm font-semibold text-gray-800">${escapeHtml(interest.name)}</h3>${interest.description ? `<p class="mt-2 text-sm text-gray-600 leading-relaxed">${escapeHtml(interest.description)}</p>` : ''}</div>`;
        });
        interestsHtml += '</div></section>';
    }

    // Professional Summary
    if (sections.summary && cvData.professional_summary) {
        const s = cvData.professional_summary;
        summaryHtml = '<section>' + addSectionHeading('Professional Summary');
        if (s.description) summaryHtml += `<p class="text-gray-700 mb-3 text-sm leading-relaxed">${escapeHtml(s.description)}</p>`;
        if (s.strengths && s.strengths.length > 0) {
            summaryHtml += '<h3 class="font-semibold text-gray-800 mb-2 text-sm">Key Strengths:</h3><ul class="list-disc list-inside space-y-1 text-sm text-gray-700">';
            s.strengths.forEach((i) => { summaryHtml += `<li>${escapeHtml(i.strength)}</li>`; });
            summaryHtml += '</ul>';
        }
        summaryHtml += '</section>';
    }

    // Work Experience
    if (sections.work && Array.isArray(cvData.work_experience) && cvData.work_experience.length > 0) {
        workHtml = '<section>' + addSectionHeading('Work Experience');
        cvData.work_experience.forEach((item) => {
            workHtml += `<div class="mb-6"><div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-2"><div class="min-w-0"><h3 class="text-lg font-semibold text-gray-900">${escapeHtml(item.position)}</h3><p class="text-base text-gray-700">${escapeHtml(item.company_name)}</p></div>`;
            if (!item.hide_date) workHtml += `<div class="text-gray-600 text-sm sm:text-right whitespace-nowrap flex-shrink-0">${formatCvPreviewDate(item.start_date)}${item.end_date ? ' - ' + formatCvPreviewDate(item.end_date) : ' - Present'}</div>`;
            workHtml += '</div>';
            if (item.description) workHtml += `<p class="text-gray-700 mb-3 text-sm leading-relaxed">${escapeHtml(item.description)}</p>`;
            if (item.responsibility_categories && item.responsibility_categories.length > 0) {
                item.responsibility_categories.forEach((cat) => {
                    if (cat.items && cat.items.length) {
                        workHtml += `<div class="mb-3"><h4 class="font-semibold text-gray-800 mb-1 text-sm">${escapeHtml(cat.name)}:</h4><ul class="list-disc space-y-1 pl-5 text-sm text-gray-700">`;
                        cat.items.forEach((r) => { workHtml += `<li>${escapeHtml(r.content)}</li>`; });
                        workHtml += '</ul></div>';
                    }
                });
            }
            workHtml += '</div>';
        });
        workHtml += '</section>';
    }

    // Projects
    if (sections.projects && Array.isArray(cvData.projects) && cvData.projects.length > 0) {
        projectsHtml = '<section>' + addSectionHeading('Projects');
        cvData.projects.forEach((p) => {
            projectsHtml += `<div class="mb-4"><div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-1">`;
            projectsHtml += p.url ? `<h3 class="text-lg font-semibold text-gray-900"><a href="${escapeHtml(p.url)}" target="_blank" class="text-blue-700 hover:text-blue-900">${escapeHtml(p.title)}</a></h3>` : `<h3 class="text-lg font-semibold text-gray-900">${escapeHtml(p.title)}</h3>`;
            if (p.start_date) projectsHtml += `<div class="text-gray-600 text-sm whitespace-nowrap flex-shrink-0 sm:text-right">${formatCvPreviewDate(p.start_date)}${p.end_date ? ' - ' + formatCvPreviewDate(p.end_date) : ''}</div>`;
            projectsHtml += '</div>';
            if (p.description) projectsHtml += `<p class="text-gray-700 text-sm leading-relaxed">${escapeHtml(p.description)}</p>`;
            projectsHtml += '</div>';
        });
        projectsHtml += '</section>';
    }

    // Professional Qualification Equivalence
    if (sections.qualificationEquivalence && Array.isArray(cvData.qualification_equivalence) && cvData.qualification_equivalence.length > 0) {
        qualHtml = '<section>' + addSectionHeading('Professional Qualification Equivalence');
        cvData.qualification_equivalence.forEach((q) => {
            qualHtml += `<div class="mb-4"><h3 class="font-semibold text-gray-900 text-sm mb-1">${escapeHtml(q.level)}</h3>`;
            if (q.description) qualHtml += `<p class="text-gray-700 text-sm leading-relaxed">${escapeHtml(q.description)}</p>`;
            if (q.evidence && q.evidence.length) {
                qualHtml += '<ul class="list-disc space-y-1 pl-5 text-sm text-gray-700">';
                q.evidence.forEach((e) => { qualHtml += `<li>${escapeHtml(e.content || e)}</li>`; });
                qualHtml += '</ul>';
            }
            qualHtml += '</div>';
        });
        qualHtml += '</section>';
    }

    // Professional Memberships
    if (sections.memberships && Array.isArray(cvData.memberships) && cvData.memberships.length > 0) {
        membershipsHtml = '<section>' + addSectionHeading('Professional Memberships');
        cvData.memberships.forEach((m) => {
            membershipsHtml += `<div class="mb-3"><div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"><div class="min-w-0"><h3 class="font-semibold text-gray-900 text-sm">${escapeHtml(m.organisation)}</h3>${m.role ? `<p class="text-gray-700 text-sm">${escapeHtml(m.role)}</p>` : ''}</div><div class="text-gray-600 text-sm sm:text-right whitespace-nowrap flex-shrink-0">${formatCvPreviewDate(m.start_date)}${m.end_date ? ' - ' + formatCvPreviewDate(m.end_date) : ' - Present'}</div></div></div>`;
        });
        membershipsHtml += '</section>';
    }

    // Assemble: Minimal = one column; Professional Blue = two-column grid
    if (isMinimal) {
        const mainHtml = summaryHtml + workHtml + educationHtml + certHtml + skillsHtml + projectsHtml + qualHtml + membershipsHtml + interestsHtml;
        html += `<div class="p-6 sm:p-8"><div class="space-y-6 max-w-3xl">${mainHtml || '<div></div>'}</div></div>`;
    } else {
        const leftHtml = certHtml + educationHtml + skillsHtml + interestsHtml;
        const rightHtml = summaryHtml + workHtml + projectsHtml + qualHtml + membershipsHtml;
        html += `<div class="p-6 sm:p-8"><div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
        <div class="lg:col-span-1 space-y-6 order-2 lg:order-1">${leftHtml || '<div></div>'}</div>
        <div class="lg:col-span-2 space-y-6 order-1 lg:order-2">${rightHtml || '<div></div>'}</div>
    </div></div>`;
    }

    if (includeQr) {
        html += `<div class="p-6 pt-0 text-right"><p class="text-xs" style="color:${mutedColor};">QR code will be included in the PDF.</p></div>`;
    }

    container.innerHTML = html || '<p class="text-gray-500">Select at least one section to preview.</p>';
}
