(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.scrollAnchor = {
    attach: function (context, settings) {
      $(window).once('scrollAnchor').on('load', function(e) {
      
        let root = $('html, body');
        function smoothScroll (element) {
          root.animate({
            scrollTop: $(element).offset().top
          }, 500);
        }

        // When loading the URL with an anchor.
        if (window.location.hash && $(window.location.hash).length) {
          smoothScroll(window.location.hash);
        }

        // When clicking in a anchor link.
        $(window).on('hashchange', function(e) {
          e.preventDefault();
          e.stopPropagation();
          smoothScroll(window.location.hash);
          return FALSE;
        });

      });
    }
  }

} (jQuery, Drupal, drupalSettings));
