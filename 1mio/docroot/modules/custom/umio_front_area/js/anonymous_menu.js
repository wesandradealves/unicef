(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.anonymousMenu = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('anonymousMenu').each( function () {

          // Add Boostrap offcanvas feature to the container (sidebar).
          var myOffcanvas = $('#sidebar--menu', context);
          var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas);

          $('button.offcanvas-show', context).click(function (e) {
            // Toggle the sidebar.
            bsOffcanvas.toggle();

            const menuCanvas = $('.umio-side-bar-container div.offcanvas-body', context);
            // If the menu wasn't built yet, creates it.
            if (menuCanvas.html() == "") {
              const list = $('ul.landing-page-anchors', context).clone();
              list
                .removeClass('landing-page-anchors')
                .find('li').css('margin-left', 0);
              menuCanvas.html(list);

              // Close sidebar when clicking on a anchor.
              $('.umio-side-bar-container a', context).once('sidebarToggle').click(function () {
                bsOffcanvas.toggle();
              });
            }
          });
        });
      });
    }
  }
} (jQuery, Drupal, drupalSettings));
