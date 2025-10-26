import { createClient } from '@supabase/supabase-js';
import type { Database } from './database.types';
import config, { safeLog } from './config';
import { browser } from '$app/environment';

/**
 * IMPORTANT NOTE ABOUT FETCH WARNINGS:
 *
 * The Supabase client uses window.fetch internally, which will generate many
 * SvelteKit warnings like:
 * "Loading X using `window.fetch`. For best results, use the `fetch` that is passed to your `load` function"
 *
 * These warnings can safely be ignored as they don't affect functionality.
 * Unfortunately, there is no direct way to make the Supabase client use SvelteKit's
 * fetch function without modifying the Supabase library code itself.
 */

// Create the Supabase client with configuration from config file
// We use let here to make sure only one instance is created
let supabaseInstance: ReturnType<typeof createClient<Database>> | null = null;

// Check if we're on a public CV page
const isPublicCvPage = browser && window.location.pathname.match(/^\/cv\/@.+$/);

// Define client options with a unique storage key for the main client
const mainClientOptions = {
	auth: {
		persistSession: true,
		autoRefreshToken: true,
		detectSessionInUrl: true,
		storageKey: 'sb-auth-token-main',
		flowType: 'pkce' as const
	},
	global: {
		fetch: customFetch
	}
};

// Create a supabase client
export const supabase = (() => {
	try {
		if (browser && !supabaseInstance) {
			const client = createClient<Database>(
				config.supabase.url,
				config.supabase.anonKey,
				mainClientOptions
			);
			supabaseInstance = client;
			safeLog('debug', 'Main Supabase client initialized');
			return client;
		}
		return (
			supabaseInstance ||
			createClient<Database>(config.supabase.url, config.supabase.anonKey, mainClientOptions)
		);
	} catch (error) {
		safeLog('error', 'Failed to initialize main Supabase client', error);
		// Return a non-null value to prevent runtime errors, but this client won't work
		return createClient<Database>(
			config.supabase.url || 'https://placeholder-url.supabase.co',
			config.supabase.anonKey || 'placeholder-key',
			mainClientOptions
		);
	}
})();

// Save the instance for future use
if (browser && !supabaseInstance) {
	supabaseInstance = supabase;
	safeLog('debug', 'Main Supabase client initialized');
}

// Create a separate client specifically for public CV pages with a different storage key
export function createPublicClient() {
	try {
		safeLog('debug', 'Created unauthenticated client for public CV access');
		return createClient<Database>(config.supabase.url, config.supabase.anonKey, {
			auth: {
				persistSession: false, // Don't persist sessions for public views
				autoRefreshToken: false, // Don't attempt to refresh tokens
				detectSessionInUrl: false, // Don't check URL for auth tokens
				storageKey: 'sb-auth-token-public', // Use a different storage key
				flowType: 'pkce' as const
			},
			global: {
				fetch: customFetch
			}
		});
	} catch (error) {
		safeLog('error', 'Failed to create public Supabase client', error);
		throw new Error('Failed to connect to the database. Please try again later.');
	}
}

// Custom fetch function with retry logic for network errors
async function customFetch(input: RequestInfo | URL, init?: RequestInit): Promise<Response> {
	const MAX_RETRIES = 3;
	let retries = 0;
	let lastError: Error = new Error('Unknown error');

	// Check if this is a refresh token request and we're on a public CV page
	const isRefreshTokenRequest =
		(typeof input === 'string' && input.includes('/auth/v1/token?grant_type=refresh_token')) ||
		(input instanceof URL && input.toString().includes('/auth/v1/token?grant_type=refresh_token'));

	// If we're on a public CV page and trying to refresh a token, return a mock response
	if (isRefreshTokenRequest && isPublicCvPage) {
		safeLog('debug', 'Intercepted refresh token request on public CV page');
		return new Response(
			JSON.stringify({
				access_token: null,
				token_type: 'bearer',
				expires_in: 3600,
				refresh_token: null,
				user: null
			}),
			{
				status: 200,
				headers: { 'Content-Type': 'application/json' }
			}
		);
	}

	while (retries < MAX_RETRIES) {
		try {
			const response = await fetch(input, init);
			return response;
		} catch (error) {
			lastError = error as Error;
			retries++;

			// Only log if in browser to avoid SSR issues
			if (browser) {
				safeLog('warn', `Fetch error (attempt ${retries}/${MAX_RETRIES})`, {
					url: typeof input === 'string' ? input : input.toString(),
					error: lastError.message
				});
			}

			// If it's a network error, wait before retrying
			if (
				lastError.message.includes('NetworkError') ||
				lastError.message.includes('Failed to fetch')
			) {
				await new Promise((resolve) => setTimeout(resolve, 1000 * retries));
			} else {
				// For non-network errors, don't retry
				break;
			}
		}
	}

	// After max retries, throw the last error
	throw lastError;
}

// Function to check if current token is about to expire (within 5 minutes)
export const isSessionExpiringSoon = async (): Promise<boolean> => {
	try {
		const { data } = await supabase.auth.getSession();
		if (!data.session) return true;

		const expiresAt = data.session.expires_at;
		if (!expiresAt) return true;

		// Check if token expires within the next 5 minutes (300 seconds)
		const expiresAtDate = new Date(expiresAt * 1000);
		const now = new Date();
		const fiveMinutesFromNow = new Date(now.getTime() + 5 * 60 * 1000);

		return expiresAtDate < fiveMinutesFromNow;
	} catch (error) {
		safeLog('error', 'Error checking token expiration', error);
		return true; // Assume token is expiring if we can't check
	}
};

// Function to clear all Supabase session data from storage
export const clearAllSessionData = (): void => {
	try {
		if (typeof window === 'undefined') return;

		// Remove Supabase token from localStorage
		window.localStorage.removeItem('sb-auth-token');

		// Clear any other auth-related items
		const keysToRemove: string[] = [];
		for (let i = 0; i < localStorage.length; i++) {
			const key = localStorage.key(i);
			if (key && (key.startsWith('sb-') || key.includes('supabase'))) {
				keysToRemove.push(key);
			}
		}

		// Remove all found keys
		keysToRemove.forEach((key) => localStorage.removeItem(key));

		safeLog('debug', 'All session data cleared', {
			count: keysToRemove.length,
			time: new Date().toISOString()
		});
	} catch (error) {
		safeLog('error', 'Error clearing session data', error);
	}
};

// Test connection and authentication during initialization
if (browser) {
	(async function checkConnection() {
		try {
			const { data, error } = await supabase.auth.getSession();

			if (error) {
				safeLog('warn', 'Supabase session check failed', { error: error.message });
			} else {
				safeLog('debug', 'Supabase connection test successful', {
					hasSession: !!data.session,
					environment: config.environment
				});
			}
		} catch (error) {
			safeLog('error', 'Failed to connect to Supabase', error);
		}
	})();
}

// Initialize and validate the Supabase client on startup
try {
	safeLog('debug', 'Supabase client initialized', {
		environment: config.environment
		// Do NOT log credentials here
	});
} catch (error) {
	safeLog('error', 'Failed to initialize Supabase client', error);
	// Re-throw critical initialization errors to prevent app from starting with invalid config
	throw error;
}
