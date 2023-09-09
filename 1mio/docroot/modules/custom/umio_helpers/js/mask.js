(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.mask = {
    attach: function (context, settings) {
      $(document, context).once('mask').each( function () {
        $(document).ready(function () {
          addMasks();
          $(document).ajaxComplete(function () {
            addMasks();
          });

        });
        function addMasks() {
          $('.cnpj-mask').mask('99.999.999/9999-99');
          $('.cnpj-mask').rules('add', {
            cnpj: true
          });
          $('.cnpj-mask').attr("inputmode", "numeric");

          $('.cpf-mask').mask('999.999.999-99');
          $('.cpf-mask').rules('add', {
            cpf: true
          });
          $('.cpf-mask').attr("inputmode", "numeric");

          $('.telephone-mask').mask("(99) 9999-9999");
          $('.telephone-mask').rules('add', {
            telephone: true
          });
          $('.telephone-mask').attr("inputmode", "numeric");

          $('.telephone-extension-mask').mask("9999");
          $('.telephone-extension-mask').attr("inputmode", "numeric");

          $('.phone-mask').mask("(99) 99999-9999");
          $('.phone-mask').rules('add', {
            phone: true
          });
          $('.phone-mask').attr("inputmode", "numeric");

          $('.postal-code-mask').mask("99999-999");
          $('.postal-code-mask').rules('add', {
            postalCode: true
          });
          $('.postal-code-mask').attr("inputmode", "numeric");
        }
        const reserved_keys = [0, 1, 4, 8, 9, 28, 29, 30, 31, 127, 1];
        $('input[inputmode="numeric"]').keypress(function (e) {
          if($.inArray(e.which, reserved_keys) < 0 && ((e.which < 48) || (e.which > 57)))
          {
            e.preventDefault();
            return FALSE;
          }
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
