<script lang="ts">
	import { createEventDispatcher } from 'svelte';

	export let value: string = '';
	export let placeholder: string = 'Enter your text here...';
	export let rows: number = 4;
	export let label: string = 'Description';

	const dispatch = createEventDispatcher();

	function handleInput(event: Event) {
		const target = event.target as HTMLTextAreaElement;
		value = target.value;
		dispatch('input', { value });
	}

	function formatText(command: string) {
		const textarea = document.getElementById('richTextArea') as HTMLTextAreaElement;
		if (!textarea) return;

		const start = textarea.selectionStart || 0;
		const end = textarea.selectionEnd || 0;
		const selectedText = value.substring(start, end);

		let replacement = '';
		switch (command) {
			case 'bold':
				replacement = `**${selectedText}**`;
				break;
			case 'italic':
				replacement = `*${selectedText}*`;
				break;
			case 'bullet':
				replacement = `• ${selectedText}`;
				break;
			case 'dash':
				replacement = `- ${selectedText}`;
				break;
		}

		const newValue = value.substring(0, start) + replacement + value.substring(end);
		value = newValue;
		dispatch('input', { value: newValue });

		// Restore focus and selection
		setTimeout(() => {
			textarea.focus();
			textarea.setSelectionRange(start, start + replacement.length);
		}, 0);
	}

	function insertBullet() {
		const textarea = document.getElementById('richTextArea') as HTMLTextAreaElement;
		if (!textarea) return;

		const cursorPos = textarea.selectionStart || 0;
		const newValue = value.substring(0, cursorPos) + '\n• ' + value.substring(cursorPos);
		value = newValue;
		dispatch('input', { value: newValue });

		// Move cursor after the bullet
		setTimeout(() => {
			textarea.focus();
			textarea.setSelectionRange(cursorPos + 3, cursorPos + 3);
		}, 0);
	}

	function insertDash() {
		const textarea = document.getElementById('richTextArea') as HTMLTextAreaElement;
		if (!textarea) return;

		const cursorPos = textarea.selectionStart || 0;
		const newValue = value.substring(0, cursorPos) + '\n- ' + value.substring(cursorPos);
		value = newValue;
		dispatch('input', { value: newValue });

		// Move cursor after the dash
		setTimeout(() => {
			textarea.focus();
			textarea.setSelectionRange(cursorPos + 3, cursorPos + 3);
		}, 0);
	}
</script>

<div class="space-y-2">
	<label class="block text-sm font-medium text-gray-700" for="richTextArea">
		{label}
	</label>

	<!-- Formatting Toolbar -->
	<div class="flex flex-wrap gap-2 rounded-t-md border border-gray-300 bg-gray-50 p-3">
		<button
			type="button"
			onclick={() => formatText('bold')}
			title="Bold (Ctrl+B)"
			class="rounded px-3 py-2 text-sm font-bold text-gray-700 transition-colors hover:bg-gray-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
		>
			B
		</button>
		<button
			type="button"
			onclick={() => formatText('italic')}
			title="Italic (Ctrl+I)"
			class="rounded px-3 py-2 text-sm text-gray-700 italic transition-colors hover:bg-gray-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
		>
			I
		</button>
		<div class="w-px bg-gray-300"></div>
		<button
			type="button"
			onclick={insertBullet}
			title="Add bullet point"
			class="rounded px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
		>
			•
		</button>
		<button
			type="button"
			onclick={insertDash}
			title="Add dash point"
			class="rounded px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
		>
			-
		</button>
	</div>

	<!-- Text Area -->
	<textarea
		id="richTextArea"
		bind:value
		oninput={handleInput}
		{rows}
		{placeholder}
		class="block min-h-[200px] w-full resize-y rounded-b-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
	></textarea>

	<!-- Formatting Help -->
	<div class="text-xs text-gray-500">
		<strong>Formatting:</strong> Select text and click B for <strong>bold</strong> or I for
		<em>italic</em>. Use • or - for bullet points.
	</div>
</div>
