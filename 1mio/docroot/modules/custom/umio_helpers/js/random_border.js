(function ($) {

  $.fn.addRandomBorderClass = function () {
    let borderTypes = [
      'rounded-top-left',
      'rounded-top-right',
      'rounded-bottom-left',
      'rounded-bottom-right',
      'rounded-none'
    ];

    var borderClass = borderTypes[Math.floor(Math.random() * borderTypes.length)];
    $(this).addClass(borderClass);
  };

})(jQuery);
