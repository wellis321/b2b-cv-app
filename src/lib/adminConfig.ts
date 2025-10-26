import { dev } from '$app/environment';

// Try to get admin emails from environment, otherwise fallback to default
// In a real environment, you would use:
// import { PUBLIC_ADMIN_EMAILS } from '$env/static/public';
// But we'll provide a fallback for now

// In real usage, add to .env file:
// PUBLIC_ADMIN_EMAILS="admin@example.com,your.email@example.com"

// This fallback will be used if env variable is not set
const defaultAdminEmails = ['admin@example.com', 'your.email@example.com'];

export function getAdminEmails(): string[] {
	try {
		// Try to get from environment (when properly set in .env)
		// Replace this with the proper import when available
		const envEmails = process.env.PUBLIC_ADMIN_EMAILS || '';

		if (envEmails) {
			return envEmails.split(',').map((email) => email.trim());
		}
	} catch (error) {
		console.warn('Could not parse admin emails from environment', error);
	}

	// Return default emails if none found in environment
	return defaultAdminEmails;
}

/**
 * Check if a user is an admin based on their email
 * @param email The user's email address
 * @returns boolean indicating if the user is an admin
 */
export function isAdminUser(email?: string): boolean {
	if (!email) return false;

	// In development mode, consider all users as admins for easier testing
	if (dev) return true;

	const adminEmails = getAdminEmails();
	return adminEmails.includes(email);
}
