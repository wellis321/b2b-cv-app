import { writable, derived, get } from 'svelte/store';
import { supabase } from '$lib/supabase';
import { browser } from '$app/environment';
import { session } from './authStore';
import type { Database } from '$lib/database.types';
import { createClient } from '@supabase/supabase-js';
import config from '$lib/config';
import { sanitizeInput } from '$lib/validation';

// Types for CV data
export interface CvData {
    userId: string | null;
    profile: any | null;
    workExperiences: any[];
    projects: any[];
    skills: any[];
    education: any[];
    certifications: any[];
    memberships: any[];
    interests: any[];
    qualificationEquivalence: any[];
    username: string | null;
}

// Default empty state
const defaultCvData: CvData = {
    userId: null,
    profile: null,
    workExperiences: [],
    projects: [],
    skills: [],
    education: [],
    certifications: [],
    memberships: [],
    interests: [],
    qualificationEquivalence: [],
    username: null
};

// Create the store
const createCvStore = () => {
    const { subscribe, set, update } = writable<CvData>({ ...defaultCvData });

    // Track loading state
    const loadingState = writable({
        loading: false,
        error: null as string | null
    });

    // Store cache by userId to avoid refetching
    let cache: Record<string, CvData> = {};

    return {
        subscribe,
        loading: {
            subscribe: loadingState.subscribe
        },

        // Reset the store to initial empty state
        reset: () => {
            set({ ...defaultCvData });
        },

        // Set cached data if available
        setCachedData: (userId: string) => {
            if (cache[userId]) {
                set(cache[userId]);
                return true;
            }
            return false;
        },

        // Load CV data for the current user
        loadCurrentUserData: async () => {
            if (!browser) return;

            const currentSession = get(session);
            if (!currentSession || !currentSession.user) {
                loadingState.update((s) => ({ ...s, error: 'User not logged in' }));
                return;
            }

            const userId = currentSession.user.id;

            // Return cached data if available
            if (cache[userId]) {
                set(cache[userId]);
                return;
            }

            try {
                loadingState.update((s) => ({ ...s, loading: true, error: null }));

                // First get the profile to check username
                const { data: profile, error: profileError } = await supabase
                    .from('profiles')
                    .select('*')
                    .eq('id', userId)
                    .single();

                if (profileError) {
                    console.error('Error loading profile:', profileError);
                    loadingState.update((s) => ({ ...s, error: 'Failed to load profile data' }));
                    return;
                }

                // Load all CV sections
                const data = await loadUserCvData(userId);

                if (data.profile) {
                    data.username = data.profile.username || null;
                }

                // Cache the data
                cache[userId] = data;

                // Update the store
                set(data);

                loadingState.update((s) => ({ ...s, loading: false }));
                return data;
            } catch (err: any) {
                console.error('Error loading CV data:', err);
                loadingState.update((s) => ({
                    ...s,
                    loading: false,
                    error: err.message || 'Failed to load CV data'
                }));
            }
        },

        // Load CV data by username (for public view)
        loadByUsername: async (username: string) => {
            if (!browser || !username) return;

            // Check if we have this username cached
            const cachedEntry = Object.values(cache).find((entry) => entry.username === username);
            if (cachedEntry) {
                set(cachedEntry);
                return cachedEntry;
            }

            try {
                loadingState.update((s) => ({ ...s, loading: true, error: null }));

                // Create a new Supabase client for this request
                // (bypassing auth to ensure public profiles can be viewed by non-authenticated users)
                const publicSupabase = createClient<Database>(
                    config.supabase.url,
                    config.supabase.anonKey,
                    {
                        auth: {
                            persistSession: false,
                            autoRefreshToken: false
                        }
                    }
                );

                // First, get the user's ID from their username
                const { data: userData, error: userError } = await publicSupabase
                    .from('profiles')
                    .select('id')
                    .eq('username', username)
                    .single();

                if (userError || !userData) {
                    console.error('Error finding user by username:', userError);
                    loadingState.update((s) => ({ ...s, loading: false, error: 'User not found' }));
                    return;
                }

                const userId = userData.id;

                // Load all CV data for this user, using public Supabase instance
                try {
                    const data = await loadUserCvData(userId, publicSupabase);
                    data.username = username;

                    // Cache the data
                    cache[userId] = data;

                    // Update the store
                    set(data);

                    loadingState.update((s) => ({ ...s, loading: false }));
                    return data;
                } catch (loadError: any) {
                    console.error('Error loading CV data sections:', loadError);
                    loadingState.update((s) => ({
                        ...s,
                        loading: false,
                        error: loadError.message || 'Failed to load CV data sections'
                    }));
                }
            } catch (err: any) {
                console.error('Error loading CV data by username:', err);
                loadingState.update((s) => ({
                    ...s,
                    loading: false,
                    error: err.message || 'Failed to load CV data'
                }));
            }
        },

        // Clear the cache for a specific user
        clearCache: (userId: string | null = null) => {
            if (userId) {
                delete cache[userId];
            } else {
                cache = {};
            }
        }
    };
};

