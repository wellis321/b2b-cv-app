import type { Content, TDocumentDefinitions, StyleDictionary } from 'pdfmake/interfaces';
import { decodeHtmlEntities } from './validation';

// Function to convert markdown formatting to plain text for PDF
function convertMarkdownToPlainText(text: string): string {
    if (!text) return '';

    return text
        // Remove bold formatting: **text** -> text
        .replace(/\*\*(.*?)\*\*/g, '$1')
        // Remove italic formatting: *text* -> text
        .replace(/\*(.*?)\*/g, '$1')
        // Convert bullet points: • text -> • text (keep the bullet)
        .replace(/^•\s+/gm, '• ')
        // Convert dash points: - text -> - text (keep the dash)
        .replace(/^-\s+/gm, '- ');
}

export interface PdfProfile {
    id: string;
    full_name: string | null;
    email: string | null;
    phone: string | null;
    location: string | null;
    photo_url: string | null;
    linkedin_url: string | null;
    bio: string | null;
}

export interface PdfWorkExperience {
    id: string;
    company_name: string;
    position: string;
    start_date: string;
    end_date: string | null;
    description: string | null;
}

export interface PdfProject {
    id: string;
    title: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    url: string | null;
}

export interface PdfSkill {
    id: string;
    name: string;
    level: string | null;
    category: string | null;
}

export interface PdfEducation {
    id: string;
    institution: string;
    degree?: string;
    course?: string;
    start_date: string | null;
    end_date: string | null;
    description: string | null;
}

export interface PdfCertification {
    id: string;
    name: string;
    issuer: string | null;
    date_issued: string | null;
    expiry_date: string | null;
    url: string | null;
    description: string | null;
}

export interface PdfMembership {
    id: string;
    organisation: string;
    role: string | null;
    start_date: string | null;
    end_date: string | null;
    description: string | null;
}

export interface PdfInterest {
    id: string;
    name: string;
    description: string | null;
}

export interface PdfQualificationEquivalence {
    id: string;
    profile_id: string;
    level: string;
    description: string | null;
    qualification?: string;
    equivalent_to?: string;
}

export interface CvData {
    profile: PdfProfile;
    workExperiences: PdfWorkExperience[];
    projects: PdfProject[];
    skills: PdfSkill[];
    education: PdfEducation[];
    certifications?: PdfCertification[];
    memberships?: PdfMembership[];
    interests?: PdfInterest[];
    qualificationEquivalence?: PdfQualificationEquivalence[];
}

export interface PdfExportConfig {
    sections: {
        profile: boolean;
        workExperience: boolean;
        projects: boolean;
        skills: boolean;
        education: boolean;
        certifications: boolean;
        memberships: boolean;
        interests: boolean;
        qualificationEquivalence: boolean;
    };
    includePhoto: boolean;
    // Template types based on subscription
    template?: string; // 'basic' | 'professional' | 'modern' | 'creative' | 'executive'
    // Custom section order could be added in the future
    sectionOrder?: string[];
}

// Default config with all sections enabled
export const defaultPdfConfig: PdfExportConfig = {
    sections: {
        profile: true,
        workExperience: true,
        projects: true,
        skills: true,
        education: true,
        certifications: true,
        memberships: true,
        interests: true,
        qualificationEquivalence: true
    },
    includePhoto: true,
    template: 'basic'
};

/**
 * Formats a date string for display
 */
