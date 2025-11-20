(function (window, document) {
  'use strict';

  var OVERLAY_SELECTOR = '.gc-inquiry-overlay';
  var TOGGLE_SELECTOR = '.inquiry-toggle';
  var LIST_SELECTOR = '.gc-inquiry-overlay__list';
  var EMPTY_SELECTOR = '.gc-inquiry-overlay__empty';
  var CLEAR_SELECTOR = '.gc-inquiry-overlay__clear';
  var CLOSE_SELECTOR = '.gc-inquiry-overlay__close';

  function getInquiryApi() {
    return window.GCInquiry || null;
  }

  function renderList(overlay) {
    var api = getInquiryApi();
    if (!api) {
      return;
    }
    var listContainer = overlay.querySelector(LIST_SELECTOR);
    var emptyState = overlay.querySelector(EMPTY_SELECTOR);
    if (!listContainer || !emptyState) {
      return;
    }

    var items = api.getInquiryList();
    listContainer.innerHTML = '';

    if (!items || !items.length) {
      emptyState.style.display = '';
      return;
    }

    emptyState.style.display = 'none';

    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      var li = document.createElement('li');
      li.className = 'gc-inquiry-overlay__item';
      li.setAttribute('data-product-id', item.id);

      var imageUrl = item.image || '';
      var title = item.title || '';
      var url = item.url || '#';

      li.innerHTML =
        '<div class="gc-inquiry-overlay__item-inner">' +
        '  <div class="gc-inquiry-overlay__thumb">' +
        (imageUrl
          ? '<img src="' + imageUrl + '" alt="' + title.replace(/"/g, '&quot;') + '"/>'
          : '') +
        '  </div>' +
        '  <div class="gc-inquiry-overlay__meta">' +
        '    <a href="' + url + '" class="gc-inquiry-overlay__title">' +
        title +
        '</a>' +
        '    <div class="gc-inquiry-overlay__subtitle">Produktseite</div>' +
        '  </div>' +
        '  <div class="gc-inquiry-overlay__actions">' +
        '    <button type="button" class="gc-inquiry-overlay__remove" data-product-id="' +
        item.id +
        '">Entfernen</button>' +
        '  </div>' +
        '</div>';

      listContainer.appendChild(li);
    }
  }

  function openOverlay(overlay) {
    if (!overlay) {
      return;
    }
    renderList(overlay);
    overlay.classList.add('is-open');
    document.body.classList.add('gc-inquiry-overlay-open');
  }

  function closeOverlay(overlay) {
    if (!overlay) {
      return;
    }
    overlay.classList.remove('is-open');
    document.body.classList.remove('gc-inquiry-overlay-open');
  }

  function bindOverlay() {
    var overlay = document.querySelector(OVERLAY_SELECTOR);
    if (!overlay) {
      return;
    }

    // Open from header toggle
    document.addEventListener('click', function (event) {
      var target = event.target;
      if (!target) {
        return;
      }
      var toggle = target.closest ? target.closest(TOGGLE_SELECTOR) : null;
      if (toggle) {
        event.preventDefault();
        openOverlay(overlay);
      }

      var closeBtn = target.closest ? target.closest(CLOSE_SELECTOR) : null;
      if (closeBtn) {
        event.preventDefault();
        closeOverlay(overlay);
      }

      var removeBtn = target.closest
        ? target.closest('.gc-inquiry-overlay__remove')
        : null;
      if (removeBtn) {
        event.preventDefault();
        var id = removeBtn.getAttribute('data-product-id');
        var api = getInquiryApi();
        if (api && id) {
          api.removeFromInquiry(id);
          renderList(overlay);
        }
      }
    });

    // Clear list
    var clearBtn = overlay.querySelector(CLEAR_SELECTOR);
    if (clearBtn) {
      clearBtn.addEventListener('click', function (event) {
        event.preventDefault();
        var api = getInquiryApi();
        if (api) {
          var list = api.getInquiryList();
          for (var i = 0; i < list.length; i++) {
            api.removeFromInquiry(list[i].id);
          }
        }
      });
    }

    // Close overlay when clicking on backdrop
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) {
        closeOverlay(overlay);
      }
    });

    // Keep overlay in sync with inquiry list changes
    window.addEventListener('inquiryListChanged', function () {
      if (overlay.classList.contains('is-open')) {
        renderList(overlay);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindOverlay);
  } else {
    bindOverlay();
  }
})(window, document);

