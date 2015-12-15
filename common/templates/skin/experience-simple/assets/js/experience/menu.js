/*!
 * Плагин автоматического сворачивания меню
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью шаблона "Experience Simple"
 * @version     0.0.1 от 21.03.2015 15:31
 */

(function ($) {

    "use strict";

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ОБЪЕКТА
    //================================================================================================================
    var altoCollapsedMenu = function (element, options) {

        // Вызывающий объект в формате jQuery
        this.$element = $(element);

        // Опции плагина
        this.options = $.extend({}, $.fn.altoCollapsedMenu.defaultOptions, options, this.$element.data());

        // Сворачивающаяся часть меню
        this.$collapse = this.$element.find(this.options.collapse);
        this.$hiddenContainer = $(this.options.hidden);

        // Инициализация плагина
        this.init();

        return this;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    altoCollapsedMenu.prototype = {

        /**
         * Устанавливает ширину контейнера сворачиваемого меню
         * @return void
         * @private
         */
        _setCollapsedWidth: function () {

            var $this = this,
                busyWidth = 0,
                key;

            // Если соседние элементы меню не указаны, то использоваться
            // будет дефолтная ширина меню.
            if ($this.options.other.length === 0) {
                return;
            }

            for (key in $this.options.other) {
                if ($this.options.other.hasOwnProperty(key)) {
                    busyWidth += $this.$element.find($this.options.other[key]).outerWidth(true);
                }
            }

            if (busyWidth !== 0) {
                $this.$collapse.css({
                    maxWidth: $this.$element.width() - (busyWidth + $this.options.widthCorrect)
                });
            }

        },

        /**
         * Обновляет меню
         * @private
         */
        _redrawMenu: function () {
            // Установим ширину блока меню
            this._setCollapsedWidth();

            var $this = this,
                $first = $this.$collapse.find('>li:visible').eq(0);

            if (!$first.length) {
                return;
            }

            var
                firstHeight = $first.outerHeight(true),
                firstTop = Math.floor($first.offset().top),
                restore = function () {
                    $($this.$hiddenContainer.find('>li').get().reverse()).each(function () {
                        $(this).insertBefore($this.$hiddenContainer.parent());
                    });
                },
                hide = function () {
                    $this.$hiddenContainer.parent().removeClass('hidden');
                    var $last = $this.$collapse.find('>li:last-child');
                    if ($this.$collapse.find('>li').length < 2) {
                        restore();
                        return false;
                    }
                    if (Math.ceil($last.offset().top) >= (firstTop + firstHeight)) {
                        $last.prev().prependTo($this.$hiddenContainer);
                        hide();
                    }
                    return false;
                };


            // Восстановим
            restore();
            // И спрячем лишнее
            hide();

            // Если нужно, то отобразим кнопку
            if ($this.$hiddenContainer.find('li').length === 0) {
                $this.$hiddenContainer.parent().addClass('hidden');
            } else {
                $this.$hiddenContainer.parent().removeClass('hidden');
            }
        },

        /**
         * Инициализация объекта. Вызывается автоматически в
         * конструкторе плагина.
         */
        init: function () {

            var $this = this;

            $this._redrawMenu();

            // Каждые 0.3 с будем пробовать перестраивать меню в случае ресайза окна
            $(window).resize(function () {
                setTimeout(function () {
                    $this._redrawMenu();
                }, 333);
            });


            return $this;
        }

    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПЛАГИНА
    //================================================================================================================

    /**
     * Определение плагина
     *
     * @param {boolean|object} option
     * @returns {altoCollapsedMenu}
     */
    $.fn.altoCollapsedMenu = function (option) {

        if (typeof option === 'boolean') {
            option = {};
        }

        //noinspection JSUnresolvedFunction
        return this.each(function () {
            var $this = $(this),
                data = $this.data('alto.altoCollapsedMenu'),
                options = typeof option === 'object' && option;

            if (!data) {
                data = new altoCollapsedMenu(this, options);
                $this.data('alto.altoCollapsedMenu', data);
            }

            return $this;
        });

    };

    /**
     * Определение конструктора плагина
     *
     * @type {Function}
     */
    $.fn.altoCollapsedMenu.Constructor = altoCollapsedMenu;

    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.altoCollapsedMenu.defaultOptions = {
        collapse: '.main.nav.nav-content',
        hidden: '.menu-hidden',
        widthCorrect: 60,
        other: [
            '.navbar-header',
            '.navbar-user'
        ]
    };

}(window.jQuery));
