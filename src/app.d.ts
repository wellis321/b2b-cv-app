// See https://svelte.dev/docs/kit/types#app.d.ts
// for information about these interfaces
import { SupabaseClient, Session } from '@supabase/supabase-js';
import { Database } from './lib/database.types';

declare global {
    namespace App {
        // interface Error {}
        interface Locals {
            supabase: SupabaseClient<Database>;
            session: Session | null;
            csrfToken?: string;
        }
        // interface PageData {}
        // interface PageState {}
        // interface Platform {}
    }
}

export { };
