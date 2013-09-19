Function.prototype.bind = function (context) {
    var fn = this;
    if (jQuery.type(fn) != 'function') {
        throw new TypeError('Function.prototype.bind: call on non-function');
    }
    ;
    if (jQuery.type(context) == 'null') {
        throw new TypeError('Function.prototype.bind: cant be bound to null');
    }
    ;
    return function () {
        return fn.apply(context, arguments);
    };
};

String.prototype.tr = function (a, p) {
    var k;
    var p = typeof(p) == 'string' ? p : '';
    var s = this;
    jQuery.each(a, function (k) {
        var tk = p ? p.split('/') : [];
        tk[tk.length] = k;
        var tp = tk.join('/');
        if (typeof(a[k]) == 'object') {
            s = s.tr(a[k], tp);
        } else {
            s = s.replace((new RegExp('%%' + tp + '%%', 'g')), a[k]);
        }
    });
    return s;
};


var ls = ls || {};

/**
 * Управление всплывающими сообщениями
 */
ls.msg = (function ($) {
    /**
     * Опции
     */
    this.options = {
        class_notice: 'n-notice',
        class_error: 'n-error'
    };

    /**
     * Отображение информационного сообщения
     */
    this.notice = function (title, msg) {
        $.notifier.broadcast(title, msg, this.options.class_notice);
    };

    /**
     * Отображение сообщения об ошибке
     */
    this.error = function (title, msg) {
        $.notifier.broadcast(title, msg, this.options.class_error);
    };

    return this;
}).call(ls.msg || {}, jQuery);


/**
 * Доступ к языковым текстовкам (предварительно должны быть прогружены в шаблон)
 */
ls.lang = (function ($) {
    /**
     * Набор текстовок
     */
    this.msgs = {};

    /**
     * Загрузка текстовок
     */
    this.load = function (msgs) {
        $.extend(true, this.msgs, msgs);
    };

    /**
     * Отображение сообщения об ошибке
     */
    this.get = function (name, replace) {
        if (this.msgs[name]) {
            var value = this.msgs[name];
            if (replace) {
                value = value.tr(replace);
            }
            return value;
        }
        return '';
    };

    return this;
}).call(ls.lang || {}, jQuery);

/**
 * Методы таймера например, запуск функии через интервал
 */
ls.timer = (function ($) {

    this.aTimers = {};

    /**
     * Запуск метода через определенный период, поддерживает пролонгацию
     */
    this.run = function (fMethod, sUniqKey, aParams, iTime) {
        iTime = iTime || 1500;
        aParams = aParams || [];
        sUniqKey = sUniqKey || Math.random();

        if (this.aTimers[sUniqKey]) {
            clearTimeout(this.aTimers[sUniqKey]);
            this.aTimers[sUniqKey] = null;
        }
        var timeout = setTimeout(function () {
            clearTimeout(this.aTimers[sUniqKey]);
            this.aTimers[sUniqKey] = null;
            fMethod.apply(this, aParams);
        }.bind(this), iTime);
        this.aTimers[sUniqKey] = timeout;
    };

    return this;
}).call(ls.timer || {}, jQuery);

/**
 * Функционал хранения js данных
 */
ls.registry = (function ($) {

    this.aData = {};

    /**
     * Сохранение
     */
    this.set = function (sName, data) {
        this.aData[sName] = data;
    };

    /**
     * Получение
     */
    this.get = function (sName) {
        return this.aData[sName];
    };

    return this;
}).call(ls.registry || {}, jQuery);

/**
 * Загрузка изображений
 */
