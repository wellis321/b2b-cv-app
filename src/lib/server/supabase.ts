import { createClient } from '@supabase/supabase-js';
import type { Database } from '$lib/database.types';
import config from '$lib/config';

// Create a Supabase admin client that ignores RLS for server-only operations
export const supabaseAdmin = createClient<Database>(config.supabase.url, config.supabase.anonKey, {
	auth: {
		persistSession: false,
		autoRefreshToken: false
	}
});
