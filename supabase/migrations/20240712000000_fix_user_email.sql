-- Fix for email being set to default value
-- NOTE: Replace 'your-user-id-here' with your actual user ID
-- NOTE: Replace 'your-real-email@example.com' with your actual email

-- First ensure the username constraint doesn't prevent updates
ALTER TABLE profiles ALTER COLUMN username DROP NOT NULL;

-- Update the profile with correct email (adjust these values for your user)
UPDATE profiles
SET email = auth.user().email
WHERE id = auth.uid() AND email = 'user@example.com';

-- Create policy to ensure email always matches auth.user().email on updates
DROP POLICY IF EXISTS "Ensure email always matches auth email" ON profiles;
CREATE POLICY "Ensure email always matches auth email"
ON profiles
FOR UPDATE
TO authenticated
USING (auth.uid() = id)
WITH CHECK (
  -- Either the email matches the auth email or it's not being changed
  (email = auth.user().email) OR
  (email IS NULL)
);

-- Update RLS policies to make profile updates work better
DROP POLICY IF EXISTS "Users can update their own profile" ON profiles;
CREATE POLICY "Users can update their own profile"
    ON profiles FOR UPDATE
    USING (auth.uid() = id);

-- Allow users to view their own profiles
DROP POLICY IF EXISTS "Users can view their own profile" ON profiles;
CREATE POLICY "Users can view their own profile"
    ON profiles FOR SELECT
    TO authenticated
    USING (auth.uid() = id);