import { json } from '@sveltejs/kit';
import { sanitizeInput } from '$lib/validation';
import type { RequestHandler } from './$types';
import { getSessionFromEvent } from '$lib/server/session';
import { createCsrfProtection } from '$lib/security/serverCsrf';
import { supabaseAdmin } from '$lib/server/supabase';
import { validateUsernameServer } from '$lib/server/validation';

export const POST: RequestHandler = async ({ request, cookies, locals }) => {
	const { validateRequest } = createCsrfProtection(cookies);
	const isValidRequest = await validateRequest(request);

	if (!isValidRequest) {
		return json({ success: false, error: 'Invalid CSRF token' }, { status: 403 });
	}

	// Get the current session
	const session = getSessionFromEvent({ locals } as any);
	if (!session) {
		return json({ success: false, error: 'Not authorized' }, { status: 401 });
	}

	try {
		const formData = await request.json();

		// Validate the username server-side (checks format and availability)
		const usernameValidation = await validateUsernameServer(formData.username, session.user.id);
		if (!usernameValidation.valid) {
			return json({ success: false, error: usernameValidation.error }, { status: 400 });
		}

		// Validate hex color format
		function isValidHexColor(color: string): boolean {
			return /^#[0-9A-F]{6}$/i.test(color);
		}

		// Validate color inputs if they exist
		if (formData.cv_header_from_color && !isValidHexColor(formData.cv_header_from_color)) {
			return json(
				{ success: false, error: 'Invalid format for header from color' },
				{ status: 400 }
			);
		}

		if (formData.cv_header_to_color && !isValidHexColor(formData.cv_header_to_color)) {
			return json({ success: false, error: 'Invalid format for header to color' }, { status: 400 });
		}

		// Sanitize all input fields
		const sanitizedData = {
			full_name: sanitizeInput(formData.full_name),
			username: formData.username ? sanitizeInput(formData.username) : undefined,
			email: sanitizeInput(formData.email),
			phone: sanitizeInput(formData.phone),
			location: sanitizeInput(formData.location),
			bio: sanitizeInput(formData.bio),
			linkedin_url: sanitizeInput(formData.linkedin_url),
			photo_url: formData.photo_url, // Don't sanitize URLs, they need to remain intact
			cv_header_from_color: formData.cv_header_from_color,
			cv_header_to_color: formData.cv_header_to_color,
			updated_at: new Date().toISOString()
		};

		// Filter out undefined values
		const profileData = Object.fromEntries(
			Object.entries(sanitizedData).filter(([_, value]) => value !== undefined)
		);

		// Save the profile data to the database
		const { error } = await supabaseAdmin.from('profiles').upsert({
			id: session.user.id,
			...profileData
		});

		if (error) {
			console.error('Error updating profile:', error);
			return json({ success: false, error: error.message }, { status: 500 });
		}

		return json({ success: true });
	} catch (err) {
		console.error('Error in profile API:', err);
		return json(
			{ success: false, error: err instanceof Error ? err.message : 'Unknown error' },
			{ status: 500 }
		);
	}
};
