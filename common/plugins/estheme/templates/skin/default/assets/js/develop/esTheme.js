/*!
 * esTheme.js
 * Файл скриптов плагина esTheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Воронов
 *              Является частью плагина esTheme
 * @version     0.0.1 от 25.02.2015 20:50
 */

(function ($) {
    "use strict";

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ОБЪЕКТА
    //================================================================================================================
    var esTheme = function (element, options) {

        // Вызывающий объект в формате $
        this.$element = $(element);

        // Флаг блокирования кнопок при аякс-запросе
        this.blockButtons = false;

        // Опции - типа объект
        this.options = $.extend({}, $.fn.esTheme.defaultOptions, options, this.$element.data());

        // Элементы выбора цвета
        this.$colorBoxes = this.$element.find('.js-color-box');

        this.init();

        return this.$element;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    //noinspection JSUnusedGlobalSymbols
    esTheme.prototype = {

        /**
         * Инициализация объекта. Вызывается автоматически в
         * конструкторе плагина.
         */
        init: function () {

            var $this = this;

            $.Color.fn.contrastColor = function () {
                var r = this._rgba[0], g = this._rgba[1], b = this._rgba[2];
                return (((r * 299) + (g * 587) + (b * 144)) / 1000) >= 131.5 ? "#333333" : "#ffffff";
            };

            $this.$colorBoxes
                .colorPicker()
                .each(function(){
                    $(this).css({
                        backgroundColor: $(this).val(),
                        color: $.Color($(this).val()).contrastColor().toString() + ' !important'
                    });
                });

            ls.hook.add('color_picker_after_select', function (cssColor, obj) {

                obj.$input.css({color: $.Color(cssColor).contrastColor().toString() + ' !important'});

            });

            ls.hook.add('color_picker_after_move', function (cssColor, obj) {

                obj.$input.css({color: $.Color(cssColor).contrastColor().toString() + ' !important'});

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
     * @param option
     * @returns {esTheme}
     */
    $.fn.esTheme = function (option) {

        return this.each(function () {
            var $this = $(this);
            var data = $this.data('alto.esTheme');
            var options = typeof option === 'object' && option;

            if (!data) {
                $this.data('alto.esTheme', (data = new esTheme(this, options)));
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
    $.fn.esTheme.Constructor = esTheme;

    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.esTheme.defaultOptions = {

    };

}(window.$));

$(function () {
    $('.js-estheme-panel')
        .esTheme({
            'hello': 'world'
        });
});