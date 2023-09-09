(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.vacancyDisplay = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('vacancyDisplay').each( function () {
          var url = new URLSearchParams(location.search);
          if (url.has("modal") && url.get("modal") == 'vacancy_created') {
            var modal = new bootstrap.Modal(document.getElementById('modal-vacancy-created'));
            modal.toggle();
          }
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
