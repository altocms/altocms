/*!
 * jAltoUploader.js
 * Файл jquery-плагина для мультизагрузки изображений Alto
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Воронов
 * @version     0.0.2 от 10.12.2014 09:53
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
    var altoMultiUploader = function (element, options) {

        // Вызывающий объект в формате jQuery
        this.$element = $(element);

        this.blockButtons = false;

        // Опции - типа объект
        this.options = $.extend({}, $.fn.altoMultiUploader.defaultOptions, options, this.$element.data());

        this.$link = this.$element.find('.js-alto-multi-uploader-link');
        this.templateHTML = $('.js-alto-multi-uploader-template').html();
        this.$list = this.$element.find('.js-alto-multi-uploader-list');
        this.$preview = this.$element.find('.js-alto-multi-uploader-target-preview');


        this.init();

        return this;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    altoMultiUploader.prototype = {

        /**
         * Инициализация объекта. Вызывается автоматически в
         * конструкторе плагина.
         */
        init: function () {

            var $this = this,
                previewConfig = {
                    el: '.js-file-preview',
                    width: 140,
                    height: 140
                },
                onCheckFiles = function (evt, data) {
                    if (data.other.length) {
                        var errors = data.other[0].errors;
                        if (errors) {
                            if (errors.maxSize !== undefined) {
                                ls.msg.error(null, $this.options.maxSizeError);
                            } else if (errors.maxWidth !== undefined) {
                                ls.msg.error(null, $this.options.maxWidthError);
                            } else if (errors.maxHeight !== undefined) {
                                ls.msg.error(null, $this.options.maxHeightError);
                            }
                        }
                    }
                };

            $('.js-alto-multi-uploader-form').fileapi({
                url: $this.options.url.upload,
                maxSize: $this.options.maxSize * FileAPI.MB,
                imageSize: {
                    maxWidth: $this.options.maxWidth,
                    maxHeight: $this.options.maxHeight
                },
                data: {
                    'security_key': ls.cfg.security_key,
                    'direct': true,
                    'target': $this.options.target,
                    'target_id': $this.options.targetId,
                    'multi': true,
                    'crop_size': $this.options.previewCrop
                },
                multiple: true,
                onFileComplete: function (evt, uiEvt) {
                    evt.widget.remove(evt.widget.__fileId);
                    $this.addPhoto(uiEvt.result);
                },
                elements: {
                    ctrl: {upload: '.js-upload'}, // Кнопка загрузки
                    emptyQueue: {hide: '.js-upload'},
                    list: '.js-files',
                    file: {
                        tpl: '.js-file-tpl',
                        preview: previewConfig,
                        upload: {show: '.progress', hide: '.js-file-rotate'},
                        complete: {hide: '.progress', remove: 'js-autoremove'},
                        progress: '.progress .progress-bar'
                    },
                    dnd: {
                        el: '.js-uploader-picker',
                        hover: 'js-uploader-picker-hover',
                        fallback: '.js-uploader-picker-supported'
                    }
                },
                onDrop: onCheckFiles,
                onSelect: onCheckFiles
            }).on('click', '.js-file-reload', function (evt) {
                    var tpl = $('.js-alto-multi-uploader-form'),
                        uid = $(evt.currentTarget).parents('.js-file-tpl').data('id'),
                        file = tpl.fileapi("_getFile", uid);
                    tpl.fileapi("_makeFilePreview", uid, file, previewConfig);
                }
            );

            // Добавим сортировку изображений в фотосете
            $this.$list
                .sortable({
                    stop: function () {

                        var elements = $this.$list.find('li');
                        if (elements.length > 0) {
                            var order = [];
                            $.each(elements.get().reverse(), function (index, value) {
                                order.push($(value).attr('id').replace('uploader_item_', ''));
                            });
                            ls.ajax($this.options.url.sort, {
                                    target: $this.options.target,
                                    target_id: $this.options.targetId,
                                    order: order
                                },
                                /**
                                 * Выведем пользователю сообщение о результате сортировки
                                 * @param {{bStateError: {boolean}, sMsg: {string}, sMsgTitle: {string}}} result
                                 */
                                function (result) {
                                    return result.bStateError
                                        ? ls.msg.error(result.sMsgTitle, result.sMsg)
                                        : ls.msg.notice(result.sMsgTitle, result.sMsg);

                                }
                            );
                        }
                    }
                })
                //.disableSelection(); // issue#349, в firefox текстовое поле описания не было доступно

            return $this;
        },

        addPhoto: function (data) {
            var $this = this;

            if (!data) {
                ls.msg.error(null, 'System error #1001');
            } else if (data.bStateError) {
                ls.msg.error(data.sMsgTitle, data.sMsg);
            } else {
                var html = this.templateHTML;
                if (html) {
                    html = $(html.replace(/ID/g, data.id).replace(/MARK_AS_PREVIEW/g, $this.options.langCoverNeed)).show();
                    html.find('img').prop('src', data.file);
                    html = $(html);
                    //html.find('js-uploader-item-cover').on('click', function(){
                    //   return $this.setCover(data.id);
                    //});
                    this.$list.append($(html)).show();
                }
            }


        },

        /**
         * Устанавливает идентификатор превью объекта
         *
         * @param id
         */
        setCover: function (id) {

            this.$preview.val(id);

            var $this = this;
            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_cover_start', [$this.options]);
            ls.ajax($this.options.url.cover, {
                    target: $this.options.target,
                    target_id: $this.options.targetId,
                    resource_id: id
                },
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}, bPreview: {boolean}}} result
                 */
                function (result) {

                    ls.progressDone();
                    $this.blockButtons = false;
                    ls.hook.run('uploader_cover_stop', [$this.options]);
                    if (!result) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (result.bStateError) {
                        ls.msg.error(null, result.sMsg);
                    } else {
                        ls.msg.notice(null, result.sMsg);
                        $this.$list.find('.js-uploader-item-cover').text($this.options.langCoverNeed);
                        if (result.bPreview) {
                            $('#uploader_item_' + id).find('.js-uploader-item-cover').text($this.options.langCoverDone);
                        }
                        ls.hook.run('uploader_set_cover_after', [$this.options, result]);
                    }

                });

            return $this;

        },

        /**
         * Удаляет изображение
         *
         * @param id
         */
        remove: function (id) {

            var $this = this;
            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_progress_start', [$this.options]);
            ls.ajax($this.options.url.remove, {
                    target: $this.options.target,
                    target_id: $this.options.targetId,
                    resource_id: id
                },
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}}} result
                 */
                function (result) {

                    ls.progressDone();
                    $this.blockButtons = false;
                    ls.hook.run('uploader_progress_stop', [$this.options]);
                    if (!result) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (result.bStateError) {
                        ls.msg.error(null, result.sMsg);
                    } else {
                        $('#uploader_item_' + id).remove();
                        ls.msg.notice(null, result.sMsg);
                        ls.hook.run('uploader_remove_image_after', [$this.options, result]);
                    }

                });

            return $this;

        },

        /**
         * Устанавливает описание изображения
         *
         * @param {int} id Ид. ресурса
         * @returns {altoMultiUploader}
         */
        setDescription: function (id) {

            var $this = this;
            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_description_start', [$this.options]);
            ls.ajax($this.options.url.description, {
                    target: $this.options.target,
                    target_id: $this.options.targetId,
                    description: $('#uploader_item_' + id).find('textarea').val(),
                    resource_id: id
                },
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}}} result
                 */
                function (result) {

                    ls.progressDone();
                    $this.blockButtons = false;
                    ls.hook.run('uploader_description_stop', [$this.options]);
                    if (!result) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (result.bStateError) {
                        ls.msg.error(null, result.sMsg);
                    } else {
                        ls.msg.notice(null, result.sMsg);
                        ls.hook.run('uploader_add_description_after', [$this.options, result]);
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
     * @returns {altoMultiUploader}
     */
    $.fn.altoMultiUploader = function (option) {

        if (typeof option === 'boolean') {
            option = {};
        }

        return this.each(function () {
            var $this = $(this);
            var data = $this.data('alto.altoMultiUploader');
            var options = typeof option === 'object' && option;

            if (!data) {
                $this.data('alto.altoMultiUploader', (data = new altoMultiUploader(this, options)));
            }

            if (typeof option === 'string') {
                data[option]();
            }

            if (typeof option === 'object') {
                if (typeof option['cover'] === 'string') {
                    return data.setCover(option['cover']);
                }
                if (typeof option['remove'] === 'string') {
                    return data.remove(option['remove']);
                }
                if (typeof option['description'] === 'string') {
                    return data.setDescription(option['description']);
                }
            }

            return $this;
        });

    };

    /**
     * Определение конструктора плагина
     *
     * @type {Function}
     */
    $.fn.altoMultiUploader.Constructor = altoMultiUploader;

    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.altoMultiUploader.defaultOptions = {
        maxSize: 6,
        maxWidth: 8000,
        maxHeight: 6000,
        maxSizeError: 'Wrong file size',
        maxWidthError: 'Wrong file width',
        maxHeightError: 'Wrong file height',
        url: {
            upload: ls.routerUrl('uploader') + 'multi-image/',
            remove: ls.routerUrl('uploader') + 'remove-image-by-id/',
            description: ls.routerUrl('uploader') + 'description/',
            cover: ls.routerUrl('uploader') + 'cover/',
            sort: ls.routerUrl('uploader') + 'sort/'
        },
        previewCrop: '400fit'
    };

}(window.jQuery, ls));