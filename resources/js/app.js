import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    initLiveNotificationPulse();
});

function initLiveNotificationPulse() {
    const pulseUrl = metaContent("cetsy-notifications-pulse-url");
    if (!pulseUrl) {
        return;
    }

    const pushConfig = {
        enabled: metaContent("cetsy-push-enabled") === "1",
        publicKey: metaContent("cetsy-push-public-key"),
        subscribeUrl: metaContent("cetsy-push-subscribe-url"),
        unsubscribeUrl: metaContent("cetsy-push-unsubscribe-url"),
    };

    injectLiveNotificationStyles();

    const state = {
        bootstrapped: false,
        pollTimer: null,
        inFlight: false,
        baseTitle: document.title,
        latestNotificationId: 0,
        latestMessageId: 0,
        audioContext: null,
        audioUnlocked: false,
        push: {
            supported: supportsWebPush(),
            checking: false,
            subscribed: false,
            subscription: null,
        },
    };

    const storageKeys = {
        prefs: "cetsy_live_alert_prefs_v1",
        handledNotification: "cetsy_last_handled_notification_id",
        handledMessage: "cetsy_last_handled_message_id",
    };

    const defaultPrefs = {
        inAppEnabled: true,
        soundEnabled: true,
        desktopEnabled: false,
        messageSoundEnabled: true,
        saleSoundEnabled: true,
        generalSoundEnabled: false,
    };

    let prefs = loadPrefs();

    ensureSettingsModal();
    bindSettingsTriggers();
    installAudioUnlock();
    bindServiceWorkerMessages();

    void syncPushState();
    window.addEventListener(
        "load",
        () => {
            void syncPushState();
        },
        { once: true }
    );

    void poll();
    state.pollTimer = window.setInterval(() => {
        void poll();
    }, 15000);

    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible") {
            void poll();
            void syncPushState();
        }
    });

    window.addEventListener("focus", () => {
        void poll();
    });

    function metaContent(name) {
        return document.querySelector(`meta[name="${name}"]`)?.content?.trim() || "";
    }

    function loadPrefs() {
        try {
            const stored = window.localStorage.getItem(storageKeys.prefs);
            if (!stored) {
                return { ...defaultPrefs };
            }

            return { ...defaultPrefs, ...JSON.parse(stored) };
        } catch (_) {
            return { ...defaultPrefs };
        }
    }

    function savePrefs(nextPrefs) {
        prefs = { ...defaultPrefs, ...nextPrefs };
        try {
            window.localStorage.setItem(storageKeys.prefs, JSON.stringify(prefs));
        } catch (_) {
            // Ignore storage failures.
        }
        syncSettingsForm();
    }

    function readStoredId(key) {
        try {
            return Number.parseInt(window.localStorage.getItem(key) || "0", 10) || 0;
        } catch (_) {
            return 0;
        }
    }

    function writeStoredId(key, value) {
        try {
            window.localStorage.setItem(key, String(value));
        } catch (_) {
            // Ignore storage failures.
        }
    }

    async function poll() {
        if (state.inFlight) {
            return;
        }

        state.inFlight = true;

        try {
            const response = await fetch(pulseUrl, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
                cache: "no-store",
            });

            if (response.status === 401 || response.status === 403) {
                stopPolling();
                return;
            }

            if (!response.ok) {
                throw new Error(`Polling failed with status ${response.status}`);
            }

            const payload = await response.json();
            handlePayload(payload);
        } catch (error) {
            console.warn("Live notification polling failed:", error);
        } finally {
            state.inFlight = false;
        }
    }

    function stopPolling() {
        if (state.pollTimer) {
            window.clearInterval(state.pollTimer);
            state.pollTimer = null;
        }
    }

    function handlePayload(payload) {
        const unreadNotifications = Number(payload?.notif || 0);
        const unreadMessages = Number(payload?.msg || 0);
        const latestNotification = payload?.latest_notification || null;
        const latestMessage = payload?.latest_message || null;

        updateNotificationBadge(unreadNotifications);
        updateDocumentTitle(unreadNotifications, unreadMessages);

        if (!state.bootstrapped) {
            state.latestNotificationId = Number(latestNotification?.id || 0);
            state.latestMessageId = Number(latestMessage?.id || 0);
            writeStoredId(storageKeys.handledNotification, state.latestNotificationId);
            writeStoredId(storageKeys.handledMessage, state.latestMessageId);
            state.bootstrapped = true;
            return;
        }

        const storedNotificationId = readStoredId(storageKeys.handledNotification);
        const storedMessageId = readStoredId(storageKeys.handledMessage);

        const nextNotificationId = Number(latestNotification?.id || 0);
        if (nextNotificationId > Math.max(state.latestNotificationId, storedNotificationId)) {
            state.latestNotificationId = nextNotificationId;
            writeStoredId(storageKeys.handledNotification, nextNotificationId);
            if (latestNotification?.type !== "message") {
                handleNotificationAlert(latestNotification);
            }
        } else {
            state.latestNotificationId = Math.max(state.latestNotificationId, nextNotificationId);
        }

        const nextMessageId = Number(latestMessage?.id || 0);
        if (nextMessageId > Math.max(state.latestMessageId, storedMessageId)) {
            state.latestMessageId = nextMessageId;
            writeStoredId(storageKeys.handledMessage, nextMessageId);
            handleMessageAlert(latestMessage);
        } else {
            state.latestMessageId = Math.max(state.latestMessageId, nextMessageId);
        }
    }

    function handleServiceWorkerPushEvent(payload) {
        if (!payload || typeof payload !== "object") {
            return;
        }

        if (payload.kind === "activity" && payload.notification) {
            const id = Number(payload.notification.id || payload.id || 0);
            if (id > 0) {
                state.latestNotificationId = Math.max(state.latestNotificationId, id);
                writeStoredId(storageKeys.handledNotification, id);
            }

            if (document.visibilityState === "visible" && payload.notification.type !== "message") {
                handleNotificationAlert(payload.notification);
            }
        }

        if (payload.kind === "message" && payload.message) {
            const id = Number(payload.message.id || payload.id || 0);
            if (id > 0) {
                state.latestMessageId = Math.max(state.latestMessageId, id);
                writeStoredId(storageKeys.handledMessage, id);
            }

            if (document.visibilityState === "visible") {
                handleMessageAlert(payload.message);
            }
        }

        void poll();
    }

    function handleNotificationAlert(notification) {
        if (!prefs.inAppEnabled || !notification) {
            return;
        }

        const isSale = isSaleNotification(notification);
        const title = isSale ? "New sale" : notification.title || "New notification";
        const body = notification.description || notification.title || "You have a new notification.";

        showToast({
            title,
            body,
            href: notification.link || null,
            actionLabel: notification.action || (isSale ? "View order" : "Open"),
            tone: isSale ? "sale" : "general",
        });

        if (isSale && prefs.soundEnabled && prefs.saleSoundEnabled) {
            playSaleSound();
        } else if (!isSale && prefs.soundEnabled && prefs.generalSoundEnabled) {
            playGeneralSound();
        }

        maybeShowDesktopNotification(title, body, notification.link || null, isSale ? "sale" : "general");
    }

    function handleMessageAlert(message) {
        if (!prefs.inAppEnabled || !message) {
            return;
        }

        const title = `New message from ${message.sender_name || "a customer"}`;
        const body = message.body_preview || "Open your inbox to reply.";

        showToast({
            title,
            body,
            href: message.link || null,
            actionLabel: "Reply",
            tone: "message",
        });

        if (prefs.soundEnabled && prefs.messageSoundEnabled) {
            playMessageSound();
        }

        maybeShowDesktopNotification(title, body, message.link || null, "message");
    }

    function isSaleNotification(notification) {
        const haystack = `${notification?.title || ""} ${notification?.description || ""}`.toLowerCase();
        return notification?.type === "order" && haystack.includes("received a new order");
    }

    function updateNotificationBadge(count) {
        const bell = document.querySelector("[data-live-notification-bell]");
        const unreadLabel = document.querySelector("[data-live-notification-unread-label]");
        if (!bell) {
            return;
        }

        let badge = bell.querySelector("[data-live-notification-count]");
        if (count > 0) {
            if (!badge) {
                badge = document.createElement("span");
                badge.setAttribute("data-live-notification-count", "true");
                badge.className = "absolute -right-1 -top-1 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[10px] font-semibold leading-none text-white";
                bell.appendChild(badge);
            }
            badge.textContent = count > 99 ? "99+" : String(count);
        } else if (badge) {
            badge.remove();
        }

        if (unreadLabel) {
            unreadLabel.textContent = `${count} unread`;
        }
    }

    function updateDocumentTitle(notificationCount, messageCount) {
        const totalUnread = Number(notificationCount || 0) + Number(messageCount || 0);
        document.title = totalUnread > 0 ? `(${totalUnread}) ${state.baseTitle}` : state.baseTitle;
    }

    function ensureSettingsModal() {
        if (document.getElementById("liveNotificationPrefsModal")) {
            syncSettingsForm();
            return;
        }

        const modal = document.createElement("div");
        modal.className = "modal";
        modal.id = "liveNotificationPrefsModal";
        modal.tabIndex = -1;
        modal.setAttribute("aria-labelledby", "liveNotificationPrefsTitle");
        modal.setAttribute("aria-hidden", "true");
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <div>
                            <h5 id="liveNotificationPrefsTitle" class="text-base font-semibold text-slate-900">Alert settings</h5>
                            <p class="mt-1 text-xs text-slate-500">Control sounds, toasts, browser popups, and true push notifications for new messages and sales.</p>
                        </div>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="space-y-4 px-4 py-4">
                        <label class="live-alert-pref">
                            <span>
                                <span class="live-alert-pref__title">Enable in-app alerts</span>
                                <span class="live-alert-pref__desc">Show alert toasts while you are using the app.</span>
                            </span>
                            <input type="checkbox" data-live-pref="inAppEnabled">
                        </label>
                        <label class="live-alert-pref">
                            <span>
                                <span class="live-alert-pref__title">Play sounds</span>
                                <span class="live-alert-pref__desc">Master switch for message, sale, and general sounds.</span>
                            </span>
                            <input type="checkbox" data-live-pref="soundEnabled">
                        </label>
                        <label class="live-alert-pref">
                            <span>
                                <span class="live-alert-pref__title">Message sound</span>
                                <span class="live-alert-pref__desc">Play a short chime when a new unread message arrives.</span>
                            </span>
                            <input type="checkbox" data-live-pref="messageSoundEnabled">
                        </label>
                        <label class="live-alert-pref">
                            <span>
                                <span class="live-alert-pref__title">Sale sound</span>
                                <span class="live-alert-pref__desc">Play a coin sound when a seller receives a new order.</span>
                            </span>
                            <input type="checkbox" data-live-pref="saleSoundEnabled">
                        </label>
                        <label class="live-alert-pref">
                            <span>
                                <span class="live-alert-pref__title">Other notification sound</span>
                                <span class="live-alert-pref__desc">Optional tone for non-message, non-sale notifications.</span>
                            </span>
                            <input type="checkbox" data-live-pref="generalSoundEnabled">
                        </label>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Device push notifications</p>
                                    <p class="text-xs text-slate-500" data-web-push-status>Checking support...</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-300 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50" data-enable-web-push>Enable push</button>
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-disable-web-push>Disable push</button>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">This uses the service worker, so installed PWA users can still receive alerts after the tab is no longer active.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Browser popups while site is open</p>
                                    <p class="text-xs text-slate-500" data-desktop-permission-status>Permission status: default</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-request-desktop-alerts>Enable browser popups</button>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs text-amber-900">
                            Sounds require one tap or click in the browser before they can play reliably. Use the test buttons below once per session if needed.
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-sky-300 px-3 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50" data-test-live-sound="message">Test message sound</button>
                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-300 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50" data-test-live-sound="sale">Test sale sound</button>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelectorAll("[data-live-pref]").forEach((input) => {
            input.addEventListener("change", () => {
                savePrefs({
                    ...prefs,
                    [input.getAttribute("data-live-pref")]: input.checked,
                });
            });
        });

        modal.querySelector("[data-request-desktop-alerts]")?.addEventListener("click", async () => {
            if (!("Notification" in window)) {
                showToast({
                    title: "Browser popups unavailable",
                    body: "This browser does not support the Notifications API.",
                    tone: "general",
                });
                return;
            }

            const permission = await Notification.requestPermission();
            savePrefs({
                ...prefs,
                desktopEnabled: permission === "granted",
            });

            if (permission === "granted") {
                showToast({
                    title: "Browser popups enabled",
                    body: state.push.subscribed
                        ? "Page-level popups are enabled, but subscribed push will handle background alerts."
                        : "You will now get browser popups while this app is open in the background.",
                    tone: "general",
                });
            }

            syncSettingsForm();
        });

        modal.querySelector("[data-enable-web-push]")?.addEventListener("click", async () => {
            await enableWebPush();
        });

        modal.querySelector("[data-disable-web-push]")?.addEventListener("click", async () => {
            await disableWebPush();
        });

        modal.querySelectorAll("[data-test-live-sound]").forEach((button) => {
            button.addEventListener("click", async () => {
                await unlockAudio();
                if (button.getAttribute("data-test-live-sound") === "sale") {
                    playSaleSound();
                } else {
                    playMessageSound();
                }
            });
        });

        syncSettingsForm();
    }

    function bindSettingsTriggers() {
        document.querySelectorAll("[data-notify-settings-trigger]").forEach((trigger) => {
            trigger.addEventListener("click", () => {
                syncSettingsForm();
            });
        });
    }

    function syncSettingsForm() {
        const modal = document.getElementById("liveNotificationPrefsModal");
        if (!modal) {
            return;
        }

        modal.querySelectorAll("[data-live-pref]").forEach((input) => {
            const key = input.getAttribute("data-live-pref");
            input.checked = Boolean(prefs[key]);
        });

        const permission = "Notification" in window ? Notification.permission : "unsupported";
        const permissionLabel = modal.querySelector("[data-desktop-permission-status]");
        if (permissionLabel) {
            permissionLabel.textContent = `Permission status: ${permission}`;
        }

        const requestButton = modal.querySelector("[data-request-desktop-alerts]");
        if (requestButton) {
            requestButton.classList.remove("opacity-60", "cursor-not-allowed");

            if (permission === "granted" && prefs.desktopEnabled) {
                requestButton.textContent = "Browser popups enabled";
                requestButton.disabled = true;
                requestButton.classList.add("opacity-60", "cursor-not-allowed");
            } else if (permission === "granted") {
                requestButton.textContent = "Use browser popups";
                requestButton.disabled = false;
            } else if (permission === "denied") {
                requestButton.textContent = "Blocked in browser settings";
                requestButton.disabled = true;
                requestButton.classList.add("opacity-60", "cursor-not-allowed");
            } else if (permission === "unsupported") {
                requestButton.textContent = "Browser popups unavailable";
                requestButton.disabled = true;
                requestButton.classList.add("opacity-60", "cursor-not-allowed");
            } else {
                requestButton.textContent = "Enable browser popups";
                requestButton.disabled = false;
            }
        }

        const pushStatus = modal.querySelector("[data-web-push-status]");
        if (pushStatus) {
            pushStatus.textContent = getWebPushStatusText();
        }

        const enablePushButton = modal.querySelector("[data-enable-web-push]");
        if (enablePushButton) {
            enablePushButton.classList.remove("opacity-60", "cursor-not-allowed");
            enablePushButton.textContent = state.push.checking ? "Checking..." : "Enable push";
            enablePushButton.disabled = state.push.checking || !state.push.supported || !isWebPushConfigured() || state.push.subscribed || permission === "denied";
            if (enablePushButton.disabled) {
                enablePushButton.classList.add("opacity-60", "cursor-not-allowed");
            }
        }

        const disablePushButton = modal.querySelector("[data-disable-web-push]");
        if (disablePushButton) {
            disablePushButton.classList.remove("opacity-60", "cursor-not-allowed");
            disablePushButton.textContent = state.push.checking ? "Checking..." : "Disable push";
            disablePushButton.disabled = state.push.checking || !state.push.supported || !state.push.subscribed;
            if (disablePushButton.disabled) {
                disablePushButton.classList.add("opacity-60", "cursor-not-allowed");
            }
        }
    }

    function getWebPushStatusText() {
        if (!state.push.supported) {
            return "Unavailable in this browser or insecure context.";
        }

        if (!pushConfig.enabled || !pushConfig.publicKey || !pushConfig.subscribeUrl || !pushConfig.unsubscribeUrl) {
            return "Server setup is missing VAPID keys or subscription endpoints.";
        }

        if (state.push.checking) {
            return "Checking this device subscription...";
        }

        if (!("Notification" in window)) {
            return "Notifications API is unavailable in this browser.";
        }

        if (Notification.permission === "denied") {
            return "Blocked in browser settings. Re-enable notifications in your browser first.";
        }

        if (state.push.subscribed) {
            return "Enabled for this device. Push will continue through the PWA/service worker.";
        }

        if (Notification.permission === "granted") {
            return "Permission granted. Enable push to subscribe this device.";
        }

        return "Not enabled yet.";
    }

    function maybeShowDesktopNotification(title, body, href, tone) {
        if (state.push.subscribed) {
            return;
        }

        if (!prefs.desktopEnabled || !("Notification" in window)) {
            return;
        }

        if (Notification.permission !== "granted" || document.visibilityState === "visible") {
            return;
        }

        try {
            const notification = new Notification(title, {
                body,
                tag: tone === "message" ? "cetsy-live-message" : "cetsy-live-notification",
                renotify: true,
            });

            notification.onclick = () => {
                window.focus();
                if (href) {
                    window.location.assign(href);
                }
                notification.close();
            };
        } catch (_) {
            // Ignore browser notification failures.
        }
    }

    async function syncPushState() {
        if (!state.push.supported || state.push.checking) {
            syncSettingsForm();
            return;
        }

        state.push.checking = true;
        syncSettingsForm();

        try {
            const registration = await awaitServiceWorkerReady();
            const subscription = await registration.pushManager.getSubscription();
            state.push.subscription = subscription;
            state.push.subscribed = Boolean(subscription);
        } catch (_) {
            state.push.subscription = null;
            state.push.subscribed = false;
        } finally {
            state.push.checking = false;
            syncSettingsForm();
        }
    }

    async function enableWebPush() {
        if (!state.push.supported) {
            showToast({
                title: "Push unavailable",
                body: "This browser or connection does not support service-worker push notifications.",
                tone: "general",
            });
            return;
        }

        if (!isWebPushConfigured()) {
            showToast({
                title: "Push not ready",
                body: "The server still needs VAPID keys before this device can subscribe.",
                tone: "general",
            });
            return;
        }

        if (!("Notification" in window)) {
            showToast({
                title: "Push unavailable",
                body: "This browser does not support notifications.",
                tone: "general",
            });
            return;
        }

        if (Notification.permission === "denied") {
            showToast({
                title: "Notifications blocked",
                body: "Re-enable notifications in your browser settings, then try again.",
                tone: "general",
            });
            return;
        }

        state.push.checking = true;
        syncSettingsForm();

        try {
            const permission = Notification.permission === "granted"
                ? "granted"
                : await Notification.requestPermission();

            if (permission !== "granted") {
                showToast({
                    title: "Push not enabled",
                    body: "Notification permission is required before this device can subscribe.",
                    tone: "general",
                });
                return;
            }

            const registration = await awaitServiceWorkerReady();
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(pushConfig.publicKey),
                });
            }

            await persistPushSubscription(subscription);
            state.push.subscription = subscription;
            state.push.subscribed = true;

            showToast({
                title: "Push enabled",
                body: "This device will now receive push notifications for new messages and sales.",
                tone: "general",
            });
        } catch (error) {
            console.warn("Failed to enable web push:", error);
            showToast({
                title: "Push setup failed",
                body: "The browser could not subscribe this device right now.",
                tone: "general",
            });
        } finally {
            state.push.checking = false;
            syncSettingsForm();
        }
    }

    async function disableWebPush() {
        if (!state.push.supported) {
            return;
        }

        state.push.checking = true;
        syncSettingsForm();

        try {
            const registration = await awaitServiceWorkerReady();
            const subscription = state.push.subscription || await registration.pushManager.getSubscription();
            if (!subscription) {
                state.push.subscribed = false;
                state.push.subscription = null;
                return;
            }

            await Promise.allSettled([
                removePushSubscription(subscription.endpoint),
                subscription.unsubscribe(),
            ]);

            state.push.subscription = null;
            state.push.subscribed = false;

            showToast({
                title: "Push disabled",
                body: "This device has been unsubscribed from push notifications.",
                tone: "general",
            });
        } catch (error) {
            console.warn("Failed to disable web push:", error);
            showToast({
                title: "Push disable failed",
                body: "The browser could not unsubscribe this device cleanly.",
                tone: "general",
            });
        } finally {
            state.push.checking = false;
            syncSettingsForm();
        }
    }

    async function persistPushSubscription(subscription) {
        const payload = subscription.toJSON();

        await postJson(pushConfig.subscribeUrl, {
            endpoint: payload.endpoint,
            expiration_time: payload.expirationTime ?? null,
            keys: payload.keys || {},
            content_encoding: getSupportedContentEncoding(),
        });
    }

    async function removePushSubscription(endpoint) {
        if (!endpoint || !pushConfig.unsubscribeUrl) {
            return;
        }

        await postJson(pushConfig.unsubscribeUrl, { endpoint });
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
            credentials: "same-origin",
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        try {
            return await response.json();
        } catch (_) {
            return null;
        }
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content?.trim() || "";
    }

    function bindServiceWorkerMessages() {
        if (!("serviceWorker" in navigator)) {
            return;
        }

        navigator.serviceWorker.addEventListener("message", (event) => {
            const message = event.data || {};

            if (message.type === "cetsy-push-event") {
                handleServiceWorkerPushEvent(message.payload || null);
                return;
            }

            if (message.type === "cetsy-push-subscription-change") {
                void syncPushState();
            }
        });
    }

    function supportsWebPush() {
        if (!("serviceWorker" in navigator) || !("PushManager" in window) || !("Notification" in window)) {
            return false;
        }

        if (window.isSecureContext) {
            return true;
        }

        return ["localhost", "127.0.0.1", "[::1]"].includes(window.location.hostname);
    }

    function isWebPushConfigured() {
        return Boolean(pushConfig.enabled && pushConfig.publicKey && pushConfig.subscribeUrl && pushConfig.unsubscribeUrl);
    }

    async function awaitServiceWorkerReady() {
        if (!("serviceWorker" in navigator)) {
            throw new Error("Service workers are unavailable.");
        }

        if (document.readyState !== "complete") {
            await new Promise((resolve) => {
                window.addEventListener("load", resolve, { once: true });
            });
        }

        const readyPromise = navigator.serviceWorker.ready;
        const timeoutPromise = new Promise((_, reject) => {
            window.setTimeout(() => reject(new Error("Service worker ready timeout.")), 10000);
        });

        return Promise.race([readyPromise, timeoutPromise]);
    }

    function getSupportedContentEncoding() {
        const encodings = window.PushManager?.supportedContentEncodings;
        if (Array.isArray(encodings) && encodings.includes("aes128gcm")) {
            return "aes128gcm";
        }

        return "aesgcm";
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        const normalized = (base64String + padding).replaceAll("-", "+").replaceAll("_", "/");
        const rawData = window.atob(normalized);
        const output = new Uint8Array(rawData.length);

        for (let index = 0; index < rawData.length; index += 1) {
            output[index] = rawData.charCodeAt(index);
        }

        return output;
    }

    function showToast({ title, body, href = null, actionLabel = "Open", tone = "general" }) {
        const viewport = ensureToastViewport();
        const toast = document.createElement("div");
        toast.className = `live-alert-toast live-alert-toast--${tone}`;

        const icon = tone === "sale" ? "fa-sack-dollar" : tone === "message" ? "fa-comments" : "fa-bell";
        const linkMarkup = href
            ? `<a href="${escapeAttribute(href)}" class="live-alert-toast__link">${escapeHtml(actionLabel)}</a>`
            : "";

        toast.innerHTML = `
            <div class="live-alert-toast__icon">
                <i class="fas ${icon}"></i>
            </div>
            <div class="live-alert-toast__body">
                <p class="live-alert-toast__title">${escapeHtml(title)}</p>
                <p class="live-alert-toast__text">${escapeHtml(body)}</p>
                <div class="live-alert-toast__actions">
                    ${linkMarkup}
                    <button type="button" class="live-alert-toast__dismiss" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        `;

        viewport.appendChild(toast);

        const dismiss = toast.querySelector(".live-alert-toast__dismiss");
        const removeToast = () => {
            toast.classList.remove("is-visible");
            window.setTimeout(() => toast.remove(), 180);
        };

        dismiss?.addEventListener("click", removeToast);
        window.setTimeout(() => toast.classList.add("is-visible"), 10);
        window.setTimeout(removeToast, 6500);
    }

    function ensureToastViewport() {
        let viewport = document.getElementById("liveAlertToastViewport");
        if (viewport) {
            return viewport;
        }

        viewport = document.createElement("div");
        viewport.id = "liveAlertToastViewport";
        viewport.className = "live-alert-toast-viewport";
        document.body.appendChild(viewport);
        return viewport;
    }

    function injectLiveNotificationStyles() {
        if (document.getElementById("live-alert-style")) {
            return;
        }

        const style = document.createElement("style");
        style.id = "live-alert-style";
        style.textContent = `
            .live-alert-toast-viewport { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom, 0px)); z-index: 120; display: flex; max-width: min(24rem, calc(100vw - 2rem)); flex-direction: column; gap: 0.75rem; pointer-events: none; }
            .live-alert-toast { display: flex; gap: 0.875rem; border-radius: 1rem; border: 1px solid #e2e8f0; background: rgba(255, 255, 255, 0.98); box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18); padding: 0.9rem 1rem; transform: translateY(0.5rem); opacity: 0; transition: transform 0.18s ease, opacity 0.18s ease; pointer-events: auto; }
            .live-alert-toast.is-visible { transform: translateY(0); opacity: 1; }
            .live-alert-toast__icon { display: inline-flex; height: 2.25rem; width: 2.25rem; flex: 0 0 2.25rem; align-items: center; justify-content: center; border-radius: 9999px; font-size: 0.95rem; }
            .live-alert-toast--message .live-alert-toast__icon { background: #dbeafe; color: #1d4ed8; }
            .live-alert-toast--sale .live-alert-toast__icon { background: #dcfce7; color: #15803d; }
            .live-alert-toast--general .live-alert-toast__icon { background: #f1f5f9; color: #334155; }
            .live-alert-toast__body { min-width: 0; flex: 1; }
            .live-alert-toast__title { margin: 0; font-size: 0.92rem; font-weight: 700; color: #0f172a; }
            .live-alert-toast__text { margin: 0.18rem 0 0; color: #475569; font-size: 0.82rem; line-height: 1.45; }
            .live-alert-toast__actions { margin-top: 0.55rem; display: flex; align-items: center; gap: 0.55rem; }
            .live-alert-toast__link { display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; background: #0f766e; padding: 0.35rem 0.8rem; font-size: 0.75rem; font-weight: 700; color: #ffffff; text-decoration: none; }
            .live-alert-toast__dismiss { border: 0; background: transparent; color: #64748b; cursor: pointer; font-size: 1.05rem; line-height: 1; padding: 0.15rem; }
            .live-alert-pref { display: flex; align-items: center; justify-content: space-between; gap: 1rem; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 0.85rem 0.95rem; }
            .live-alert-pref__title { display: block; color: #0f172a; font-size: 0.9rem; font-weight: 700; }
            .live-alert-pref__desc { display: block; margin-top: 0.18rem; color: #64748b; font-size: 0.78rem; line-height: 1.4; }
            .live-alert-pref input[type="checkbox"] { height: 1.05rem; width: 1.05rem; flex: 0 0 1.05rem; }
            @media (max-width: 640px) { .live-alert-toast-viewport { left: 0.75rem; right: 0.75rem; max-width: none; } }
        `;

        document.head.appendChild(style);
    }

    function installAudioUnlock() {
        const unlock = () => {
            void unlockAudio();
            window.removeEventListener("pointerdown", unlock);
            window.removeEventListener("keydown", unlock);
            window.removeEventListener("touchstart", unlock);
        };

        window.addEventListener("pointerdown", unlock, { passive: true });
        window.addEventListener("keydown", unlock, { passive: true });
        window.addEventListener("touchstart", unlock, { passive: true });
    }

    async function unlockAudio() {
        const ContextClass = window.AudioContext || window.webkitAudioContext;
        if (!ContextClass) {
            return null;
        }

        if (!state.audioContext) {
            state.audioContext = new ContextClass();
        }

        if (state.audioContext.state === "suspended") {
            try {
                await state.audioContext.resume();
            } catch (_) {
                return null;
            }
        }

        state.audioUnlocked = state.audioContext.state === "running";
        return state.audioContext;
    }

    function playMessageSound() {
        playToneSequence([
            { frequency: 784, duration: 0.09, type: "sine", gain: 0.03 },
            { frequency: 988, duration: 0.11, type: "sine", gain: 0.03, delay: 0.1 },
        ]);
    }

    function playSaleSound() {
        playToneSequence([
            { frequency: 1046, duration: 0.07, type: "triangle", gain: 0.04 },
            { frequency: 1318, duration: 0.08, type: "triangle", gain: 0.045, delay: 0.08 },
            { frequency: 1567, duration: 0.12, type: "triangle", gain: 0.05, delay: 0.16 },
        ]);
    }

    function playGeneralSound() {
        playToneSequence([
            { frequency: 659, duration: 0.08, type: "sine", gain: 0.025 },
        ]);
    }

    async function playToneSequence(steps) {
        const ctx = await unlockAudio();
        if (!ctx || !state.audioUnlocked) {
            return;
        }

        const startAt = ctx.currentTime + 0.01;
        steps.forEach((step) => {
            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();
            const delay = Number(step.delay || 0);
            const begin = startAt + delay;
            const end = begin + Number(step.duration || 0.1);

            oscillator.type = step.type || "sine";
            oscillator.frequency.setValueAtTime(Number(step.frequency || 440), begin);
            gainNode.gain.setValueAtTime(0.0001, begin);
            gainNode.gain.exponentialRampToValueAtTime(Number(step.gain || 0.03), begin + 0.02);
            gainNode.gain.exponentialRampToValueAtTime(0.0001, end);

            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);
            oscillator.start(begin);
            oscillator.stop(end + 0.02);
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#39;");
    }

    function escapeAttribute(value) {
        return escapeHtml(value);
    }
}
