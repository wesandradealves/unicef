(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.addressCustomization = {
    attach: function (context, settings) {
      $(document).ready(function () {
        var administrativeSelector = '.js-form-item select.administrative-area';
        var localityInputSelector = '.js-form-item input.locality';
        var localitySelectSelector = '.js-form-item select.locality';

        $(document, context).once('addressCustomization').each( function () {
          // Enable the city input when change state input
          $(localityInputSelector).attr('disabled', true);
          $(administrativeSelector).on('change', function () {
            if ($(this).val()) {
              $(localityInputSelector).attr('disabled', FALSE);
            } else {
              $(localityInputSelector).attr('disabled', true);
            }
          });
        });

        $(document).ajaxComplete(function () {
          if ($(administrativeSelector).val()) {
            $(localityInputSelector).attr('disabled', FALSE);
          } else {
            $(localityInputSelector).attr('disabled', true);
          }
        });

        // Adding select2 in State and City selects
        addSelect2(administrativeSelector);
        addSelect2(localitySelectSelector);

        function addSelect2(selector) {
          $(selector).select2({
            theme: "bootstrap-5",
            language: {
              noResults: function () {
                return "Nenhum resultado encontrado";
              }
            }
          });
        }
      });
    }
  }
} (jQuery, Drupal, drupalSettings));
