# Fix for Public CV Access Issue

This document outlines the steps needed to fix the issue with public CV pages not displaying all sections when accessed at `/cv/@[username]`.

## Issue Description

When viewing a public CV page (e.g., `/cv/@johndoe`), only the profile section is being displayed, even though data exists for other sections like work experience, education, and skills.

The issue is related to Supabase Row Level Security (RLS) policies. While the `profiles` table has a policy to allow public access, the other CV-related tables don't have similar policies.

## Solution

Two changes are needed to fix this issue:

1. Add RLS policies to all CV-related tables to allow public access
2. Fix some reactive state handling issues in the CV page component

## 1. Supabase RLS Policies

Run the following SQL commands in the Supabase SQL Editor to add public access policies to all CV-related tables:

```sql
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

-- Responsibility Categories
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
```

## 2. Code Changes to CV Page Component

The CV page component has been updated to:

1. Fix reactive state handling for `loading` state
2. Replace deprecated `onclick` attributes with Svelte's `on:click` event handlers
3. Replace `onerror` attribute with Svelte's `on:error` event handler
4. Extract the print function to a separate handler function

These changes ensure more consistent reactivity and better state handling when viewing public CVs.

## Testing the Fix

After applying these changes:

1. Make sure you have a user with a username set in their profile
2. Ensure this user has data in various CV sections (work experience, skills, etc.)
3. Visit `/cv/@username` (replacing "username" with the actual username)
4. Verify that all CV sections are now visible

## Technical Explanation

The issue occurred because:

1. RLS policies in Supabase control which rows can be accessed by different users
2. While the `profiles` table had a public access policy, other tables did not
3. This meant that non-authenticated users could see profile data but not other CV sections
4. The public policies we added use a JOIN to the profiles table to only allow access to CV data that belongs to profiles with usernames set (i.e., public profiles)

The code changes also ensure that the reactive state is properly updated when the CV data is loaded from the store.
