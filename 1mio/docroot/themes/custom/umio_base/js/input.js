(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.input = {
    attach: function (context, settings) {
      $(document, context).once('input').each( function () {
        $(document).ready(function () {
          $('input[type="number"]').attr('inputmode', 'numeric');

          const reserved_keys = [0, 1, 4, 8, 9, 28, 29, 30, 31, 127, 1];
          $('input[type="number"]').keypress(function (e) {
            if ($.inArray(e.which, reserved_keys) < 0 && ((e.which < 48) || (e.which > 57))) {
              e.preventDefault();
              return FALSE;
            }
          });
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
