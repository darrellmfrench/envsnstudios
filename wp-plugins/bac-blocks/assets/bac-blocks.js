/* BAC Blocks — mobile nav toggle + login/contact modals */
(function () {

  function wireNav() {
    document.querySelectorAll('.bac-nav-toggle').forEach(function (btn) {
      if (btn.dataset.bacWired) return;
      btn.dataset.bacWired = '1';
      var collapse = document.getElementById(btn.getAttribute('aria-controls')) ||
                     btn.parentNode.querySelector('.bac-nav-collapse');
      if (!collapse) return;
      btn.addEventListener('click', function () {
        var open = collapse.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      });
    });
  }

  function modalIdFrom(el) {
    var id = el.getAttribute('data-bac-open');
    if (id === 'login') return 'login-modal';
    if (id === 'contact') return 'contact-modal';
    if (id) return id;
    var href = el.getAttribute('href') || '';
    if (href.indexOf('#') === 0) return href.slice(1);
    return '';
  }

  function wireModals() {
    /* Remove any legacy duplicate dialogs (e.g. from an old footer template)
       so our login-modal / contact-modal ids are unambiguous and ours win. */
    ['login-modal', 'contact-modal'].forEach(function (id) {
      document.querySelectorAll('dialog[id="' + id + '"]').forEach(function (d) {
        if (!d.classList.contains('bac-modal')) d.remove();
      });
    });

    /* Openers: [data-bac-open], or links to #login-modal / #contact-modal */
    var openers = document.querySelectorAll('[data-bac-open], a[href="#login-modal"], a[href="#contact-modal"]');
    openers.forEach(function (el) {
      if (el.dataset.bacOpenWired) return;
      el.dataset.bacOpenWired = '1';
      el.addEventListener('click', function (e) {
        var dlg = document.getElementById(modalIdFrom(el));
        if (dlg && typeof dlg.showModal === 'function') {
          e.preventDefault();
          dlg.showModal();
        }
      });
    });

    /* Close on X button or backdrop click (Escape is native to <dialog>) */
    document.querySelectorAll('dialog.bac-modal').forEach(function (dlg) {
      if (dlg.dataset.bacModalWired) return;
      dlg.dataset.bacModalWired = '1';
      dlg.querySelectorAll('[data-bac-close]').forEach(function (b) {
        b.addEventListener('click', function () { dlg.close(); });
      });
      dlg.addEventListener('click', function (e) {
        if (e.target === dlg) dlg.close();
      });
    });

    /* Contact form: front-end placeholder (connect to a real mail handler later) */
    var cf = document.getElementById('bac-contact-form');
    if (cf && !cf.dataset.bacWired) {
      cf.dataset.bacWired = '1';
      cf.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = cf.querySelector('[type="submit"]');
        var label = btn.textContent;
        btn.textContent = 'Message Sent!';
        btn.disabled = true;
        setTimeout(function () {
          var d = cf.closest('dialog');
          if (d) d.close();
          cf.reset();
          btn.textContent = label;
          btn.disabled = false;
        }, 1600);
      });
    }
  }

  // FAQ uses native <details>/<summary> — no JS needed.

  function wireUserMenu() {
    document.querySelectorAll('.bac-nav-user-trigger').forEach(function (btn) {
      if (btn.dataset.bacWired) return;
      btn.dataset.bacWired = '1';
      var menu = document.getElementById(btn.getAttribute('aria-controls'));
      if (!menu) return;

      function close() {
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      }
      function open() {
        menu.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
      }

      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (menu.hidden) open(); else close();
      });
      document.addEventListener('click', function (e) {
        if (!menu.hidden && !menu.contains(e.target) && e.target !== btn) close();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !menu.hidden) close();
      });
    });
  }

  function init() { wireNav(); wireModals(); wireUserMenu(); }
  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
})();
