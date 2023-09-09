(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.mask = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $("input[name='field_company_cnpj_value']").mask('00.000.000/0000-00');
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
