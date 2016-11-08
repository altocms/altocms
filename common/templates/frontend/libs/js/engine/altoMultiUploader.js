/*!
 * altoMultiUploader.js
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

/*global jQuery, ls, FileAPI, window */

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

        this.$photoset = this.$element.find(this.options.photoset);
        this.templateHTML = $(this.options.result_template).html();
        this.$list = this.$element.find('.js-alto-multi-uploader-list');
        this.$preview = this.$element.find('.js-alto-multi-uploader-target-preview');
        this.uploaded = [];
        this.descriptionQueue = [];
        this.descriptionTimerId = [];

        this.init();

        return this;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    altoMultiUploader.prototype = {
        /**
         * Инициализация объекта.
         * Вызывается автоматически в конструкторе плагина.
         */
        init: function () {
            /*jslint unparam: true*/
            // Для onCheckFiles поскольку первый параметр не используется
            var $this = this,
                previewConfig = {
                    el: '.js-file-preview',
                    width: $this.options.preview_width,
                    height: $this.options.preview_height
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
            /*jslint unparam: false*/

            $($this.options.form).fileapi({
                url: $this.options.url.upload,
                clearOnComplete: true,
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
                    'tmp': $this.options.tmp,
                    'crop_size': $this.options.previewCrop
                },
                multiple: true,
                onFileComplete: function (evt, uiEvt) {
                    if (uiEvt && uiEvt.status && uiEvt.status >= 400) {
                        ls.msg.error('HTTP Error ' + uiEvt.status, uiEvt.statusText ? uiEvt.statusText : '');
                    } else {
                        /*jslint nomen: true*/
                        // Специфическое поле FileAPI
                        evt.widget.remove(evt.widget.__fileId);
                        /*jslint nomen: false*/
                        $this.addImg(uiEvt.result);
                    }
                },
                onComplete: $this.options.onComplete,
                elements: {
                    ctrl: {upload: $this.options.upload}, // Кнопка загрузки
                    emptyQueue: {hide: $this.options.upload},
                    list: $this.options.list,
                    file: {
                        tpl: $this.options.tpl,
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
            }).on('click', this.$element.attr('class') + ' ' + '.js-file-reload', function (evt) {
                var tpl = $($this.options.from),
                    uid = $(evt.currentTarget).parents($this.options.tpl).data('id'),
                    file = tpl.fileapi("_getFile", uid);
                tpl.fileapi("_makeFilePreview", uid, file, previewConfig);
            });

            // Добавим сортировку изображений в фотосете
            $this.$list
                .sortable({
                    stop: function () {
                        var elements = $this.$list.find('li'),
                            order = [];
                        if (elements.length > 0) {
                            /*jslint unparam: true*/
                            $.each(elements.get().reverse(), function (index, value) {
                                order.push($(value).attr('id').replace('uploader_item_', ''));
                            });
                            /*jslint unparam: false*/
                            ls.ajax($this.options.url.sort,
                                {
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
                                });
                        }
                    }
                });
            //.disableSelection(); // issue#349, в firefox текстовое поле описания не было доступно

            $this.initDescription();

            return $this;
        },

        initDescription: function () {
            var $this = this,
                submitForm;

            // Добавим детектор изменения описания изображения
            $this.$element.find('textarea').live('change', function () {
                $(this).data('change', true);
            });

            // Установим обработчик submit. При нажатии на них нужно сначала обработать
            // очередь установки описания, а затем выполнить submit;
            if (this.options.submitForm) {
                submitForm = $(this.options.submitForm);
                submitForm.on('submit', function () {
                    ls.progressStart();
                    // Обработаем принудительно
                    $this.processDescriptionQueue(function () {
                        submitForm.off('submit').submit();
                    });
                });
            }
        },

        addImg: function (data) {
            var $this = this,
                html;

            ls.log('add img', data);
            if (!data) {
                ls.msg.error(null, 'System error #1001');
            } else if (typeof data === 'string') {
                ls.msg.error(null, 'System error #1003');
            } else if (data.bStateError) {
                ls.msg.error(data.sMsgTitle, data.sMsg);
            } else {
                html = this.templateHTML;
                if (html) {
                    html = $(html.replace(/ID/g, data.id).replace(/PHOTOSET-IS-COVER/g, '')).show();
                    html.find('img').prop('src', data.file);
                    html = $(html);
                    $this.uploaded.push(data.file.replace(new RegExp('-' + $this.options.previewCrop + '.*'), ''));
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
            var $this = this;

            this.$preview.val(id);

            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_cover_start', [$this.options]);
            ls.ajax($this.options.url.cover,
                {
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
                        $this.$photoset.find('.js-uploader-item-cover').removeClass('photoset-is-cover');
                        if (result.bPreview) {
                            $('#uploader_item_' + id).find('.js-uploader-item-cover').addClass('photoset-is-cover');
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
            ls.ajax($this.options.url.remove,
                {
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
         * Выполняет очередь заданий по установке описания изображения
         */
        processDescriptionQueue: function (callback) {

            if (this.blockButtons) {
                return this;
            }

            var $this = this,
                data = $this.descriptionQueue.pop();

            if (data === undefined) {
                if (callback !== false) {
                    callback();
                }
                return false;
            }

            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_description_start', [$this.options]);
            ls.ajax($this.options.url.description, data,
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}}} result
                 */
                function (result) {
                    ls.progressDone();
                    ls.hook.run('uploader_description_stop', [$this.options]);
                    $this.blockButtons = false;
                    $this.processDescriptionQueue(callback); // Рекурсивно следующий элемент очереди
                    if (!result) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (result.bStateError) {
                        ls.msg.error(null, result.sMsg);
                    } else {
                        if (callback === false) {
                            ls.msg.notice(null, result.sMsg);
                        }
                        ls.hook.run('uploader_add_description_after', [$this.options, result]);
                    }
                });
        },

        /**
         * Устанавливает описание изображения
         *
         * @param {int} id Ид. ресурса
         * @returns {altoMultiUploader}
         */
        setDescription: function (id) {
            var $this = this,
                $textarea = $('#uploader_item_' + id).find('textarea'),
                data;

            // Текстовку устанавливаем только если есть изменения
            if ($textarea.data('change') !== true) {
                return $this;
            }
            $textarea.data('change', false);

            // Сформируем набор данных для передачи
            data = {
                target: $this.options.target,
                target_id: $this.options.targetId,
                description: $textarea.val(),
                resource_id: id
            };

            // И добавим его в очередь
            $this.descriptionQueue.push(data);

            // Очередь будет содержать либо один элемент, либо ещё и те, которые
            // не успели обработаться из-за наложения ajax-запросов к серверу
            $this.processDescriptionQueue(false);

            return $this;
        },

        getUploaded: function () {
            var uploaded = this.uploaded;
            this.uploaded = [];
            return uploaded;
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
        var $this = $(this),
            data = $this.data('alto.altoMultiUploader'),
            options = typeof option === 'object' && option,
            funcResult = false;

        if (typeof option === 'boolean') {
            option = {};
        }

        //if (typeof option === 'getUploaded') {
        //    data[option]();
        //}

        if (!data) {
            data = new altoMultiUploader(this, options);
            $this.data('alto.altoMultiUploader', data);
        }

        if (typeof option === 'string') {
            funcResult = data[option]();
        }

        if (typeof option === 'object') {
            if (typeof option.cover === 'string') {
                return data.setCover(option.cover);
            }
            if (typeof option.remove === 'string') {
                return data.remove(option.remove);
            }
            if (typeof option.description === 'string') {
                return data.setDescription(option.description);
            }
        }

        return funcResult || $this;
    };

    /**
     * Определение конструктора плагина
     *
     * @type {Function}
     */
    $.fn.altoMultiUploader.Constructor = altoMultiUploader;

    /*jslint unparam: true*/
    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.altoMultiUploader.defaultOptions = {
        maxSize: 6,
        maxWidth: 8000,
        maxHeight: 6000,
        photoset: '.js-alto-multi-photoset-list',
        maxSizeError: 'Wrong file size',
        maxWidthError: 'Wrong file width',
        maxHeightError: 'Wrong file height',
        tpl: '.js-file-tpl',
        list: '.js-files',
        upload: '.js-upload',
        form: '.js-alto-multi-uploader-form',
        result_template: '.js-alto-multi-uploader-template',
        preview_width: 140,
        preview_height: 140,
        onComplete: function (evt, uiEvt) {
        },
        url: {
            upload: ls.routerUrl('uploader') + 'multi-image/',
            remove: ls.routerUrl('uploader') + 'remove-image-by-id/',
            description: ls.routerUrl('uploader') + 'description/',
            cover: ls.routerUrl('uploader') + 'cover/',
            sort: ls.routerUrl('uploader') + 'sort/'
        },
        previewCrop: '400fit',
        tmp: true,
        submitForm: ''
    };
    /*jslint unparam: false*/

}(window.jQuery, ls));