(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.customValidator = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('customValidator').each( function () {
          jQuery.validator.addMethod("greaterthantoday", function (value, element) {
            var valueDate = new Date(value);
            var today = new Date();
            if (valueDate >= today) {
              return true;
            }
            return FALSE;
          }, jQuery.validator.format("Data deve ser maior do que data atual."));

          jQuery.validator.addMethod('lessThan', function (value, element, param) {
            return this.optional(element) || !$(param).val() || parseInt(value) < parseInt($(param).val());
          }, "Informe um valor menor");

          jQuery.validator.addMethod('greaterThan', function (value, element, param) {
            return this.optional(element) || !$(param).val() || parseInt(value) > parseInt($(param).val());
          }, "Informe um valor maior");

          jQuery.validator.addMethod('cnpj', function (value, element, param) {
            var regex = /^[0-9]{2}\.?[0-9]{3}\.?[0-9]{3}\/?[0-9]{4}\-?[0-9]{2}/;
            return this.optional(element) || regex.test(value);
          }, "Informe um CNPJ válido");

          jQuery.validator.addMethod('cpf', function (value, element, param) {
            var regex = /^\d{3}\.\d{3}\.\d{3}\-\d{2}$/
            return this.optional(element) || regex.test(value);
          }, "Informe um CPF válido");

          jQuery.validator.addMethod('telephone', function (value, element, param) {
            var regex = /^\([0-9]{2}\) [0-9]{4}\-[0-9]{4}/;
            return this.optional(element) || regex.test(value);
          }, "Informe um telefone válido");

          jQuery.validator.addMethod('phone', function (value, element, param) {
            var regex = /^\([0-9]{2}\) [0-9]{5}\-[0-9]{4}/;
            return this.optional(element) || regex.test(value);
          }, "Informe um celular válido");

          jQuery.validator.addMethod('postalCode', function (value, element, param) {
            var regex = /^[0-9]{5}\-[0-9]{3}/;
            return this.optional(element) || regex.test(value);
          }, "Informe um CEP válido");
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
