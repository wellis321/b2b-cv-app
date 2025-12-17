<?php
/**
 * CV Preview & PDF Generation Page
 * Allows users to select sections and generate PDF with QR code
 */

require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cv-data.php';

requireAuth();

$userId = getUserId();

// Load CV data
$cvData = loadCvData($userId);
$profile = $cvData['profile'];

// Ensure default visibility flags are present
if ($profile) {
    $profile['show_photo'] = $profile['show_photo'] ?? 1;
    $profile['show_photo_pdf'] = $profile['show_photo_pdf'] ?? 1;
    $profile['show_qr_code'] = $profile['show_qr_code'] ?? ($profile['show_photo'] ? 0 : 1);
}

// Format date helper
function formatCvDate($date, $format = 'dd/mm/yyyy') {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;

    switch ($format) {
        case 'mm/dd/yyyy':
            return date('m/d/Y', $timestamp);
        case 'yyyy-mm-dd':
            return date('Y-m-d', $timestamp);
        case 'dd/mm/yyyy':
        default:
            return date('d/m/Y', $timestamp);
    }
}

$dateFormat = $profile['date_format_preference'] ?? 'dd/mm/yyyy';
$cvUrl = APP_URL . '/cv/@' . $profile['username'];
$profileShowPhotoCv = $profile['show_photo'] ?? 1;
$profileShowPhotoPdf = $profile['show_photo_pdf'] ?? 1;
$profileShowQrCode = $profile['show_qr_code'] ?? ($profileShowPhotoCv ? 0 : 1);
$profileShowQrCodePdfDefault = $profileShowPhotoPdf ? $profileShowQrCode : 1;

