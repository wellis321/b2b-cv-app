import { supabase } from '$lib/supabase';

export interface ResponsibilityCategory {
    id: string;
    work_experience_id: string;
    name: string;
    sort_order: number;
    created_at?: string;
}

export interface ResponsibilityItem {
    id: string;
    category_id: string;
    content: string;
    sort_order: number;
    created_at?: string;
}

export type CategoryWithItems = ResponsibilityCategory & {
    items: ResponsibilityItem[];
};

// Get all categories and their items for a work experience entry
export async function getResponsibilitiesForExperience(
    workExperienceId: string
): Promise<CategoryWithItems[]> {
    if (!workExperienceId) {
        console.error(
            'getResponsibilitiesForExperience called with invalid workExperienceId:',
            workExperienceId
        );
        return [];
    }

    try {
        // First get all categories
        const { data: categories, error: categoriesError } = await supabase
            .from('responsibility_categories')
            .select('*')
            .eq('work_experience_id', workExperienceId)
            .order('sort_order', { ascending: true });

        if (categoriesError) {
            console.error('Error fetching responsibility categories:', categoriesError);
            return [];
        }

        if (!categories || categories.length === 0) {
            return [];
        }

        // Get all items for these categories
        const categoryIds = categories.map((cat) => cat.id);

        const { data: items, error: itemsError } = await supabase
            .from('responsibility_items')
            .select('*')
            .in('category_id', categoryIds)
            .order('sort_order', { ascending: true });

        if (itemsError) {
            console.error('Error fetching responsibility items:', itemsError);
            return categories.map((cat) => ({ ...cat, items: [] }));
        }

        // Group items by category
        const result = categories.map((category) => ({
            ...category,
            items: items?.filter((item) => item.category_id === category.id) || []
        }));

        return result;
    } catch (err) {
        console.error('Unexpected error fetching responsibilities:', err);
        return [];
    }
}

// Add a new category to a work experience
export async function addCategory(
    workExperienceId: string,
    name: string
): Promise<ResponsibilityCategory | null> {
    try {
        // Get the highest order to place the new category at the end
        const { data: existingCategories, error: countError } = await supabase
            .from('responsibility_categories')
            .select('sort_order')
            .eq('work_experience_id', workExperienceId)
            .order('sort_order', { ascending: false })
            .limit(1);

        const nextOrder =
            existingCategories && existingCategories.length > 0
                ? existingCategories[0].sort_order + 1
                : 0;

        const { data, error } = await supabase
            .from('responsibility_categories')
            .insert({
                work_experience_id: workExperienceId,
                name,
                sort_order: nextOrder
            })
            .select()
            .single();

        if (error) {
            console.error('Error adding responsibility category:', error);
            return null;
        }

        return data;
    } catch (err) {
        console.error('Unexpected error adding category:', err);
        return null;
    }
}

// Update a category
export async function updateCategory(categoryId: string, name: string): Promise<boolean> {
    try {
        const { error } = await supabase
            .from('responsibility_categories')
            .update({ name })
            .eq('id', categoryId);

        if (error) {
            console.error('Error updating responsibility category:', error);
            return false;
        }

        return true;
    } catch (err) {
        console.error('Unexpected error updating category:', err);
        return false;
    }
}

// Delete a category and all its items
export async function deleteCategory(categoryId: string): Promise<boolean> {
    try {
        // Delete all items in the category first
        const { error: itemsError } = await supabase
            .from('responsibility_items')
            .delete()
            .eq('category_id', categoryId);

        if (itemsError) {
            console.error('Error deleting category items:', itemsError);
            return false;
        }

        // Then delete the category
        const { error } = await supabase
            .from('responsibility_categories')
            .delete()
            .eq('id', categoryId);

        if (error) {
            console.error('Error deleting responsibility category:', error);
            return false;
        }

        return true;
    } catch (err) {
        console.error('Unexpected error deleting category:', err);
        return false;
    }
}

