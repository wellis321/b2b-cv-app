import { supabase } from '$lib/supabase';

export interface QualificationEquivalence {
	id: string;
	profile_id: string | null;
	level: string;
	description: string | null;
	qualification?: string | null;
	equivalent_to?: string | null;
	created_at?: string;
	updated_at?: string;
}

export interface SupportingEvidence {
	id: string;
	qualification_equivalence_id: string | null;
	content: string;
	sort_order: number;
	created_at?: string;
	updated_at?: string;
}

export type QualificationWithEvidence = QualificationEquivalence & {
	evidence: SupportingEvidence[];
};

// Get all qualifications for a user
export async function getQualifications(userId: string): Promise<QualificationWithEvidence[]> {
	try {
		// First get all qualifications
		const { data: qualifications, error: qualificationsError } = await supabase
			.from('professional_qualification_equivalence')
			.select('*')
			.eq('profile_id', userId)
			.order('created_at', { ascending: false });

		if (qualificationsError) {
			console.error('Error fetching qualifications:', qualificationsError);
			return [];
		}

		if (!qualifications || qualifications.length === 0) {
			return [];
		}

		// Get all evidence for these qualifications
		const qualificationIds = qualifications.map((qual) => qual.id);
		const { data: evidenceItems, error: evidenceError } = await supabase
			.from('supporting_evidence')
			.select('*')
			.in('qualification_equivalence_id', qualificationIds)
			.order('sort_order', { ascending: true });

		if (evidenceError) {
			console.error('Error fetching supporting evidence:', evidenceError);
			return qualifications.map((qual) => ({ ...qual, evidence: [] }));
		}

		// Group evidence by qualification
		return qualifications.map((qualification) => ({
			...qualification,
			evidence:
				evidenceItems?.filter((item) => item.qualification_equivalence_id === qualification.id) ||
				[]
		}));
	} catch (err) {
		console.error('Unexpected error fetching qualifications:', err);
		return [];
	}
}

// Get a single qualification with its evidence
export async function getQualification(id: string): Promise<QualificationWithEvidence | null> {
	try {
		// Get the qualification
		const { data: qualification, error: qualificationError } = await supabase
			.from('professional_qualification_equivalence')
			.select('*')
			.eq('id', id)
			.single();

		if (qualificationError) {
			console.error('Error fetching qualification:', qualificationError);
			return null;
		}

		// Get all evidence for this qualification
		const { data: evidenceItems, error: evidenceError } = await supabase
			.from('supporting_evidence')
			.select('*')
			.eq('qualification_equivalence_id', id)
			.order('sort_order', { ascending: true });

		if (evidenceError) {
			console.error('Error fetching supporting evidence:', evidenceError);
			return { ...qualification, evidence: [] };
		}

		return {
			...qualification,
			evidence: evidenceItems || []
		};
	} catch (err) {
		console.error('Unexpected error fetching qualification:', err);
		return null;
	}
}

// Create a new qualification
export async function createQualification(
	userId: string,
	level: string,
	description: string
): Promise<QualificationEquivalence | null> {
	try {
		const { data, error } = await supabase
			.from('professional_qualification_equivalence')
			.insert({
				profile_id: userId,
				level,
				description,
				qualification: level,
				equivalent_to: 'Equivalent qualification'
			})
			.select()
			.single();

		if (error) {
			console.error('Error creating qualification:', error);
			return null;
		}

		return data;
	} catch (err) {
		console.error('Unexpected error creating qualification:', err);
		return null;
	}
}

// Update a qualification
export async function updateQualification(
	id: string,
	level: string,
	description: string
): Promise<boolean> {
	try {
		const { error } = await supabase
			.from('professional_qualification_equivalence')
			.update({
				level,
				description,
				qualification: level,
				updated_at: new Date().toISOString()
			})
			.eq('id', id);

		if (error) {
			console.error('Error updating qualification:', error);
			return false;
		}

		return true;
	} catch (err) {
		console.error('Unexpected error updating qualification:', err);
		return false;
	}
}

// Delete a qualification and its evidence
export async function deleteQualification(id: string): Promise<boolean> {
	try {
		// Delete the qualification (evidence will be deleted via cascade)
		const { error } = await supabase
			.from('professional_qualification_equivalence')
			.delete()
			.eq('id', id);

		if (error) {
			console.error('Error deleting qualification:', error);
			return false;
		}

		return true;
	} catch (err) {
		console.error('Unexpected error deleting qualification:', err);
		return false;
	}
}

// Add a new evidence item
export async function addEvidence(
	qualificationId: string,
	content: string
): Promise<SupportingEvidence | null> {
	try {
		// Get the highest order to place the new item at the end
		const { data: existingItems, error: countError } = await supabase
			.from('supporting_evidence')
			.select('sort_order')
			.eq('qualification_equivalence_id', qualificationId)
			.order('sort_order', { ascending: false })
			.limit(1);

		const nextOrder =
			existingItems && existingItems.length > 0 ? existingItems[0].sort_order + 1 : 0;

		const { data, error } = await supabase
			.from('supporting_evidence')
			.insert({
				qualification_equivalence_id: qualificationId,
				content,
				sort_order: nextOrder
			})
			.select()
			.single();

		if (error) {
			console.error('Error adding evidence:', error);
			return null;
		}

		return data;
	} catch (err) {
		console.error('Unexpected error adding evidence:', err);
		return null;
	}
}

// Update an evidence item
export async function updateEvidence(id: string, content: string): Promise<boolean> {
	try {
		const { error } = await supabase
			.from('supporting_evidence')
			.update({
				content,
				updated_at: new Date().toISOString()
			})
			.eq('id', id);

		if (error) {
			console.error('Error updating evidence:', error);
			return false;
		}

		return true;
	} catch (err) {
		console.error('Unexpected error updating evidence:', err);
		return false;
	}
}

// Delete an evidence item
export async function deleteEvidence(id: string): Promise<boolean> {
	try {
		const { error } = await supabase.from('supporting_evidence').delete().eq('id', id);

		if (error) {
			console.error('Error deleting evidence:', error);
			return false;
		}

		return true;
	} catch (err) {
		console.error('Unexpected error deleting evidence:', err);
		return false;
	}
}

// Reorder evidence items
export async function reorderEvidence(evidenceIds: string[]): Promise<boolean> {
	try {
		const updates = evidenceIds.map((id, index) => ({
			id,
			sort_order: index
		}));

		for (const update of updates) {
			const { error } = await supabase
				.from('supporting_evidence')
				.update({ sort_order: update.sort_order })
				.eq('id', update.id);

			if (error) {
				console.error('Error reordering evidence:', error);
				return false;
			}
		}

		return true;
	} catch (err) {
		console.error('Unexpected error reordering evidence:', err);
		return false;
	}
}