ls.img = (function ($) {

    this.ajaxUploadInit = function(options) {
        var self = this;

        var defaults = {
            cropOptions: {
                minSize: [32, 32]
            },
            selectors: {
                form: '.js-ajax-image-upload',
                image: '.js-ajax-image-upload-image',
                image_crop: '.js-image-upload-crop',
                remove_button: '.js-ajax-image-upload-remove',
                choose_button: '.js-ajax-image-upload-choose',
                input_file: '.js-ajax-image-upload-file',
                crop_cancel_button: '.js-ajax-image-upload-crop-cancel',
                crop_submit_button: '.js-ajax-image-upload-crop-submit'
            },
            urls: {
                upload: '', // ls.actionUrl('settings') + 'profile/upload-avatar/',
                remove: '',
                cancel: '',
                crop:   ''
            },
            onUploaded: function(imageUrl, options) {
                self.currentForms.image.attr('src', imageUrl + '?' + Math.random());
                if (options.resizeForm) {
                    self.ajaxUploadModalCrop(imageUrl, options);
                }
            }
        };

        var options = $.extend(true, {}, defaults, options);

        $(options.selectors.form).each(function () {
            var $form = $(this);

            var forms = {
                form: $form,
                remove_button:  $form.find(options.selectors.remove_button),
                choose_button:  $form.find(options.selectors.choose_button),
                image:  $form.find(options.selectors.image),
                image_crop:  $form.find(options.selectors.image_crop)
            };

            console.log('===', options.selectors.input_file, $form.find(options.selectors.input_file));
            $form.find(options.selectors.input_file).on('change', function () {
                self.currentForms = forms;
                self.currentOptions = options;
                if ($(this).data('resize-form')) {
                    options.resizeForm = $(this).data('resize-form');
                }
                self.ajaxUpload(null, $(this), options);
            });

            forms.remove_button.on('click', function (e) {
                self.ajaxUploadRemove(options, forms);
                e.preventDefault();
            });
        });
    };

    /**
     * Upload temporary image
     *
     * @param form
     * @param input
     * @param options
     */
    this.ajaxUpload = function(form, input, options) {
        var self = this;

        if ( !form && input ) {
            var form = $('<form method="post" enctype="multipart/form-data"></form>').hide().appendTo('body');

            input.clone(true).insertAfter(input);
            input.appendTo(form);
        }

        ls.ajaxSubmit(options.urls.upload, form, function (data) {
            if (data.bStateError) {
                ls.msg.error(data.sMsgTitle,data.sMsg);
            } else {
                if (options.onUploaded) {
                    if (data.sText && data.sText.match(/^<img\s+/)) {
                        var img = $(data.sText);
                        var url = img.attr('src');
                    } else {
                        url = '';
                    }
                    options.onUploaded(url, options);
                }
            }
            form.remove();
        }.bind(this));
    };

    /**
     * Resize & crop image before final uploading
     *
     * @param sImgFile
     * @param options
     */
    this.ajaxUploadModalCrop = function(sImgFile, options) {
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
        var imageCrop = $(options.resizeForm).find('.js-image-upload-crop');
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
     * Removes uploaded image
     */
    this.ajaxUploadRemove = function(options, elements) {
        ls.ajax(options.urls.remove, {}, function(result) {
            if (result.bStateError) {
                ls.msg.error(null,result.sMsg);
            } else {
                elements.image.attr('src', result.sFile + '?' + Math.random());
                elements.remove_button.hide();
                elements.choose_button.text(result.sTitleUpload);
            }
        });
    };

    /**
     * Cancels drop/resizing
     */
    this.ajaxUploadCropCancel = function (button) {
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
            }
            button.removeClass('loading');
        });
    };

    /**
     * Crop/Resize uploaded image
     */
    this.ajaxUploadCropSubmit = function (button) {
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
                self.currentForms.image.attr('src', result.sFile + '?' + Math.random());
                $(modal).modal('hide');
                self.currentForms.remove_button.show();
                self.currentForms.choose_button.text(result.sTitleUpload);
            }
            button.removeClass('loading');
        });

        return false;
    };

    return this;
}).call(ls.img || {}, jQuery);

/**
 * Flash загрузчик
 */
