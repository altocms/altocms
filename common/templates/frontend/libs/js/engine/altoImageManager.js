/*!
 * altoImageManager.js
 * Файл jquery-плагина менеджера клиентских изображений Alto
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 * @version     0.0.2 от 15.01.2015 11:15
 * @since       Alto 1.1
 */

// Объекты jQuery и ls не видны из этого файла, поэтому в JSLint для
// пропуска проверки параметров этой функции они внесены как заранее
// предопределённые

(function ($, ls) {

    "use strict";

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ОБЪЕКТА
    //================================================================================================================
    var altoImageManager = function (element, options) {

        // Вызывающий объект в формате jQuery
        this.$element = $(element);
        // Флаг блокирования ajax-запроса
        this.blockButtons = false;
        // Опции - типа объект
        this.options = $.extend({}, $.fn.altoImageManager.defaultOptions, options, this.$element.data());

        this.page = 1;
        this.pages = 1;
        this.category = '';

        // Элементы окна
        //this.$btnRefreshTree = this.$element.find('.refresh-tree');
        this.$btnNext = this.$element.find('#images-next-page');
        this.$btnPrev = this.$element.find('#images-prev-page');
        this.$ctrCategories = this.$element.find('#image-categories-tree-container');
        this.$ctrImages = this.$element.find('#image-container');

        this.init();

        return this;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    altoImageManager.prototype = {

        /**
         * Инициализация объекта. Вызывается автоматически в
         * конструкторе плагина.
         */
        init: function () {
            var $this = this;

            $this
                ._refreshCategories()
                ._initTreeButtons()
                ._initNavs();
            //._initBtnRefreshTree();

            return $this;
        },

        _initNavs: function () {
            var $this = this;

            $this.$btnNext.on('click', function () {
                $this._loadPage(parseInt($this.page, 10) + 1);
            });

            $this.$btnPrev.on('click', function () {
                $this._loadPage(parseInt($this.page, 10) - 1);
            });

            return $this;
        },

        _initTreeButtons: function () {
            var $this = this;

            $this.$ctrCategories.find('a').live('click', function () {
                $this.$ctrCategories.find('li').removeClass('active');
                $this.category = $(this)
                    .parent().addClass('active').end()
                    .data('category');
                $this._loadPage(1);
            });

            return $this;
        },

        _refreshNavs: function () {
            var $this = this;

            $this.$btnNext.removeAttr('disabled');
            $this.$btnNext.removeAttr('disabled');
            if ($this.page == $this.pages) {
                $this.$btnNext.attr('disabled', 'disabled');
            }
            if ($this.page == 1) {
                $this.$btnPrev.attr('disabled', 'disabled');
            }

            return $this;
        },

        _loadPage: function (page) {
            var $this = this;

            /**
             * Функция успешного выполнения запроса
             * @param result
             */
            var success = function (result) {
                $this.$ctrImages
                    .fadeOut(200, function () {
                        $this.$ctrImages
                            .html($(result.images))
                            .fadeIn(200, function () {
                                $this.$ctrImages.find('a').on('click', function () {
                                    var link = $(this).find('img').attr('src');
                                    var $html = '<img src="' + link + '" alt="" />';
                                    $.markItUp({replaceWith: $html});
                                    jQuery('#js-alto-image-manager').modal('hide');
                                });
                            });
                    });
                $this.page = result.page;
                $this.pages = result.pages;

                $this._refreshNavs();
            };

            /**
             * Функция, вызываемая при ошибке запроса
             */
            var error = function () {

            };

            /**
             * Данные для отправки серверу
             * @type {{}}
             */
            var data = {page: page, category: $this.category, topic_id: $('#topic_id').val()};

            $this._ajax(
                $this.options.url.loadImages,
                data,
                success,
                error
            );

            return $this;
        },

        /**
         * Обновляет дерево категорий
         * @private
         */
        _refreshCategories: function () {
            var $this = this;

            /**
             * Функция успешного выполнения запроса
             * @param result
             */
            var success = function (result) {
                $this.$ctrCategories.html(result.categories);
            };

            /**
             * Функция, вызываемая при ошибке запроса
             */
            var error = function () {

            };

            /**
             * Данные для отправки серверу
             * @type {{}}
             */
            var data = {};

            $this._ajax(
                $this.options.url.loadTree,
                data,
                success,
                error
            );

            return $this;
        },

        /**
         * Инициализация кнопки обновления дерева
         * @returns {altoImageManager}
         * @private
         */
        //_initBtnRefreshTree: function () {
        //    var $this = this;
        //
        //    $this.$btnRefreshTree.on('click', function () {
        //        $this._refreshCategories();
        //    });
        //
        //    return $this;
        //},

        /**
         * Отображает лоадеры формы
         * @private
         */
        _showLoader: function () {


        },

        /**
         * Скрывает лоадеры формы
         * @private
         */
        _hideLoader: function () {


        },

        /**
         * Обертка ajax-отправки данных
         *
         * @param {string} url Урл обработки
         * @param {object} data Передаваемый данные
         * @param {function} success Функция, исполняемая в случае удачного завершения запроса
         * @param {function|bool} error Функция, выполняемая при ошибке в звпросе
         * @returns {altoImageManager}
         * @private
         */
        _ajax: function (url, data, success, error) {
            var $this = this;

            // Заблокируем другие аякс-запросы и включим лоадеры
            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this._showLoader();
            $this.blockButtons = true;

            // Отправим запрос
            ls.ajax(url, data,
                /**
                 * Обработчик результата запроса
                 * @param {{bStateError: bool}} result
                 */
                function (result) {

                    // Завершим лоадеры
                    ls.progressDone();
                    $this._hideLoader();
                    $this.blockButtons = false;

                    // Вызовем колбэки если необходимо
                    if (!result || result.bStateError == true) {
                        if ($.type(error) == 'function') {
                            error.call($this, result);
                        } else {
                            ls.msg.error(null, 'System error #1001');
                        }
                    } else {
                        if ($.type(success) == 'function') {
                            success.call($this, result);
                        }
                    }

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
     * @returns {altoImageManager}
     */
    $.fn.altoImageManager = function (option) {

        if (typeof option === 'boolean') {
            option = {};
        }

        return this.each(function () {
            var $this = $(this);

            var data = $this.data('alto.altoImageManager');
            var options = typeof option === 'object' && option;

            if (!data) {
                $this.data('alto.altoImageManager', (data = new altoImageManager(this, options)));
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
    $.fn.altoImageManager.Constructor = altoImageManager;

    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.altoImageManager.defaultOptions = {
        url: {
            loadTree: ls.routerUrl('ajax') + 'image-manager-load-tree/',
            loadImages: ls.routerUrl('ajax') + 'image-manager-load-images/'
        }
    };

}(window.jQuery, ls));


//====================================================================================================================
//      АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ ПЛАГИНА
//====================================================================================================================
jQuery(function () {
    jQuery('#js-alto-image-manager').altoImageManager(false);
});