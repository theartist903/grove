      let jqueryParams = [],
        jQuery = function (r) {
          return ((jqueryParams = [...jqueryParams, r]), jQuery);
        },
        $ = function (r) {
          return ((jqueryParams = [...jqueryParams, r]), $);
        };
      ((window.jQuery = jQuery), (window.$ = jQuery));
      let customHeadScripts = !1;
      ((jQuery.fn = jQuery.prototype = {}),
        ($.fn = jQuery.prototype = {}),
        (jQuery.noConflict = function (r) {
          if (window.jQuery)
            return (
              (jQuery = window.jQuery),
              ($ = window.jQuery),
              (customHeadScripts = !0),
              jQuery.noConflict
            );
        }),
        (jQuery.ready = function (r) {
          jqueryParams = [...jqueryParams, r];
        }),
        ($.ready = function (r) {
          jqueryParams = [...jqueryParams, r];
        }),
        (jQuery.load = function (r) {
          jqueryParams = [...jqueryParams, r];
        }),
        ($.load = function (r) {
          jqueryParams = [...jqueryParams, r];
        }),
        (jQuery.fn.ready = function (r) {
          jqueryParams = [...jqueryParams, r];
        }),
        ($.fn.ready = function (r) {
          jqueryParams = [...jqueryParams, r];
        }));
