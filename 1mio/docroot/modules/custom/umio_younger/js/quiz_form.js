(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.edit_profile_form = {
    attach:function (context, settings) {
      $(document).once('quiz_form').ready(function () {
        $('.js-form-item label').on('click', function (e) {
          let checkeds = ($('.form-check-input:checked').length);
          if (checkeds >= 4 && (!$(this).parent().find('input').is(':checked'))) {
            e.preventDefault();
            e.stopPropagation();
            return FALSE;
          }
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