$subscriptionContext = getUserSubscriptionContext($userId);
$subscriptionFrontendContext = buildSubscriptionFrontendContext($subscriptionContext);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview & PDF - CV Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.addEventListener('load', function() {
            if (typeof QRCode === 'undefined') {
                console.warn('QRCode library not available after page load')
            }
        })
    </script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.min.js"></script>
    <script type="module" src="/js/pdf-generator.js?v=<?php echo time(); ?>"></script>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Preview & Generate PDF</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Section Selection Panel -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Select Sections</h2>

                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" id="section-profile" checked class="mr-2">
                            <span>Personal Profile</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-summary" checked class="mr-2">
                            <span>Professional Summary</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-work" checked class="mr-2">
                            <span>Work Experience</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-education" checked class="mr-2">
                            <span>Education</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-skills" checked class="mr-2">
                            <span>Skills</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-projects" checked class="mr-2">
                            <span>Projects</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-certifications" checked class="mr-2">
                            <span>Certifications</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-memberships" checked class="mr-2">
                            <span>Professional Memberships</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="section-interests" checked class="mr-2">
                            <span>Interests & Activities</span>
                        </label>
                    </div>

                    <div class="mt-6 pt-6 border-t">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   id="include-photo"
                                   class="mr-2"
                                   <?php echo $profileShowPhotoPdf ? 'checked' : ''; ?>>
                            <span>Include Profile Photo</span>
                        </label>
                        <p class="mt-2 text-xs text-gray-500">
                            This sets the default when you first open the preview. You can still toggle the photo before generating the PDF.
                        </p>
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   id="include-qr"
                                   class="mr-2"
                                   <?php echo $profileShowQrCodePdfDefault ? 'checked' : ''; ?>>
                            <span>Include QR Code</span>
                        </label>
                        <p class="mt-2 text-xs text-gray-500">
                            The QR code will appear in the header if the photo is hidden; otherwise it is placed at the bottom of the PDF.
                        </p>
                    </div>

                    <div class="mt-6 pt-6 border-t">
                        <label for="template-select" class="block text-sm font-medium text-gray-700 mb-2">
                            PDF Style
                        </label>
                        <select id="template-select" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3">
                            <option value="">Loading templates...</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500" id="template-description">
                            Clean layout with blue accent lines and structured typography.
                        </p>
                        <?php if (!empty($subscriptionFrontendContext['allowedTemplateIds']) && count($subscriptionFrontendContext['allowedTemplateIds']) === 1): ?>
                            <p class="mt-2 text-xs text-gray-500">
                                Upgrade to unlock additional template designs and colour themes.
                            </p>
                        <?php endif; ?>
                    </div>

                    <button id="generate-pdf-button" onclick="generatePDF()" class="mt-6 w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Generate PDF
                    </button>
                    <?php if (!planPdfEnabled($subscriptionContext)): ?>
                        <p class="mt-3 text-sm text-gray-500">
                            PDF downloads are available on Pro plans.
                            <a href="/subscription.php" class="text-blue-600 hover:text-blue-800 underline">Upgrade now</a>.
                        </p>
                    <?php endif; ?>

                    <a href="/cv.php" class="mt-4 block text-center text-blue-600 hover:text-blue-800">
                        View Online CV →
                    </a>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">CV Preview</h2>
                    <div id="cv-preview" class="border border-gray-200 p-8 bg-white min-h-[600px]">
                        <!-- Preview will be rendered here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

        <script type="module">
        import { DEFAULT_TEMPLATE_ID, getTemplateMeta, getPreviewRenderer, listTemplates } from '/templates/index.js?v=<?php echo time(); ?>';

        <?php
        // Helper to decode HTML entities recursively in arrays
        function decodeEntitiesRecursive($data) {
            if (is_array($data)) {
                return array_map('decodeEntitiesRecursive', $data);
            } elseif (is_string($data)) {
                return html_entity_decode($data, ENT_QUOTES, 'UTF-8');
            }
            return $data;
        }
        $cvDataDecoded = decodeEntitiesRecursive($cvData);
        $profileDecoded = decodeEntitiesRecursive($profile);
        ?>
        const SubscriptionContext = <?php echo json_encode($subscriptionFrontendContext, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        window.SubscriptionContext = SubscriptionContext;
        const allowedTemplateIds = new Set(SubscriptionContext?.allowedTemplateIds || []);
        const cvData = <?php echo json_encode($cvDataDecoded, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const profile = <?php echo json_encode($profileDecoded, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const dateFormat = <?php echo json_encode($dateFormat, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const cvUrl = <?php echo json_encode($cvUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        let selectedTemplate = SubscriptionContext?.defaultTemplateId || DEFAULT_TEMPLATE_ID;

        function updateTemplateDescription(templateId) {
            const template = getTemplateMeta(templateId);
            const descEl = document.getElementById('template-description');
            if (descEl) {
                descEl.textContent = template && template.description ? template.description : '';
            }
        }

        // Debug: Check if data is loaded
        console.log('CV Data loaded:', cvData);
        console.log('Profile loaded:', profile);
        console.log('Profile show_photo value:', profile.show_photo, 'Type:', typeof profile.show_photo);
        console.log('Profile show_photo_pdf value:', profile.show_photo_pdf, 'Type:', typeof profile.show_photo_pdf);
        console.log('Profile show_qr_code value:', profile.show_qr_code, 'Type:', typeof profile.show_qr_code);
        console.log('Subscription context:', SubscriptionContext);

        function loadQRCodeLibrary() {
            return new Promise((resolve, reject) => {
                if (typeof QRCode !== 'undefined' || typeof window.QRCode !== 'undefined') {
                    console.log('QRCode library already loaded');
                    resolve();
                    return;
                }

                const existingScript = document.querySelector('script[src*="qrcode"]');
                if (existingScript) {
                    console.log('QRCode script tag found, waiting for load...');
                    let attempts = 0;
                    const checkInterval = setInterval(() => {
                        attempts++;
                        if (typeof QRCode !== 'undefined' || typeof window.QRCode !== 'undefined') {
                            clearInterval(checkInterval);
                            console.log('QRCode library loaded after waiting');
                            resolve();
                        } else if (attempts > 20) {
                            clearInterval(checkInterval);
                            console.warn('QRCode library still not loaded after waiting');
                            loadScript();
                        }
                    }, 100);
                    return;
                }

                function loadScript() {
                    console.log('Loading QRCode library dynamically...');
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js';
                    script.onload = () => {
                        console.log('QRCode library loaded dynamically');
                        setTimeout(() => {
                            if (typeof QRCode !== 'undefined' || typeof window.QRCode !== 'undefined') {
                                resolve();
                            } else {
                                console.warn('QRCode library script loaded but QRCode not available');
                                console.log('Checking window object for QRCode:', Object.keys(window).filter((k) => k.toLowerCase().includes('qr')));
                                reject(new Error('QRCode library loaded but not available'));
                            }
                        }, 100);
                    };
                    script.onerror = () => {
                        console.error('Failed to load QRCode library from unpkg');
                        reject(new Error('Failed to load QRCode library'));
                    };
                    document.head.appendChild(script);
                }

                loadScript();
            });
        }

        function getSections() {
            return {
                profile: document.getElementById('section-profile')?.checked ?? true,
                summary: document.getElementById('section-summary')?.checked ?? true,
                work: document.getElementById('section-work')?.checked ?? true,
                education: document.getElementById('section-education')?.checked ?? true,
                skills: document.getElementById('section-skills')?.checked ?? true,
                projects: document.getElementById('section-projects')?.checked ?? true,
                certifications: document.getElementById('section-certifications')?.checked ?? true,
                memberships: document.getElementById('section-memberships')?.checked ?? true,
                interests: document.getElementById('section-interests')?.checked ?? true
            };
        }

        function getSelectedTemplate() {
            const templateSelect = document.getElementById('template-select');
            const candidate = templateSelect && templateSelect.value ? templateSelect.value : selectedTemplate || DEFAULT_TEMPLATE_ID;
            if (allowedTemplateIds.size > 0 && !allowedTemplateIds.has(candidate)) {
                return selectedTemplate;
            }
            return candidate;
        }

        async function generatePDF() {
            try {
                console.log('🎨 Using client-side PDF generation with pdfmake');

                if (!SubscriptionContext?.pdfEnabled) {
                    const message = 'PDF export is available on Pro plans.';
                    if (SubscriptionContext?.upgradeUrl) {
                        if (confirm(message + ' View upgrade options now?')) {
                            window.location.href = SubscriptionContext.upgradeUrl;
                        }
                    } else {
                        alert(message);
                    }
                    return;
                }

                if (!cvData || !profile) {
                    alert('Error: CV data not loaded. Please refresh the page and try again.');
                    console.error('CV data or profile not loaded');
                    return;
                }

                if (typeof pdfMake === 'undefined') {
                    throw new Error('PDF library not loaded. Please refresh the page.');
                }

                // Show loading state
                const button = document.getElementById('generate-pdf-button');
                const originalText = button?.textContent;
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Generating PDF...';
                }

                // Get selected sections and map to PDF template format
                const sectionsObj = getSections();
                const qualificationsCheckbox = document.getElementById('section-qualifications');
                const sections = {
                    profile: sectionsObj.profile,
                    professionalSummary: sectionsObj.summary,
                    summary: sectionsObj.summary,
                    workExperience: sectionsObj.work,
                    work: sectionsObj.work,
                    education: sectionsObj.education,
                    skills: sectionsObj.skills,
                    projects: sectionsObj.projects,
                    certifications: sectionsObj.certifications,
                    qualificationEquivalence: qualificationsCheckbox ? qualificationsCheckbox.checked : false,
                    memberships: sectionsObj.memberships,
                    interests: sectionsObj.interests
                };

                // Get include photo and QR code settings
                const includePhoto = document.getElementById('include-photo')?.checked ?? true;
                const includeQr = document.getElementById('include-qr')?.checked ?? true;

                // Get selected template
                const selectedTemplate = getSelectedTemplate();

                // Generate QR code if needed
                let qrCodeImage = null;
                if (includeQr && cvUrl) {
                    try {
                        const qrDiv = document.createElement('div');
                        qrDiv.style.cssText = 'position: absolute; left: -9999px;';
                        document.body.appendChild(qrDiv);

                        let QRCodeLib = typeof QRCode !== 'undefined' ? QRCode : window.QRCode;
                        if (QRCodeLib) {
                            new QRCodeLib(qrDiv, {
                                text: cvUrl,
                                width: 200,
                                height: 200,
                                colorDark: '#000000',
                                colorLight: '#FFFFFF'
                            });

                            // Wait a moment for QR code to render
                            await new Promise(resolve => setTimeout(resolve, 100));

                            // Get the canvas element
                            const qrCanvas = qrDiv.querySelector('canvas');
                            if (qrCanvas) {
                                qrCodeImage = qrCanvas.toDataURL('image/png');
                                console.log('QR code generated');
                            }
                        }

                        document.body.removeChild(qrDiv);
                    } catch (qrError) {
                        console.warn('QR code generation failed:', qrError);
                    }
                }

                // Get profile photo as base64 if needed
                let photoBase64 = null;
                if (includePhoto && profile.photo_url) {
                    try {
                        photoBase64 = await window.PdfGenerator.getImageAsBase64(profile.photo_url);
                        if (!photoBase64) {
                            console.warn('Failed to load profile photo, continuing without it');
                        }
                    } catch (error) {
                        console.error('Error loading profile photo:', error);
                        // Continue without photo
                    }
                }

                // Prepare config
                const config = {
                    sections: sections,
                    includePhoto: includePhoto,
                    includeQRCode: includeQr
                };

                // Build document definition using the template system
                // Only include photo_base64 if we successfully loaded it
                const profileWithPhoto = { ...profile }
                if (photoBase64) {
                    profileWithPhoto.photo_base64 = photoBase64
                } else {
                    // Remove photo_base64 if it wasn't loaded successfully
                    delete profileWithPhoto.photo_base64
                }

                const docDefinition = await window.PdfGenerator.buildDocDefinition(
                    cvData,
                    profileWithPhoto,
                    config,
                    selectedTemplate,
                    cvUrl,
                    qrCodeImage
                );

                // Generate and download PDF
                pdfMake.createPdf(docDefinition).download(
                    `${(profile.full_name || 'CV').replace(/[^a-z0-9_\-]/gi, '_')}_CV.pdf`
                );

                console.log('✅ PDF generated successfully using pdfmake');

                // Restore button
                if (button) {
                    button.disabled = false;
                    button.textContent = originalText;
                }

            } catch (error) {
                console.error('PDF generation error:', error);
                alert('Error generating PDF: ' + (error?.message || 'Unknown error'));

                // Restore button on error
                const button = document.getElementById('generate-pdf-button');
                if (button) {
                    button.disabled = false;
                    button.textContent = 'Generate PDF';
                }
            }
        }

        async function renderPreview() {
            try {
                const previewDiv = document.getElementById('cv-preview');
                if (!previewDiv) {
                    console.error('Preview div not found');
                    return;
                }

                if (!cvData || !profile) {
                    previewDiv.innerHTML = '<p class="text-red-600">Error: CV data not loaded. Please refresh the page.</p>';
                    return;
                }

                selectedTemplate = getSelectedTemplate();
                const sections = getSections();
                const includePhoto = document.getElementById('include-photo')?.checked ?? true;
                const includeQr = document.getElementById('include-qr')?.checked ?? true;

                const templateMeta = getTemplateMeta(selectedTemplate);
                const previewRenderer = getPreviewRenderer(selectedTemplate);

                if (!previewRenderer || typeof previewRenderer.render !== 'function') {
                    console.warn('Preview renderer not available for template:', selectedTemplate);
                    previewDiv.innerHTML = '<p class="text-red-600">Preview not available for the selected template.</p>';
                    return;
                }

                // Load the actual render function (async loader)
                const renderFunction = await previewRenderer.render();

                renderFunction(previewDiv, {
                    cvData,
                    profile,
                    sections,
                    includePhoto,
                    includeQr,
                    template: templateMeta
                });
            } catch (error) {
                console.error('Error rendering preview:', error);
                const previewDiv = document.getElementById('cv-preview');
                if (previewDiv) {
                    previewDiv.innerHTML = '<p class="text-red-600">Error rendering preview: ' + error.message + '</p>';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', renderPreview));

            const templateSelectEl = document.getElementById('template-select');
            if (templateSelectEl) {
                const allTemplates = listTemplates();
                let availableTemplates = allTemplates.filter((templateMeta) => {
                    if (allowedTemplateIds.size === 0) {
                        return true;
                    }
                    return allowedTemplateIds.has(templateMeta.id);
                });

                if (availableTemplates.length === 0) {
                    availableTemplates = allTemplates;
                }

                templateSelectEl.innerHTML = '';

                if (Array.isArray(availableTemplates) && availableTemplates.length > 0) {
                    if (!availableTemplates.some((templateMeta) => templateMeta.id === selectedTemplate)) {
                        selectedTemplate = availableTemplates[0]?.id || DEFAULT_TEMPLATE_ID;
                    }

                    availableTemplates.forEach((templateMeta) => {
                        const option = document.createElement('option');
                        option.value = templateMeta.id;
                        option.textContent = templateMeta.name;
                        if (templateMeta.id === selectedTemplate) {
                            option.selected = true;
                        }
                        templateSelectEl.appendChild(option);
                    });
                } else {
                    const fallbackOption = document.createElement('option');
                    fallbackOption.value = DEFAULT_TEMPLATE_ID;
                    fallbackOption.textContent = 'Professional Blue (default)';
                    templateSelectEl.appendChild(fallbackOption);
                    selectedTemplate = DEFAULT_TEMPLATE_ID;
                }

                selectedTemplate = templateSelectEl.value || DEFAULT_TEMPLATE_ID;
                updateTemplateDescription(selectedTemplate);
                templateSelectEl.addEventListener('change', (event) => {
                    if (allowedTemplateIds.size > 0 && !allowedTemplateIds.has(event.target.value)) {
                        event.target.value = selectedTemplate;
                        alert('Upgrade to access this template.');
                        return;
                    }
                    selectedTemplate = event.target.value || DEFAULT_TEMPLATE_ID;
                    updateTemplateDescription(selectedTemplate);
                    renderPreview();
                });
            } else {
                updateTemplateDescription(selectedTemplate);
            }

            const pdfButton = document.getElementById('generate-pdf-button');
            if (pdfButton && !SubscriptionContext?.pdfEnabled) {
                pdfButton.disabled = true;
                pdfButton.classList.add('opacity-60', 'cursor-not-allowed');
                pdfButton.textContent = 'Upgrade to download PDF';
            }

            renderPreview();
        });

        window.addEventListener('load', renderPreview);
        window.generatePDF = generatePDF;
    </script>

    <?php partial('footer'); ?>
</body>
</html>