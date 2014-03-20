/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */
;
var ls = ls || {};

ls.userfield = ( function ($) {
    "use strict";
    var $that = this;

    this.init = function() {
        this.modalWindow = $('#modal-userfield');
        this.fieldIdPrefix = '#userfield_';
        this.iCountMax = 2;
    }

    this.addUserfieldDialog = function () {
        this.modalWindow.find('.modal-title').text(ls.lang.get('action.admin.user_field_admin_title_add'));
        this.modalWindow.find('.btn-primary').text(ls.lang.get('action.admin.user_field_add'));
        $('#user_fields_form_name').val('');
        $('#user_fields_form_title').val('');
        $('#user_fields_form_id').val('');
        $('#user_fields_form_pattern').val('');
        $('#user_fields_form_type').val('');
        $('#user_fields_form_action').val('add');
        this.modalWindow.modal('show');
        return false;
    };

    this.updateUserfieldDialog = function (id) {
        this.modalWindow.find('.modal-title').text(ls.lang.get('action.admin.user_field_admin_title_edit'));
        this.modalWindow.find('.btn-primary').text(ls.lang.get('action.admin.user_field_update'));
        $('#user_fields_form_action').val('update');
        var field = $(this.fieldIdPrefix + id);

        $('#user_fields_form_name').val(field.find('.userfield_admin_name').text());
        $('#user_fields_form_title').val(field.find('.userfield_admin_title').text());
        $('#user_fields_form_pattern').val(field.find('.userfield_admin_pattern').text());
        $('#user_fields_form_type').find('[value="' + field.find('.userfield_admin_type').text() + '"]').prop('selected', true).trigger('refresh');
        $('#user_fields_form_id').val(id);
        this.modalWindow.modal('show');
        return false;
    };

    this.applyForm = function () {
        this.modalWindow.modal('hide');
        if ($('#user_fields_form_action').val() == 'add') {
            this.addUserfield();
        } else if ($('#user_fields_form_action').val() == 'update') {
            this.updateUserfield();
        }
    };

    this.addUserfield = function () {
        var name = $('#user_fields_form_name').val();
        var title = $('#user_fields_form_title').val();
        var pattern = $('#user_fields_form_pattern').val();
        var type = $('#user_fields_form_type').val();

        var url = ls.routerUrl('admin') + 'settings-userfields';
        var params = {'action': 'add', 'name': name, 'title': title, 'pattern': pattern, 'type': type};

        ls.progressStart();
        ls.ajax(url, params, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (!response.bStateError) {
                var html = $($that.fieldIdPrefix + 'ID').outerHTML();
                var field = $(html.replace('ID', response.id)).show();
                field.find('.userfield_admin_name').text(name);
                field.find('.userfield_admin_title').text(title);
                field.find('.userfield_admin_pattern').text(pattern);
                field.find('.userfield_admin_type').text(type);
                $('#user_field_list').append(field);
                ls.msg.notice(response.sMsgTitle, response.sMsg);
            } else {
                ls.msg.error(response.sMsgTitle, response.sMsg);
            }
        });
    };

    this.updateUserfield = function () {
        var id = $('#user_fields_form_id').val();
        var name = $('#user_fields_form_name').val();
        var title = $('#user_fields_form_title').val();
        var pattern = $('#user_fields_form_pattern').val();
        var type = $('#user_fields_form_type').val();

        var url = ls.routerUrl('admin') + 'settings-userfields';
        var params = {'action': 'update', 'id': id, 'name': name, 'title': title, 'pattern': pattern, 'type': type};

        ls.progressStart();
        ls.ajax(url, params, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (!response.bStateError) {
                var field = $($that.fieldIdPrefix + id);
                field.find('.userfield_admin_name').text(name);
                field.find('.userfield_admin_title').text(title);
                field.find('.userfield_admin_pattern').text(pattern);
                field.find('.userfield_admin_type').text(type);
                ls.hook.run('ls_userfield_update_userfield_after', [params, response]);
                ls.msg.notice(response.sMsgTitle, response.sMsg);
            } else {
                ls.msg.error(response.sMsgTitle, response.sMsg);
            }
        });
    };

    this.deleteUserfield = function (id) {
        var title = ls.lang.get('action.admin.user_field_delete_confirm_title');
        var text = ls.lang.get('action.admin.user_field_delete_confirm_text', {field: $(this.fieldIdPrefix + id).find('.userfield_admin_name').text()});
        ls.modal.confirm(title, text, function () {
            var url = ls.routerUrl('admin') + 'settings-userfields';
            var params = {'action': 'delete', 'id': id};

            ls.progressStart();
            ls.ajax(url, params, function (response) {
                ls.progressDone();
                if (!response) {
                    ls.msg.error(null, 'System error #1001');
                } else if (!response.bStateError) {
                    $($that.fieldIdPrefix + id).remove();
                    ls.msg.notice(response.sMsgTitle, response.sMsg);
                    ls.hook.run('ls_userfield_update_userfield_after', [params, response]);
                } else {
                    ls.msg.error(response.sMsgTitle, response.sMsg);
                }
            });
        });
        return false;
    };

    this.addFormField = function () {
        var tpl = $('#profile_user_field_template').clone();
        /**
         * Находим доступный тип контакта
         */
        var value;
        tpl.find('select').find('option').each(function (k, v) {
            if (this.getCountFormField($(v).val()) < this.iCountMax) {
                value = $(v).val();
                return false;
            }
        }.bind(this));

        if (value) {
            tpl.find('select').val(value);
            $('#user-field-contact-contener').append(tpl.show());
        } else {
            ls.msg.error('', ls.lang.get('settings_profile_field_error_max', {count: this.iCountMax}));
        }
        return false;
    };

    this.changeFormField = function (obj) {
        var iCount = this.getCountFormField($(obj).val());
        if (iCount > this.iCountMax) {
            ls.msg.error('', ls.lang.get('settings_profile_field_error_max', {count: this.iCountMax}));
        }
    };

    this.getCountFormField = function (value) {
        var iCount = 0;
        $('#user-field-contact-contener').find('select').each(function (k, v) {
            if (value == $(v).val()) {
                iCount++;
            }
        });
        return iCount;
    };

    this.removeFormField = function (obj) {
        $(obj).parent('.js-user-field-item').detach();
        return false;
    };

    $(function() {
        ls.userfield.init();
    });

    return this;
}).call(ls.userfield || {}, jQuery);