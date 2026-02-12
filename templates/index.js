// Use cache buster for dynamic imports
const CACHE_BUSTER = new Date().getTime()

// Lazy-loaded modules
let professionalBluePreview = null
let professionalBluePdf = null
let classicPreview = null
let classicPdf = null
let modernPreview = null
let modernPdf = null
let structuredPreview = null
let structuredPdf = null

// Dynamic imports with cache busting
async function loadProfessionalBluePreview() {
    if (!professionalBluePreview) {
        const module = await import(`./default/preview.js?v=${CACHE_BUSTER}`)
        professionalBluePreview = module.render
    }
    return professionalBluePreview
}

async function loadProfessionalBluePdf() {
    if (!professionalBluePdf) {
        const module = await import(`./default/pdf.js?v=${CACHE_BUSTER}`)
        professionalBluePdf = module.buildDocDefinition
    }
    return professionalBluePdf
}

async function loadClassicPreview() {
    if (!classicPreview) {
        const module = await import(`./classic/preview.js?v=${CACHE_BUSTER}`)
        classicPreview = module.render
    }
    return classicPreview
}

async function loadClassicPdf() {
    if (!classicPdf) {
        const module = await import(`./classic/pdf.js?v=${CACHE_BUSTER}`)
        classicPdf = module.buildDocDefinition
    }
    return classicPdf
}

async function loadModernPreview() {
    if (!modernPreview) {
        const module = await import(`./modern/preview.js?v=${CACHE_BUSTER}`)
        modernPreview = module.render
    }
    return modernPreview
}

async function loadModernPdf() {
    if (!modernPdf) {
        const module = await import(`./modern/pdf.js?v=${CACHE_BUSTER}`)
        modernPdf = module.buildDocDefinition
    }
    return modernPdf
}

async function loadStructuredPreview() {
    if (!structuredPreview) {
        const module = await import(`./structured/preview.js?v=${CACHE_BUSTER}`)
        structuredPreview = module.render
    }
    return structuredPreview
}

async function loadStructuredPdf() {
    if (!structuredPdf) {
        const module = await import(`./structured/pdf.js?v=${CACHE_BUSTER}`)
        structuredPdf = module.buildDocDefinition
    }
    return structuredPdf
}

const DEFAULT_TEMPLATE_ID = 'professional'

const templateRegistry = {
    professional: {
        id: 'professional',
        name: 'Professional Blue',
        description: 'Clean layout with blue accent accents and structured typography.',
        colors: {
            header: '#1f2937',
            body: '#374151',
            accent: '#2563eb',
            muted: '#6b7280',
            divider: '#d1d5db',
            link: '#2563eb'
        },
        sectionDivider: {
            color: '#d1d5db',
            width: 1,
            margin: [0, 4, 0, 6]
        },
        preview: {
            render: loadProfessionalBluePreview
        },
        pdf: {
            buildDocDefinition: loadProfessionalBluePdf
        }
    },
    minimal: {
        id: 'minimal',
        name: 'Minimal',
        description: 'Simplified monochrome layout with understated section dividers.',
        colors: {
            header: '#111827',
            body: '#374151',
            accent: '#111827',
            muted: '#6b7280',
            divider: '#d1d5db',
            link: '#1f2937'
        },
        sectionDivider: {
            color: '#e5e7eb',
            width: 0.75,
            margin: [0, 6, 0, 10]
        },
        preview: {
            render: loadProfessionalBluePreview
        },
        pdf: {
            buildDocDefinition: loadProfessionalBluePdf
        }
    },
    classic: {
        id: 'classic',
        name: 'Classic',
        description: 'Traditional format with navy accents, ideal for academia and government.',
        colors: {
            header: '#1e3a8a',
            body: '#475569',
            accent: '#1e3a8a',
            muted: '#64748b',
            divider: '#1e3a8a',
            link: '#1e40af'
        },
        sectionDivider: {
            color: '#1e3a8a',
            width: 1,
            margin: [0, 6, 0, 10]
        },
        preview: {
            render: loadClassicPreview
        },
        pdf: {
            buildDocDefinition: loadClassicPdf
        }
    },
    structured: {
        id: 'structured',
        name: 'Structured',
        description: 'Clean professional layout with light blue accents, skills grouped by category, and Career Highlights.',
        colors: {
            header: '#1e3a8a',
            body: '#374151',
            accent: '#0ea5e9',
            muted: '#64748b',
            divider: '#7dd3fc',
            link: '#0284c7'
        },
        sectionDivider: {
            color: '#7dd3fc',
            width: 2,
            margin: [0, 4, 0, 8]
        },
        preview: {
            render: loadStructuredPreview
        },
        pdf: {
            buildDocDefinition: loadStructuredPdf
        }
    },
    modern: {
        id: 'modern',
        name: 'Modern',
        description: 'Two-column sidebar design with teal accents for tech professionals.',
        colors: {
            header: '#0f172a',
            body: '#334155',
            accent: '#0d9488',
            muted: '#64748b',
            divider: '#e2e8f0',
            link: '#0891b2'
        },
        sectionDivider: {
            color: '#0d9488',
            width: 3,
            margin: [0, 8, 0, 12]
        },
        preview: {
            render: loadModernPreview
        },
        pdf: {
            buildDocDefinition: loadModernPdf
        }
    }
}

export function getTemplateMeta(templateId) {
    return templateRegistry[templateId] || templateRegistry[DEFAULT_TEMPLATE_ID]
}

export function getPreviewRenderer(templateId) {
    const meta = getTemplateMeta(templateId)
    return meta ? meta.preview : null
}

export function getPdfRenderer(templateId) {
    const meta = getTemplateMeta(templateId)
    return meta ? meta.pdf : null
}

export function listTemplates() {
    return Object.values(templateRegistry).map(({ id, name, description }) => ({ id, name, description }))
}

export { DEFAULT_TEMPLATE_ID }
