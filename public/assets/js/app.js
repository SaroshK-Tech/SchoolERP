(function () {
  'use strict';

  // Auto-dismiss alerts after a few seconds.
  document.querySelectorAll('.alert').forEach(function (el) {
    var t = setTimeout(function () {
      el.style.transition = 'opacity .4s, transform .4s';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-4px)';
      setTimeout(function () { el.remove(); }, 400);
    }, 5000);
  });

  // Confirm dialogs for destructive actions (elements with data-confirm).
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (!el) return;
    var msg = el.getAttribute('data-confirm') || 'Are you sure?';
    if (!window.confirm(msg)) {
      e.preventDefault();
    }
  });

  // Simple checkbox: select all helper (elements with data-check-all + data-check-list).
  document.addEventListener('change', function (e) {
    var all = e.target.closest('[data-check-all]');
    if (!all) return;
    var sel = all.getAttribute('data-check-all');
    document.querySelectorAll(sel).forEach(function (cb) {
      cb.checked = all.checked;
      cb.dispatchEvent(new Event('change'));
    });
  });

  // Filter forms auto-submit on change (elements with data-auto-submit inside a form).
  document.addEventListener('change', function (e) {
    var el = e.target.closest('[data-auto-submit]');
    if (!el) return;
    var form = el.closest('form');
    if (form) form.submit();
  });
})();