// Helper function to sanitize text fields in arrays
function sanitizeArray(arr: any[], textFields: string[]): any[] {
    if (!Array.isArray(arr)) return arr;

    return arr.map((item) => {
        const sanitizedItem = { ...item };
        for (const field of textFields) {
            if (typeof sanitizedItem[field] === 'string') {
                sanitizedItem[field] = sanitizeInput(sanitizedItem[field]);
            }
        }
        return sanitizedItem;
    });
}

// Sanitize CV data to prevent XSS
function sanitizeCvData(cvData: CvData): CvData {
    if (!cvData) return cvData;

    // Create a deep copy to avoid mutating original data
    const sanitized = { ...cvData };

    // Sanitize profile data
    if (sanitized.profile) {
        if (typeof sanitized.profile.full_name === 'string') {
            sanitized.profile.full_name = sanitizeInput(sanitized.profile.full_name);
        }
        if (typeof sanitized.profile.location === 'string') {
            sanitized.profile.location = sanitizeInput(sanitized.profile.location);
        }
    }

    // Sanitize each section
    if (Array.isArray(sanitized.workExperiences)) {
        sanitized.workExperiences = sanitizeArray(sanitized.workExperiences, [
            'company_name',
            'position',
            'description'
        ]);
    }

    if (Array.isArray(sanitized.projects)) {
        sanitized.projects = sanitizeArray(sanitized.projects, ['title', 'description', 'url']);
    }

    if (Array.isArray(sanitized.education)) {
        sanitized.education = sanitizeArray(sanitized.education, [
            'institution',
            'degree',
            'field_of_study',
            'description'
        ]);
    }

    if (Array.isArray(sanitized.skills)) {
        sanitized.skills = sanitizeArray(sanitized.skills, ['name', 'category']);
    }

    if (Array.isArray(sanitized.certifications)) {
        sanitized.certifications = sanitizeArray(sanitized.certifications, [
            'name',
            'issuer',
            'description'
        ]);
    }

    if (Array.isArray(sanitized.memberships)) {
        sanitized.memberships = sanitizeArray(sanitized.memberships, [
            'organisation',
            'role',
            'description'
        ]);
    }

    if (Array.isArray(sanitized.interests)) {
        sanitized.interests = sanitizeArray(sanitized.interests, ['name', 'description']);
    }

    return sanitized;
}

