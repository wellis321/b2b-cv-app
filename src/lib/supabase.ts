import { createClient } from '@supabase/supabase-js';
import { PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY } from '$env/static/public';
import type { Database } from './database.types';

console.log('SUPABASE_URL:', PUBLIC_SUPABASE_URL);
console.log('SUPABASE_ANON_KEY:', PUBLIC_SUPABASE_ANON_KEY);

if (!PUBLIC_SUPABASE_URL || !PUBLIC_SUPABASE_ANON_KEY) {
    throw new Error('Missing Supabase environment variables');
}

// Check if window is defined (browser environment)
const isClient = typeof window !== 'undefined';

export const supabase = createClient<Database>(PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY, {
    auth: {
        persistSession: true,
        autoRefreshToken: true,
        detectSessionInUrl: true,
        // Instead of a custom cookie implementation, rely on Supabase's storage
        // This ensures compatibility between client and server
        storageKey: 'sb-authstate',
    }
});