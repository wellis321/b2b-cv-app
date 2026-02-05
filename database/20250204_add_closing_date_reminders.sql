-- Add status 'interested' (before applied) and user reminder preferences for job closing dates
-- Migration: 20250204_add_closing_date_reminders

-- Job applications: add 'interested' to status ENUM (for jobs not yet applied)
ALTER TABLE job_applications
MODIFY COLUMN status ENUM(
    'interested',
    'in_progress',
    'applied',
    'interviewing',
    'offered',
    'rejected',
    'accepted',
    'withdrawn'
) DEFAULT 'applied';

-- Profiles: closing date reminder settings (browser notifications)
-- reminder_days: comma-separated, e.g. '7,3,1' for 1 week, 3 days, 1 day before
ALTER TABLE profiles
ADD COLUMN closing_date_reminder_enabled TINYINT(1) NOT NULL DEFAULT 1,
ADD COLUMN closing_date_reminder_days VARCHAR(50) NOT NULL DEFAULT '7,3,1';
