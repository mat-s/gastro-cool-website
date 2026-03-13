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
