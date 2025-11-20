(function (window, document) {
  'use strict';

  function getApi() {
    return window.GCInquiry || null;
  }

  function updateBadges() {
    var api = getApi();
    var count = 0;
    if (api && typeof api.getInquiryList === 'function') {
      var list = api.getInquiryList();
      if (Array.isArray(list)) {
        count = list.length;
      }
    }

    var nodes = document.querySelectorAll('[data-gc-inquiry-count]');
    for (var i = 0; i < nodes.length; i++) {
      nodes[i].textContent = String(count);
    }
  }

  function init() {
    updateBadges();

    window.addEventListener('inquiryListChanged', function () {
      updateBadges();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);

