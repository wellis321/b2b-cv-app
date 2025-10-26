import type { Session } from '@supabase/supabase-js';
import type { RequestEvent } from '@sveltejs/kit';

/**
 * Get the current server session from locals or from event.locals
 * This can be used in server endpoints and page server load functions
 * @returns The current session or null if not authenticated
 */
export async function getServerSession(locals?: any): Promise<Session | null> {
	try {
		// If locals is provided directly, use it
		if (locals && locals.session) {
			return locals.session;
		}

		// No session found
		return null;
	} catch (error) {
		console.error('Error getting server session:', error);
		return null;
	}
}

/**
 * Get the current server session from a request event
 * This is used in endpoints and load functions
 */
export function getSessionFromEvent(event: RequestEvent): Session | null {
	return event.locals.session || null;
}
