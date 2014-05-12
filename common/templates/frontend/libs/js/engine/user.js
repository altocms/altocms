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
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-login', '.js-form-login', function (result, status, xhr, form) {
            if (result && result.sUrlRedirect) {
                ls.progressStart();
                window.location.href = result.sUrlRedirect
            }
        });

        /* Registration */
        ls.ajaxForm(ls.routerUrl('registration') + 'ajax-registration', '.js-form-registration', function (result, status, xhr, form) {
            if (result && result.sUrlRedirect) {
                ls.progressStart();
                window.location.href = result.sUrlRedirect
            }
        });

        /* Password reset */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-reminder', '.js-form-reminder', function (result, status, xhr, form) {
            if (result && result.sUrlRedirect) {
                ls.progressStart();
                window.location.href = result.sUrlRedirect
            }
        });

        /* Request for activation link */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-reactivation', '.js-form-reactivation', function (result, status, xhr, form) {
            form.find('input').val('');
            ls.hook.run('ls_user_reactivation_after', [form, result]);
        });

        /* Аякс загрузка изображений */
        this.uploadImageInit('avatar', {
            selectors: {
                inputFile: '.js-profile-avatar-file',
                removeButton: '.js-profile-avatar-remove'
            },
            cropOptions: {
                aspectRatio: 1
            },
            url: {
                upload: ls.routerUrl('settings') + 'profile/upload-avatar/',
                remove: ls.routerUrl('settings') + 'profile/remove-avatar/',
                cancel: ls.routerUrl('settings') + 'profile/cancel-avatar/',
                crop: ls.routerUrl('settings') + 'profile/resize-avatar/'
            },
            lang: {
                title: 'settings_profile_avatar_resize_title',
                help: 'settings_profile_avatar_resize_text'
            }
        });

        this.uploadImageInit('photo', {
            selectors: {
                inputFile: '.js-profile-photo-file',
                removeButton: '.js-profile-photo-remove'
            },
            url: {
                upload: ls.routerUrl('settings') + 'profile/upload-photo/',
                remove: ls.routerUrl('settings') + 'profile/remove-photo/',
                cancel: ls.routerUrl('settings') + 'profile/cancel-photo/',
                crop: ls.routerUrl('settings') + 'profile/resize-photo/'
            },
            lang: {
                title: 'settings_profile_photo_resize_title',
                help: 'settings_profile_photo_resize_text'
            }
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

    /* UPLOAD USER'S AVATAR AND PHOTO */

    /**
     * Sets options for the mode
     *
     * @param mode
     * @param options
     */
    this.uploadImageInit = function(mode, options) {
        var defaults = {
            cropOptions: {
                minSize: [32, 32],
                setSelect: [0, 0, 500, 500]
            },
            selectors: {
                inputFile: '.js-image-upload-file',
                removeButton: '.js-image-upload-remove',
                cropModal: '#modal-crop_img',
                cropImage: '.js-crop_img'
            },
            url: {
                upload: ls.routerUrl('settings') + 'profile/upload-avatar/',
                remove: ls.routerUrl('settings') + 'profile/remove-avatar/',
                cancel: ls.routerUrl('settings') + 'profile/cancel-avatar/',
                crop: ls.routerUrl('settings') + 'profile/resize-avatar/'
            }
        };

        options = $.extend(true, {}, defaults, options);
        this.options[mode] = options;

        $(options.selectors.inputFile).on('change', function(){
            $that.uploadImage(this, options);
        });

        $(options.selectors.removeButton).on('click', function(){
            $that.uploadImageRemove(this, options);
            return false;
        });
    };

    /**
     * Remove uploaded image (avatar or photo)
     */
    this.uploadImageRemove = function (button, options) {
        if (typeof options == "string") {
            options = $that.options[options];
        }
        ls.progressStart();
        ls.ajax(options.url.remove, {}, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var img = $($(options.selectors.inputFile).data('target'));
                img.attr('src', result.sFile + '?' + Math.random());
                $(button).css({visibility: 'hidden'});

                ls.hook.run('ls_user_remove_avatar_after', [result]);
            }
        });
    };

    /**
     * Init crop API
     *
     * @param cropImage
     * @param options
     */
    this.uploadImageCropInit = function(cropImage, options) {
        this.uploadImageCropDone();
        $(cropImage).Jcrop(options, function () {
            $that.jcropImage = this;
        });
    }

    /**
     * Destroy crop API
     */
    this.uploadImageCropDone = function() {
        if ($that.jcropImage) {
            $that.jcropImage.release();
            $that.jcropImage.destroy();
        }
    }

    /**
     * Отмена ресайза аватарки, подчищаем временный данные
     */
    this.uploadImageCropCancel = function (button, options) {
        button = $(button);
        if (typeof options == "string") {
            options = $that.options[options];
        }

        var modal = button.parents('.modal').first();
        button.addClass('loading');

        ls.progressStart();
        ls.ajax(options.url.cancel, {}, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $(modal).modal('hide');
                ls.hook.run('ls_user_cancel_avatar_after', [result]);
            }
            button.removeClass('loading');
        });
    };

    /**
     * Выполняет ресайз аватара
     */
    this.uploadImageCropSubmit = function (button, options) {
        button = $(button);
        if (typeof options == "string") {
            options = $that.options[options];
        }

        if (!this.jcropImage) {
            return false;
        }

        var params = {
            size: this.jcropImage.tellSelect()
        };

        var modal = button.parents('.modal').first();
        button.addClass('loading');
        ls.progressStart();
        ls.ajax(options.url.crop, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var img = $($(options.selectors.inputFile).data('target'));
                $('<img src="' + result.sFile + '?' + Math.random() + '" />');
                img.attr('src', result.sFile + '?' + Math.random());
                $(modal).modal('hide');
                $(options.selectors.removeButton).css({visibility: 'visible'});

                ls.hook.run('ls_user_resize_avatar_after', [params, result]);
            }
            button.removeClass('loading');
        });
        return false;
    };

    this.uploadImageModalCrop = function (sImgFile, options) {
        var modal = $(options.selectors.cropModal);
        var cropImage = modal.find(options.selectors.cropImage);

        if (!modal.length) {
            ls.debug('Error [Ajax Image Upload]:\nModal window of image resizing not found');
            return;
        }
        if (options.lang.title) {
            modal.find('.modal-title').text(options.lang.title);
        }
        if (options.lang.title) {
            modal.find('.modal-title').text(ls.lang.get(options.lang.title));
        }
        if (options.lang.help) {
            modal.find('.js-crop_img-help').text(ls.lang.get(options.lang.help));
        }

        modal.modal('show');

        modal.find('.js-confirm').off('click').on('click', function(){
            $that.uploadImageCropSubmit(this, options);
            return false;
        });

        modal.find('.js-cancel').off('click').on('click', function(){
            $that.uploadImageCropCancel(this, options);
            return false;
        });

        $(cropImage).attr('src', sImgFile + '?' + Math.random()).css({
            'width': 'auto',
            'height': 'auto'
        });

        $that.uploadImageCropInit(cropImage, options.cropOptions);
    };

    this.uploadImage = function (input, options) {
        var form = $('<form method="post" enctype="multipart/form-data"/>').hide().appendTo('body'),
            clone;
        input = $(input);
        input.clone(true).insertAfter(input);
        input.removeAttr('id').appendTo(form);

        ls.ajaxSubmit(options.url.upload, form, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                $that.uploadImageModalCrop(result.sTmpFile, options);
            }
            form.remove();
        }, {progress: true});
    };

    return this;
}).call(ls.user || {},jQuery);

$(function() {
    ls.user.init();
});
