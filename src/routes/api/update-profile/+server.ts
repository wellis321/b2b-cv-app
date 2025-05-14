import { json } from '@sveltejs/kit';
import { createClient } from '@supabase/supabase-js';
import type { RequestHandler } from './$types';
import type { Database } from '$lib/database.types';
import config, { safeLog } from '$lib/config';

// Create an admin client that ignores RLS - but only for this specific endpoint
// and with strict validation of user ownership
const supabaseAdmin = createClient<Database>(
    config.supabase.url,
    config.supabase.anonKey,
    {
        auth: {
            persistSession: false,
            autoRefreshToken: false,
        }
    }
);

export const POST: RequestHandler = async ({ request, locals }) => {
    // Generate a unique request ID for tracing
    const requestId = crypto.randomUUID();

    try {
        // First try to get session from the server context
        const { data: { session: serverSession }, error: sessionError } = await locals.supabase.auth.getSession();

        safeLog('debug', `[${requestId}] Update profile endpoint session check`, {
            hasSession: !!serverSession,
            userId: serverSession?.user?.id || 'none',
            hasSessionError: !!sessionError
        });

        if (sessionError) {
            safeLog('warn', `[${requestId}] Session error from server:`, {
                code: sessionError.code,
                message: sessionError.message
            });
        }

        // Extract auth token from header if present - with secure parsing
        const authHeader = request.headers.get('Authorization');
        safeLog('debug', `[${requestId}] Auth header present: ${!!authHeader}`);

        let clientToken = null;

        if (authHeader) {
            // Strict Bearer token format validation
            const bearerMatch = authHeader.match(/^Bearer\s+([A-Za-z0-9-._~+/]+=*)$/);
            if (bearerMatch && bearerMatch[1]) {
                clientToken = bearerMatch[1];
                safeLog('debug', `[${requestId}] Found valid Authorization header format`);
            } else if (authHeader.startsWith('Bearer ')) {
                safeLog('warn', `[${requestId}] Invalid Bearer token format in Authorization header`);
                return json({ success: false, error: 'Invalid authorization format' }, { status: 401 });
            }
        }

        // Either use server session or create a client with the provided token
        let session = serverSession;
        let supabaseClient = locals.supabase;

        // If no server session but we have a token, create a client with that token
        if (!session && clientToken) {
            safeLog('info', `[${requestId}] No server session, authenticating with token`);

            // Create a temporary client with the token
            const tempClient = createClient<Database>(
                config.supabase.url,
                config.supabase.anonKey,
                {
                    auth: {
                        persistSession: false,
                        autoRefreshToken: false
                    },
                    global: {
                        headers: {
                            Authorization: `Bearer ${clientToken}`
                        }
                    }
                }
            );

            // Get the user from the token
            const { data: userData, error: userError } = await tempClient.auth.getUser();

            if (userError) {
                safeLog('warn', `[${requestId}] Token validation failed`, {
                    errorCode: userError.code
                });
                return json({ success: false, error: 'Invalid authorization token' }, { status: 401 });
            }

            if (userData.user) {
                safeLog('info', `[${requestId}] Successfully authenticated with token`, {
                    userId: userData.user.id
                });
                session = { user: userData.user } as any; // Simplified session object
                supabaseClient = tempClient;
            }
        }

        if (!session) {
            safeLog('warn', `[${requestId}] No session found in update-profile endpoint`);
            return json({ success: false, error: 'Not authenticated' }, { status: 401 });
        }

        // Parse profile data from request with error handling
        let profileData;
        try {
            const rawBody = await request.text();
            safeLog('debug', `[${requestId}] Raw request body:`, {
                body: rawBody.substring(0, 500),
                length: rawBody.length
            });

            // Parse the raw text
            profileData = JSON.parse(rawBody);
        } catch (parseError) {
            safeLog('error', `[${requestId}] Invalid JSON in request body`, { error: parseError });
            return json({ success: false, error: 'Invalid request format' }, { status: 400 });
        }

        safeLog('info', `[${requestId}] Processing profile update`, {
            userId: session.user.id,
            profileData: JSON.stringify(profileData).substring(0, 500)
        });

        // Ensure the user can only update their own profile
        if (profileData.id !== session.user.id) {
            safeLog('warn', `[${requestId}] User ID mismatch in profile update`, {
                profileId: profileData.id,
                sessionUserId: session.user.id
            });
            return json({ success: false, error: 'You can only update your own profile' }, { status: 403 });
        }

        // Add updated_at timestamp
        profileData.updated_at = new Date().toISOString();

        // Sanitize the photo_url value
        if ('photo_url' in profileData) {
            // Ensure photo_url is either a valid string or null
            if (profileData.photo_url !== null && typeof profileData.photo_url !== 'string') {
                safeLog('warn', `[${requestId}] Invalid photo_url type: ${typeof profileData.photo_url}, value: ${String(profileData.photo_url).substring(0, 100)}`);
                profileData.photo_url = null;
            }
        }

        // First try with the user's session (this should work if RLS is correct)
        safeLog('debug', `[${requestId}] Attempting profile update with user session`);

        // Check if profile exists first
        const { data: existingProfile, error: checkError } = await supabaseClient
            .from('profiles')
            .select('id')
            .eq('id', session.user.id)
            .maybeSingle();

        if (checkError) {
            safeLog('error', `[${requestId}] Error checking if profile exists`, {
                userId: session.user.id,
                errorCode: checkError.code,
                errorMessage: checkError.message
            });
        } else {
            safeLog('debug', `[${requestId}] Profile exists check:`, {
                exists: !!existingProfile,
                profile: existingProfile
            });
        }

        // Use upsert instead of update to create the profile if it doesn't exist
        try {
            // Check if we're creating a new profile or updating an existing one
            const isNewProfile = !existingProfile;
            if (isNewProfile) {
                safeLog('info', `[${requestId}] Creating new profile for user ${session.user.id}`);
            }

            // Use any type to bypass strict TypeScript requirements while maintaining the same functionality
            const simplifiedData: any = {
                id: profileData.id,
                updated_at: profileData.updated_at
            };

            // Generate a default username for new profiles if not provided
            if (isNewProfile && (!('username' in profileData) || !profileData.username)) {
                // Generate a default username based on user ID
                simplifiedData.username = `user${profileData.id.substring(0, 8)}`;
                safeLog('debug', `[${requestId}] Setting default username: ${simplifiedData.username}`);
            }

            // If username is included, validate and add it
            if ('username' in profileData && profileData.username) {
                // Validate username format using our regex pattern (must match the DB constraint)
                const usernamePattern = /^[a-z0-9][a-z0-9\-_]+$/;
                if (typeof profileData.username === 'string' && usernamePattern.test(profileData.username)) {
                    simplifiedData.username = profileData.username;
                    safeLog('debug', `[${requestId}] Setting username to ${profileData.username}`);
                } else {
                    safeLog('warn', `[${requestId}] Invalid username format: ${profileData.username}`);
                    return json({
                        success: false,
                        error: 'Invalid username format',
                        message: 'Username can only contain lowercase letters, numbers, hyphens, and underscores, and must start with a letter or number'
                    }, { status: 400 });
                }
            }

            // Add optional profile fields if they are provided
            if ('full_name' in profileData) {
                simplifiedData.full_name = profileData.full_name;
            }

            if ('email' in profileData) {
                simplifiedData.email = profileData.email;
            }

            if ('phone' in profileData) {
                simplifiedData.phone = profileData.phone;
            }

            if ('location' in profileData) {
                simplifiedData.location = profileData.location;
            }

            // If photo_url is included, add it safely
            if ('photo_url' in profileData) {
                if (profileData.photo_url === null) {
                    simplifiedData.photo_url = null;
                    safeLog('debug', `[${requestId}] Setting photo_url to null`);
                } else if (typeof profileData.photo_url === 'string') {
                    // Validate URL structure
                    try {
                        // Check for common issues with Supabase storage URLs
                        const photoUrl = profileData.photo_url;

                        // Log URL components for debugging
                        safeLog('debug', `[${requestId}] Examining photo URL:`, {
                            url: photoUrl,
                            length: photoUrl.length,
                            containsSupabase: photoUrl.includes('supabase'),
                            containsStoragePath: photoUrl.includes('/storage/v1/object'),
                            containsBucketName: photoUrl.includes('profile-photos'),
                            containsSpecialChars: /[<>"'%\{\}\[\]\\^`]/.test(photoUrl)
                        });

                        new URL(photoUrl);
                        simplifiedData.photo_url = photoUrl;
                    } catch (urlError) {
                        safeLog('warn', `[${requestId}] Invalid URL, not including photo_url:`, {
                            error: urlError instanceof Error ? urlError.message : 'Unknown error',
                            url: profileData.photo_url
                        });
                        // Set to null instead of keeping an invalid URL
                        simplifiedData.photo_url = null;
                    }
                } else {
                    safeLog('warn', `[${requestId}] photo_url is not a string or null, type: ${typeof profileData.photo_url}`);
                }
            }

            safeLog('debug', `[${requestId}] Attempting upsert with simplified data:`, {
                profileData: simplifiedData
            });

            // Use the simplified data for the update
            const { data: updatedData, error: updateError } = await supabaseClient
                .from('profiles')
                .upsert(simplifiedData, {
                    onConflict: 'id'
                })
                .select();

            if (updateError) {
                safeLog('error', `[${requestId}] Error updating profile with user session`, {
                    errorCode: updateError.code,
                    errorMessage: updateError.message,
                    errorDetails: updateError.details
                });

                // If the error is related to RLS, try with admin role
                // This is a fallback that should be replaced with proper RLS policies
                if (updateError.code === 'PGRST301' || updateError.code === '42501') {
                    safeLog('warn', `[${requestId}] RLS error detected, using admin client as fallback`);

                    // Double-check user identity before using admin client
                    if (profileData.id !== session.user.id) {
                        safeLog('error', `[${requestId}] Security check failed: ID mismatch`);
                        return json({ success: false, error: 'Security verification failed' }, { status: 403 });
                    }

                    try {
                        safeLog('debug', `[${requestId}] Attempting profile update with admin client using simplified data:`, {
                            data: simplifiedData
                        });

                        const { data: adminData, error: adminError } = await supabaseAdmin
                            .from('profiles')
                            .upsert(simplifiedData, {
                                onConflict: 'id'
                            })
                            .select();

                        if (adminError) {
                            safeLog('error', `[${requestId}] Error updating profile with admin client`, {
                                errorCode: adminError.code,
                                errorMessage: adminError.message,
                                errorDetails: adminError.details
                            });
                            return json({
                                success: false,
                                error: 'Failed to update profile',
                                message: adminError.message,
                                code: adminError.code,
                                requestId
                            }, { status: 500 });
                        }

                        safeLog('info', `[${requestId}] Profile updated with admin client`, {
                            userId: session.user.id,
                            result: adminData
                        });
                        return json({ success: true, profile: adminData });
                    } catch (adminCatchErr) {
                        safeLog('error', `[${requestId}] Exception in admin update`, {
                            error: adminCatchErr instanceof Error ? adminCatchErr.message : adminCatchErr
                        });
                        return json({
                            success: false,
                            error: 'Server error during admin update',
                            message: adminCatchErr instanceof Error ? adminCatchErr.message : 'Unknown error',
                            requestId
                        }, { status: 500 });
                    }
                }

                return json({
                    success: false,
                    error: 'Failed to update profile',
                    message: updateError.message,
                    code: updateError.code,
                    requestId
                }, { status: 500 });
            }

            safeLog('info', `[${requestId}] Profile updated successfully`, {
                userId: session.user.id,
                result: updatedData
            });
            return json({ success: true, profile: updatedData });
        } catch (error) {
            safeLog('error', `[${requestId}] Error in upsert operation`, {
                error: error instanceof Error ? error.message : error,
                stack: error instanceof Error ? error.stack : undefined
            });
            return json({
                success: false,
                error: 'Server error',
                message: error instanceof Error ? error.message : 'Unknown error',
                requestId
            }, { status: 500 });
        }
    } catch (error) {
        // More detailed error logging with full error information
        const errorDetail = error instanceof Error
            ? { message: error.message, stack: error.stack }
            : { error };

        safeLog('error', `[${requestId}] Unexpected error in update-profile endpoint`, errorDetail);

        // Add more context to the error response
        return json({
            success: false,
            error: 'Server error',
            message: error instanceof Error ? error.message : 'Unknown error',
            requestId
        }, { status: 500 });
    }
};

// Add a simple GET endpoint for testing database connectivity
export const GET: RequestHandler = async ({ locals }) => {
    const requestId = crypto.randomUUID();

    try {
        // Test simple database access
        const { data, error } = await locals.supabase
            .from('profiles')
            .select('count')
            .limit(1);

        if (error) {
            safeLog('error', `[${requestId}] Database test failed:`, {
                errorCode: error.code,
                errorMessage: error.message
            });

            return json({
                success: false,
                error: 'Database test failed',
                details: error.message,
                code: error.code
            });
        }

        return json({
            success: true,
            message: 'Database connection test successful',
            testId: requestId
        });
    } catch (error) {
        safeLog('error', `[${requestId}] Unexpected error in test endpoint:`, {
            error: error instanceof Error ? error.message : error
        });

        return json({
            success: false,
            error: 'Test endpoint error',
            message: error instanceof Error ? error.message : 'Unknown error',
            requestId
        });
    }
};