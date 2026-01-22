-- Add custom homepage JavaScript support
-- Migration: 20250122_add_custom_homepage_js
-- Description: Adds JavaScript field to organisations table for custom homepage scripts

ALTER TABLE organisations
ADD COLUMN custom_homepage_js TEXT NULL COMMENT 'Custom homepage JavaScript code';


