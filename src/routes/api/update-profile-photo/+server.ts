import { json } from '@sveltejs/kit';
import { createClient } from '@supabase/supabase-js';
import type { RequestHandler } from './$types';
import type { Database } from '$lib/database.types';
import config, { safeLog } from '$lib/config';

// Create admin client that ignores RLS - but only for this specific endpoint
// with strict validation of user ownership
const supabaseAdmin = createClient<Database>(config.supabase.url, config.supabase.anonKey, {
    auth: {
        persistSession: false,
        autoRefreshToken: false
    }
});

// This endpoint is specifically for photo updates and exempted from CSRF checks in hooks.server.ts
export const POST: RequestHandler = async ({ request, locals }) => {
    const requestId = crypto.randomUUID();

    try {
        safeLog('info', `[${requestId}] Processing profile photo update request`);

        // First try to get session from the server context
        const {
            data: { session: serverSession },
            error: sessionError
        } = await locals.supabase.auth.getSession();

        if (sessionError) {
            safeLog('warn', `[${requestId}] Server session error:`, {
                message: sessionError.message,
                code: sessionError.code
            });
        }

        // Extract auth token from header for client-side auth
        const authHeader = request.headers.get('Authorization');
        let clientToken = null;

        if (authHeader) {
            const bearerMatch = authHeader.match(/^Bearer\s+([A-Za-z0-9-._~+/]+=*)$/);
            if (bearerMatch && bearerMatch[1]) {
                clientToken = bearerMatch[1];
                safeLog('debug', `[${requestId}] Found valid Authorization header`);
            } else {
                safeLog('warn', `[${requestId}] Invalid Authorization header format`);
                return json(
                    {
                        success: false,
                        error: 'Invalid authorization format',
                        requestId
                    },
                    { status: 401 }
                );
            }
        } else {
            safeLog('debug', `[${requestId}] No Authorization header found`);
        }

        // Either use server session or create a client with the provided token
        let session = serverSession;
        let supabaseClient = locals.supabase;

        // If no server session but we have a token, create a client with the token
        if (!session && clientToken) {
            safeLog('info', `[${requestId}] No server session, attempting token-based auth`);

            // Create a temporary client with the token
            const tempClient = createClient<Database>(config.supabase.url, config.supabase.anonKey, {
                auth: {
                    persistSession: false,
                    autoRefreshToken: false
                },
                global: {
                    headers: {
                        Authorization: `Bearer ${clientToken}`
                    }
                }
            });

            // Get the user from the token
            const { data: userData, error: userError } = await tempClient.auth.getUser();

            if (userError) {
                safeLog('warn', `[${requestId}] Token validation failed:`, {
                    message: userError.message,
                    code: userError.code
                });

                return json(
                    {
                        success: false,
                        error: 'Authentication failed',
                        message: 'Invalid or expired token',
                        requestId
                    },
                    { status: 401 }
                );
            }

            if (userData.user) {
                session = { user: userData.user } as any; // Simplified session object
                supabaseClient = tempClient;
                safeLog('info', `[${requestId}] Token auth successful for user:`, {
                    userId: userData.user.id
                });
            }
        }

        if (!session) {
            safeLog('warn', `[${requestId}] No valid session found`);
            return json(
                {
                    success: false,
                    error: 'Authentication required',
                    message: 'Please log in to update your profile photo',
                    requestId
                },
                { status: 401 }
            );
        }

        // Parse profile data from request
        let profileData;
        try {
            const rawBody = await request.text();
            safeLog('debug', `[${requestId}] Request body:`, {
                body: rawBody.substring(0, 300) // Log first 300 chars to avoid excessive logging
            });

            profileData = JSON.parse(rawBody);
        } catch (parseError) {
            safeLog('warn', `[${requestId}] Failed to parse request body:`, {
                error: parseError instanceof Error ? parseError.message : 'Unknown parsing error'
            });

            return json(
                {
                    success: false,
                    error: 'Invalid request format',
                    message: 'The request could not be parsed as JSON',
                    requestId
                },
                { status: 400 }
            );
        }

        // Ensure the user can only update their own profile
        if (profileData.id !== session.user.id) {
            safeLog('warn', `[${requestId}] User ID mismatch:`, {
                requestUserId: profileData.id,
                sessionUserId: session.user.id
            });

            return json(
                {
                    success: false,
                    error: 'Forbidden',
                    message: 'You can only update your own profile',
                    requestId
                },
                { status: 403 }
            );
        }

        // This endpoint only updates photo_url field
        if (!('photo_url' in profileData)) {
            safeLog('warn', `[${requestId}] Missing photo_url field in request`);

            return json(
                {
                    success: false,
                    error: 'Invalid request',
                    message: 'This endpoint is only for photo updates',
                    requestId
                },
                { status: 400 }
            );
        }

        // Validate photo_url
        if (profileData.photo_url !== null && typeof profileData.photo_url !== 'string') {
            safeLog('warn', `[${requestId}] Invalid photo_url format:`, {
                photoUrlType: typeof profileData.photo_url
            });

            return json(
                {
                    success: false,
                    error: 'Invalid data',
                    message: 'Photo URL must be a string or null',
                    requestId
                },
                { status: 400 }
            );
        }

        // Check if profile exists
        const { data: existingProfile, error: checkError } = await supabaseClient
            .from('profiles')
            .select('id, username') // Ensure we get the username to preserve it
            .eq('id', session.user.id)
            .maybeSingle();

        if (checkError) {
            safeLog('error', `[${requestId}] Error checking profile existence:`, {
                error: checkError.message,
                code: checkError.code
            });
        }

        if (!existingProfile) {
            safeLog('warn', `[${requestId}] Profile not found for user:`, {
                userId: session.user.id
            });

            return json(
                {
                    success: false,
                    error: 'Not found',
                    message: 'Profile not found. Please complete your profile first',
                    requestId
                },
                { status: 404 }
            );
        }

        // Prepare the update data - include username to prevent NOT NULL constraint violation
        const updateData = {
            id: session.user.id,
            photo_url: profileData.photo_url,
            username: existingProfile.username, // Preserve existing username
            updated_at: new Date().toISOString()
        };

        safeLog('info', `[${requestId}] Updating profile photo:`, {
            userId: session.user.id,
            hasPhotoUrl: profileData.photo_url !== null
        });

        // Try to update with the user client first
        const { data: updatedData, error: updateError } = await supabaseClient
            .from('profiles')
            .upsert(updateData, { onConflict: 'id' })
            .select();

        if (updateError) {
            safeLog('warn', `[${requestId}] Client update failed, trying admin update:`, {
                error: updateError.message,
                code: updateError.code
            });

            // Fall back to admin client if needed
            try {
                const { data: adminData, error: adminError } = await supabaseAdmin
                    .from('profiles')
                    .upsert(updateData, { onConflict: 'id' })
                    .select();

                if (adminError) {
                    safeLog('error', `[${requestId}] Admin update failed:`, {
                        error: adminError.message,
                        code: adminError.code
                    });

                    return json(
                        {
                            success: false,
                            error: 'Database error',
                            message: 'Failed to update profile with photo',
                            details: adminError.message,
                            requestId
                        },
                        { status: 500 }
                    );
                }

                safeLog('info', `[${requestId}] Profile photo updated successfully via admin client`);

                return json({
                    success: true,
                    profile: adminData,
                    message: 'Profile photo updated successfully'
                });
            } catch (adminCatchErr) {
                safeLog('error', `[${requestId}] Exception in admin update:`, {
                    error: adminCatchErr instanceof Error ? adminCatchErr.message : 'Unknown error'
                });

                return json(
                    {
                        success: false,
                        error: 'Server error',
                        message: 'An unexpected error occurred during the update',
                        details: adminCatchErr instanceof Error ? adminCatchErr.message : 'Unknown error',
                        requestId
                    },
                    { status: 500 }
                );
            }
        }

        safeLog('info', `[${requestId}] Profile photo updated successfully via user client`);

        return json({
            success: true,
            profile: updatedData,
            message: 'Profile photo updated successfully'
        });
    } catch (error) {
        safeLog('error', `[${requestId}] Unexpected error in photo-update endpoint:`, {
            error: error instanceof Error ? error.message : 'Unknown error',
            stack: error instanceof Error ? error.stack : undefined
        });

        return json(
            {
                success: false,
                error: 'Server error',
                message: 'An unexpected error occurred while processing your request',
                details: error instanceof Error ? error.message : 'Unknown error',
                requestId
            },
            { status: 500 }
        );
    }
};
