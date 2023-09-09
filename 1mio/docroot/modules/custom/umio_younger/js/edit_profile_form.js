(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.edit_profile_form = {
    attach: function (context, settings) {
      $(document).once('edit_profile_form').ready(function () {
        // Create the event to call the function when chaging the value
        // of a checkbox.
        let selector = '[id^="edit-field-user-professional-traject"]';
        $(document).on('change', selector, function () {
          hideShowEndDate($(this), 'click');
        });
        // Start the event on all those checkboxes on the form.
        if ($(selector).length) {
          $(selector).map(function (key, object) {
            hideShowEndDate($(this), 'load');
          });
        }
      });
      $('edit_profile_form').ready(function () {
        $('[type="date"]').on('blur', function () {
          if (this.value != '') {
            var dateField = this.value;
            var arrDateField = dateField.split("-");
            var dateField_concat = new Date(arrDateField[0], arrDateField[1] - 1, arrDateField[2]);
            var todayDate = new Date();
            if ($('#' + this.id + '-error')) {
              $('[for="' + this.name + '"]').remove();
            }
            if (dateField_concat >= todayDate) {
              let labelText = Drupal.t("This date cannot be later than today.");
              let label = $("<label>").attr({id: this.id + '-error', for: this.name, class: 'error'}).text(labelText);
              $(label).insertAfter(this);
              $(this).val('');
            }
          }
        });
      });
    }
  }
  function hideShowEndDate(check, event) {
    if (check[0].name) {
      // Once we already get the checkbox field name we'll access the "end" field
      // by changing the word 'current' by 'end' and "[value]" by "[0][value][date]".
      let endPathName = ((check[0].name)
                          .replace("current", "end"))
                          .replace("[value]", "[0][value][date]");

      // And once we get the end field path we can access his wrapper by
      // converting all those '[', ']' by dashes and changing the access
      // the 0-value for the -wrapper access.
      let endPathWrapper = 'edit-' + ((endPathName)
                            .replaceAll('][', '-')
                            .replaceAll('[', '-')
                            .replaceAll(']', '-')
                            .replaceAll('_', '-')
                            .replace('-0-value-date-', '-wrapper'));
      // Check if the checkbox is actually checked.
      if (check.is(':checked')) {
        // If it is, we show the field wrapper and mark as required.
        $("[name='" + endPathName + "']").attr('required', '');
        $("[id^='" + endPathWrapper + "']").hide();
        $("[id^='" + endPathWrapper + "']").children('h4').removeClass("form-required");
      }
      else {
        // If it is not, we hide the field wrapper and remove the required mark.
        $("[name='" + endPathName + "']").attr('required', 'required');
        $("[id^='" + endPathWrapper + "']").show();
        $("[id^='" + endPathWrapper + "']").children('h4').addClass("form-required");
        // If the function got called from a click event we clean the value when it hides.
        if (event == 'click') {
          $("[name='" + endPathName + "']").val('');
        }
      }
    }
  }
} (jQuery, Drupal, drupalSettings));
