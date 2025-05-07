-- Create the responsibility_categories table
CREATE TABLE responsibility_categories (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    work_experience_id UUID REFERENCES work_experience(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL
);

-- Add RLS (Row Level Security) policies for responsibility_categories
ALTER TABLE responsibility_categories ENABLE ROW LEVEL SECURITY;

-- Create policy to allow users to select their own responsibility categories
CREATE POLICY "Users can view their own responsibility categories"
ON responsibility_categories
FOR SELECT
USING (EXISTS (
    SELECT 1 FROM work_experience
    WHERE work_experience.id = responsibility_categories.work_experience_id
    AND work_experience.profile_id = auth.uid()
));

-- Create policy to allow users to insert their own responsibility categories
CREATE POLICY "Users can insert their own responsibility categories"
ON responsibility_categories
FOR INSERT
WITH CHECK (EXISTS (
    SELECT 1 FROM work_experience
    WHERE work_experience.id = responsibility_categories.work_experience_id
    AND work_experience.profile_id = auth.uid()
));

-- Create policy to allow users to update their own responsibility categories
CREATE POLICY "Users can update their own responsibility categories"
ON responsibility_categories
FOR UPDATE
USING (EXISTS (
    SELECT 1 FROM work_experience
    WHERE work_experience.id = responsibility_categories.work_experience_id
    AND work_experience.profile_id = auth.uid()
));

-- Create policy to allow users to delete their own responsibility categories
CREATE POLICY "Users can delete their own responsibility categories"
ON responsibility_categories
FOR DELETE
USING (EXISTS (
    SELECT 1 FROM work_experience
    WHERE work_experience.id = responsibility_categories.work_experience_id
    AND work_experience.profile_id = auth.uid()
));

-- Create the responsibility_items table
CREATE TABLE responsibility_items (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    category_id UUID REFERENCES responsibility_categories(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL
);

-- Add RLS (Row Level Security) policies for responsibility_items
ALTER TABLE responsibility_items ENABLE ROW LEVEL SECURITY;

-- Create policy to allow users to select their own responsibility items
CREATE POLICY "Users can view their own responsibility items"
ON responsibility_items
FOR SELECT
USING (EXISTS (
    SELECT 1 FROM responsibility_categories
    JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
    WHERE responsibility_categories.id = responsibility_items.category_id
    AND work_experience.profile_id = auth.uid()
));

-- Create policy to allow users to insert their own responsibility items
CREATE POLICY "Users can insert their own responsibility items"
ON responsibility_items
FOR INSERT
WITH CHECK (EXISTS (
    SELECT 1 FROM responsibility_categories
    JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
    WHERE responsibility_categories.id = responsibility_items.category_id
    AND work_experience.profile_id = auth.uid()
));

-- Create policy to allow users to update their own responsibility items
CREATE POLICY "Users can update their own responsibility items"
ON responsibility_items
FOR UPDATE
USING (EXISTS (
    SELECT 1 FROM responsibility_categories
    JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
    WHERE responsibility_categories.id = responsibility_items.category_id
    AND work_experience.profile_id = auth.uid()
));

-- Create policy to allow users to delete their own responsibility items
CREATE POLICY "Users can delete their own responsibility items"
ON responsibility_items
FOR DELETE
USING (EXISTS (
    SELECT 1 FROM responsibility_categories
    JOIN work_experience ON responsibility_categories.work_experience_id = work_experience.id
    WHERE responsibility_categories.id = responsibility_items.category_id
    AND work_experience.profile_id = auth.uid()
));