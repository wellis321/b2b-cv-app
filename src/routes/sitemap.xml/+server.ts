import type { RequestHandler } from './$types';

export const GET: RequestHandler = async () => {
    const baseUrl = 'https://simple-cv-builder.com';
    const routes = [
        '/',
        '/profile',
        '/dashboard',
        '/work-experience',
        '/education',
        '/projects',
        '/skills',
        '/certifications',
        '/memberships',
        '/interests',
        '/qualification-equivalence',
        '/professional-summary',
        '/preview-cv',
        '/subscription',
        '/privacy',
        '/terms'
    ];

    const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${routes
            .map(
                (route) => `  <url>
    <loc>${baseUrl}${route}</loc>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>${route === '/' ? '1.0' : '0.8'}</priority>
  </url>`
            )
            .join('\n')}
</urlset>`;

    return new Response(sitemap, {
        headers: {
            'Content-Type': 'application/xml',
            'Cache-Control': 'public, max-age=3600'
        }
    });
};
