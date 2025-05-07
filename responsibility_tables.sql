-- Create responsibility_categories table
CREATE TABLE IF NOT EXISTS responsibility_categories (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    work_experience_id UUID REFERENCES work_experience(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Create responsibility_items table
CREATE TABLE IF NOT EXISTS responsibility_items (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    category_id UUID REFERENCES responsibility_categories(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Enable RLS
ALTER TABLE responsibility_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE responsibility_items ENABLE ROW LEVEL SECURITY;

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