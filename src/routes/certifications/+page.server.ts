import type { Actions, PageServerLoad } from './$types';
import { supabase } from '$lib/supabase';
import { fail, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async () => {
    const { data: { session } } = await supabase.auth.getSession();
    if (!session) return { certifications: [] };
    const { data, error } = await supabase
        .from('certifications')
        .select('*')
        .eq('profile_id', session.user.id)
        .order('date_obtained', { ascending: false });
    if (error) return { certifications: [], error: error.message };
    return { certifications: data };
};

export const actions: Actions = {
    default: async ({ request }) => {
        const { data: { session } } = await supabase.auth.getSession();
        if (!session) return fail(401, { error: 'Not authenticated' });

        const formData = await request.formData();
        const name = formData.get('name') as string;
        const issuer = formData.get('issuer') as string;
        const dateObtained = formData.get('dateObtained') as string;
        const expiryDate = formData.get('expiryDate') as string;
        const id = formData.get('id') as string | null;
        const action = formData.get('action') as string | null;

        // Validate required fields
        if (!name?.trim()) return fail(400, { error: 'Name is required' });
        if (!issuer?.trim()) return fail(400, { error: 'Issuer is required' });
        if (!dateObtained) return fail(400, { error: 'Date obtained is required' });

        try {
            // Handle delete action
            if (action === 'delete' && id) {
                const { error } = await supabase
                    .from('certifications')
                    .delete()
                    .eq('id', id)
                    .eq('profile_id', session.user.id);

                if (error) return fail(400, { error: error.message });
                throw redirect(303, '/certifications?success=delete');
            }

            // Handle update action
            if (id) {
                const { error } = await supabase
                    .from('certifications')
                    .update({
                        name,
                        issuer,
                        date_obtained: dateObtained,
                        expiry_date: expiryDate || null
                    })
                    .eq('id', id)
                    .eq('profile_id', session.user.id);

                if (error) return fail(400, { error: error.message });
                throw redirect(303, '/certifications?success=update');
            }

            // Handle create action (default)
            const { error } = await supabase
                .from('certifications')
                .insert({
                    profile_id: session.user.id,
                    name,
                    issuer,
                    date_obtained: dateObtained,
                    expiry_date: expiryDate || null
                });

            if (error) return fail(400, { error: error.message });
            throw redirect(303, '/certifications?success=create');
        } catch (err) {
            // Handle unexpected errors but don't catch redirects
            if (err instanceof Error) {
                console.error('Error processing certification action:', err);
                return fail(500, { error: 'An unexpected error occurred' });
            }
            throw err;
        }
    }
};