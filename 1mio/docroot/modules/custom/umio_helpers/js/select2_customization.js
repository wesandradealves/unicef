(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.select2Customization = {
    attach: function (context, settings) {

      $(document, context).once('select2Customization').each( function () {
        $(document).ready(function () {
          $('select.select2').select2({
            allowClear: true,
            placeholder :'Selecione..',
            theme: 'bootstrap-5',
            language: 'pt-BR',
          });
          $('select.select2-without-allow-clear').select2({
            theme: 'bootstrap-5',
            language: 'pt-BR',
          });

          $("select.select2-without-search").select2({
            theme: 'bootstrap-5',
            language: 'pt-BR',
            minimumResultsForSearch: Infinity
          });
        })

        $("select").on("select2:close", function (e) {
          $(this).valid();
        });

        jQuery.validator.setDefaults({
          highlight: function ( element, errorClass, validClass ) {
            if ( element.type === "radio" ) {
              this.findByName( element.name ).addClass( errorClass ).removeClass( validClass );
              this.findByName( element.name ).parent().addClass( errorClass ).removeClass( validClass );
            } else {
              $( element ).addClass( errorClass ).removeClass( validClass );
              $( element ).parent().addClass( errorClass ).removeClass( validClass );
            }
          },
          unhighlight: function ( element, errorClass, validClass ) {
            if ( element.type === "radio" ) {
              this.findByName( element.name ).removeClass( errorClass ).addClass( validClass );
              this.findByName( element.name ).parent().removeClass( errorClass ).addClass( validClass );
            } else {
              $( element ).removeClass( errorClass ).addClass( validClass );
              $( element ).parent().removeClass( errorClass ).addClass( validClass );
            }
          }
        })
      });

    }
  }

} (jQuery, Drupal, drupalSettings));
