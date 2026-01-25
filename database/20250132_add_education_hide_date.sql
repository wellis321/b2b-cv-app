-- Add hide_date to education (match work_experience behaviour)
ALTER TABLE education ADD COLUMN hide_date TINYINT(1) NOT NULL DEFAULT 0;

-- Add hide_date to cv_variant_education for variant CVs
ALTER TABLE cv_variant_education ADD COLUMN hide_date TINYINT(1) NOT NULL DEFAULT 0;
