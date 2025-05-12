import { browser } from '$app/environment';
import { getCsrfHeaderName } from './csrf';

/**
 * Get the CSRF token from the meta tag
 */
export function getCsrfTokenFromDocument(): string | null {
    if (!browser) return null;

    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? (metaTag as HTMLMetaElement).content : null;
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
    // Add CSRF token to the request
    const csrfConfig = addCsrfToFetchConfig(config);

    // Perform the fetch with the updated config
    return fetch(url, csrfConfig);
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
        const response = await fetchWithCsrf(url, {
            ...config,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...config.headers
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        return response.json();
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