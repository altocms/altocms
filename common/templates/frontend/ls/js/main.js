;var ls = ls || {};

/* ****************************************************
 * Frontend for LS-compatibility
 */
if (!Function.prototype.bind) {
    Function.prototype.bind = function (context) {
        var fn = this;
        if (jQuery.type(fn) != 'function') {
            throw new TypeError('Function.prototype.bind: call on non-function');
        }

        if (jQuery.type(context) == 'null') {
            throw new TypeError('Function.prototype.bind: cant be bound to null');
        }

        return function () {
            return fn.apply(context, arguments);
        };
    };
    ls.nativeBind = false;
} else {
    ls.nativeBind = true;
}

String.prototype.tr = function (a, p) {
    var $that = this;
    p = typeof(p) == 'string' ? p : '';
    jQuery.each(a, function (k) {
        var tk = p ? p.split('/') : [];
        tk[tk.length] = k;
        var tp = tk.join('/');
        if (typeof(a[k]) == 'object') {
            $that = $that.tr(a[k], tp);
        } else {
            $that = $that.replace((new RegExp('%%' + tp + '%%', 'g')), a[k]);
        }
    });
    return $that;
};

/**
 * Управление всплывающими сообщениями
 */
ls.msg = (function ($) {
    /**
     * Опции
     */
    this.options = {
        classNotice: 'n-notice',
        classError: 'n-error'
    };

    /**
     * Отображение информационного сообщения
     */
    this.notice = function (title, msg) {
        $.notifier.broadcast(title, msg, this.options.classNotice);
    };

    /**
     * Отображение сообщения об ошибке
     */
    this.error = function (title, msg) {
        $.notifier.broadcast(title, msg, this.options.classError);
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
 * Flash загрузчик
 */
ls.swfupload = (function ($) {

    this.swfu = null;
    this.swfOptions = {};

    this.initOptions = function () {

        this.swfOptions = {
            // Backend Settings
            upload_url: aRouter["content"] + "photo/upload",
            post_params: {'SSID': SESSION_ID, 'security_key': ALTO_SECURITY_KEY},

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
            button_text: '<span class="button">' + ls.lang.get('topic_photoset_upload_choose') + '</span>',
            button_text_style: '.button { color: #1F8AB7; font-size: 14px; }',
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_text_left_padding: 6,
            button_text_top_padding: 3,
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

        f.onSwfobject = function () {
            if (window.swfobject && swfobject.swfupload) {
                f.onSwfobjectSwfupload();
            } else {
                if (ls.cfg.assets['swfobject/plugin/swfupload.js']) {
                    ls.debug('window.swfobject && swfobject.swfupload is undefined, load swfobject/plugin/swfupload.js');
                    $.getScript(ls.cfg.assets['swfobject/plugin/swfupload.js'], f.onSwfobjectSwfupload);
                } else {
                    ls.debug('cannot load swfobject/plugin/swfupload.js');
                }
            }
        }.bind(this);

        f.onSwfobjectSwfupload = function () {
            if (window.SWFUpload) {
                f.onSwfupload();
            } else {
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
        this.swfu = new SWFUpload(this.swfOptions);
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
        var text = (BLOG_USE_TINYMCE || (ls.cfg && ls.cfg.wysiwyg)) ? tinyMCE.activeEditor.getContent() : $('#' + textId).val();
        var ajaxUrl = aRouter['ajax'] + 'preview/text/';
        var ajaxOptions = {text: text, save: save};

        if (!divPreview) {
            divPreview = 'text_preview';
        }
        var elementPreview = $('#' + divPreview);
        elementPreview.addClass('loader');

        ls.ajax(ajaxUrl, ajaxOptions, function (result) {
            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(result.sMsgTitle || 'Error', result.sMsg || 'Please try again later');
            } else {
                elementPreview.removeClass('loader');
                elementPreview.html(result.sText);
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
     * Форматирует секунды в оставшееся время
     *
     * @param time
     */
    this.timeRest = function (time) {
        var d, h, m, s;
        if (time < 60) {
            return this.sprintf('%2d sec', time);
        }
        s = time % 60;
        m = (time - s) / 60;
        if (m < 60) {
            return this.sprintf('%2d:%02d', m, s);
        }
        time = m;
        m = time % 60;
        h = (time - m) / 60;
        if (h < 24) {
            return this.sprintf('%2d:%02d:%02d', h, m, s);
        }
        time = h;
        h = time % 24;
        d = (time - h) / 24;
        return this.sprintf('%3d, %2d:%02d:%02d', d, h, m, s);
    };

    /**
     * Return a formatted string
     *
     * @returns {string}
     */
    this.sprintf = function () {
        //
        // +   original by: Ash Searle (http://hexmen.com/blog/)
        // + namespaced by: Michael White (http://crestidg.com)

        var regex = /%%|%(\d+\$)?([-+#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuidfegEG])/g;
        var a = arguments, i = 0, format = a[i++];

        // pad()
        var pad = function (str, len, chr, leftJustify) {
            var padding = (str.length >= len) ? '' : new Array(1 + len - str.length >>> 0).join(chr);
            return leftJustify ? str + padding : padding + str;
        };

        // justify()
        var justify = function (value, prefix, leftJustify, minWidth, zeroPad) {
            var diff = minWidth - value.length;
            if (diff > 0) {
                if (leftJustify || !zeroPad) {
                    value = pad(value, minWidth, ' ', leftJustify);
                } else {
                    value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
                }
            }
            return value;
        };

        // formatBaseX()
        var formatBaseX = function (value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
            // Note: casts negative numbers to positive ones
            var number = value >>> 0;
            prefix = prefix && number && {'2': '0b', '8': '0', '16': '0x'}[base] || '';
            value = prefix + pad(number.toString(base), precision || 0, '0', false);
            return justify(value, prefix, leftJustify, minWidth, zeroPad);
        };

        // formatString()
        var formatString = function (value, leftJustify, minWidth, precision, zeroPad) {
            if (precision != null) {
                value = value.slice(0, precision);
            }
            return justify(value, '', leftJustify, minWidth, zeroPad);
        };

        // finalFormat()
        var doFormat = function (substring, valueIndex, flags, minWidth, _, precision, type) {
            if (substring == '%%') return '%';

            // parse flags
            var leftJustify = false, positivePrefix = '', zeroPad = false, prefixBaseX = false;
            for (var j = 0; flags && j < flags.length; j++) switch (flags.charAt(j)) {
                case ' ':
                    positivePrefix = ' ';
                    break;
                case '+':
                    positivePrefix = '+';
                    break;
                case '-':
                    leftJustify = true;
                    break;
                case '0':
                    zeroPad = true;
                    break;
                case '#':
                    prefixBaseX = true;
                    break;
            }

            // parameters may be null, undefined, empty-string or real valued
            // we want to ignore null, undefined and empty-string values
            if (!minWidth) {
                minWidth = 0;
            } else if (minWidth == '*') {
                minWidth = +a[i++];
            } else if (minWidth.charAt(0) == '*') {
                minWidth = +a[minWidth.slice(1, -1)];
            } else {
                minWidth = +minWidth;
            }

            // Note: undocumented perl feature:
            if (minWidth < 0) {
                minWidth = -minWidth;
                leftJustify = true;
            }

            if (!isFinite(minWidth)) {
                throw new Error('sprintf: (minimum-)width must be finite');
            }

            if (!precision) {
                precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type == 'd') ? 0 : void(0);
            } else if (precision == '*') {
                precision = +a[i++];
            } else if (precision.charAt(0) == '*') {
                precision = +a[precision.slice(1, -1)];
            } else {
                precision = +precision;
            }

            // grab value using valueIndex if required?
            var value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];

            switch (type) {
                case 's':
                    return formatString(String(value), leftJustify, minWidth, precision, zeroPad);
                case 'c':
                    return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
                case 'b':
                    return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                case 'o':
                    return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                case 'x':
                    return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                case 'X':
                    return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad).toUpperCase();
                case 'u':
                    return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                case 'i':
                case 'd':
                {
                    var number = parseInt(+value);
                    var prefix = number < 0 ? '-' : positivePrefix;
                    value = prefix + pad(String(Math.abs(number)), precision, '0', false);
                    return justify(value, prefix, leftJustify, minWidth, zeroPad);
                }
                case 'e':
                case 'E':
                case 'f':
                case 'F':
                case 'g':
                case 'G':
                {
                    var number = +value;
                    var prefix = number < 0 ? '-' : positivePrefix;
                    var method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
                    var textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
                    value = prefix + Math.abs(number)[method](precision);
                    return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
                }
                default:
                    return substring;
            }
        };

        return format.replace(regex, doFormat);
    };
    return this;
}).call(ls.tools || {}, jQuery);


/**
 * Дополнительные функции
 */
ls = (function ($) {
    var $that = this;
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
        params.security_key = ALTO_SECURITY_KEY;

        $.each(params, function (k, v) {
            if (typeof(v) == "boolean") {
                params[k] = v ? 1 : 0;
            }
        });

        if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0) {
            if (url.indexOf('/') != 0) {
                url = aRouter['ajax'] + url + '/';
            } else {
                url = DIR_WEB_ROOT + url + '/'
            }
        }

        var ajaxOptions = {
            type: more.type || "POST",
            url: url,
            data: params,
            dataType: more.dataType || 'json',
            success: callback || function () {
                $that.debug("ajax success: ");
                $that.debug.apply(this, arguments);
            }.bind(this),
            error: more.error || function (msg) {
                $that.debug("ajax error: ");
                $that.debug.apply(this, arguments);
            }.bind(this),
            complete: more.complete || function (msg) {
                $that.debug("ajax complete: ");
                $that.debug.apply(this, arguments);
            }.bind(this)
        };

        $that.hook.run('ls_ajax_before', [ajaxOptions], this);

        return $.ajax(ajaxOptions);
    };

    /**
     * Сохранение данных конфигурации
     *
     * @param params
     * @param callback
     * @param more
     * @returns {*}
     */
    this.ajaxConfig = function (params, callback, more) {
        var url = aRouter['admin'] + 'ajax/config/';
        var args = params;
        params = {
            keys: []
        };
        $.each(args, function (key, val) {
            key = key.replace(/\./g, '--');
            params.keys.push(key);
            params[key] = val;
        });
        return $that.ajax(url, params, callback, more);
    };

    /**
     * Выполнение AJAX отправки формы, включая загрузку файлов
     */
    this.ajaxSubmit = function (url, form, callback, more) {
        more = more || {};
        if (typeof(form) == 'string') {
            form = $('#' + form);
        }
        if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0 && url.indexOf('/') != 0) {
            url = aRouter['ajax'] + url + '/';
        }

        var options = {
            type: 'POST',
            url: url,
            dataType: more.dataType || 'json',
            data: {security_key: ALTO_SECURITY_KEY},
            success: callback || function () {
                $that.debug("ajax success: ");
                $that.debug.apply(this, arguments);
            }.bind(this),
            error: more.error || function () {
                $that.debug("ajax error: ");
                $that.debug.apply(this, arguments);
            }.bind(this)

        };

        $that.hook.run('ls_ajaxsubmit_before', [options], this);

        form.ajaxSubmit(options);
    };

    /**
     * Загрузка изображения
     */
    this.ajaxUploadImg = function (form, sToLoad) {
        $that.ajaxSubmit('upload/image/', form, function (data) {
            if (data.bStateError) {
                $that.msg.error(data.sMsgTitle, data.sMsg);
            } else {
                $that.insertToEditor(data.sText);
                $('#window_upload_img').find('input[type="text"], input[type="file"]').val('');
                $('#window_upload_img').jqmHide();
            }
        });
    };

    this.insertToEditor = function(html) {
        $.markItUp({replaceWith: html});
    }

    /**
     * Определение URL экшена
     *
     * @param action
     */
    this.routerUrl = function(action) {
        if (aRouter && aRouter[action]) {
            return aRouter[action];
        } else {
            return $that.cfg.url.root + action + '/';
        }
    }

    this.getAssetUrl = function(asset) {
        if (this.cfg && this.cfg.assets && this.cfg.assets[asset]) {
            return this.cfg.assets[asset];
        }
    }

    this.getAssetPath = function(asset) {
        var url = this.getAssetUrl(asset);
        if (url) {
            return url.substring(0, url.lastIndexOf('/'));
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
        // Modern browsers
        if (typeof console != 'undefined' && typeof console.log == 'function') {
            Function.prototype.bind.call(console.log, console).apply(console, arguments);
        } else
        // IE8
        if (!ls.nativeBind && typeof console != 'undefined' && typeof console.log == 'object') {
            Function.prototype.call.call(console.log, console, Array.prototype.slice.call(arguments));
        } else {
            //alert(msg);
        }
    };

    return this;
}).call(ls || {}, jQuery);


/**
 * Popup-окна
 */
ls.winModal = (function ($) {
    this.stack = [];

    this.makeElement = function (options) {
        options = options || {};
        var content = '<div class="b-modal-content">' + options.content + '</div>';
        if (options.header) content = '<div class="b-modal-header">' + options.header + '</div>' + content;
        if (options.footer) content = content + '<div class="b-modal-footer">' + options.footer + '</div>';
        content = '<div class="b-modal" style="display: none;">' + content + '</div>';
        return $(content).appendTo('body');
    };

    this.show = function (element) {
        $(element).jqm({modal: true});
        return $(element).jqmShow();
    };

    this.hide = function (element) {
        if (!element && this.stack.length) {
            element = this.stack.pop();
        }
        if (element) {
            return $(element).jqmHide();
        }
    };

    this.close = function (element) {
        element = this.hide(element);
        $(element).remove();
    };

    this.wait = function (message) {
        var options = {
            content: message ? message : 'Please wait... <span class="loader">&nbsp;&nbsp;&nbsp;&nbsp;</span>'
        };
        var win = ls.winModal.makeElement(options);
        this.stack.push(this.show(win));
    };

    return this;
}).call(ls.winModal || {}, jQuery);

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

/*
 * Костыль для исправления модаьного окна
 */
jQuery(function(){
    var modal_write = jQuery('#modal_write');
    if (modal_write.length) {
        modal_write.find('.write-item-type-poll, .write-item-type-link, .write-item-type-photoset').each(function(){
            jQuery(this.remove());
        });
    }
});

/**
 * Костыли для ИЕ
 */
ls.ie = (function ($) {

    // эмуляция border-sizing в IE
    this.bordersizing = function (inputs) {
        if ($('html').hasClass('ie7')) {
            if (!tinyMCE) $('textarea.mce-editor').addClass('markItUpEditor');

            inputs.each(function () {
                var obj = $(this);
                if (obj.css('box-sizing') == 'border-box') {
                    obj.css('width', '100%');
                    obj.width(2 * obj.width() - obj.outerWidth());
                }
            });
        }
    };

    return this;
}).call(ls.ie || {}, jQuery);

(ls.options || {}).debug = 1;

var ALTO_SECURITY_KEY = ALTO_SECURITY_KEY || LIVESTREET_SECURITY_KEY;
