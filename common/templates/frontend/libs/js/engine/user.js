var ls = ls || {};

/**
 * Управление пользователями
 */
ls.user = (function ($) {

    this.jcropImage = null;

    /**
     * Инициализация
     */
    this.init = function() {
        var self = this;

        /* Авторизация */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-login', '.js-form-login', function (result, status, xhr, form) {
            result.sUrlRedirect && (window.location = result.sUrlRedirect);
            ls.hook.run('ls_user_login_after', [form, result]);
        });

        /* Регистрация */
        ls.ajaxForm(ls.routerUrl('registration') + 'ajax-registration', '.js-form-registration', function (result, status, xhr, form) {
            result.sUrlRedirect && (window.location = result.sUrlRedirect);
            ls.hook.run('ls_user_registration_after', [form, result]);
        });

        /* Восстановление пароля */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-reminder', '.js-form-recovery', function (result, status, xhr, form) {
            result.sUrlRedirect && (window.location = result.sUrlRedirect);
            ls.hook.run('ls_user_recovery_after', [form, result]);
        });

        /* Повторный запрос на ссылку активации */
        ls.ajaxForm(ls.routerUrl('login') + 'ajax-reactivation', '.js-form-reactivation', function (result, status, xhr, form) {
            form.find('input').val('');
            ls.hook.run('ls_user_reactivation_after', [form, result]);
        });

        /* Аякс загрузка изображений */
        this.ajaxUploadImageInit({
            selectors: {
                element: '.js-ajax-avatar-upload'
            },
            cropOptions: {
                aspectRatio: 1
            },
            urls: {
                upload: ls.routerUrl('settings') + 'profile/upload-avatar/',
                remove: ls.routerUrl('settings') + 'profile/remove-avatar/',
                cancel: ls.routerUrl('settings') + 'profile/cancel-avatar/',
                crop: ls.routerUrl('settings') + 'profile/resize-avatar/'
            }
        });

        this.ajaxUploadImageInit({
            selectors: {
                element: '.js-ajax-photo-upload'
            },
            urls: {
                upload: ls.routerUrl('settings') + 'profile/upload-photo/',
                remove: ls.routerUrl('settings') + 'profile/remove-photo/',
                cancel: ls.routerUrl('settings') + 'profile/cancel-photo/',
                crop: ls.routerUrl('settings') + 'profile/resize-photo/'
            }
        });

        $('.js-ajax-image-upload-crop-cancel').on('click', function (e) {
            self.ajaxUploadImageCropCancel();
        });

        $('.js-ajax-image-upload-crop-submit').on('click', function (e) {
            self.ajaxUploadImageCropSubmit();
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
        ls.ajax(url, params, function (result) {ls.log('result:', result);
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
    this.addFriend = function (obj, idUser, sAction) {
        if (sAction != 'link' && sAction != 'accept') {
            var sText = $('#add_friend_text').val();
            $('#add_friend_form').children().each(function (i, item) {
                $(item).attr('disabled', 'disabled')
            });
        } else {
            var sText = '';
        }

        if (sAction == 'accept') {
            var url = ls.routerUrl('profile') + 'ajaxfriendaccept/';
        } else {
            var url = ls.routerUrl('profile') + 'ajaxfriendadd/';
        }

        var params = {idUser: idUser, userText: sText};

        ls.ajax(url, params, function (result) {
            $('#add_friend_form').children().each(function (i, item) {
                $(item).removeAttr('disabled')
            });
            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);
                $('#add_friend_form').jqmHide();
                $('#add_friend_item').remove();
                $('#profile_actions').prepend($($.trim(result.sToggleText)));
                ls.hook.run('ls_user_add_friend_after', [idUser, sAction, result], obj);
            }
        });
        return false;
    };

    /**
     * Удаление из друзей
     */
    this.removeFriend = function (obj, idUser, sAction) {
        var url = ls.routerUrl('profile') + 'ajaxfrienddelete/';
        var params = {idUser: idUser, sAction: sAction};

        ls.ajax(url, params, function (result) {
            if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);
                $('#delete_friend_item').remove();
                $('#profile_actions').prepend($($.trim(result.sToggleText)));
                ls.hook.run('ls_user_remove_friend_after', [idUser, sAction, result], obj);
            }
        });
        return false;
    };

    /**
     * Поиск пользователей по началу логина
     */
    this.searchUsersByPrefix = function (sPrefix, obj) {
        obj = $(obj);
        var url = ls.routerUrl('people') + 'ajax-search/';
        var params = {user_login: sPrefix, isPrefix: 1};
        $('#search-user-login').addClass('loader');

        ls.ajax(url, params, function (result) {
            $('#search-user-login').removeClass('loader');
            $('#user-prefix-filter').find('.active').removeClass('active');
            obj.parent().addClass('active');
            if (result.bStateError) {
                $('#users-list-search').hide();
                $('#users-list-original').show();
            } else {
                $('#users-list-original').hide();
                $('#users-list-search').html(result.sText).show();
                ls.hook.run('ls_user_search_users_by_prefix_after', [sPrefix, obj, result]);
            }
        });
        return false;
    };

    /**
     * Подписка
     */
    this.followToggle = function (obj, iUserId) {
        if ($(obj).hasClass('followed')) {
            ls.stream.unsubscribe(iUserId);
            $(obj).toggleClass('followed').text(ls.lang.get('profile_user_follow'));
        } else {
            ls.stream.subscribe(iUserId);
            $(obj).toggleClass('followed').text(ls.lang.get('profile_user_unfollow'));
        }
        return false;
    };

    /**
     * Поиск пользователей
     */
    this.searchUsers = function (form) {
        var url = ls.routerUrl('people') + 'ajax-search/';
        var inputSearch = $('#' + form).find('input');
        inputSearch.addClass('loader');

        ls.ajaxSubmit(url, form, function (result) {
            inputSearch.removeClass('loader');
            if (result.bStateError) {
                $('#users-list-search').hide();
                $('#users-list-original').show();
            } else {
                $('#users-list-original').hide();
                $('#users-list-search').html(result.sText).show();
                ls.hook.run('ls_user_search_users_after', [form, result]);
            }
        });
    };

    this.ajaxUploadImageInit = function(options) {
        var self = this;

        var defaults = {
            cropOptions: {
                minSize: [32, 32]
            },
            selectors: {
                element: '.js-ajax-image-upload',
                image: '.js-ajax-image-upload-image',
                image_crop: '.js-image-crop',
                remove_button: '.js-ajax-image-upload-remove',
                choose_button: '.js-ajax-image-upload-choose',
                input_file: '.js-ajax-image-upload-file',
                crop_cancel_button: '.js-ajax-image-upload-crop-cancel',
                crop_submit_button: '.js-ajax-image-upload-crop-submit'
            },
            urls: {
                upload: ls.routerUrl('settings') + 'profile/upload-avatar/',
                remove: ls.routerUrl('settings') + 'profile/remove-avatar/',
                cancel: ls.routerUrl('settings') + 'profile/cancel-avatar/',
                crop: ls.routerUrl('settings') + 'profile/resize-avatar/'
            }
        };

        var options = $.extend(true, {}, defaults, options);

        $(options.selectors.element).each(function () {
            var $element = $(this);

            var elements = {
                element: $element,
                remove_button: $element.find(options.selectors.remove_button),
                choose_button: $element.find(options.selectors.choose_button),
                image: $element.find(options.selectors.image),
                image_crop: $element.find(options.selectors.image_crop)
            };

            $element.find(options.selectors.input_file).on('change', function () {
                self.currentElements = elements;
                self.currentOptions = options;
                if ($(this).data('resize-form')) {
                    options.resizeForm = $(this).data('resize-form');
                }
                self.ajaxUploadImage(null, $(this), options);
            });

            elements.remove_button.on('click', function (e) {
                self.ajaxUploadImageRemove(options, elements);
                e.preventDefault();
            });
        });
    };

    /**
     * Загрузка временной аватарки
     */
    this.ajaxUploadImage = function (form, input, options) {
        if (!form && input) {
            var form = $('<form method="post" enctype="multipart/form-data"></form>').hide().appendTo('body');

            input.clone(true).insertAfter(input);
            input.appendTo(form);
        }

        ls.ajaxSubmit(options.urls.upload, form, function (data) {
            if (data.bStateError) {
                ls.msg.error(data.sMsgTitle, data.sMsg);
            } else {
                this.ajaxUploadImageModalCrop(data.sTmpFile, options);
            }
            form.remove();
        }.bind(this));
    };

    /**
     * Показывает форму для ресайза аватарки
     */
    this.ajaxUploadImageModalCrop = function (sImgFile, options) {
        var self = this;

        this.jcropImage && this.jcropImage.destroy();

        if (!options.resizeForm) {
            options.resizeForm = '#modal-image-crop';
        }
        if ($(options.resizeForm).length)
            $(options.resizeForm).modal('show');
        else {
            ls.debug('Error [Ajax Image Upload]:\nModal window of image resizing not found');
        }
        var imageCrop = $(options.resizeForm).find('.js-image-crop');
        $(imageCrop).attr('src', sImgFile + '?' + Math.random()).css({
            'width': 'auto',
            'height': 'auto'
        });

        $(imageCrop).Jcrop(options.cropOptions, function () {
            self.jcropImage = this;
            this.setSelect([0, 0, 500, 500]);
        });
    };

    /**
     * Удаление аватарки
     */
    this.ajaxUploadImageRemove = function (options, elements) {
        ls.ajax(options.urls.remove, {}, function (result) {
            if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                elements.image.attr('src', result.sFile + '?' + Math.random());
                elements.remove_button.hide();
                elements.choose_button.text(result.sTitleUpload);

                ls.hook.run('ls_user_remove_avatar_after', [result]);
            }
        });
    };

    /**
     * Отмена ресайза аватарки, подчищаем временный данные
     */
    this.ajaxUploadImageCropCancel = function (button) {
        var button = $(button);
        var modal = button.parents('.modal').first();
        if (!modal.length) {
            modal = $('#modal-image-crop');
        }
        button.addClass('loading');
        ls.ajax(this.currentOptions.urls.cancel, {}, function (result) {
            if (result.bStateError) {
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
    this.ajaxUploadImageCropSubmit = function (button) {
        var self = this;

        if (!this.jcropImage) {
            return false;
        }

        var params = {
            size: this.jcropImage.tellSelect()
        };

        var button = $(button);
        var modal = button.parents('.modal').first();
        if (!modal.length) {
            modal = $('#modal-image-crop');
        }
        button.addClass('loading');
        ls.ajax(self.currentOptions.urls.crop, params, function (result) {
            if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $('<img src="' + result.sFile + '?' + Math.random() + '" />');
                self.currentElements.image.attr('src', result.sFile + '?' + Math.random());
                $(modal).modal('hide');
                self.currentElements.remove_button.show();
                self.currentElements.choose_button.text(result.sTitleUpload);

                ls.hook.run('ls_user_resize_avatar_after', [params, result]);
            }
            button.removeClass('loading');
        });

        return false;
    };

    return this;
}).call(ls.user || {},jQuery);

$(function() {
    ls.user.init();
});
