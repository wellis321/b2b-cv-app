<script lang="ts">
	/**
	 * A reusable component for form fields with consistent styling
	 */
	export let label: string;
	export let id: string;
	export let type: string = 'text';
	export let value: string = '';
	export let placeholder: string = '';
	export let required: boolean = false;
	export let errorMessage: string | null = null;
	export let disabled: boolean = false;

	// Generate a unique ID if not provided
	$: fieldId = id || label.toLowerCase().replace(/\s+/g, '-');
</script>

<div>
	<label class="mb-1 block text-sm font-medium text-gray-700" for={fieldId}>
		{label}
		{required ? '*' : ''}
	</label>

	<input
		id={fieldId}
		name={fieldId}
		{type}
		bind:value
		{placeholder}
		class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {errorMessage
			? 'border-red-500'
			: ''}"
		{required}
		{disabled}
		on:input
		on:change
		on:blur
	/>

	{#if errorMessage}
		<p class="mt-1 text-sm text-red-600">{errorMessage}</p>
	{/if}

	<slot></slot>
</div>