ls.swfupload = (function ($) {

    this.swfu = null;
    this.swfOptions = {};

    this.initOptions = function () {

        this.swfOptions = {
            // Backend Settings
            upload_url: ls.actionUrl("content") + "photo/upload",
            post_params: {'SSID': SESSION_ID, 'security_ls_key': ls.cfg.security_key},

            prevent_swf_caching : false,

            // File Upload Settings
            file_types: "*.jpg;*.jpe;*.jpeg;*.png;*.gif;*.JPG;*.JPE;*.JPEG;*.PNG;*.GIF",
            file_types_description: "Images",
            file_upload_limit: "0",

            // Event Handler Settings
            file_queue_error_handler: this.handlerFileQueueError,
            file_dialog_complete_handler: this.handlerFileDialogComplete,
            upload_progress_handler: this.handlerUploadProgress,
            upload_error_handler: this.handlerUploadError,
            upload_success_handler: this.handlerUploadSuccess,
            upload_complete_handler: this.handlerUploadComplete,

            // Button Settings
            button_placeholder_id: "start-upload",
            button_width: 122,
            button_height: 30,
            button_text: '<span class="swfupload">' + ls.lang.get('topic_photoset_upload_choose') + '</span>',
            button_text_style: '.swfupload { color: #777777; font-size: 14px; }',
            button_text_left_padding: 0,
            button_text_top_padding: 0,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_cursor: SWFUpload.CURSOR.HAND,

            // Flash Settings
            flash_url: ls.cfg.assets['swfupload/swfupload.swf'],

            custom_settings: {
            },

            // Debug Settings
            debug: false
        };

        ls.hook.run('ls_swfupload_init_options_after', arguments, this.swfOptions);

    };

    this.loadSwf = function () {
        var f = {};

        f.onSwfobject = function(){
            if(window.swfobject && swfobject.swfupload){
                f.onSwfobjectSwfupload();
            }else{
                if (ls.cfg.assets['swfobject/plugin/swfupload.js']) {
                    ls.debug('window.swfobject && swfobject.swfupload is undefined, load swfobject/plugin/swfupload.js');
                    $.getScript(ls.cfg.assets['swfobject/plugin/swfupload.js'], f.onSwfobjectSwfupload);
                } else {
                    ls.debug('cannot load swfobject/plugin/swfupload.js');
                }
            }
        }.bind(this);

        f.onSwfobjectSwfupload = function(){
            if(window.SWFUpload){
                f.onSwfupload();
            }else{
                if (ls.cfg.assets['swfupload/swfupload.js']) {
                    ls.debug('window.SWFUpload is undefined, load swfupload/swfupload.js');
                    $.getScript(ls.cfg.assets['swfupload/swfupload.js'], f.onSwfupload);
                } else {
                    ls.debug('cannot load swfupload/swfupload.js');
                }
            }
        }.bind(this);

        f.onSwfupload = function () {
            this.initOptions();
            $(this).trigger('load');
        }.bind(this);


        (function () {
            if (window.swfobject) {
                //f.onSwfobject();
                f.onSwfobjectSwfupload();
            } else {
                ls.debug('window.swfobject is undefined, need to load swfobject/swfobject.js');
            }
        }.bind(this))();
    };


    this.init = function (opt) {
        if (opt) {
            $.extend(true, this.swfOptions, opt);
        }
        var placeholder = $('#' + this.swfOptions.button_placeholder_id);
        var label = placeholder.parent("label");
        if (placeholder.length) {
            var color = placeholder.css('color'), re = /^rgb(a)?\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)/ig;
            var r=re.exec(color);
            if (r) {
                var n = parseInt(r[2]) * 65536 + parseInt(r[3]) * 256 + parseInt(r[4]);
                color = '#' + n.toString(16);
            }
            var style = '.swfupload {color:' + color.toUpperCase() + '; '
                + 'font-size:' + placeholder.css('font-size') + '; '
                + 'font-family:' + placeholder.css('font-family') + '; '
                + 'font-style:' + placeholder.css('font-style') + '; '
                + 'font-weight:' + placeholder.css('font-weight') + '; '
                + 'text-align:' + placeholder.css('text-align') + '; '
                + '}';
            this.swfOptions.button_text_style = style;
            if (label.length) {
                this.swfOptions.button_width = parseInt(label.outerWidth());
                this.swfOptions.button_text_top_padding = parseInt(label.css('padding-top'));
            }
        }
        this.swfu = new SWFUpload(this.swfOptions);
        if (label.length) {
            $(label).css("padding", "0").click(function(){ return false; });
        }
        return this.swfu;
    };

    this.handlerFileQueueError = function (file, errorCode, message) {
        $(this).trigger('eFileQueueError', [file, errorCode, message]);
    };

    this.handlerFileDialogComplete = function (numFilesSelected, numFilesQueued) {
        $(this).trigger('eFileDialogComplete', [numFilesSelected, numFilesQueued]);
        if (numFilesQueued > 0) {
            this.startUpload();
        }
    };

    this.handlerUploadProgress = function (file, bytesLoaded) {
        var percent = Math.ceil((bytesLoaded / file.size) * 100);
        $(this).trigger('eUploadProgress', [file, bytesLoaded, percent]);
    };

    this.handlerUploadError = function (file, errorCode, message) {
        $(this).trigger('eUploadError', [file, errorCode, message]);
    };

    this.handlerUploadSuccess = function (file, serverData) {
        $(this).trigger('eUploadSuccess', [file, serverData]);
    };

    this.handlerUploadComplete = function (file) {
        var next = this.getStats().files_queued;
        if (next > 0) {
            this.startUpload();
        }
        $(this).trigger('eUploadComplete', [file, next]);
    };

    return this;
}).call(ls.swfupload || {}, jQuery);


