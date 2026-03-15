import axios from 'axios';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let deferredInstallPrompt = null;
const promptDismissKey = 'simelati_pwa_prompt_dismissed';
const standaloneMode = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
let swRegistration = null;
const dismissDurationMs = 7 * 24 * 60 * 60 * 1000;

function promptDismissed() {
    const value = Number(window.localStorage.getItem(promptDismissKey) || 0);
    if (!value) {
        return false;
    }
    return Date.now() - value < dismissDurationMs;
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            swRegistration = await navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' });

            if (swRegistration.waiting) {
                showUpdatePrompt();
            }

            swRegistration.addEventListener('updatefound', () => {
                const worker = swRegistration.installing;
                if (!worker) {
                    return;
                }

                worker.addEventListener('statechange', () => {
                    if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdatePrompt();
                    }
                });
            });
        } catch (_) {
            // silent fail
        }
    });
}

function showInstallPrompt() {
    const promptEl = document.getElementById('pwaInstallPrompt');
    const topbarInstallBtn = document.getElementById('pwaInstallTopbar');
    if (!promptEl) {
        return;
    }

    const dismissed = promptDismissed();
    if (standaloneMode) {
        promptEl.hidden = true;
        if (topbarInstallBtn) {
            topbarInstallBtn.hidden = true;
        }
        return;
    }

    promptEl.hidden = !!dismissed;
    if (topbarInstallBtn) {
        topbarInstallBtn.hidden = false;
    }
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
    const topbarInstallBtn = document.getElementById('pwaInstallTopbar');
    if (promptEl) {
        promptEl.hidden = true;
    }
    if (topbarInstallBtn) {
        topbarInstallBtn.hidden = true;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    showInstallPrompt();

    const promptEl = document.getElementById('pwaInstallPrompt');
    const installBtn = document.getElementById('pwaInstallBtn');
    const dismissBtn = document.getElementById('pwaInstallDismiss');
    const topbarInstallBtn = document.getElementById('pwaInstallTopbar');
    const updateBtn = document.getElementById('pwaUpdateBtn');

    const runInstall = async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            if (promptEl) {
                promptEl.hidden = true;
            }
            if (topbarInstallBtn) {
                topbarInstallBtn.hidden = true;
            }
            return;
        }

        window.alert(installHelpMessage());
    };

    if (installBtn) {
        installBtn.addEventListener('click', runInstall);
    }

    if (topbarInstallBtn) {
        topbarInstallBtn.addEventListener('click', runInstall);
    }

    if (dismissBtn && promptEl) {
        dismissBtn.addEventListener('click', () => {
            window.localStorage.setItem(promptDismissKey, String(Date.now()));
            promptEl.hidden = true;
            if (topbarInstallBtn) {
                topbarInstallBtn.hidden = true;
            }
        });
    }

    if (updateBtn) {
        updateBtn.addEventListener('click', () => {
            if (swRegistration?.waiting) {
                swRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
            }
            window.location.reload();
        });
    }
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

function showUpdatePrompt() {
    const updateEl = document.getElementById('pwaUpdatePrompt');
    if (!updateEl) {
        return;
    }
    updateEl.hidden = false;
}

