(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.edit_profile_form = {
    attach:function (context, settings) {
      $(document).once('public_profile_form').ready(function () {
        $('.form-check-input:checked').each(function () {
          $(this).parent().addClass('active-border');
        });

        $(".form-check-input").click( function () {
          if ($(this).prop('checked') == true) {
            $(this).parent().addClass('active-border');
          }
          else {
            $(this).parent().removeClass('active-border');
          }
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
