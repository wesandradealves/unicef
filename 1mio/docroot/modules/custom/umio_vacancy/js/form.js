(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.createVacancyForm = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('createVacancyForm').each( function () {

          $('#edit-field-vacancy-salary-options--wrapper > legend > span').addClass('form-required');
          $('#edit-field-vacancy-salary-min-wrapper > div > label').removeClass('form-required');
          $('#edit-field-vacancy-salary-max-wrapper > div > label').removeClass('form-required');

          handleDecentWorkModal();
          validateClosingDate();
          validateMaxMinSalary();

          $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').attr('type', 'numeric');
          $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').removeAttr('step');
          $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').mask("#.###,##", {reverse: true});
          $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').attr("inputmode", "numeric");

          $("#node-vacancy-form, #node-vacancy-edit-form").submit(function () {
            handleTurnNumericToNumber();
          });

          $(window).on("beforeunload", function (e) {
            handleTurnNumericToNumber();
          });

          function handleTurnNumericToNumber() {
            $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').val(function (index, value) {
              return numberToNumericValue(value);
            });
            $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').attr('type', 'number');
            $('#edit-field-vacancy-salary-0-value, #edit-field-vacancy-salary-min-0-value, #edit-field-vacancy-salary-max-0-value').attr('step', '0.01');
          }

          function numberToNumericValue(value) {
            if (!value) {
              return 0.0;
            }

            let current = value;
            current = value.replace(".", "").replace(",", ".");
            return parseFloat(current);
          }

          function handleDecentWorkModal() {
            var decentWorkFormsSelector = [
              '#node-vacancy-form',
              '#node-vacancy-edit-form',
              '#vacancy-batch-import'
            ];
            var decentWork = new bootstrap.Modal(document.getElementById('modal-vacancy-decent-work'));
            decentWorkFormsSelector.forEach(element => {
              $(element + ' #edit-decent-work').on('click', function (e) {
                e.preventDefault();
                decentWork.toggle();
              });
              $(element + ' #modal-vacancy-decent-work .js-btn-submit').on('click', function (e) {
                decentWork.toggle();
                $(element + ' #edit-submit').click();
              });
            });
          }

          function validateClosingDate() {
            $('#edit-field-vacancy-closing-date-0-value-date').rules('add', {
              greaterthantoday : true
            });
            $('#edit-field-vacancy-closing-date-0-value-date').removeAttr('title');
          }

          function validateMaxMinSalary() {
            $('#edit-field-vacancy-salary-min-0-value').rules('add', {
              lessThan: '#edit-field-vacancy-salary-max-0-value'
            });
            $('#edit-field-vacancy-salary-max-0-value').rules('add', {
              greaterThan: '#edit-field-vacancy-salary-min-0-value'
            });
          }
        });
      });

    }
  }

} (jQuery, Drupal, drupalSettings));
