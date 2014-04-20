/**
 * Активность
 */

var ls = ls || {};

ls.stream = ( function ($) {
    this.isBusy = false;
    this.sDateLast = null;

    this.options = {
        selectors: {
            userList: 'js-activity-block-users',
            getMoreButton: 'activity-get-more',
            userListId: 'activity-block-users',
            inputId: 'activity-block-users-input',
            noticeId: 'activity-block-users-notice',
            userListItemId: 'activity-block-users-item-'
        },
        elements: {
            userItem: function (element) {
                return $('<li id="' + ls.stream.options.selectors.userListItemId + element.uid + '">' +
                    '<input type="checkbox" ' +
                    'class="input-checkbox" ' +
                    'data-user-id="' + element.uid + '" ' +
                    'checked="checked" />' +
                    '<a href="' + element.user_web_path + '">' + element.user_login + '</a>' +
                    '</li>');
            }
        }
    }

    /**
     * Init
     */
    this.init = function () {
        var self = this;

        $('.' + this.options.selectors.userList).on('change', 'input[type=checkbox]', function () {
            var userId = $(this).data('user-id');

            $(this).prop('checked') ? self.subscribe(userId) : self.unsubscribe(userId);
        });

        $('#' + this.options.selectors.getMoreButton).on('click', function () {
            self.getMore(this);
        });

        $('#' + this.options.selectors.inputId).keydown(function (event) {
            event.which == 13 && ls.stream.appendUser();
        });
    };

    /**
     * Подписаться на пользователя
     * @param  {Number} iUserId ID пользователя
     */
    this.subscribe = function (iUserId) {
        var self = this,
            url = ls.routerUrl('stream') + 'subscribe/',
            params = { 'id': iUserId };

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                ls.msg.notice(result.sMsgTitle, result.sMsg);
                ls.hook.run('ls_stream_subscribe_after', [params, result]);
            }
        });
    };

    /**
     * Отписаться от пользователя
     * @param  {Number} iUserId ID пользователя
     */
    this.unsubscribe = function (iUserId) {
        var self = this,
            url = ls.routerUrl('stream') + 'unsubscribe/',
            params = { 'id': iUserId };

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (result && !result.bStateError) {
                ls.msg.notice(result.sMsgTitle, result.sMsg);
                ls.hook.run('ls_stream_unsubscribe_after', [params, result]);
            }
        });
    };

    /**
     * Подписаться на пользователя
     */
    this.appendUser = function () {
        var self = this,
            sLogin = $('#' + self.options.selectors.inputId).val();

        if (!sLogin) {
            return;
        }

        ls.progressStart();
        ls.ajax(ls.routerUrl('stream') + 'subscribeByLogin/', { 'login': sLogin }, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var checkbox = $('.' + self.options.selectors.userList).find('input[data-user-id=' + result.uid + ']');

                $('#' + self.options.selectors.noticeId).remove();

                if (checkbox.length) {
                    if (checkbox.prop("checked")) {
                        ls.msg.error(ls.lang.get('error'), ls.lang.get('stream_subscribes_already_subscribed'));
                    } else {
                        checkbox.prop("checked", true);
                        ls.msg.notice(result.sMsgTitle, result.sMsg);
                    }
                } else {
                    $('#' + self.options.selectors.inputId).autocomplete('close').val('');
                    $('#' + self.options.selectors.userListId).show().append(self.options.elements.userItem(result));
                    ls.msg.notice(result.sMsgTitle, result.sMsg);
                }

                ls.hook.run('ls_stream_append_user_after', [checkbox.length, result]);
            }
        });
    };

    this.switchEventType = function (iType) {
        var url = ls.routerUrl('stream') + 'switchEventType/';
        var params = {'type': iType};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (result && !result.bStateError) {
                ls.msg.notice(result.sMsgTitle, result.sMsg);
                ls.hook.run('ls_stream_switch_event_type_after', [params, result]);
            }
        });
    };

    /**
     * Подгрузка событий
     * @param  {Object} oGetMoreButton Кнопка
     */
    this.getMore = function (oGetMoreButton) {
        if (this.isBusy) return;

        var $oGetMoreButton = $(oGetMoreButton),
            $oLastId = $('#activity-last-id');
        iLastId = $oLastId.val();

        if (!iLastId) return;

        $oGetMoreButton.addClass('loading');
        this.isBusy = true;

        var params = $.extend({}, {
            'iLastId': iLastId,
            'sDateLast': this.sDateLast
        }, ls.tools.getDataOptions($oGetMoreButton, 'param'));

        var url = ls.routerUrl('stream') + 'get_more' + (params.type ? '_' + params.type : '') + '/';

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (result.events_count) {
                    $('#activity-event-list').append(result.result);
                    $oLastId.attr('value', result.iStreamLastId);
                }

                if (!result.events_count) {
                    $oGetMoreButton.hide();
                }
            }

            $oGetMoreButton.removeClass('loading');

            ls.hook.run('ls_stream_get_more_after', [iLastId, result]);

            this.isBusy = false;
        }.bind(this));
    };

    return this;
}).call(ls.stream || {}, jQuery);