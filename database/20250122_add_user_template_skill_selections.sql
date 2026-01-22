-- Add user_template_skill_selections table
-- Migration: 20250122_add_user_template_skill_selections
-- Description: Stores user's selected skills for PDF export per template

CREATE TABLE IF NOT EXISTS user_template_skill_selections (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    template_id VARCHAR(36) NOT NULL,
    selected_skill_ids TEXT NOT NULL COMMENT 'JSON array of skill IDs',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_template (user_id, template_id),
    INDEX idx_user_id (user_id),
    INDEX idx_template_id (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

