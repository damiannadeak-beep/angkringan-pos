const CACHE_NAME = 'angkringan-pos-v3';
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/logo-angkringan.png',
  '/js/pwa-offline.js'
];

// Install Event: Cache core static assets
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return Promise.all(
        STATIC_ASSETS.map((url) => {
          return cache.add(url).catch((err) => {
            console.warn(`PWA pre-cache warning for ${url}:`, err);
          });
        })
      );
    })
  );
});

// Activate Event: Claim clients immediately & delete old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event: Cache-First for static assets & Stale-While-Revalidate / Cache-Fallback for HTML pages
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  // Handle HTML navigation requests
  if (event.request.mode === 'navigate' || (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });
          }
          return networkResponse;
        })
        .catch(async () => {
          // Return any cached HTML page match when offline
          const cachedMatch = await caches.match(event.request, { ignoreSearch: true });
          if (cachedMatch) {
            return cachedMatch;
          }
          // Fallback to any cached HTML in cache
          const cache = await caches.open(CACHE_NAME);
          const keys = await cache.keys();
          for (const req of keys) {
            if (req.url.includes('/kasir/pos')) {
              return cache.match(req);
            }
          }
          return caches.match('/');
        })
    );
    return;
  }

  // Handle static assets (CSS, JS, Images, Fonts)
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        // Fetch background update
        fetch(event.request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, networkResponse));
          }
        }).catch(() => {});
        return cachedResponse;
      }
      return fetch(event.request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
        }
        return networkResponse;
      });
    })
  );
});
