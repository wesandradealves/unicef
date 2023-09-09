(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.courseForm = {
    attach: function (context, settings) {
      $(document, context).once('courseForm').each( function () {
        $(document).ready(function () {
          $('select[name="field_course_type"]').on('change', function () {
            if ($(this).val() === 'Presencial') {
              var stateFormItemSelector = '.js-form-item-field-course-location-0-subform-field-paragraph-address-0-address-administrative-area';
              $(stateFormItemSelector + ' label').addClass('js-form-required form-required');
              $(stateFormItemSelector + ' select').addClass('required');

              var cityFormItemSelector = '.js-form-item-field-course-location-0-subform-field-paragraph-address-0-address-locality';
              $(cityFormItemSelector + ' label').addClass('js-form-required form-required');
            }
          });
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
