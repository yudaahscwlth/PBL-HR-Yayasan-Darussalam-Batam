// Service Worker untuk HR Darussalam PWA - Next.js Optimized
const STATIC_CACHE = "hr-darussalam-static-v20";
const DYNAMIC_CACHE = "hr-darussalam-dynamic-v20";
const OFFLINE_CACHE = "hr-darussalam-offline-v20";

// Essential URLs to cache immediately (static assets only, NOT HTML pages)
// Note: Do NOT cache "/" as Next.js pages need proper hydration
const STATIC_ASSETS = [
  "/offline.html",
  "/manifest.json",
  "/favicon.ico",
  "/icons/logo-original.png",
  "/icons/icon-192x192.png",
  "/icons/icon-512x512.png",
];

// Next.js specific assets patterns
const NEXTJS_ASSETS = [
  '/_next/static/css/',
  '/_next/static/chunks/',
  '/_next/static/media/',
  '/_next/static/js/',
];

// Install event - cache static assets including offline page
self.addEventListener("install", (event) => {
  console.log("[SW] Install event triggered");

  event.waitUntil(
    caches.open(STATIC_CACHE).then(async (cache) => {
      console.log("[SW] Caching static assets");
      // Cache each asset individually to handle failures gracefully
      for (const url of STATIC_ASSETS) {
        try {
          await cache.add(url);
          console.log(`[SW] Cached: ${url}`);
        } catch (error) {
          console.warn(`[SW] Failed to cache ${url}:`, error);
        }
      }
    }).then(() => {
      console.log("[SW] All static assets cached");
      return self.skipWaiting();
    })
  );
});

// Activate event - clean up old caches
self.addEventListener("activate", (event) => {
  console.log("[SW] Activate event triggered");

  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== STATIC_CACHE &&
            cacheName !== DYNAMIC_CACHE &&
            cacheName !== OFFLINE_CACHE) {
            console.log("[SW] Deleting old cache:", cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log("[SW] Claiming clients");
      return self.clients.claim();
    })
  );
});

// Helper function to determine cache strategy
function getCacheStrategy(request) {
  const url = new URL(request.url);
  const pathname = url.pathname;

  // Skip non-GET requests and non-http protocols
  if (request.method !== "GET" || !url.protocol.startsWith("http")) {
    return "network-only";
  }

  // Network-first for navigation and API calls
  if (
    request.mode === "navigate" ||
    request.destination === "document" ||
    pathname.startsWith("/api/") ||
    pathname.includes("/_next/data/") ||
    pathname === "/pwa/offline"
  ) {
    return "network-first";
  }

  // Cache-first for static assets (CSS, JS, Images, Fonts)
  if (
    request.destination === "style" ||
    request.destination === "script" ||
    request.destination === "image" ||
    request.destination === "font" ||
    pathname.includes('/_next/static/') ||
    pathname.includes('/styles/') ||
    pathname.includes('/fonts/') ||
    pathname.endsWith(".css") ||
    pathname.endsWith(".js") ||
    pathname.endsWith(".png") ||
    pathname.endsWith(".jpg") ||
    pathname.endsWith(".jpeg") ||
    pathname.endsWith(".svg") ||
    pathname.endsWith(".ico") ||
    pathname.endsWith(".woff") ||
    pathname.endsWith(".woff2") ||
    pathname.endsWith(".ttf") ||
    pathname.endsWith(".eot")
  ) {
    return "cache-first";
  }

  // Stale-while-revalidate for other dynamic content
  return "stale-while-revalidate";
}

// Network-first strategy with timeout
async function networkFirst(request) {
  const TIMEOUT = 8000; // 8 second timeout

  try {
    // Create timeout promise
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('Request timeout')), TIMEOUT);
    });

    // Race between fetch and timeout
    const networkResponse = await Promise.race([
      fetch(request),
      timeoutPromise
    ]);

    // Clone response for cache
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone()).catch(err => {
        console.warn("[SW] Failed to cache response:", err);
      });
    }

    return networkResponse;
  } catch (error) {
    console.log("[SW] Network failed, trying cache:", error.message);

    // Try to get from cache
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
      console.log("[SW] Serving from cache:", request.url);
      return cachedResponse;
    }

    // Special handling for navigation requests - serve inline offline page
    if (request.mode === "navigate" || request.destination === "document") {
      console.log("[SW] Navigation request offline, serving inline offline page");
      return serveOfflinePage();
    }

    // For API requests, return proper error
    if (request.url.includes('/api/')) {
      return new Response(
        JSON.stringify({
          error: "Offline",
          message: "Tidak dapat terhubung ke server"
        }),
        {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        }
      );
    }

    return new Response("Offline - No cached data available", {
      status: 503,
      statusText: "Service Unavailable",
      headers: { 'Content-Type': 'text/plain' }
    });
  }
}

