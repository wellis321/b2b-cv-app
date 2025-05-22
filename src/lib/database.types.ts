export type Json = string | number | boolean | null | { [key: string]: Json | undefined } | Json[];

export interface Database {
    public: {
        Tables: {
            certifications: {
                Row: {
                    created_at: string;
                    date_obtained: string;
                    expiry_date: string | null;
                    id: string;
                    issuer: string;
                    name: string;
                    profile_id: string | null;
                    updated_at: string;
                };
                Insert: {
                    created_at?: string;
                    date_obtained: string;
                    expiry_date?: string | null;
                    id?: string;
                    issuer: string;
                    name: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Update: {
                    created_at?: string;
                    date_obtained?: string;
                    expiry_date?: string | null;
                    id?: string;
                    issuer?: string;
                    name?: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'certifications_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            education: {
                Row: {
                    created_at: string;
                    end_date: string | null;
                    field_of_study: string | null;
                    id: string;
                    institution: string;
                    profile_id: string | null;
                    qualification: string;
                    start_date: string;
                    updated_at: string;
                };
                Insert: {
                    created_at?: string;
                    end_date?: string | null;
                    field_of_study?: string | null;
                    id?: string;
                    institution: string;
                    profile_id?: string | null;
                    qualification: string;
                    start_date: string;
                    updated_at?: string;
                };
                Update: {
                    created_at?: string;
                    end_date?: string | null;
                    field_of_study?: string | null;
                    id?: string;
                    institution?: string;
                    profile_id?: string | null;
                    qualification?: string;
                    start_date?: string;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'education_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            interests: {
                Row: {
                    created_at: string;
                    description: string | null;
                    id: string;
                    name: string;
                    profile_id: string | null;
                    updated_at: string;
                };
                Insert: {
                    created_at?: string;
                    description?: string | null;
                    id?: string;
                    name: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Update: {
                    created_at?: string;
                    description?: string | null;
                    id?: string;
                    name?: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'interests_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            professional_memberships: {
                Row: {
                    created_at: string;
                    end_date: string | null;
                    id: string;
                    organisation: string;
                    profile_id: string | null;
                    role: string | null;
                    start_date: string;
                    updated_at: string;
                };
                Insert: {
                    created_at?: string;
                    end_date?: string | null;
                    id?: string;
                    organisation: string;
                    profile_id?: string | null;
                    role?: string | null;
                    start_date: string;
                    updated_at?: string;
                };
                Update: {
                    created_at?: string;
                    end_date?: string | null;
                    id?: string;
                    organisation?: string;
                    profile_id?: string | null;
                    role?: string | null;
                    start_date?: string;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'professional_memberships_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            professional_qualification_equivalence: {
                Row: {
                    created_at: string;
                    description: string | null;
                    id: string;
                    level: string;
                    profile_id: string | null;
                    updated_at: string;
                };
                Insert: {
                    created_at?: string;
                    description?: string | null;
                    id?: string;
                    level: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Update: {
                    created_at?: string;
                    description?: string | null;
                    id?: string;
                    level?: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'professional_qualification_equivalence_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            profiles: {
                Row: {
                    created_at: string;
                    email: string | null;
                    full_name: string | null;
                    id: string;
                    location: string | null;
                    phone: string | null;
                    photo_url: string | null;
                    updated_at: string;
                    username: string;
                    linkedin_url: string | null;
                    bio: string | null;
                    cv_header_from_color: string;
                    cv_header_to_color: string;
                };
                Insert: {
                    created_at?: string;
                    email?: string | null;
                    full_name?: string | null;
                    id: string;
                    location?: string | null;
                    phone?: string | null;
                    photo_url?: string | null;
                    updated_at?: string;
                    username: string;
                    linkedin_url?: string | null;
                    bio?: string | null;
                    cv_header_from_color?: string;
                    cv_header_to_color?: string;
                };
                Update: {
                    created_at?: string;
                    email?: string | null;
                    full_name?: string | null;
                    id?: string;
                    location?: string | null;
                    phone?: string | null;
                    photo_url?: string | null;
                    updated_at?: string;
                    username?: string;
                    linkedin_url?: string | null;
                    bio?: string | null;
                    cv_header_from_color?: string;
                    cv_header_to_color?: string;
                };
                Relationships: [];
            };
            projects: {
                Row: {
                    created_at: string;
                    description: string | null;
                    end_date: string | null;
                    id: string;
                    profile_id: string | null;
                    start_date: string | null;
                    title: string;
                    updated_at: string;
                    url: string | null;
                };
                Insert: {
                    created_at?: string;
                    description?: string | null;
                    end_date?: string | null;
                    id?: string;
                    profile_id?: string | null;
                    start_date?: string | null;
                    title: string;
                    updated_at?: string;
                    url?: string | null;
                };
                Update: {
                    created_at?: string;
                    description?: string | null;
                    end_date?: string | null;
                    id?: string;
                    profile_id?: string | null;
                    start_date?: string | null;
                    title?: string;
                    updated_at?: string;
                    url?: string | null;
                };
                Relationships: [
                    {
                        foreignKeyName: 'projects_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            responsibility_categories: {
                Row: {
                    created_at: string;
                    id: string;
                    name: string;
                    sort_order: number;
                    work_experience_id: string | null;
                };
                Insert: {
                    created_at?: string;
                    id?: string;
                    name: string;
                    sort_order?: number;
                    work_experience_id?: string | null;
                };
                Update: {
                    created_at?: string;
                    id?: string;
                    name?: string;
                    sort_order?: number;
                    work_experience_id?: string | null;
                };
                Relationships: [
                    {
                        foreignKeyName: 'responsibility_categories_work_experience_id_fkey';
                        columns: ['work_experience_id'];
                        isOneToOne: false;
                        referencedRelation: 'work_experience';
                        referencedColumns: ['id'];
                    }
                ];
            };
            responsibility_items: {
                Row: {
                    category_id: string | null;
                    content: string;
                    created_at: string;
                    id: string;
                    sort_order: number;
                };
                Insert: {
                    category_id?: string | null;
                    content: string;
                    created_at?: string;
                    id?: string;
                    sort_order?: number;
                };
                Update: {
                    category_id?: string | null;
                    content?: string;
                    created_at?: string;
                    id?: string;
                    sort_order?: number;
                };
                Relationships: [
                    {
                        foreignKeyName: 'responsibility_items_category_id_fkey';
                        columns: ['category_id'];
                        isOneToOne: false;
                        referencedRelation: 'responsibility_categories';
                        referencedColumns: ['id'];
                    }
                ];
            };
            skills: {
                Row: {
                    category: string | null;
                    created_at: string;
                    id: string;
                    level: string | null;
                    name: string;
                    profile_id: string | null;
                    updated_at: string;
                };
                Insert: {
                    category?: string | null;
                    created_at?: string;
                    id?: string;
                    level?: string | null;
                    name: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Update: {
                    category?: string | null;
                    created_at?: string;
                    id?: string;
                    level?: string | null;
                    name?: string;
                    profile_id?: string | null;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'skills_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
            supporting_evidence: {
                Row: {
                    content: string;
                    created_at: string;
                    id: string;
                    qualification_equivalence_id: string | null;
                    sort_order: number;
                    updated_at: string;
                };
                Insert: {
                    content: string;
                    created_at?: string;
                    id?: string;
                    qualification_equivalence_id?: string | null;
                    sort_order?: number;
                    updated_at?: string;
                };
                Update: {
                    content?: string;
                    created_at?: string;
                    id?: string;
                    qualification_equivalence_id?: string | null;
                    sort_order?: number;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'supporting_evidence_qualification_equivalence_id_fkey';
                        columns: ['qualification_equivalence_id'];
                        isOneToOne: false;
                        referencedRelation: 'professional_qualification_equivalence';
                        referencedColumns: ['id'];
                    }
                ];
            };
            work_experience: {
                Row: {
                    company_name: string;
                    created_at: string;
                    description: string | null;
                    end_date: string | null;
                    id: string;
                    position: string;
                    profile_id: string | null;
                    start_date: string;
                    updated_at: string;
                };
                Insert: {
                    company_name: string;
                    created_at?: string;
                    description?: string | null;
                    end_date?: string | null;
                    id?: string;
                    position: string;
                    profile_id?: string | null;
                    start_date: string;
                    updated_at?: string;
                };
                Update: {
                    company_name?: string;
                    created_at?: string;
                    description?: string | null;
                    end_date?: string | null;
                    id?: string;
                    position?: string;
                    profile_id?: string | null;
                    start_date?: string;
                    updated_at?: string;
                };
                Relationships: [
                    {
                        foreignKeyName: 'work_experience_profile_id_fkey';
                        columns: ['profile_id'];
                        isOneToOne: false;
                        referencedRelation: 'profiles';
                        referencedColumns: ['id'];
                    }
                ];
            };
        };
        Views: {
            [_ in never]: never;
        };
        Functions: {
            [_ in never]: never;
        };
        Enums: {
            [_ in never]: never;
        };
        CompositeTypes: {
            [_ in never]: never;
        };
    };
}
