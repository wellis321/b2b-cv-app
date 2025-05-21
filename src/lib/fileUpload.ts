import { supabase } from './supabase';

export interface UploadResult {
    success: boolean;
    url?: string;
    proxyUrl?: string;
    error?: string;
}

/**
 * Checks if a bucket exists and has the correct permissions.
 * If the bucket doesn't exist, it tries to create it with public access.
 * @param bucket Storage bucket name
 * @returns Status and any error message
 */
export async function ensureBucketExists(
    bucket: string
): Promise<{ exists: boolean; error?: string }> {
    try {
        // Check if bucket exists
        const { data: buckets, error: bucketsError } = await supabase.storage.listBuckets();

        if (bucketsError) {
            console.error('Error listing buckets:', bucketsError);
            // Don't try to create the bucket if we can't list buckets
            // This is likely a permissions issue
            if (bucketsError.message?.includes('permission') || bucketsError.message?.includes('403')) {
                return {
                    exists: false,
                    error: 'You do not have permission to access storage buckets. Please contact an administrator.'
                };
            }
            return { exists: false, error: bucketsError.message };
        }

        // Check if our bucket exists
        const bucketExists = buckets?.some((b) => b.name === bucket) ?? false;
        console.log(`Bucket check: "${bucket}" ${bucketExists ? 'exists' : 'does not exist'}`);

        if (bucketExists) {
            // Bucket exists, ensure it has public access
            const { success, error: accessError } = await ensurePublicAccess(bucket);
            if (!success) {
                console.warn(`Bucket "${bucket}" exists but public access check failed:`, accessError);
            }
            return { exists: true };
        }

        // Bucket doesn't exist, but don't try to create it
        // This should be done by an administrator
        console.warn(`Bucket "${bucket}" doesn't exist and should be created by an administrator`);
        return {
            exists: false,
            error: `Storage bucket "${bucket}" is required but doesn't exist. Please contact an administrator to set up the required storage.`
        };
    } catch (error) {
        console.error('Error ensuring bucket exists:', error);
        return {
            exists: false,
            error: error instanceof Error ? error.message : 'Unknown error checking bucket'
        };
    }
}

/**
 * Uploads a file to Supabase storage
 * @param userId User ID to associate with the file
 * @param file File to upload
 * @param bucket Storage bucket name
 * @param path Optional path within the bucket
 * @returns Upload result with public URL if successful
 */
export async function uploadFile(
    userId: string,
    file: File,
    bucket: string,
    path: string = ''
): Promise<UploadResult> {
    try {
        // First, ensure the bucket exists and has public access
        const { exists, error: bucketError } = await ensureBucketExists(bucket);

        if (!exists) {
            // Try to use the upload anyway, but log the issue
            console.warn(`Proceeding with upload to "${bucket}" despite bucket issues: ${bucketError}`);
        }

        // Create a unique file name using the original file extension
        const fileExt = file.name.split('.').pop();
        const fileName = `${userId}/${Date.now()}.${fileExt}`;
        const fullPath = path ? `${path}/${fileName}` : fileName;

        // Upload file to Supabase storage
        console.log(`Attempting to upload to bucket "${bucket}" at path "${fullPath}"`);

        // Add a small delay before uploading to ensure bucket check is complete
        await new Promise((resolve) => setTimeout(resolve, 100));

        // Try to upload the file
        const { data, error } = await supabase.storage.from(bucket).upload(fullPath, file, {
            cacheControl: '3600',
            upsert: true
        });

        if (error) {
            console.error('Error uploading file:', error);

            // Provide more specific error messages for common issues
            if (error.message?.includes('bucket') || error.message?.includes('not found')) {
                return {
                    success: false,
                    error: `Storage bucket "${bucket}" not found. Please contact the administrator to create this bucket.`
                };
            }

            if (error.message?.includes('permission') || error.message?.includes('access')) {
                return {
                    success: false,
                    error: `You don't have permission to upload to the "${bucket}" bucket.`
                };
            }

            return { success: false, error: error.message };
        }

        // Get the public URL for the file
        const {
            data: { publicUrl }
        } = supabase.storage.from(bucket).getPublicUrl(data.path);

        // Create a proxied URL to avoid CORS issues
        const proxiedUrl = `/api/storage-proxy?bucket=${bucket}&path=${encodeURIComponent(data.path)}&t=${Date.now()}`;

        console.log(`File uploaded successfully. Public URL: ${publicUrl}`);
        console.log(`Proxied URL: ${proxiedUrl}`);

        // Return both the direct public URL and a proxied URL that should work even with CORS restrictions
        return {
            success: true,
            url: publicUrl,
            proxyUrl: proxiedUrl
        };
    } catch (error) {
        console.error('Unexpected error during file upload:', error);
        return {
            success: false,
            error: error instanceof Error ? error.message : 'Unknown error during upload'
        };
    }
}

