// Utility function for consistent date formatting across the app
export function formatDateWithPreference(
	dateString: string | null,
	dateFormatPreference: 'month-year' | 'year-only' = 'month-year'
): string {
	if (!dateString) return 'Present';

	try {
		const date = new Date(dateString);

		if (dateFormatPreference === 'year-only') {
			return date.getFullYear().toString();
		} else {
			// Format as MM/YYYY
			return `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
		}
	} catch (e) {
		// Fallback to basic formatting if date parsing fails
		if (dateFormatPreference === 'year-only') {
			return new Date().getFullYear().toString();
		} else {
			return '01/2024'; // Default fallback
		}
	}
}

// Function to get date format preference from profile data
export function getDateFormatPreference(profile: any): 'month-year' | 'year-only' {
	return profile?.date_format_preference || 'month-year';
}
