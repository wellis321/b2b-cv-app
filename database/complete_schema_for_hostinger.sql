-- =====================================================
-- Complete Database Schema for B2B CV Builder
-- =====================================================
-- This file contains all tables and migrations needed
-- for the B2B CV Builder application.
-- 
-- Usage: Paste this entire file into phpMyAdmin SQL tab
-- Make sure you select your database first before running
-- =====================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- =====================================================
-- PROFILES TABLE (User accounts with CV profile data)
-- Includes organisation-related fields for multi-tenancy
-- =====================================================
CREATE TABLE IF NOT EXISTS profiles (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    username VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(50),
    location VARCHAR(255),
    linkedin_url TEXT,
    bio TEXT,
    photo_url TEXT,
    photo_responsive JSON,
    cv_header_from_color VARCHAR(50) DEFAULT '#4338ca',
    cv_header_to_color VARCHAR(50) DEFAULT '#7e22ce',
    date_format_preference VARCHAR(20) DEFAULT 'dd/mm/yyyy',

    -- Email verification
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    email_verification_expires DATETIME,

    -- Password reset
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,

    -- Profile photo visibility
    show_photo BOOLEAN DEFAULT TRUE,
    show_photo_pdf BOOLEAN DEFAULT TRUE,
    show_qr_code BOOLEAN DEFAULT FALSE,

    -- Subscription fields (for individual users - legacy B2C support)
    plan VARCHAR(50) DEFAULT 'free',
    subscription_status VARCHAR(50) DEFAULT 'inactive',
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255),
    subscription_current_period_end DATETIME,
    subscription_cancel_at DATETIME,
    template_preference VARCHAR(50) DEFAULT 'classic',

    -- Organisation/Multi-tenancy fields
    organisation_id VARCHAR(36),
    account_type VARCHAR(20) DEFAULT 'individual',
    managed_by VARCHAR(36),
    cv_visibility VARCHAR(20) DEFAULT 'public',
    cv_status VARCHAR(20) DEFAULT 'draft',
    invitation_token VARCHAR(255),

    -- AI Settings
    ai_service_preference VARCHAR(50),
    ollama_base_url VARCHAR(255),
    ollama_model VARCHAR(100),
    openai_api_key TEXT,
    anthropic_api_key TEXT,
    gemini_api_key TEXT,
    grok_api_key TEXT,
    huggingface_api_key TEXT,
    browser_ai_model VARCHAR(100),
    cv_rewrite_prompt_instructions TEXT,

    -- Custom CV Template (legacy - kept for backward compatibility)
    custom_cv_template_html TEXT,
    custom_cv_template_css TEXT,
    custom_cv_template_active BOOLEAN DEFAULT FALSE,
    custom_cv_template_description TEXT,

    -- Super Admin
    is_super_admin BOOLEAN DEFAULT FALSE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_profiles_email (email),
    INDEX idx_profiles_username (username),
    INDEX idx_profiles_organisation (organisation_id),
    INDEX idx_profiles_account_type (account_type),
    INDEX idx_profiles_managed_by (managed_by),
    INDEX idx_profiles_cv_status (cv_status),
    INDEX idx_ai_service_preference (ai_service_preference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ORGANISATIONS TABLE (Recruitment agencies)
-- =====================================================
CREATE TABLE IF NOT EXISTS organisations (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    custom_domain VARCHAR(255) UNIQUE,
    logo_url TEXT,
    primary_colour VARCHAR(7) DEFAULT '#4338ca',
    secondary_colour VARCHAR(7) DEFAULT '#7e22ce',

    -- Subscription fields (organisation-level billing)
    plan VARCHAR(50) DEFAULT 'agency_basic',
    subscription_status VARCHAR(50) DEFAULT 'inactive',
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255),
    subscription_current_period_end DATETIME,
    subscription_cancel_at DATETIME,
    max_candidates INT DEFAULT 10,
    max_team_members INT DEFAULT 3,

    -- Settings
    default_cv_visibility ENUM('private', 'organisation', 'public') DEFAULT 'organisation',
    allow_candidate_self_registration BOOLEAN DEFAULT FALSE,
    require_candidate_approval BOOLEAN DEFAULT TRUE,

    -- Organisation AI Settings
    org_ai_service_preference VARCHAR(50),
    org_openai_api_key TEXT,
    org_anthropic_api_key TEXT,
    org_gemini_api_key TEXT,
    org_grok_api_key TEXT,
    org_ollama_base_url VARCHAR(255),
    org_ollama_model VARCHAR(100),
    org_browser_ai_model VARCHAR(100),
    org_ai_enabled BOOLEAN DEFAULT FALSE,

    -- Custom Homepage
    custom_homepage_enabled BOOLEAN DEFAULT FALSE COMMENT 'Enable custom homepage instead of default template',
    custom_homepage_html TEXT NULL COMMENT 'Custom homepage HTML content',
    custom_homepage_css TEXT NULL COMMENT 'Custom homepage CSS styles',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_organisations_slug (slug),
    INDEX idx_organisations_custom_domain (custom_domain),
    INDEX idx_organisations_subscription_status (subscription_status),
    INDEX idx_org_ai_enabled (org_ai_enabled),
    INDEX idx_custom_homepage_enabled (custom_homepage_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ORGANISATION MEMBERS TABLE (Recruiters, Admins)
-- =====================================================
CREATE TABLE IF NOT EXISTS organisation_members (
    id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    role ENUM('owner', 'admin', 'recruiter', 'viewer') NOT NULL DEFAULT 'recruiter',
    is_active BOOLEAN DEFAULT TRUE,
    invited_by VARCHAR(36),
    joined_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES profiles(id) ON DELETE SET NULL,

    UNIQUE KEY unique_org_user (organisation_id, user_id),
    INDEX idx_org_members_org (organisation_id),
    INDEX idx_org_members_user (user_id),
    INDEX idx_org_members_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CANDIDATE INVITATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS candidate_invitations (
    id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36) NOT NULL,
    email VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    invited_by VARCHAR(36) NOT NULL,
    assigned_recruiter VARCHAR(36),
    token VARCHAR(255) UNIQUE NOT NULL,
    message TEXT,
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP,
    accepted_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_recruiter) REFERENCES profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (accepted_by) REFERENCES profiles(id) ON DELETE SET NULL,

    INDEX idx_invitations_org (organisation_id),
    INDEX idx_invitations_email (email),
    INDEX idx_invitations_token (token),
    INDEX idx_invitations_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TEAM INVITATIONS TABLE (For inviting recruiters/admins)
-- =====================================================
CREATE TABLE IF NOT EXISTS team_invitations (
    id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin', 'recruiter', 'viewer') NOT NULL DEFAULT 'recruiter',
    invited_by VARCHAR(36) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    message TEXT,
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP,
    accepted_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (accepted_by) REFERENCES profiles(id) ON DELETE SET NULL,

    INDEX idx_team_inv_org (organisation_id),
    INDEX idx_team_inv_email (email),
    INDEX idx_team_inv_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ACTIVITY LOG TABLE (Audit trail)
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36),
    user_id VARCHAR(36),
    target_user_id VARCHAR(36),
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (target_user_id) REFERENCES profiles(id) ON DELETE SET NULL,

    INDEX idx_activity_org (organisation_id),
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_target (target_user_id),
    INDEX idx_activity_action (action),
    INDEX idx_activity_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LIMIT INCREASE REQUESTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS limit_increase_requests (
    id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36) NOT NULL,
    requested_by VARCHAR(36) NOT NULL,
    request_type ENUM('candidates', 'team_members') NOT NULL,
    current_limit INT NOT NULL,
    requested_limit INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'denied', 'cancelled') DEFAULT 'pending',
    reviewed_by VARCHAR(36),
    reviewed_at DATETIME,
    review_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES profiles(id) ON DELETE SET NULL,
    INDEX idx_requests_org (organisation_id),
    INDEX idx_requests_status (status),
    INDEX idx_requests_type (request_type),
    INDEX idx_requests_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- WORK EXPERIENCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS work_experience (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    description TEXT,
    sort_order INT DEFAULT 0,
    hide_date BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_work_experience_profile (profile_id),
    INDEX idx_work_experience_sort (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RESPONSIBILITY CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS responsibility_categories (
    id VARCHAR(36) PRIMARY KEY,
    work_experience_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_experience_id) REFERENCES work_experience(id) ON DELETE CASCADE,
    INDEX idx_resp_cat_work_exp (work_experience_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RESPONSIBILITY ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS responsibility_items (
    id VARCHAR(36) PRIMARY KEY,
    category_id VARCHAR(36) NOT NULL,
    content TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES responsibility_categories(id) ON DELETE CASCADE,
    INDEX idx_resp_items_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROJECTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS projects (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    url TEXT,
    image_url TEXT,
    image_path TEXT,
    image_responsive JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_projects_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- EDUCATION TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS education (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    degree VARCHAR(255) NOT NULL,
    field_of_study VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_education_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SKILLS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS skills (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    level VARCHAR(50),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_skills_profile (profile_id),
    INDEX idx_skills_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CERTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS certifications (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    issuer VARCHAR(255) NOT NULL,
    date_obtained DATE,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_certifications_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROFESSIONAL MEMBERSHIPS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS professional_memberships (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    organisation VARCHAR(255) NOT NULL,
    role VARCHAR(255),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_memberships_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INTERESTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS interests (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_interests_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROFESSIONAL QUALIFICATION EQUIVALENCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS professional_qualification_equivalence (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    level VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_qual_equiv_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SUPPORTING EVIDENCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS supporting_evidence (
    id VARCHAR(36) PRIMARY KEY,
    qualification_equivalence_id VARCHAR(36) NOT NULL,
    content TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (qualification_equivalence_id) REFERENCES professional_qualification_equivalence(id) ON DELETE CASCADE,
    INDEX idx_evidence_qual (qualification_equivalence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROFESSIONAL SUMMARY TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS professional_summary (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_prof_summary_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROFESSIONAL SUMMARY STRENGTHS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS professional_summary_strengths (
    id VARCHAR(36) PRIMARY KEY,
    professional_summary_id VARCHAR(36) NOT NULL,
    strength VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_summary_id) REFERENCES professional_summary(id) ON DELETE CASCADE,
    INDEX idx_strengths_summary (professional_summary_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- REMOTE WORK STORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS remote_work_stories (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36) NOT NULL,
    story_title VARCHAR(255) NOT NULL,
    story_content TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_remote_stories_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- JOB APPLICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS job_applications (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    job_description TEXT,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('applied', 'interviewing', 'offered', 'rejected', 'accepted', 'withdrawn', 'in_progress') DEFAULT 'applied',
    salary_range VARCHAR(100),
    job_location VARCHAR(255),
    remote_type ENUM('onsite', 'hybrid', 'remote') DEFAULT 'onsite',
    application_url TEXT,
    notes TEXT,
    next_follow_up TIMESTAMP NULL,
    had_interview BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_application_date (application_date),
    INDEX idx_next_follow_up (next_follow_up),
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- JOB APPLICATION FILES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS job_application_files (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    application_id VARCHAR(36) NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    custom_name VARCHAR(255),
    mime_type VARCHAR(100) NOT NULL,
    size INT NOT NULL,
    file_purpose ENUM('resume', 'cover_letter', 'portfolio', 'other') DEFAULT 'other',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_application_id (application_id),
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES job_applications(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- JOB APPLICATION AUDIT LOGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS job_application_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(36),
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(36) NOT NULL,
    old_values JSON,
    new_values JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_action (user_id, action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variants (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    job_application_id VARCHAR(36) NULL,
    variant_name VARCHAR(255) NOT NULL,
    is_master BOOLEAN DEFAULT FALSE,
    created_from_variant_id VARCHAR(36) NULL,
    ai_generated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (job_application_id) REFERENCES job_applications(id) ON DELETE SET NULL,
    FOREIGN KEY (created_from_variant_id) REFERENCES cv_variants(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_job_application_id (job_application_id),
    INDEX idx_is_master (user_id, is_master)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT PROFESSIONAL SUMMARY
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_professional_summary (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cv_variant_professional_summary_strengths (
    id VARCHAR(36) PRIMARY KEY,
    professional_summary_id VARCHAR(36) NOT NULL,
    strength VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_summary_id) REFERENCES cv_variant_professional_summary(id) ON DELETE CASCADE,
    INDEX idx_professional_summary_id (professional_summary_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT WORK EXPERIENCE
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_work_experience (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_work_experience_id VARCHAR(36) NULL,
    company_name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    description TEXT,
    sort_order INT DEFAULT 0,
    hide_date BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_work_experience_id) REFERENCES work_experience(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id),
    INDEX idx_sort_order (cv_variant_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cv_variant_responsibility_categories (
    id VARCHAR(36) PRIMARY KEY,
    work_experience_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_experience_id) REFERENCES cv_variant_work_experience(id) ON DELETE CASCADE,
    INDEX idx_work_experience_id (work_experience_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cv_variant_responsibility_items (
    id VARCHAR(36) PRIMARY KEY,
    category_id VARCHAR(36) NOT NULL,
    content TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES cv_variant_responsibility_categories(id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT EDUCATION
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_education (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_education_id VARCHAR(36) NULL,
    institution VARCHAR(255) NOT NULL,
    degree VARCHAR(255) NOT NULL,
    field_of_study VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_education_id) REFERENCES education(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT PROJECTS
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_projects (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_project_id VARCHAR(36) NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    url TEXT,
    image_url TEXT,
    image_path TEXT,
    image_responsive JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT SKILLS
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_skills (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_skill_id VARCHAR(36) NULL,
    name VARCHAR(255) NOT NULL,
    level VARCHAR(50),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_skill_id) REFERENCES skills(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT CERTIFICATIONS
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_certifications (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_certification_id VARCHAR(36) NULL,
    name VARCHAR(255) NOT NULL,
    issuer VARCHAR(255) NOT NULL,
    date_obtained DATE NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_certification_id) REFERENCES certifications(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT PROFESSIONAL MEMBERSHIPS
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_memberships (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_membership_id VARCHAR(36) NULL,
    organisation VARCHAR(255) NOT NULL,
    role VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_membership_id) REFERENCES professional_memberships(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT INTERESTS
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_interests (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_interest_id VARCHAR(36) NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_interest_id) REFERENCES interests(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV VARIANT QUALIFICATION EQUIVALENCE
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_variant_qualification_equivalence (
    id VARCHAR(36) PRIMARY KEY,
    cv_variant_id VARCHAR(36) NOT NULL,
    original_qualification_id VARCHAR(36) NULL,
    level VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_qualification_id) REFERENCES professional_qualification_equivalence(id) ON DELETE SET NULL,
    INDEX idx_cv_variant_id (cv_variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cv_variant_supporting_evidence (
    id VARCHAR(36) PRIMARY KEY,
    qualification_equivalence_id VARCHAR(36) NOT NULL,
    content TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (qualification_equivalence_id) REFERENCES cv_variant_qualification_equivalence(id) ON DELETE CASCADE,
    INDEX idx_qualification_equivalence_id (qualification_equivalence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV QUALITY ASSESSMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_quality_assessments (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    cv_variant_id VARCHAR(36) NULL,
    overall_score INT,
    ats_score INT,
    content_score INT,
    formatting_score INT,
    keyword_match_score INT,
    recommendations JSON,
    enhanced_recommendations JSON,
    strengths JSON,
    weaknesses JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (cv_variant_id) REFERENCES cv_variants(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_cv_variant_id (cv_variant_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CV TEMPLATES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_templates (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    template_name VARCHAR(255) NOT NULL DEFAULT 'Untitled Template',
    template_html TEXT NOT NULL,
    template_css TEXT NULL,
    template_description TEXT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (user_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USER FEEDBACK TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS user_feedback (
    id VARCHAR(36) PRIMARY KEY,
    profile_id VARCHAR(36),
    feedback_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE SET NULL,
    INDEX idx_feedback_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Add Foreign Key Constraints (after all tables exist)
-- =====================================================
-- Note: Profiles table is created before organisations, so we add the FK constraint here.
-- If you get a "Duplicate key" error, the constraint already exists - that's OK, skip this.
-- Uncomment the line below if you need to add the foreign key constraint:

-- ALTER TABLE profiles ADD CONSTRAINT fk_profiles_organisation FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS=1;

-- =====================================================
-- Schema creation complete!
-- =====================================================
-- All tables have been created successfully.
-- You can now start using the B2B CV Builder application.
-- =====================================================

