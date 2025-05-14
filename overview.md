# CV Builder Application Overview

## Introduction

The CV Builder application allows users to create their professional CV/resume through a user-friendly interface. The application is built with SvelteKit, offering both frontend and backend capabilities, and uses Supabase for data storage and user authentication.

## Sections

### 1. Personal Profile

This section captures the user's basic personal information:

- **Full Name** (first name, middle name, last name) - _first and last name required_
- **Email** - _optional_
- **Phone** - _optional_
- **Location** - _optional_
- **Photo** - _optional_, with camera integration for direct capture

### 2. Work Experience

Displays professional history in descending date order (most recent first):

- **Experience Title** - Position or role at the company
- **Experience Start Date** - When the position began
- **Experience End Date** - When the position ended (or "Present" if current)
- **Description** - Overview of the role and achievements
- **Key Responsibilities** - Categorized responsibilities with ability to create multiple items within each category

### 3. Education

Details of educational background:

- **Institution Name**
- **Degree/Qualification**
- **Field of Study**
- **Start Date**
- **End Date**
- **Description** - Additional details about courses, achievements, etc.

### 4. Projects

Showcase of significant projects:

- **Project Name**
- **Duration/Dates**
- **Description**
- **Technologies Used**
- **Link** - Optional URL to the project
- **Outcomes/Results**

### 5. Skills

List of professional skills:

- **Skill Name**
- **Proficiency Level** - Optional rating
- **Category** - For organizing skills (e.g., Technical, Soft Skills, Languages)

### 6. Certifications

Professional certifications and credentials:

- **Certification Name**
- **Issuing Organization**
- **Date Obtained**
- **Expiration Date** (if applicable)
- **Credential ID** (optional)

### 7. Professional Qualification Equivalence

For international qualifications or degree equivalence:

- **Original Qualification**
- **Equivalent Qualification**
- **Accredited By**
- **Country/Region**

### 8. Professional Memberships

Affiliations with professional organizations:

- **Organization Name**
- **Membership Type**
- **Member Since**
- **Membership ID** (optional)

### 9. Interests and Activities

Personal interests that demonstrate character and additional skills:

- **Interest/Activity Name**
- **Description** - Brief explanation of involvement or relevance

## Output Options

The completed CV can be:

1. **Viewed as a Website** - Online presentation format

2. **Exported as PDF** - Formatted document for printing or sharing
   - Users can select which sections to include in the PDF export
   - Each section (Personal Profile, Work Experience, Education, etc.) can be toggled on/off
   - Photo from Personal Profile can be included or excluded based on user preference
   - Users can choose from multiple layout templates for the PDF export
   - Order of sections can be customized for the PDF

## Technical Features

- User authentication and profile management
- Data persistence with Supabase
- Photo upload and camera integration
- Responsive design for all device sizes
- PDF generation capability with customizable sections
- User has their own cv page with a URL that is unique for them
