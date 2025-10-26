import { safeLog } from './config';

/**
 * Interface for validation errors
 */
export interface ValidationError {
	field: string;
	message: string;
}

/**
 * Interface for validation result
 */
export interface ValidationResult {
	success: boolean;
	errors: ValidationError[];
}

/**
 * Field validator types
 */
export type Validator<T> = (value: T) => string | null;

/**
 * Validation schema type
 */
export type ValidationSchema<T> = {
	[K in keyof T]?: Validator<T[K]>[];
};

/**
 * Helper to create a validator
 */
export function createValidator<T>(schema: ValidationSchema<T>) {
	return (data: T): ValidationResult => {
		const errors: ValidationError[] = [];

		// Run each validator for each field
		for (const field in schema) {
			const validators = schema[field] || [];
			const value = data[field];

			for (const validator of validators) {
				const errorMessage = validator(value);
				if (errorMessage) {
					errors.push({ field, message: errorMessage });
					break; // Stop on first error for this field
				}
			}
		}

		return {
			success: errors.length === 0,
			errors
		};
	};
}

/**
 * Common validators
 */
export const validators = {
	required: (fieldName: string): Validator<any> => {
		return (value: any) => {
			if (value === null || value === undefined || value === '') {
				return `${fieldName} is required`;
			}
			return null;
		};
	},

	minLength: (fieldName: string, min: number): Validator<string> => {
		return (value: string) => {
			if (value && value.length < min) {
				return `${fieldName} must be at least ${min} characters`;
			}
			return null;
		};
	},

	maxLength: (fieldName: string, max: number): Validator<string> => {
		return (value: string) => {
			if (value && value.length > max) {
				return `${fieldName} cannot exceed ${max} characters`;
			}
			return null;
		};
	},

	email: (fieldName: string): Validator<string> => {
		return (value: string) => {
			if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
				return `${fieldName} must be a valid email address`;
			}
			return null;
		};
	},

	url: (fieldName: string): Validator<string> => {
		return (value: string) => {
			if (value && !/^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/.test(value)) {
				return `${fieldName} must be a valid URL`;
			}
			return null;
		};
	},

	date: (fieldName: string): Validator<string> => {
		return (value: string) => {
			if (value && isNaN(new Date(value).getTime())) {
				return `${fieldName} must be a valid date`;
			}
			return null;
		};
	},

	phone: (fieldName: string): Validator<string> => {
		return (value: string) => {
			if (value && !/^(\+\d{1,3})?[-.\s]?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/.test(value)) {
				return `${fieldName} must be a valid phone number`;
			}
			return null;
		};
	},

	custom: <T>(
		fieldName: string,
		validationFn: (value: T) => boolean,
		errorMessage: string
	): Validator<T> => {
		return (value: T) => {
			if (value !== undefined && value !== null && !validationFn(value)) {
				return errorMessage;
			}
			return null;
		};
	}
};

/**
 * Utility to check against common XSS patterns
 */
export function checkForXss(input: string): boolean {
	if (!input) return false;

	const xssPatterns = [
		/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
		/javascript:/gi,
		/on\w+\s*=/gi,
		/data:text\/html/gi
	];

	return xssPatterns.some((pattern) => pattern.test(input));
}

/**
 * Sanitize inputs to prevent XSS
 */
export function sanitizeInput(input: string): string {
	if (!input) return input;

	// Basic HTML entity encoding for dangerous characters
	return input
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}

/**
 * Decode HTML entities back to their original characters
 * Use this for displaying sanitized content in the UI
 */
export function decodeHtmlEntities(input: string): string {
	if (!input) return input;

	// Create a temporary element to use the browser's built-in decoder
	const doc = new DOMParser().parseFromString(input, 'text/html');
	return doc.documentElement.textContent || input;
}

/**
 * Validates a username format (client-side only validation)
 * Note: This does NOT check availability - server-side validation is required for that
 *
 * @param username The username to validate
 */
export function validateUsername(username: string): { valid: boolean; error?: string } {
	// Check if empty
	if (!username.trim()) {
		return { valid: false, error: 'Username is required' };
	}

	// Check length
	if (username.length < 3) {
		return { valid: false, error: 'Username must be at least 3 characters long' };
	}

	if (username.length > 30) {
		return { valid: false, error: 'Username must be less than 30 characters' };
	}

	// Check format (lowercase letters, numbers, hyphens, underscores)
	const validFormat = /^[a-z0-9][a-z0-9\-_]+$/.test(username);
	if (!validFormat) {
		return {
			valid: false,
			error:
				'Username can only contain lowercase letters, numbers, hyphens, and underscores, and must start with a letter or number'
		};
	}

	// Format is valid (availability check must happen server-side)
	return { valid: true };
}

/**
 * Example usage:
 *
 * import { createValidator, validators } from './validation';
 *
 * // Define a validator for user input
 * const validateUser = createValidator({
 *     name: [validators.required('Name'), validators.maxLength('Name', 100)],
 *     email: [validators.required('Email'), validators.email('Email')],
 *     phone: [validators.phone('Phone')]
 * });
 *
 * function handleSubmit(data) {
 *     const result = validateUser(data);
 *     if (!result.success) {
 *         // Handle validation errors
 *         return result.errors;
 *     }
 *     // Proceed with valid data
 * }
 */
