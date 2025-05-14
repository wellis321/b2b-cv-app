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
