import { validateCsrfToken, requiresCsrfCheck, getCsrfToken } from './csrf';

/**
 * Create CSRF protection middleware for server endpoints
 */
export function createCsrfProtection(cookies: any) {
	// Get or create a CSRF token - this will be available in the page
	const csrfToken = getCsrfToken(cookies);

	// Return the validation function and the token
	return {
		validateRequest: async (request: Request): Promise<boolean> => {
			// Skip CSRF check for methods that don't modify state
			if (!requiresCsrfCheck(request.method)) {
				return true;
			}

			// Validate the CSRF token
			return validateCsrfToken(request, csrfToken);
		},
		getCsrfToken: () => csrfToken
	};
}
