/**
 * OrderTrack Service Worker
 * Static assets: Cache First | API/data: Network First
 */
const VERSION = 'v3';
const STATIC_CACHE = `ordertrack-static-${VERSION}`;
const DATA_CACHE = `ordertrack-data-${VERSION}`;
const OFFLINE_URL = './offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    './css/app.css',
    './css/auth.css',
    './css/layout.css',
    './css/guest.css',
    './js/app.js',
    './js/customer-order.js',
    './js/shopper-status.js',
    './js/shopper-ai.js',
    './manifest.json',
    './icons/icon-192.png',
    './icons/icon-512.png',
];

function isStaticAsset(url) {
    const path = url.pathname;

    if (path.endsWith('/manifest.json')) {
        return true;
    }

    return /\.(css|js|png|jpg|jpeg|gif|svg|ico|webp|woff2?)$/i.test(path);
}

function isDataRequest(request, url) {
    if (request.method !== 'GET') {
        return true;
    }

    const accept = request.headers.get('Accept') || '';

    if (accept.includes('application/json')) {
        return true;
    }

    if (request.mode === 'navigate') {
        return true;
    }

    if (url.pathname.includes('/shopper/orders/') && url.pathname.endsWith('/status')) {
        return true;
    }

    return !isStaticAsset(url);
}

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        return caches.match(OFFLINE_URL);
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache = await caches.open(DATA_CACHE);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request);

        if (cached) {
            return cached;
        }

        if (request.mode === 'navigate') {
            const offline = await caches.match(OFFLINE_URL);

            if (offline) {
                return offline;
            }
        }

        return new Response(
            JSON.stringify({
                success: false,
                message: "You're offline, showing cached data.",
                offline: true,
            }),
            {
                status: 503,
                headers: { 'Content-Type': 'application/json' },
            }
        );
    }
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== STATIC_CACHE && key !== DATA_CACHE)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' && request.method !== 'POST') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.method === 'POST') {
        event.respondWith(
            fetch(request).catch(() =>
                new Response(
                    JSON.stringify({
                        success: false,
                        message: "You're offline. Please reconnect to update data.",
                        offline: true,
                    }),
                    { status: 503, headers: { 'Content-Type': 'application/json' } }
                )
            )
        );
        return;
    }

    if (isDataRequest(request, url)) {
        event.respondWith(networkFirst(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
    }
});
