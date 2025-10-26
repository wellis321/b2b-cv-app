# CV App Analytics System

This document provides an overview of the built-in analytics system for tracking page visits and user activity in the CV App.

## Quick Start Guide

To get the analytics system up and running:

1. **Apply the Database Migration**:

   - Navigate to `/admin/analytics/apply-migration` in your app
   - Follow the instructions to apply the SQL migration
   - Or manually run the SQL in `src/lib/migrations/20240530_create_page_analytics.sql` via Supabase dashboard

2. **Configure Admin Access**:

   - By default, `admin@example.com` is set as an admin
   - To add your email, either:
     - Edit `src/lib/adminConfig.ts` to add your email to `defaultAdminEmails`
     - Or set up the `PUBLIC_ADMIN_EMAILS` environment variable in your `.env` file:
       ```
       PUBLIC_ADMIN_EMAILS="admin@example.com,your.email@example.com"
       ```

3. **Verify Tracking**:

   - The `AnalyticsTracker` component is already included in the main layout
   - Visit a few pages in your application to generate data
   - Check your database for entries in the `page_analytics` table

4. **Access the Dashboard**:
   - Go to `/admin/analytics` to view your analytics data
   - Note: Only admin users can access this page

## Features

- **Page Visit Tracking**: Automatically tracks every page visit across the application
- **User Identification**: Differentiates between authenticated and anonymous users
- **Session Tracking**: Maintains session continuity for visitor statistics
- **Device & Browser Detection**: Collects information about user devices and browsers
- **Aggregated Views**: Pre-built database views for common analytics queries
- **Privacy-Focused**: Collects only necessary information for analytics purposes
- **Admin Dashboard**: Visualize analytics data through an admin interface

## Implementation Components

### 1. Database Schema

The analytics system uses the following database tables and views:

- `page_analytics`: Raw analytics data table storing individual page visits
- `daily_page_views`: Aggregated daily page views by path
- `monthly_page_views`: Aggregated monthly page views by path
- `analytics_dashboard_data`: Dashboard-focused aggregated metrics

### 2. Client-Side Tracking

The `AnalyticsTracker.svelte` component is integrated into the main layout to track page visits on the client side. This approach enables:

- Detection of client-side navigation events
- Access to browser and device information
- Session management using local storage

### 3. Analytics Service

The `analyticsService.ts` module provides functions for:

- Tracking page views
- Querying analytics data
- Generating statistics for the dashboard
- Real-time active user counting

### 4. Admin Dashboard

The analytics dashboard at `/admin/analytics` provides:

- Overview metrics (total views, unique visitors, etc.)
- Daily traffic visualization
- Most popular pages table
- User segment breakdowns (authenticated vs. anonymous)
- Date range filtering

## Setup Instructions

### Applying the Database Migration

1. Navigate to `/admin/analytics/apply-migration`
2. Follow the instructions to apply the migration
3. Or manually apply the SQL migration located at `src/lib/migrations/20240530_create_page_analytics.sql`

### Securing the Analytics Data

The analytics system includes Row Level Security policies to:

- Restrict analytics data access to administrators
- Allow users to view only their own data
- Prevent unauthorized data modifications

## Technical Details

### Data Collection

The following data is collected for each page visit:

- **Page Path**: The URL path of the page visited
- **User ID**: The authenticated user's ID (if available)
- **Session ID**: A unique ID for the browser session
- **Authentication Status**: Whether the user was logged in
- **Browser & Device**: Basic browser and device type information
- **Referrer**: The referring URL (if available)
- **Query Parameters**: URL query parameters (if any)
- **Timestamp**: When the page was visited

### Performance Considerations

- Client-side tracking is lightweight and asynchronous
- Database indexes optimize query performance
- Aggregated views reduce computation for common queries
- Row-level security ensures data isolation

## Dashboard Access

Access to the analytics dashboard is restricted to administrators. Currently, this is determined by:

- Development environment: All users in development mode
- Email check: Users with specific admin emails in production

## Customizing Admin Access

To customize which users have admin access:

1. Open `src/routes/+layout.svelte`
2. Find the `checkAdmin()` function
3. Modify the `adminEmails` array to include your admin email addresses
4. Or implement a more robust role-based system using database roles

```typescript
// Example of customizing admin access
function checkAdmin() {
	if ($session?.user) {
		const adminEmails = ['admin@example.com', 'your.email@example.com'];
		isAdmin = adminEmails.includes($session.user.email);
	} else {
		isAdmin = false;
	}
}
```

## Future Enhancements

Planned improvements to the analytics system:

1. More detailed device and browser statistics
2. Geographic location tracking (with user consent)
3. Event tracking for specific user actions
4. Conversion funnels and goal tracking
5. Custom report generation
6. Export capabilities for analytics data
7. Real-time analytics dashboard

## Privacy Considerations

The analytics system is designed with privacy in mind:

- No personal identifiable information is collected beyond user IDs
- Session IDs are randomly generated and not tied to cookies
- IP addresses are not stored
- Query parameters are sanitized to remove sensitive data
- Data retention policies can be implemented as needed

## Troubleshooting

If analytics data is not being collected:

1. Ensure the database migration has been applied
2. Check that the `AnalyticsTracker` component is included in the layout
3. Verify that the browser supports localStorage for session tracking
4. Check for console errors related to analytics tracking

## File Structure

The analytics system consists of the following files:

```
src/
├── lib/
│   ├── analytics/
│   │   └── analyticsService.ts        # Core analytics functionality
│   ├── components/
│   │   └── AnalyticsTracker.svelte    # Page visit tracking component
│   └── migrations/
│       └── 20240530_create_page_analytics.sql  # Database migration
└── routes/
    ├── admin/
    │   └── analytics/
    │       ├── +page.svelte            # Analytics dashboard
    │       └── apply-migration/
    │           └── +page.svelte        # Migration application page
    └── +layout.svelte                  # Includes analytics tracker
```
