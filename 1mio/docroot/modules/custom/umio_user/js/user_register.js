(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.userRegister = {
    attach: function (context, settings) {
      $(document, context).once('userRegister').each( function () {
        $(document).ready(function () {
          $(document).keypress(function (event) {
            if (event.keyCode == '13') {
              event.preventDefault();
              $('input[data-drupal-selector="edit-next"]').trigger('click');
            }
          });
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
