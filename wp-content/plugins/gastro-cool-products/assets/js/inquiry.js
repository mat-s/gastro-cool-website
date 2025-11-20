(function (window, document) {
  'use strict';

  var STORAGE_KEY = 'gastrocool_inquiry_list';

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
              url: String(item.url || '')
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
          url: String(parsed.url || '')
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
     * @returns {Array<{id:string,title:string,image:string,url:string}>}
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
        url: String(product.url || '')
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
