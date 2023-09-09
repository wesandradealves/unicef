(function ($, Drupal) {
  "use strict";
  Drupal.behaviors.comment_customization = {
    attach: function (context) {
      let ownForm = $('form#comment-form.comment-form.own-form').length;
      if (ownForm) {
        $('.form-item-notify').remove();
      }
    },
  };
})(jQuery, Drupal);
