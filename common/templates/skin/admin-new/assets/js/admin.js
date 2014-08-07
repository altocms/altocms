/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

var admin = admin || {};

(function ($) {
    admin = {
        store:{'@': 'altocms.ru'},

        init:function () {
            // Autocomplete
            ls.autocomplete.add($(".js-autocomplete-tags-sep"), ls.routerUrl('ajax') + 'autocompleter/tag/', true);
            ls.autocomplete.add($(".js-autocomplete-tags"), ls.routerUrl('ajax') + 'autocompleter/tag/', false);
            ls.autocomplete.add($(".js-autocomplete-users-sep"), ls.routerUrl('ajax') + 'autocompleter/user/', true);
            ls.autocomplete.add($(".js-autocomplete-users"), ls.routerUrl('ajax') + 'autocompleter/user/', false);

            // Autofocus
            $('form').each(function(){
                $(this).find('.js-focus-in:visible').first().focus();
            });
        }
    };

    var $this = admin;

    $this.uniqId = function () {
        return 'id-' + new Date().valueOf() + '-' + Math.floor(Math.random() * 1000000000);
    };

    $this.progressOn = function (element, progressClass) {
        element = $(element);
        if (!element.data('adm-progress-store')) {
            element.data('adm-progress-store', element.html());
        }
        element.css('width', element.outerWidth());
        //element.css('height', element.outerHeight());
        if (!progressClass) progressClass = 'adm-progress';
        element.html('<i class="' + progressClass + '"></i>');
    };

    $this.progressOff = function (element) {
        element = $(element);
        element.find('i.adm-progress').remove();
        if (element.data('adm-progress-store')) element.html(element.data('adm-progress-store'));
    };

    // plugins
    $this.plugin = {
        turn:function (pluginId, action) {
            if (!action) {
                action = 'deactivate';
            } else if (action != 'deactivate') {
                action = 'activate';
            }
            $('tr[id^=plugin-] .check-row [type=checkbox]').prop("checked", "");
            $('tr[id^=plugin-' + pluginId + '] .check-row [type=checkbox]').prop("checked", "checked");
            $('#form_plugins_list input[name=plugin_action]').val(action);
            $('#form_plugins_list').submit();
        },

        turnOn:function (pluginId) {
            return $this.plugin.turn(pluginId, 'activate');
        },

        turnOff:function (pluginId) {
            return $this.plugin.turn(pluginId, 'deactivate');
        }
    };

    $this.popoverExt = function (element, options) {
        options = $.extend({
            attr:{ },
            css:{ },
            events:{ }
        }, options);
        if (!options.attr.id) options.attr.id = admin.uniqId();
        element = $(element);
        element.popover(options);
        element.data('popoverOptions', options);

        var popover = element.data('popover');
        var popoverElement = $(popover.tip());

        if (options.attr) {
            $.each(options.attr, function (key, value) {
                switch (key) {
                    case 'class':
                        popoverElement.addClass(value);
                        break;
                    default:
                        popoverElement.prop(key, value);
                }
            });
        }
        if (options.css) {
            $.each(options.css, function (key, value) {
                popoverElement.css(key, value);
            });
        }
        $.each(options.events, function (key, func) {
            $(element).on(key, function (e) {
                func.apply(this, [e]);
            });
        });
        if (popover.getTitle() === false) {
            popoverElement.find('.popover-title').css({display:'none'});
        }
        return popoverElement;
    };

    // указательные окна
    $this.pointup = function (element, options) {
        options = $.extend({
            trigger:'manual',
            placement:'top',
            onCancel:function (e, popover) {
                // nothing
            },
            onConfirm:function (e, popover) {
                // nothing
            }
        }, options);
        if (!options.attr.id) options.attr.id = admin.uniqId();

        element = $(element);
        var popoverElement = admin.popoverExt(element, options);

        $(document).on('click', '#' + options.attr.id + ".popover .confirm", {source:element}, function (e) {
            var opt = element.data('popoverOptions');
            if (opt.onConfirm) opt.onConfirm(e, $('#' + opt.attr.id));
            element.popover('hide');
        });
        $(document).on('click', '#' + options.attr.id + ".popover .cancel", {source:element}, function (e) {
            var opt = element.data('popoverOptions');
            if (opt.onCancel) opt.onCancel(e, $('#' + opt.attr.id));
            element.popover('hide');
        });

        return popoverElement;
    };

    $this.getPopover = function (element) {
        var popover = element.data('popover');
        if (popover) {
            var popoverElement = $(popover.tip());
            return popoverElement;
        }
    };

    $this.isEmpty = function (mixedVar) {
        return ((typeof mixedVar == 'undefined') || (mixedVar === null));
    };

    $this.dashboard = function(element, source, mode) {
        if (mode == 'iframe' && source) {
            source = source.replace('@', 'http://' + admin.store['@'] + '/');
            var content = $('<iframe/>').prop('src', source);
        }
        $(element).append(content);
    }

    $this.dashboardNews = function (params) {
        if ($('.b-dashboard-news.refresh').length) {
            admin.dashboard('.b-dashboard-news', '@info/news/?' + params, 'iframe');
        }
    };

    $this.dashboardUpdates = function (params) {
        if ($('.b-dashboard-updates.refresh').length) {
            admin.dashboard('.b-dashboard-updates', '@info/updates/?' + params, 'iframe');
        }
    };

    $this.vote = function (type, idTarget, value, viewElements, funcDone) {
        var options = {
            classes_action:{
                voted:'voted',
                plus:'plus',
                minus:'minus',
                positive:'positive',
                negative:'negative',
                quest:'quest'
            },
            classes_element:{
                voting:'voting',
                count:'count',
                total:'total',
                plus:'plus',
                minus:'minus'
            }
        };

        var typeVote = {
            user:{
                url:'/admin/ajaxvote/user/',
                targetName:'idUser'
            }
        };

        var voteResult = function (result) {
            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                if (viewElements.skill) {
                    $(viewElements.skill).text(result.iSkill);
                    if (type == 'user' && $('user_skill_' + idTarget)) {
                        $('#user_skill_' + idTarget).text(result.iSkill);
                    }
                }

                if (viewElements['rating']) {
                    var view = $(viewElements['rating']);

                    result.iRating = parseFloat(result.iRating);
                    if (result.iRating > 0) {
                        result.iRating = '+' + result.iRating;
                    } else if (result.iRating == 0) {
                        result.iRating = '0';
                    }
                    view.removeClass(options.classes_action.negative)
                        .removeClass(options.classes_action.positive)
                        .text(result.iRating);
                    if (result.iRating < 0) {
                        view.addClass(options.classes_action.negative)
                    } else {
                        view.addClass(options.classes_action.positive)
                    }
                }

                if (viewElements['voteCount']) {
                    $(viewElements.voteCount).text(result.iCountVote);
                }

                ls.msg.notice(result.sMsgTitle, result.sMsg);
            }
            if (funcDone) funcDone();
        };

        // do
        if (!typeVote[type]) {
            return false;
        }

        $this.vote.idTarget = idTarget;
        $this.vote.value = value;
        $this.vote.type = type;

        var params = {}, more;
        params['value'] = value;
        params[typeVote[type].targetName] = idTarget;

        ls.ajax(typeVote[type].url, params, function (result) {
            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(result.sMsgTitle || 'Error', result.sMsg || 'Please try again later');
            } else {
                voteResult(result, $this.vote);
            }
        }, more);

    }

})(jQuery);

