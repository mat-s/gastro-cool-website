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
