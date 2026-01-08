/**
 * Service Worker for Family Gallery PWA
 * Provides offline support and caching
 */

const CACHE_NAME = 'family-gallery-v1';
const API_CACHE = 'family-gallery-api-v1';
const IMAGE_CACHE = 'family-gallery-images-v1';

// Files to cache on install
const STATIC_CACHE_URLS = [
    '/',
    '/index.html',
    '/css/styles.css',
    '/js/app.js',
    '/js/auth.js',
    '/js/camera.js',
    '/js/gallery.js',
    '/js/upload.js',
    '/offline.html'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Install');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[ServiceWorker] Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activate');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && 
                        cacheName !== API_CACHE && 
                        cacheName !== IMAGE_CACHE) {
                        console.log('[ServiceWorker] Removing old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // API requests - network first, then cache
    if (url.pathname.includes('/wp-json/family-gallery/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone the response
                    const responseClone = response.clone();
                    
                    // Cache successful GET requests
                    if (request.method === 'GET' && response.status === 200) {
                        caches.open(API_CACHE).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    
                    return response;
                })
                .catch(() => {
                    // Network failed, try cache
                    return caches.match(request);
                })
        );
        return;
    }

    // Image requests - cache first, then network
    if (request.destination === 'image') {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                return fetch(request).then((response) => {
                    // Cache images for offline viewing
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(IMAGE_CACHE).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    
                    return response;
                });
            })
        );
        return;
    }

    // Static assets - cache first, then network
    event.respondWith(
        caches.match(request)
            .then((response) => {
                if (response) {
                    return response;
                }
                
                return fetch(request).then((response) => {
                    // Don't cache if not successful
                    if (!response || response.status !== 200 || response.type === 'error') {
                        return response;
                    }
                    
                    const responseClone = response.clone();
                    
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    
                    return response;
                });
            })
            .catch(() => {
                // Show offline page for navigation requests
                if (request.mode === 'navigate') {
                    return caches.match('/offline.html');
                }
            })
    );
});

// Background sync for uploads (when back online)
self.addEventListener('sync', (event) => {
    if (event.tag === 'upload-photos') {
        event.waitUntil(uploadPendingPhotos());
    }
});

// Function to upload pending photos when back online
async function uploadPendingPhotos() {
    // Get pending uploads from IndexedDB
    // This would be implemented based on your upload queue logic
    console.log('[ServiceWorker] Uploading pending photos...');
}

// Push notification support (for future use)
self.addEventListener('push', (event) => {
    const data = event.data.json();
    
    const options = {
        body: data.body,
        icon: '/images/icons/icon-192.png',
        badge: '/images/icons/icon-72.png',
        vibrate: [200, 100, 200]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});
