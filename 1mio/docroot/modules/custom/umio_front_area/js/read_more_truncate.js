(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.print_specifications = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('readMoreTruncate').each( function () {
          let viewPortWidth = $(document).width();
          $('.read-more--container').each(function () {
            let text = $(this).find('.read-more--text');
            let link = $(this).find('.read-more--link');
            let height = text.find('.field__item').height();

            if (viewPortWidth > 576 && height >= 72 ||
                viewPortWidth <= 576 && height >= 120) {
              link.show();
            } else {
              link.hide();
            }
          });
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
