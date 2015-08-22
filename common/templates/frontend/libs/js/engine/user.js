;var ls = ls || {};

/**
 * Управление пользователями
 */
ls.user = (function ($) {
    var $that = this;

    this.jcropImage = null;

    this.options = {};

    /**
     * Initialization
     */
    this.init = function() {

        // Authorization
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-login/', '.js-form-login', function (result, status, xhr, form) {
            if (result && result.sUrlRedirect) {
                ls.progressStart();
                window.location.href = result.sUrlRedirect
            }
        });

        /* Registration */
        ls.ajaxForm(ls.routerUrl('registration') + 'ajax-registration/', '.js-form-registration', function (result, status, xhr, form) {
            if (result && result.sUrlRedirect) {
                ls.progressStart();
                window.location.href = result.sUrlRedirect
            }
        });

        /* Password reset */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-reminder/', '.js-form-reminder', function (result, status, xhr, form) {
            if (result && result.sUrlRedirect) {
                ls.progressStart();
                window.location.href = result.sUrlRedirect
            }
        });

        /* Request for activation link */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-reactivation/', '.js-form-reactivation', function (result, status, xhr, form) {
            form.find('input').val('');
            ls.hook.run('ls_user_reactivation_after', [form, result]);
        });

    };

    /**
     * Валидация полей формы при регистрации
     */
    this.validateRegistrationFields = function (form, fields) {
        var url = ls.routerUrl('registration') + 'ajax-validate-fields/';
        var params = {fields: fields};
        form = $(form);

        $.each(fields, function (i, data) {
            $('[name=' + data.field + ']').addClass('loader');
        });
        ls.ajax(url, params, function (result) {
            $.each(fields, function (i, data) {
                $('[name=' + data.field + ']').removeClass('loader');
                if (result.aErrors && result.aErrors[data.field][0]) {
                    form.find('.validate-error-field-' + data.field).removeClass('validate-error-hide').addClass('validate-error-show').text(result.aErrors[data.field][0]);
                    form.find('.validate-ok-field-' + data.field).hide();
                } else {
                    form.find('.validate-error-field-' + data.field).removeClass('validate-error-show').addClass('validate-error-hide');
                    form.find('.validate-ok-field-' + data.field).show();
                }
            });
            ls.hook.run('ls_user_validate_registration_fields_after', [fields, form, result]);
        });
    };

    /**
     * Валидация конкретного поля формы
     */
    this.validateRegistrationField = function(form, fieldName, fieldValue, params) {
        var fields = [];
        if (fieldName == 'password') {
            var login = $(form).find('[name=login]').val();
            if (login) {
                params['login'] = login;
            }
        }
        fields.push({field: fieldName, value: fieldValue, params: params || {}});
        this.validateRegistrationFields(form, fields);
    };

    /**
     * Добавление в друзья
     */
    this.addFriend = function (form, idUser, sAction) {
        var sText ='',
            url = '';
        form = $(form);
        if (sAction != 'link' && sAction != 'accept') {
            sText = $('#add_friend_text').val();
            form.children().each(function (i, item) {
                $(item).attr('disabled', 'disabled')
            });
        }

        if (sAction == 'accept') {
            url = ls.routerUrl('profile') + 'ajaxfriendaccept/';
        } else {
            url = ls.routerUrl('profile') + 'ajaxfriendadd/';
        }

        var params = {idUser: idUser, userText: sText};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            form.children().each(function (i, item) {
                $(item).removeAttr('disabled')
            });
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);
                $('#modal-add_friend').modal('hide');
                $('#profile_actions  li:first').html($.trim(result.sToggleText));
                ls.hook.run('ls_user_add_friend_after', [idUser, sAction, result], form);
            }
        });
        return false;
    };

    /**
     * Удаление из друзей
     */
    this.removeFriend = function (button, idUser, sAction) {
        var url = ls.routerUrl('profile') + 'ajaxfrienddelete/',
            params = {idUser: idUser, sAction: sAction};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);
                $('#profile_actions li:first').html($.trim(result.sToggleText));
                ls.hook.run('ls_user_remove_friend_after', [idUser, sAction, result], button);
            }
        });
        return false;
    };

    /**
     * Поиск пользователей
     */
    this.searchUsers = function (form) {
        form = $(form);
        var url = ls.routerUrl('people') + 'ajax-search/';
        var inputSearch = form.find('input');
        inputSearch.addClass('loader');

        ls.ajaxSubmit(url, form, function (result) {
            inputSearch.removeClass('loader');
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                $('#users-list-search').hide();
                $('#users-list-original').show();
            } else {
                $('#users-list-original').hide();
                $('#users-list-search').html(result.sText).show();
                ls.hook.run('ls_user_search_users_after', [form, result]);
            }
        });
    };

    /**
     * Поиск пользователей по началу логина
     */
    this.searchUsersByPrefix = function (sPrefix, button) {
        var url = ls.routerUrl('people') + 'ajax-search/',
            params = {user_login: sPrefix, isPrefix: 1};

        button = $(button);
        $('#search-user-login').addClass('loader');

        ls.ajax(url, params, function (result) {
            $('#search-user-login').removeClass('loader');
            $('#user-prefix-filter').find('.active').removeClass('active');
            button.parent().addClass('active');
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                $('#users-list-search').hide();
                $('#users-list-original').show();
            } else {
                $('#users-list-original').hide();
                $('#users-list-search').html(result.sText).show();
                ls.hook.run('ls_user_search_users_by_prefix_after', [sPrefix, button, result]);
            }
        });
        return false;
    };

    /**
     * Подписка
     */
    this.followToggle = function (button, iUserId) {
        button = $(button);
        if (button.hasClass('followed')) {
            ls.stream.unsubscribe(iUserId);
            button.toggleClass('followed').text(ls.lang.get('profile_user_follow'));
        } else {
            ls.stream.subscribe(iUserId);
            button.toggleClass('followed').text(ls.lang.get('profile_user_unfollow'));
        }
        return false;
    };


    return this;
}).call(ls.user || {},jQuery);

$(function() {
    ls.user.init();
});