// Cache-first strategy
async function cacheFirst(request) {
  // Try cache first
  const cachedResponse = await caches.match(request);

  if (cachedResponse) {
    console.log("[SW] Cache hit:", request.url);
    return cachedResponse;
  }

  // If not in cache, try network
  try {
    const networkResponse = await fetch(request);

    if (networkResponse && networkResponse.status === 200) {
      // Cache the new response
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone()).catch(err => {
        console.warn("[SW] Failed to cache static asset:", err);
      });
    }

    return networkResponse;
  } catch (error) {
    console.log("[SW] Network failed for cache-first request:", request.url);

    // Return appropriate fallbacks based on request type
    if (request.destination === "image") {
      return getPlaceholderImage();
    }

    if (request.destination === "style") {
      return getEmptyCSS();
    }

    if (request.destination === "script") {
      return getEmptyJS();
    }

    // For fonts, return empty response to avoid console errors
    if (request.destination === "font") {
      return new Response('', {
        status: 404,
        statusText: 'Font not available offline'
      });
    }

    throw error;
  }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
  const cachedResponse = await caches.match(request);

  // Always try to update cache in background
  const fetchPromise = fetch(request).then(async (networkResponse) => {
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone()).catch(err => {
        console.warn("[SW] Failed to update cache:", err);
      });
    }
    return networkResponse;
  }).catch(err => {
    console.warn("[SW] Background update failed:", err);
  });

  // Return cached response immediately, or wait for network
  return cachedResponse || fetchPromise;
}

// Offline page response - tries cache first, then simple fallback
async function serveOfflinePage() {
  try {
    // Try to get offline.html from cache
    const cachedOffline = await caches.match('/offline.html');
    if (cachedOffline) {
      console.log("[SW] Serving offline.html from cache");
      return cachedOffline;
    }

    // Try to fetch offline.html (might work on slow connections)
    try {
      const fetchedOffline = await fetch('/offline.html');
      if (fetchedOffline && fetchedOffline.ok) {
        return fetchedOffline;
      }
    } catch (e) {
      console.log("[SW] Cannot fetch offline.html");
    }

    // Simple fallback if offline.html is not available
    console.log("[SW] Serving simple fallback offline page");
    return new Response(
      `<!DOCTYPE html>
      <html lang="id">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HR Darussalam - Offline</title>
        <style>
          * { margin: 0; padding: 0; box-sizing: border-box; }
          body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
          }
          .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
          }
          .icon { font-size: 64px; margin-bottom: 20px; }
          h1 { color: #1e40af; font-size: 24px; margin-bottom: 10px; }
          p { color: #6b7280; font-size: 14px; margin-bottom: 20px; }
          button {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
          }
          button:hover { opacity: 0.9; }
        </style>
      </head>
      <body>
        <div class="card">
          <div class="icon">📶</div>
          <h1>Mode Offline</h1>
          <p>Anda sedang tidak terhubung ke internet.<br>Periksa koneksi dan coba lagi.</p>
          <button onclick="window.location.reload()">🔄 Coba Lagi</button>
        </div>
        <script>
          window.addEventListener('online', function() {
            window.location.reload();
          });
        </script>
      </body>
      </html>`,
      {
        status: 200,
        statusText: "OK",
        headers: {
          'Content-Type': 'text/html; charset=utf-8',
          'Cache-Control': 'no-cache'
        }
      }
    );
  } catch (error) {
    console.error("[SW] Failed to serve offline page:", error);
    return new Response("Anda sedang offline. Silakan cek koneksi internet Anda.", {
      status: 200,
      headers: { 'Content-Type': 'text/plain; charset=utf-8' }
    });
  }
}

// Placeholder responses for different asset types
function getPlaceholderImage() {
  return new Response(
    `<svg width="200" height="150" xmlns="http://www.w3.org/2000/svg">
      <rect width="100%" height="100%" fill="#f3f4f6"/>
      <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#6b7280" font-family="sans-serif" font-size="14">
        Image Not Available
      </text>
    </svg>`,
    {
      headers: {
        "Content-Type": "image/svg+xml",
        "Cache-Control": "public, max-age=86400"
      }
    }
  );
}

function getEmptyCSS() {
  return new Response(
    "/* CSS not available offline */",
    {
      headers: {
        "Content-Type": "text/css",
        "Cache-Control": "public, max-age=300"
      }
    }
  );
}

function getEmptyJS() {
  return new Response(
    "// JS not available offline",
    {
      headers: {
        "Content-Type": "application/javascript",
        "Cache-Control": "public, max-age=300"
      }
    }
  );
}

