/**
 * Заметки
 */
;
var ls = ls || {};

ls.usernote = (function ($) {
    "use strict";

    /**
     * Дефолтные опции
     */
    var defaults = {
        // Роутеры
        routers: {
            save: ls.routerUrl('profile') + 'ajax-note-save/',
            remove: ls.routerUrl('profile') + 'ajax-note-remove/'
        },

        // Селекторы
        selectors: {
            note: '.js-usernote',
            noteWrap: '.js-usernote-wrap',
            noteText: '.js-usernote-text',
            noteActions: '.js-usernote-actions',
            noteEditButton: '.js-usernote-button-edit',
            noteRemoveButton: '.js-usernote-button-remove',
            noteAddButton: '.js-usernote-button-add',

            noteForm: '.js-usernote-form',
            noteEditSaveButton: '.js-usernote-form-save',
            noteEditCancelButton: '.js-usernote-form-cancel'
        }
    };

    this.options = {};

    /**
     * Инициализация
     *
     * @param  {Object} options Опции
     */
    this.init = function (options) {
        var $that = this;

        this.options = $.extend({}, defaults, options);

        // Добавление
        $(this.options.selectors.note).each(function () {
            var usernoteWidget = $(this);

            // Показывает форму добавления
            usernoteWidget.find($that.options.selectors.noteAddButton).on('click', function (e) {
                $that.showForm(usernoteWidget);
                e.preventDefault();
            }.bind(self));

            // Отмена
            usernoteWidget.find($that.options.selectors.noteEditCancelButton).on('click', function (e) {
                $that.hideForm(usernoteWidget);
                return false;
            });

            // Сохранение заметки
            usernoteWidget.find($that.options.selectors.noteEditSaveButton).on('click', function (e) {
                $that.save(usernoteWidget);
                return false;
            });

            // Удаление заметки
            usernoteWidget.find($that.options.selectors.noteRemoveButton).on('click', function (e) {
                $that.remove(usernoteWidget);
                e.preventDefault();
            });

            // Редактирование заметки
            usernoteWidget.find($that.options.selectors.noteEditButton).on('click', function (e) {
                $that.showForm(usernoteWidget);
                e.preventDefault();
            });
        });
    };

    /**
     * Показывает форму редактирования
     *
     * @param  {Object} usernoteWidget
     */
    this.showForm = function (usernoteWidget) {
        var text = $.trim(usernoteWidget.find(this.options.selectors.noteText).text()),
            form = usernoteWidget.find(this.options.selectors.noteForm),
            textarea = form.find('textarea');

        usernoteWidget.find(this.options.selectors.noteAddButton).hide();
        usernoteWidget.find(this.options.selectors.noteWrap).hide();
        form.show();
        textarea.val(text).focus();
    };

    /**
     * Скрывает форму редактирования
     *
     * @param  {Object} oVars Общие переменные
     */
    this.hideForm = function (usernoteWidget) {
        var text = usernoteWidget.find(this.options.selectors.noteText),
            form = usernoteWidget.find(this.options.selectors.noteForm),
            textarea = form.find('textarea'),
            html = $.trim(text.html());

        form.hide();
        if (html) {
            usernoteWidget.find(this.options.selectors.noteWrap).show();
            text.show();
        } else {
            usernoteWidget.find(this.options.selectors.noteAddButton).show();
            text.hide();
        }
    };

    /**
     * Сохраняет заметку
     *
     * @param  {Object} oVars Общие переменные
     */
    this.save = function (usernoteWidget) {
        var textarea = usernoteWidget.find(this.options.selectors.noteForm + ' textarea'),
            params = {
                iUserId: parseInt(usernoteWidget.data('user-id')),
                text: textarea.val()
            };

        ls.progressStart();
        textarea.prop('disable', true);
        ls.ajax(this.options.routers.save, params, function (result) {
            textarea.prop('disable', false);
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                usernoteWidget.find(this.options.selectors.noteText).html(result.sText).show();
                if (result.sText) {
                    usernoteWidget.find(this.options.selectors.noteWrap).show();
                } else {
                    usernoteWidget.find(this.options.selectors.noteAddButton).show();
                }
                this.hideForm(usernoteWidget);

                ls.hook.run('ls_usernote_save_after', [params, result]);
            }
        }.bind(this));
    };

    /**
     * Удаление заметки
     *
     * @param  {Object} oVars Общие переменные
     */
    this.remove = function (usernoteWidget) {
        var textarea = usernoteWidget.find(this.options.selectors.noteForm + ' textarea'),
            params = {
                iUserId: parseInt(usernoteWidget.data('user-id'))
            };

        ls.progressStart();
        textarea.prop('disable', true);
        ls.ajax(this.options.routers.remove, params, function (result) {
            textarea.prop('disable', false);
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                usernoteWidget.find(this.options.selectors.noteText).empty();
                usernoteWidget.find(this.options.selectors.noteWrap).hide();
                usernoteWidget.find(this.options.selectors.noteAddButton).show();
                this.hideForm(usernoteWidget);

                ls.hook.run('ls_usernote_remove_after', [params, result]);
            }
        }.bind(this));
    };

    $(function(){
        ls.usernote.init();
    });

    return this;
}).call(ls.usernote || {}, jQuery);