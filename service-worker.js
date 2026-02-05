/**
 * Pass-through service worker for the content editor.
 * Ensures same-origin requests (PHP pages and /api/) always go to the network.
 * Fixes "Failed to fetch" when another SW (e.g. from WebLLM) intercepts and fails.
 */
self.addEventListener('install', function () {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function (event) {
  const url = new URL(event.request.url);
  const sameOrigin = url.origin === self.location.origin;
  const isAppRequest =
    sameOrigin &&
    (url.pathname.indexOf('/api/') === 0 || url.pathname.endsWith('.php') || url.pathname === '/' || url.pathname === '/index.php');

  if (isAppRequest) {
    event.respondWith(
      fetch(event.request).catch(function (err) {
        // Return JSON error response so frontend can parse it properly
        return new Response(JSON.stringify({
          success: false,
          error: 'Network error: ' + (err.message || 'Failed to connect to server')
        }), {
          status: 503,
          statusText: 'Service Unavailable',
          headers: {
            'Content-Type': 'application/json'
          }
        });
      })
    );
  }
});
