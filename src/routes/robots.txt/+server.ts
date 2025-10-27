import type { RequestHandler } from './$types';

export const GET: RequestHandler = async () => {
    const robots = `User-agent: *
Allow: /
Disallow: /api/
Disallow: /admin/
Disallow: /preview-cv
Disallow: /*/edit

Sitemap: https://simple-cv-builder.com/sitemap.xml
`;

    return new Response(robots, {
        headers: {
            'Content-Type': 'text/plain',
            'Cache-Control': 'public, max-age=3600'
        }
    });
};
