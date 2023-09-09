(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.filters = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document).once('filters').each( function () {
          var screen = $( window ).width();
          var _opportunity_type = 'All';
          var el  = $('#edit-umio-feeds-opportunities-type').closest('.filters--container').find('.js-form-item-umio__feeds__opportunity-model');

          window.addEventListener('resize', function(event) {
            screen = window.innerWidth
            if($(el).length && screen >= 768) {
              $(el)[0].style.width = _opportunity_type == 'vacancy' || _opportunity_type == 'All' ? '50%' : '100%';
            } else if($(el).length && screen <= 768) {
              $(el)[0].style.width = '100%';
            }            
          }, true);  

          var filter_fulltext = $('#edit-search-api-fulltext').val();
          var filter_feeds_opportunities_type = $('#edit-umio-feeds-opportunities-type').val();
          var filter_feeds_opportunities = $('#edit-umio-feeds-opportunities').val();
          var filter_feeds_opportunity_model = $('#edit-umio-feeds-opportunity-model').val();
          var filter_feeds_opportunity_locality = $('#edit-umio-feeds-opportunity-locality').val();

          if (filter_fulltext != "" || filter_feeds_opportunities_type != 'All' || filter_feeds_opportunities != 'All' || filter_feeds_opportunity_model != 'All' || filter_feeds_opportunity_locality != 'All' ) {
            $('#edit-submit-feeds-jovens').trigger('click');
          }

          // Set trigger to the customized fields;
          $('#edit-umio-feeds-opportunity-locality').change(function () {
            // Get the value
            let opportunity_type = $(this).val();
            let filter_type = $('#edit-umio-feeds-opportunities-type').val();

            opportunity_type = $(this)[0].selectedOptions[0].innerText;       

            // Try set the value to the fields.
            if (opportunity_type != 'All' && opportunity_type != '- Qualquer -') {
              opportunity_type = opportunity_type.split('-');

              let administrative_area = opportunity_type[1].trim();
              let locality = opportunity_type[0].trim();

              $("#edit-administrative-area-vacancy-uf").val(administrative_area).change();
              $("#edit-locality-city-vacancy").val(locality).change();

              switch (filter_type) {
                case 'vacancy':
                  $("#edit-field-vacancy-state").val(administrative_area).change();
                  $("#edit-field-vacancy-city").val(locality).change();

                  $("#edit-administrative-area-course-uf").val('').change();
                  $("#edit-locality-city-course").val('').change();     
                  
                  break;
                case 'course':
                  $("#edit-administrative-area-course-uf").val(administrative_area).change();
                  $("#edit-locality-city-course").val(locality).change();

                  $("#edit-field-vacancy-state").val('').change();
                  $("#edit-field-vacancy-city").val('').change();         
                  break;
                default:
                  break;
              }                   
            } else {
              $("#edit-administrative-area-course-uf").val('').change();
              $("#edit-locality-city-course").val('').change();
              $("#edit-administrative-area-vacancy-uf").val('').change();
              $("#edit-locality-city-vacancy").val('').change();
              $("#edit-field-vacancy-state").val('').change();
              $("#edit-field-vacancy-city").val('').change();               
            }
          });

          $('#edit-umio-feeds-opportunities, #edit-umio-feeds-opportunities-course, #edit-umio-feeds-opportunities-vacancy').on('select2:select', function (e) {
            let specific_opportunity_type = this.value;

            // Try set the value to the fields.
            if ($("#edit-field-vacancy-type > option[value=" + specific_opportunity_type + "]").length) {
              $("#edit-field-vacancy-type").val(specific_opportunity_type).change();
            }

            if ($("#edit-field-course-type > option[value=" + specific_opportunity_type + "]").length) {
              $("#edit-field-course-type").val(specific_opportunity_type).change();
            }

          });      

          // Set trigger to the customized fields;
          $('#edit-umio-feeds-opportunities-type').change(function () {
            $("#edit-field-vacancy-state").val('').change();
            $("#edit-field-vacancy-city").val('').change();  
            $('#edit-umio-feeds-opportunity-locality').val('All').change();

            _opportunity_type = this.value;

            $("#edit-field-vacancy-type").val('All');
            $("#edit-field-course-type").val('All');

            let field = $("#edit-umio-feeds-opportunity-locality")[0];

            field.closest('div').style.display = _opportunity_type == 'vacancy' || _opportunity_type == 'All' ? 'block' : 'none';

            if($(el).length && screen >= 768) {
              $(el)[0].style.width = _opportunity_type == 'vacancy' || _opportunity_type == 'All' ? '50%' : '100%';
            } else if($(el).length && screen <= 768) {
              $(el)[0].style.width = '100%';
            }

            $('.form-item-umio__feeds__opportunities, .form-item-umio__feeds__opportunities-course, .form-item-umio__feeds__opportunities-vacancy').addClass('d-none');
            switch (_opportunity_type) {
              case 'All':
                $('#edit-type').val('All');
                $('.form-item-umio__feeds__opportunities').removeClass('d-none');
                $('#edit-umio-feeds-opportunities-course, #edit-umio-feeds-opportunities-vacancy').select2().val("All").trigger("change");
                break;

              case 'course':
                $('#edit-type').val('1');
                $('.form-item-umio__feeds__opportunities-course').removeClass('d-none');
                $('#edit-umio-feeds-opportunities, #edit-umio-feeds-opportunities-vacancy').select2().val("All").trigger("change");                
                break;

              case 'vacancy':
                $('#edit-type').val('2');
                $('.form-item-umio__feeds__opportunities-vacancy').removeClass('d-none');
                $('#edit-umio-feeds-opportunities, #edit-umio-feeds-opportunities-course').select2().val("All").trigger("change");
                break;
            }
          });

          $('#edit-umio-feeds-opportunity-model').change(function () {
             // Get the value
             let opportunity_type = $(this).val();

             // Set the value pair by the field values.
             switch (opportunity_type) {
                case 'All':
                  value_custom_vacancy = 'All';
                  value_custom_course = 'All';
                  break;

                case 'present':
                  value_custom_vacancy = opportunity_type;
                  value_custom_course = 'Presencial';
                  break;

                case 'remote':
                  value_custom_vacancy = opportunity_type;
                  value_custom_course = 'Online';
                  break;

                case 'hybrid':
                  value_custom_vacancy = opportunity_type;
                  value_custom_course = 'Hibrido';
                  break;
              }

            // Try set the value to the fields.
            if ( $("#edit-field-vacancy-job-model > option[value=" + value_custom_vacancy + "]").length) {
              $("#edit-field-vacancy-job-model").val(value_custom_vacancy).change();
            } else {
              $("#edit-field-vacancy-job-model").val('All').change();
            }
            if ($("#edit-field-course-model > option[value=" + value_custom_course + "]").length) {
              $("#edit-field-course-model").val(value_custom_course).change();
            } else {
              $("#edit-field-course-model").val('All').change();
            }
          });

          // Make some front styles.
          $('.js-filter--open-button').removeClass('d-none');

          if ($('div.umio-card--grid-container > article').length > 0) {
            $('.js-filter--print-button').removeClass('d-none');
          }

          $('.js-filter--open-button').click(function () {
            $('.js-filters--container').removeClass('d-none');
            if ($('.filter--open-button--is-filtered').length) {
              $('.filter--clear-button').removeClass('d-none');
              $('.filter--clear-button-outside').addClass('d-none');
            }
            $(this).addClass('d-none');
          });

          $('.js-filter--close-button').click(function () {
            $('.js-filters--container').addClass('d-none');
            $('.js-filter--open-button').removeClass('d-none');
            if ($('.filter--open-button--is-filtered').length) {
              $('.filter--clear-button-outside').removeClass('d-none');
            }
          });

          if ($('input[id="edit-umio-search-api-fulltext-requested"].form-control').val() != "") {
            $('#edit-submit-feeds-jovens').click();
          }
          $('section.hero-banner-opportunities').addClass('d-print-none');
          $('div.feed--local-tasks').addClass('d-print-none');
          if ($('div.umio-card--grid-container > article').length == 0) {
            $('.filter--open-button').css('grid-area', 'print');
          }
        });
      });
    }
  }

} (jQuery, Drupal, drupalSettings));
