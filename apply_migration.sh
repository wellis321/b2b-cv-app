#!/bin/bash

echo "Applying work experience sort_order migration..."

# Check if we're in the right directory
if [ ! -f "supabase/migrations/20250108000001_add_work_experience_sort_order.sql" ]; then
    echo "Error: Migration file not found. Please run this script from the project root directory."
    exit 1
fi

# Apply the migration using Supabase CLI
echo "Running migration..."
supabase db reset --db-url postgresql://postgres:postgres@localhost:54322/postgres

echo "Migration completed successfully!"
echo ""
echo "The work_experience table now has a sort_order field that allows users to:"
echo "1. Reorder their work experiences by dragging and dropping"
echo "2. Maintain a custom order while defaulting to date-based sorting"
echo "3. Reset back to date-based sorting when needed"
echo ""
echo "You can now use the reorder functionality in the work experience page."
