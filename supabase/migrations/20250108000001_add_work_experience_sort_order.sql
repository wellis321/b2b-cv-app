-- Add sort_order field to work_experience table
ALTER TABLE work_experience ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0;

-- Create index for better performance when sorting
CREATE INDEX idx_work_experience_sort_order ON work_experience(profile_id, sort_order);

-- Update existing records to have sequential sort_order based on start_date (newest first)
UPDATE work_experience
SET sort_order = subquery.rn
FROM (
    SELECT id, ROW_NUMBER() OVER (PARTITION BY profile_id ORDER BY start_date DESC, created_at DESC) as rn
    FROM work_experience
) subquery
WHERE work_experience.id = subquery.id;
