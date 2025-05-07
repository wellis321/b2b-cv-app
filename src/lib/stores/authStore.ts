import { writable } from 'svelte/store';
import { supabase } from '$lib/supabase';
import type { Session } from '@supabase/supabase-js';
import { browser } from '$app/environment';

// Create a writable store with initial value of null
export const session = writable<Session | null>(null);

// Flag to track if we've initialized the session
let sessionInitialized = false;

// Initialize the store with the current session
export const initializeSession = async () => {
    try {
        // Don't try to initialize twice
        if (sessionInitialized && browser) {
            return;
        }

        // Clear any existing session first to avoid stale data
        session.set(null);

        // Get the session from Supabase
        const { data, error } = await supabase.auth.getSession();

        if (error) {
            console.error('Error getting session:', error);
            return;
        }

        console.log('Session initialized:', data.session ? 'Session present' : 'No session');

        if (data.session) {
            // Set the session in the store
            session.set(data.session);

            // Force refresh token to ensure it's valid
            try {
                const { data: refreshData, error: refreshError } = await supabase.auth.refreshSession();

                if (refreshError) {
                    console.error('Error refreshing token:', refreshError);
                    // Force logout if refresh fails
                    await logout();
                } else if (refreshData.session) {
                    console.log('Session refreshed successfully');
                    session.set(refreshData.session);
                }
            } catch (refreshErr) {
                console.error('Error during token refresh:', refreshErr);
            }
        }

        sessionInitialized = true;
    } catch (err) {
        console.error('Error initializing session:', err);
    }
};

// Subscribe to auth changes
export const setupAuthListener = () => {
    if (!browser) {
        return () => { };
    }

    try {
        const { data: { subscription } } = supabase.auth.onAuthStateChange(
            (event, currentSession) => {
                console.log('Auth state changed in store:', event, 'Session:', currentSession ? 'Present' : 'None');

                if (event === 'SIGNED_OUT') {
                    // Make sure we completely reset the session
                    session.set(null);
                    sessionInitialized = false;
                } else {
                    session.set(currentSession);
                }
            }
        );

        return () => {
            subscription.unsubscribe();
        };
    } catch (err) {
        console.error('Error setting up auth listener:', err);
        return () => { }; // Return a no-op function if there's an error
    }
};

// Directly login a user
export const login = async (email: string, password: string) => {
    try {
        const { data, error } = await supabase.auth.signInWithPassword({
            email,
            password
        });

        if (error) {
            console.error('Login error:', error);
            throw error;
        }

        console.log('Login successful, session:', data.session ? 'Present' : 'No session');
        session.set(data.session);
        return data;
    } catch (err) {
        console.error('Unexpected error during login:', err);
        throw err;
    }
};

// Sign up a new user
export const signup = async (email: string, password: string) => {
    try {
        const { data, error } = await supabase.auth.signUp({
            email,
            password,
            options: {
                emailRedirectTo: browser ? window.location.origin : undefined
            }
        });

        if (error) {
            console.error('Signup error:', error);
            throw error;
        }

        console.log('Signup successful, session:', data.session ? 'Present' : 'No session');
        return data;
    } catch (err) {
        console.error('Unexpected error during signup:', err);
        throw err;
    }
};

// Sign out
export const logout = async () => {
    try {
        const { error } = await supabase.auth.signOut();
        if (error) {
            console.error('Logout error:', error);
        } else {
            console.log('Logout successful');
            session.set(null);
            sessionInitialized = false;
        }
    } catch (err) {
        console.error('Unexpected error during logout:', err);
        // Still set session to null in case of error
        session.set(null);
        sessionInitialized = false;
    }
};

// Create a profile for a user
export const createProfile = async (userId: string, email: string) => {
    try {
        // Get current session to include the token
        const { data } = await supabase.auth.getSession();
        const accessToken = data.session?.access_token;

        const response = await fetch('/api/create-profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(accessToken ? { 'Authorization': `Bearer ${accessToken}` } : {})
            },
            body: JSON.stringify({ userId, email }),
            credentials: 'include'
        });

        return await response.json();
    } catch (err) {
        console.error('Error creating profile:', err);
        throw err;
    }
};

// Update a profile
export const updateProfile = async (profileData: any) => {
    try {
        // Get current session to include the token
        const { data } = await supabase.auth.getSession();
        const accessToken = data.session?.access_token;

        const response = await fetch('/api/update-profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(accessToken ? { 'Authorization': `Bearer ${accessToken}` } : {})
            },
            body: JSON.stringify(profileData),
            credentials: 'include'
        });

        return await response.json();
    } catch (err) {
        console.error('Error updating profile:', err);
        throw err;
    }
};