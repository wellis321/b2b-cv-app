import { supabase } from '$lib/supabase';
import { browser } from '$app/environment';
import { updateSectionStatus } from '$lib/cv-sections';

/**
 * Client-side load function for skills
 * This runs in the browser after the server-side load
 */
export const load = async ({ data, depends }) => {
    // Register dependency to allow for invalidation
    depends('app:skills');

    // If not in a browser, just return the server data
    if (!browser) {
        return { ...data };
    }

    // Return server data first
    if (data.skills && data.skills.length > 0) {
        // Update section status silently in the background
        if (browser) {
            updateSectionStatus().catch(err => {
                console.error('Error updating section status:', err);
            });
        }

        return { ...data };
    }

    try {
        // Check if we have an active session in the browser
        const { data: { session } } = await supabase.auth.getSession();

        if (!session) {
            return {
                ...data,
                // If we don't have skills from the server and no session, return empty array
                skills: data.skills || []
            };
        }

        // Get user's skills
        const { data: skills, error } = await supabase
            .from('skills')
            .select('*')
            .eq('profile_id', session.user.id)
            .order('name');

        if (error) {
            console.error('Error loading skills in client:', error);
            return {
                ...data,
                error: 'Error loading skills'
            };
        }

        // Update section status silently
        updateSectionStatus().catch(err => {
            console.error('Error updating section status:', err);
        });

        // Return the skills from client
        return {
            ...data,
            skills: skills || [],
            // Clear any server error since we loaded data successfully
            error: undefined
        };
    } catch (err) {
        console.error('Unexpected error loading skills in client:', err);
        return {
            ...data,
            error: 'Error loading data from browser'
        };
    }
};