import { browser } from '$app/environment';
import { getCsrfHeaderName } from './csrf';

/**
 * Get the CSRF token from the response headers or meta tag
 */
export function getCsrfTokenFromDocument(): string | null {
    if (!browser) return null;

    // First try to get from meta tag (if it exists)
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return (metaTag as HTMLMetaElement).content;
    }

    // If no meta tag, try to get from a global variable or localStorage
    // This is a fallback for when the token is set via headers
    const storedToken = localStorage.getItem('csrf-token');
    if (storedToken) {
        return storedToken;
    }

    // Log warning if no token found
    console.warn(
        'CSRF token not found in document. Meta tags:',
        Array.from(document.querySelectorAll('meta')).map((m) => ({
            name: m.getAttribute('name'),
            content: m.getAttribute('content')?.substring(0, 10) + '...'
        }))
    );

    return null;
}

/**
 * Initialize CSRF token from server response
 * This should be called when the page loads to get the token from headers
 */
export async function initializeCsrfToken(): Promise<void> {
    if (!browser) return;

    try {
        // Make a request to get the CSRF token
        const response = await fetch('/api/csrf-token', {
            method: 'GET',
            credentials: 'same-origin'
        });

        if (response.ok) {
            const data = await response.json();
            const csrfToken = data.csrfToken || response.headers.get('X-CSRF-Token');

            if (csrfToken) {
                localStorage.setItem('csrf-token', csrfToken);
                console.debug('CSRF token initialized from server');
            } else {
                console.warn('No CSRF token found in response');
            }
        } else {
            console.warn('Failed to get CSRF token, status:', response.status);
        }
    } catch (error) {
        console.warn('Failed to initialize CSRF token:', error);
    }
}

/**
 * Add CSRF token to a fetch configuration
 */
export function addCsrfToFetchConfig(config: RequestInit = {}): RequestInit {
    if (!browser) return config;

    const token = getCsrfTokenFromDocument();
    if (!token) {
        console.warn('CSRF token not found in document');
        return config;
    }

    // Create headers if they don't exist
    const headers = new Headers(config.headers || {});

    // Add the CSRF token header
    headers.set(getCsrfHeaderName(), token);

    return {
        ...config,
        headers
    };
}

/**
 * Fetch with CSRF protection
 */
export async function fetchWithCsrf(url: string, config: RequestInit = {}): Promise<Response> {
    try {
        // Add CSRF token to the request
        const csrfConfig = addCsrfToFetchConfig(config);

        // Check if CSRF token was added successfully
        if (
            !csrfConfig.headers ||
            !(csrfConfig.headers instanceof Headers) ||
            !Array.from((csrfConfig.headers as Headers).keys()).some(
                (h) => h.toLowerCase() === getCsrfHeaderName().toLowerCase()
            )
        ) {
            console.warn(
                `CSRF token not added to request to ${url}. Headers:`,
                csrfConfig.headers instanceof Headers
                    ? Array.from((csrfConfig.headers as Headers).entries())
                    : csrfConfig.headers
            );
        }

        // Perform the fetch with the updated config
        return fetch(url, csrfConfig);
    } catch (error) {
        console.error(`Error in CSRF-protected fetch to ${url}:`, error);
        throw error;
    }
}

/**
 * API client with CSRF protection
 */
export const api = {
    get: async <T>(url: string, config: RequestInit = {}): Promise<T> => {
        const response = await fetchWithCsrf(url, { ...config, method: 'GET' });
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        return response.json();
    },

    post: async <T>(url: string, data: any, config: RequestInit = {}): Promise<T> => {
        try {
            // Debug log the request details (remove in production)
            if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
                console.debug('API Request:', {
                    url,
                    method: 'POST',
                    contentType: 'application/json',
                    hasToken: config.headers && 'Authorization' in (config.headers as any),
                    hasCsrf: getCsrfTokenFromDocument() !== null
                });
            }

            const response = await fetchWithCsrf(url, {
                ...config,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...config.headers
                },
                body: JSON.stringify(data)
            });

            // If response is not OK, try to get more detailed error info
            if (!response.ok) {
                const errorText = await response.text();
                let errorMessage = `API error: ${response.status}`;

                try {
                    // Try to parse error as JSON
                    const errorJson = JSON.parse(errorText);
                    if (errorJson.error) {
                        errorMessage = errorJson.error;
                        // Also check for more detailed message field
                        if (errorJson.message) {
                            errorMessage += `: ${errorJson.message}`;
                        }
                    }
                } catch (e) {
                    // If the error text can't be parsed as JSON, use the raw text if available
                    if (errorText) {
                        errorMessage = `${errorMessage} - ${errorText}`;
                    }
                }

                throw new Error(errorMessage);
            }

            return response.json();
        } catch (error) {
            console.error('API POST request failed:', error);
            throw error;
        }
    },

    put: async <T>(url: string, data: any, config: RequestInit = {}): Promise<T> => {
        const response = await fetchWithCsrf(url, {
            ...config,
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                ...config.headers
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        return response.json();
    },

    delete: async <T>(url: string, config: RequestInit = {}): Promise<T> => {
        const response = await fetchWithCsrf(url, {
            ...config,
            method: 'DELETE'
        });
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        return response.json();
    },

    patch: async <T>(url: string, data: any, config: RequestInit = {}): Promise<T> => {
        const response = await fetchWithCsrf(url, {
            ...config,
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                ...config.headers
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        return response.json();
    }
};
