(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.registerCompany = {
    attach: function (context, settings) {
      $(document, context).once('registerCompany').each( function () {
        $(document).ready(function () {
          if ($('#edit-field-company-address-administrativearea').length) {
            $("#edit-field-company-address-administrativearea").trigger("change");
          }
          $(document).keypress(function (event) {
            if (event.keyCode == '13') {
              event.preventDefault();
              $('input[data-drupal-selector="edit-next"]').trigger('click');
            }
          });
          $(".form-select").select2({
            theme: "bootstrap-5",
            minimumResultsForSearch: -1,
            language: {
              noResults: function () {
                return Drupal.t("No results found");
              }
            }
          });
        });
      });
    }
  }
} (jQuery, Drupal, drupalSettings));
