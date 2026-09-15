/**
 * BHS Health Delivery System - PWA Registration & Install Handler
 */
(function () {
  'use strict';

  // 1. Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      // Determine service worker path dynamically based on script location
      const swUrl = new URL('../sw.js', document.currentScript ? document.currentScript.src : window.location.href).pathname;
      
      navigator.serviceWorker
        .register(swUrl, { scope: './' })
        .then((reg) => {
          console.log('[PWA] ServiceWorker registered with scope:', reg.scope);
        })
        .catch((err) => {
          // Fallback to root sw.js
          navigator.serviceWorker
            .register('/sw.js')
            .catch((e) => console.log('[PWA] ServiceWorker fallback notice:', e.message));
        });
    });
  }

  // 2. Check if already installed / running in standalone mode
  const isStandalone =
    window.matchMedia('(display-mode: standalone)').matches ||
    window.navigator.standalone === true ||
    document.referrer.includes('android-app://');

  if (isStandalone) {
    console.log('[PWA] Running in standalone mobile app mode.');
    // Hide all install triggers if already installed
    document.documentElement.classList.add('pwa-standalone');
    return;
  }

  const isIOS =
    /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) &&
    !window.MSStream;

  let deferredPrompt = null;

  // 3. Listen for Chromium beforeinstallprompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    window.deferredPwaPrompt = e;

    // Show floating install banner on mobile viewport if not dismissed recently
    showInstallBanner();
  });

  // 4. In-App Install Trigger Function
  window.triggerPwaInstall = async function () {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      console.log('[PWA] User choice outcome:', outcome);
      deferredPrompt = null;
      window.deferredPwaPrompt = null;
      hideInstallBanner();
    } else if (isIOS) {
      showIOSInstallModal();
    } else {
      // Fallback hint
      if (typeof window.showSystemToast === 'function') {
        window.showSystemToast('To install, open your browser menu and select "Install App" or "Add to Home Screen".', {
          type: 'info',
          title: 'Mobile App'
        });
      } else {
        alert('To install the app, tap your browser menu (⋮) and select "Install app" or "Add to Home Screen".');
      }
    }
  };

  // 5. Show Floating Install Banner
  function showInstallBanner() {
    if (document.getElementById('pwaMobileBanner')) return;
    if (localStorage.getItem('bhs_pwa_banner_dismissed') === 'true') return;
    // Only display on mobile screen sizes (<= 860px)
    if (window.innerWidth > 860) return;

    const banner = document.createElement('div');
    banner.id = 'pwaMobileBanner';
    banner.className = 'pwa-mobile-banner';
    banner.innerHTML = `
      <div class="pwa-banner-left">
        <div class="pwa-app-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
          </svg>
        </div>
        <div class="pwa-banner-text">
          <strong>BHS Health App</strong>
          <p>Fast booking & real-time queue</p>
        </div>
      </div>
      <div class="pwa-banner-actions">
        <button type="button" class="pwa-btn-install" id="pwaBannerInstallBtn">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          <span>Install</span>
        </button>
        <button type="button" class="pwa-btn-dismiss" id="pwaBannerDismissBtn" aria-label="Dismiss banner">×</button>
      </div>
    `;

    document.body.appendChild(banner);

    document.getElementById('pwaBannerInstallBtn')?.addEventListener('click', () => {
      window.triggerPwaInstall();
    });

    document.getElementById('pwaBannerDismissBtn')?.addEventListener('click', () => {
      hideInstallBanner();
      localStorage.setItem('bhs_pwa_banner_dismissed', 'true');
    });
  }

  function hideInstallBanner() {
    const banner = document.getElementById('pwaMobileBanner');
    if (banner) {
      banner.style.animation = 'none';
      banner.style.opacity = '0';
      banner.style.transform = 'translateY(100%)';
      banner.style.transition = 'all 0.25s ease';
      setTimeout(() => banner.remove(), 250);
    }
  }

  // 6. Show iOS Safari Instructions Modal
  function showIOSInstallModal() {
    if (document.getElementById('pwaIOSBackdrop')) return;

    const modalWrap = document.createElement('div');
    modalWrap.id = 'pwaIOSBackdrop';
    modalWrap.className = 'pwa-ios-backdrop';
    modalWrap.innerHTML = `
      <div class="pwa-ios-modal">
        <div class="pwa-ios-modal-head">
          <h3>Install BHS Health on iPhone</h3>
          <button type="button" class="pwa-ios-close-btn" id="pwaIOSCloseBtn" aria-label="Close">×</button>
        </div>
        <p style="font-size: 0.88rem; color: #64748b; margin: 0; text-align: left;">Install this web application on your home screen for quick offline access and full-screen experience:</p>
        
        <div class="pwa-ios-steps">
          <div class="pwa-ios-step">
            <span class="pwa-step-num">1</span>
            <span>Tap the <strong>Share</strong> button in Safari toolbar:</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
              <polyline points="16 6 12 2 8 6"/>
              <line x1="12" y1="2" x2="12" y2="15"/>
            </svg>
          </div>
          <div class="pwa-ios-step">
            <span class="pwa-step-num">2</span>
            <span>Scroll down and tap <strong>"Add to Home Screen"</strong></span>
          </div>
          <div class="pwa-ios-step">
            <span class="pwa-step-num">3</span>
            <span>Tap <strong>Add</strong> in the top right corner</span>
          </div>
        </div>

        <button type="button" class="pwa-ios-ok-btn" id="pwaIOSOkBtn">Got it</button>
      </div>
    `;

    document.body.appendChild(modalWrap);

    const closeModal = () => modalWrap.remove();
    document.getElementById('pwaIOSCloseBtn')?.addEventListener('click', closeModal);
    document.getElementById('pwaIOSOkBtn')?.addEventListener('click', closeModal);
    modalWrap.addEventListener('click', (e) => {
      if (e.target === modalWrap) closeModal();
    });
  }

  // 7. Bind click on any [data-pwa-install] elements in DOM
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-pwa-install]').forEach((el) => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        window.triggerPwaInstall();
      });
    });

    // Check iOS prompt
    if (isIOS && !isStandalone && window.innerWidth <= 860) {
      setTimeout(() => {
        showInstallBanner();
      }, 3000);
    }
  });
})();
