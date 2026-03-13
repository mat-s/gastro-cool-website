(function () {
  'use strict';

  function initAccordion(wrapper) {
    var btn  = wrapper.querySelector('.gc-tech-table__toggle');
    var body = wrapper.querySelector('.gc-tech-table__body');

    if (!btn || !body) return;

    // Set initial inline height so CSS transitions work correctly
    if (wrapper.classList.contains('is-closed')) {
      body.style.height = '0';
    }

    btn.addEventListener('click', function () {
      console.log('clicked');
      if (wrapper.classList.contains('is-open')) {
        close(wrapper, btn, body);
      } else {
        open(wrapper, btn, body);
      }
    });
  }

  function open(wrapper, btn, body) {
    // Measure target height before making visible
    body.style.height = '0';
    body.style.visibility = 'visible';
    wrapper.classList.remove('is-closed');
    wrapper.classList.add('is-open');
    btn.setAttribute('aria-expanded', 'true');

    var target = body.scrollHeight;
    // Force reflow so transition fires
    body.offsetHeight; // eslint-disable-line no-unused-expressions
    body.style.height = target + 'px';

    body.addEventListener('transitionend', function onEnd(e) {
      if (e.propertyName !== 'height') return;
      body.style.height = 'auto';
      body.removeEventListener('transitionend', onEnd);
    });
  }

  function close(wrapper, btn, body) {
    // Pin to current height first so transition starts from there
    body.style.height = body.scrollHeight + 'px';
    body.offsetHeight; // eslint-disable-line no-unused-expressions
    wrapper.classList.remove('is-open');
    wrapper.classList.add('is-closed');
    btn.setAttribute('aria-expanded', 'false');
    body.style.height = '0';
  }

  function init() {
    document.querySelectorAll('.gc-tech-table--accordion').forEach(initAccordion);
  }

  // Elementor editor re-renders widgets on change
  // if (window.elementorFrontend) {
  //   window.elementorFrontend.hooks.addAction(
  //     'frontend/element_ready/gcp_tech_table.default',
  //     function ($el) {
  //       var wrapper = $el[0].querySelector('.gc-tech-table--accordion');
  //       if (wrapper) initAccordion(wrapper);
  //     }
  //   );
  // }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
