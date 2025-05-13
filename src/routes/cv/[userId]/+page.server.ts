import type { PageServerLoad } from './$types';
import { createClient } from '@supabase/supabase-js';
import config from '$lib/config';
import type { Database } from '$lib/database.types';

export const load: PageServerLoad = async ({ params }) => {
    const { userId } = params;

    // Create a new Supabase client just for this request to bypass auth
    const supabase = createClient<Database>(
        config.supabase.url,
        config.supabase.anonKey,
        {
            auth: {
                persistSession: false,
                autoRefreshToken: false
            }
        }
    );

    try {
        // Fetch profile data
        const { data: profile, error: profileError } = await supabase
            .from('profiles')
            .select('full_name, email, phone, location, photo_url')
            .eq('id', userId)
            .single();

        if (profileError) {
            console.error('Error loading profile for public view:', profileError);
            return { params, error: 'CV not found', profile: null };
        }

        // Fetch work experiences
        const { data: workExperiences, error: workError } = await supabase
            .from('work_experience')
            .select('*')
            .eq('profile_id', userId)
            .order('start_date', { ascending: false });

        if (workError) {
            console.error('Error loading work experiences for public view:', workError);
        }

        // Fetch projects
        const { data: projects, error: projectsError } = await supabase
            .from('projects')
            .select('*')
            .eq('profile_id', userId)
            .order('start_date', { ascending: false });

        if (projectsError) {
            console.error('Error loading projects for public view:', projectsError);
        }

        // Fetch skills
        const { data: skills, error: skillsError } = await supabase
            .from('skills')
            .select('*')
            .eq('profile_id', userId);

        if (skillsError) {
            console.error('Error loading skills for public view:', skillsError);
        }

        // Fetch education
        let education = [];
        try {
            const { data: educationData, error: educationError } = await supabase
                .from('education')
                .select('*')
                .eq('profile_id', userId)
                .order('start_date', { ascending: false });

            if (!educationError && educationData) {
                education = educationData;
            }
        } catch (err) {
            console.log('Education table might not exist yet or error fetching:', err);
        }

        // Fetch certifications
        let certifications = [];
        try {
            const { data: certData, error: certError } = await supabase
                .from('certifications')
                .select('*')
                .eq('profile_id', userId)
                .order('date_obtained', { ascending: false });

            if (!certError && certData) {
                // Map database fields to expected fields in the UI
                certifications = certData.map(cert => ({
                    ...cert,
                    date_issued: cert.date_obtained // Map for consistency
                }));
            }
        } catch (err) {
            console.log('Certifications table might not exist yet or error fetching:', err);
        }

        // Fetch memberships
        let memberships = [];
        try {
            const { data: membershipData, error: membershipError } = await supabase
                .from('professional_memberships')
                .select('*')
                .eq('profile_id', userId)
                .order('start_date', { ascending: false });

            if (!membershipError && membershipData) {
                memberships = membershipData;
            }
        } catch (err) {
            console.log('Professional memberships table might not exist yet or error fetching:', err);
        }

        // Fetch interests
        let interests = [];
        try {
            const { data: interestsData, error: interestsError } = await supabase
                .from('interests')
                .select('*')
                .eq('profile_id', userId);

            if (!interestsError && interestsData) {
                interests = interestsData;
            }
        } catch (err) {
            console.log('Interests table might not exist yet or error fetching:', err);
        }

        // Fetch qualification equivalence
        let qualificationEquivalence = [];
        try {
            const { data: qualificationData, error: qualificationError } = await supabase
                .from('professional_qualification_equivalence')
                .select('*')
                .eq('profile_id', userId);

            if (!qualificationError && qualificationData) {
                qualificationEquivalence = qualificationData;
            }
        } catch (err) {
            console.log('Professional qualification equivalence table might not exist yet or error fetching:', err);
        }

        return {
            params,
            profile,
            workExperiences: workExperiences || [],
            projects: projects || [],
            skills: skills || [],
            education: education || [],
            certifications: certifications || [],
            memberships: memberships || [],
            interests: interests || [],
            qualificationEquivalence: qualificationEquivalence || []
        };
    } catch (err) {
        console.error('Unexpected error loading public CV data:', err);
        return { params, error: 'An error occurred loading this CV', profile: null };
    }
};