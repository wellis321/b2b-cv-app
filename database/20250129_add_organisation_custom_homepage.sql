-- Add custom homepage support for organisations
-- Migration: 20250129_add_organisation_custom_homepage
-- Description: Adds columns to organisations table for storing custom homepage HTML/CSS

ALTER TABLE organisations
ADD COLUMN custom_homepage_enabled BOOLEAN DEFAULT FALSE COMMENT 'Enable custom homepage instead of default template',
ADD COLUMN custom_homepage_html TEXT NULL COMMENT 'Custom homepage HTML content',
ADD COLUMN custom_homepage_css TEXT NULL COMMENT 'Custom homepage CSS styles';

ALTER TABLE organisations
ADD INDEX idx_custom_homepage_enabled (custom_homepage_enabled);

