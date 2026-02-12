// Use dynamic import with cache busting
const CACHE_BUSTER = new Date().getTime()
let templateModule = null

// Load the template module dynamically with cache busting
async function loadTemplateModule() {
    if (!templateModule) {
        templateModule = await import(`/templates/index.js?v=${CACHE_BUSTER}`)
    }
    return templateModule
}

const subscriptionContext = window.SubscriptionContext || {}
const allowedTemplateIds = new Set(subscriptionContext.allowedTemplateIds || [])
const pdfEnabled = subscriptionContext.pdfEnabled !== false

function filterTemplatesForPlan(templates) {
    if (allowedTemplateIds.size === 0) {
        return templates
    }
    return templates.filter((template) => allowedTemplateIds.has(template.id))
}

async function listTemplatesForPlan() {
    const module = await loadTemplateModule()
    return filterTemplatesForPlan(module.listTemplates())
}

async function getImageAsBase64FromBlob(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onloadend = () => resolve(reader.result)
        reader.onerror = reject
        reader.readAsDataURL(blob)
    })
}

async function getImageAsPngBase64(url) {
    try {
        if (!url) return null
        const response = await fetch(url, { credentials: 'include', mode: 'same-origin' })
        if (!response.ok) return null
        const blob = await response.blob()
        const dataUrl = await new Promise((resolve, reject) => {
            const reader = new FileReader()
            reader.onloadend = () => resolve(reader.result)
            reader.onerror = reject
            reader.readAsDataURL(blob)
        })
        if (!dataUrl || !dataUrl.startsWith('data:image/')) return null
        return new Promise((resolve) => {
            const img = new Image()
            img.onload = () => {
                const w = Math.min(img.width || 400, 400)
                const h = Math.min(img.height || 400, 400)
                const canvas = document.createElement('canvas')
                canvas.width = w
                canvas.height = h
                const ctx = canvas.getContext('2d')
                ctx.drawImage(img, 0, 0, w, h)
                const png = canvas.toDataURL('image/png')
                resolve(png ? png.replace(/\s/g, '') : null)
            }
            img.onerror = () => resolve(null)
            img.src = dataUrl
        })
    } catch (e) {
        console.error('getImageAsPngBase64:', e)
        return null
    }
}

async function getImageAsBase64(url) {
    try {
        if (!url) {
            return null
        }

        // If URL points to storage, use storage proxy
        let fetchUrl = url
        let useStorageProxy = false
        if (url.includes('/storage/')) {
            // Extract the path after /storage/ (handles both relative and absolute URLs)
            const storageMatch = url.match(/\/storage\/(.+)$/)
            if (storageMatch) {
                // Use preview-photo: converts to JPEG server-side for pdfmake compatibility
                fetchUrl = `/api/preview-photo.php?path=${encodeURIComponent(storageMatch[1])}`
                useStorageProxy = true
                console.log('Using preview-photo for image:', fetchUrl, 'Original:', url)
            }
        }

        console.log('Fetching image from:', fetchUrl)
        let response
        try {
            response = await fetch(fetchUrl, {
                credentials: 'include',
                mode: 'same-origin'
            })
        } catch (fetchError) {
            // If storage proxy fails and we haven't tried direct URL, try direct
            if (useStorageProxy && url.startsWith('http')) {
                console.warn('Storage proxy failed, trying direct URL:', fetchError)
                response = await fetch(url, {
                    credentials: 'include',
                    mode: 'cors'
                })
            } else {
                throw fetchError
            }
        }

        if (!response.ok) {
            console.error('Image fetch failed:', response.status, response.statusText, 'URL:', fetchUrl)
            throw new Error(`Failed to fetch image: ${response.status} ${response.statusText}`)
        }
        const blob = await response.blob()
        console.log('Image blob loaded, type:', blob.type, 'size:', blob.size)

        // preview-photo.php returns JPEG; pass directly to pdfmake
        if (blob.type === 'image/jpeg' || blob.type === 'image/jpg') {
            const dataUrl = await getImageAsBase64FromBlob(blob)
            if (dataUrl && dataUrl.startsWith('data:image/jpeg')) {
                // Remove any whitespace (line breaks) - pdfmake can reject malformed base64
                return dataUrl.replace(/\s/g, '')
            }
        }

        // Fallback for direct storage URLs: FileReader -> Image -> canvas -> JPEG
        const dataUrl = await getImageAsBase64FromBlob(blob)
        if (!dataUrl || !dataUrl.startsWith('data:image/')) return null

        return new Promise((resolve) => {
            const img = new Image()
            img.onload = () => {
                const w = Math.max(1, Math.min(img.width || 1, 400))
                const h = Math.max(1, Math.min(img.height || 1, 400))
                if (w <= 0 || h <= 0) {
                    resolve(null)
                    return
                }
                const canvas = document.createElement('canvas')
                canvas.width = w
                canvas.height = h
                const ctx = canvas.getContext('2d')
                ctx.drawImage(img, 0, 0, w, h)
                resolve(canvas.toDataURL('image/jpeg', 0.85))
            }
            img.onerror = () => resolve(null)
            img.src = dataUrl
        })
    } catch (error) {
        console.error('Error loading image for PDF:', error)
        return null
    }
}

