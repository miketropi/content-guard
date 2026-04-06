/**
 * Content Guard – Frontend Protection Script
 * Vanilla JS only. No jQuery. No external dependencies.
 *
 * Config is injected via wp_localize_script as WPCGConfig.
 */
(function () {
  'use strict';

  if (typeof WPCGConfig === 'undefined') { return; }

  var cfg           = WPCGConfig;
  var popupOpen     = false;
  var popupCooldown = false; // Prevent rapid re-trigger.

  // ─── DOM refs (resolved lazily after DOMContentLoaded) ───────────────────
  var overlay, popup, closeBtn;

  function resolveDOM() {
    overlay  = document.getElementById('cogu-overlay');
    popup    = document.getElementById('cogu-popup');
    closeBtn = document.getElementById('cogu-close');
  }

  // ─── Popup open / close ──────────────────────────────────────────────────
  function openPopup() {
    if (!cfg.showPopup || popupOpen || popupCooldown) { return; }
    setTimeout(function () {
      if (!overlay || !popup) { return; }
      overlay.removeAttribute('aria-hidden');
      overlay.classList.add('cogu-overlay--visible');
      popup.removeAttribute('aria-hidden');
      popup.classList.add('cogu-popup--visible');
      popup.focus();
      popupOpen = true;
    }, cfg.popupDelay || 0);
  }

  function closePopup() {
    if (!overlay || !popup) { return; }
    overlay.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('cogu-overlay--visible');
    popup.setAttribute('aria-hidden', 'true');
    popup.classList.remove('cogu-popup--visible');
    popupOpen    = false;
    popupCooldown = true;
    // Short cooldown so accidental double-trigger doesn't reopen immediately.
    setTimeout(function () { popupCooldown = false; }, 1500);
  }

  // ─── Right click ─────────────────────────────────────────────────────────
  if (cfg.disableRightClick) {
    document.addEventListener('contextmenu', function (e) {
      // Allow on actual href links (usability).
      if (e.target.closest('a[href]')) { return; }
      e.preventDefault();
      openPopup();
    });
  }

  // ─── Copy / Cut ──────────────────────────────────────────────────────────
  if (cfg.disableCopy) {
    document.addEventListener('copy', function (e) {
      if (isFormField(e.target)) { return; }
      e.preventDefault();
      openPopup();
    });
  }

  if (cfg.disableCut) {
    document.addEventListener('cut', function (e) {
      if (isFormField(e.target)) { return; }
      e.preventDefault();
      openPopup();
    });
  }

  // ─── Text selection ──────────────────────────────────────────────────────
  if (cfg.disableSelect) {
    document.addEventListener('selectstart', function (e) {
      if (isFormField(e.target)) { return; }
      e.preventDefault();
    });
  }

  // ─── Drag & drop ─────────────────────────────────────────────────────────
  if (cfg.disableDrag) {
    document.addEventListener('dragstart', function (e) {
      e.preventDefault();
    });
  }

  // ─── Keyboard shortcuts ──────────────────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    var ctrl  = e.ctrlKey || e.metaKey;
    var shift = e.shiftKey;
    var key   = (e.key || '').toLowerCase();
    var code  = e.keyCode;

    // DevTools keys.
    if (cfg.disableDevtools) {
      if (code === 123) { // F12
        e.preventDefault();
        openPopup();
        return;
      }
      if (ctrl && shift && (key === 'i' || key === 'j' || key === 'c')) {
        e.preventDefault();
        return;
      }
      if (ctrl && key === 'u') { // View source
        e.preventDefault();
        return;
      }
    }

    // Print.
    if (cfg.disablePrint && ctrl && key === 'p') {
      e.preventDefault();
      return;
    }

    // Copy (keyboard path — redundant guard alongside the copy event listener).
    if (cfg.disableCopy && ctrl && key === 'c') {
      if (isFormField(document.activeElement)) { return; }
      e.preventDefault();
      openPopup();
      return;
    }

    // Select all.
    if (cfg.disableSelect && ctrl && key === 'a') {
      if (isFormField(document.activeElement)) { return; }
      e.preventDefault();
      return;
    }

    // Cut (keyboard path).
    if (cfg.disableCut && ctrl && key === 'x') {
      if (isFormField(document.activeElement)) { return; }
      e.preventDefault();
      return;
    }
  });

  // ─── Popup close events ──────────────────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    if ((e.key === 'Escape' || e.key === 'Esc') && popupOpen) {
      closePopup();
    }
  });

  // Close button & overlay click — wired after DOM ready.
  document.addEventListener('DOMContentLoaded', function () {
    resolveDOM();

    if (closeBtn) {
      closeBtn.addEventListener('click', closePopup);
    }

    if (overlay && cfg.closeOnOverlay) {
      overlay.addEventListener('click', closePopup);
    }
  });

  // ─── Subscribe form (AJAX) ───────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    if (!e.target || e.target.id !== 'cogu-subscribe-btn') { return; }
    handleSubscribe();
  });

  function handleSubscribe() {
    var emailInput = document.getElementById('cogu-email-input');
    var msgEl      = document.getElementById('cogu-subscribe-msg');
    var formEl     = document.getElementById('cogu-subscribe-form');
    var btn        = document.getElementById('cogu-subscribe-btn');

    if (!emailInput || !msgEl) { return; }

    var email = emailInput.value.trim();
    if (!email || !isValidEmail(email)) {
      showMsg(msgEl, 'error', 'Please enter a valid email address.');
      return;
    }

    btn.disabled = true;
    btn.textContent = '...';

    var body = new URLSearchParams();
    body.append('action', 'cogu_subscribe');
    body.append('nonce',  cfg.nonce);
    body.append('email',  email);

    fetch(cfg.ajaxUrl, {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    body.toString(),
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          if (formEl) { formEl.style.display = 'none'; }
          showMsg(msgEl, 'success', data.data.message || 'Thank you!');
          if (data.data.redirect) {
            setTimeout(function () { window.location.href = data.data.redirect; }, 1200);
          }
        } else {
          showMsg(msgEl, 'error', (data.data && data.data.message) || 'Something went wrong.');
          btn.disabled = false;
        }
      })
      .catch(function () {
        showMsg(msgEl, 'error', 'Network error. Please try again.');
        btn.disabled = false;
      });
  }

  // ─── Helpers ─────────────────────────────────────────────────────────────
  function isFormField(el) {
    if (!el) { return false; }
    var tag = el.tagName;
    return tag === 'INPUT' || tag === 'TEXTAREA' || el.isContentEditable;
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function showMsg(el, type, text) {
    el.className = 'cogu-subscribe-msg cogu-subscribe-msg--' + type;
    el.textContent = text;
  }

}());
