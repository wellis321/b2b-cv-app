import { json } from '@sveltejs/kit';
import { createClient } from '@supabase/supabase-js';
import type { RequestHandler } from './$types';
import type { Database } from '$lib/database.types';
import { createCsrfProtection } from '$lib/security/serverCsrf';
import { supabaseAdmin } from '$lib/server/supabase';
import config from '$lib/config';

export const GET: RequestHandler = async ({ locals, request }) => {
	// First try to get session from the server context
	const {
		data: { session: serverSession },
		error: sessionError
	} = await locals.supabase.auth.getSession();

	if (sessionError) {
		console.warn('Server session error:', sessionError);
	}

	// Extract auth token from header if present
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

	// If no server session but we have a token, create a client with that token
	if (!session && clientToken) {
		console.log('No server session, attempting token-based auth');

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
			console.warn('Token validation failed:', userError);
			return json({ success: false, error: 'Authentication failed' }, { status: 401 });
		}

		if (userData.user) {
			session = { user: userData.user } as any;
			supabaseClient = tempClient;
			console.log('Token auth successful for user:', userData.user.id);
		}
	}

	if (!session) {
		return json({ success: false, error: 'Not authorized' }, { status: 401 });
	}

	try {
		console.log('GET API - Looking for professional summary for user:', session.user.id);
		console.log('GET API - Session user ID:', session.user.id);

		// Get professional summary with strengths
		const { data: summary, error: summaryError } = await supabaseClient
			.from('professional_summary')
			.select(
				`
                id,
                description,
                professional_summary_strengths (
                    id,
                    strength,
                    sort_order
                )
            `
			)
			.eq('profile_id', session.user.id)
			.maybeSingle();

		console.log('GET API - Database query result:', { summary, error: summaryError });

		if (summaryError) {
			console.error('Error fetching professional summary:', summaryError);
			// If table doesn't exist, return null instead of error
			if (
				summaryError.code === 'PGRST116' ||
				summaryError.message.includes('relation') ||
				summaryError.message.includes('does not exist')
			) {
				console.warn('Professional summary table does not exist yet, returning null');
				return json({ success: true, professionalSummary: null });
			}
			return json({ success: false, error: summaryError.message }, { status: 500 });
		}

		// Transform the data to match our interface
		const professionalSummary = summary
			? {
					id: summary.id,
					description: summary.description,
					strengths: summary.professional_summary_strengths || []
				}
			: null;

		return json({ success: true, professionalSummary });
	} catch (err) {
		console.error('Error in professional summary API:', err);
		// If it's a table doesn't exist error, return null instead of error
		if (
			err instanceof Error &&
			(err.message.includes('relation') || err.message.includes('does not exist'))
		) {
			console.warn('Professional summary table does not exist yet, returning null');
			return json({ success: true, professionalSummary: null });
		}
		return json(
			{ success: false, error: err instanceof Error ? err.message : 'Unknown error' },
			{ status: 500 }
		);
	}
};

export const POST: RequestHandler = async ({ request, cookies, locals }) => {
	const { validateRequest } = createCsrfProtection(cookies);
	const isValidRequest = await validateRequest(request);

	if (!isValidRequest) {
		return json({ success: false, error: 'Invalid CSRF token' }, { status: 403 });
	}

	try {
		// First try to get session from the server context
		const {
			data: { session: serverSession },
			error: sessionError
		} = await locals.supabase.auth.getSession();

		if (sessionError) {
			console.warn('Server session error:', sessionError);
		}

		// Extract auth token from header if present
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

		// If no server session but we have a token, create a client with that token
		if (!session && clientToken) {
			console.log('No server session, attempting token-based auth');

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
				console.warn('Token validation failed:', userError);
				return json({ success: false, error: 'Authentication failed' }, { status: 401 });
			}

			if (userData.user) {
				session = { user: userData.user } as any;
				supabaseClient = tempClient;
				console.log('Token auth successful for user:', userData.user.id);
			}
		}

		if (!session) {
			return json({ success: false, error: 'Not authorized' }, { status: 401 });
		}

		const { description, strengths } = await request.json();

		console.log('POST API - Saving data for user:', session.user.id, { description, strengths });
		console.log('POST API - Session user ID:', session.user.id);
		console.log('POST API - Description length:', description?.length || 0);
		console.log('POST API - Strengths count:', strengths?.length || 0);

		// Start a transaction-like operation
		// First, get or create the professional summary
		let { data: existingSummary, error: fetchError } = await supabaseClient
			.from('professional_summary')
			.select('id')
			.eq('profile_id', session.user.id)
			.maybeSingle();

		if (fetchError) {
			console.error('Error fetching existing professional summary:', fetchError);
			return json({ success: false, error: fetchError.message }, { status: 500 });
		}

		let summaryId: string;

		if (existingSummary) {
			// Update existing summary
			const { data: updatedSummary, error: updateError } = await supabaseClient
				.from('professional_summary')
				.update({
					description: description || null,
					updated_at: new Date().toISOString()
				})
				.eq('id', existingSummary.id)
				.select('id')
				.single();

			if (updateError) {
				console.error('Error updating professional summary:', updateError);
				return json({ success: false, error: updateError.message }, { status: 500 });
			}

			summaryId = updatedSummary.id;
		} else {
			// Create new summary
			const { data: newSummary, error: createError } = await supabaseClient
				.from('professional_summary')
				.insert({
					profile_id: session.user.id,
					description: description || null
				})
				.select('id')
				.single();

			if (createError) {
				console.error('Error creating professional summary:', createError);
				return json({ success: false, error: createError.message }, { status: 500 });
			}

			summaryId = newSummary.id;
		}

		// Delete existing strengths
		const { error: deleteError } = await supabaseClient
			.from('professional_summary_strengths')
			.delete()
			.eq('professional_summary_id', summaryId);

		if (deleteError) {
			console.error('Error deleting existing strengths:', deleteError);
			return json({ success: false, error: deleteError.message }, { status: 500 });
		}

		// Insert new strengths if any
		if (strengths && strengths.length > 0) {
			const strengthsToInsert = strengths
				.filter((strength: any) => strength.strength && strength.strength.trim() !== '')
				.map((strength: any, index: number) => ({
					professional_summary_id: summaryId,
					strength: strength.strength.trim(),
					sort_order: index
				}));

			if (strengthsToInsert.length > 0) {
				const { error: insertError } = await supabaseClient
					.from('professional_summary_strengths')
					.insert(strengthsToInsert);

				if (insertError) {
					console.error('Error inserting strengths:', insertError);
					return json({ success: false, error: insertError.message }, { status: 500 });
				}
			}
		}

		console.log('POST API - Professional summary saved successfully, summaryId:', summaryId);
		console.log('POST API - Final description:', description);
		console.log('POST API - Final strengths count:', strengths?.length || 0);

		return json({ success: true });
	} catch (err) {
		console.error('Error in professional summary API:', err);
		return json(
			{ success: false, error: err instanceof Error ? err.message : 'Unknown error' },
			{ status: 500 }
		);
	}
};
