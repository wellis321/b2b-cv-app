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
            // Use Node.js runtime for Vercel
            runtime: 'nodejs18',
            target: 'node18'
        }),
        csp: {
            directives: {
                'script-src': ['self', 'https://js.stripe.com'],
                'img-src': ['self', 'data:', 'blob:', 'https://storage.googleapis.com', 'https://*.supabase.co']
            },
            reportOnly: {
                'script-src': ['self'],
                'report-uri': ['/api/csp-report']
            }
        }
    }
};

export default config;
