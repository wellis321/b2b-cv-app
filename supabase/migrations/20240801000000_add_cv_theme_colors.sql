-- Add CV theme color columns to profiles table
ALTER TABLE public.profiles
ADD COLUMN IF NOT EXISTS cv_header_from_color TEXT DEFAULT 'indigo-700',
ADD COLUMN IF NOT EXISTS cv_header_to_color TEXT DEFAULT 'purple-700';

-- Comment on columns
COMMENT ON COLUMN public.profiles.cv_header_from_color IS 'First color of gradient in CV header (Tailwind class name)';
COMMENT ON COLUMN public.profiles.cv_header_to_color IS 'Second color of gradient in CV header (Tailwind class name)';

-- Check if RLS is enabled for profiles table
DO $$
BEGIN
    -- Enable RLS if not already enabled
    IF NOT EXISTS (
        SELECT 1 FROM pg_tables
        WHERE tablename = 'profiles'
        AND rowsecurity = true
    ) THEN
        ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
    END IF;

    -- Check if the update policy exists and create it if it doesn't
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies
        WHERE tablename = 'profiles'
        AND policyname = 'Users can update their own profiles'
    ) THEN
        CREATE POLICY "Users can update their own profiles"
        ON public.profiles
        FOR UPDATE
        USING (auth.uid() = id)
        WITH CHECK (auth.uid() = id);
    END IF;

    -- Check if the select policy exists and create it if it doesn't
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies
        WHERE tablename = 'profiles'
        AND policyname = 'Public profiles are viewable by everyone'
    ) THEN
        CREATE POLICY "Public profiles are viewable by everyone"
        ON public.profiles
        FOR SELECT
        USING (true);
    END IF;
END $$;