/**
 * Вспомогательные функции
 */
ls.tools = (function ($) {

    /**
     * Переводит первый символ в верхний регистр
     */
    this.ucfirst = function (str) {
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1, str.length - 1);
    };

    /**
     * Выделяет все chekbox с определенным css классом
     */
    this.checkAll = function (cssclass, checkbox, invert) {
        $('.' + cssclass).each(function (index, item) {
            if (invert) {
                $(item).attr('checked', !$(item).attr("checked"));
            } else {
                $(item).attr('checked', $(checkbox).attr("checked"));
            }
        });
    };

    /**
     * Предпросмотр
     */
    this.textPreview = function (textId, save, divPreview) {
        var text = ls.cfg.wysiwyg ? tinyMCE.activeEditor.getContent() : $('#' + textId).val();
        var ajaxUrl = ls.actionUrl('ajax') + 'preview/text/';
        var ajaxOptions = {text: text, save: save};
        ls.hook.marker('textPreviewAjaxBefore');
        ls.ajax(ajaxUrl, ajaxOptions, function (result) {
            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(result.sMsgTitle || 'Error', result.sMsg || 'Please try again later');
            } else {
                if (!divPreview) {
                    divPreview = 'text_preview';
                }
                var elementPreview = $('#' + divPreview);
                ls.hook.marker('textPreviewDisplayBefore');
                if (elementPreview.length) {
                    elementPreview.html(result.sText);
                    ls.hook.marker('textPreviewDisplayAfter');
                }
            }
        });
    };

    /**
     * Возвращает выделенный текст на странице
     */
    this.getSelectedText = function () {
        var text = '';
        if (window.getSelection) {
            text = window.getSelection().toString();
        } else if (window.document.selection) {
            var sel = window.document.selection.createRange();
            text = sel.text || sel;
            if (text.toString) {
                text = text.toString();
            } else {
                text = '';
            }
        }
        return text;
    };

    /**
     * Получает значение атрибута data
     */
    this.getOption = function (element, data, defaultValue) {
        var option = element.data(data);

        switch (option) {
            case 'true':
                return true;
            case 'false':
                return false;
            case undefined:
                return defaultValue
            default:
                return option;
        }
    };

    this.getDataOptions = function (element, prefix) {
        var prefix = prefix || 'option',
            resultOptions = {},
            dataOptions = typeof element === 'string' ? $(element).data() : element.data();

        for (option in dataOptions) {
            // Remove 'option' prefix
            if (option.substring(0, prefix.length) == prefix) {
                var str = option.substring(prefix.length);
                resultOptions[str.charAt(0).toLowerCase() + str.substring(1)] = dataOptions[option];
            }
        }

        return resultOptions;
    };

    return this;
}).call(ls.tools || {}, jQuery);


