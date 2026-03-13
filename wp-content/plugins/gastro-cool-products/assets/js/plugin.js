(function (window, document) {
  'use strict';

  var STORAGE_KEY = 'gastrocool_inquiry_list';

  function sanitizeQuantity(value) {
    var qty = parseInt(value, 10);
    return isNaN(qty) || qty < 1 ? 1 : qty;
  }

  /**
   * Read inquiry list from localStorage.
   * Always returns an array of product objects.
   */
  function readList() {
    if (!window.localStorage) {
      console.warn('localStorage not available');
      return [];
    }
    try {
      var raw = window.localStorage.getItem(STORAGE_KEY);
      console.log('Raw localStorage value:', raw);
      
      if (!raw) {
        console.log('No stored inquiry list found, returning empty array');
        return [];
      }
      
      var parsed = JSON.parse(raw);
      console.log('Parsed inquiry list:', parsed);
      
      if (Array.isArray(parsed)) {
        // Validate array items
        var validItems = [];
        for (var i = 0; i < parsed.length; i++) {
          var item = parsed[i];
          if (item && item.id) {
            validItems.push({
              id: String(item.id),
              title: String(item.title || ''),
              image: String(item.image || ''),
              url: String(item.url || ''),
              quantity: sanitizeQuantity(item.quantity || 1)
            });
          }
        }
        console.log('Valid items from localStorage:', validItems);
        return validItems;
      }
      
      // Legacy / fallback: if a single object was stored before, wrap it
      if (parsed && typeof parsed === 'object' && parsed.id) {
        console.log('Converting single object to array:', parsed);
        return [{
          id: String(parsed.id),
          title: String(parsed.title || ''),
          image: String(parsed.image || ''),
          url: String(parsed.url || ''),
          quantity: sanitizeQuantity(parsed.quantity || 1)
        }];
      }
      
      console.warn('Invalid data in localStorage, returning empty array:', parsed);
      return [];
    } catch (e) {
      console.error('Error reading inquiry list from localStorage:', e);
      return [];
    }
  }

  /**
   * Dispatch a custom event after the inquiry list has changed.
   * Consumers can listen on window for "inquiryListChanged".
   */
  function dispatchChange(list) {
    var event;
    try {
      event = new CustomEvent('inquiryListChanged', { detail: { list: list } });
    } catch (e) {
      event = document.createEvent('CustomEvent');
      event.initCustomEvent('inquiryListChanged', false, false, { list: list });
    }
    window.dispatchEvent(event);
  }

  /**
   * Persist inquiry list to localStorage and notify listeners.
   */
  function writeList(list) {
    if (!window.localStorage) {
      console.warn('localStorage not available');
      return false;
    }
    try {
      console.log('Writing inquiry list to localStorage:', list);
      console.log('Serialized inquiry list:', JSON.stringify(list));
      
      // Validate list is array
      if (!Array.isArray(list)) {
        console.error('writeList: list is not an array', list);
        return false;
      }
      
      var serialized = JSON.stringify(list);
      window.localStorage.setItem(STORAGE_KEY, serialized);
      
      // Verify write was successful
      var verification = window.localStorage.getItem(STORAGE_KEY);
      if (verification !== serialized) {
        console.error('localStorage write verification failed');
        return false;
      }
      
      console.log('Successfully wrote to localStorage:', verification);
      dispatchChange(list);
      return true;
    } catch (e) {
      console.error('Error writing inquiry list to localStorage:', e);
      // Check if it's a quota exceeded error
      if (e.name === 'QuotaExceededError' || e.code === 22) {
        console.error('localStorage quota exceeded');
        // Try to clear some space and retry
        try {
          // Remove old test keys
          window.localStorage.removeItem('test_key');
          window.localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
          console.log('Retry after clearing space succeeded');
          dispatchChange(list);
          return true;
        } catch (retryError) {
          console.error('Retry also failed:', retryError);
        }
      }
      return false;
    }
  }

  var GCInquiry = {
    STORAGE_KEY: STORAGE_KEY,

    /**
     * Get current inquiry list.
     * @returns {Array<{id:string,title:string,image:string,url:string,quantity:number}>}
     */
    getInquiryList: function () {
      return readList();
    },

    /**
     * Check whether a product ID is already in the inquiry list.
     * @param {string|number} productId
     * @returns {boolean}
     */
    isInInquiry: function (productId) {
      var id = String(productId);
      var list = readList();
      for (var i = 0; i < list.length; i++) {
        if (String(list[i].id) === id) {
          return true;
        }
      }
      return false;
    },

    /**
     * Add a product object to the inquiry list (if not present).
     * @param {{id:string|number,title?:string,image?:string,url?:string}} product
     */
    addToInquiry: function (product) {
      if (!product || !product.id) {
        console.error('addToInquiry: Invalid product object', product);
        return false;
      }
      
      var id = String(product.id);
      var list = readList();
      console.log('Adding product to inquiry:', product);
      console.log('Current inquiry list before adding:', list);
      
      // Check if already exists
      for (var i = 0; i < list.length; i++) {
        if (String(list[i].id) === id) {
          console.log('Product already in inquiry list:', id);
          return false;
        }
      }
      
      // Create new product entry
      var newProduct = {
        id: id,
        title: String(product.title || ''),
        image: String(product.image || ''),
        url: String(product.url || ''),
        quantity: sanitizeQuantity(product.quantity || 1)
      };
      
      // Create new array to avoid reference issues
      var newList = list.slice();
      newList.push(newProduct);
      
      console.log('New inquiry list after adding:', newList);
      
      // Write and verify
      var success = writeList(newList);
      if (success) {
        console.log('Successfully added product to inquiry:', newProduct);
      } else {
        console.error('Failed to add product to inquiry:', newProduct);
      }
      
      return success;
    },

    /**
     * Remove a product from the inquiry list by ID.
     * @param {string|number} productId
     */
    removeFromInquiry: function (productId) {
      console.log('Removing product ID from inquiry:', productId);
      var id = String(productId);
      var list = readList();
      var next = [];
      var found = false;
      
      for (var i = 0; i < list.length; i++) {
        if (String(list[i].id) !== id) {
          next.push(list[i]);
        } else {
          found = true;
        }
      }
      
      if (!found) {
        console.log('Product not found in inquiry list:', id);
        return false;
      }
      
      console.log('New inquiry list after removal:', next);
      var success = writeList(next);
      
      if (success) {
        console.log('Successfully removed product from inquiry:', id);
      } else {
        console.error('Failed to remove product from inquiry:', id);
      }
      
      return success;
    },

    /**
     * Update quantity for a product in the inquiry list.
     * @param {string|number} productId
     * @param {number} quantity
     */
    updateQuantity: function (productId, quantity) {
      var id = String(productId);
      var list = readList();
      var updated = false;
      var next = [];

      for (var i = 0; i < list.length; i++) {
        var item = list[i];
        if (String(item.id) === id) {
          item.quantity = sanitizeQuantity(quantity);
          updated = true;
        }
        next.push(item);
      }

      if (!updated) {
        console.warn('updateQuantity: product not found', id);
        return false;
      }

      return writeList(next);
    },

    /**
     * Clear the entire inquiry list.
     * Useful for debugging.
     */
    clearInquiry: function () {
      console.log('Clearing entire inquiry list');
      var success = writeList([]);
      if (success) {
        console.log('Successfully cleared inquiry list');
      } else {
        console.error('Failed to clear inquiry list');
      }
      return success;
    },

    /**
     * Debug function to check localStorage status.
     */
    debug: function () {
      console.log('=== GCInquiry Debug ===');
      console.log('localStorage available:', !!window.localStorage);
      if (window.localStorage) {
        try {
          var raw = window.localStorage.getItem(STORAGE_KEY);
          console.log('Raw value in localStorage:', raw);
          console.log('Parsed list:', readList());
          console.log('localStorage quota test...');
          var testKey = 'gc_test_' + Date.now();
          window.localStorage.setItem(testKey, 'test');
          window.localStorage.removeItem(testKey);
          console.log('localStorage quota test: OK');
        } catch (e) {
          console.error('localStorage error:', e);
        }
      }
      console.log('Current inquiry list length:', this.getInquiryList().length);
      console.log('=== End Debug ===');
    }
  };

  /**
   * Update visual state of a toggle button based on current inquiry list.
   */
  function updateButtonState(el) {
    var id = el.getAttribute('data-product-id');
    if (!id) {
      return;
    }
    var active = GCInquiry.isInInquiry(id);
    if (active) {
      el.classList.add('is-inquiry');
      el.setAttribute('aria-pressed', 'true');
    } else {
      el.classList.remove('is-inquiry');
      el.setAttribute('aria-pressed', 'false');
    }
  }

  /**
   * Handle click on a single inquiry toggle button.
   * Toggles presence of product in the list.
   */
  function handleButtonClick(el) {
    var id = el.getAttribute('data-product-id');
    if (!id) {
      return;
    }
    var product = {
      id: id,
      title: el.getAttribute('data-product-title') || '',
      image: el.getAttribute('data-product-image') || '',
      url: el.getAttribute('data-product-url') || ''
    };

    console.log('Is in inquiry before toggle:', GCInquiry.isInInquiry(id));

    if (GCInquiry.isInInquiry(id)) {
      GCInquiry.removeFromInquiry(id);
    } else {
      GCInquiry.addToInquiry(product);
    }
    updateButtonState(el);
  }

  /**
   * Bind delegated click handler and initialize button states on DOM ready.
   */
  function bindButtons() {
    var buttons = document.querySelectorAll('[data-gc-inquiry-button]');
    for (var i = 0; i < buttons.length; i++) {
      updateButtonState(buttons[i]);
    }

    document.addEventListener('click', function (event) {
      var target = event.target;
      if (!target) {
        return;
      }
      var button = target.closest ? target.closest('[data-gc-inquiry-button]') : null;
      if (!button) {
        return;
      }
      event.preventDefault();
      handleButtonClick(button);
    });

    window.addEventListener('inquiryListChanged', function () {
      var allButtons = document.querySelectorAll('[data-gc-inquiry-button]');
      for (var i = 0; i < allButtons.length; i++) {
        updateButtonState(allButtons[i]);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindButtons);
  } else {
    bindButtons();
  }

  // Expose API globally
  window.GCInquiry = GCInquiry;

  // Example button markup:
  // <button
  //   type="button"
  //   class="gcp-btn gcp-btn--ghost"
  //   data-gc-inquiry-button="1"
  //   data-product-id="123"
  //   data-product-title="Product Name"
  //   data-product-image="https://example.com/image.jpg"
  //   data-product-url="https://example.com/products/123">
  //   Für Beratung vormerken
  // </button>
})(window, document);

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


(function (window, document) {
  'use strict';

  var FORM_SELECTOR = '[data-gc-inquiry-form]';

  function getForms() {
    return Array.prototype.slice.call(document.querySelectorAll(FORM_SELECTOR));
  }

  function getProducts() {
    if (window.GCInquiry && typeof window.GCInquiry.getInquiryList === 'function') {
      return window.GCInquiry.getInquiryList();
    }
    return [];
  }

  function setMessage(form, text, isError) {
    var messageEl = form.querySelector('.gc-inquiry-form__message');
    if (!messageEl) {
      return;
    }
    messageEl.textContent = text || '';
    messageEl.classList.toggle('gc-inquiry-form__message--error', !!isError);
    messageEl.classList.toggle('gc-inquiry-form__message--success', !isError && !!text);
  }

  function disableForm(form, disabled) {
    var inputs = form.querySelectorAll('input, textarea, button');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].disabled = !!disabled;
    }
  }

  function handleSubmit(form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var ajaxUrl = form.getAttribute('data-ajax-url');
      if (!ajaxUrl) {
        return;
      }

      setMessage(form, '');

      var formData = new FormData(form);
      formData.append('products', JSON.stringify(getProducts()));

      // Disable after collecting data to avoid disabled inputs being dropped
      disableForm(form, true);

      fetch(ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      })
        .then(function (response) {
          if (!response.ok) {
            throw response;
          }
          return response.json();
        })
        .then(function (data) {
          if (data && data.success) {
            var successMessage = form.getAttribute('data-success-message') || '';
            setMessage(form, successMessage, false);
            form.classList.add('gc-inquiry-form--submitted');
          } else {
            var msg = data && data.data && data.data.message ? data.data.message : 'Fehler';
            setMessage(form, msg, true);
          }
        })
        .catch(function (err) {
          if (err && typeof err.json === 'function') {
            err.json().then(function (data) {
              var msg = data && data.data && data.data.message ? data.data.message : 'Fehler';
              setMessage(form, msg, true);
            });
          } else {
            setMessage(form, 'Es ist ein Fehler aufgetreten.', true);
          }
        })
        .finally(function () {
          disableForm(form, false);
        });
    });
  }

  function init() {
    getForms().forEach(function (form) {
      handleSubmit(form);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);

(function (window, document) {
  'use strict';

  var WIDGET_SELECTOR = '[data-gc-inquiry-list]';

  function getContainers() {
    return Array.prototype.slice.call(document.querySelectorAll(WIDGET_SELECTOR));
  }

  function normalizeQuantity(value) {
    var qty = parseInt(value, 10);
    return isNaN(qty) || qty < 1 ? 1 : qty;
  }

  function buildItem(product, container, index) {
    var li = document.createElement('li');
    li.className = 'gc-inquiry-list__item';
    li.setAttribute('data-product-id', product.id);

    var inner = document.createElement('div');
    inner.className = 'gc-inquiry-list__item-inner';

    var thumb = document.createElement('div');
    thumb.className = 'gc-inquiry-list__thumb';
    if (product.image) {
      var img = document.createElement('img');
      img.src = product.image;
      img.alt = product.title || '';
      thumb.appendChild(img);
    } else {
      var placeholder = document.createElement('div');
      placeholder.className = 'gc-inquiry-list__thumb-placeholder';
      placeholder.textContent = (product.title || '').charAt(0).toUpperCase() || '?';
      thumb.appendChild(placeholder);
    }

    var meta = document.createElement('div');
    meta.className = 'gc-inquiry-list__meta';
    if (product.url) {
      var link = document.createElement('a');
      link.className = 'gc-inquiry-list__title';
      link.href = product.url;
      link.textContent = product.title || '';
      meta.appendChild(link);
    } else {
      var title = document.createElement('span');
      title.className = 'gc-inquiry-list__title';
      title.textContent = product.title || '';
      meta.appendChild(title);
    }

    var actions = document.createElement('div');
    actions.className = 'gc-inquiry-list__actions';

    var quantityWrap = document.createElement('div');
    quantityWrap.className = 'gc-inquiry-list__quantity';
    var quantityId = 'gc-inquiry-qty-' + product.id + '-' + index;

    var quantityLabel = document.createElement('label');
    quantityLabel.className = 'gc-inquiry-list__quantity-label';
    quantityLabel.setAttribute('for', quantityId);
    quantityLabel.textContent = container.getAttribute('data-quantity-label') || 'Menge';

    var quantityInput = document.createElement('input');
    quantityInput.className = 'gc-inquiry-list__quantity-input';
    quantityInput.type = 'number';
    quantityInput.min = '1';
    quantityInput.inputMode = 'numeric';
    quantityInput.id = quantityId;
    quantityInput.value = normalizeQuantity(product.quantity);
    quantityInput.setAttribute('data-gc-inquiry-qty', 'true');
    quantityInput.setAttribute('data-product-id', product.id);

    quantityWrap.appendChild(quantityLabel);
    quantityWrap.appendChild(quantityInput);

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'gc-inquiry-list__remove';
    removeBtn.setAttribute('data-product-id', product.id);
    removeBtn.textContent = container.getAttribute('data-remove-label') || 'Entfernen';

    actions.appendChild(quantityWrap);
    actions.appendChild(removeBtn);

    inner.appendChild(thumb);
    inner.appendChild(meta);
    inner.appendChild(actions);
    li.appendChild(inner);

    return li;
  }

  function renderContainer(container) {
    if (!window.GCInquiry || typeof window.GCInquiry.getInquiryList !== 'function') {
      return;
    }

    var list = window.GCInquiry.getInquiryList();
    var itemsTarget = container.querySelector('[data-gc-inquiry-items]');
    var emptyEl = container.querySelector('[data-gc-inquiry-empty]');
    var headingEl = container.querySelector('[data-gc-inquiry-heading]');

    if (!itemsTarget) {
      return;
    }

    itemsTarget.innerHTML = '';

    var headingText = container.getAttribute('data-heading-text') || '';
    if (headingEl && headingText) {
      headingEl.textContent = headingText;
    }
    if (emptyEl) {
      var emptyText = container.getAttribute('data-empty-text') || emptyEl.textContent;
      emptyEl.textContent = emptyText;
    }

    if (Array.isArray(list) && list.length > 0) {
      list.forEach(function (product, index) {
        itemsTarget.appendChild(buildItem(product, container, index));
      });
      container.classList.add('gc-inquiry-list--has-items');
      container.classList.remove('gc-inquiry-list--empty');
    } else {
      container.classList.add('gc-inquiry-list--empty');
      container.classList.remove('gc-inquiry-list--has-items');
    }
  }

  function refreshAll() {
    getContainers().forEach(renderContainer);
  }

  function bindContainer(container) {
    if (container.__gcInquiryListBound) {
      return;
    }
    container.__gcInquiryListBound = true;

    container.addEventListener('click', function (event) {
      var target = event.target;
      if (!target) {
        return;
      }
      var removeBtn = target.closest ? target.closest('.gc-inquiry-list__remove') : null;
      if (removeBtn && container.contains(removeBtn)) {
        event.preventDefault();
        var productId = removeBtn.getAttribute('data-product-id');
        if (productId && window.GCInquiry && typeof window.GCInquiry.removeFromInquiry === 'function') {
          window.GCInquiry.removeFromInquiry(productId);
          renderContainer(container);
        }
      }
    });

    var handleQuantityChange = function (event) {
      var input = event.target;
      if (!input || input.getAttribute('data-gc-inquiry-qty') !== 'true') {
        return;
      }
      var productId = input.getAttribute('data-product-id');
      var quantity = normalizeQuantity(input.value);
      input.value = quantity;
      if (productId && window.GCInquiry && typeof window.GCInquiry.updateQuantity === 'function') {
        window.GCInquiry.updateQuantity(productId, quantity);
        renderContainer(container);
      }
    };

    container.addEventListener('change', handleQuantityChange);
    container.addEventListener('input', handleQuantityChange);
  }

  function init() {
    var containers = getContainers();
    containers.forEach(function (container) {
      bindContainer(container);
    });
    refreshAll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.addEventListener('inquiryListChanged', refreshAll);
})(window, document);

(function () {
  'use strict';

  function initGallery(gallery) {
    const mainImg  = gallery.querySelector('.gc-product-gallery__main-img');
    const thumbsEl = gallery.querySelector('.gc-product-gallery__thumbs');
    const current  = gallery.querySelector('.gc-product-gallery__counter-current');

    if (!mainImg || !thumbsEl) return;

    // Swiper für die Thumbnail-Reihe
    if (window.Swiper) {
      new window.Swiper(thumbsEl, {
        slidesPerView: 5,
        spaceBetween: 10,
        navigation: {
          prevEl: thumbsEl.querySelector('.swiper-button-prev'),
          nextEl: thumbsEl.querySelector('.swiper-button-next'),
        },
      });
    }

    // Click-Handler auf Buttons (innerhalb .swiper-slide)
    thumbsEl.querySelectorAll('.gc-product-gallery__thumb').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        const src   = thumb.dataset.src;
        const alt   = thumb.dataset.alt || '';
        const index = parseInt(thumb.dataset.index, 10);

        // Swap main image
        mainImg.classList.add('is-loading');
        mainImg.onload = function () {
          mainImg.classList.remove('is-loading');
        };
        mainImg.src = src; 
        mainImg.alt = alt;

        // Active state
        thumbsEl.querySelectorAll('.gc-product-gallery__thumb').forEach(function (t) {
          t.classList.remove('gc-product-gallery__thumb--active');
        });
        thumb.classList.add('gc-product-gallery__thumb--active');

        // Counter
        if (current) current.textContent = index + 1;
      });
    });
  }

  function init() {
    document.querySelectorAll('[data-gallery]').forEach(initGallery);
  }

  // Elementor frontend hook + plain DOMContentLoaded
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/gcp_product_gallery.default', function ($el) {
      initGallery($el[0]);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

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

//# sourceMappingURL=plugin.js.map
