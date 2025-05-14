import { writable, derived, get } from 'svelte/store';
import { supabase } from '$lib/supabase';
import { browser } from '$app/environment';
import { session } from './authStore';
import type { Database } from '$lib/database.types';
import { createClient } from '@supabase/supabase-js';
import config from '$lib/config';

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
                loadingState.update(s => ({ ...s, error: 'User not logged in' }));
                return;
            }

            const userId = currentSession.user.id;

            // Return cached data if available
            if (cache[userId]) {
                set(cache[userId]);
                return;
            }

            try {
                loadingState.update(s => ({ ...s, loading: true, error: null }));

                // First get the profile to check username
                const { data: profile, error: profileError } = await supabase
                    .from('profiles')
                    .select('*')
                    .eq('id', userId)
                    .single();

                if (profileError) {
                    console.error('Error loading profile:', profileError);
                    loadingState.update(s => ({ ...s, error: 'Failed to load profile data' }));
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

                loadingState.update(s => ({ ...s, loading: false }));
                return data;
            } catch (err: any) {
                console.error('Error loading CV data:', err);
                loadingState.update(s => ({
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
            const cachedEntry = Object.values(cache).find(entry => entry.username === username);
            if (cachedEntry) {
                set(cachedEntry);
                return cachedEntry;
            }

            try {
                loadingState.update(s => ({ ...s, loading: true, error: null }));

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
                    loadingState.update(s => ({ ...s, loading: false, error: 'User not found' }));
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

                    loadingState.update(s => ({ ...s, loading: false }));
                    return data;
                } catch (loadError: any) {
                    console.error('Error loading CV data sections:', loadError);
                    loadingState.update(s => ({
                        ...s,
                        loading: false,
                        error: loadError.message || 'Failed to load CV data sections'
                    }));
                }
            } catch (err: any) {
                console.error('Error loading CV data by username:', err);
                loadingState.update(s => ({
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

// Helper function to load all CV data for a user
async function loadUserCvData(userId: string, clientInstance?: any): Promise<CvData> {
    // Start with empty data
    const data: CvData = { ...defaultCvData, userId };

    // Use the provided client instance or fall back to the default supabase client
    const client = clientInstance || supabase;

    try {
        // Fetch profile data
        const { data: profile, error: profileError } = await client
            .from('profiles')
            .select('*')
            .eq('id', userId)
            .single();

        if (profileError) {
            console.error('Error loading profile:', profileError);
            throw new Error('Failed to load profile data');
        }

        data.profile = profile;

        // Load work experiences
        const { data: workData, error: workError } = await client
            .from('work_experience')
            .select('*')
            .eq('profile_id', userId)
            .order('start_date', { ascending: false });

        if (!workError) {
            data.workExperiences = workData || [];
        } else {
            console.error('Error loading work experiences:', workError);
        }

        // Load projects
        const { data: projectsData, error: projectsError } = await client
            .from('projects')
            .select('*')
            .eq('profile_id', userId)
            .order('start_date', { ascending: false });

        if (!projectsError) {
            data.projects = projectsData || [];
        } else {
            console.error('Error loading projects:', projectsError);
        }

        // Load skills
        const { data: skillsData, error: skillsError } = await client
            .from('skills')
            .select('*')
            .eq('profile_id', userId);

        if (!skillsError) {
            data.skills = skillsData || [];
        } else {
            console.error('Error loading skills:', skillsError);
        }

        // Load education
        try {
            const { data: educationData, error: educationError } = await client
                .from('education')
                .select('*')
                .eq('profile_id', userId)
                .order('start_date', { ascending: false });

            if (!educationError) {
                data.education = educationData || [];
            } else {
                console.error('Error loading education:', educationError);
            }
        } catch (err) {
            // Silently handle missing table
            data.education = [];
        }

        // Load certifications
        try {
            const { data: certData, error: certError } = await client
                .from('certifications')
                .select('*')
                .eq('profile_id', userId)
                .order('date_obtained', { ascending: false });

            if (!certError) {
                data.certifications = certData || [];
            } else {
                console.error('Error loading certifications:', certError);
            }
        } catch (err) {
            // Silently handle missing table
            data.certifications = [];
        }

        // Load memberships
        try {
            const { data: membershipData, error: membershipError } = await client
                .from('professional_memberships')
                .select('*')
                .eq('profile_id', userId)
                .order('start_date', { ascending: false });

            if (!membershipError) {
                data.memberships = membershipData || [];
            } else {
                console.error('Error loading memberships:', membershipError);
            }
        } catch (err) {
            // Silently handle missing table
            data.memberships = [];
        }

        // Load interests
        try {
            const { data: interestsData, error: interestsError } = await client
                .from('interests')
                .select('*')
                .eq('profile_id', userId);

            if (!interestsError) {
                data.interests = interestsData || [];
            } else {
                console.error('Error loading interests:', interestsError);
            }
        } catch (err) {
            // Silently handle missing table
            data.interests = [];
        }

        // Load qualification equivalence
        try {
            const { data: qualificationData, error: qualificationError } = await client
                .from('professional_qualification_equivalence')
                .select('*')
                .eq('profile_id', userId);

            if (!qualificationError) {
                data.qualificationEquivalence = qualificationData || [];
            } else {
                console.error('Error loading qualification equivalence:', qualificationError);
            }
        } catch (err) {
            // Silently handle missing table
            data.qualificationEquivalence = [];
        }

        return data;
    } catch (error) {
        console.error('Error in loadUserCvData:', error);
        throw error;
    }
}

// Create and export the store instance
export const cvStore = createCvStore();

// Derived stores for convenience
export const profile = derived(cvStore, $cvStore => $cvStore.profile);
export const workExperiences = derived(cvStore, $cvStore => $cvStore.workExperiences);
export const skills = derived(cvStore, $cvStore => $cvStore.skills);
export const education = derived(cvStore, $cvStore => $cvStore.education);
export const projects = derived(cvStore, $cvStore => $cvStore.projects);
export const certifications = derived(cvStore, $cvStore => $cvStore.certifications);
export const memberships = derived(cvStore, $cvStore => $cvStore.memberships);
export const interests = derived(cvStore, $cvStore => $cvStore.interests);
export const qualificationEquivalence = derived(cvStore, $cvStore => $cvStore.qualificationEquivalence);