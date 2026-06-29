(function () {
    'use strict';

    const PUBLIC_BASE = document.querySelector('meta[name="app-base"]')?.content || '.';
    let deferredInstallPrompt = null;

    /* ── Service Worker ─────────────────────────────────────── */
    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        window.addEventListener('load', () => {
            navigator.serviceWorker
                .register(PUBLIC_BASE + '/sw.js', { scope: PUBLIC_BASE + '/' })
                .then((registration) => {
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;

                        if (!newWorker) {
                            return;
                        }

                        newWorker.addEventListener('statechange', () => {
                            if (
                                newWorker.state === 'installed' &&
                                navigator.serviceWorker.controller
                            ) {
                                showUpdateToast();
                            }
                        });
                    });
                })
                .catch((err) => console.error('SW registration failed:', err));
        });
    }

    function showUpdateToast() {
        const toast = document.getElementById('pwa-update-toast');

        if (!toast) {
            return;
        }

        toast.classList.remove('d-none');
    }

    /* ── Offline banner ───────────────────────────────────────── */
    function initOfflineBanner() {
        const banner = document.getElementById('offline-banner');

        if (!banner) {
            return;
        }

        function updateOnlineStatus() {
            if (navigator.onLine) {
                banner.classList.add('d-none');
            } else {
                banner.classList.remove('d-none');
            }
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    }

    /* ── Add to Home Screen prompt ──────────────────────────── */
    function initInstallPrompt() {
        const banner = document.getElementById('install-banner');
        const installBtn = document.getElementById('install-btn');
        const dismissBtn = document.getElementById('install-dismiss');

        if (!banner) {
            return;
        }

        const dismissed = localStorage.getItem('pwa-install-dismissed');

        if (dismissed === 'true') {
            return;
        }

        const isStandalone =
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true;

        if (isStandalone) {
            return;
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            banner.classList.remove('d-none');
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (!deferredInstallPrompt) {
                    showManualInstallHint();
                    return;
                }

                deferredInstallPrompt.prompt();
                const { outcome } = await deferredInstallPrompt.userChoice;
                deferredInstallPrompt = null;
                banner.classList.add('d-none');

                if (outcome === 'accepted') {
                    localStorage.setItem('pwa-install-dismissed', 'true');
                }
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                banner.classList.add('d-none');
                localStorage.setItem('pwa-install-dismissed', 'true');
            });
        }

        const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);

        if (isIos && !isStandalone) {
            setTimeout(() => banner.classList.remove('d-none'), 3000);
        }
    }

    function showManualInstallHint() {
        const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);

        if (isIos) {
            alert('To install: tap the Share button, then "Add to Home Screen".');
        } else {
            alert('To install: open the browser menu and select "Install app" or "Add to Home Screen".');
        }
    }

    /* ── SW update reload ───────────────────────────────────── */
    function initUpdateReload() {
        const reloadBtn = document.getElementById('pwa-reload-btn');

        if (reloadBtn) {
            reloadBtn.addEventListener('click', () => window.location.reload());
        }
    }

    /* ── AJAX toast notifications ───────────────────────────── */
    const TOAST_ICONS = {
        success: 'bi-check-circle-fill',
        danger: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill',
    };

    function showToast(message, type = 'success', delay = 4000) {
        const container = document.getElementById('ajax-toast-container');

        if (!container || typeof bootstrap === 'undefined') {
            return;
        }

        const toastType = type === 'error' ? 'danger' : type;
        const icon = TOAST_ICONS[toastType] || TOAST_ICONS.info;
        const id = 'toast-' + Date.now();

        const html =
            '<div id="' + id + '" class="toast align-items-center text-bg-' + toastType + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
            '<div class="toast-body d-flex align-items-center gap-2">' +
            '<i class="bi ' + icon + '"></i><span>' + escapeHtml(message) + '</span>' +
            '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div>';

        container.insertAdjacentHTML('beforeend', html);

        const el = document.getElementById(id);
        const toast = new bootstrap.Toast(el, { delay: delay });
        toast.show();

        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.AppToast = { show: showToast };

    registerServiceWorker();
    initOfflineBanner();
    initInstallPrompt();
    initUpdateReload();
})();
