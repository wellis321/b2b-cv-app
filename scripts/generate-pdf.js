#!/usr/bin/env node

/**
 * Puppeteer PDF Generator
 *
 * This script uses Puppeteer to generate high-quality PDFs from HTML.
 * It's called by the PHP backend via subprocess.
 *
 * Usage: node generate-pdf.js <url> <output-path>
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

async function generatePDF(url, outputPath) {
    let browser;

    try {
        console.error(`Starting PDF generation for: ${url}`);

        // Launch browser
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security', // Allow CORS for images
            ]
        });

        const page = await browser.newPage();

        // Set viewport for consistent rendering
        await page.setViewport({
            width: 1200,
            height: 1600,
            deviceScaleFactor: 2
        });

        console.error('Loading page...');

        // Navigate to the URL with extended timeout
        await page.goto(url, {
            waitUntil: ['networkidle0', 'domcontentloaded'],
            timeout: 30000
        });

        // Wait a bit for any dynamic content/images to load
        await page.waitForTimeout(1000);

        console.error('Generating PDF...');

        // Generate PDF
        await page.pdf({
            path: outputPath,
            format: 'A4',
            printBackground: true,
            margin: {
                top: '15mm',
                right: '15mm',
                bottom: '15mm',
                left: '15mm'
            },
            preferCSSPageSize: false,
            displayHeaderFooter: false
        });

        console.error(`PDF generated successfully: ${outputPath}`);

        // Output success message to stdout (PHP will read this)
        console.log(JSON.stringify({
            success: true,
            file: outputPath
        }));

    } catch (error) {
        console.error('Error generating PDF:', error.message);
        console.log(JSON.stringify({
            success: false,
            error: error.message
        }));
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

// Parse command line arguments
const args = process.argv.slice(2);

if (args.length < 2) {
    console.error('Usage: node generate-pdf.js <url> <output-path>');
    process.exit(1);
}

const [url, outputPath] = args;

// Ensure output directory exists
const outputDir = path.dirname(outputPath);
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

// Run the PDF generation
generatePDF(url, outputPath);
