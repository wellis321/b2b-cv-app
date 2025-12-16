-- Add template preference fields to profiles table
-- Migration: 20241216_add_template_preferences
-- Description: Adds columns for user template preferences and customization settings

ALTER TABLE profiles
ADD COLUMN preferred_template_id VARCHAR(50) DEFAULT 'minimal',
ADD COLUMN template_accent_color VARCHAR(7) DEFAULT NULL,
ADD COLUMN template_font_size VARCHAR(20) DEFAULT 'medium',
ADD COLUMN template_spacing VARCHAR(20) DEFAULT 'normal',
ADD COLUMN template_customization_json TEXT DEFAULT NULL,
ADD INDEX idx_profiles_template (preferred_template_id);

-- Set default template based on current plan for existing users
UPDATE profiles
SET preferred_template_id = 'professional'
WHERE plan IN ('pro_monthly', 'pro_annual', 'lifetime')
AND preferred_template_id = 'minimal';

-- Note: Run this migration after backing up the database
-- To rollback:
-- ALTER TABLE profiles DROP COLUMN preferred_template_id, DROP COLUMN template_accent_color,
-- DROP COLUMN template_font_size, DROP COLUMN template_spacing, DROP COLUMN template_customization_json;