/**
 * Дополнительные функции
 */
ls = (function ($) {

    /**
     * Глобальные опции
     */
    this.options = this.options || {};

    /**
     * Выполнение AJAX запроса, автоматически передает security key
     */
    this.ajax = function (url, params, callback, more) {
        more = more || {};
        params = params || {};
        params.security_ls_key = ls.cfg.security_key;

        $.each(params, function (k, v) {
            if (typeof(v) == "boolean") {
                params[k] = v ? 1 : 0;
            }
        });

        if (url.indexOf('/') == 0) {
            url = ls.cfg.url.root + url;
        } else if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0) {
            url = aRouter['ajax'] + url ;
        }
        if (url.substring(url.length-1) != '/') {
            url += '/';
        }

        var ajaxOptions = $.extend({}, {
            type: 'POST',
            url: url,
            data: params,
            dataType: 'json',
            success: callback || function () {
                ls.debug("ajax success: ");
                ls.debug.apply(this, arguments);
            }.bind(this),
            error: function (msg) {
                ls.debug("ajax error: ");
                ls.debug.apply(this, arguments);
            }.bind(this),
            complete: function (msg) {
                ls.debug("ajax complete: ");
                ls.debug.apply(this, arguments);
            }.bind(this)
        }, more);

        var beforeSendFunc = ajaxOptions.beforeSend ? ajaxOptions.beforeSend : null;
        ajaxOptions.beforeSend = function (xhr) {
            xhr.setRequestHeader('X-Powered-By', 'Alto CMS');
            xhr.setRequestHeader('X-Alto-Ajax-Key', ls.cfg.security_key);
            if (beforeSendFunc) {
                beforeSendFunc(xhr);
            }
        }

        ls.hook.run('ls_ajax_before', [ajaxOptions, callback, more], this);

        return $.ajax(ajaxOptions);
    };

    this.ajaxGet = function (url, params, callback, more) {
        more = more || {};
        params = params || {};
        more.type = 'GET';
        return this.ajax(url, params, callback, more);
    }

    this.ajaxPost = function (url, params, callback, more) {
        more = more || {};
        params = params || {};
        more.type = 'POST';
        return this.ajax(url, params, callback, more);
    }

    /**
     * Выполнение AJAX отправки формы, включая загрузку файлов
     */
    this.ajaxSubmit = function (url, form, callback, more) {
        var more = more || {}
        form = typeof form == 'string' ? $(form) : form;

        if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0 && url.indexOf('/') != 0) {
            url = aRouter['ajax'] + url + '/';
        }

        var options = {
            type: 'POST',
            url: url,
            dataType: more.dataType || 'json',
            data: {
                security_ls_key: ls.cfg.security_key
            },
            beforeSubmit: function (arr, form, options) {
                form.find('[type=submit]').prop('disabled', true).addClass('loading');
            },
            beforeSerialize: function (form, options) {
                return form.parsley('validate');
            },
            success: typeof callback == 'function' ? function (result, status, xhr, form) {
                if (result.bStateError) {
                    form.find('[type=submit]').prop('disabled', false).removeClass('loading');
                    ls.msg.error(null, result.sMsg);

                    if (more && more.warning)
                        more.warning(result, status, xhr, form);
                } else {
                    if (result.sMsg) {
                        form.find('[type=submit]').prop('disabled', false).removeClass('loading');
                        ls.msg.notice(null, result.sMsg);
                    }
                    callback(result, status, xhr, form);
                }
            } : function () {
                ls.debug("ajax success: ");
                ls.debug.apply(this, arguments);
            }.bind(this),
            error: more.error || function () {
                ls.debug("ajax error: ");
                ls.debug.apply(this, arguments);
            }.bind(this)
        };

        ls.hook.run('ls_ajaxsubmit_before', [options, form, callback, more], this);

        form.ajaxSubmit(options);
    };

    /**
     * Создание ajax формы
     *
     * @param  {string}          url      Ссылка
     * @param  {jquery, string}  form     Селектор формы либо объект jquery
     * @param  {Function}        callback Success коллбэк
     * @param  {[type]}          more     Дополнительные параметры
     */
    this.ajaxForm = function (url, form, callback, more) {
        var form = typeof form == 'string' ? $(form) : form;

        form.on('submit', function (e) {
            ls.ajaxSubmit(url, form, callback, more);
            e.preventDefault();
        });
    };

    /**
     * Сохранение данных конфигурации
     *
     * @param params
     * @param callback
     * @param more
     * @returns {*}
     */
    this.ajaxConfig = function(params, callback, more) {
        var url = '/admin/ajax/config/';
        var args = params;
        params = {
            keys: []
        };
        $.each(args, function(key, val) {
            key = key.replace(/\./g, '--');
            params.keys.push(key);
            params[key] = val;
        });
        return ls.ajaxPost(url, params, callback, more);
    };

    /**
     * Определение URL экшена
     *
     * @param action
     */
    this.actionUrl = function(action) {
        if (aRouter && aRouter[action]) {
            return aRouter[action];
        } else {
            return ls.cfg.url.root + action + '/';
        }
    }

    /**
     * Дебаг сообщений
     */
    this.debug = function () {
        if (this.options.debug) {
            this.log.apply(this, arguments);
        }
    };

    /**
     * Лог сообщений
     */
    this.log = function () {
        if (window.console && window.console.log) {
            Function.prototype.bind.call(console.log, console).apply(console, arguments);
        } else {
            //alert(msg);
        }
    };

    return this;
}).call(ls || {}, jQuery);


