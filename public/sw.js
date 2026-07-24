const CACHE_NAME = 'fafima-cache-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/icon_fafima_small.png',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache).catch(err => console.log('Asset caching error', err));
            })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('message', event => {
    if (event.data && (event.data.type === 'SKIP_WAITING' || event.data === 'skipWaiting')) {
        self.skipWaiting();
    }
});

self.addEventListener('fetch', event => {
    // Network-first strategy for HTML/dynamic content, fallback to cache
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).then(response => {
            return caches.open(CACHE_NAME).then(cache => {
                if (response.status === 200) {
                    cache.put(event.request, response.clone());
                }
                return response;
            });
        }).catch(() => {
            return caches.match(event.request);
        })
    );
});

self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/icons/icon-192x192.png',
            actions: msg.actions || [],
            data: msg.data || {}
        }));
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