export function formatDate(dateString: string | null): string {
    if (!dateString) return 'Present';

    try {
        const date = new Date(dateString);
        return `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
    } catch (e) {
        return dateString;
    }
}

/**
 * Get template-specific styles
 */
function getTemplateStyles(template: string = 'basic'): StyleDictionary {
    // Basic template - default
    const basicStyles: StyleDictionary = {
        header: {
            fontSize: 18,
            bold: true,
            margin: [0, 0, 0, 10] as [number, number, number, number]
        },
        subheader: {
            fontSize: 14,
            bold: true,
            margin: [0, 10, 0, 5] as [number, number, number, number]
        },
        normal: {
            fontSize: 11
        },
        tableHeader: {
            bold: true,
            fontSize: 12,
            color: 'black'
        },
        jobPosition: {
            fontSize: 12,
            bold: true
        },
        company: {
            fontSize: 11,
            italics: true
        },
        dates: {
            fontSize: 11,
            alignment: 'right',
            color: '#444444'
        },
        institution: {
            fontSize: 11,
            italics: true
        },
        skills: {
            fontSize: 11
        },
        footer: {
            alignment: 'center',
            fontSize: 9,
            color: '#888888',
            margin: [0, 10, 0, 0] as [number, number, number, number]
        }
    };

    // Professional template
    if (template === 'professional') {
        return {
            ...basicStyles,
            header: {
                ...basicStyles.header,
                color: '#2c3e50',
                fontSize: 20,
                margin: [0, 0, 0, 15] as [number, number, number, number]
            },
            subheader: {
                ...basicStyles.subheader,
                color: '#3498db',
                fontSize: 15
            },
            jobPosition: {
                ...basicStyles.jobPosition,
                color: '#2c3e50'
            },
            company: {
                ...basicStyles.company,
                color: '#34495e'
            }
        };
    }

    // Modern template - sleek design with accent colors
    if (template === 'modern') {
        return {
            ...basicStyles,
            header: {
                ...basicStyles.header,
                color: '#1a237e',
                fontSize: 22,
                margin: [0, 0, 0, 12] as [number, number, number, number]
            },
            subheader: {
                ...basicStyles.subheader,
                color: '#3f51b5',
                fontSize: 16,
                bold: true
            },
            jobPosition: {
                ...basicStyles.jobPosition,
                color: '#303f9f',
                fontSize: 13,
                bold: true
            },
            company: {
                ...basicStyles.company,
                color: '#424242',
                italics: false,
                bold: false
            },
            dates: {
                ...basicStyles.dates,
                color: '#5c6bc0',
                fontSize: 10
            },
            institution: {
                ...basicStyles.institution,
                color: '#424242',
                italics: false,
                bold: false,
                fontSize: 11
            }
        };
    }

    // Executive template - sophisticated, corporate look
    if (template === 'executive') {
        return {
            ...basicStyles,
            header: {
                ...basicStyles.header,
                color: '#000000',
                fontSize: 22,
                margin: [0, 0, 0, 15] as [number, number, number, number]
            },
            subheader: {
                ...basicStyles.subheader,
                color: '#263238',
                fontSize: 16,
                margin: [0, 12, 0, 6] as [number, number, number, number]
            },
            jobPosition: {
                ...basicStyles.jobPosition,
                color: '#000000',
                fontSize: 13,
                margin: [0, 5, 0, 2] as [number, number, number, number]
            },
            company: {
                ...basicStyles.company,
                color: '#37474f',
                italics: false,
                bold: true
            },
            dates: {
                ...basicStyles.dates,
                color: '#546e7a',
                fontSize: 10,
                bold: false
            },
            institution: {
                ...basicStyles.institution,
                color: '#37474f',
                italics: false,
                bold: true
            }
        };
    }

    // Creative template - vibrant and distinctive
    if (template === 'creative') {
        return {
            ...basicStyles,
            header: {
                ...basicStyles.header,
                color: '#6200ea',
                fontSize: 24,
                margin: [0, 0, 0, 12] as [number, number, number, number]
            },
            subheader: {
                ...basicStyles.subheader,
                color: '#651fff',
                fontSize: 16,
                bold: true
            },
            jobPosition: {
                ...basicStyles.jobPosition,
                color: '#7c4dff',
                fontSize: 13,
                bold: true
            },
            company: {
                ...basicStyles.company,
                color: '#512da8',
                italics: false,
                bold: false
            },
            dates: {
                ...basicStyles.dates,
                color: '#9575cd',
                fontSize: 10
            },
            institution: {
                ...basicStyles.institution,
                color: '#512da8',
                italics: false,
                bold: false
            }
        };
    }

    // Minimal template - clean and simple
    if (template === 'minimal') {
        return {
            ...basicStyles,
            header: {
                ...basicStyles.header,
                color: '#212121',
                fontSize: 20,
                bold: true,
                margin: [0, 0, 0, 10] as [number, number, number, number]
            },
            subheader: {
                ...basicStyles.subheader,
                color: '#424242',
                fontSize: 14,
                bold: true
            },
            jobPosition: {
                ...basicStyles.jobPosition,
                color: '#212121',
                fontSize: 12,
                bold: true
            },
            company: {
                ...basicStyles.company,
                color: '#616161',
                italics: false
            },
            dates: {
                ...basicStyles.dates,
                color: '#757575',
                fontSize: 10
            },
            institution: {
                ...basicStyles.institution,
                color: '#616161',
                italics: false
            }
        };
    }

    return basicStyles;
}

/**
 * Get template-specific document definition overrides
 */
function getTemplateDocDefinition(template: string = 'basic'): Partial<TDocumentDefinitions> {
    // Basic template - default
    const basicDef: Partial<TDocumentDefinitions> = {
        pageMargins: { left: 40, top: 60, right: 40, bottom: 60 },
        defaultStyle: {
            font: 'Roboto'
        }
    };

    // Professional template - standard business look with a blue accent
    if (template === 'professional') {
        return {
            ...basicDef,
            pageMargins: { left: 40, top: 60, right: 40, bottom: 60 },
            footer: (currentPage: number, pageCount: number) => {
                return {
                    text: `${currentPage} / ${pageCount}`,
                    alignment: 'center',
                    style: 'footer'
                };
            }
        };
    }

    // Modern template - sleek design with sidebar
    if (template === 'modern') {
        return {
            ...basicDef,
            pageMargins: { left: 120, top: 60, right: 40, bottom: 60 }, // Wider left margin for side panel
            background: function () {
                return [
                    {
                        canvas: [
                            {
                                type: 'rect',
                                x: 0,
                                y: 0,
                                w: 100,
                                h: 842, // A4 height
                                color: '#e8eaf6' // Light indigo background
                            }
                        ]
                    }
                ];
            },
            footer: (currentPage: number, pageCount: number) => {
                return {
                    text: `${currentPage} / ${pageCount}`,
                    alignment: 'right',
                    style: 'footer',
                    margin: [0, 0, 40, 0]
                };
            }
        };
    }

    // Executive template - sophisticated with header line
    if (template === 'executive') {
        return {
            ...basicDef,
            pageMargins: { left: 50, top: 70, right: 50, bottom: 50 },
            header: function () {
                return {
                    canvas: [
                        {
                            type: 'line',
                            x1: 50,
                            y1: 35,
                            x2: 545,
                            y2: 35,
                            lineWidth: 3,
                            lineColor: '#263238'
                        }
                    ]
                };
            },
            footer: (currentPage: number, pageCount: number) => {
                return [
                    {
                        canvas: [
                            {
                                type: 'line',
                                x1: 50,
                                y1: 5,
                                x2: 545,
                                y2: 5,
                                lineWidth: 1,
                                lineColor: '#78909c'
                            }
                        ]
                    },
                    {
                        text: `${currentPage} / ${pageCount}`,
                        alignment: 'center',
                        style: 'footer',
                        margin: [0, 10, 0, 0]
                    }
                ];
            }
        };
    }

    // Creative template - vibrant with distinctive header
    if (template === 'creative') {
        return {
            ...basicDef,
            pageMargins: { left: 40, top: 85, right: 40, bottom: 50 },
            header: function () {
                return {
                    canvas: [
                        {
                            type: 'rect',
                            x: 0,
                            y: 0,
                            w: 595, // A4 width
                            h: 60,
                            color: '#b388ff' // Light purple header
                        }
                    ]
                };
            },
            footer: (currentPage: number, pageCount: number) => {
                return {
                    text: `${currentPage} / ${pageCount}`,
                    alignment: 'center',
                    style: 'footer'
                };
            }
        };
    }

    // Minimal template - clean and simple
    if (template === 'minimal') {
        return {
            ...basicDef,
            pageMargins: { left: 60, top: 60, right: 60, bottom: 60 },
            footer: (currentPage: number, pageCount: number) => {
                return {
                    text: `${currentPage} / ${pageCount}`,
                    alignment: 'center',
                    style: 'footer'
                };
            }
        };
    }

    return basicDef;
}

export async function createCvDocDefinition(
    cvData: CvData,
    config: PdfExportConfig = defaultPdfConfig
): Promise<TDocumentDefinitions> {
    const { profile } = cvData;

    // Get template-specific styles
    const styles = getTemplateStyles(config.template);

    // Get template-specific document definition properties
    const templateDocDef = getTemplateDocDefinition(config.template);

    // Initialize document content
    const content: Content[] = [];

    // Only add profile if enabled in config
    if (config.sections.profile) {
        // Add header with profile information
        const headerContent = [
            {
                text: profile.full_name ? decodeHtmlEntities(profile.full_name) : 'Your Name',
                style: 'header'
            }
        ];

        if (profile.location) {
            headerContent.push({ text: decodeHtmlEntities(profile.location), style: 'normal' });
        }

        if (profile.email) {
            headerContent.push({ text: decodeHtmlEntities(profile.email), style: 'normal' });
        }

        if (profile.phone) {
            headerContent.push({ text: decodeHtmlEntities(profile.phone), style: 'normal' });
        }

        if (profile.photo_url && config.includePhoto) {
            try {
                // Fetch the image
                const response = await fetch(profile.photo_url);

                // Check if the response is valid
                if (!response.ok) {
                    throw new Error(`Failed to load image: ${response.status} ${response.statusText}`);
                }

                const blob = await response.blob();

                // Check if the blob is a valid image
                if (!blob.type.startsWith('image/')) {
                    throw new Error(`Invalid image format: ${blob.type}`);
                }

                // Convert to base64
                const reader = new FileReader();
                const imgDataPromise = new Promise<string>((resolve, reject) => {
                    reader.onload = () => resolve(reader.result as string);
                    reader.onerror = reject;
                    reader.readAsDataURL(blob);
                });

                const imgData = await imgDataPromise;

                // Add header with image
                content.push({
                    columns: [
                        {
                            width: '*',
                            stack: headerContent
                        },
                        {
                            width: 'auto',
                            image: imgData,
                            fit: [70, 70],
                            alignment: 'right'
                        }
                    ]
                });
            } catch (err) {
                console.warn('Could not add profile photo to PDF:', err);
                // Continue without photo
                content.push({
                    stack: headerContent
                });
            }
        } else {
            // No photo, just add the text
            content.push({
                stack: headerContent
            });
        }

        // Add LinkedIn URL as a clickable link after the header
        if (profile.linkedin_url) {
            content.push({
                text: 'LinkedIn Profile',
                style: 'normal',
                color: '#0000EE',
                decoration: 'underline',
                link: profile.linkedin_url,
                margin: [0, 10, 0, 0]
            });
        }

        // Add bio after the header if available
        if (profile.bio && profile.bio.trim().length > 0) {
            content.push({
                text: decodeHtmlEntities(profile.bio),
                style: 'normal',
                margin: [0, 10, 0, 0]
            });
        }

        // Add separator
        content.push({
            canvas: [
                {
                    type: 'line',
                    x1: 0,
                    y1: 5,
                    x2: 515,
                    y2: 5,
                    lineWidth: 1,
                    lineColor: '#CCCCCC'
                }
            ],
            margin: [0, 10, 0, 10]
        });
    }

    // Work Experience
    if (config.sections.workExperience && cvData.workExperiences.length > 0) {
        content.push({ text: 'Work Experience', style: 'subheader' });

        for (const job of cvData.workExperiences) {
            // Work experience header with company and date
            const jobHeader = {
                columns: [
                    {
                        width: '*',
                        text: decodeHtmlEntities(job.position),
                        style: 'jobPosition'
                    },
                    {
                        width: 'auto',
                        text: `${formatDate(job.start_date)} - ${formatDate(job.end_date)}`,
                        style: 'dates'
                    }
                ]
            };

            content.push(jobHeader);

            // Company name
            content.push({
                text: decodeHtmlEntities(job.company_name),
                style: 'company',
                margin: [0, 3, 0, 3]
            });

            // Description if available
            if (job.description) {
                // Strip out Key Responsibilities section if it exists
                let descriptionText = job.description;
                if (descriptionText.includes('Key Responsibilities:')) {
                    descriptionText = descriptionText.split('Key Responsibilities:')[0].trim();
                }

                // Convert markdown formatting to plain text for PDF
                const plainTextDescription = convertMarkdownToPlainText(descriptionText);

                content.push({
                    text: decodeHtmlEntities(plainTextDescription),
                    style: 'normal',
                    margin: [0, 5, 0, 3]
                });
            }

            // Try to get responsibilities for this job
            try {
                // Dynamically import to avoid server-side issues
                const { getResponsibilitiesForExperience } = await import(
                    '../routes/work-experience/responsibilities'
                );
                const categories = await getResponsibilitiesForExperience(job.id);

                if (categories && categories.length > 0) {
                    const responsibilitiesContent: Content[] = [];

                    categories.forEach(category => {
                        if (category.items.length > 0) {
                            // Add category name
                            responsibilitiesContent.push({
                                text: decodeHtmlEntities(category.name) + ':',
                                bold: true,
                                margin: [0, 5, 0, 3]
                            });

                            // Add items as bullet points
                            const items = category.items.map(item => ({
                                text: decodeHtmlEntities(item.content),
                                margin: [0, 2, 0, 2] as [number, number, number, number]
                            }));

                            responsibilitiesContent.push({
                                ul: items
                            });
                        }
                    });

                    if (responsibilitiesContent.length > 0) {
                        content.push({
                            stack: [
                                { text: 'Key Responsibilities:', bold: true, margin: [0, 5, 0, 3] as [number, number, number, number] },
                                ...responsibilitiesContent
                            ],
                            margin: [10, 0, 0, 10] as [number, number, number, number]
                        });
                    }
                }
            } catch (err) {
                console.error('Error loading responsibilities for PDF:', err);
                // Continue without responsibilities if there's an error
            }

            // Add spacing at the end of each job
            content.push({
                text: '',
                margin: [0, 0, 0, 10]
            });
        }
    }

    // Qualification Equivalence - moved to come right after Work Experience
    if (
        config.sections.qualificationEquivalence &&
        cvData.qualificationEquivalence &&
        cvData.qualificationEquivalence.length > 0
    ) {
        content.push({ text: 'Professional Qualification Equivalence', style: 'subheader' });

        for (const qual of cvData.qualificationEquivalence) {
            content.push({
                text: decodeHtmlEntities(qual.qualification || qual.level),
                style: 'jobPosition'
            });

            if (qual.equivalent_to && qual.equivalent_to !== 'NULL') {
                content.push({
                    text: `Equivalent to: ${decodeHtmlEntities(qual.equivalent_to)}`,
                    style: 'institution',
                    margin: [0, 3, 0, 3]
                });
            }

            if (qual.description) {
                // Convert markdown formatting to plain text for PDF
                const plainTextDescription = convertMarkdownToPlainText(qual.description);

                content.push({
                    text: decodeHtmlEntities(plainTextDescription),
                    style: 'normal',
                    margin: [0, 3, 0, 15]
                });
            }
        }
    }

    // Projects
    if (config.sections.projects && cvData.projects.length > 0) {
        content.push({ text: 'Projects', style: 'subheader' });

        for (const project of cvData.projects) {
            // Project header with title and date
            const dateText = project.start_date
                ? `${formatDate(project.start_date)} - ${formatDate(project.end_date)}`
                : '';

            if (dateText) {
                content.push({
                    columns: [
                        {
                            width: '*',
                            text: decodeHtmlEntities(project.title),
                            style: 'jobPosition'
                        },
                        {
                            width: 'auto',
                            text: dateText,
                            style: 'dates'
                        }
                    ]
                });
            } else {
                content.push({
                    text: decodeHtmlEntities(project.title),
                    style: 'jobPosition'
                });
            }

            // Description if available
            if (project.description) {
                // Convert markdown formatting to plain text for PDF
                const plainTextDescription = convertMarkdownToPlainText(project.description);

                content.push({
                    text: decodeHtmlEntities(plainTextDescription),
                    style: 'normal',
                    margin: [0, 5, 0, 5]
                });
            }

            // URL if available
            if (project.url) {
                content.push({
                    text: decodeHtmlEntities(project.url),
                    style: 'normal',
                    color: '#0000EE',
                    decoration: 'underline',
                    margin: [0, 5, 0, 5]
                });
            }

            // Add some space between project entries
            content.push({ text: '', margin: [0, 0, 0, 10] });
        }
    }

    // Skills
    if (config.sections.skills && cvData.skills.length > 0) {
        content.push({ text: 'Skills', style: 'subheader' });

        // Group skills by category
        const skillsByCategory: Record<string, PdfSkill[]> = {};
        for (const skill of cvData.skills) {
            const category = skill.category || 'Other';
            if (!skillsByCategory[category]) {
                skillsByCategory[category] = [];
            }
            skillsByCategory[category].push(skill);
        }

        // Add skills by category
        for (const category in skillsByCategory) {
            if (Object.prototype.hasOwnProperty.call(skillsByCategory, category)) {
                const skills = skillsByCategory[category];

                if (skills.length > 0) {
                    // Only add category header if not 'Other' or if there are multiple categories
                    if (category !== 'Other' || Object.keys(skillsByCategory).length > 1) {
                        content.push({
                            text: decodeHtmlEntities(category),
                            bold: true,
                            margin: [0, 5, 0, 3]
                        });
                    }

                    // Format skills with level if available
                    const skillTexts = skills.map((skill) => {
                        const skillName = decodeHtmlEntities(skill.name);
                        return skill.level
                            ? `${skillName} (${decodeHtmlEntities(skill.level)})`
                            : skillName;
                    });

                    content.push({
                        text: skillTexts.join(', '),
                        style: 'skills',
                        margin: [0, 0, 0, 5]
                    });
                }
            }
        }
    }

    // Education
    if (config.sections.education && cvData.education.length > 0) {
        content.push({ text: 'Education', style: 'subheader' });

        for (const edu of cvData.education) {
            // Education header with institution and date
            content.push({
                columns: [
                    {
                        width: '*',
                        text: decodeHtmlEntities(edu.institution),
                        style: 'institution'
                    },
                    {
                        width: 'auto',
                        text: `${formatDate(edu.start_date)} - ${formatDate(edu.end_date)}`,
                        style: 'dates'
                    }
                ]
            });

            // Degree/qualification
            const degreeText = edu.degree || edu.course || '';
            if (degreeText) {
                content.push({
                    text: decodeHtmlEntities(degreeText),
                    style: 'jobPosition',
                    margin: [0, 3, 0, 3]
                });
            }

            // Description if available
            if (edu.description) {
                // Convert markdown formatting to plain text for PDF
                const plainTextDescription = convertMarkdownToPlainText(edu.description);

                content.push({
                    text: decodeHtmlEntities(plainTextDescription),
                    style: 'normal',
                    margin: [0, 5, 0, 5]
                });
            }

            // Add some space between education entries
            content.push({ text: '', margin: [0, 0, 0, 10] });
        }
    }

    // Certifications
    console.log('CERTIFICATIONS CHECK:');
    console.log('- config.sections.certifications =', config.sections.certifications);
    console.log('- cvData.certifications exists =', !!cvData.certifications);
    console.log(
        '- cvData.certifications is array =',
        cvData.certifications ? Array.isArray(cvData.certifications) : false
    );
    console.log(
        '- cvData.certifications length =',
        cvData.certifications
            ? Array.isArray(cvData.certifications)
                ? cvData.certifications.length
                : 'not array'
            : 'undefined'
    );
    if (cvData.certifications && cvData.certifications.length > 0) {
        console.log('- Sample certification:', JSON.stringify(cvData.certifications[0]));
    }

    // Fix for potential issues with certifications array
    const certsToProcess =
        cvData.certifications && Array.isArray(cvData.certifications)
            ? cvData.certifications.filter((c) => c && typeof c === 'object' && c.name)
            : [];
    console.log('- Valid certifications to process:', certsToProcess.length);

    if (config.sections.certifications && certsToProcess.length > 0) {
        console.log('Adding certifications to PDF:', certsToProcess);
        content.push({ text: 'Certifications', style: 'subheader' });

        certsToProcess.forEach((cert) => {
            try {
                console.log('Processing certification:', cert);

                // Validate cert has the required 'name' field
                if (!cert || !cert.name) {
                    console.warn('Invalid certification data found, skipping', cert);
                    return;
                }

                // Check date formatting
                let dateText = '';
                if (cert.date_issued) {
                    try {
                        dateText = `Issued: ${formatDate(cert.date_issued)}`;
                    } catch (err) {
                        console.warn('Error formatting date_issued:', err);
                        dateText = cert.date_issued;
                    }
                }

                // Certification header
                const certHeader = {
                    columns: [
                        {
                            width: '*',
                            text: decodeHtmlEntities(cert.name),
                            style: 'jobPosition'
                        },
                        {
                            width: 'auto',
                            text: dateText,
                            style: 'dates'
                        }
                    ]
                };

                content.push(certHeader);

                // Issuer
                if (cert.issuer) {
                    content.push({
                        text: decodeHtmlEntities(cert.issuer),
                        style: 'institution',
                        margin: [0, 3, 0, 3]
                    });
                }

                // URL if available
                if (cert.url) {
                    content.push({
                        text: decodeHtmlEntities(cert.url),
                        style: 'company',
                        margin: [0, 3, 0, 3],
                        color: '#3366cc',
                        decoration: 'underline'
                    });
                }

                // Description if available
                if (cert.description) {
                    // Convert markdown formatting to plain text for PDF
                    const plainTextDescription = convertMarkdownToPlainText(cert.description);

                    content.push({
                        text: decodeHtmlEntities(plainTextDescription),
                        style: 'normal',
                        margin: [0, 5, 0, 10] as [number, number, number, number]
                    });
                } else {
                    // Add spacing if no description
                    content.push({
                        text: '',
                        margin: [0, 0, 0, 10] as [number, number, number, number]
                    });
                }
            } catch (err) {
                console.error('Error processing certification in PDF:', err, cert);
                // Continue with next certification
            }
        });
    } else {
        console.log(
            'Certifications section conditions not met:',
            'config.sections.certifications:',
            config.sections.certifications,
            'cvData.certifications exists:',
            !!cvData.certifications,
            'cvData.certifications length (if exists):',
            cvData.certifications ? cvData.certifications.length : 0
        );
    }

    // Professional Memberships
    if (config.sections.memberships && cvData.memberships && cvData.memberships.length > 0) {
        content.push({ text: 'Professional Memberships', style: 'subheader' });

        for (const membership of cvData.memberships) {
            // Membership header with organization and date
            const dateText = membership.start_date
                ? `${formatDate(membership.start_date)} - ${formatDate(membership.end_date)}`
                : '';

            if (dateText) {
                content.push({
                    columns: [
                        {
                            width: '*',
                            text: decodeHtmlEntities(membership.organisation),
                            style: 'jobPosition'
                        },
                        {
                            width: 'auto',
                            text: dateText,
                            style: 'dates'
                        }
                    ]
                });
            } else {
                content.push({
                    text: decodeHtmlEntities(membership.organisation),
                    style: 'jobPosition'
                });
            }

            // Role if available
            if (membership.role) {
                content.push({
                    text: decodeHtmlEntities(membership.role),
                    style: 'company',
                    margin: [0, 3, 0, 3]
                });
            }

            // Description if available
            if (membership.description) {
                // Convert markdown formatting to plain text for PDF
                const plainTextDescription = convertMarkdownToPlainText(membership.description);

                content.push({
                    text: decodeHtmlEntities(plainTextDescription),
                    style: 'normal',
                    margin: [0, 5, 0, 5]
                });
            }

            // Add some space between membership entries
            content.push({ text: '', margin: [0, 0, 0, 10] });
        }
    }

    // Interests
    if (config.sections.interests && cvData.interests && cvData.interests.length > 0) {
        content.push({ text: 'Interests & Activities', style: 'subheader' });

        for (const interest of cvData.interests) {
            content.push({
                text: decodeHtmlEntities(interest.name),
                style: 'jobPosition'
            });

            // Description if available
            if (interest.description) {
                // Convert markdown formatting to plain text for PDF
                const plainTextDescription = convertMarkdownToPlainText(interest.description);

                content.push({
                    text: decodeHtmlEntities(plainTextDescription),
                    style: 'normal',
                    margin: [0, 5, 0, 5]
                });
            }

            // Add some space between interest entries
            content.push({ text: '', margin: [0, 0, 0, 10] });
        }
    }

    // Combine template properties with standard document properties
    return {
        // Template-specific properties
        ...templateDocDef,

        // Standard document properties
        content: content,
        styles: styles,
        defaultStyle: {
            font: 'Roboto',
            fontSize: 11
        },
        info: {
            title: `CV - ${profile.full_name || 'CV'}`,
            author: profile.full_name || 'CV App User',
            subject: 'CV',
            keywords: 'CV, Resume'
        },
        pageSize: 'A4',

        // Footer (if not already defined by template)
        footer: templateDocDef.footer || function (currentPage: number, pageCount: number) {
            return {
                text: `CV created with CV App by William and Max Ellis | Page ${currentPage} of ${pageCount}`,
                style: 'footer',
                margin: [40, 0, 40, 0] as [number, number, number, number]
            };
        }
    };
}

/**
 * Generates and downloads a PDF CV
 */
export async function generateCvPdf(
    cvData: CvData,
    config: PdfExportConfig = defaultPdfConfig
): Promise<void> {
    // Log the received configuration
    console.log('PDF Generator received config:', JSON.stringify(config, null, 2));

    // Normalize certification data to ensure correct field mappings
    if (
        cvData.certifications &&
        Array.isArray(cvData.certifications) &&
        cvData.certifications.length > 0
    ) {
        cvData.certifications = cvData.certifications.map((cert: any) => {
            // Ensure all required fields exist with proper mappings
            return {
                id: cert.id || '',
                name: cert.name || 'Unnamed Certification',
                issuer: cert.issuer || null,
                // Use date_issued if available, fall back to date_obtained
                date_issued: cert.date_issued || cert.date_obtained || null,
                expiry_date: cert.expiry_date || null,
                url: cert.url || null,
                description: cert.description || null
            };
        });
    }

    // Specifically check certifications data and config
    console.log('Certifications config enabled:', config.sections.certifications);
    console.log('Certifications data exists:', !!cvData.certifications);
    console.log('Certifications data array?', Array.isArray(cvData.certifications));
    console.log(
        'Certifications data length:',
        cvData.certifications ? cvData.certifications.length : 0
    );

    if (config.sections.certifications && cvData.certifications && cvData.certifications.length > 0) {
        console.log('First certification in data:', JSON.stringify(cvData.certifications[0], null, 2));
    }

    // Import pdfmake
    const pdfMake = await import('pdfmake/build/pdfmake');
    const pdfFonts = await import('pdfmake/build/vfs_fonts');

    // Set virtual font directory - this is the correct way to assign fonts
    pdfMake.default.vfs = pdfFonts.default.pdfmake ? pdfFonts.default.pdfmake.vfs : pdfFonts.default;

    // Create document definition with config
    const docDefinition = await createCvDocDefinition(cvData, config);

    // Generate and download the PDF
    pdfMake.default.createPdf(docDefinition).download(`${cvData.profile.full_name || 'CV'}.pdf`);
}