async function buildDocDefinition(cvData, profile, config, templateId, cvUrl, qrCodeImage) {
    if (!pdfEnabled) {
        throw new Error('PDF export is not available for your current plan.')
    }
    const module = await loadTemplateModule()
    const targetTemplateId = templateId || module.DEFAULT_TEMPLATE_ID

    if (allowedTemplateIds.size > 0 && !allowedTemplateIds.has(targetTemplateId)) {
        throw new Error('This template is not available for your current plan.')
    }

    const pdfRenderer = module.getPdfRenderer(targetTemplateId)

    if (!pdfRenderer || typeof pdfRenderer.buildDocDefinition !== 'function') {
        throw new Error(`PDF renderer not registered for template: ${targetTemplateId}`)
    }

    // Fetch profile photo as JPEG data URL (preview-photo returns JPEG; use images dict key for pdfmake)
    const profileForTemplate = { ...profile }
    if (profile.photo_url_pdf && config?.includePhoto !== false) {
        const dataUrl = await getImageAsBase64(profile.photo_url_pdf)
        if (dataUrl && /^data:image\/(jpeg|png);base64,/.test(dataUrl)) {
            profileForTemplate.photo_base64 = dataUrl
            console.log('[PDF DEBUG] getImageAsBase64 OK:', { len: dataUrl.length, prefix: dataUrl.substring(0, 50) })
        } else {
            console.warn('[PDF DEBUG] getImageAsBase64 invalid:', { hasData: !!dataUrl, prefix: dataUrl?.substring?.(0, 60) })
        }
        delete profileForTemplate.photo_url_pdf
    }

    const builderFunction = await pdfRenderer.buildDocDefinition()
    const docDefinition = await builderFunction({
        cvData,
        profile: profileForTemplate,
        config,
        cvUrl,
        qrCodeImage,
        templateId: targetTemplateId
    })
    // Ensure images dict is set at top level (pdfmake requires dataURL in images, not inline)
    if (profileForTemplate.photo_base64 && docDefinition && /^data:image\/(jpeg|png);base64,/.test(profileForTemplate.photo_base64)) {
        docDefinition.images = docDefinition.images || {}
        docDefinition.images.profilePhoto = profileForTemplate.photo_base64
        console.log('[PDF DEBUG] Set images.profilePhoto, len:', profileForTemplate.photo_base64.length)
    } else {
        console.log('[PDF DEBUG] Skipped images.profilePhoto:', { hasPhoto: !!profileForTemplate.photo_base64, hasDD: !!docDefinition, test: profileForTemplate.photo_base64 ? /^data:image\/(jpeg|png);base64,/.test(profileForTemplate.photo_base64) : false })
    }
    return docDefinition
}

const pdfGenerator = {
    listTemplates: () => listTemplatesForPlan(),
    buildDocDefinition,
    getImageAsBase64,
    isHeaderPushedDown: (cvData) => {
        const hasProfile = !!(
            cvData &&
            cvData.profile &&
            (cvData.profile.location || cvData.profile.email || cvData.profile.phone || cvData.profile.bio)
        )
        const hasSummary = !!(
            cvData &&
            cvData.professional_summary &&
            (cvData.professional_summary.description ||
                (cvData.professional_summary.strengths && cvData.professional_summary.strengths.length))
        )
        return hasProfile && !hasSummary
    }
}

window.PdfGenerator = pdfGenerator
