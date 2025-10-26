import { json } from '@sveltejs/kit';
import type { RequestEvent } from '@sveltejs/kit';
import { dev } from '$app/environment';

// Get admin emails - server side version
function getAdminEmails(): string[] {
	// In production, you should set this in your server environment
	return ['admin@example.com', 'your.email@example.com'];
}

// Check if user is admin - server side version
function isAdminUser(email?: string): boolean {
	if (!email) return false;
	// In development mode, consider all users as admins for easier testing
	if (dev) return true;
	return getAdminEmails().includes(email);
}

export async function POST({ locals, request }: RequestEvent) {
	// Check authentication
	const { session } = locals;
	if (!session?.user) {
		return json({ success: false, error: 'Unauthorized' }, { status: 401 });
	}

	// Check if the user is an admin
	const isAdmin = isAdminUser(session.user.email);

	if (!isAdmin) {
		return json({ success: false, error: 'Forbidden' }, { status: 403 });
	}

	try {
		// Instead of reading from the filesystem (which isn't available in Edge Functions),
		// we'll return instructions for applying the migration manually

		return json({
			success: true,
			message:
				'To apply the analytics migration, please execute the SQL file manually in the Supabase dashboard.',
			instructions: [
				'1. Log in to your Supabase dashboard',
				'2. Go to the SQL Editor',
				'3. Create a new query',
				'4. Find the migration file in your project at src/lib/migrations/20240530_create_page_analytics.sql',
				'5. Copy and paste the contents into the SQL Editor',
				'6. Run the query'
			]
		});
	} catch (err: any) {
		console.error('Error processing migration request:', err);
		return json(
			{
				success: false,
				error: `Failed to process request: ${err.message}`
			},
			{ status: 500 }
		);
	}
}
