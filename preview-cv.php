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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            if (typeof QRCode === 'undefined') {
                console.warn('QRCode library not available after page load')
            }
        })
    </script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                console.log('🎨 Using HTML-to-PDF approach (html2canvas + jsPDF)');

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

                // Check for html2canvas and jsPDF
                if (typeof html2canvas === 'undefined') {
                    alert('Error: html2canvas library not loaded. Please refresh the page.');
                    console.error('html2canvas is not defined');
                    return;
                }

                if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
                    alert('Error: jsPDF library not loaded. Please refresh the page.');
                    console.error('jsPDF is not defined');
                    return;
                }

                // Update the preview to make sure it's current
                await renderPreview();

                const previewDiv = document.getElementById('cv-preview');
                if (!previewDiv || !previewDiv.innerHTML.trim()) {
                    alert('Error: Preview not rendered. Please ensure at least one section is selected.');
                    return;
                }

                // Show loading state
                const button = document.getElementById('generate-pdf-button');
                const originalText = button?.textContent;
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Generating PDF...';
                }

                // Create a temporary container optimized for PDF
                const pdfContainer = document.createElement('div');
                pdfContainer.style.cssText = `
                    position: absolute;
                    left: -9999px;
                    top: 0;
                    width: 210mm;
                    padding: 15mm;
                    background: white;
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 11pt;
                    line-height: 1.5;
                    color: #000;
                    box-sizing: border-box;
                `;
                pdfContainer.innerHTML = previewDiv.innerHTML;
                document.body.appendChild(pdfContainer);

                // Convert all images to base64 to avoid CORS issues
                const images = pdfContainer.getElementsByTagName('img');
                const imagePromises = Array.from(images).map(async (img) => {
                    try {
                        if (img.src && !img.src.startsWith('data:')) {
                            const response = await fetch(img.src);
                            const blob = await response.blob();
                            const reader = new FileReader();
                            return new Promise((resolve) => {
                                reader.onloadend = () => {
                                    img.src = reader.result;
                                    resolve();
                                };
                                reader.readAsDataURL(blob);
                            });
                        }
                    } catch (error) {
                        console.warn('Failed to load image:', img.src, error);
                    }
                });
                await Promise.all(imagePromises);

                // Add QR code if requested
                const includeQR = document.getElementById('include-qr')?.checked ?? true;
                if (includeQR && cvUrl) {
                    try {
                        const qrContainer = document.createElement('div');
                        qrContainer.style.cssText = 'margin-top: 20px; text-align: right;';

                        const qrDiv = document.createElement('div');
                        qrDiv.style.display = 'inline-block';
                        qrContainer.appendChild(qrDiv);

                        let QRCodeLib = typeof QRCode !== 'undefined' ? QRCode : window.QRCode;
                        if (QRCodeLib) {
                            new QRCodeLib(qrDiv, {
                                text: cvUrl,
                                width: 100,
                                height: 100,
                                colorDark: '#000000',
                                colorLight: '#FFFFFF'
                            });

                            const qrLabel = document.createElement('p');
                            qrLabel.textContent = 'View my CV online';
                            qrLabel.style.cssText = 'font-size: 9pt; color: #6b7280; margin: 5px 0 0 0;';
                            qrContainer.appendChild(qrLabel);

                            pdfContainer.appendChild(qrContainer);
                        }
                    } catch (qrError) {
                        console.warn('QR code generation failed:', qrError);
                    }
                }

                // Create PDF with better page break handling
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4',
                    compress: true
                });

                // Use jsPDF's html method for better page breaks
                await pdf.html(pdfContainer, {
                    callback: function(doc) {
                        // Clean up temporary container
                        document.body.removeChild(pdfContainer);

                        // Download the PDF
                        const fileName = `${(profile.full_name || 'CV').replace(/[^a-z0-9_\-]/gi, '_')}_CV.pdf`;
                        doc.save(fileName);

                        console.log('✅ PDF generated successfully using HTML-to-PDF approach');

                        // Restore button
                        if (button) {
                            button.disabled = false;
                            button.textContent = originalText;
                        }
                    },
                    x: 0,
                    y: 0,
                    width: 210, // A4 width in mm
                    windowWidth: 794, // A4 width in pixels at 96 DPI (210mm)
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#ffffff'
                    },
                    autoPaging: 'text' // Better page break handling for text
                });

                return; // Exit early since callback handles cleanup and download

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