// Add a new item to a category
export async function addItem(
    categoryId: string,
    content: string
): Promise<ResponsibilityItem | null> {
    try {
        console.log('addItem function called with:', { categoryId, content });

        // Verify we have a valid session first
        const { data: sessionData, error: sessionError } = await supabase.auth.getSession();
        if (sessionError || !sessionData.session) {
            console.error('Authentication error when adding responsibility item:', sessionError?.message || 'No active session');
            throw new Error('Authentication required to add responsibility items');
        }

        // Get the highest order to place the new item at the end
        const { data: existingItems, error: countError } = await supabase
            .from('responsibility_items')
            .select('sort_order')
            .eq('category_id', categoryId)
            .order('sort_order', { ascending: false })
            .limit(1);

        console.log('Existing items query result:', { existingItems, countError });

        // Handle potential errors from the count query
        if (countError) {
            console.error('Error getting existing items:', countError);
        }

        const nextOrder =
            existingItems && existingItems.length > 0 ? existingItems[0].sort_order + 1 : 0;

        console.log('Calculated next order:', nextOrder);

        // Use a direct insert with error handling
        try {
            const insertResult = await supabase
                .from('responsibility_items')
                .insert({
                    category_id: categoryId,
                    content,
                    sort_order: nextOrder
                })
                .select()
                .single();

            const { data, error } = insertResult;
            console.log('Insert result:', { data, error });

            if (error) {
                console.error('Error adding responsibility item:', error);
                return null;
            }

            return data;
        } catch (insertErr: any) {
            // Check if this is an authentication error
            if (insertErr?.message?.includes('JWT') || insertErr?.status === 401) {
                console.error('Authentication error during insert:', insertErr);

                // Try to refresh the session
                const { data: refreshData, error: refreshError } = await supabase.auth.refreshSession();
                if (refreshError || !refreshData.session) {
                    console.error('Failed to refresh session:', refreshError);
                    throw new Error('Authentication expired. Please log in again.');
                }

                // Retry the insert with the refreshed token
                const { data, error } = await supabase
                    .from('responsibility_items')
                    .insert({
                        category_id: categoryId,
                        content,
                        sort_order: nextOrder
                    })
                    .select()
                    .single();

                if (error) {
                    console.error('Error adding item after token refresh:', error);
                    return null;
                }

                return data;
            } else {
                // Re-throw other errors
                throw insertErr;
            }
        }
    } catch (err: any) {
        console.error('Unexpected error adding item:', err?.message || err);
        throw err; // Re-throw to allow calling code to handle the error
    }
}

// Update an item
export async function updateItem(itemId: string, content: string): Promise<boolean> {
    try {
        const { error } = await supabase
            .from('responsibility_items')
            .update({ content })
            .eq('id', itemId);

        if (error) {
            console.error('Error updating responsibility item:', error);
            return false;
        }

        return true;
    } catch (err) {
        console.error('Unexpected error updating item:', err);
        return false;
    }
}

// Delete an item
export async function deleteItem(itemId: string): Promise<boolean> {
    try {
        const { error } = await supabase.from('responsibility_items').delete().eq('id', itemId);

        if (error) {
            console.error('Error deleting responsibility item:', error);
            return false;
        }

        return true;
    } catch (err) {
        console.error('Unexpected error deleting item:', err);
        return false;
    }
}

// Reorder categories
export async function reorderCategories(categoryIds: string[]): Promise<boolean> {
    try {
        // Create a batch of updates
        const updates = categoryIds.map((id, index) => ({
            id,
            sort_order: index
        }));

        for (const update of updates) {
            const { error } = await supabase
                .from('responsibility_categories')
                .update({ sort_order: update.sort_order })
                .eq('id', update.id);

            if (error) {
                console.error('Error reordering category:', error);
                return false;
            }
        }

        return true;
    } catch (err) {
        console.error('Unexpected error reordering categories:', err);
        return false;
    }
}

// Reorder items within a category
export async function reorderItems(itemIds: string[]): Promise<boolean> {
    try {
        // Create a batch of updates
        const updates = itemIds.map((id, index) => ({
            id,
            sort_order: index
        }));

        for (const update of updates) {
            const { error } = await supabase
                .from('responsibility_items')
                .update({ sort_order: update.sort_order })
                .eq('id', update.id);

            if (error) {
                console.error('Error reordering item:', error);
                return false;
            }
        }

        return true;
    } catch (err) {
        console.error('Unexpected error reordering items:', err);
        return false;
    }
}
