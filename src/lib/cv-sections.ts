import { writable, derived } from 'svelte/store';
import { browser } from '$app/environment';
import { supabase } from './supabase';

export interface CVSection {
    id: string;
    name: string;
    path: string;
    description: string;
    dataTable?: string;
}

export interface SectionStatus {
    isComplete: boolean;
    count: number;
}

// Define all CV sections and their order
export const CV_SECTIONS: CVSection[] = [
    {
        id: 'profile',
        name: 'Personal Profile',
        path: '/profile',
        description: 'Your basic information and contact details',
        dataTable: 'profiles'
    },
    {
        id: 'work-experience',
        name: 'Work Experience',
        path: '/work-experience',
        description: 'Your employment history',
        dataTable: 'work_experience'
    },
    {
        id: 'education',
        name: 'Education',
        path: '/education',
        description: 'Your academic background',
        dataTable: 'education'
    },
    {
        id: 'projects',
        name: 'Projects',
        path: '/projects',
        description: 'Key projects you have worked on',
        dataTable: 'projects'
    },
    {
        id: 'skills',
        name: 'Skills',
        path: '/skills',
        description: 'Your technical and soft skills',
        dataTable: 'skills'
    },
    {
        id: 'certifications',
        name: 'Certifications',
        path: '/certifications',
        description: 'Professional certifications and credentials',
        dataTable: 'certifications'
    },
    {
        id: 'memberships',
        name: 'Professional Memberships',
        path: '/memberships',
        description: 'Professional organisations and memberships',
        dataTable: 'memberships'
    },
    {
        id: 'interests',
        name: 'Interests & Activities',
        path: '/interests',
        description: 'Your hobbies and personal interests',
        dataTable: 'interests'
    }
];

// Find section by current path
export function getCurrentSection(path: string): CVSection | undefined {
    const normalizedPath = path.endsWith('/') ? path.slice(0, -1) : path;
    return CV_SECTIONS.find(section => section.path === normalizedPath);
}

// Get next and previous sections based on current path
export function getAdjacentSections(path: string): { prev: CVSection | null, next: CVSection | null } {
    const currentIndex = CV_SECTIONS.findIndex(section => section.path === path);

    if (currentIndex === -1) return { prev: null, next: null };

    const prev = currentIndex > 0 ? CV_SECTIONS[currentIndex - 1] : null;
    const next = currentIndex < CV_SECTIONS.length - 1 ? CV_SECTIONS[currentIndex + 1] : null;

    return { prev, next };
}

// Create a store to track the completion status of each section
export const sectionStatus = writable<Record<string, SectionStatus>>(
    CV_SECTIONS.reduce<Record<string, SectionStatus>>((acc, section) => {
        acc[section.id] = { isComplete: false, count: 0 };
        return acc;
    }, {})
);

// Function to update section status based on data counts
export async function updateSectionStatus(): Promise<void> {
    if (!browser) return;

    const { data: { session } } = await supabase.auth.getSession();
    if (!session) return;

    const userId = session.user.id;

    for (const section of CV_SECTIONS) {
        if (!section.dataTable) continue;

        try {
            // Special handling for the profile section
            if (section.id === 'profile') {
                // Check if profile exists and has at least a name
                const { data: profileData, error: profileError } = await supabase
                    .from('profiles')
                    .select('full_name')
                    .eq('id', userId)
                    .maybeSingle();

                if (!profileError) {
                    const isComplete = !!profileData && !!profileData.full_name;
                    sectionStatus.update(status => {
                        status[section.id] = {
                            isComplete,
                            count: isComplete ? 1 : 0
                        };
                        return status;
                    });
                }
            } else {
                // For other sections, just check the count
                const { count, error } = await supabase
                    .from(section.dataTable)
                    .select('*', { count: 'exact', head: true })
                    .eq('profile_id', userId);

                if (!error) {
                    sectionStatus.update(status => {
                        status[section.id] = {
                            isComplete: (count || 0) > 0,
                            count: count || 0
                        };
                        return status;
                    });
                }
            }
        } catch (err) {
            console.error(`Error checking ${section.id} status:`, err);
        }
    }
}