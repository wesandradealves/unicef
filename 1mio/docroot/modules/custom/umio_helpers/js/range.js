(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.range = {
    attach: function (context, settings) {

      $(document).ready(function () {
        $(document, context).once('range').each( function () {
          $(".js-range-slider").ionRangeSlider({
            skin: 'round',
            prettify_separator: '.'
          });

          $(".js-range-slider").each(function () {
            var range = $(this);
            var value = range.val();
            var id = range.attr('id') + '_range';
            var min = range.attr('min');
            var max = range.attr('max');
            var inputHtml = "<input type='text' class='form-control range-slider-controls' id='" + id + "' value='" + value + "'/>";
            range.parent().prepend(inputHtml);
            instance = range.data("ionRangeSlider");
            $('#' + id).mask('##.###', {
              reverse: true,
              watchDataMask: true
            });
            $('#' + id).on('keyup', function (e) {
              var val = $(this).cleanVal();
              instance.update({
                from: val
              });
            }).keyup(function (e) {
              var val = $(this).cleanVal();
              if (val != "") {
                if (parseInt(val) < parseInt(min)) {
                  $(this).val(min);
                }
                if (parseInt(val) > parseInt(max)) {
                  $(this).val(max);
                }
                if (!typeof val == 'number') {
                  $(this).val(value);
                }
              }
              $(this).trigger('input');
            });

            $(this).on('change', function () {
              var val = $(this).prop("value");
              $('#' + id).val(val);
              $('#' + id).trigger('input');
            });
          })
        });
      });
    }
  }
} (jQuery, Drupal, drupalSettings));
