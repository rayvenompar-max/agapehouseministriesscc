/**
 * Daybreak — Service Worker
 * Strategy:
 *   - HTML navigation requests          → Network-first, NO caching.
 *                                         HTML is PHP-rendered and contains server session state
 *                                         (window.CURRENT_MEMBER). Caching it causes stale auth.
 *   - CSS / JS static assets            → Cache-first, then network fallback
 *   - API calls                         → Pass-through to network (no caching; session cookies must work)
 *   - Images                            → Cache-first with long TTL
 */

const CACHE_VERSION = 'daybreak-v6';
const STATIC_CACHE  = CACHE_VERSION + '-static';
const IMAGE_CACHE   = CACHE_VERSION + '-images';

const BASE = '/DigitalEvangelization';

// ── Static assets to pre-cache on install ────────────────────────────────────
// Intentionally excludes ALL HTML — PHP pages contain server-rendered session
// state and must always be fetched live from the server.
const STATIC_ASSETS = [
  BASE + '/public/css/app.css',
  BASE + '/public/js/app.js',
];

// ── Install: pre-cache static assets only ────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

// ── Activate: clean up ALL old caches from previous SW versions ──────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== STATIC_CACHE && key !== IMAGE_CACHE)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ── Fetch: routing strategy ───────────────────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests — let the browser handle them natively
  if (request.method !== 'GET') return;

  // Skip requests outside our scope and admin routes
  if (!url.pathname.startsWith(BASE)) return;
  if (url.pathname.startsWith(BASE + '/admin')) return;

  // API calls — always pass through to network (never cache; session cookies must work)
  if (url.pathname.startsWith(BASE + '/api')) {
    return;
  }

  // HTML navigation requests — network-first, NO caching.
  // Stale cached HTML with old window.CURRENT_MEMBER would show wrong auth state.
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() =>
        // Only show offline page if network is truly unavailable
        new Response(
          '<!doctype html><html lang="en"><head><meta charset="utf-8">' +
          '<meta name="viewport" content="width=device-width,initial-scale=1">' +
          '<title>Offline — Agape House</title>' +
          '<style>body{font-family:sans-serif;text-align:center;padding:3rem;background:#0A1B33;color:#fff}' +
          'h1{color:#7FC4E8}p{color:#8FA9C4}</style></head>' +
          '<body><h1>You\'re offline</h1>' +
          '<p>Please check your connection and try again.</p>' +
          '<button onclick="location.reload()" style="margin-top:1rem;padding:.6rem 1.4rem;' +
          'background:#3E7CB1;color:#fff;border:none;border-radius:100px;cursor:pointer;font-size:14px">' +
          'Retry</button></body></html>',
          { status: 200, headers: { 'Content-Type': 'text/html' } }
        )
      )
    );
    return;
  }

  // Images — cache-first
  if (/\.(png|jpg|jpeg|gif|webp|svg|ico)$/i.test(url.pathname)) {
    event.respondWith(cacheFirstWithNetwork(request, IMAGE_CACHE));
    return;
  }

  // Fonts (Google Fonts CDN) — cache-first
  if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
    event.respondWith(cacheFirstWithNetwork(request, STATIC_CACHE));
    return;
  }

  // CSS / JS static assets — cache-first, network fallback
  event.respondWith(cacheFirstWithNetwork(request, STATIC_CACHE));
});

// ── Strategy helpers ─────────────────────────────────────────────────────────

/**
 * Cache-first: return cached response if available, otherwise fetch & cache.
 * Only returns a synthetic 503 on a true network failure (no connectivity).
 * Real server responses (including error codes) are always passed through as-is.
 */
async function cacheFirstWithNetwork(request, cacheName) {
  const cache  = await caches.open(cacheName);
  const cached = await cache.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    // Only cache clean 200 responses; always return whatever the server sent
    if (response && response.status === 200 && response.type !== 'opaque') {
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    // Network is genuinely unavailable — return a soft offline placeholder
    return new Response('Offline — please check your connection.', {
      status: 200,
      headers: { 'Content-Type': 'text/plain' },
    });
  }
}
