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

// Create method outerHTML()
// Usage: $(element).outerHTML();
(function($) {
    if (!$.fn.outerHTML) {
        $.fn.extend({
            outerHTML: function() {
                if (this.length) {
                    if (this.get(0).outerHTML) {
                        if (typeof this.get(0).outerHTML == 'function') {
                            return this.get(0).outerHTML();
                        } else {
                            return this.get(0).outerHTML;
                        }
                    } else {
                        return $('<div/>').append(this.clone()).html();
                    }
                }
                return '';
            }
        });
    }
})( jQuery );

var ls = (function ($) {
    /**
     * Log info
     */
    this.log = function () {
        if (window.console && window.console.log) {
            Function.prototype.bind.call(console.log, console).apply(console, arguments);
        } else {
            //alert(msg);
        }
    };

    /**
     * Debug info
     */
    this.debug = function () {
        if (ls.options.debug) {
            ls.log.apply(this, arguments);
        }
    };

    return this;
}).call(ls || {}, jQuery);

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

/* Modal windows */
ls.modal = (function ($) {
    "use strict";
    var emptyWindow;

    /**
     * Options
     */
    this.options = {
        selectorModalEmpty: '#modal-empty',
        classButtonConfirm: 'btn btn-success',
        classButtonCancel: 'btn btn-danger'
    };

    this.init = function (options) {
        if (options) {
            this.options = $.extend(this.options, options);
        }
        emptyWindow = $(this.options.selectorModalEmpty).first();
    };

    this.clear = function() {
        if (emptyWindow.length) {
            emptyWindow.find('.modal-title').empty();
            emptyWindow.find('.modal-body').empty();
            emptyWindow.find('.modal-footer').empty();
            emptyWindow.find('.js-confirm, .js-cancel').off('click');
        }
    };

    this.confirm = function (title, msg, options) {
        if (!title) {
            title = ' ';
        }
        if (typeof options == 'function') {
            options = {onConfirm: options};
        }
        this.clear();
        emptyWindow.find('.modal-title').html(title);
        emptyWindow.find('.modal-body').html(msg);
        emptyWindow.find('.modal-footer').append(
            $('<button class="btn btn-default js-cancel">' + ls.lang.get('text_cancel') + '</button>')
        );
        emptyWindow.find('.modal-footer').append(
            $('<button class="btn btn-primary js-confirm">' + ls.lang.get('text_confirm') + '</button>')
        );

        emptyWindow.find('.js-confirm').on('click', function(){
            emptyWindow.modal('hide');
            if (options.onConfirm) {
                options.onConfirm();
            }
            return false;
        });

        emptyWindow.find('.js-cancel').on('click', function(){
            emptyWindow.modal('hide');
            if (options.onCancel) {
                options.onCancel();
            }
            return false;
        });
        emptyWindow.modal('show');
    };

    this.alert = function () {

    };

    $(function(){
        ls.modal.init();
    });

    return this;
}).call(ls.modal || {}, jQuery);

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
     * Получить текстовку
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
     *
     * @param sUniqKey
     * @param fMethod
     * @param aParams
     * @param iSeconds
     */
    this.run = function (sUniqKey, fMethod, aParams, iSeconds) {
        var timer = {
            id: ls.uniqId(),
            callback: null,
            params: [],
            timeout: 1500
        };

        if (typeof sUniqKey == 'function') {
            // sUniqKey is missed
            timer.id = ls.uniqId();
            timer.callback = sUniqKey;
            timer.params = fMethod ? fMethod : timer.params;
            timer.timeout = parseFloat(aParams) > 0 ? parseFloat(aParams) * 1000 : timer.timeout;
        } else {
            timer.id = sUniqKey;
            timer.callback = fMethod;
            timer.params = aParams ? aParams : timer.params;
            timer.timeout = parseFloat(iSeconds) > 0 ? parseFloat(iSeconds) * 1000 : timer.timeout;
        }

        if (this.aTimers[timer.id]) {
            clearTimeout(this.aTimers[timer.id]);
            this.aTimers[timer.id] = null;
        }
        var timeout = setTimeout(function () {
            clearTimeout(this.aTimers[timer.id]);
            this.aTimers[timer.id] = null;
            timer.callback.apply(this, timer.params);
        }.bind(this), timer.timeout);
        this.aTimers[timer.id] = timeout;
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
        var ajaxUrl = ls.routerUrl('ajax') + 'preview/text/';
        var ajaxOptions = {text: text, save: save};

        ls.ajax(ajaxUrl, ajaxOptions, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(result.sMsgTitle || 'Error', result.sMsg || 'Please try again later');
            } else {
                if (!divPreview) {
                    divPreview = 'text_preview';
                }
                var elementPreview = $('#' + divPreview);
                if (elementPreview.length) {
                    elementPreview.html(result.sText);
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
    var $that = this;

    /**
     * Глобальные опции
     */
    this.options = this.options || {};

    this.options.progressInit = false;
    this.options.progressType = 'syslabel';
    this.options.progressCnt  = 0;

    /**
     * Выполнение AJAX запроса, автоматически передает security key
     */
    this.ajax = function (url, params, callback, more) {
        more = more || {};
        params = params || {};
        params.security_key = ls.cfg.security_key;

        $.each(params, function (k, v) {
            if (typeof(v) == "boolean") {
                params[k] = v ? 1 : 0;
            }
        });

        if (url.indexOf('/') == 0) {
            url = ls.cfg.url.root + url;
        } else if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0) {
            url = ls.routerUrl('ajax') + url ;
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
    };

    this.ajaxPost = function (url, params, callback, more) {
        more = more || {};
        params = params || {};
        more.type = 'POST';
        return this.ajax(url, params, callback, more);
    };

    /**
     * Выполнение AJAX отправки формы, включая загрузку файлов
     */
    this.ajaxSubmit = function (url, form, callback, more) {
        var success = null,
            progressDone = function() { }; // empty function for progress vizualization

        form = $(form);
        more = more || {};
        if (more && more.progress) {
            progressDone = ls.progressDone;
        }

        if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0 && url.indexOf('/') != 0) {
            url = ls.routerUrl('ajax') + url + '/';
        }

        var options = {
            type: 'POST',
            url: url,
            dataType: more.dataType || 'json',
            data: {
                security_key: ls.cfg.security_key
            },
            beforeSubmit: function (arr, form, options) {
                form.find('[type=submit]').prop('disabled', true).addClass('loading');
            }
        };

        if (typeof callback == 'function') {
            options.success = function (result, status, xhr, form) {
                progressDone();
                form.find('[type=submit]').prop('disabled', false).removeClass('loading');
                if (!result) {
                    ls.msg.error(null, 'System error #1001');
                } else if (result.bStateError) {
                    ls.msg.error(null, result.sMsg);

                    if (more && more.warning)
                        more.warning(result, status, xhr, form);
                } else {
                    if (result.sMsg) {
                        ls.msg.notice(null, result.sMsg);
                    }
                    callback(result, status, xhr, form);
                }
            }.bind(this);
        } else {
            options.success = function () {
                progressDone();
                ls.debug("ajax success: ");
                ls.debug.apply(this, arguments);
            }.bind(this);
        }
        options.error = function() {
            if (more.progress) {
                ls.progressDone();
            }
        };

        if (more.error) {
            options.error = function() {
                progressDone();
            }
        } else {
            options.error = more.error || function () {
                progressDone();
                ls.debug("ajax error: ");
                ls.debug.apply(this, arguments);
            }.bind(this);
        }

        ls.hook.run('ls_ajaxsubmit_before', [options, form, callback, more], this);

        if (more.progress) {
            ls.progressStart();
        }
        form.ajaxSubmit(options);
    };

    /**
     * Создание ajax формы
     *
     * @param  {string}          url      Ссылка
     * @param  {jquery, string}  form     Селектор формы либо объект jquery
     * @param  {Function}        callback Success коллбэк
     * @param  {type}            [more]   Дополнительные параметры
     */
    this.ajaxForm = function (url, form, callback, more) {
        form = typeof form == 'string' ? $(form) : form;
        more = $.extend({ progress: true }, more);

        form.on('submit', function (e) {
            ls.ajaxSubmit(url, form, callback, more);
            e.preventDefault();
        });
    };

    /**
     * Uploads image
     */
    this.ajaxUploadImg = function (form, sToLoad) {
        form = $(form);
        var tag = form[0].tagName;
        if (tag != 'FORM' ) {
            form = form.parents('form').first();
        }
        var modal = form.parents('.modal').first();
        $that.progressStart();
        $that.ajaxSubmit('upload/image/', form, function (result) {
            $that.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                $that.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                $that.insertToEditor(result.sText);
                modal.find('input[type="text"], input[type="file"]').val('');
                modal.modal('hide');
            }
        });
    };

    /**
     * Insert html
     *
     * @param html
     */
    this.insertToEditor = function(html) {
        $.markItUp({replaceWith: html});
    };

    /**
     * Saves config data
     *
     * @param params
     * @param callback
     * @param more
     * @returns {*}
     */
    this.ajaxConfig = function(params, callback, more) {
        var url = ls.routerUrl('admin') + '/ajax/config/';
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
     * Returns URL of action
     *
     * @param action
     */
    this.routerUrl = function(action) {
        if (window.aRouter && window.aRouter[action]) {
            return window.aRouter[action];
        } else {
            return ls.cfg.url.root + action + '/';
        }
    };

    /**
     * Returns asset url
     *
     * @param asset
     * @returns {*}
     */
    this.getAssetUrl = function(asset) {
        if (this.cfg && this.cfg.assets && this.cfg.assets[asset]) {
            return this.cfg.assets[asset];
        }
    };

    /**
     * Returns path of asset
     *
     * @param asset
     * @returns {string}
     */
    this.getAssetPath = function(asset) {
        var url = this.getAssetUrl(asset);
        if (url) {
            return url.substring(0, url.lastIndexOf('/'));
        }
    };

    /**
     * Loads asset script
     *
     * @param asset
     * @param success
     */
    this.loadAssetScript = function (asset, success) {
        var url = ls.getAssetUrl(asset);
        if (!url) {
            ls.debug('error: [asset "' + asset + '"] not defined');
        } else {
            $.ajax({
                url: url,
                dataType: 'script'
            })
                .done(function () {
                    ls.debug('success: [asset "' + asset + '"] ajax loaded');
                    success();
                })
                .fail(function () {
                    ls.debug('error: [asset "' + asset + '"] ajax not loaded');
                });
        }
    };

    /**
     * Begins to show progress
     */
    this.progressStart = function() {

        if (!$that.options.progressInit) {
            $that.options.progressInit = true;
            if ($that.options.progressType == 'syslabel') {
                $.SysLabel.init({
                    css: {
                        'z-index': $that.maxZIndex('.modal')
                    }
                });
            }
        }
        if (++$that.options.progressCnt == 1) {
            if ($that.options.progressType == 'syslabel') {
                $.SysLabel.show();
            } else {
                NProgress.start();
            }
        }
    };

    /**
     * Ends to show progress
     */
    this.progressDone = function() {

        if (--$that.options.progressCnt <= 0) {
            if ($that.options.progressType == 'syslabel') {
                $.SysLabel.hide();
            } else {
                NProgress.done();
            }
            $that.options.progressCnt = 0;
        }
    };

    /**
     * Create unique ID
     *
     * @returns {string}
     */
    this.uniqId = function () {
        return 'id-' + new Date().valueOf() + '-' + Math.floor(Math.random() * 1000000000);
    };

    /**
     * Calculate max z-index
     *
     * @param selector
     * @returns {number}
     */
    this.maxZIndex = function(selector) {
        var elements = $.makeArray(selector ? $(selector) : document.getElementsByTagName("*"));
        var max = 0;
        $.each(elements, function(index, item){
            var val = parseFloat($(item).css('z-index')) || 0;
            if (val > max) {
                max = val;
            }
        });
        return max;
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
            obj.bind('keydown', function (event) {
                if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
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

ls.cfg = ls.cfg || { };
ls.cfg.security_key = ls.cfg.security_key || ALTO_SECURITY_KEY;
