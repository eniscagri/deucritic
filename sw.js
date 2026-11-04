```js
const CACHE_VERSION = '15';

const STATIC_CACHE = `deucritic-static-v${CACHE_VERSION}`;
const DYNAMIC_CACHE = `deucritic-dynamic-v${CACHE_VERSION}`;
const API_CACHE = `deucritic-api-v${CACHE_VERSION}`;
const IMAGE_CACHE = `deucritic-images-v${CACHE_VERSION}`;
const FONT_CACHE = `deucritic-fonts-v${CACHE_VERSION}`;
const VIDEO_CACHE = `deucritic-videos-v${CACHE_VERSION}`;

const OFFLINE_IMAGE = './uploads/kapalinet.png';

const STATIC_ASSETS = [

    './',
    './index.php',
    './dm.php',
    './manifest.json',

    './logo-192.png',
    './logo-512.png',

    './uploads/kapalinet.png',

    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'

];



self.addEventListener('install', event => {

    event.waitUntil(

        caches.open(STATIC_CACHE)

            .then(cache => cache.addAll(STATIC_ASSETS))

    );

    self.skipWaiting();

});



self.addEventListener('activate', event => {

    event.waitUntil(

        caches.keys().then(keys => {

            return Promise.all(

                keys.map(key => {

                    if (

                        key !== STATIC_CACHE &&
                        key !== DYNAMIC_CACHE &&
                        key !== API_CACHE &&
                        key !== IMAGE_CACHE &&
                        key !== FONT_CACHE &&
                        key !== VIDEO_CACHE

                    ) {

                        return caches.delete(key);

                    }

                })

            );

        })

    );

    self.clients.claim();

});



self.addEventListener('fetch', event => {

    const request = event.request;

    const url = new URL(request.url);



    if (request.method !== 'GET') return;



    if (request.mode === 'navigate') {

        event.respondWith(

            fetch(request)

                .then(response => {

                    const cloned = response.clone();

                    caches.open(DYNAMIC_CACHE)

                        .then(cache => {

                            cache.put(request, cloned);

                        });

                    return response;

                })

                .catch(async () => {

                    const cached = await caches.match(request);

                    return cached || caches.match('./index.php');

                })

        );

        return;

    }



    if (

        request.destination === 'script' ||
        request.destination === 'style'

    ) {

        event.respondWith(

            caches.match(request)

                .then(async cached => {

                    const fetchPromise = fetch(request)

                        .then(response => {

                            caches.open(DYNAMIC_CACHE)

                                .then(cache => {

                                    cache.put(request, response.clone());

                                });

                            return response;

                        });

                    return cached || fetchPromise;

                })

        );

        return;

    }



    if (request.destination === 'font') {

        event.respondWith(

            caches.open(FONT_CACHE)

                .then(async cache => {

                    const cached = await cache.match(request);

                    if (cached) return cached;

                    try {

                        const response = await fetch(request);

                        cache.put(request, response.clone());

                        return response;

                    } catch {

                        return Response.error();

                    }

                })

        );

        return;

    }



    if (request.destination === 'image') {

        event.respondWith(

            caches.open(IMAGE_CACHE)

                .then(async cache => {

                    const cached = await cache.match(request);

                    if (cached) return cached;

                    try {

                        const response = await fetch(request);

                        cache.put(request, response.clone());

                        trimCache(IMAGE_CACHE, 200);

                        return response;

                    } catch {

                        return caches.match(OFFLINE_IMAGE);

                    }

                })

        );

        return;

    }



    if (request.destination === 'video') {

        event.respondWith(

            caches.open(VIDEO_CACHE)

                .then(async cache => {

                    const cached = await cache.match(request);

                    if (cached) return cached;

                    try {

                        const response = await fetch(request);

                        cache.put(request, response.clone());

                        trimCache(VIDEO_CACHE, 20);

                        return response;

                    } catch {

                        return Response.error();

                    }

                })

        );

        return;

    }



    if (

        request.url.includes('/api/') ||
        request.url.includes('.php')

    ) {

        event.respondWith(

            fetch(request)

                .then(response => {

                    const cloned = response.clone();

                    caches.open(API_CACHE)

                        .then(cache => {

                            cache.put(request, cloned);

                            trimCache(API_CACHE, 150);

                        });

                    return response;

                })

                .catch(async () => {

                    const cached = await caches.match(request);

                    return cached || caches.match(OFFLINE_IMAGE);

                })

        );

        return;

    }



    event.respondWith(

        caches.match(request)

            .then(async cached => {

                if (cached) return cached;

                try {

                    const response = await fetch(request);

                    const cloned = response.clone();

                    caches.open(DYNAMIC_CACHE)

                        .then(cache => {

                            cache.put(request, cloned);

                            trimCache(DYNAMIC_CACHE, 150);

                        });

                    return response;

                } catch {

                    return caches.match(OFFLINE_IMAGE);

                }

            })

    );

});



async function trimCache(cacheName, maxItems) {

    const cache = await caches.open(cacheName);

    const keys = await cache.keys();

    if (keys.length > maxItems) {

        cache.delete(keys[0]);

        trimCache(cacheName, maxItems);

    }

}



self.addEventListener('message', event => {

    if (event.data === 'SKIP_WAITING') {

        self.skipWaiting();

    }



    if (event.data === 'CLEAR_ALL_CACHE') {

        caches.keys().then(keys => {

            keys.forEach(key => caches.delete(key));

        });

    }



    if (event.data === 'FORCE_UPDATE') {

        updateContent();

    }

});



self.addEventListener('push', event => {

    let data = {};

    if (event.data) {

        data = event.data.json();

    }

    event.waitUntil(

        self.registration.showNotification(

            data.title || 'Deucritic',

            {

                body: data.body || 'Yeni bildirim',

                icon: './logo-192.png',

                badge: './logo-192.png',

                image: data.image || null,

                vibrate: [200, 100, 200],

                tag: data.tag || 'deucritic-notification',

                renotify: true,

                silent: false,

                requireInteraction: false,

                data: {

                    url: data.url || './'

                },

                actions: [

                    {

                        action: 'open',

                        title: 'Aç'

                    },

                    {

                        action: 'close',

                        title: 'Kapat'

                    }

                ]

            }

        )

    );

});



self.addEventListener('notificationclick', event => {

    event.notification.close();

    if (event.action === 'close') return;

    event.waitUntil(

        clients.matchAll({

            type: 'window',

            includeUncontrolled: true

        })

            .then(windowClients => {

                for (const client of windowClients) {

                    if (

                        client.url === event.notification.data.url &&
                        'focus' in client

                    ) {

                        return client.focus();

                    }

                }

                return clients.openWindow(

                    event.notification.data.url

                );

            })

    );

});



self.addEventListener('sync', event => {

    if (event.tag === 'sync-posts') {

        event.waitUntil(syncPosts());

    }



    if (event.tag === 'sync-likes') {

        event.waitUntil(syncLikes());

    }



    if (event.tag === 'sync-messages') {

        event.waitUntil(syncMessages());

    }

});



async function syncPosts() {

    try {

        console.log('posts synced');

    } catch (e) {

        console.log(e);

    }

}



async function syncLikes() {

    try {

        console.log('likes synced');

    } catch (e) {

        console.log(e);

    }

}



async function syncMessages() {

    try {

        console.log('messages synced');

    } catch (e) {

        console.log(e);

    }

}



self.addEventListener('periodicsync', event => {

    if (event.tag === 'update-feed') {

        event.waitUntil(updateContent());

    }

});



async function updateContent() {

    try {

        const cache = await caches.open(API_CACHE);

        const response = await fetch('./index.php');

        cache.put('./index.php', response);

    } catch (e) {

        console.log(e);

    }

}
```
