/**
 * Global helper functions for use across the application
 */

import { browser } from '$app/environment';

/**
 * Attach a global helper for safely injecting scripts
 */
if (browser) {
    // Add the function to window for global access
    (window as any).safeScript = (callback: Function): void => {
        try {
            // Direct execution
            callback();
        } catch (error) {
            console.error('Error executing script:', error);
        }
    };
}

/**
 * Initialize global helpers
 * Call this function in your root layout
 */
export function initGlobalHelpers(): void {
    // This function doesn't need to do anything else
    // since the browser check and function attachment happens at module level
}