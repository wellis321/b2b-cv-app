import { error } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { supabase } from '$lib/supabase';
import { safeLog } from '$lib/config';

/**
 * A simple proxy for Supabase storage to avoid CORS issues
 * This endpoint fetches files from Supabase storage and serves them directly
 */

// Define a common function to handle both GET and HEAD requests
async function handleRequest(request: Request, url: URL, method: string) {
	const bucket = url.searchParams.get('bucket');
	const path = url.searchParams.get('path');
	const timestamp = url.searchParams.get('t'); // For cache busting

	if (!bucket || !path) {
		throw error(400, 'Missing bucket or path parameter');
	}

	try {
		safeLog(
			'debug',
			`Storage proxy ${method} request for ${bucket}/${path} (timestamp: ${timestamp})`
		);

		// Try to get a signed URL first (works even if bucket isn't public)
		const { data: signedData, error: signedError } = await supabase.storage
			.from(bucket)
			.createSignedUrl(path, 60); // 60 second expiry

		let fileUrl;

		if (signedError) {
			safeLog('warn', `Couldn't create signed URL: ${signedError.message}, trying public URL`);

			// Fall back to public URL if signed URL fails
			const { data: publicData } = supabase.storage.from(bucket).getPublicUrl(path);

			fileUrl = publicData.publicUrl;
			safeLog('debug', `Using public URL: ${fileUrl}`);
		} else {
			fileUrl = signedData.signedUrl;
			safeLog('debug', `Using signed URL: ${fileUrl}`);
		}

		// Fetch the file through the server to avoid CORS
		const fetchOptions: RequestInit = {
			method: method,
			headers: {
				Accept: 'image/*,*/*' // Accept any content type, but prefer images
			}
		};

		safeLog('debug', `Fetching file from: ${fileUrl}`);
		const response = await fetch(fileUrl, fetchOptions);

		if (!response.ok) {
			safeLog('error', `Failed to fetch file: ${response.status} ${response.statusText}`);
			throw error(response.status, `Failed to fetch file: ${response.statusText}`);
		}

		// Get content type from response
		const contentType = response.headers.get('content-type') || 'application/octet-stream';
		const headers = new Headers({
			'Content-Type': contentType,
			'Cache-Control': 'public, max-age=3600',
			'Access-Control-Allow-Origin': '*', // Allow access from any origin
			'Content-Disposition': 'inline', // Force inline display for images
			'X-Content-Type-Options': 'nosniff', // Prevent content type sniffing
			Pragma: 'no-cache',
			Expires: '0'
		});

		// Log success for debugging
		safeLog('debug', `Storage proxy success for ${bucket}/${path} (${contentType})`);

		// For HEAD requests, just return the headers
		if (method === 'HEAD') {
			return new Response(null, {
				status: 200,
				headers
			});
		}

		// For GET requests, return the file content
		const fileData = await response.arrayBuffer();
		safeLog('debug', `Serving ${fileData.byteLength} bytes of data`);

		return new Response(fileData, {
			status: 200,
			headers
		});
	} catch (err) {
		safeLog(
			'error',
			`Storage proxy ${method} error: ${err instanceof Error ? err.message : String(err)}`
		);
		throw error(500, 'Error proxying storage file');
	}
}

// GET handler
export const GET: RequestHandler = async ({ request, url }) => {
	return handleRequest(request, url, 'GET');
};

// HEAD handler
export const HEAD: RequestHandler = async ({ request, url }) => {
	return handleRequest(request, url, 'HEAD');
};
