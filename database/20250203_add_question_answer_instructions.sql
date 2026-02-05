-- Add answer instructions to job application questions
-- Migration: 20250203_add_question_answer_instructions
-- Description: Optional instructions per question (word limit, format, etc.) for AI to follow

ALTER TABLE job_application_questions
ADD COLUMN answer_instructions TEXT NULL COMMENT 'Optional stipulations: e.g. max 100 words, use bullet points' AFTER answer_text;
