import type { Database } from '$lib/database.types';

// Define profile type from the database schema
export type Profile = Database['public']['Tables']['profiles']['Row'] & {
    // Add backward compatibility fields
    profile_photo_url?: string | null;
};

// Define a simplified profile type for default profile creation
export interface DefaultProfile {
    id: string;
    email: string | undefined;
    full_name: string;
    phone: string;
    location: string;
    username?: string;
    photo_url?: string | null;
    profile_photo_url?: string | null;
}

// Define a union type for profile data that can be either a complete profile or a default profile
export type ProfileData = Profile | DefaultProfile;