// Fetch event - implement caching strategies
self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== "GET") {
    return;
  }

  // Skip external requests (except for common CDNs)
  if (url.origin !== self.location.origin) {
    const allowedCDNs = [
      'cdnjs.cloudflare.com',
      'fonts.googleapis.com',
      'fonts.gstatic.com',
      'unpkg.com'
    ];

    if (!allowedCDNs.some(cdn => url.hostname.includes(cdn))) {
      return;
    }
  }

  // Skip browser extensions and other protocols
  if (url.protocol === 'chrome-extension:' || url.protocol === 'chrome:') {
    return;
  }

  const strategy = getCacheStrategy(request);

  console.log(`[SW] ${strategy}: ${request.url}`);

  event.respondWith(
    (async () => {
      try {
        switch (strategy) {
          case "network-first":
            return await networkFirst(request);
          case "cache-first":
            return await cacheFirst(request);
          case "stale-while-revalidate":
            return await staleWhileRevalidate(request);
          case "network-only":
          default:
            return await fetch(request);
        }
      } catch (error) {
        console.error("[SW] Fetch strategy failed:", error);

        // Final fallback for navigation requests
        if (request.mode === "navigate" || request.destination === "document") {
          return serveOfflinePage();
        }

        return new Response(
          JSON.stringify({
            error: "Service unavailable",
            message: "Tidak dapat memuat resource yang diminta"
          }),
          {
            status: 503,
            statusText: "Service Unavailable",
            headers: { 'Content-Type': 'application/json' }
          }
        );
      }
    })()
  );
});

// Background sync event
self.addEventListener("sync", (event) => {
  console.log("[SW] Background sync event:", event.tag);

  if (event.tag === "sync-data") {
    event.waitUntil(syncOfflineData());
  }
});

// Sync offline data
async function syncOfflineData() {
  try {
    console.log("[SW] Starting background sync...");

    // Notify clients about sync start
    const allClients = await self.clients.matchAll();
    allClients.forEach(client => {
      client.postMessage({
        type: "SYNC_STATUS",
        status: "syncing",
        timestamp: new Date().toISOString()
      });
    });

    // Here you would implement actual sync logic with your API
    // For now, simulate sync process
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Notify completion
    allClients.forEach(client => {
      client.postMessage({
        type: "SYNC_STATUS",
        status: "completed",
        timestamp: new Date().toISOString()
      });
    });

    console.log("[SW] Background sync completed");
  } catch (error) {
    console.error("[SW] Background sync failed:", error);

    // Notify error
    const allClients = await self.clients.matchAll();
    allClients.forEach(client => {
      client.postMessage({
        type: "SYNC_STATUS",
        status: "failed",
        error: error.message,
        timestamp: new Date().toISOString()
      });
    });
  }
}

// Push notification event
self.addEventListener("push", (event) => {
  const data = event.data ? event.data.json() : {};
  const title = data.title || "HR Darussalam";
  const options = {
    body: data.body || "Notifikasi baru dari HR Darussalam",
    icon: "/icons/icon-192x192.png",
    badge: "/icons/icon-96x96.png",
    image: data.image,
    vibrate: [200, 100, 200],
    data: {
      url: data.url || "/",
      timestamp: new Date().toISOString()
    },
    requireInteraction: true,
    actions: [
      {
        action: "open",
        title: "Buka Aplikasi"
      },
      {
        action: "dismiss",
        title: "Tutup"
      }
    ]
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

// Notification click event
self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  if (event.action === "dismiss") {
    return;
  }

  const urlToOpen = event.notification.data.url || "/";

  event.waitUntil(
    clients.matchAll({ type: "window" }).then((clientList) => {
      // If a window is already open, focus it
      for (const client of clientList) {
        if (client.url.includes(urlToOpen) && "focus" in client) {
          return client.focus();
        }
      }

      // Otherwise, open a new window
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

// Handle messages from clients
self.addEventListener("message", (event) => {
  const { data } = event;

  if (data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }

  if (data.type === "GET_VERSION") {
    event.ports[0]?.postMessage({
      type: "VERSION",
      version: "4.0.0",
      cacheVersion: STATIC_CACHE
    });
  }

  if (data.type === "CACHE_URLS") {
    // Cache additional URLs on demand
    const urlsToCache = data.urls || [];
    event.waitUntil(
      caches.open(STATIC_CACHE).then(cache => {
        return cache.addAll(urlsToCache);
      })
    );
  }
});

// Periodic background sync (if supported)
if ("periodicSync" in self.registration) {
  self.addEventListener("periodicsync", (event) => {
    if (event.tag === "sync-data-periodic") {
      console.log("[SW] Periodic sync triggered");
      event.waitUntil(syncOfflineData());
    }
  });
}

// Error handling for unhandled rejections
self.addEventListener("unhandledrejection", (event) => {
  console.error("[SW] Unhandled rejection:", event.reason);
});

self.addEventListener("error", (event) => {
  console.error("[SW] Error:", event.error);
});

console.log("[SW] Service Worker loaded successfully - Next.js Optimized");
