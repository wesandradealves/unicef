(function ($, Drupal, drupalSettings) {
  var route = 0;
  Drupal.behaviors.onSelectButton = {
    attach: function (context, settings) {

      $(document, context).once('onSelectButton').each( function () {
        $(document).ready(function () {

          $('#button-individual').on('click', function (e) {
            $(this).addClass('btn-active').removeClass('btn-inactive');
            $('#button-lote').removeClass('btn-active').addClass('btn-inactive');
            $('#btn-submit').removeAttr('disabled');
            $('#btn-submit').attr('formaction', '/node/add/vacancy');
            route = 1;
          });

          $('#button-lote').on('click', function (e) {
            $(this).addClass('btn-active').removeClass('btn-inactive');
            $('#button-individual').removeClass('btn-active').addClass('btn-inactive');
            $('#btn-submit').removeAttr('disabled');
            $('#btn-submit').attr('formaction', '/admin/vacancy/batch-import');
            route = 2;
          });

          $('#btn-submit').on('click', function (e) {
            if (route == 0) {
              e.preventDefault();
              return 0;
            }
          });
        });
      });
    }
  }
} (jQuery, Drupal, drupalSettings));
