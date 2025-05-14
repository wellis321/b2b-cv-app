# Deployment Guide

This document outlines the process for deploying the CV App to production.

## Prerequisites

- GitHub account
- Supabase account
- Vercel account

## Production Environment Setup

### 1. Supabase Setup

1. Create a new Supabase project for production:

   - Log in to your Supabase account
   - Create a new project with a name like "cv-app-production"
   - Take note of the generated database password
   - Navigate to Project Settings > Database to find your connection details

2. Apply the database schema:
   - Go to the SQL Editor in your Supabase project
   - Copy the contents of `supabase/schema.sql` from the repository
   - Run the SQL to create all necessary tables and policies

### 2. Environment Variables

You'll need to configure the following environment variables for production:

- `PUBLIC_SUPABASE_URL`: Your Supabase project URL
- `PUBLIC_SUPABASE_ANON_KEY`: Your Supabase project anonymous key
- `NODE_ENV`: Set to `production`

## Deployment Options

### Option 1: Manual Deployment via Vercel Dashboard

1. Push your code to GitHub
2. In the Vercel dashboard, click "Add New..." and select "Project"
3. Import your GitHub repository
4. Configure the project:
   - Set Framework Preset to "SvelteKit"
   - Add the environment variables mentioned above
   - Click "Deploy"

### Option 2: Using the Deployment Script

We've included a `deploy.sh` script to simplify the deployment process:

1. Make sure you have the Vercel CLI installed:

   ```bash
   npm i -g vercel
   ```

2. Log in to Vercel:

   ```bash
   vercel login
   ```

3. Run the deployment script:

   ```bash
   # For a preview deployment
   ./deploy.sh

   # For a production deployment
   ./deploy.sh --prod
   ```

### Option 3: GitHub CI/CD Integration

Vercel automatically integrates with GitHub to deploy:

- Main branch updates trigger production deployments
- Pull requests trigger preview deployments

This is configured automatically when you connect your GitHub repository to Vercel.

## Post-Deployment Steps

1. Test the application by accessing your Vercel deployment URL
2. Verify that you can sign up, log in, and create CV content
3. Test the public CV viewing functionality
4. If using a custom domain, configure it in the Vercel project settings

## Troubleshooting

### Common Issues

- **Database Connection Issues**: Verify Supabase credentials are correctly set as environment variables
- **Build Failures**: Check the build logs in Vercel for specific errors
- **Authentication Problems**: Ensure Supabase Auth settings are properly configured

### Monitoring

- Use Vercel Analytics to monitor your application performance
- Check Supabase logs for database-related issues
- Enable Vercel's Web Analytics and Speed Insights for detailed performance metrics

## Rollback Procedure

If a deployment causes issues:

1. In the Vercel dashboard, go to your project
2. Navigate to the "Deployments" tab
3. Find a previous working deployment
4. Click the three dots menu and select "Promote to Production"
