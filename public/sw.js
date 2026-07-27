const CACHE_NAME = 'mobilepos-shell-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE_URLS = ['/manifest.json', '/icons/icon-192.png', '/icons/icon-512.png', OFFLINE_URL];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Page navigations: always prefer fresh data (prices/stock change), fall back
    // to a static offline notice only when the network is unreachable.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Built assets, icons, and the manifest are safe to cache-first since Vite
    // fingerprints filenames on every build.
    const isStaticAsset = url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/')
        || url.pathname === '/manifest.json';

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                return response;
            })),
        );
    }
});
