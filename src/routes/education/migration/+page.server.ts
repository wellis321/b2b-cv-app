import { redirect } from '@sveltejs/kit';
import { supabase } from '$lib/supabase';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
	// Check if user is authenticated
	const {
		data: { session }
	} = await supabase.auth.getSession();

	if (!session) {
		throw redirect(302, '/');
	}

	return {
		user: session.user
	};
};
