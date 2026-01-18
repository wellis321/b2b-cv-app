-- Add Organisation Email Configuration
-- Migration: 20250130_add_organisation_email
-- Description: Adds email address fields to organisations table for sending emails from organisation addresses

ALTER TABLE organisations
ADD COLUMN organisation_email VARCHAR(255) NULL COMMENT 'Email address used for sending organisation emails (invitations, etc.)',
ADD COLUMN organisation_email_name VARCHAR(255) NULL COMMENT 'Display name for organisation emails (e.g., "Acme Recruiting Team")';

-- Add index for faster lookups
ALTER TABLE organisations
ADD INDEX idx_organisation_email (organisation_email);

