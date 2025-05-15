import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { safeLog } from '$lib/config';

/**
 * API endpoint to receive CSP violation reports from browsers
 * This lets us monitor for potential XSS attacks in production
 */
export const POST: RequestHandler = async ({ request }) => {
    try {
        // Parse the CSP violation report
        const report = await request.json();

        // Log the CSP violation
        safeLog('warn', 'CSP Violation Report', {
            report,
            url: request.url,
            userAgent: request.headers.get('user-agent')
        });

        // Return a success response
        return json({ success: true });
    } catch (error) {
        safeLog('error', 'Error processing CSP violation report', { error });
        return json({ success: false, error: 'Invalid report format' }, { status: 400 });
    }
};

// Preflight for CORS
export const OPTIONS: RequestHandler = () => {
    return new Response(null, {
        status: 204,
        headers: {
            'Access-Control-Allow-Methods': 'POST',
            'Access-Control-Allow-Headers': 'Content-Type'
        }
    });
};