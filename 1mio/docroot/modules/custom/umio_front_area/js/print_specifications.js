(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.print_specifications = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('print_specifications').each( function () {
          if (navigator.userAgent.indexOf('Chrome') > 0) {
            $('html').addClass('chrome-browser-specifications');
          }
        });
      });
    }
  }
} (jQuery, Drupal, drupalSettings));
