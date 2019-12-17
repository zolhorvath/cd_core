/**
 * @file
 * Track the pointer.
 */

/* eslint-env es6:false, node:false */
/* eslint-disable strict, func-names, object-shorthand, no-var, prefer-template, prefer-arrow-callback */
(function($, Drupal) {
  "use strict";

  Drupal.behaviors.pointerTracker = {
    attach: function() {
      $(document)
        .once("pointerTracker")
        .each(function() {
          var $document = $(this);
          var $crosshair = $('<span id="pt-crosshair"></span>');
          var pointerdown = function pointerdown() {
            $crosshair.toggleClass("pointerdown", true);
          };
          var pointerup = function pointerup() {
            $crosshair.toggleClass("pointerdown", false);
          };
          var pointertrack = function pointertrack(event) {
            const left = event.changedTouches
              ? event.changedTouches[0].clientX
              : event.clientX;
            const top = event.changedTouches
              ? event.changedTouches[0].clientY
              : event.clientY;
            $crosshair.css({ left: left, top: top });
          };
          var hidecrosshair = function hidecrosshair() {
            pointerup();
            $crosshair.removeAttr("style");
          };
          $document.find("body").prepend($crosshair);

          $document.on({
            "mousedown.pointerTracker": pointerdown,
            "touchstart.pointerTracker": pointerdown,
            "pointerdown.pointerTracker": pointerdown,
            "mousemove.pointerTracker": pointertrack,
            "touchmove.pointerTracker": pointertrack,
            "pointermove.pointerTracker": pointertrack,
            "mouseup.pointerTracker": pointerup,
            "touchend.pointerTracker": pointerup,
            "pointerup.pointerTracker": pointerup,
            "mouseleave.pointerTracker": hidecrosshair
          });
        });
    }
  };
})(jQuery, Drupal);
/* eslint-enable strict, func-names, object-shorthand, no-var, prefer-template, prefer-arrow-callback */
/* eslint-env es6:true, node:true */
