const CACHE_NAME = 'fafima-cache-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/build/assets/app-C1D4qvwq.css', // This will change on build, but this is a simple SW example
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache).catch(err => console.log('Asset caching error', err));
            })
    );
});

self.addEventListener('fetch', event => {
    // Basic network-first strategy for dynamic content, cache-first for static assets
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).then(response => {
            return caches.open(CACHE_NAME).then(cache => {
                // Cache valid responses
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
            icon: msg.icon || '/icon-192x192.png',
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
