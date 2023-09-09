(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.register_young_form = {
    attach: function (context, settings) {
      $(document).once('register_young_form').ready(function () {
        $('[data-drupal-selector="edit-field-user-birth-date"]').blur(function () {
          let date = new Date($(this)[0].value);
          if (date.getTime() != NaN) {
            //If it is a valid date we calculate the age.
            let birthDate = new Date(date);
            let dateDiff = Date.now() - birthDate;
            let ageDiff = new Date(dateDiff);
            let age = Math.abs(ageDiff.getFullYear() - 1970);
            hideShowCheckbox(age);
          }
        });
        //Hide the checkbox on form construction.
        hideShowCheckbox(18);
      });
    }
  }
  function hideShowCheckbox(age) {
    let fieldWrapper = '[class*="form-item-field-cb-user-birth-date"]';
    let fieldName = '[data-drupal-selector="edit-field-cb-user-birth-date"]';
    if (age >= 18) {
      // If it is, we show the field wrapper and mark as required.
      $(fieldName).attr('required', '');
      $(fieldWrapper).addClass('d-none');
      $(fieldWrapper).removeClass("form-required");
    }
    else {
      // If it is not, we hide the field wrapper and remove the required mark.
      $(fieldName).attr('required', 'required');
      $(fieldWrapper).removeClass('d-none');
      $(fieldWrapper).addClass("form-required");
    }
  }
} (jQuery, Drupal, drupalSettings));
