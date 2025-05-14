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

	if (!bucket || !path) {
		throw error(400, 'Missing bucket or path parameter');
	}

	try {
		safeLog('debug', `Storage proxy ${method} request for ${bucket}/${path}`);

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
		} else {
			fileUrl = signedData.signedUrl;
		}

		// Fetch the file through the server to avoid CORS
		const fetchOptions: RequestInit = {
			method: method
		};

		const response = await fetch(fileUrl, fetchOptions);

		if (!response.ok) {
			throw error(response.status, `Failed to fetch file: ${response.statusText}`);
		}

		// Get content type from response
		const contentType = response.headers.get('content-type') || 'application/octet-stream';
		const headers = new Headers({
			'Content-Type': contentType,
			'Cache-Control': 'public, max-age=3600',
			'Access-Control-Allow-Origin': '*' // Allow access from any origin
		});

		// For HEAD requests, just return the headers
		if (method === 'HEAD') {
			return new Response(null, {
				status: 200,
				headers
			});
		}

		// For GET requests, return the file content
		const fileData = await response.arrayBuffer();
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
