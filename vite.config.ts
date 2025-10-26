import tailwindcss from '@tailwindcss/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [tailwindcss(), sveltekit()],
	build: {
		// Increase chunk size limit to prevent warnings
		chunkSizeWarningLimit: 1600,
		rollupOptions: {
			output: {
				// Use manual chunks to better organize large dependencies
				manualChunks: {
					svelte: ['svelte', 'svelte/internal', 'svelte/store'],
					'svelte-kit': ['@sveltejs/kit']
				}
			}
		}
	}
});
