import { render as renderProfessionalBluePreview } from './default/preview.js'
import { buildDocDefinition as buildProfessionalBluePdf } from './default/pdf.js'
import { render as renderClassicPreview } from './classic/preview.js'
import { buildDocDefinition as buildClassicPdf } from './classic/pdf.js'
import { render as renderModernPreview } from './modern/preview.js'
import { buildDocDefinition as buildModernPdf } from './modern/pdf.js'

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
            render: renderProfessionalBluePreview
        },
        pdf: {
            buildDocDefinition: buildProfessionalBluePdf
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
            render: renderProfessionalBluePreview
        },
        pdf: {
            buildDocDefinition: buildProfessionalBluePdf
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
            render: renderClassicPreview
        },
        pdf: {
            buildDocDefinition: buildClassicPdf
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
            render: renderModernPreview
        },
        pdf: {
            buildDocDefinition: buildModernPdf
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
