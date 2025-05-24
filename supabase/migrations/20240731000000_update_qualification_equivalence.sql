-- Add missing columns to professional_qualification_equivalence table
ALTER TABLE professional_qualification_equivalence
ADD COLUMN IF NOT EXISTS qualification TEXT;

ALTER TABLE professional_qualification_equivalence
ADD COLUMN IF NOT EXISTS equivalent_to TEXT;

-- Update existing records to set qualification = level for compatibility
UPDATE professional_qualification_equivalence
SET qualification = level
WHERE qualification IS NULL;

-- Add migration comment
COMMENT ON TABLE professional_qualification_equivalence IS 'Table storing professional qualification equivalence information with additional fields for UI compatibility';