-- Fix script for work experience ordering issues
-- Run this in your Supabase SQL editor if work experiences are missing

-- First, let's check what we have
SELECT 'Current state:' as info;
SELECT
    id,
    company_name,
    position,
    start_date,
    end_date,
    sort_order,
    created_at
FROM work_experience
WHERE profile_id = (
    SELECT id FROM profiles WHERE username = 'wellis'
)
ORDER BY COALESCE(sort_order, 999999), start_date DESC;

-- If sort_order is NULL or missing, let's fix it
UPDATE work_experience
SET sort_order = subquery.rn
FROM (
    SELECT id, ROW_NUMBER() OVER (PARTITION BY profile_id ORDER BY start_date DESC, created_at DESC) as rn
    FROM work_experience
    WHERE profile_id = (
        SELECT id FROM profiles WHERE username = 'wellis'
    )
) subquery
WHERE work_experience.id = subquery.id
AND work_experience.profile_id = (
    SELECT id FROM profiles WHERE username = 'wellis'
);

-- Now let's verify the fix
SELECT 'After fix:' as info;
SELECT
    id,
    company_name,
    position,
    start_date,
    end_date,
    sort_order,
    created_at
FROM work_experience
WHERE profile_id = (
    SELECT id FROM profiles WHERE username = 'wellis'
)
ORDER BY sort_order ASC, start_date DESC;

-- If you still don't see work experiences, let's check if they exist at all
SELECT 'All work experiences for wellis user:' as info;
SELECT
    we.id,
    we.company_name,
    we.position,
    we.start_date,
    we.end_date,
    we.sort_order,
    we.profile_id,
    p.username
FROM work_experience we
JOIN profiles p ON we.profile_id = p.id
WHERE p.username = 'wellis';
