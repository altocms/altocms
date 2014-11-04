/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

var admin = admin || {};

admin.user = (function ($) {
    "use strict"; // jshint ;_;

    var $that = this;

    var init = function() {

        $that.modalWindowAddUser = $('#modal-user_add');
        $that.modalWindowInviteUser = $('#modal-user_invite');
    };

    this.addUserDialog = function() {

        $that.modalWindowAddUser.find('form input').each(function(){
            var input = $(this);
            if (input.attr('name')=='checkbox') {
                input.prop('checked', false);
            } else {
                input.val('');
            }
        });
        $that.modalWindowAddUser.modal('show');
    };

    this.addUserSubmit = function(button) {
        var form = $(button).closest('form'),
            url = ls.routerUrl('/admin/ajax/user/add');

        ls.progressStart();
        ls.ajaxSubmit(url, form, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else {
                form.find('.form-group').removeClass('has-error').addClass('has-success');
                if (response.bStateError) {
                    if (response.aErrors) {
                        $.each(response.aErrors, function(idx, val){
                            $('[name=user_' + idx).parents('.form-group:first').removeClass('has-success').addClass('has-error');
                            ls.msg.error('', val);
                        });
                    } else {
                        ls.msg.error(response.sMsgTitle ? response.sMsgTitle : 'Error', response.sMsg);
                    }
                } else {
                    if (response.sMsg) {
                        ls.msg.notice(response.sMsgTitle ? response.sMsgTitle : '', response.sMsg);
                    }
                    ls.progressStart();
                    window.location.href = ls.routerUrl('admin') + 'users-list/';
                }
            }
        });
    };

    this.inviteUserDialog = function() {

        $that.modalWindowInviteUser.modal('show');
    };

    this.inviteUserSubmit = function(button) {
        var form = $(button).closest('form'),
            url = ls.routerUrl('/admin/ajax/user/invite');

        ls.progressStart();
        ls.ajaxSubmit(url, form, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else {
                if (response.bStateError) {
                    ls.msg.error(response.sMsgTitle ? response.sMsgTitle : 'Error', response.sMsg);
                } else {
                    if (response.sMsg) {
                        ls.msg.notice(response.sMsgTitle ? response.sMsgTitle : '', response.sMsg);
                    }
                    ls.progressStart();
                    window.location.href = ls.routerUrl('admin') + 'users-invites/';
                }
            }
        });
    };

    this.filterReset = function(form) {
        form = $(form);
        form.find('input[type=text]').each(function(){
            $(this).val('').removeClass('success');
        });
        form.submit();
    };

    this.setIpInfo = function(ip1, ip2) {
        var ipList = $('#user-win-iplist');
        ipList.find('.ip-split-reg').text(ip1);
        ipList.find('.ip-split-last').text(ip2);
    };

    this.selectAll = function (element) {
        if ($(element).prop('checked')) {
            $('tr.selectable td.checkbox input[type=checkbox]').prop('checked', true);
            $('tr.selectable').addClass('info');
        } else {
            $('tr.selectable td.checkbox input[type=checkbox]').prop('checked', false);
            $('tr.selectable').removeClass('info');
        }
        admin.user.select();
    };

    this.select = function () {
        var list_id = [], list_login = [];

        $('tr.selectable td.check-row input[type=checkbox]:checked').each(function () {
            var id = parseInt($(this).data('user-id')), login = $(this).data('user-login');
            if (id && login) {
                list_id.push(id);
                list_login.push(login);
            }
            $(this).parents('tr.selectable').addClass('info');
        });

        var users_view = '', users_list_id = list_id.join(', '), users_list_login = list_login.join(', ');
        $.each(list_login, function (index, item) {
            if (users_view) {
                users_view += ', ';
            }
            users_view += '<span class="popup-user">' + item + '</span>';
        });
        $('form input.users_list').each(function () {
            $(this).val(users_list_id);
        });
        $('form input.users_list_login').each(function () {
            $(this).val(users_list_login);
        });
        $('form .users_list_view').each(function () {
            $(this).html(users_view);
        });
    };

    this.unsetAdmin = function(login) {
        var form = $('#user-do-command');
        if (form.length) {
            form.find('[name=adm_user_cmd]').val('adm_user_unsetadmin');
            form.find('[name=users_list]').val(login);
            form.submit();
        }
    };

    this.activate = function(login) {
        var form = $('#user-do-command');
        if (form.length) {
            form.find('[name=adm_user_cmd]').val('adm_user_activate');
            form.find('[name=users_list]').val(login);
            form.submit();
        }
    };

    $(function(){
        init();
    });

    return this;
}).call(admin.user || {}, jQuery);
