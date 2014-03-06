/**
 * Лента
 */

var ls = ls || {};

ls.userfeed = (function ($) {
    this.isBusy = false;

    this.options = {
        selectors: {
            userList: 'js-userfeed-block-users',
            userListId: 'userfeed-block-users',
            inputId: 'userfeed-block-users-input',
            noticeId: 'userfeed-block-users-notice',
            userListItemId: 'userfeed-block-users-item-'
        },
        elements: {
            userItem: function (element) {
                return ls.stream.options.elements.userItem(element);
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

            $(this).prop('checked') ? self.subscribe('users', userId) : self.unsubscribe('users', userId);
        });

        $('#' + this.options.selectors.inputId).keydown(function (event) {
            event.which == 13 && ls.userfeed.appendUser();
        });
    };

    this.subscribe = function (sType, iId) {
        var url = ls.routerUrl('feed') + 'subscribe/';
        var params = {'type': sType, 'id': iId};

        ls.ajax(url, params, function (data) {
            if (!data.bStateError) {
                ls.msg.notice(data.sMsgTitle, data.sMsg);
                ls.hook.run('ls_userfeed_subscribe_after', [sType, iId, data]);
            }
        });
    }

    this.unsubscribe = function (sType, iId) {
        var url = ls.routerUrl('feed') + 'unsubscribe/';
        var params = {'type': sType, 'id': iId};

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.notice(result.sMsgTitle, result.sMsg);
                ls.hook.run('ls_userfeed_unsubscribe_after', [sType, iId, result]);
            }
        });
    }

    this.appendUser = function () {
        var self = this,
            sLogin = $('#' + self.options.selectors.inputId).val();

        if (!sLogin) return;

        ls.ajax(ls.routerUrl('feed') + 'subscribeByLogin/', {'login': sLogin}, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                var checkbox = $('.' + self.options.selectors.userList).find('input[data-user-id=' + result.uid + ']');

                $('#' + self.options.selectors.noticeId).remove();

                if (checkbox.length) {
                    if (checkbox.prop('checked')) {
                        ls.msg.error(result.lang_error_title, result.lang_error_msg);
                        return;
                    } else {
                        checkbox.prop('checked', true);
                        ls.msg.notice(result.sMsgTitle, result.sMsg);
                    }
                } else {
                    $('#' + self.options.selectors.inputId).autocomplete('close').val('');
                    $('#' + self.options.selectors.userListId).append(self.options.elements.userItem(result));
                    ls.msg.notice(result.sMsgTitle, result.sMsg);
                }
            }
        });
    }

    this.getMore = function () {
        if (this.isBusy) {
            return;
        }
        var lastId = $('#userfeed_last_id').val();
        if (!lastId) return;
        $('#userfeed_get_more').addClass('loading');
        this.isBusy = true;

        var url = ls.routerUrl('feed') + 'get_more/';
        var params = {'last_id': lastId};

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (result.topics_count) {
                    $('#userfeed_loaded_topics').append(result.result);
                    $('#userfeed_last_id').attr('value', result.iUserfeedLastId);
                }
                if (!result.topics_count) {
                    $('#userfeed_get_more').hide();
                }
            }
            $('#userfeed_get_more').removeClass('loading');
            ls.hook.run('ls_userfeed_get_more_after', [lastId, result]);
            this.isBusy = false;
        }.bind(this));
    }

    return this;
}).call(ls.userfeed || {}, jQuery);