!function ($) {
    "use strict"; // jshint ;_;

    $.fn.progressOn = function () {
        return admin.progressOn(this);
    }

    $.fn.progressOff = function () {
        return admin.progressOff(this);
    }

}(window.jQuery);

$(function () {
    admin.init();
});

$(function(){
    $('[data-toggle="popover"]').each(function(){
        var selector = $(this).data('popover');
        if (selector) {
            var popover = $(selector);
            if (popover.length) {
                popover = popover.first();
                if ($(this).css('cursor') == 'auto') {
                    $(this).css('cursor', 'pointer');
                }
                $(this).click(function() {
                    $('.popover').hide();
                    var p = $(this).position();
                    if (popover.hasClass('top')) {
                        p.left -= parseInt((popover.outerWidth() - $(this).outerWidth()) / 2);
                        p.top -= parseInt(popover.outerHeight());
                    }
                    else if (popover.hasClass('right')) {
                        p.left += parseInt($(this).width());
                        p.top -= parseInt((popover.outerHeight() - $(this).outerHeight()) / 2);
                    }
                    else if (popover.hasClass('bottom')) {
                        p.left -= parseInt((popover.outerWidth() - $(this).outerWidth()) / 2);
                        p.top += parseInt($(this).outerHeight());
                    }
                    else if (popover.hasClass('left')) {
                        p.left -= parseInt(popover.outerWidth());
                        p.top -= parseInt((popover.outerHeight() - $(this).outerHeight()) / 2);
                    }

                    popover.css({
                        position: 'absolute',
                        top:p.top,
                        left:p.left
                    });
                    popover.appendTo($(this).parent());
                    popover.find('.close').each(function(){
                        if (!$(this).onclick) {
                            var dismiss = $(this).data('dismiss');
                            if (dismiss) {
                                var parent = $(this).parents('.' + dismiss).first();
                                if (parent.length) {
                                    $(this).click(function() { parent.hide(); })
                                }
                            }
                        }
                    });
                    popover.fadeIn();
                });
            }
        }
    });

    // выделение строк в таблицах
    $('.check-row [type=checkbox]').click(function(){
        if ($(this).prop('checked')) {
            $(this).parents('tr.selectable').addClass('info');
        } else {
            $(this).parents('tr.selectable').removeClass('info');
        }
    });

    if ($.uniform) {
        $(function() {
            $(".uniform select, .uniform :file, .uniform :radio, .uniform :checkbox:not([name|=b-switch])").uniform({
                fileDefaultText: ls.lang.get("action.admin.form_no_file_selected"),
                fileBtnText:  ls.lang.get("action.admin.form_choose_file")
            });
            $(".uniform label.checkbox :checkbox").change(function(event){
                if ($(this).prop('checked')) {
                    $(this).parents('label').first().addClass('checked');
                } else {
                    $(this).parents('label').first().removeClass('checked');
                }
            });
        });
    }

    l10n = l10n || {date_format: 'dd.mm.yyy', week_start: 1};

    $('.datepicker').datepicker({
        format: l10n.date_format,
        weekStart: l10n.week_start
    });

});

// EOF