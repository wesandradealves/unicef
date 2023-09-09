(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.cityManagerRegister = {
    attach: function (context, settings) {
      const isStampSelector = "select[name='field_company_unicef_stamp']"
      const institutionTypeSelector = "select[name='field_institution_type']"
      // Region (when stamp = yes); state and city dropdown.
      const regionDropdownSelector = "select[name='field_company_region']";
      const stateDropdownSelector = "select[name='companyAdministrativeArea']";
      const cityDropdownSelector = "select[name='companyLocality']";
      // Sidebar step related to commitment terms.
      const termsStepSelector = '.by-step-progress-bar li:last-of-type';

      $(document, context).once('cityManagerRegister').each( function () {
        $(document).ready(function () {

          // Add visual indicators that a field is required.
          function addRequiredMark(obj) {
            if (!obj.length) {
              return;
            }
            const isRequired = obj.is(':required');
            const hasRequiredClasses = obj.hasClass('form-required');
            if (isRequired && !hasRequiredClasses) {
              obj.parent().find('label').addClass('js-form-required form-required');
              obj.addClass('js-form-required form-required')
            }
            else if (!isRequired && hasRequiredClasses) {
              obj.parent().find('label').removeClass('js-form-required form-required');
              obj.removeClass('js-form-required form-required')
            }
          }

          function clearValues(obj) {
            obj.find("option[value!='']").remove();
          }

          $(stateDropdownSelector).val('');
          // Stamp cities doesn't need to upload commitment terms.
          if ($(isStampSelector).val() === '1') {
            $(termsStepSelector).hide();
            if ($(regionDropdownSelector).val() !== '') {
              // Load states based on pre-selected region.
              $(regionDropdownSelector).trigger('change');
            }
          }
          else if ($(isStampSelector).val() === '0' && $(institutionTypeSelector).val() !== '') {
            $(institutionTypeSelector).trigger('change');
          }

          $(isStampSelector).on('change', function () {
            if ($(this).val() === '1') {
              $(termsStepSelector).hide();
            }
            else {
              $(termsStepSelector).show();
            }
            // Remove all values except empty index ("Select...").
            clearValues($(cityDropdownSelector));

            // Reload cities list based on the selected state.
            if ($(this).val() !== '') {
              $(stateDropdownSelector).trigger('change');
            }
          });

          $(regionDropdownSelector).on('change', function () {
            // Remove all values except the empty index ("Select...").
            clearValues($(cityDropdownSelector));
            clearValues($(stateDropdownSelector));
          });

          $(institutionTypeSelector).on('change', function () {
            if ($(this).val() === 'state') {
              $('#form-item-administrative-area').removeClass('col-4');
              $('#form-item-locality-company').hide();
            }
            else {
              $('#form-item-administrative-area').addClass('col-4');
              $('#form-item-locality-company').show();
            }
          });

          $(document).ajaxComplete(function (event, request, settings) {
            let isStamp = $(isStampSelector).val();
            if (isStamp === '') {
              $('#form-item-administrative-area').hide();
              // If isStamp is empty, there's no need for additional logic.
              return;
            }
            // Checks for triggering element in order to avoid infinite loops0.
            let triggeringElement = settings.extraData._triggering_element_name;
            if (triggeringElement != 'companyAdministrativeArea') {
              // Reload cities list based on the selected state.
              $(stateDropdownSelector).trigger('change');
            }
            if (isStamp === '0' && $(institutionTypeSelector).val() === 'state') {
              // Hide city dropdown.
              $('#form-item-administrative-area').removeClass('col-4');
              $('#form-item-locality-company').hide();
            }
            else {
              // Display city dropdown and check for "required mark".
              $('#form-item-administrative-area').addClass('col-4');
              $('#form-item-locality-company').show();
              addRequiredMark($(cityDropdownSelector));
            }
          });
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
