(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.partner_cities_behaviour = {
    attach: function (context, settings) {

      const changeFieldsExhibition = () => {
        let selectedRegionType = $('#edit-parent-target-id-1').val();

        switch (selectedRegionType) {
          case '366':
            $('#edit-parent-target-id').parent().addClass('d-none');
            $('#edit-parent-target-region-1').parent().addClass('d-none');
            $('#edit-parent-target-region-0').parent().removeClass('d-none');
            break;

          case '416':
            $('#edit-parent-target-id').parent().addClass('d-none');
            $('#edit-parent-target-region-0').parent().addClass('d-none');
            $('#edit-parent-target-region-1').parent().removeClass('d-none');
            break;

          case 'All':
            $('#edit-parent-target-id').parent().removeClass('d-none');
            $('#edit-parent-target-region-0').parent().addClass('d-none');
            $('#edit-parent-target-region-1').parent().addClass('d-none');
            break;
        }

      }

      $(document).ready(function () {
        $(document).once('partner_cities_behaviour').each( function () {

          $('#edit-parent-target-id-1').change(function () {
            changeFieldsExhibition();

            $('#edit-parent-target-id').val('All').change();
            $('#edit-parent-target-region-1').val('All').change();
            $('#edit-parent-target-region-0').val('All').change();
          });

          $('#edit-parent-target-id, #edit-parent-target-region-0, #edit-parent-target-region-1').change(function () {
            let selectedOption = $(this).find('option:selected')[0].value;
            $('#edit-parent-target-id').val(selectedOption);
          });

          changeFieldsExhibition();
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
