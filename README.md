# sv

Everything you need to build a Svelte project, powered by [`sv`](https://github.com/sveltejs/cli).

## Creating a project

If you're seeing this, you've probably already done this step. Congrats!

```bash
# create a new project in the current directory
npx sv create

# create a new project in my-app
npx sv create my-app
```

## Developing

Once you've created a project and installed dependencies with `npm install` (or `pnpm install` or `yarn`), start a development server:

```bash
npm run dev

# or start the server and open the app in a new browser tab
npm run dev -- --open
```

## Building

To create a production version of your app:

```bash
npm run build
```

You can preview the production build with `npm run preview`.

> To deploy your app, you may need to install an [adapter](https://svelte.dev/docs/kit/adapters) for your target environment.

# CV App

A modern CV builder application built with SvelteKit, Supabase, and Tailwind CSS.

## Features

- Create a professional CV with sections for personal details, work experience, education, skills, projects, and more
- Customize which sections to display
- Generate a PDF export of your CV
- Shareable public URL for your CV (e.g., `yourdomain.com/cv/@username`)
- Responsive design that works on any device

## User-Friendly Public URLs

The CV App now supports user-friendly URLs for sharing your CV. When you set a username in your profile settings, you'll automatically get a public URL for your CV in the format:

```
https://yourdomain.com/cv/@username
```

This makes it easy to share your CV with employers, add it to your email signature, or include it on your business card. Your username must be:

- At least 3 characters long
- Contain only lowercase letters, numbers, hyphens, and underscores
- Start with a letter or number

The legacy UUID-based URLs (e.g., `https://yourdomain.com/cv/123e4567-e89b-12d3-a456-426614174000`) will continue to work for backward compatibility.

## PDF Export

The app provides a customizable PDF export feature that allows you to select which sections of your CV to include in the export.

## Getting Started

1. Clone the repository
2. Install dependencies: `npm install`
3. Set up your Supabase project and add the URL and anon key to `.env`
4. Run the development server: `npm run dev`

## Deployment

The app is configured for deployment on Vercel with a Supabase backend. Follow these steps to deploy:

### Production Setup

1. Create a new Supabase project for production
2. Apply the database schema from `supabase/schema.sql` to your production Supabase project
3. Get your Supabase URL and anon key from the project settings

### Deploying to Vercel

1. Push your code to GitHub
2. Connect your repository to Vercel
3. Set the following environment variables in your Vercel project settings:
   - `PUBLIC_SUPABASE_URL`: Your production Supabase URL
   - `PUBLIC_SUPABASE_ANON_KEY`: Your production Supabase anon key
   - `NODE_ENV`: Set to `production`
4. Deploy your project

### Optimizations

The app has been optimized for production:

- Uses `@sveltejs/adapter-vercel` for optimal deployment on Vercel
- Implements caching strategies for public CV routes
- Configures security headers via `vercel.json`
- Sets optimal cache durations for static assets
