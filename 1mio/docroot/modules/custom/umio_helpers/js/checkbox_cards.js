(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.checkboxCards = {
    attach: function (context, settings) {

      $(document, context).once('checkboxCards').each( function () {

        $(document).ready(function () {
          $('.checkbox_cards').find('.form-item').each(function () {
            $(this).find('label').addRandomBorderClass();
          })
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
