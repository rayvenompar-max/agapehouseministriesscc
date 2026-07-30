/**
 * Daybreak — Service Worker
 * Strategy:
 *   - App shell (HTML, CSS, JS, fonts) → Cache-first, then network fallback
 *   - API calls                         → Network-first, then cache fallback
 *   - Images                            → Cache-first with long TTL
 */

const CACHE_VERSION  = 'daybreak-v1';
const SHELL_CACHE    = CACHE_VERSION + '-shell';
const IMAGE_CACHE    = CACHE_VERSION + '-images';
const API_CACHE      = CACHE_VERSION + '-api';

const BASE = '/DigitalEvangelization';

// ── App shell files to pre-cache on install ──────────────────────────────────
const SHELL_ASSETS = [
  BASE + '/',
  BASE + '/public/css/app.css',
  BASE + '/public/js/app.js',
  // Google Fonts — these will be cached on first visit
];

// ── Install: pre-cache the shell ─────────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(SHELL_CACHE).then(cache => cache.addAll(SHELL_ASSETS))
  );
  // Activate immediately — don't wait for old tabs to close
  self.skipWaiting();
});

// ── Activate: clean up old caches ────────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key.startsWith('daybreak-') && key !== SHELL_CACHE && key !== IMAGE_CACHE && key !== API_CACHE)
          .map(key => caches.delete(key))
      )
    )
  );
  // Take control of all open clients immediately
  self.clients.claim();
});

// ── Fetch: routing strategy ───────────────────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET, browser extensions, and admin routes
  if (request.method !== 'GET') return;
  if (!url.pathname.startsWith(BASE)) return;
  if (url.pathname.startsWith(BASE + '/admin')) return;
  if (url.pathname.startsWith(BASE + '/api')) {
    event.respondWith(networkFirstWithCache(request, API_CACHE));
    return;
  }

  // Images — cache-first
  if (/\.(png|jpg|jpeg|gif|webp|svg|ico)$/i.test(url.pathname)) {
    event.respondWith(cacheFirstWithNetwork(request, IMAGE_CACHE));
    return;
  }

  // Fonts (Google Fonts CDN) — cache-first
  if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
    event.respondWith(cacheFirstWithNetwork(request, SHELL_CACHE));
    return;
  }

  // App shell (HTML, CSS, JS) — cache-first, network fallback
  event.respondWith(cacheFirstWithNetwork(request, SHELL_CACHE));
});

// ── Strategy helpers ─────────────────────────────────────────────────────────

/**
 * Cache-first: return cached response if available, otherwise fetch & cache.
 */
async function cacheFirstWithNetwork(request, cacheName) {
  const cache    = await caches.open(cacheName);
  const cached   = await cache.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    // Only cache valid responses
    if (response && response.status === 200 && response.type !== 'opaque') {
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    // Offline fallback — return the cached SPA shell for navigation requests
    if (request.mode === 'navigate') {
      const shell = await cache.match(BASE + '/');
      if (shell) return shell;
    }
    return new Response('Offline — please check your connection.', {
      status: 503,
      headers: { 'Content-Type': 'text/plain' },
    });
  }
}

/**
 * Network-first: try the network, fall back to cache.
 * Used for API calls so data is always fresh when online.
 */
async function networkFirstWithCache(request, cacheName) {
  const cache = await caches.open(cacheName);
  try {
    const response = await fetch(request);
    if (response && response.status === 200) {
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await cache.match(request);
    if (cached) return cached;
    return new Response(JSON.stringify({ status: 'error', message: 'You are offline.' }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}
