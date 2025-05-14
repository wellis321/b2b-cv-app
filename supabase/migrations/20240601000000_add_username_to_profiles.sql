-- Add username column to profiles table
ALTER TABLE profiles ADD COLUMN username TEXT UNIQUE;

-- Create an index for faster lookups by username
CREATE INDEX idx_profiles_username ON profiles(username);

-- Set default usernames based on user ID to avoid null values
-- Note: In a production environment, you might want a more sophisticated approach
UPDATE profiles SET username = CONCAT('user', SUBSTRING(id::text, 1, 8)) WHERE username IS NULL;

-- After setting defaults, make the field required for new records
ALTER TABLE profiles ALTER COLUMN username SET NOT NULL;

-- Add a constraint to ensure username follows our formatting rules (letters, numbers, dashes, underscores only)
ALTER TABLE profiles ADD CONSTRAINT username_format CHECK (username ~ '^[a-z0-9][a-z0-9\-_]+$');

-- Update RLS policies to include username
CREATE POLICY "Public profiles are viewable by everyone" ON profiles
    FOR SELECT USING (true);

CREATE POLICY "Users can update their own profile" ON profiles
    FOR UPDATE USING (auth.uid() = id);