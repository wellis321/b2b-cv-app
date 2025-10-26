import { writable, get } from 'svelte/store';
import { supabase, isSessionExpiringSoon, clearAllSessionData } from '$lib/supabase';
import type { Session } from '@supabase/supabase-js';
import { browser } from '$app/environment';
import { safeLog } from '$lib/config';
import { api } from '$lib/security/clientCsrf';

// Create a writable store with initial value of null
export const session = writable<Session | null>(null);
export const authLoading = writable<boolean>(true);
export const authError = writable<string | null>(null);

// Flag to track if we've initialized the session
let sessionInitialized = false;

// Max idle time before force logout (30 minutes in milliseconds)
const MAX_IDLE_TIME = 30 * 60 * 1000;
let lastActivityTime = Date.now();
let activityCheckInterval: ReturnType<typeof setInterval> | null = null;

// Update the last activity time
function updateLastActivity() {
	lastActivityTime = Date.now();
}

// Check for user inactivity and token expiration
function setupInactivityCheck() {
	if (!browser || activityCheckInterval) return;

	// Add activity listeners
	window.addEventListener('mousemove', updateLastActivity);
	window.addEventListener('keypress', updateLastActivity);
	window.addEventListener('click', updateLastActivity);
	window.addEventListener('scroll', updateLastActivity);

	// Set current time as last activity
	updateLastActivity();

	// Check for inactivity every minute
	activityCheckInterval = setInterval(async () => {
		const currentTime = Date.now();
		const idleTime = currentTime - lastActivityTime;

		// If user has been idle for too long, log them out
		if (idleTime > MAX_IDLE_TIME) {
			safeLog('info', 'User inactive, logging out', { idleTime });
			await logout();
			return;
		}

		// Also check if token is about to expire
		if (await isSessionExpiringSoon()) {
			safeLog('info', 'Session expiring soon, refreshing');
			// Try to refresh the session
			try {
				const { data, error } = await supabase.auth.refreshSession();
				if (error) {
					safeLog('error', 'Failed to refresh session', { error: error.message });
					await logout();
				} else if (data && data.session) {
					session.set(data.session);
					safeLog('debug', 'Session refreshed successfully');
				}
			} catch (err) {
				safeLog('error', 'Error during session refresh', err);
				await logout();
			}
		}
	}, 60000); // Check every minute
}

// Cleanup inactivity check
function cleanupInactivityCheck() {
	if (!browser || !activityCheckInterval) return;

	clearInterval(activityCheckInterval);
	activityCheckInterval = null;

	window.removeEventListener('mousemove', updateLastActivity);
	window.removeEventListener('keypress', updateLastActivity);
	window.removeEventListener('click', updateLastActivity);
	window.removeEventListener('scroll', updateLastActivity);
}

// Initialize the store with the current session
export const initializeSession = async (forceRefresh = false) => {
	if (!browser) {
		return;
	}

	try {
		// Set loading state
		authLoading.set(true);
		authError.set(null);

		// Skip authentication for public CV pages
		const currentPath = browser ? window.location.pathname : null;
		if (currentPath && currentPath.startsWith('/cv/@')) {
			safeLog('debug', 'Skipping session initialization for public CV page');
			sessionInitialized = true;
			authLoading.set(false);
			return;
		}

		// Don't initialize twice unless forced
		if (sessionInitialized && !forceRefresh) {
			authLoading.set(false);
			return;
		}

		// Clear any existing session first to avoid stale data
		session.set(null);

		// Get the session from Supabase
		const { data, error } = await supabase.auth.getSession();

		if (error) {
			safeLog('error', 'Error getting session', { error: error.message });
			authError.set(error.message);
			authLoading.set(false);
			return;
		}

		if (data.session) {
			// Set the session in the store
			session.set(data.session);

			try {
				// Check if token is about to expire and refresh if needed
				if (await isSessionExpiringSoon()) {
					safeLog('info', 'Session near expiry during initialization, refreshing');
					const { data: refreshData, error: refreshError } = await supabase.auth.refreshSession();

					if (refreshError) {
						safeLog('error', 'Failed to refresh session on init', { error: refreshError.message });
						// Don't log out immediately, just note the error
					} else if (refreshData && refreshData.session) {
						session.set(refreshData.session);
						safeLog('debug', 'Session refreshed on init');
					}
				}

				// Setup inactivity check
				setupInactivityCheck();

				// Simpler session verification with proper error handling
				try {
					// Use fetch with timeout protection
					const abortController = new AbortController();
					const timeoutId = setTimeout(() => abortController.abort(), 5000); // 5 second timeout

					const response = await fetch('/api/verify-session', {
						method: 'GET',
						headers: {
							Authorization: `Bearer ${data.session.access_token}`
						},
						signal: abortController.signal
					});

					clearTimeout(timeoutId);

					if (response.ok) {
						safeLog('debug', 'Session verified successfully');
						sessionInitialized = true;
					} else {
						// Try refreshing the session one more time
						safeLog('warn', 'Session verification failed, attempting refresh');
						const { data: refreshAttempt, error: refreshAttemptError } =
							await supabase.auth.refreshSession();

						if (refreshAttemptError) {
							safeLog('error', 'Failed to refresh after verification failure', {
								error: refreshAttemptError.message
							});

							// Continue with existing session even if verification fails
							// This is important for client-side operations to work
							safeLog('info', 'Continuing with existing session despite verification failure');
							sessionInitialized = true;
						} else if (refreshAttempt?.session) {
							session.set(refreshAttempt.session);
							safeLog('info', 'Successfully refreshed session after verification failure');
							sessionInitialized = true;
						}
					}
				} catch (verifyErr) {
					// Handle verification errors gracefully
					safeLog('warn', 'Error during session verification', {
						error: verifyErr instanceof Error ? verifyErr.message : 'Unknown error'
					});

					// Don't force logout or show errors for network issues
					if (
						verifyErr instanceof Error &&
						(verifyErr.message.includes('network') ||
							verifyErr.message.includes('timeout') ||
							verifyErr.message.includes('abort'))
					) {
						safeLog('info', 'Network issue during verification, continuing with session');
						sessionInitialized = true;
					} else {
						authError.set('Session verification failed. Please refresh the page.');
					}
				}
			} catch (innerErr) {
				safeLog('error', 'Unexpected error during session initialization', innerErr);
				authError.set('Failed to initialize session');
			}
		} else {
			safeLog('debug', 'No session found during initialization');
		}
	} catch (outerErr) {
		safeLog('error', 'Error in initializeSession', outerErr);
		authError.set('Failed to initialize session');
	} finally {
		authLoading.set(false);
	}
};

