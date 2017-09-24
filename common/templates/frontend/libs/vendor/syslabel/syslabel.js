/**
 * syslabel
 */

;(function ($) {
    "use strict";
    $.SysLabel = { };
    var $that = $.SysLabel;

    $that.label = null;
    $that.tick = 0;
    $that.inProgress = false;

    $that.options = {
        text: 'wait',
        html: '',
        top: 0,
        dotted: 3,
        template: '<div class="b-syslabel"></div>'
    };

    $that.init = function(options) {
        $that.options = $.extend($that.options, options);
    };

    $that.show = function (options) {

        $that.options = $.extend($that.options, options);
        if (!$that.label) {
            $that.label = $($that.options.template);
            if ($that.options.css) {
                $that.label.css($that.options.css);
            }
            $that.label.appendTo('body');
        }
        if ($that.options.html) {
            $that.label.html($that.options.html);
            $that.options.dotted = 0;
        } else {
            $that.label.text($that.options.text);
        }
        $that.label.show();
        $that.inProgress = true;
        if ($that.options.dotted) {
            $that.render();
        }
        return this;
    };

    $that.render = function () {
        if ($that.options.dotted && $that.inProgress) {
            if ($that.tick++ >= $that.options.dotted) {
                $that.tick = 0;
            }
            var text = $that.options.text;
            for (var i = 0; i < $that.tick; i++) {
                text += '.';
            }
            $that.label.text(text);
            setTimeout(function () {
                $that.render();
            }, 1000);
        }
        return this;
    };

    $that.hide = function() {
        if ($that.inProgress) {
            if ($that.label) {
                $that.label.hide();
            }
            $that.inProgress = false;
        }
        return this;
    }

}(jQuery));