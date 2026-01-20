# AI CV Features Documentation

## Overview

Simple CV Builder now includes powerful AI-powered features to help users create better CVs and improve their job application success rate. These features use artificial intelligence to rewrite CVs for specific job applications and provide quality assessments.

**AI Technology:** All AI features run directly in your browser using browser-based AI technology. No cloud services, API keys, or configuration required - it works immediately for all users. For organisations or users who prefer cloud-based AI (OpenAI, Anthropic, Gemini, etc.), those options are also available if configured in account settings.

## Features

### 1. AI CV Rewriting

Generate job-specific CV variants automatically by analyzing job descriptions and tailoring your CV content to match.

**How it works:**
- Paste a job description or select from your tracked job applications
- AI analyzes the job requirements and your current CV
- Automatically rewrites relevant sections (professional summary, work experience, skills)
- Creates a new CV variant linked to the job application
- You can review, edit, and customise the AI-generated content

**Access:**
- From Job Applications page: Click "Generate AI CV" button on any job application
- From CV Variants page: Click "Generate AI CV" button
- Direct URL: `/cv-variants/rewrite.php`

**Benefits:**
- Saves time by automatically tailoring your CV
- Improves keyword matching for ATS systems
- Maintains factual accuracy (doesn't invent experiences)
- Creates multiple versions for different job types

### 2. CV Quality Assessment

Get comprehensive AI-powered feedback on your CV with scores and actionable recommendations.

**What it assesses:**
- **Overall Quality** (0-100): General CV completeness and professionalism
- **ATS Compatibility** (0-100): How well your CV will pass Applicant Tracking Systems
- **Content Quality** (0-100): Relevance, impact, and specificity of content
- **Formatting** (0-100): Consistency, readability, and structure
- **Keyword Matching** (0-100): Alignment with job requirements (when job description provided)

**Feedback includes:**
- Strengths: What you're doing well
- Weaknesses: Areas that need improvement
- Recommendations: Specific, actionable steps to improve your CV

**Access:**
- From CV Variants page: Click "Assess" on any CV variant
- From Job Applications page: Click "Assess CV Quality" button
- Direct URL: `/cv-quality.php?variant_id=XXX`

### 3. CV Variants Management

Create and manage multiple versions of your CV for different job applications.

**Features:**
- **Master CV**: Your main CV that serves as the base for all variants
- **Job-Specific Variants**: Tailored CVs linked to specific job applications
- **AI-Generated Variants**: CVs created using AI rewriting
- **Custom Variants**: Manually created CV versions

**Management:**
- View all variants in one place
- Rename variants for easy identification
- Delete variants you no longer need
- Edit any variant like a normal CV
- View variants in full CV format

**Access:**
- Direct URL: `/cv-variants.php`
- Linked from Job Applications page

## Technical Details

### AI Service Configuration

The system supports multiple AI providers:

1. **Ollama (Local, Free)** - Default for development
   - Runs locally on your machine
   - No API costs
   - Requires Ollama installation
   - Configured via `.env`:
     ```
     AI_SERVICE=ollama
     OLLAMA_BASE_URL=http://localhost:11434
     OLLAMA_MODEL=llama3.2:3b
     ```

2. **OpenAI API** - Production option
   - Requires API key
   - Configured via `.env`:
     ```
     AI_SERVICE=openai
     OPENAI_API_KEY=your_key_here
     OPENAI_MODEL=gpt-4-turbo-preview
     ```

3. **Anthropic API (Claude)** - Production option
   - Requires API key
   - Configured via `.env`:
     ```
     AI_SERVICE=anthropic
     ANTHROPIC_API_KEY=your_key_here
     ANTHROPIC_MODEL=claude-3-opus-20240229
     ```

### Database Schema

**CV Variants Table:**
- Stores variant metadata (name, linked job application, creation source)
- Links to user and job applications
- Tracks if variant is master CV or AI-generated

**CV Variant Data Tables:**
- Separate tables for each CV section (work experience, education, skills, etc.)
- Mirrors structure of master CV tables
- Links to `cv_variant_id` instead of `profile_id`

**CV Quality Assessments Table:**
- Stores assessment results with scores and recommendations
- Links to CV variants and users
- JSON fields for strengths, weaknesses, and recommendations

### API Endpoints

**POST `/api/ai-rewrite-cv.php`**
- Generates job-specific CV variant
- Requires: `job_application_id` or `job_description`, `csrf_token`
- Optional: `cv_variant_id` (source CV, defaults to master)
- Returns: `variant_id` of newly created variant

**POST `/api/ai-assess-cv.php`**
- Assesses CV quality
- Requires: `csrf_token`
- Optional: `cv_variant_id`, `job_application_id`
- Returns: Assessment with scores and recommendations

## User Guide

### Creating Your First AI CV

1. **Prepare your master CV**
   - Ensure your master CV is complete with work experience, skills, education, etc.
   - The AI uses this as the base for rewriting

2. **Find a job application**
   - Add a job application in the Job Applications tracker
   - Include the full job description in the "Job Description" field
   - **Or upload a job description file** (PDF, Word, Excel) - the AI will automatically read it

3. **Generate AI CV**
   - Open the job application
   - Click "Generate AI CV" button
   - The AI will use uploaded files if available, or the text in the job description field
   - Wait for AI processing (may take 30-60 seconds)
   - Review the generated variant

4. **Review and edit**
   - Check the AI-generated content
   - Make any necessary edits
   - The variant is saved and linked to the job application

### Assessing CV Quality

1. **Select a CV variant**
   - Go to CV Variants page
   - Choose the variant you want to assess

2. **Run assessment**
   - Click "Assess" or "Run Assessment"
   - Wait for AI analysis (may take 20-40 seconds)

3. **Review results**
   - Check scores for each category
   - Read strengths and weaknesses
   - Follow recommendations to improve

4. **Make improvements**
   - Edit your CV based on feedback
   - Re-assess to see improvement

### Managing CV Variants

- **View all variants**: Go to `/cv-variants.php`
- **Rename variant**: Click "Rename" button
- **Delete variant**: Click "Delete" button (master CV cannot be deleted)
- **View variant**: Click "View" to see full CV
- **Edit variant**: Click "View" then navigate to section pages (editing support coming soon)

## File Uploads for Job Applications

### Supported File Types
- **PDF files** (.pdf)
- **Word documents** (.doc, .docx)
- **Excel spreadsheets** (.xls, .xlsx)
- **Text files** (.txt, .csv)
- **Images** (.jpg, .jpeg, .png) - with optional OCR support

### How File Uploads Work with AI

1. **Upload Files**
   - When creating or editing a job application, use the file upload section
   - Drag and drop files or click to browse
   - Multiple files can be uploaded per application
   - Maximum file size: 10MB per file

2. **AI Integration**
   - When you click "Generate AI CV", the system automatically:
     - Checks for uploaded files
     - Extracts text from files (PDF, Word, Excel, text)
     - Combines file content with any text in the job description field
     - Uses the combined content to generate your CV variant

3. **Text Extraction**
   - Click "Extract Text" on any uploaded file to populate the job description field
   - Useful for reviewing or editing the extracted content
   - Works with PDF, Word, Excel, and text files

### Benefits
- **No manual copying**: Upload the job posting file directly
- **Better accuracy**: AI reads the complete job description from files
- **Time saving**: Skip copy-paste steps
- **File organisation**: Keep all application materials in one place

## Best Practices

1. **Keep master CV updated**: AI rewriting uses your master CV as the base
2. **Include complete job descriptions**: More detail = better AI rewriting
3. **Review AI output**: Always check AI-generated content for accuracy
4. **Use quality assessments**: Regularly assess your CV to identify improvements
5. **Create variants strategically**: Generate variants for jobs that are good matches
6. **Edit after generation**: Fine-tune AI-generated content to match your voice

## Limitations

- AI rewriting maintains factual accuracy but may need manual refinement
- Quality assessments are AI-generated suggestions, not guarantees
- Processing time depends on AI service (local Ollama may be slower)
- Requires internet connection for cloud AI services
- Local Ollama requires sufficient system resources

## Troubleshooting

**AI rewriting fails:**
- Check AI service configuration in `.env`
- Verify API keys are correct (for cloud services)
- Ensure Ollama is running (for local service)
- Check job description is not empty

**Quality assessment fails:**
- Ensure CV has sufficient content
- Check AI service is accessible
- Verify database connection

**Variant not displaying:**
- Check variant exists in database
- Verify user has access to variant
- Ensure CV data is properly loaded

## Future Enhancements

Planned improvements:
- Preview changes before saving AI-generated variants
- Accept/reject individual section changes
- Batch generate variants for multiple jobs
- Compare variants side-by-side
- Export assessment reports
- Integration with job matching services

## Support

For issues or questions:
- Check this documentation
- Review error messages in browser console
- Check server logs for detailed errors
- Contact support with specific error details