// Subscribe to auth changes
export const setupAuthListener = () => {
	if (!browser) {
		return () => {};
	}

	try {
		const {
			data: { subscription }
		} = supabase.auth.onAuthStateChange(async (event, currentSession) => {
			safeLog('debug', 'Auth state changed', { event, hasSession: !!currentSession });
			authLoading.set(true);

			if (event === 'SIGNED_OUT') {
				// Clear session when signed out
				session.set(null);
				sessionInitialized = false;
				authError.set(null);
				// Clean up inactivity check
				cleanupInactivityCheck();
			} else if (event === 'SIGNED_IN' || event === 'TOKEN_REFRESHED') {
				// Update the session
				session.set(currentSession);
				// Setup inactivity check for new sessions
				if (event === 'SIGNED_IN') {
					setupInactivityCheck();
				}
			}

			authLoading.set(false);
		});

		return () => {
			subscription.unsubscribe();
			cleanupInactivityCheck();
		};
	} catch (err) {
		safeLog('error', 'Error setting up auth listener', err);
		authLoading.set(false);
		return () => {};
	}
};

// Directly login a user
export const login = async (email: string, password: string) => {
	try {
		authLoading.set(true);
		authError.set(null);
		cleanupInactivityCheck(); // Clean up any existing check

		const { data, error } = await supabase.auth.signInWithPassword({
			email,
			password
		});

		if (error) {
			safeLog('error', 'Login error', { error: error.message });
			authError.set(error.message);
			throw error;
		}

		if (!data?.session?.access_token) {
			safeLog('warn', 'Login successful but missing access_token in session');
		}

		session.set(data.session);
		authError.set(null);

		// Set activity time and start monitoring
		updateLastActivity();
		setupInactivityCheck();

		// Flag to help with page refresh after auth
		if (browser) {
			sessionStorage.setItem('just_authenticated', 'true');
		}

		return data;
	} catch (err) {
		safeLog('error', 'Unexpected error during login', err);
		throw err;
	} finally {
		authLoading.set(false);
	}
};

// Sign up a new user
export const signup = async (email: string, password: string) => {
	try {
		authLoading.set(true);
		authError.set(null);

		const { data, error } = await supabase.auth.signUp({
			email,
			password
		});

		if (error) {
			safeLog('error', 'Signup error', { error: error.message });
			authError.set(error.message);
			throw error;
		}

		// Flag to help with page refresh after auth
		if (browser) {
			sessionStorage.setItem('just_authenticated', 'true');
		}

		return data;
	} catch (err) {
		safeLog('error', 'Unexpected error during signup', err);
		throw err;
	} finally {
		authLoading.set(false);
	}
};

// Sign out
export const logout = async () => {
	try {
		authLoading.set(true);

		// Clean up inactivity check
		cleanupInactivityCheck();

		// Sign out from Supabase
		const { error } = await supabase.auth.signOut();

		if (error) {
			safeLog('error', 'Logout error from Supabase', { error: error.message });
			authError.set(error.message);
		} else {
			safeLog('info', 'Logout successful');

			// Additional cleanup to ensure all auth data is cleared
			clearAllSessionData();

			session.set(null);
			sessionInitialized = false;
			authError.set(null);
		}
	} catch (err) {
		safeLog('error', 'Unexpected error during logout', err);
		authError.set('Failed to logout');

		// Still attempt to clean up locally even if there was an error
		clearAllSessionData();
		session.set(null);
		sessionInitialized = false;
	} finally {
		authLoading.set(false);
	}
};

// Create a profile for a user - now with CSRF protection
export const createProfile = async (userId: string, email: string) => {
	try {
		return await api.post('/api/create-profile', { userId, email });
	} catch (err) {
		safeLog('error', 'Error creating profile', err);
		throw err;
	}
};

// Update a profile - now with CSRF protection
export const updateProfile = async (profileData: any) => {
	try {
		// Get the current session
		const currentSession = get(session);
		if (!currentSession) {
			safeLog('error', 'Error updating profile: No active session');
			return { success: false, error: 'Authentication required' };
		}

		// Add Authorization header with the token if available
		const headers: Record<string, string> = {};
		if (currentSession?.access_token) {
			headers['Authorization'] = `Bearer ${currentSession.access_token}`;
		}

		// Call the API with the auth headers
		const result = await api.post('/api/update-profile', profileData, { headers });

		// Return success
		return result;
	} catch (err) {
		safeLog('error', 'Error updating profile', err);
		return { success: false, error: err instanceof Error ? err.message : 'Unknown error' };
	}
};
