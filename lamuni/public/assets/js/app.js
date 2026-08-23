/**
 * Lamuni — Client-side JavaScript
 * ----------------------------------------
 * Handles:
 *  1. Phone/BVN 11-digit validation
 *  2. Password confirm match
 *  3. English/Hausa language toggle
 *  4. Unread notification badge update
 *  5. Auto-dismiss alerts
 */

(function () {
  'use strict';

  /* ── 1. Registration form validation ── */
  const registerForm = document.getElementById('register-form');
  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      let valid = true;

      // Phone
      const phone = document.getElementById('phone');
      const phoneErr = document.getElementById('phone-error');
      if (phone) {
        if (!/^\d{11}$/.test(phone.value.trim())) {
          phone.classList.add('is-invalid');
          if (phoneErr) phoneErr.textContent = langText('phone_error');
          valid = false;
        } else {
          phone.classList.remove('is-invalid');
        }
      }

      // BVN
      const bvn = document.getElementById('bvn');
      const bvnErr = document.getElementById('bvn-error');
      if (bvn && bvn.value.trim() !== '') {
        if (!/^\d{11}$/.test(bvn.value.trim())) {
          bvn.classList.add('is-invalid');
          if (bvnErr) bvnErr.textContent = langText('bvn_error');
          valid = false;
        } else {
          bvn.classList.remove('is-invalid');
        }
      }

      // Password confirm
      const pass    = document.getElementById('password');
      const confirm = document.getElementById('password_confirm');
      const passErr = document.getElementById('pass-error');
      if (pass && confirm) {
        if (pass.value !== confirm.value) {
          confirm.classList.add('is-invalid');
          if (passErr) passErr.textContent = langText('pass_mismatch');
          valid = false;
        } else {
          confirm.classList.remove('is-invalid');
        }
      }

      if (!valid) e.preventDefault();
    });

    // Real-time phone validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
      phoneInput.addEventListener('input', function () {
        const phoneErr = document.getElementById('phone-error');
        if (/^\d{11}$/.test(this.value.trim())) {
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        } else {
          this.classList.remove('is-valid');
        }
      });
    }

    // Real-time BVN validation
    const bvnInput = document.getElementById('bvn');
    if (bvnInput) {
      bvnInput.addEventListener('input', function () {
        if (this.value.trim() === '') {
          this.classList.remove('is-invalid', 'is-valid');
          return;
        }
        if (/^\d{11}$/.test(this.value.trim())) {
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        } else {
          this.classList.remove('is-valid');
        }
      });
    }
  }

  /* ── 2. Loan application form ── */
  const applyForm = document.getElementById('apply-form');
  if (applyForm) {
    applyForm.addEventListener('submit', function (e) {
      const amount = document.getElementById('amount');
      if (amount && parseFloat(amount.value) < 1000) {
        amount.classList.add('is-invalid');
        e.preventDefault();
      }
    });
  }

  /* ── 3. Language Toggle (English / Hausa) ── */
  const LANG_KEY = 'lm_lang';
  let currentLang = localStorage.getItem(LANG_KEY) || 'en';

  const translations = {
    en: {
      phone_error:   'Phone number must be exactly 11 digits.',
      bvn_error:     'BVN must be exactly 11 digits.',
      pass_mismatch: 'Passwords do not match.',
    },
    ha: {
      phone_error:   'Lambar waya dole ta kasance lambobi 11 daidai.',
      bvn_error:     'BVN dole ta kasance lambobi 11 daidai.',
      pass_mismatch: 'Kalmomin sirri ba su dace ba.',
    },
  };

  function langText(key) {
    return (translations[currentLang] || translations['en'])[key] || key;
  }

  function applyLang(lang) {
    currentLang = lang;
    localStorage.setItem(LANG_KEY, lang);

    // Toggle data-lang attributes
    document.querySelectorAll('[data-en][data-ha]').forEach(function (el) {
      el.textContent = lang === 'ha' ? el.dataset.ha : el.dataset.en;
    });

    // Update placeholder attributes
    document.querySelectorAll('[data-placeholder-en][data-placeholder-ha]').forEach(function (el) {
      el.placeholder = lang === 'ha' ? el.dataset.placeholderHa : el.dataset.placeholderEn;
    });

    // Update active button
    document.querySelectorAll('.lang-btn').forEach(function (btn) {
      btn.classList.toggle('active', btn.dataset.lang === lang);
    });
  }

  // Wire up toggle buttons
  document.querySelectorAll('.lang-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      applyLang(this.dataset.lang);
    });
  });

  // Apply saved language on page load
  applyLang(currentLang);

  /* ── 4. Auto-dismiss alerts after 6 s ── */
  document.querySelectorAll('.alert-dismissible').forEach(function (el) {
    setTimeout(function () {
      const bsAlert = bootstrap && bootstrap.Alert ? bootstrap.Alert.getOrCreateInstance(el) : null;
      if (bsAlert) bsAlert.close();
      else el.style.display = 'none';
    }, 6000);
  });

  /* ── 5. Confirm dangerous actions ── */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });

})();
