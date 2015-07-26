/*!
 * altoPopover.js
 * Файл jquery-плагина для динамического вывода тултипов
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 * @version     0.0.1 от 19.02.2015 20:42
 * @since       Alto 1.1
 */

/**
 * Пример:
 * <a
 *      data-alto-role="popover"
 *      data-api="user/{$oUser->getId()}"
 *      data-api-param-tpl="default"
 *      data-trigger="click"
 *      data-placement="top"
 *      data-animation="true"
 *      data-cache="false"
 *      class="userlogo" href="#">{$oUser->getDisplayName()}</a>
 *
 * <a
 *      data-alto-popover-api="user/{$oUser->getId()}/info"
 *      href="#">{$oUser->getDisplayName()}</a>
 * Эквивалентно
 * <a
 *      data-alto-role="popover"
 *      data-api="user/{$oUser->getId()}"
 *      href="#">{$oUser->getDisplayName()}</a>
 */

// Объекты jQuery и ls не видны из этого файла, поэтому в JSLint для
// пропуска проверки параметров этой функции они внесены как заранее
// предопределённые

/* global jQuery, ls */

(function ($, ls) {
    "use strict";

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ОБЪЕКТА
    //================================================================================================================
    var altoPopover = function (element, options) {

        // Вызывающий объект в формате jQuery
        this.$element = $(element);

        // Флаг блокирования повторных аякс-запросов
        this.blockButtons = false;

        // Мышь на активном popover-е
        this.over = false;

        // Опции плагина
        this.options = $.extend({}, $.fn.altoPopover.defaultOptions, options, this.$element.data());

        // Инициализация плагина
        this.init();

        return this;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    altoPopover.prototype = {

        /**
         * Инициализация объекта. Вызывается автоматически в
         * конструкторе плагина.
         */
        init: function () {
            var $this = this;

            // Установим параметры поповера
            $this._checkParams();

            // Вывод поповера при клике
            if ($this.options.trigger === 'click') {
                // Поповер выводится при событии на гиперссылке и контейнере
                $this.$element
                    .on('click', function (e) {

                        // Если тултип открывается при клике, то нужно запретить дальнейший
                        // переход по ссылке. По умолчанию - поповер открывается при наведении мыши
                        if ($this.options.trigger === 'click') {
                            e.preventDefault();
                        }

                        // Сформируем поповер и, если нужно, получим его контент аяксом
                        $this._preparePopover();

                        $this.$element.popover('toggle');

                        return false;

                    });
            }
            // Вывод поповера при наведении
            else if ($this.options.trigger === 'hover') {

                // Поповер выводится при событии на гиперссылке и контейнере
                $this.$element[jQuery().hoverIntent ? 'hoverIntent' : 'hover'](
                    function (event) {
                        event.preventDefault();
                        if ($this.$element.next('.popover').css('display') === 'block') {
                            return $this;
                        }
                        // Сформируем поповер и, если нужно, получим его контент аяксом
                        //noinspection JSCheckFunctionSignatures
                        $.altoPopoverCollection().not($this.$element).each(function () {
                            if ($(this).data('bs.popover') !== undefined) {
                                $(this).popover('hide');
                            }
                        });
                        //$this._preparePopover();
                        if ($(this).data('bs.popover') === undefined) {
                            $this._preparePopover();
                        }
                        $this._show();
                    }
                );

                $('body').on('click', function () {
                    $this._hide();
                });

            }

            return $this;
        },

        /**
         * Show popover
         * @private
         */
        _show: function () {
            var $this = this, popover = $this.$element.data('bs.popover');

            if (popover && !popover.tip().is(':visible')) {
                $this.$element.popover('show');
            }
        },

        /**
         * Hide popover
         * @private
         */
        _hide: function () {
            var $this = this, popover = $this.$element.data('bs.popover');

            if (popover && popover.tip().is(':visible')) {
                $this.$element.popover('hide');
            }
        },

        /**
         * Toggle visibility of popover
         * @private
         */
        _toggle: function () {
            var $this = this, popover = $this.$element.data('bs.popover');

            if (popover) {
                if (!popover.tip().is(':visible')) {
                    $this.$element.popover('show');
                } else {
                    $this.$element.popover('hide');
                }
            }
        },

        /**
         * Выводит поповер
         *
         * @returns {altoPopover}
         * @private
         */
        _preparePopover: function () {
            var $this = this,
                params,
                content,
                popoverOptions,
                popover = $this.$element.data('bs.popover');

            if (!$this.options.api) {
                return $this;
            }

            if ($this.options.cache !== false) {
                content = $this._getCachedContent();
            }
            popoverOptions = {
                animation: $this.options.animation,
                content: content ? content : '<div class="alto-popover-content"><div class="loader"></div></div>',
                delay: 0,
                html: true,
                placement: $this.options.placement,
                title: '',
                trigger: 'manual',
                container: $this.options.container
            };

            // Сформируем и покажем поповер
            $this.$element
                .popover(popoverOptions)
                .data('bs.popover')
                .tip()
                .addClass($this.options.selector)
                .addClass('alto-popover');

            popover = $this.$element.data('bs.popover');
            $this.$element.data('bs.popover').tip().mouseenter(function () {
                $this.over = true;
            }).mouseleave(function () {
                $this.over = false;
                $this._hide();
            });

            content = $this._getCachedContent();
            if ($this.options.cache !== false && content) {
                var tipClasses = popover.$tip.prop('class');
                popover.options.content = content;
                popover.options.html = true;
                popover.setContent();
                popover.$tip.addClass(tipClasses);
            } else if ($this.options.cache === false || !content) {
                // При открытии, если нужно, то отправим запрос аяксом
                // к АПИ сайта на получение html всплывающего сообщения
                params = $this._loadParams();
                $this._showLoader();
                ls.ajaxGet(
                    ls.routerUrl('api') + $this.options.api,
                    params,
                    /**
                     * Выведем контент тултипа
                     * @param {{bStateError: {boolean}, result: {json}}} response
                     */
                    function (response) {
                        $this._hideLoader();
                        if (response && response.result) {
                            var result = $.parseJSON(response.result), tipClasses;
                            if (response.bStateError) {
                                ls.msg.error(null, result.error);
                                $this._hide();
                                return;
                            }
                            content = '<div class="alto-popover-content">' + result.data + '</div>';

                            tipClasses = popover.$tip.prop('class');
                            popover.options.content = content;
                            popover.options.html = true;
                            popover.setContent();
                            popover.$tip.addClass(tipClasses);
                            if ($this.options.cache !== false) {
                                $this._setCachedContent(content);
                            }
                        } else {
                            $this._loadError(response);
                        }
                    }
                )
                    .fail(function() {
                        $this._loadError(response);
                    })
            }


            return $this;
        },

        /**
         * Обработка ошибки ajax-запроса
         * @private
         */
        _loadError: function(response) {
            var $this = this;

            $this._hide();
        },

        /**
         * Возвращает контент из кеша
         * @returns
         * @private
         */
        _getCachedContent : function() {
            var $this = this;

            if ($.fn.altoPopover.cachedContent[$this.options.api]) {
                return $.fn.altoPopover.cachedContent[$this.options.api];
            }
        },

        /**
         * Сохраняет контент в кеше
         * @private
         */
        _setCachedContent : function(content) {
            var $this = this;

            $.fn.altoPopover.cachedContent[$this.options.api] = content;
        },

        /**
         * Возвращает параметры API-метода
         * @returns
         * @private
         */
        _loadParams: function () {
            var $this = this,
                data = {},
                key,
                currentParam;

            for (key in $this.options) {
                if ($this.options.hasOwnProperty(key)) {
                    if (key == 'altoRole') {
                        data['role'] = $this.options[key];
                    } else {
                        //noinspection JSUnfilteredForInLoop
                        currentParam = key.match(/apiParam(\S+)/);
                        if (currentParam !== null && currentParam[1] !== undefined) {
                            //noinspection JSUnfilteredForInLoop
                            data[currentParam[1].toLowerCase()] = $this.options[key];
                        }
                    }
                }
            }

            return data;
        },

        /**
         * Проверяет масив на список допустимых знаений и если
         * допустимого значения нет, то возвращает дефолтное.
         *
         * @param {string} needle Искомое значение
         * @param {string[]} haystack Массив для поиска
         * @param {string} def Дефолтное значение
         * @returns {string}
         * @private
         */
        _checkArray: function (needle, haystack, def) {
            var found = false,
                key;
            for (key in haystack) {
                if (haystack.hasOwnProperty(key) && haystack[key] === needle) {
                    return needle;
                }
            }
            if (!found) {
                return def;
            }
        },

        /**
         * Дефолтные параметры перекрываются в дата-атрибутах их
         * необходимо проверить по допустимым значениям, если
         * значение ошибочно, то рабочим принимается дефолтное
         * значение этого параметра
         *
         * @return {bool}
         * @private
         */
        _checkParams: function () {
            var $this = this;

            // Возможно два способа открытия всплывающего сообщения
            // при наведении мышью или при клике на ссылку (click|hover),
            $this.options.trigger = $this._checkArray($this.options.trigger, ['click', 'hover'], 'hover');

            // Положение поповера
            $this.options.placement = $this.options.placement.replace(/\s{2,}/, ' ');
            $this.options.placement = $this._checkArray($this.options.placement, [
                'top', 'left', 'right', 'bottom',
                'auto top', 'auto left', 'auto right', 'auto bottom',
                'auto'
            ], 'auto');

            // Анимация
            $this.options.animation = $this.options.animation !== false;

            // Кэширование
            $this.options.cache = $this.options.cache !== false;

            return true;
        },

        /**
         * Отображает лоадеры формы
         * @private
         */
        _showLoader: function () {
            //ls.progressStart();
            return false;
        },

        /**
         * Скрывает лоадеры формы
         * @private
         */
        _hideLoader: function () {
            //ls.progressDone();
            return false;
        }

    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПЛАГИНА
    //================================================================================================================

    /**
     * Определение плагина
     *
     * @param {boolean|object} option
     * @returns {altoPopover}
     */
    $.fn.altoPopover = function (option) {

        if (typeof option === 'boolean') {
            option = {};
        }

        //noinspection JSUnresolvedFunction
        return this.each(function () {
            var $this = $(this),
                data = $this.data('alto.altoPopover'),
                options = typeof option === 'object' && option;

            if (!data) {
                data = new altoPopover(this, options);
                $this.data('alto.altoPopover', data);
            }

            if (typeof option === 'string') {
                data[option]();
            }

            return $this;
        });

    };

    /**
     * Определение конструктора плагина
     *
     * @type {Function}
     */
    $.fn.altoPopover.Constructor = altoPopover;

    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.altoPopover.defaultOptions = {
        trigger: 'hover',  // Метод отображения поповера, может быть hover|click
        placement: 'auto bottom', // Положение поповера, может быть top|left|bottom|right|auto
        animation: true,   // Анимация при отображении true|false
        cache: true,       // Кэшировать ли данные и отображать только полученный при первом вызове результат true|false
        api: false,        // Вызываемое API,
        selector: '',      // Произвольный селектор
        container: 'body'  // Контейнер
    };

    /**
     * Глобальный кеш контента
     *
     * @type {{}}
     */
    $.fn.altoPopover.cachedContent = {};

    /**
     * Глобальная коллекция элементов
     *
     * @type {{}}
     */
    $.fn.altoPopover.collection = null;

}(window.jQuery, window.ls));


//====================================================================================================================
//      АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ ПЛАГИНА
//====================================================================================================================
jQuery(function ($) {
    "use strict";

    $.altoPopoverCollection = function() {
        if ($.fn.altoPopover.collection === null) {
            $.fn.altoPopover.collection = $('[class|="js-popover"],[class*=" js-popover-"],[data-alto-popover-api],[data-alto-role="popover"]');
        }
        return $.fn.altoPopover.collection;
    };

    $('body').on('DOMSubtreeModified', function() {
        $.fn.altoPopover.collection = null;
    });

    $.altoPopoverCollection().each(function () {
        var element = $(this),
            classes = element.prop('class').split(' '),
            role = element.data('alto-role'),
            options,
            found,
            api;

        // <element class="js-popover-..." >
        if (!$(this).data('alto-popover-api') && (!role || (role && role != 'popover'))) {
            $.each(classes, function(key, val){
                if (val && (found = val.match(/^js-popover-(\w+)-(\d+)/))) {
                    options = false;
                    switch (found[1]) {
                        case 'user':
                            options = { altoRole: 'popover', api: 'user/' + found[2] + '/info' };
                            element.altoPopover(options);
                            return;
                        case 'blog':
                            options = { altoRole: 'popover', api: 'blog/' + found[2] + '/info' };
                            element.altoPopover(options);
                            return;
                        default:
                        // nothing
                    }
                }
            });
        }

        if (api = element.data('alto-popover-api')) {
            // <element data-alto-popover-api="..." >
            options = { altoRole: 'popover', api: element.data('alto-popover-api') };
            element.altoPopover(options);
        } else {
            // <element data-alto-role="popover" data-api="..." ... >
            element.altoPopover(false)
        }
    });

});
