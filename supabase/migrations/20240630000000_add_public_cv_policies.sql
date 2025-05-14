-- Add public access policies for all CV-related tables to enable
-- viewing of public CVs without authentication

-- Work Experience
CREATE POLICY "Public work experience is viewable by everyone" ON work_experience
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = work_experience.profile_id
            AND profiles.username IS NOT NULL  -- Only show for profiles with a username
        )
    );

-- Skills
CREATE POLICY "Public skills are viewable by everyone" ON skills
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = skills.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Education
CREATE POLICY "Public education is viewable by everyone" ON education
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = education.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Projects
CREATE POLICY "Public projects are viewable by everyone" ON projects
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = projects.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Certifications
CREATE POLICY "Public certifications are viewable by everyone" ON certifications
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = certifications.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Professional Memberships
CREATE POLICY "Public memberships are viewable by everyone" ON professional_memberships
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = professional_memberships.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Interests
CREATE POLICY "Public interests are viewable by everyone" ON interests
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = interests.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Professional Qualification Equivalence
CREATE POLICY "Public qualification equivalence is viewable by everyone" ON professional_qualification_equivalence
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM profiles
            WHERE profiles.id = professional_qualification_equivalence.profile_id
            AND profiles.username IS NOT NULL
        )
    );

-- Responsibility Categories (only needed if showing these on the public CV)
CREATE POLICY "Public responsibility categories are viewable by everyone" ON responsibility_categories
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM work_experience
            JOIN profiles ON profiles.id = work_experience.profile_id
            WHERE work_experience.id = responsibility_categories.work_experience_id
            AND profiles.username IS NOT NULL
        )
    );

-- Responsibility Items
CREATE POLICY "Public responsibility items are viewable by everyone" ON responsibility_items
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM responsibility_categories
            JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
            JOIN profiles ON profiles.id = work_experience.profile_id
            WHERE responsibility_items.category_id = responsibility_categories.id
            AND profiles.username IS NOT NULL
        )
    );

-- Supporting Evidence
CREATE POLICY "Public supporting evidence is viewable by everyone" ON supporting_evidence
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM professional_qualification_equivalence
            JOIN profiles ON profiles.id = professional_qualification_equivalence.profile_id
            WHERE supporting_evidence.qualification_equivalence_id = professional_qualification_equivalence.id
            AND profiles.username IS NOT NULL
        )
    );