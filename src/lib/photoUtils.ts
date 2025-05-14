import { getPathFromUrl } from '$lib/fileUpload';

// Constants
export const PROFILE_PHOTOS_BUCKET = 'profile-photos';
export const DEFAULT_PROFILE_PHOTO = '/images/default-profile.svg';

/**
 * Converts a Supabase Storage URL to a proxied URL to avoid CORS issues
 * @param url The original Supabase Storage URL
 * @param bucket The storage bucket name
 * @returns A proxied URL that goes through the server's storage-proxy endpoint
 */
export function getProxiedPhotoUrl(url: string | null, bucket: string = PROFILE_PHOTOS_BUCKET): string {
    if (!url) return DEFAULT_PROFILE_PHOTO;

    // Check if URL is a valid Supabase URL
    if (url.includes('supabase.co/storage') && url.includes(bucket)) {
        // Extract path from the URL
        const path = getPathFromUrl(url, bucket);
        if (!path) return DEFAULT_PROFILE_PHOTO;

        // Use the server-side proxy to avoid CORS issues
        // Add a timestamp to prevent caching issues
        return `/api/storage-proxy?bucket=${bucket}&path=${encodeURIComponent(path)}&t=${Date.now()}`;
    }

    return url;
}

/**
 * Validates if a photo URL is accessible
 * @param url The URL to validate
 * @param bucket The storage bucket name
 * @returns A boolean indicating if the URL is accessible
 */
export async function validatePhotoUrl(url: string | null, bucket: string = PROFILE_PHOTOS_BUCKET): Promise<boolean> {
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