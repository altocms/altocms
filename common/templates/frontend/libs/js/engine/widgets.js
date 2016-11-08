/**
 * Widgets
 */
;var ls = ls || {};

ls.widgets = (function ($) {
    "use strict";

    var $that = this;

    this.options = {
        activeClass: 'active',
        loaderClass: 'loader',
        widgetSelector: '.widget-type-stream',
        contentSelector: '.js-widget-stream-content',
        type: {
            stream_comment: {
                url: ls.routerUrl('ajax') + 'stream/comment/'
            },
            stream_topic: {
                url: ls.routerUrl('ajax') + 'stream/topic/'
            },
            stream_wall: {
                url: ls.routerUrl('ajax') + 'stream/wall/'
            },
            blogs_top: {
                url: ls.routerUrl('ajax') + 'blogs/top/'
            },
            blogs_join: {
                url: ls.routerUrl('ajax') + 'blogs/join/'
            },
            blogs_self: {
                url: ls.routerUrl('ajax') + 'blogs/self/'
            }
        }
    };

    this.init = function (options) {
        this.options = $.extend(this.options, options);

        if ($(this.options.widgetSelector).length) {
            $('.js-widget-stream-navs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var widget = $(this).closest($that.options.widgetSelector);
                widget.css('height', widget.height());
                widget.find($that.options.contentSelector).css('min-height', 30);
                $that.load(this, 'stream', null, function(html) {
                    $that.refreshContent(widget, html);
                });
            });
            $('.js-widget-stream-navs li.active a[data-toggle="tab"]').triggerHandler('shown.bs.tab');
        }

    };

    this.load = function (but, widgetName, params, success) {
        var type = $(but).data('type');

        if (!type) {
            ls.log('Error: type not defined in data');
            return;
        }
        type = widgetName + '_' + type;
        if (!this.options.type[type]) {
            ls.log('Error: type "' + type + '" not defined in options');
            return;
        }

        params = $.extend(true, {}, this.options.type[type].params || {}, params || {});

        var content = $('.js-widget-' + widgetName + '-content');

        content.empty().addClass(this.options.loaderClass);

        $('.js-widget-' + widgetName + '-item').removeClass(this.options.activeClass);
        $(but).addClass(this.options.activeClass);

        ls.ajax(this.options.type[type].url, params, function (result) {
            content.empty().removeClass(this.options.loaderClass);
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (success) {
                    success(result.sText);
                } else {
                    content.html(result.sText);
                }
            }
        }.bind(this));
    };

    this.refreshContent = function(widget, html) {
        widget.find(this.options.contentSelector).html(html);
        widget.css('height', 'auto');
    };

    $(function() {
        ls.widgets.init();
    });

    return this;
}).call(ls.widgets || {}, jQuery);

// EOF