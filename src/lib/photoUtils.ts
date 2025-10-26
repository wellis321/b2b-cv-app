import { getPathFromUrl } from '$lib/fileUpload';

// Constants
export const PROFILE_PHOTOS_BUCKET = 'profile-photos';
export const PROJECT_IMAGES_BUCKET = 'project-images';
export const DEFAULT_PROFILE_PHOTO = '/images/default-profile.svg';
export const DEFAULT_PROJECT_IMAGE = '/images/default-project.svg';

/**
 * Get a URL for a photo that is proxied through our server
 * This helps avoid CORS issues and keeps our Supabase URL private
 */
export function getProxiedPhotoUrl(
	url: string | null | undefined,
	bucket: string = PROFILE_PHOTOS_BUCKET
): string {
	if (!url) {
		console.log('No URL provided to getProxiedPhotoUrl, returning default');
		return bucket === PROFILE_PHOTOS_BUCKET ? DEFAULT_PROFILE_PHOTO : DEFAULT_PROJECT_IMAGE;
	}

	try {
		console.log(`Creating proxied URL for ${url} in bucket ${bucket}`);

		// Get path from the URL for the storage-proxy endpoint
		const path = getPathFromUrl(url, bucket);

		if (!path) {
			console.warn(`Could not extract path from URL: ${url}, returning default`);
			return bucket === PROFILE_PHOTOS_BUCKET ? DEFAULT_PROFILE_PHOTO : DEFAULT_PROJECT_IMAGE;
		}

		// Create a proxy URL through our API - add timestamp to prevent caching issues
		const proxyUrl = `/api/storage-proxy?bucket=${bucket}&path=${encodeURIComponent(path)}&t=${Date.now()}`;

		console.log(`Generated proxy URL: ${proxyUrl}`);

		return proxyUrl;
	} catch (error) {
		console.error('Error creating proxied photo URL:', error);
		return bucket === PROFILE_PHOTOS_BUCKET ? DEFAULT_PROFILE_PHOTO : DEFAULT_PROJECT_IMAGE;
	}
}

/**
 * Validates if a photo URL is accessible
 * @param url The URL to validate
 * @param bucket The storage bucket name
 * @returns A boolean indicating if the URL is accessible
 */
export async function validatePhotoUrl(
	url: string | null,
	bucket: string = PROFILE_PHOTOS_BUCKET
): Promise<boolean> {
	if (!url) return false;

	// Check if URL is a valid Supabase URL
	if (url.includes('supabase.co/storage') && url.includes(bucket)) {
		const path = getPathFromUrl(url, bucket);
		if (!path) return false;

		try {
			// Test accessing the file through our proxy
			const proxyUrl = `/api/storage-proxy?bucket=${bucket}&path=${encodeURIComponent(path)}&t=${Date.now()}`;
			const response = await fetch(proxyUrl, { method: 'HEAD' });
			return response.ok;
		} catch (error) {
			console.error('Error validating photo URL via proxy:', error);
			return false;
		}
	}

	return false;
}
