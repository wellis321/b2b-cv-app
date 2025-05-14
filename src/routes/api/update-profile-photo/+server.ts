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
		// First try to get session from the server context
		const {
			data: { session: serverSession },
			error: sessionError
		} = await locals.supabase.auth.getSession();

		// Extract auth token from header for client-side auth
		const authHeader = request.headers.get('Authorization');
		let clientToken = null;

		if (authHeader) {
			const bearerMatch = authHeader.match(/^Bearer\s+([A-Za-z0-9-._~+/]+=*)$/);
			if (bearerMatch && bearerMatch[1]) {
				clientToken = bearerMatch[1];
			}
		}

		// Either use server session or create a client with the provided token
		let session = serverSession;
		let supabaseClient = locals.supabase;

		// If no server session but we have a token, create a client with the token
		if (!session && clientToken) {
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

			if (!userError && userData.user) {
				session = { user: userData.user } as any; // Simplified session object
				supabaseClient = tempClient;
			}
		}

		if (!session) {
			return json({ success: false, error: 'Not authenticated' }, { status: 401 });
		}

		// Parse profile data from request
		let profileData;
		try {
			const rawBody = await request.text();
			profileData = JSON.parse(rawBody);
		} catch (parseError) {
			return json({ success: false, error: 'Invalid request format' }, { status: 400 });
		}

		// Ensure the user can only update their own profile
		if (profileData.id !== session.user.id) {
			return json(
				{ success: false, error: 'You can only update your own profile' },
				{ status: 403 }
			);
		}

		// This endpoint only updates photo_url field
		if (!('photo_url' in profileData)) {
			return json(
				{ success: false, error: 'This endpoint is only for photo updates' },
				{ status: 400 }
			);
		}

		// Validate photo_url
		if (profileData.photo_url !== null && typeof profileData.photo_url !== 'string') {
			return json({ success: false, error: 'Invalid photo URL format' }, { status: 400 });
		}

		// Check if profile exists
		const { data: existingProfile, error: checkError } = await supabaseClient
			.from('profiles')
			.select('id, username') // Ensure we get the username to preserve it
			.eq('id', session.user.id)
			.maybeSingle();

		if (!existingProfile) {
			return json(
				{
					success: false,
					error: 'Profile not found, please complete your profile first'
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

		// Try to update with the user client first
		const { data: updatedData, error: updateError } = await supabaseClient
			.from('profiles')
			.upsert(updateData, { onConflict: 'id' })
			.select();

		if (updateError) {
			// Fall back to admin client if needed
			try {
				const { data: adminData, error: adminError } = await supabaseAdmin
					.from('profiles')
					.upsert(updateData, { onConflict: 'id' })
					.select();

				if (adminError) {
					return json(
						{
							success: false,
							error: 'Failed to update photo',
							message: adminError.message
						},
						{ status: 500 }
					);
				}

				return json({ success: true, profile: adminData });
			} catch (adminCatchErr) {
				return json(
					{
						success: false,
						error: 'Server error during admin update',
						message: adminCatchErr instanceof Error ? adminCatchErr.message : 'Unknown error'
					},
					{ status: 500 }
				);
			}
		}

		return json({ success: true, profile: updatedData });
	} catch (error) {
		safeLog('error', `[${requestId}] Unexpected error in photo-update endpoint`, {
			error: error instanceof Error ? error.message : 'Unknown error'
		});

		return json(
			{
				success: false,
				error: 'Server error',
				message: error instanceof Error ? error.message : 'Unknown error',
				requestId
			},
			{ status: 500 }
		);
	}
};
