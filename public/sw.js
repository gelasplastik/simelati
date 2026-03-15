const SW_VERSION = 'simelati-v3';
const STATIC_CACHE = `${SW_VERSION}-static`;
const ASSET_CACHE = `${SW_VERSION}-assets`;
const PAGE_CACHE = `${SW_VERSION}-pages`;

const CORE_FILES = [
    '/manifest.json',
    '/offline.html',
    '/assets/logo/simelati-logo.png',
    '/assets/pwa/icon-192.png',
    '/assets/pwa/icon-512.png',
    '/assets/pwa/icon-512-maskable.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(CORE_FILES))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => ![STATIC_CACHE, ASSET_CACHE, PAGE_CACHE].includes(key))
                .map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstPage(request));
        return;
    }

    if (isStaticAsset(request, url)) {
        event.respondWith(staleWhileRevalidateAsset(request));
    }
});

async function networkFirstPage(request) {
    const cache = await caches.open(PAGE_CACHE);
    const requestUrl = new URL(request.url);
    const shouldCachePage = isCacheablePage(requestUrl.pathname);

    try {
        const response = await fetch(request);
        if (shouldCachePage && response && response.status === 200) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        return caches.match('/offline.html');
    }
}

async function staleWhileRevalidateAsset(request) {
    const cache = await caches.open(ASSET_CACHE);
    const cached = await cache.match(request);
    const networkFetch = fetch(request)
        .then((response) => {
            if (response && response.status === 200) {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => null);

    return cached || networkFetch || Response.error();
}

function isStaticAsset(request, url) {
    return (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/assets/') ||
        url.pathname === '/manifest.json' ||
        url.pathname === '/favicon.ico' ||
        ['style', 'script', 'image', 'font'].includes(request.destination)
    );
}

function isCacheablePage(pathname) {
    return [
        '/',
        '/login',
        '/dashboard',
        '/admin/dashboard',
        '/parent/dashboard',
        '/izin-siswa',
        '/offline.html',
    ].includes(pathname);
}
