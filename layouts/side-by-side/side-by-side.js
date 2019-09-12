/**
 * @file
 * Enhancement for side by side layout.
 */

/* eslint-env es6:false, node:false */
/* eslint-disable strict, func-names, object-shorthand, no-var, prefer-template, prefer-arrow-callback */
(function($, Drupal) {
  "use strict";

  Drupal.behaviors.sidebysideLayout = {
    attach: function(context) {
      $(".js-sbs-layout-region-main", context)
        .once("sidebysideLayout")
        .each(function() {
          Drupal.cdCore.sidebyside($(this));
        });
    }
  };

  Drupal.cdCore = Drupal.cdCore || {};

  Drupal.cdCore.sidebyside = function($main) {
    var _self = this;
    var options = {
      0: {
        label: Drupal.t("All"),
        active: null
      },
      1: {
        label: Drupal.t("Odd"),
        active: "show-odd"
      },
      2: {
        label: Drupal.t("Even"),
        active: "show-even"
      }
    };
    this.main = $main;
    this.options = $('<div class="sbs-menu">')
      .on("click keyup", ".js-sbs-menu-item", function(event) {
        var handledActions = [32, 13, "click"];
        var keyCode = event.keyCode || event.charCode || "click";

        if (handledActions.indexOf(keyCode) > -1) {
          $.cookie("sbs", $(this).data("sbs-key"), { expires: 91, path: "/" });

          $(this)
            .addClass("active")
            .siblings()
            .removeClass("active");

          _self.main.removeClass("show-odd show-even");
          _self.main.addClass($(this).data("sbs-class"));

          event.preventDefault();
        }
      })
      .insertBefore($main);

    try {
      this.currentState = parseInt($.cookie("sbs"), 10) || 0;
    } catch (e) {
      this.currentState = 0;
    }

    this.options.prepend(
      $("<span>", {
        text: Drupal.t("Columns shown:"),
        class: "sbs-menu__prefix"
      })
    );

    Object.keys(options).forEach(function(key) {
      var active = _self.currentState === parseInt(key, 10);
      $("<a>", {
        text: options[key].label,
        class: "sbs-menu__item" + (active ? " active" : ""),
        role: "button",
        href: "#"
      })
        .data("sbs-key", key)
        .data("sbs-class", options[key].active)
        .addClass("js-sbs-menu-item")
        .appendTo(_self.options);

      if (active) {
        _self.main.addClass(options[key].active);
      }
    });
  };
})(jQuery, Drupal);
/* eslint-enable strict, func-names, object-shorthand, no-var, prefer-template, prefer-arrow-callback */
/* eslint-env es6:true, node:true */
