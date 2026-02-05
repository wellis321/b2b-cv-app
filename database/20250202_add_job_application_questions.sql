-- Add Application Questions to Job Applications
-- Migration: 20250202_add_job_application_questions
-- Description: Table for application form questions and AI-generated answers per job

CREATE TABLE IF NOT EXISTS job_application_questions (
    id VARCHAR(36) PRIMARY KEY,
    job_application_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    question_text TEXT NOT NULL,
    answer_text TEXT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_job_application_id (job_application_id),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (job_application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
