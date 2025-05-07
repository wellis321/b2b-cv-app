-- Create profiles table
CREATE TABLE profiles (
    id UUID REFERENCES auth.users ON DELETE CASCADE,
    full_name TEXT,
    email TEXT UNIQUE,
    phone TEXT,
    location TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    PRIMARY KEY (id)
);

-- Create work_experience table
CREATE TABLE work_experience (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    company_name TEXT NOT NULL,
    position TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create responsibility_categories table
CREATE TABLE responsibility_categories (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    work_experience_id UUID REFERENCES work_experience(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create responsibility_items table
CREATE TABLE responsibility_items (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    category_id UUID REFERENCES responsibility_categories(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create projects table
CREATE TABLE projects (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    url TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create education table
CREATE TABLE education (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    institution TEXT NOT NULL,
    degree TEXT NOT NULL,
    field_of_study TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create skills table
CREATE TABLE skills (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    level TEXT,
    category TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create certifications table
CREATE TABLE certifications (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    issuer TEXT NOT NULL,
    date_obtained DATE NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create professional_memberships table
CREATE TABLE professional_memberships (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    organisation TEXT NOT NULL,
    role TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create interests table
CREATE TABLE interests (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    profile_id UUID REFERENCES profiles(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create RLS policies
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE work_experience ENABLE ROW LEVEL SECURITY;
ALTER TABLE responsibility_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE responsibility_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE education ENABLE ROW LEVEL SECURITY;
ALTER TABLE skills ENABLE ROW LEVEL SECURITY;
ALTER TABLE certifications ENABLE ROW LEVEL SECURITY;
ALTER TABLE professional_memberships ENABLE ROW LEVEL SECURITY;
ALTER TABLE interests ENABLE ROW LEVEL SECURITY;

-- Create policies for authenticated users
CREATE POLICY "Users can view their own profile"
    ON profiles FOR SELECT
    USING (auth.uid() = id);

CREATE POLICY "Users can update their own profile"
    ON profiles FOR UPDATE
    USING (auth.uid() = id);

CREATE POLICY "Users can insert their own profile"
    ON profiles FOR INSERT
    WITH CHECK (auth.uid() = id);

-- Similar policies for other tables
CREATE POLICY "Users can manage their own work experience"
    ON work_experience FOR ALL
    USING (auth.uid() = profile_id);

-- Policies for responsibility categories
CREATE POLICY "Users can view their own responsibility categories"
    ON responsibility_categories FOR SELECT
    USING (EXISTS (
        SELECT 1 FROM work_experience
        WHERE work_experience.id = responsibility_categories.work_experience_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can insert their own responsibility categories"
    ON responsibility_categories FOR INSERT
    WITH CHECK (EXISTS (
        SELECT 1 FROM work_experience
        WHERE work_experience.id = responsibility_categories.work_experience_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can update their own responsibility categories"
    ON responsibility_categories FOR UPDATE
    USING (EXISTS (
        SELECT 1 FROM work_experience
        WHERE work_experience.id = responsibility_categories.work_experience_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can delete their own responsibility categories"
    ON responsibility_categories FOR DELETE
    USING (EXISTS (
        SELECT 1 FROM work_experience
        WHERE work_experience.id = responsibility_categories.work_experience_id
        AND work_experience.profile_id = auth.uid()
    ));

-- Policies for responsibility items
CREATE POLICY "Users can view their own responsibility items"
    ON responsibility_items FOR SELECT
    USING (EXISTS (
        SELECT 1 FROM responsibility_categories
        JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
        WHERE responsibility_categories.id = responsibility_items.category_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can insert their own responsibility items"
    ON responsibility_items FOR INSERT
    WITH CHECK (EXISTS (
        SELECT 1 FROM responsibility_categories
        JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
        WHERE responsibility_categories.id = responsibility_items.category_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can update their own responsibility items"
    ON responsibility_items FOR UPDATE
    USING (EXISTS (
        SELECT 1 FROM responsibility_categories
        JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
        WHERE responsibility_categories.id = responsibility_items.category_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can delete their own responsibility items"
    ON responsibility_items FOR DELETE
    USING (EXISTS (
        SELECT 1 FROM responsibility_categories
        JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
        WHERE responsibility_categories.id = responsibility_items.category_id
        AND work_experience.profile_id = auth.uid()
    ));

CREATE POLICY "Users can manage their own projects"
    ON projects FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own education"
    ON education FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own skills"
    ON skills FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own certifications"
    ON certifications FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own professional memberships"
    ON professional_memberships FOR ALL
    USING (auth.uid() = profile_id);

CREATE POLICY "Users can manage their own interests"
    ON interests FOR ALL
    USING (auth.uid() = profile_id);