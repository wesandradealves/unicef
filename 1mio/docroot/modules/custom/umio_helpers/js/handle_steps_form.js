(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.handleSteps = {
    attach: function (context, settings) {

      $(document, context).once('handleSteps').each( function () {
        $(document).ready(function () {
          let currentStep = $('input[name="page_current_step"]').val();
          handleSteps(currentStep - 1);
        });
      });
    }
  }

  function handleSteps(currentStep) {
    var progressBar = $('ul.by-step-progress-bar');
    var active = progressBar.find('li')[currentStep];
    var newActive = progressBar.find('li')[currentStep + 1];
    makePreviousStepDone(progressBar, currentStep);
    makeStepDone($(active));
    makeStepActive($(newActive));
  }

  function makePreviousStepDone(progressBar, currentStep) {
    for (var step = 0; step < currentStep; step++) {
      var active = progressBar.find('li')[step];
      makeStepDone($(active));
    }
  }

  function makeStepDone(element) {
    element.addClass('done');
    var icon = element.find('.icon-progress-bar i');
    // Remove class that starts with 'ph-'
    $(icon).removeClass(function (index, className) {
      return (className.match(/(^|\s)ph-\S+/g) || []).join(' ');
    });
    icon.removeClass('svg-gray').addClass('ph-check svg-white');
  }

  function makeStepActive(element) {
    element.addClass('active');
    var icon = element.find('.icon-progress-bar i');
    icon.removeClass('svg-gray').addClass('svg-white');
  }

} (jQuery, Drupal, drupalSettings));
