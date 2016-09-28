/*!
 * altoUploader.js
 * Файл jquery-плагина для аплоадера изображений Alto
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Воронов
 * @version     0.0.2 от 01.12.2014 20:11
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
    var altoUploader = function (element, options) {

        // Вызывающий объект в формате jQuery
        this.$element = $(element);

        this.blockButtons = false;

        // Опции - типа объект
        this.options = $.extend({}, $.fn.altoUploader.defaultOptions, options, this.$element.data());
        if (this.options.aspectRatio !== undefined) {
            this.options.cropOptions.aspectRatio = this.options.aspectRatio;
            delete this.options.aspectRatio;
        }

        // Картинка, на странице, над которой проводится операция
        this.$image = this.$element.find('img.js-uploader-image');
        this.$imageFull = this.$element.find('img.js-uploader-image-full');

        // Кнопки управления картинкой
        this.buttons = {
            $upload: this.$element.find('.js-uploader-button-upload'),
            $remove: this.$element.find('.js-uploader-button-remove')
        };

        if (this.options.crop === 'yes') {
            this.$modal = $(this.options.selectors.cropModal);
            this.$cropImage = $(this.options.selectors.cropImage);
            this.jcropImage = null;
        }

        // Загружаемый файл изображения
        this.$file = this.$element.find('.js-uploader-file');

        this.init();

        return this.$element;
    };

    //================================================================================================================
    //      ОПРЕДЕЛЕНИЕ ПРОТОТИПА ОБЪЕКТА
    //================================================================================================================
    altoUploader.prototype = {
        /**
         * Инициализация объекта. Вызывается автоматически в
         * конструкторе плагина.
         */
        init: function () {
            var $this = this;

            $this.$element.trigger('alto.uploader.before_init', $this);

            // Инициализация загрузчика картинки
            $this.$file.off('change').on('change', function () {
                $this.uploadImage(this);
                return false;
            });

            // Инициализация кнопки удаления картинки
            $this.buttons.$remove.off('click').on('click', function () {
                $this.uploadImageRemove();
                return false;
            });

            $this.$element.trigger('alto.uploader.after_init', $this);

            return $this;
        },

        /**
         * Обработчик загрузки картинки
         */
        uploadImage: function (input) {
            var $this = this;

            if ($this.blockButtons) {
                return $this;
            }
            var form = $('<form method="post" enctype="multipart/form-data"/>').hide().appendTo('body');
            input = $(input);
            input.clone(true).insertAfter(input);
            input.removeAttr('id').appendTo(form);

            // Поместим в форму идентификатор цели
            $('<input type="hidden" name="target" value="' + $this.options.target + '">').appendTo(form);
            $('<input type="hidden" name="target_id" value="' + $this.options.targetId + '">').appendTo(form);

            // Отправим запрос серверу на загрузку указаной картинки
            // с включенным прогрессом
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_progress_start', [$this.options]);

            ls.ajaxSubmit($this.options.url.upload, form,
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}, sMsgTitle: {string}, sPreview: {string}}} result
                 */
                function (result) {
                    ls.progressDone();
                    $this.blockButtons = false;
                    ls.hook.run('uploader_progress_stop', [$this.options]);
                    if (!result) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (result.bStateError) {
                        ls.msg.error(result.sMsgTitle, result.sMsg);
                    } else {
                        if ($this.options.crop === 'yes') {
                            $this.uploadImageModalCrop(result.sPreview);
                        } else {
                            $this.uploadDirect();
                        }
                    }
                    // Удалим форму, больше она не нужна
                    form.remove();

                }, {progress: true});

            return $this;
        },

        /**
         * Загрузка картинки без ресайза
         */
        uploadDirect: function () {
            var $this = this;

            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_progress_start', [$this.options]);
            ls.ajax($this.options.url.direct, {
                    target: $this.options.target,
                    target_id: $this.options.targetId,
                    crop_size: $this.options.previewCrop
                },
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}, sFilePreview: {string}, sFile: {string}}} result
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
                        $this.$image.attr('src', result.sFilePreview + '?' + Math.random());
                        if ($this.$imageFull.length > 0) {
                            $this.$imageFull.attr('src', result.sFile + '?' + Math.random());
                        }
                        $this.buttons.$remove.css({
                            display: 'block'
                        });

                        ls.hook.run('uploader_upload_image_after', [$this.options, result]);
                    }
                });

            return this;
        },

        /**
         * Удаляет картинку
         */
        uploadImageRemove: function () {
            var $this = this;

            if ($this.blockButtons) {
                return $this;
            }
            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_progress_start', [$this.options]);
            ls.ajax($this.options.url.remove, {
                    target: $this.options.target,
                    target_id: $this.options.targetId
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
                        $this.$image.attr('src', $this.options.empty + '?' + Math.random());
                        $this.buttons.$remove.css({
                            display: 'none'
                        });

                        ls.hook.run('uploader_remove_image_after', [$this.options, result]);
                    }
                });

            return $this;
        },

        /**
         * Открытие окна ресайза изображения
         *
         * @param {string} sImgFile Путь к файлу
         */
        uploadImageModalCrop: function (sImgFile) {
            var $this = this;

            // Установим текстовки
            if ($this.options.title !== undefined) {
                $this.$modal.find('.modal-title').text(ls.lang.get($this.options.title) || $this.options.title);
            }
            if ($this.options.help !== undefined) {
                $this.$modal.find('.js-crop_img-help').text(ls.lang.get($this.options.help) || $this.options.help);
            }

            // Покажем форму кропа
            $this.$modal.modal('show');

            // Установим события на кнопки окончания и отмены кропа
            $this.$modal.find('.js-confirm').off('click').on('click', function () {
                $this.uploadImageCropSubmit(this);
                return false;
            });
            $this.$modal.find('.js-cancel').off('click').on('click', function () {
                $this.uploadImageCropCancel(this);
                return false;
            });

            $($this.$cropImage).attr('src', sImgFile + '?' + Math.random()).css({
                'width': 'auto',
                'height': 'auto'
            });

            $this.uploadImageCropInit($this.$cropImage);

            return $this;
        },

        /**
         * Инициализация окна кропа
         *
         * @param cropImage
         */
        uploadImageCropInit: function (cropImage) {
            var $this = this;

            this.uploadImageCropDone();

            $(cropImage)
                .removeData()
                .Jcrop($this.options.cropOptions, function () {
                    $this.jcropImage = this;
                });

            return $this;
        },

        /**
         * Разрушение окна кропа
         */
        uploadImageCropDone: function () {
            var $this = this;

            if ($this.jcropImage) {
                $this.jcropImage.release();
                $this.jcropImage.destroy();
            }
            $('.jcrop-holder').remove();

            return $this;
        },

        /**
         * Выполняет ресайз аватара
         */
        uploadImageCropSubmit: function (button) {
            var $this = this;

            if ($this.blockButtons) {
                return $this;
            }
            button = $(button);

            var params = {
                size: $this.jcropImage.tellSelect(),
                crop_size: $this.options.previewCrop
            };

            // Добавим лоадер на кнопку
            button.addClass('loading');

            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_progress_start', [$this.options]);
            ls.ajax($this.options.url.crop, params,
                /**
                 * Обработчик результата, пришедшего от сервера
                 * @param {{bStateError: {boolean}, sMsg: {string}, sFilePreview: {string}, sFile: {string}}} result
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
                        $this.$image.attr('src', result.sFilePreview + '?' + Math.random());

                        if ($this.$imageFull.length > 0) {
                            $this.$imageFull.attr('src', result.sFile + '?' + Math.random());
                        }

                        // Спрячем форму
                        $this.$modal.modal('hide');

                        $this.buttons.$remove.css({
                            display: 'block'
                        });

                        ls.hook.run('uploader_upload_image_after', [$this.options, result]);
                    }
                    button.removeClass('loading');

                });

            return $this;
        },

        /**
         * Отмена ресайза аватарки, подчищаем временный данные
         */
        uploadImageCropCancel: function (button) {
            var $this = this;

            if ($this.blockButtons) {
                return $this;
            }
            button = $(button);

            button.addClass('loading');

            ls.progressStart();
            $this.blockButtons = true;
            ls.hook.run('uploader_progress_start', [$this.options]);
            ls.ajax($this.options.url.cancel, {
                    target: $this.options.target,
                    target_id: $this.options.targetId
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
                        $this.$modal.modal('hide');
                        ls.hook.run('ls_uploader_cancel_after', [result]);
                    }

                    button.removeClass('loading');
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
     * @returns {altoUploader}
     */
    $.fn.altoUploader = function (option) {

        if (typeof option === 'boolean') {
            option = {};
        }

        return this.each(function () {
            var $this = $(this);
            var data = $this.data('alto.altoUploader');
            var options = typeof option === 'object' && option;

            if (!data) {
                $this.data('alto.altoUploader', (data = new altoUploader(this, options)));
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
    $.fn.altoUploader.Constructor = altoUploader;

    /**
     * Параметры плагина
     *
     * @type {object}
     */
    $.fn.altoUploader.defaultOptions = {
        cropOptions: {
            minSize: [32, 32],
            setSelect: [0, 0, 800, 800]
        },
        selectors: {
            cropModal: '#modal-crop_img',
            cropImage: '.js-crop_img'
        },
        url: {
            upload: ls.routerUrl('uploader') + 'upload-image/',
            remove: ls.routerUrl('uploader') + 'remove-image/',
            cancel: ls.routerUrl('uploader') + 'cancel-image/',
            direct: ls.routerUrl('uploader') + 'direct-image/',
            crop: ls.routerUrl('uploader') + 'resize-image/'
        },
        previewCrop: '400fit'
    };

}(window.jQuery, ls));


//====================================================================================================================
//      АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ ПЛАГИНА
//====================================================================================================================
jQuery(function () {
    jQuery('.js-alto-uploader').each(function(){
        jQuery(this).altoUploader(false);
    });
});
