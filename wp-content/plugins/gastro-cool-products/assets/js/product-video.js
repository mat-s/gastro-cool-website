// ── Product Video – tab switching ─────────────────────────────────────

(function () {
  'use strict';

  function initVideoTabs(widget) {
    var tabs   = widget.querySelectorAll('.gc-product-video__tab');
    var panels = widget.querySelectorAll('.gc-product-video__panel');

    if (!tabs.length) return;

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var index = tab.getAttribute('data-index');

        tabs.forEach(function (t) {
          t.classList.remove('is-active');
          t.setAttribute('aria-selected', 'false');
        });

        panels.forEach(function (p) {
          p.classList.remove('is-active');
          p.classList.add('is-hidden');
        });

        tab.classList.add('is-active');
        tab.setAttribute('aria-selected', 'true');

        var panel = widget.querySelector('.gc-product-video__panel[data-index="' + index + '"]');
        if (panel) {
          panel.classList.remove('is-hidden');
          panel.classList.add('is-active');
        }
      });
    });
  }

  function init() {
    document.querySelectorAll('.gc-product-video').forEach(initVideoTabs);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Elementor frontend re-init
  window.addEventListener('elementor/frontend/init', function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      window.elementorFrontend.hooks.addAction('frontend/element_ready/gcp_product_video.default', function (scope) {
        initVideoTabs(scope[0]);
      });
    }
  });
})();
