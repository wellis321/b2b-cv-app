-- Debug script to check work experience data
-- Run this in your Supabase SQL editor to see what's happening

-- Check if sort_order field exists
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns
WHERE table_name = 'work_experience'
AND column_name = 'sort_order';

-- Check current work experience data with sort_order
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

-- Check if there are any NULL sort_order values
SELECT COUNT(*) as null_sort_orders
FROM work_experience
WHERE profile_id = (
    SELECT id FROM profiles WHERE username = 'wellis'
) AND sort_order IS NULL;

-- Check the profiles table for the wellis user
SELECT id, username, first_name, last_name
FROM profiles
WHERE username = 'wellis';
