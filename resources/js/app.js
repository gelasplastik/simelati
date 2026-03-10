import axios from 'axios';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Silent fail to avoid breaking UX when service worker is unavailable.
        });
    });
}

let deferredInstallPrompt = null;
const promptDismissKey = 'simelati_pwa_prompt_dismissed';
const standaloneMode = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

function showInstallPrompt() {
    const promptEl = document.getElementById('pwaInstallPrompt');
    if (!promptEl) {
        return;
    }

    const dismissed = window.localStorage.getItem(promptDismissKey);
    if (dismissed || standaloneMode) {
        promptEl.hidden = true;
        return;
    }

    promptEl.hidden = false;
}

function isIos() {
    return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
}

function installHelpMessage() {
    if (isIos()) {
        return 'Buka menu Share di Safari, lalu pilih "Add to Home Screen".';
    }

    return 'Gunakan menu browser (ikon titik tiga) lalu pilih "Install app" atau "Tambahkan ke layar utama".';
}

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    showInstallPrompt();
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    const promptEl = document.getElementById('pwaInstallPrompt');
    if (promptEl) {
        promptEl.hidden = true;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    showInstallPrompt();

    const promptEl = document.getElementById('pwaInstallPrompt');
    const installBtn = document.getElementById('pwaInstallBtn');
    const dismissBtn = document.getElementById('pwaInstallDismiss');

    if (!promptEl || !installBtn || !dismissBtn) {
        return;
    }

    installBtn.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            promptEl.hidden = true;
            return;
        }

        window.alert(installHelpMessage());
    });

    dismissBtn.addEventListener('click', () => {
        window.localStorage.setItem(promptDismissKey, '1');
        promptEl.hidden = true;
    });
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const submitter = event.submitter instanceof HTMLButtonElement
        ? event.submitter
        : form.querySelector('button[type=\"submit\"]');
    if (!submitter || submitter.dataset.loadingApplied === '1') {
        return;
    }

    submitter.dataset.loadingApplied = '1';
    submitter.dataset.originalText = submitter.innerHTML;
    submitter.disabled = true;
    submitter.innerHTML = '<span class=\"spinner-border spinner-border-sm me-1\" role=\"status\" aria-hidden=\"true\"></span>Menyimpan...';
});
