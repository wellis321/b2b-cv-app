import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { safeLog } from '$lib/config';

/**
 * Endpoint to collect and log CSP violation reports
 * This is exempt from CSRF checks as browsers don't send CSRF tokens with CSP reports
 */
export const POST: RequestHandler = async ({ request }) => {
	try {
		// Get the CSP report data from the request
		const data = await request.json();
		const report = data['csp-report'] || data;

		// Log the CSP violation
		safeLog('warn', 'CSP Violation', {
			...report,
			userAgent: request.headers.get('user-agent'),
			referer: request.headers.get('referer')
		});

		// Return a 204 No Content response
		return new Response(null, { status: 204 });
	} catch (error) {
		safeLog('error', 'Error processing CSP report', { error });
		return json({ error: 'Invalid CSP report format' }, { status: 400 });
	}
};

// OPTIONS handler for CORS preflight requests
export const OPTIONS: RequestHandler = () => {
	return new Response(null, {
		status: 204,
		headers: {
			'Access-Control-Allow-Methods': 'POST, OPTIONS',
			'Access-Control-Allow-Headers': 'Content-Type'
		}
	});
};
