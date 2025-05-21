// Svelte 5 runes are globals and don't need to be imported
import type { HTMLFormAttributes } from 'svelte/elements';

// Interface for validation result
interface ValidationResult {
    valid: boolean;
    errors: Record<string, string>;
}

// Interface for validation rules
interface ValidationRules {
    [field: string]: {
        required?: boolean;
        minLength?: number;
        maxLength?: number;
        pattern?: RegExp;
        custom?: (value: string) => boolean;
        message?: string;
    };
}

// Form validation attachment
export function formValidation(
    node: HTMLFormElement,
    options: {
        rules: ValidationRules;
        validateOnBlur?: boolean;
        validateOnChange?: boolean;
        validateOnSubmit?: boolean;
    }
) {
    // Default options
    const config = {
        validateOnBlur: true,
        validateOnChange: false,
        validateOnSubmit: true,
        ...options
    };

    // Internal state
    let errors = $state({} as Record<string, string>);
    let touched = $state({} as Record<string, boolean>);
    let dirty = $state({} as Record<string, boolean>);
    let submitted = $state(false);
    let isValid = $state(true);

    // Initialize fields
    const fields = Object.keys(config.rules);
    fields.forEach(field => {
        errors[field] = '';
        touched[field] = false;
        dirty[field] = false;
    });

    // Validation function
    function validateField(fieldName: string, value: string): string {
        const rules = config.rules[fieldName];
        if (!rules) return '';

        // Required validation
        if (rules.required && (!value || value.trim() === '')) {
            return rules.message || `${fieldName} is required`;
        }

        // Skip other validations if value is empty and not required
        if (!value) return '';

        // Min length validation
        if (rules.minLength && value.length < rules.minLength) {
            return rules.message || `${fieldName} must be at least ${rules.minLength} characters`;
        }

        // Max length validation
        if (rules.maxLength && value.length > rules.maxLength) {
            return rules.message || `${fieldName} must be no more than ${rules.maxLength} characters`;
        }

        // Pattern validation
        if (rules.pattern && !rules.pattern.test(value)) {
            return rules.message || `${fieldName} format is invalid`;
        }

        // Custom validation
        if (rules.custom && !rules.custom(value)) {
            return rules.message || `${fieldName} is invalid`;
        }

        return '';
    }

    // Validate all fields
    function validateAll(): ValidationResult {
        const formData = new FormData(node);
        const newErrors: Record<string, string> = {};
        let isFormValid = true;

        fields.forEach(field => {
            const value = formData.get(field) as string || '';
            const error = validateField(field, value);
            newErrors[field] = error;
            if (error) isFormValid = false;
        });

        // Update state
        errors = newErrors;
        isValid = isFormValid;

        return {
            valid: isFormValid,
            errors: newErrors
        };
    }

    // Handle blur event
    function handleBlur(event: FocusEvent) {
        if (!config.validateOnBlur) return;

        const target = event.target as HTMLInputElement;
        if (!target || !target.name || !config.rules[target.name]) return;

        touched[target.name] = true;
        errors[target.name] = validateField(target.name, target.value);
        updateFormValidity();
    }

    // Handle change event
    function handleChange(event: Event) {
        if (!config.validateOnChange) return;

        const target = event.target as HTMLInputElement;
        if (!target || !target.name || !config.rules[target.name]) return;

        dirty[target.name] = true;
        errors[target.name] = validateField(target.name, target.value);
        updateFormValidity();
    }

    // Handle submit event
    function handleSubmit(event: SubmitEvent) {
        submitted = true;

        if (config.validateOnSubmit) {
            const result = validateAll();

            // Mark all fields as touched
            fields.forEach(field => {
                touched[field] = true;
            });

            // If the form is not valid, prevent submission
            if (!result.valid) {
                event.preventDefault();
                event.stopPropagation();

                // Focus the first field with an error
                const firstErrorField = fields.find(field => errors[field]);
                if (firstErrorField) {
                    const input = node.querySelector(`[name="${firstErrorField}"]`) as HTMLInputElement;
                    if (input) input.focus();
                }
            }
        }
    }

    // Update overall form validity
    function updateFormValidity() {
        isValid = Object.values(errors).every(error => !error);
    }

    // Attach event listeners
    node.addEventListener('blur', handleBlur, true);
    node.addEventListener('input', handleChange, true);
    node.addEventListener('submit', handleSubmit, true);

    // Clean up event listeners
    return {
        destroy() {
            node.removeEventListener('blur', handleBlur, true);
            node.removeEventListener('input', handleChange, true);
            node.removeEventListener('submit', handleSubmit, true);
        }
    };
}

// Svelte 5 attachment type
declare module 'svelte' {
    interface FiveHTMLAttributes<Node> extends HTMLFormAttributes {
        'use:formValidation'?: {
            rules: ValidationRules;
            validateOnBlur?: boolean;
            validateOnChange?: boolean;
            validateOnSubmit?: boolean;
        };
    }
}