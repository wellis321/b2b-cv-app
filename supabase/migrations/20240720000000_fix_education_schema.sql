-- Check if 'qualification' column exists and add it if it doesn't
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_name = 'education'
        AND column_name = 'qualification'
    ) THEN
        -- Add qualification column
        ALTER TABLE education ADD COLUMN qualification TEXT;

        -- Copy data from degree to qualification
        UPDATE education
        SET qualification = degree
        WHERE degree IS NOT NULL;

        -- Make qualification column NOT NULL after data migration
        ALTER TABLE education ALTER COLUMN qualification SET NOT NULL;
    END IF;
END$$;