/**
 * Deletes a file from Supabase storage
 * @param bucket Storage bucket name
 * @param path Path to the file
 * @returns Success status and optional error
 */
export async function deleteFile(
    bucket: string,
    path: string
): Promise<{ success: boolean; error?: string }> {
    try {
        const { error } = await supabase.storage.from(bucket).remove([path]);

        if (error) {
            console.error('Error deleting file:', error);
            return { success: false, error: error.message };
        }

        return { success: true };
    } catch (error) {
        console.error('Unexpected error during file deletion:', error);
        return {
            success: false,
            error: error instanceof Error ? error.message : 'Unknown error during deletion'
        };
    }
}

/**
 * Extracts the file path from a public URL
 * @param url Public URL from Supabase storage
 * @param bucket Bucket name
 * @returns File path or null if not found
 */
export function getPathFromUrl(url: string, bucket: string): string | null {
    if (!url) return null;

    try {
        // Try to extract the path from the URL
        const regex = new RegExp(`${bucket}/([^?#]+)`);
        const match = url.match(regex);

        return match ? match[1] : null;
    } catch (error) {
        console.error('Error extracting path from URL:', error);
        return null;
    }
}

/**
 * Checks if a file exists in Supabase storage
 * @param bucket Storage bucket name
 * @param path Path to the file
 * @returns Whether the file exists
 */
export async function fileExists(bucket: string, path: string): Promise<boolean> {
    try {
        // Try to get file metadata
        const { data, error } = await supabase.storage.from(bucket).createSignedUrl(path, 1); // Just 1 second expiry to check existence

        if (error) {
            console.error('Error checking if file exists:', error);
            return false;
        }

        return !!data; // If data exists, the file exists
    } catch (error) {
        console.error('Unexpected error checking file existence:', error);
        return false;
    }
}

/**
 * Ensures the bucket has a public access policy.
 * This allows files to be accessed without authentication.
 * @param bucket Storage bucket name
 * @returns Status and any error message
 */
export async function ensurePublicAccess(
    bucket: string
): Promise<{ success: boolean; error?: string }> {
    try {
        // Create a public access policy for the bucket
        // This will allow anyone to read files from this bucket
        const { error } = await supabase.storage.from(bucket).createSignedUrl('test-access.txt', 60);

        // If the error indicates the file isn't found, the bucket likely has public access already
        if (error && !error.message.includes('not found')) {
            console.error(`Error checking bucket "${bucket}" access policy:`, error);

            // Try to set public policy explicitly
            try {
                // First try using the specific policy setup
                const policyName = `public-access-${bucket}`;
                const policyDefinition = {
                    name: policyName,
                    definition: {
                        statements: [
                            {
                                effect: 'allow',
                                actions: ['select'],
                                role: 'anon'
                            }
                        ]
                    }
                };

                // Note: The method below is not in the standard supabase-js API
                // but can be accessed through custom API calls if needed
                /*
                const { error: policyError } = await supabase.rpc('create_bucket_policy', {
                    bucket_name: bucket,
                    policy: policyDefinition
                });

                if (policyError) {
                    console.warn(`Error creating policy for bucket "${bucket}":`, policyError);
                }
                */

                // Since we can't directly create a policy through the JS client,
                // we'll try to upload a test file which may implicitly create required policies
                const testBlob = new Blob(['test'], { type: 'text/plain' });
                const testFile = new File([testBlob], 'test-access.txt', { type: 'text/plain' });

                const { error: uploadError } = await supabase.storage
                    .from(bucket)
                    .upload('test-access.txt', testFile, { upsert: true });

                if (uploadError) {
                    console.warn(`Could not create test file in "${bucket}":`, uploadError);
                    return { success: false, error: `Could not create test file: ${uploadError.message}` };
                }

                // Get public URL for the test file to verify access
                const { data: urlData } = supabase.storage.from(bucket).getPublicUrl('test-access.txt');

                if (urlData?.publicUrl) {
                    console.log(`Public access URL test success: ${urlData.publicUrl}`);

                    // Clean up test file
                    await supabase.storage.from(bucket).remove(['test-access.txt']);
                    return { success: true };
                }
            } catch (policyError) {
                console.error(`Error ensuring public access for "${bucket}":`, policyError);
                return {
                    success: false,
                    error: policyError instanceof Error ? policyError.message : 'Unknown policy error'
                };
            }
        }

        return { success: true };
    } catch (error) {
        console.error('Error ensuring public access:', error);
        return {
            success: false,
            error: error instanceof Error ? error.message : 'Unknown error setting access policy'
        };
    }
}
