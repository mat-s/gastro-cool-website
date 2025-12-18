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
