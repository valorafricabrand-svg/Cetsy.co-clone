/* Basic PWA service worker for offline support + web push. */
const CACHE_NAME = "cetsy-pwa-v3";
const OFFLINE_URL = "/offline.html";

self.addEventListener("install", (event) => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      await cache.addAll([
        OFFLINE_URL
      ]);
      self.skipWaiting();
    })()
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    (async () => {
      const names = await caches.keys();
      await Promise.all(
        names.filter((n) => n !== CACHE_NAME).map((n) => caches.delete(n))
      );
      await self.clients.claim();
    })()
  );
});

self.addEventListener("push", (event) => {
  event.waitUntil(handlePushEvent(event));
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  event.waitUntil(handleNotificationClick(event));
});

self.addEventListener("pushsubscriptionchange", (event) => {
  event.waitUntil(
    (async () => {
      const clientsList = await self.clients.matchAll({
        type: "window",
        includeUncontrolled: true,
      });

      await Promise.all(
        clientsList.map((client) =>
          client.postMessage({
            type: "cetsy-push-subscription-change",
          })
        )
      );
    })()
  );
});

self.addEventListener("fetch", (event) => {
  const { request } = event;
  const requestUrl = new URL(request.url);

  if (
    requestUrl.origin === self.location.origin &&
    requestUrl.pathname.startsWith("/downloads/")
  ) {
    return;
  }

  if (request.mode === "navigate") {
    event.respondWith(
      (async () => {
        try {
          const fresh = await fetch(request);
          return fresh;
        } catch (err) {
          const cache = await caches.open(CACHE_NAME);
          const cached = await cache.match(OFFLINE_URL);
          return cached || new Response("You are offline.", { status: 503, headers: { "Content-Type": "text/plain" } });
        }
      })()
    );
    return;
  }

  if (["style", "script", "image", "font"].includes(request.destination)) {
    event.respondWith(
      (async () => {
        try {
          const response = await fetch(request);
          const cache = await caches.open(CACHE_NAME);
          cache.put(request, response.clone());
          return response;
        } catch (err) {
          const cached = await caches.match(request);
          if (cached) return cached;
          throw err;
        }
      })()
    );
  }
});

async function handlePushEvent(event) {
  const payload = parsePushPayload(event);
  const clientsList = await self.clients.matchAll({
    type: "window",
    includeUncontrolled: true,
  });

  await Promise.all(
    clientsList.map((client) =>
      client.postMessage({
        type: "cetsy-push-event",
        payload,
      })
    )
  );

  const hasVisibleClient = clientsList.some((client) => client.visibilityState === "visible");
  if (hasVisibleClient) {
    return;
  }

  return self.registration.showNotification(payload.title, {
    body: payload.body,
    icon: payload.icon,
    badge: payload.badge,
    tag: payload.tag,
    renotify: true,
    data: {
      url: payload.url,
      payload,
    },
  });
}

async function handleNotificationClick(event) {
  const targetUrl = event.notification.data?.url || "/";
  const clientsList = await self.clients.matchAll({
    type: "window",
    includeUncontrolled: true,
  });

  for (const client of clientsList) {
    if ("focus" in client) {
      await client.focus();
    }

    if ("navigate" in client) {
      await client.navigate(targetUrl);
    }

    return;
  }

  if (self.clients.openWindow) {
    await self.clients.openWindow(targetUrl);
  }
}

function parsePushPayload(event) {
  try {
    const data = event.data?.json();
    if (data && typeof data === "object") {
      return normalizePushPayload(data);
    }
  } catch (error) {
    // Fall through to text payload handling.
  }

  const text = event.data?.text?.() || "";
  return normalizePushPayload({
    title: "Cetsy",
    body: text || "You have a new notification.",
  });
}

function normalizePushPayload(payload) {
  return {
    ...payload,
    title: payload?.title || "Cetsy",
    body: payload?.body || "You have a new notification.",
    url: payload?.url || "/notifications",
    tag: payload?.tag || "cetsy-push",
    icon: payload?.icon || "/assets/images/cetsylogmain.png",
    badge: payload?.badge || "/favicon.ico",
  };
}
