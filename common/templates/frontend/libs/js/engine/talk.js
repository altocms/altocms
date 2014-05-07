;var ls = ls || {};

/**
 * Функционал личных сообщений
 */
ls.talk = (function ($) {

    /**
     * Init
     */
    this.init = function () {
        // Добавляем или удаляем друга из списка получателей
        $('#friends input:checkbox').change(function () {
            ls.talk.toggleRecipient($('#' + $(this).attr('id') + '_label').text(), $(this).prop('checked'));
        });

        // Добавляем всех друзей в список получателей
        $('#friend_check_all').click(function () {
            $('#friends input:checkbox').each(function (index, item) {
                ls.talk.toggleRecipient($('#' + $(item).attr('id') + '_label').text(), true);
                $(item).attr('checked', true);
            });
            return false;
        });

        // Удаляем всех друзей из списка получателей
        $('#friend_uncheck_all').click(function () {
            $('#friends input:checkbox').each(function (index, item) {
                ls.talk.toggleRecipient($('#' + $(item).attr('id') + '_label').text(), false);
                $(item).attr('checked', false);
            });
            return false;
        });

        // Удаляем пользователя из черного списка
        $("#black_list_block").on("click", "a.delete", function () {
            ls.talk.removeFromBlackList(this);
            return false;
        });

        // Удаляем пользователя из переписки
        $("#speaker_list_block").on("click", "a.delete", function () {
            ls.talk.removeFromTalk(this, $('#talk_id').val());
            return false;
        });
    };

    /**
     * Добавляет пользователя к переписке
     */
    this.addToTalk = function (idTalk) {
        var sUsers = $('#talk_speaker_add').val();
        if (!sUsers) return false;
        $('#talk_speaker_add').val('');

        var url = ls.routerUrl('talk') + 'ajaxaddtalkuser/';
        var params = {users: sUsers, idTalk: idTalk};

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $.each(result.aUsers, function (index, item) {
                    var list = $('#speaker_list');
                    if (list.length == 0) {
                        list = $('<ul class="list" id="speaker_list"></ul>');
                        $('#speaker_list_block').append(list);
                    }
                    var listItem = $('<li id="speaker_item_' + item.sUserId + '_area"><a href="' + item.sUserLink + '" class="user">' + item.sUserLogin + '</a> - <a href="#" id="speaker_item_' + item.sUserId + '" class="delete">' + ls.lang.get('delete') + '</a></li>')
                    list.append(listItem);
                    ls.hook.run('ls_talk_add_to_talk_item_after', [idTalk, item], listItem);
                });

                ls.hook.run('ls_talk_add_to_talk_after', [idTalk, result]);
            }
        });
        return false;
    };

    /**
     * Удаляет пользователя из переписки
     */
    this.removeFromTalk = function (link, idTalk) {
        link = $(link);

        $('#' + link.attr('id') + '_area').fadeOut(500, function () {
            $(this).remove();
        });
        var idTarget = link.attr('id').replace('speaker_item_', '');

        var url = ls.routerUrl('talk') + 'ajaxdeletetalkuser/';
        var params = {idTarget: idTarget, idTalk: idTalk};

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
                link.parent('li').show();
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
                link.parent('li').show();
            }
            ls.hook.run('ls_talk_remove_from_talk_after', [idTalk, idTarget], link);
        });

        return false;
    };

    /**
     * Добавляет пользователя в черный список
     */
    this.addToBlackList = function (form, blackList) {
        form = $(form);
        var userListInput = form.find('[name=user_list]');
        var users = userListInput.val();

        if (!users) return false;

        var url = ls.routerUrl('talk') + 'ajaxaddtoblacklist/';
        var params = {users: users};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                blackList = $(blackList);
                userListInput.val('');
                var html = blackList.find('#user_black_list_item_ID').get(0).outerHTML;
                $.each(result.aUsers, function (index, item) {
                    var htmlItem = html.replace(/ID/g, item.sUserId)
                        .replace(/URL/g, item.sUserUrl)
                        .replace(/NAME/g, item.sUserName)
                        .replace(/AVATAR/g, item.sUserAvatar);
                    var li = $(htmlItem).appendTo(blackList).show();
                    ls.hook.run('ls_talk_add_to_black_list_item_after', [item, li]);
                });
                ls.hook.run('ls_talk_add_to_black_list_after', [result]);
            }
        });
        return false;
    };

    /**
     * Удаляет пользователя из черного списка
     */
    this.removeFromBlackList = function (id) {
        var item = $('#user_black_list_item_' + id);

        var url = ls.routerUrl('talk') + 'ajaxdeletefromblacklist/';
        var params = {idTarget: id};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
                return false;
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
                return false;
            }
            item.fadeOut(500, function () {
                $(this).remove();
            });
            ls.hook.run('ls_talk_remove_from_black_list_after', [id]);
        });
        return false;
    };

    /**
     * Добавляет или удаляет друга из списка получателей
     */
    this.toggleRecipient = function (login, add) {
        var to = $.map($('#talk_users').val().split(','), function (item, index) {
            item = $.trim(item);
            return item != '' ? item : null;
        });
        if (add) {
            to.push(login);
            to = $.richArray.unique(to);
        } else {
            to = $.richArray.without(to, login);
        }
        $('#talk_users').val(to.join(', '));
    };

    /**
     * Очищает поля фильтра
     */
    this.clearFilter = function () {
        $('#block_talk_search_content').find('input[type="text"]').val('');
        $('#block_talk_search_content').find('input[type="checkbox"]').removeAttr("checked");
        return false;
    };

    /**
     * Удаление списка писем
     */
    this.removeTalks = function () {

        if ($('.form_talks_checkbox:checked').length == 0) {
            return false;
        }
        ls.modal.confirm({
            text: ls.lang.get('talk_inbox_delete_confirm')
        }, {
            onConfirm: function() {
                $('#form_talks_list_submit_unread').val(0);
                $('#form_talks_list_submit_del').val(1);
                $('#form_talks_list_submit_read').val(0);
                $('#form_talks_list').submit();
            }
        });
        return false;
    };

    this.removeMessage = function (id) {

        ls.modal.confirm({
            text: ls.lang.get('talk_inbox_delete_confirm')
        }, {
            onConfirm: function() {
                location.href = ls.routerUrl('talk') + 'delete/' + id + '/?security_key=' + ALTO_SECURITY_KEY;
            }
        });
        return false;
    };

    /**
     * Пометка о прочтении писем
     */
    this.makeReadTalks = function () {
        if ($('.form_talks_checkbox:checked').length == 0) {
            return false;
        }
        $('#form_talks_list_submit_unread').val(0);
        $('#form_talks_list_submit_read').val(1);
        $('#form_talks_list_submit_del').val(0);
        $('#form_talks_list').submit();
        return false;
    };

    /**
     * Пометка непрочтенных писем
     */
    this.makeUnreadTalks = function () {
        if ($('.form_talks_checkbox:checked').length == 0) {
            return false;
        }
        $('#form_talks_list_submit_unread').val(1);
        $('#form_talks_list_submit_read').val(0);
        $('#form_talks_list_submit_del').val(0);
        $('#form_talks_list').submit();
        return false;
    };

    return this;
}).call(ls.talk || {}, jQuery);
