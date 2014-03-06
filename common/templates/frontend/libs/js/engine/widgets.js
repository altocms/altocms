/**
 * Widgets
 */
var ls = ls || {};

ls.widgets = (function ($) {
    "use strict";

    this.options = {
        active: 'active',
        loaderClass: 'loader',
        type: {
            stream_comment: {
                url: ls.routerUrl('ajax') + 'stream/comment/'
            },
            stream_topic: {
                url: ls.routerUrl('ajax') + 'stream/topic/'
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

        if ($('.widget-type-stream').length) {
            $('.js-widget-stream-navs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var widget = $('.widget-type-stream');
                widget.css('height', widget.height());
                ls.widgets.load(this, 'stream', null, function(html) {
                    $('.js-widget-stream-content').html(html);
                    $('.widget-type-stream').css('height', 'auto');
                });
            });
            $('.js-widget-stream-navs li.active a[data-toggle="tab"]').triggerHandler('shown.bs.tab');
        }

        // Кнопка обновления блока
        $('#js-stream-update').on('click', function () {
            ((tabs.is(':visible')) ? tabs : $('#js-dropdown-menu-stream')).find('li.active').tab('activate');
            $(this).addClass('active');
            setTimeout(function () {
                $(this).removeClass('active');
            }.bind(this), 600);
        });

    };

    this.load = function (but, widgetName, params, success) {
        var type = $(but).data('type');

        if (!type) return;
        type = widgetName + '_' + type;

        params = $.extend(true, {}, this.options.type[type].params || {}, params || {});

        var content = $('.js-widget-' + widgetName + '-content');

        content.empty().addClass(this.options.loaderClass);

        $('.js-widget-' + widgetName + '-item').removeClass(this.options.active);
        $(but).addClass(this.options.active);

        ls.ajax(this.options.type[type].url, params, function (result) {
            content.empty().removeClass(this.options.loaderClass);
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (success) {
                    var result = success(result.sText);
                } else {
                    content.html(result.sText);
                }
            }
        }.bind(this));
    };

    return this;
}).call(ls.blocks || {}, jQuery);

$(function() {
    ls.widgets.init();
});