// Modify the loadUserCvData function to apply sanitization
async function loadUserCvData(userId: string, clientInstance?: any): Promise<CvData> {
    // Use provided client instance or default to supabase
    const client = clientInstance || supabase;

    // Try to refresh token if using the main client (not for public viewing)
    if (!clientInstance && browser) {
        try {
            const { error: refreshError } = await supabase.auth.refreshSession();
            if (refreshError) {
                console.warn('Could not refresh session before loading CV data:', refreshError.message);
            }
        } catch (refreshErr) {
            console.warn('Error during session refresh before loading CV data:', refreshErr);
        }
    }

    // Start with empty data
    const data: CvData = { ...defaultCvData, userId };

    try {
        // Load all sections in parallel for efficiency
        const [
            profileResult,
            workExperiencesResult,
            projectsResult,
            skillsResult,
            educationResult,
            certificationsResult,
            membershipResult,
            interestsResult,
            qualificationEquivalenceResult
        ] = await Promise.all([
            client.from('profiles').select('*').eq('id', userId).single(),
            client.from('work_experience').select('*').eq('profile_id', userId).order('start_date', { ascending: false }),
            client.from('projects').select('*').eq('profile_id', userId).order('start_date', { ascending: false }),
            client.from('skills').select('*').eq('profile_id', userId),
            client.from('education').select('*').eq('profile_id', userId).order('start_date', { ascending: false }),
            client.from('certifications').select('*').eq('profile_id', userId).order('date_obtained', { ascending: false }),
            client.from('professional_memberships').select('*').eq('profile_id', userId).order('start_date', { ascending: false }),
            client.from('interests').select('*').eq('profile_id', userId),
            client
                .from('professional_qualification_equivalence')
                .select('*')
                .eq('profile_id', userId)
        ]);

        // Check profile result - this is required
        if (profileResult.error) {
            console.error('Error loading profile:', profileResult.error);
            throw new Error('Failed to load profile data');
        }

        // Populate data with results, handle any errors gracefully
        data.profile = profileResult.data;
        data.workExperiences = workExperiencesResult.error ? [] : workExperiencesResult.data || [];
        data.projects = projectsResult.error ? [] : projectsResult.data || [];
        data.skills = skillsResult.error ? [] : skillsResult.data || [];
        data.education = educationResult.error ? [] : educationResult.data || [];
        data.certifications = certificationsResult.error ? [] : certificationsResult.data || [];
        data.memberships = membershipResult.error ? [] : membershipResult.data || [];
        data.interests = interestsResult.error ? [] : interestsResult.data || [];
        data.qualificationEquivalence = qualificationEquivalenceResult.error ? [] : qualificationEquivalenceResult.data || [];

        // Load responsibilities for each work experience entry
        if (data.workExperiences.length > 0) {
            try {
                // Fetch responsibilities for each work experience
                const workResponsibilities = await Promise.all(
                    data.workExperiences.map(async (work) => {
                        try {
                            // Query the responsibility categories for this work experience
                            const { data: categories, error: categoriesError } = await client
                                .from('responsibility_categories')
                                .select('*')
                                .eq('work_experience_id', work.id)
                                .order('sort_order', { ascending: true });

                            if (categoriesError || !categories || categories.length === 0) {
                                return { ...work, responsibilities: [] };
                            }

                            // Get all items for these categories
                            const categoryIds = categories.map((cat: any) => cat.id);
                            const { data: items, error: itemsError } = await client
                                .from('responsibility_items')
                                .select('*')
                                .in('category_id', categoryIds)
                                .order('sort_order', { ascending: true });

                            if (itemsError) {
                                return {
                                    ...work,
                                    responsibilities: categories.map((cat: any) => ({ ...cat, items: [] }))
                                };
                            }

                            // Group items by category
                            const responsibilities = categories.map((category: any) => ({
                                ...category,
                                items: items?.filter((item: any) => item.category_id === category.id) || []
                            }));

                            return { ...work, responsibilities };
                        } catch (err) {
                            console.error(`Error loading responsibilities for work experience ${work.id}:`, err);
                            return { ...work, responsibilities: [] };
                        }
                    })
                );

                // Replace the work experiences with the ones including responsibilities
                data.workExperiences = workResponsibilities;
            } catch (respErr) {
                console.error('Error loading work responsibilities:', respErr);
                // Continue with the work experiences we have, without responsibilities
            }
        }

        // Log any errors that occurred
        [
            { name: 'work experiences', result: workExperiencesResult },
            { name: 'projects', result: projectsResult },
            { name: 'skills', result: skillsResult },
            { name: 'education', result: educationResult },
            { name: 'certifications', result: certificationsResult },
            { name: 'memberships', result: membershipResult },
            { name: 'interests', result: interestsResult },
            { name: 'qualification equivalence', result: qualificationEquivalenceResult }
        ].forEach(item => {
            if (item.result.error) {
                console.error(`Error loading ${item.name}:`, item.result.error);
            }
        });

        // Apply sanitization before returning the data
        return sanitizeCvData(data);
    } catch (err) {
        console.error('Error loading CV data:', err);
        throw err;
    }
}

// Create and export the store instance
export const cvStore = createCvStore();

// Derived stores for convenience
export const profile = derived(cvStore, ($cvStore) => $cvStore.profile);
export const workExperiences = derived(cvStore, ($cvStore) => $cvStore.workExperiences);
export const skills = derived(cvStore, ($cvStore) => $cvStore.skills);
export const education = derived(cvStore, ($cvStore) => $cvStore.education);
export const projects = derived(cvStore, ($cvStore) => $cvStore.projects);
export const certifications = derived(cvStore, ($cvStore) => $cvStore.certifications);
export const memberships = derived(cvStore, ($cvStore) => $cvStore.memberships);
export const interests = derived(cvStore, ($cvStore) => $cvStore.interests);
export const qualificationEquivalence = derived(
    cvStore,
    ($cvStore) => $cvStore.qualificationEquivalence
);