/**
 * Автокомплитер
 */
ls.autocomplete = (function ($) {
    /**
     * Добавляет автокомплитер к полю ввода
     */
    this.add = function (obj, sPath, multiple) {
        if (multiple) {
            obj.bind("keydown", function (event) {
                if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
                    event.preventDefault();
                }
            })
                .autocomplete({
                    source: function (request, response) {
                        ls.ajax(sPath, {value: ls.autocomplete.extractLast(request.term)}, function (data) {
                            response(data.aItems);
                        });
                    },
                    search: function () {
                        var term = ls.autocomplete.extractLast(this.value);
                        if (term.length < 2) {
                            return false;
                        }
                    },
                    focus: function () {
                        return false;
                    },
                    select: function (event, ui) {
                        var terms = ls.autocomplete.split(this.value);
                        terms.pop();
                        terms.push(ui.item.value);
                        terms.push("");
                        this.value = terms.join(", ");
                        return false;
                    }
                });
        } else {
            obj.autocomplete({
                source: function (request, response) {
                    ls.ajax(sPath, {value: ls.autocomplete.extractLast(request.term)}, function (data) {
                        response(data.aItems);
                    });
                }
            });
        }
    };

    this.split = function (val) {
        return val.split(/,\s*/);
    };

    this.extractLast = function (term) {
        return ls.autocomplete.split(term).pop();
    };

    return this;
}).call(ls.autocomplete || {}, jQuery);

/**
 * Костыли для ИЕ
 */
ls.ie = (function ($) {

    return this;
}).call(ls.ie || {}, jQuery);

(ls.options || {}).debug = 1;

var ALTO_SECURITY_KEY = ALTO_SECURITY_KEY || LIVESTREET_SECURITY_KEY;
