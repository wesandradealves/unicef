(function ($) {
  // Argument passed from InvokeCommand.
  $.fn.select2ReRun = function (select2Eelement) {
    $(select2Eelement).select2({
      theme: 'bootstrap-5',
      language: 'pt-BR',
    });
    $(select2Eelement).next().addClass('form-select-locality');
    $(this).insertAfter($(this).parent().find('.name'));
  };
})(jQuery);
