import adapter from '@sveltejs/adapter-vercel';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
    // Consult https://svelte.dev/docs/kit/integrations
    // for more information about preprocessors
    preprocess: vitePreprocess(),

    kit: {
        // adapter-auto only supports some environments, see https://svelte.dev/docs/kit/adapter-auto for a list.
        // If your environment is not supported, or you settled on a specific environment, switch out the adapter.
        // See https://svelte.dev/docs/kit/adapters for more information about adapters.
        adapter: adapter({
            // Use the Edge runtime which is more compatible
            runtime: 'edge',
            // Enable regional edge functions
            regions: ['iad1']
        }),
        csp: {
            mode: 'auto', // Uses nonces for dynamic pages, hashes for prerendered pages
            directives: {
                'default-src': ['self'],
                'script-src': ['self', 'https://storage.googleapis.com'],
                'connect-src': ['self', 'https://*.supabase.co', 'wss://*.supabase.co', 'https://storage.googleapis.com'],
                'img-src': ['self', 'data:', 'https://storage.googleapis.com', 'https://*.supabase.co'],
                'style-src': ['self', 'unsafe-inline'], // Most Svelte transitions need this
                'font-src': ['self'],
                'object-src': ['none'],
                'base-uri': ['self'],
                'frame-ancestors': ['none'],
                'form-action': ['self'],
                'upgrade-insecure-requests': true
            }
        }
    }
};

export default config;
