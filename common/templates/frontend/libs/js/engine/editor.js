/**
 * Вспомгательные функции для текстового редактора
 */

;var ls = ls || {};

ls.editor = (function ($) {
    "use strict";

    /**
     * Дефолтные опции
     */
    var defaults = {
        // Селекторы
        selectors: {
            insertImageButton: '.js-insert-image-button',
            uploadImageButton: '.js-upload-image-button',
            previewImageLoader: '.js-topic-preview-loader'
        }
    };

    /**
     * Инициализация
     *
     * @param  {Object} options Опции
     */
    this.init = function (options) {
        var self = this;

        this.options = $.extend({}, defaults, options);

        // Вставка ссылки
        $(this.options.selectors.insertImageButton).on('click', function (e) {
            var sUrl = $('#img_url').val(),
                sAlign = $('#form-image-url-align').val(),
                sTitle = $('#form-image-url-title').val();

            self.insertImageUrlToEditor(sUrl, sAlign, sTitle);
        });

        // Вставка ссылки
        $(this.options.selectors.uploadImageButton).on('click', function (e) {
            var sFormId = $(this).data('form-id');

            self.ajaxUploadImg(sFormId);
            return false;
        });

        // Справка по разметке редактора
        $('.js-editor-help').each(function () {
            var oEditorHelp = $(this),
                oTargetForm = $('#' + oEditorHelp.data('form-id'));

            oEditorHelp.find('.js-tags-help-link').on('click', function (e) {
                if ($(this).data('insert')) {
                    var sTag = $(this).data('insert');
                } else {
                    var sTag = $(this).text();
                }

                $.markItUp({
                    target: oTargetForm,
                    replaceWith: sTag
                });

                e.preventDefault();
            });
        });
    };

    /**
     * Вставка ссылки загруженного изображения в редактор
     *
     * @param  {String} sUrl   Ссылка
     * @param  {String} sAlign Выравнивание
     * @param  {String} sTitle Описание
     */
    this.insertImageUrlToEditor = function (sUrl, sAlign, sTitle) {
        sAlign = sAlign == 'center' ? 'class="image-center"' : 'align="' + sAlign + '"';

        $.markItUp({
            replaceWith: '<img src="' + sUrl + '" title="' + sTitle + '" ' + sAlign + ' />'
        });

        this.hideUploadImageModal();
    };

    /**
     * Загрузка изображения
     *
     * @param  {String} sFormId ID формы
     */
    this.ajaxUploadImg = function (sFormId) {
        var self = this;

        ls.ajaxSubmit('upload/image/', sFormId, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                $.markItUp({
                    replaceWith: result.sText
                });

                self.hideUploadImageModal();
            }
        });
    };

    /**
     * Функция, закрепляющая шапку редактора markitUp вверху страницы
     * при скролле
     *
     * @param {{}} options
     */
    this.float = function (options) {
        // Получим все редакторы на странице
        var aMarkItUp = $(options.textareaClass).not(':hidden');

        // Если хоть один редактор найден, то он и будет
        // отслеживаться
        if (aMarkItUp.length > 0) {

            // При каждом скролле будем перерисовывать положение
            // тулбара редактора
            $(window).scroll(function () {

                // Положение текущего скролла
                var htmlTop = $(window).scrollTop();

                // Переберём редакторы, может кто-то ушел за пределы страницы
                aMarkItUp.each(function () {
                    var $this = $(this);
                    var editor = $this.parents(options.editorClass);
                    if (editor.length == 0) {
                        editor = $this.prev();
                        $this = $('.mce-edit-area')
                    }
                    var header = editor.find(options.headerClass);

                    if ($this.offset() && $this.offset().top < htmlTop + options.topStep + header.outerHeight()
                        && $this.offset().top + $this.outerHeight() - 60 - options.topStep > htmlTop + options.topStep + header.outerHeight()) {
                        header.css({
                            position: 'fixed',
                            top: options.topStep,
                            width: $this.outerWidth() + options.dif
                        }).addClass('float');

                        if (options.css !== 'undefined') {
                            header.css(options.css)
                        }

                    } else {
                        header.removeAttr('style').removeClass('float');
                    }
                });

            });
        }
    };

    /**
     * Закрытие окна загрузки изображения
     */
    this.hideUploadImageModal = function () {
        var oModal = $('#modal-image-upload');

        oModal.find('input[type="text"], input[type="file"]').val('');
        oModal.modal('hide');
    };

    return this;
}).call(ls.editor || {}, jQuery);