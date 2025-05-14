import { sanitizeInput } from '$lib/validation';

// ... existing code ...

// Add sanitization function for CV data
function sanitizeCvData(cvData: any): any {
	if (!cvData) return cvData;

	// Create a deep copy to avoid mutating original data
	const sanitized = JSON.parse(JSON.stringify(cvData));

	// Sanitize profile data
	if (sanitized.profile) {
		if (typeof sanitized.profile.full_name === 'string') {
			sanitized.profile.full_name = sanitizeInput(sanitized.profile.full_name);
		}
		if (typeof sanitized.profile.location === 'string') {
			sanitized.profile.location = sanitizeInput(sanitized.profile.location);
		}
	}

	// Sanitize arrays of objects with text fields
	const sanitizeArray = (arr: any[], textFields: string[]) => {
		if (!Array.isArray(arr)) return arr;

		return arr.map((item) => {
			const sanitizedItem = { ...item };
			for (const field of textFields) {
				if (typeof sanitizedItem[field] === 'string') {
					sanitizedItem[field] = sanitizeInput(sanitizedItem[field]);
				}
			}
			return sanitizedItem;
		});
	};

	// Sanitize each section
	if (Array.isArray(sanitized.workExperiences)) {
		sanitized.workExperiences = sanitizeArray(sanitized.workExperiences, [
			'company_name',
			'position',
			'description'
		]);
	}

	if (Array.isArray(sanitized.projects)) {
		sanitized.projects = sanitizeArray(sanitized.projects, ['title', 'description', 'url']);
	}

	if (Array.isArray(sanitized.education)) {
		sanitized.education = sanitizeArray(sanitized.education, [
			'institution',
			'degree',
			'field_of_study',
			'description'
		]);
	}

	if (Array.isArray(sanitized.skills)) {
		sanitized.skills = sanitizeArray(sanitized.skills, ['name', 'category']);
	}

	if (Array.isArray(sanitized.certifications)) {
		sanitized.certifications = sanitizeArray(sanitized.certifications, [
			'name',
			'issuer',
			'description'
		]);
	}

	if (Array.isArray(sanitized.memberships)) {
		sanitized.memberships = sanitizeArray(sanitized.memberships, [
			'organisation',
			'role',
			'description'
		]);
	}

	if (Array.isArray(sanitized.interests)) {
		sanitized.interests = sanitizeArray(sanitized.interests, ['name', 'description']);
	}

	return sanitized;
}

// The load function - we don't need to sanitize data here since data is loaded client-side
// with the cvStore. The function just returns an empty object to let client-side loading proceed.
export const load = async ({ locals }) => {
	// The actual data loading happens in +page.ts client-side
	return {};
};
