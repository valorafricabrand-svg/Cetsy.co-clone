(function () {
  'use strict';

  var DISMISS_KEY = 'pwa_install_prompt_dismissed_until';
  var DISMISS_DAYS = 7;
  var SW_URL = '/service-worker.js';
  var INSTALL_DELAY_MS = 3500;

  var deferredPromptEvent = null;
  var promptElement = null;
  var installButton = null;
  var dismissButton = null;
  var closeButton = null;
  var headingElement = null;
  var messageElement = null;
  var iosHelpElement = null;

  registerServiceWorker();

  if (!isMobileClient() || isStandaloneMode()) {
    return;
  }

  injectStyles();

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPromptEvent = event;
    showPrompt('installable');
  });

  window.addEventListener('appinstalled', function () {
    deferredPromptEvent = null;
    hidePrompt(false);
    setDismissed(180);
  });

  window.setTimeout(function () {
    if (isDismissed() || isStandaloneMode()) {
      return;
    }

    if (deferredPromptEvent) {
      showPrompt('installable');
      return;
    }

    if (isIosSafari()) {
      showPrompt('ios');
    }
  }, INSTALL_DELAY_MS);

  function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      return;
    }
    if (!isSecureContextAllowed()) {
      return;
    }

    window.addEventListener('load', function () {
      navigator.serviceWorker.register(SW_URL).catch(function (error) {
        console.warn('SW registration failed:', error);
      });
    }, { once: true });
  }

  function isSecureContextAllowed() {
    if (location.protocol === 'https:') {
      return true;
    }
    return location.hostname === 'localhost'
      || location.hostname === '127.0.0.1'
      || location.hostname === '[::1]';
  }

  function isStandaloneMode() {
    var mediaStandalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
    var iosStandalone = typeof window.navigator.standalone === 'boolean' && window.navigator.standalone;
    return Boolean(mediaStandalone || iosStandalone);
  }

  function isMobileClient() {
    var ua = window.navigator.userAgent || '';
    var uaMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
    var coarsePointer = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
    return Boolean(uaMobile || coarsePointer);
  }

  function isIosSafari() {
    var ua = window.navigator.userAgent || '';
    var ios = /iPhone|iPad|iPod/i.test(ua);
    var safari = /Safari/i.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS|DuckDuckGo/i.test(ua);
    return ios && safari;
  }

  function isDismissed() {
    try {
      var raw = localStorage.getItem(DISMISS_KEY);
      if (!raw) {
        return false;
      }
      var until = parseInt(raw, 10);
      return !isNaN(until) && until > Date.now();
    } catch (_) {
      return false;
    }
  }

  function setDismissed(days) {
    try {
      var daysToSave = typeof days === 'number' ? days : DISMISS_DAYS;
      var expiresAt = Date.now() + (daysToSave * 24 * 60 * 60 * 1000);
      localStorage.setItem(DISMISS_KEY, String(expiresAt));
    } catch (_) {
      // Ignore storage errors in private mode.
    }
  }

  function getAppName() {
    var appMeta = document.querySelector('meta[name="application-name"]');
    if (appMeta && appMeta.content) {
      return appMeta.content.trim();
    }
    var appleMeta = document.querySelector('meta[name="apple-mobile-web-app-title"]');
    if (appleMeta && appleMeta.content) {
      return appleMeta.content.trim();
    }
    return 'this app';
  }

  function createPrompt() {
    if (promptElement) {
      return;
    }

    promptElement = document.createElement('div');
    promptElement.id = 'pwa-install-prompt';
    promptElement.setAttribute('role', 'dialog');
    promptElement.setAttribute('aria-live', 'polite');
    promptElement.setAttribute('aria-label', 'Install app prompt');
    promptElement.innerHTML = ''
      + '<div class="pwa-install-card">'
      + '  <button type="button" class="pwa-install-close" data-action="close" aria-label="Close">&times;</button>'
      + '  <h3 class="pwa-install-title"></h3>'
      + '  <p class="pwa-install-message"></p>'
      + '  <p class="pwa-install-ios-help" hidden></p>'
      + '  <div class="pwa-install-actions">'
      + '    <button type="button" class="pwa-btn pwa-btn-muted" data-action="dismiss">Not now</button>'
      + '    <button type="button" class="pwa-btn pwa-btn-primary" data-action="install">Install</button>'
      + '  </div>'
      + '</div>';

    document.body.appendChild(promptElement);

    headingElement = promptElement.querySelector('.pwa-install-title');
    messageElement = promptElement.querySelector('.pwa-install-message');
    iosHelpElement = promptElement.querySelector('.pwa-install-ios-help');
    installButton = promptElement.querySelector('[data-action="install"]');
    dismissButton = promptElement.querySelector('[data-action="dismiss"]');
    closeButton = promptElement.querySelector('[data-action="close"]');

    installButton.addEventListener('click', onInstallClick);
    dismissButton.addEventListener('click', onDismissClick);
    closeButton.addEventListener('click', onDismissClick);
  }

  function showPrompt(mode) {
    if (isDismissed() || isStandaloneMode()) {
      return;
    }

    createPrompt();

    var appName = getAppName();
    headingElement.textContent = 'Install ' + appName;
    messageElement.textContent = 'Add it to your home screen for faster access on your phone.';

    if (mode === 'installable') {
      installButton.hidden = false;
      iosHelpElement.hidden = true;
      installButton.textContent = 'Install';
    } else {
      installButton.hidden = true;
      iosHelpElement.hidden = false;
      iosHelpElement.textContent = 'On iPhone Safari: tap Share, then tap "Add to Home Screen".';
    }

    promptElement.classList.add('is-visible');
  }

  function hidePrompt(rememberDismissal) {
    if (!promptElement) {
      return;
    }
    promptElement.classList.remove('is-visible');
    if (rememberDismissal) {
      setDismissed(DISMISS_DAYS);
    }
  }

  function onDismissClick() {
    hidePrompt(true);
  }

  function onInstallClick() {
    if (!deferredPromptEvent) {
      hidePrompt(true);
      return;
    }

    deferredPromptEvent.prompt();
    deferredPromptEvent.userChoice
      .then(function (choice) {
        if (choice && choice.outcome === 'accepted') {
          hidePrompt(false);
          setDismissed(180);
        } else {
          setDismissed(2);
        }
        deferredPromptEvent = null;
      })
      .catch(function () {
        setDismissed(2);
        deferredPromptEvent = null;
      });
  }

  function injectStyles() {
    if (document.getElementById('pwa-install-style')) {
      return;
    }

    var style = document.createElement('style');
    style.id = 'pwa-install-style';
    style.textContent = ''
      + '#pwa-install-prompt {'
      + '  position: fixed;'
      + '  left: 0;'
      + '  right: 0;'
      + '  bottom: 0;'
      + '  z-index: 1200;'
      + '  display: none;'
      + '  padding: 12px;'
      + '  pointer-events: none;'
      + '}'
      + '#pwa-install-prompt.is-visible {'
      + '  display: block;'
      + '}'
      + '#pwa-install-prompt .pwa-install-card {'
      + '  max-width: 560px;'
      + '  margin: 0 auto;'
      + '  background: #ffffff;'
      + '  border: 1px solid rgba(15, 23, 42, 0.14);'
      + '  box-shadow: 0 14px 30px rgba(2, 6, 23, 0.24);'
      + '  border-radius: 14px;'
      + '  padding: 14px 14px 12px;'
      + '  pointer-events: auto;'
      + '  position: relative;'
      + '}'
      + '#pwa-install-prompt .pwa-install-close {'
      + '  position: absolute;'
      + '  right: 8px;'
      + '  top: 6px;'
      + '  border: 0;'
      + '  background: transparent;'
      + '  color: #475569;'
      + '  font-size: 26px;'
      + '  line-height: 1;'
      + '  cursor: pointer;'
      + '}'
      + '#pwa-install-prompt .pwa-install-title {'
      + '  margin: 0 28px 4px 0;'
      + '  color: #0f172a;'
      + '  font-size: 1rem;'
      + '}'
      + '#pwa-install-prompt .pwa-install-message,'
      + '#pwa-install-prompt .pwa-install-ios-help {'
      + '  margin: 0;'
      + '  color: #475569;'
      + '  font-size: 0.92rem;'
      + '}'
      + '#pwa-install-prompt .pwa-install-ios-help {'
      + '  margin-top: 4px;'
      + '}'
      + '#pwa-install-prompt .pwa-install-actions {'
      + '  margin-top: 10px;'
      + '  display: flex;'
      + '  justify-content: flex-end;'
      + '  gap: 8px;'
      + '}'
      + '#pwa-install-prompt .pwa-btn {'
      + '  border: 0;'
      + '  border-radius: 10px;'
      + '  padding: 8px 12px;'
      + '  font-size: 0.875rem;'
      + '  cursor: pointer;'
      + '}'
      + '#pwa-install-prompt .pwa-btn-muted {'
      + '  background: #f1f5f9;'
      + '  color: #0f172a;'
      + '}'
      + '#pwa-install-prompt .pwa-btn-primary {'
      + '  background: #027333;'
      + '  color: #ffffff;'
      + '}'
      + '@media (max-width: 420px) {'
      + '  #pwa-install-prompt {'
      + '    padding: 10px;'
      + '  }'
      + '  #pwa-install-prompt .pwa-install-card {'
      + '    border-radius: 12px;'
      + '  }'
      + '}';

    document.head.appendChild(style);
  }
})();
