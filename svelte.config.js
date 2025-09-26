import adapter from '@sveltejs/adapter-vercel';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
    preprocess: vitePreprocess(),

    kit: {
        adapter: adapter({
            runtime: 'nodejs20.x'
        }),

        csp: {
            directives: {
                'script-src': ['self', 'https://js.stripe.com', 'unsafe-inline'],
                'img-src': ['self', 'data:', 'blob:', 'https://storage.googleapis.com', 'https://*.supabase.co'],
                'connect-src': ['self', 'https://*.supabase.co', 'https://api.stripe.com'],
                'frame-src': ['self', 'https://js.stripe.com', 'https://hooks.stripe.com']
            },
            reportOnly: {
                'script-src': ['self'],
                'report-uri': ['/api/csp-report']
            }
        },

        alias: {
            $lib: 'src/lib',
            $components: 'src/lib/components'
        }
    }
};

export default config;
