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

        this.profile = this.options.profile === undefined ? false : this.options.profile;

        this.page = 1;
        this.pages = 1;
        this.category = this.profile ? 'topic' : 'insert-from-pc';
        this.prev_category = null;
        this.topicId = 0;

        // Элементы окна
        this.$btnRefreshTree = this.$element.find('.image-categories-nav-refresh');
        this.$btnTriggerParams = this.$element.find('.image-categories-nav-trigger');
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

            if ($this.profile) {
                // Если профиль, то сначала грузим топики
                $this._loadPage(1, 'undefined');
            } else {
                // Загрузка списка категорий
                $this._refreshCategories();
            }

            // Инициализация списка категорий и элементов управления
            $this.elements.btnRefreshTree($this);                       // Кнопка обновления меню окна
            $this.elements.btnTriggerParams($this);                     // Кнопка сворачивания/разворачивания параметров
            $this.elements.blockCategoriesButtons($this);               // Кнопки выбора категорий

            // Инициализация первой страницы
            $this._initPages();

            return $this;
        },

        /**
         * Инициализация загружаемой динамичести страницы
         *
         * @returns {altoImageManager}
         * @private
         */
        _initPages: function () {
            var $this = this;

            this.$pageUploadPc = this.$element.find('#aim-page-pc');
            this.$pageUploadLink = this.$element.find('#aim-page-link');
            this.$pageImages = this.$element.find('#aim-page-images');
            this.$blockParams = this.$element.find('#aim-params');
            this.$imagesNav = this.$element.find('#aim-images-nav');
            this.$topicButtons = this.$element.find('.aim-topic-photoset');
            this.$talkButtons = this.$element.find('.aim-talk-photoset');

            $this.elements.pageUploadPc($this);       // Страница добавления картинки с компьютера
            $this.elements.pageUploadLink($this);     // Страница добавления картинки из интернета
            $this.elements.pageImages($this);         // Страница картинок
            $this.elements.imagesNav($this);          // Кнопки постраничной навигации
            $this.elements.blockParams($this);        // Блок параметров изображения на странице
            $this.elements.topicButtons($this);       // Кнопки выбора топика
            $this.elements.talkButtons($this);        // Кнопки выбора письма

            return $this;
        },

        /**
         * Загружает страницу
         *
         * @param {int} page
         * @returns {altoImageManager}
         * @param {string} topicId
         */
        _loadPage: function (page, topicId) {
            var $this = this;

            /**
             * Функция успешного выполнения запроса
             * @param result
             */
            var success = function (result) {
                ls.progressDone();
                $this.$ctrImages
                    .fadeOut(200, function () {
                        $this.$ctrImages
                            .html($(result.images))
                            .fadeIn(200, function () {

                                $this._initPages();
                                $this.$element.trigger('aim-load-page-success', result);

                            });
                    });
                $this.page = result.page;
                $this.pages = result.pages;

                // Загрузка списка категорий после загрузки картинок
                if ($this.profile && $.trim($this.$ctrCategories.text()) == '') {
                    $this._refreshCategories();
                }

            };

            /**
             * Функция, вызываемая при ошибке запроса
             */
            var error = function () {
                ls.progressDone(true);
                $this.$element.trigger('aim-load-page-error');
                ls.msg.error(null, 'System error #1001');
            };

            /**
             * Данные для отправки серверу
             * @type {{}}
             */
            var data = {page: page, category: $this.category, topic_id: topicId, target: $this.$ctrImages.data('target')};

            ls.progressStart();
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
         */
        _refreshCategories: function () {
            var $this = this;

            /**
             * Функция успешного выполнения запроса
             * @param result
             */
            var success = function (result) {
                $this.$ctrCategories.html(result.categories);
                $this.$element.trigger('aim-refresh-categories-success', result);
                // Отрисуем кнопку
                $this.$ctrCategories.find('li').removeClass('active');
                // Запомним категорию
                $this.$ctrCategories.find('[data-category="' + $this.category + '"]').parent('li')
                    .addClass('active');
            };

            /**
             * Функция, вызываемая при ошибке запроса
             */
            var error = function () {
                ls.progressDone(true);
                $this.$element.trigger('aim-refresh-categories-error');
                ls.msg.error(null, 'System error #1001');
            };

            /**
             * Данные для отправки серверу
             * @type {{}}
             */
            var data = {
                topic_id: $('#topic_id').val(),
                profile: $this.profile
            };

            $this.$element.trigger('aim-refresh-categories-start');

            $this._ajax(
                $this.options.url.loadTree,
                data,
                success,
                error
            );

            return $this;
        },

        /**
         * Получает ид. топика, страница которого открыта
         *
         * @returns {*}
         */
        _getTopicId: function () {
            return $('#topic_id').val();
        },

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
         */
        _ajax: function (url, data, success, error) {
            var $this = this, more = {};

            // Заблокируем другие аякс-запросы и включим лоадеры
            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this._showLoader();
            $this.blockButtons = true;

            var queryType = 'ajax';
            if (data.type == 'submit') {
                data = data.form;
                queryType = 'ajaxSubmit';
            }

            data.profile = $this.profile;

            // что бы можно было понять, что запрос пришел из админки
            // и подставить нужную тему оформления
            if ($this.options.admin !== undefined) {
                data.admin = $this.options.admin;
            }

            if ($.type(error) == 'function') {
                more.error = error;
            }
            more.complete = function() {
                $this.blockButtons = false;
            };

            // Отправим запрос
            ls[queryType](url, data,
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
                            if (result && result.sMsg) {
                                ls.msg.error(result.sMsgTitle ? result.sMsgTitle : '', result.sMsg);
                            } else {
                                ls.msg.error(null, 'System error #1001');
                            }
                        }
                    } else {
                        if ($.type(success) == 'function') {
                            success.call($this, result);
                        }
                    }

                }, more);

            return $this;
        }

    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ РАСШИРЕННЫХ СВОЙСТВ ОБЪЕКТА
    //================================================================================================================
    altoImageManager.prototype.elements = {
        /**
         * Кнопки выбора картинок письма
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        talkButtons: function ($this) {

            var backButtons = $('#backTalks');

            $this.$talkButtons
                .off('click')
                .on('click', function () {
                    $this.category = 'talk';
                    $this.topicId = $(this).data('talk-id');
                    $this._loadPage(1, $this.topicId);
                    return false;
                });

            $this.$element
                .off('aim-load-page-success.talkButtons')
                .on('aim-load-page-success.talkButtons', function (e, data) {
                    if (data.category == 'talk') {
                        backButtons.fadeIn(200);
                    } else {
                        backButtons.fadeOut(200);
                    }
                });

            backButtons
                .off('click')
                .on('click', function () {
                    $this.category = 'talks';
                    $this.topicId = $this._getTopicId();
                    $this._loadPage(1, $this.topicId);
                    return false;
                });

            return $this.$talkButtons;
        },
        /**
         * Кнопки выбора картинок топика
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        topicButtons: function ($this) {

            var backButtons = $('#backTopics');

            $this.$topicButtons
                .off('click')
                .on('click', function () {
                    $this.prev_category = $this.category;
                    $this.category = '_topic';
                    $this.topicId = $(this).data('topic-id');
                    $this._loadPage(1, $this.topicId);
                    return false;
                });

            $this.$element
                .off('aim-load-page-success.topicButtons')
                .on('aim-load-page-success.topicButtons', function (e, data) {
                    if (data.category == '_topic') {
                        backButtons.fadeIn(200);
                    } else {
                        backButtons.fadeOut(200);
                    }
                });

            backButtons
                .off('click')
                .on('click', function () {
                    //$this.category = 'topics';
                    $this.category = $this.prev_category;
                    $this.topicId = $this._getTopicId();
                    $this._loadPage(1, $this.topicId);
                    return false;
                });

            return $this.$topicButtons;
        },

        /**
         * Кнопки постраничной навигации
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        imagesNav: function ($this) {

            $this.$imagesNav.setEmptyPages = function () {
                $('#aim-pages-container').text('');
                return $this.$imagesNav;
            };

            $this.$imagesNav.setEmptyPages();

            var $btnNext = $this.$imagesNav.find('#images-next-page');
            var $btnPrev = $this.$imagesNav.find('#images-prev-page');

            $this.$imagesNav.setRefresh = function () {
                $btnPrev.removeAttr('disabled');
                $btnNext.removeAttr('disabled');
                if ($this.page == $this.pages) {
                    $btnNext.attr('disabled', 'disabled');
                }
                if ($this.page == 1) {
                    $btnPrev.attr('disabled', 'disabled');
                }
                return $this.$imagesNav;
            };

            $this.$imagesNav.setPages = function () {

                if ($this.pages === undefined || $this.pages < 2) {
                    $this.$imagesNav.setEmptyPages();
                    return $this.$imagesNav;
                }

                var tpl = $('#aim-pages-template').text();
                tpl = tpl.replace('%page%', $this.page).replace('%pages%', $this.pages);
                $('#aim-pages-container').html(tpl);
                return $this.$imagesNav;
            };

            if ($this.pages > 1) {
                $this.$imagesNav.show();
            } else {
                $this.$imagesNav.hide();
                return $this.$imagesNav;
            }

            $btnNext
                .off('click')
                .on('click', function () {
                    $this._loadPage(parseInt($this.page, 10) + 1, $this.topicId);
                    $this.$imagesNav.setPages();
                    return false;
                });

            $btnPrev
                .off('click')
                .on('click', function () {
                    $this._loadPage(parseInt($this.page, 10) - 1, $this.topicId);
                    $this.$imagesNav.setPages();
                    return false;
                });

            $this.$element
                .off('aim-load-page-success.imagesNav')
                .on('aim-load-page-success.imagesNav', function () {
                    $this.$imagesNav
                        .setRefresh()
                        .setPages();
                });

            return $this.$imagesNav;
        },

        /**
         * Скрывает/отображает настройки добавляемой картинки
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        btnTriggerParams: function ($this) {
            $this.$btnTriggerParams
                .off('click')
                .on('click', function () {
                    $this.$btnTriggerParams
                        .toggleClass('hidden-options');

                    $this.$blockParams.slideToggle('fast');
                    return false;
                });

            $this.$btnTriggerParams.setReset = function () {

                $this.$btnTriggerParams
                    .addClass('hidden-options');

                return $this.$btnTriggerParams;
            };

            $this.$element
                .off('hidden.bs.modal aim-load-page-success.btnTriggerParams')
                .on('hidden.bs.modal aim-load-page-success.btnTriggerParams', function () {
                    $this.$btnTriggerParams.setReset();
                });

            return $this.$btnTriggerParams;

        },

        /**
         * Инициализация кнопки обновления дерева. У кнопки появились два метода:
         * - $this.$btnRefreshTree.startSpin()      - Запуск вращения кнопки
         * - $this.$btnRefreshTree.stopSpin()       - Остановка вращения кнопки
         * Кнопка реагирует этими событиями на начало обновления дерева и
         * на окончания обновления. При нажатии на нее запускается метод
         * {$this._refreshCategories}
         *
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        btnRefreshTree: function ($this) {

            // Запускаем вращение иконки кнопки
            $this.$btnRefreshTree.startSpin = function () {
                $this.$btnRefreshTree
                    .attr('disabled', 'disabled')
                    .find('i').addClass('glyphicon-spin fa-spin');
                return $this.$btnRefreshTree;
            };

            // Останавливаем вращение иконки кнопки
            $this.$btnRefreshTree.stopSpin = function () {
                $this.$btnRefreshTree
                    .removeAttr('disabled')
                    .find('i').removeClass('glyphicon-spin fa-spin');
                return $this.$btnRefreshTree;
            };

            // Нажатие кнопки
            $this.$btnRefreshTree.live('click', function () {
                $this._refreshCategories($this.$btnRefreshTree.startSpin());
                return false;
            });

            // Управление вращением иконки
            $this.$element
                .on('aim-refresh-categories-start', function () {
                    $this.$btnRefreshTree.startSpin();
                    $this.$ctrCategories
                        .animate({opacity: 0.4}, 400)
                        .addClass('disabled-block');
                })
                .on('aim-refresh-categories-success', function () {
                    $this.$btnRefreshTree.stopSpin();
                    $this.$ctrCategories
                        .animate({opacity: 1}, 500)
                        .removeClass('disabled-block');
                })
                .on('aim-refresh-categories-error', function () {
                    $this.$btnRefreshTree.stopSpin();
                    $this.$ctrCategories
                        .animate({opacity: 1}, 500)
                        .removeClass('disabled-block');
                });

            return $this.$btnRefreshTree;

        },

        /**
         * Страница загрузки изображений с компьютера пользователя
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        pageUploadPc: function ($this) {
            $this.$pageUploadPc.find('.js-aim-btn-upload-image')
                .off('click')
                .live('click', function () {
                    var success = function (result) {
                        ls.insertToEditor(result.sText);
                        $this.$pageUploadPc.find('input[type="file"]').val('');
                        $this.$pageUploadPc.find('.btn-file span').last().remove();

                        this.$element.modal('hide');
                    };
                    var error = function () {

                    };
                    var data = {
                        type: 'submit',
                        form: $this.$pageUploadPc.find('form')
                    };

                    $this._ajax(
                        'upload/image/',
                        data,
                        success,
                        error
                    );
                    return false;
                });

            return $this.$pageUploadPc;
        },

        /**
         * Страница загрузки изображений с из интернета
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        pageUploadLink: function ($this) {
            $this.$pageUploadLink.find('.js-aim-btn-upload-image')
                .off('click')
                .live('click', function () {
                    var success = function (result) {
                        ls.insertToEditor(result.sText);
                        this.$element.modal('hide');
                    };
                    var error = null;
                    var data = {
                        type: 'submit',
                        form: $this.$pageUploadLink.find('form')
                    };

                    $this._ajax(
                        'upload/image/',
                        data,
                        success,
                        error
                    );
                    return false;
                });

            $this.$pageUploadLink.find('.js-aim-btn-insert-link')
                .off('click')
                .live('click', function () {
                    ls.insertImageToEditor(this);
                    return false;
                });

            return $this.$pageUploadPc;
        },

        /**
         * Блок настроек изображения
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        blockParams: function ($this) {

            $this.$blockParams.setClear = function () {
                $this.$blockParams
                    .find('input[name="img_width"]').val('100').end()
                    .find('#form-image-title').val('').end()
                    .find('#form-image-align :nth-child(1)').attr("selected", "selected");

                return $this.$blockParams;
            };

            $this.$element
                .off('hidden.bs.modal')
                .on('hidden.bs.modal', function () {
                    $this.$blockParams.setClear();
                });

            return $this.$blockParams;
        },

        /**
         * Страница изображений
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        pageImages: function ($this) {
            if ($this.profile) {
                return false;
            }

            $this.$pageImages.find('a')
                .off()
                .on('click', function () {
                    var url = $(this).data('url'),
                        align = $this.$pageImages.find('[name=align]').val(),
                        title = $this.$pageImages.find('[name=title]').val(),
                        size = parseInt($this.$pageImages.find('[name=img_width]').val(), 10);

                    align = (align == 'center') ? ' class="image-center"' : ((align == '') ? '' : 'align="' + align + '" ');
                    size = (size == 0) ? '' : ' width="' + size + '%" ';
                    title = (title == '') ? '' : ' title="' + title + '"' + ' alt="' + title + '" ';

                    var html = '<img src="' + url + '"' + title + align + size + ' />';
                    if (tinymce) {
                        ls.insertToEditor(html);
                    } else {
                        $.markItUp({replaceWith: html});
                    }

                    $this.$element.modal('hide');
                    return false;
                });

            return $this.$pageImages;
        },

        /**
         * Кнопки категорий
         * @param {altoImageManager} $this
         * @returns jQuery
         * @private
         */
        blockCategoriesButtons: function ($this) {

            $this.$ctrCategories.find('.js-image-categories-tree a')
                .off('click')
                .live('click', function () {
                    // Отрисуем кнопку
                    $this.$ctrCategories.find('li').removeClass('active');
                    // Запомним категорию
                    $this.category = $(this)
                        .parent().addClass('active').end()
                        .data('category');
                    // Загрузим страницу
                    $this.topicId = $this._getTopicId();
                    $this._loadPage(1, $this.topicId);

                    $this.$element.trigger('aim-category-select', $this.category);

                    return false;
                });

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