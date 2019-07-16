/**
 * @file
 * Enhances Theme Switcher form.
 */

/* eslint-env es6:false, node:false */
/* eslint-disable strict, func-names, object-shorthand, no-var, prefer-template, prefer-arrow-callback */
(function($, Drupal) {
  "use strict";

  Drupal.behaviors.themeswitcherAutoSubmit = {
    attach: function(context) {
      var $form = $(".js-themeswitcher-form", context).once(
        "themeswitcherAutoSubmit"
      );
      $form.on("change", "select", function() {
        $form.trigger("submit");
      });
    }
  };
})(jQuery, Drupal);
/* eslint-enable strict, func-names, object-shorthand, no-var, prefer-template, prefer-arrow-callback */
/* eslint-env es6:true, node:true */
