/**
 * Комментарии
 */

;var ls = ls || {};

ls.comments = (function ($) {
    var $that = this;

    /**
     * Опции
     */
    this.defaults = {
        type: {
            topic: {
                urlAdd:      ls.routerUrl('blog') + 'ajaxaddcomment/',
                urlResponse: ls.routerUrl('blog') + 'ajaxresponsecomment/',
                urlGet:      ls.routerUrl('blog') + 'ajaxgetcomment/',
                urlUpdate:   ls.routerUrl('blog') + 'ajaxupdatecomment/'
            },
            talk: {
                urlAdd:      ls.routerUrl('talk') + 'ajaxaddcomment/',
                urlResponse: ls.routerUrl('talk') + 'ajaxresponsecomment/'
            }
        },
        classes: {
            formLoading: 'loading',
            folding: 'comment-folding',
            showNew: 'comment-new',
            showCurrent: 'comment-current',
            showDeleted: 'comment-deleted',
            showSelf: 'comment-self',
            comment: 'comment',
            gotoParent: 'comment-goto-parent',
            gotoChild: 'comment-goto-child'
        },
        selectors: {
            form: '.js-form-comment'
        },
        replyForm: null,
        wysiwyg: null,
        folding: true,
        editTimers: []
    };

    this.options = {

    };

    this.iCurrentShowFormComment = 0;
    this.iCurrentViewComment = null;
    this.aCommentNew = [];

    this.getReplyForm = function(form) {
        if (form) {
            form = $(form).closest('form');
            if (form.length) {
                return form;
            }
        }
        if (!this.options.replyForm) {
            this.options.replyForm = $(this.options.selectors.form);
        }
        if (this.options.replyForm && this.options.replyForm.length) {
            return this.options.replyForm;
        }
        return null;
    };

    /**
     * Init
     * @param  {Object} options Options
     */
    this.init = function (options) {

        this.options = $.extend({}, this.defaults, options);
        if (this.getReplyForm()) {

            this.initEvent();
            this.calcNewComments();
            this.checkFolding();
            this.checkEditTimers();
            this.toggleCommentForm(this.iCurrentShowFormComment);

            if (typeof(this.options.wysiwyg) != 'number') {
                this.options.wysiwyg = Boolean(ls.cfg.wysiwyg && tinymce);
            }
            ls.hook.run('ls_comments_init_after', [], this);
        }
    };

    /**
     * Sets/Gets text from comment form
     *
     * @returns {*}
     */
    this.formCommentText = function() {
        var replyForm = this.getReplyForm(),
            textarea = replyForm.find('textarea');

        if (arguments.length) {
            textarea.val(arguments[0]);
            if (this.options.wysiwyg && tinyMCE.activeEditor) {
                tinyMCE.activeEditor.setContent(arguments[0]);
            }
        } else {
            if (this.options.wysiwyg && tinyMCE.activeEditor) {
                return tinyMCE.activeEditor.getContent();
            } else {
                return textarea.val();
            }
        }
    };

    this.initEvent = function () {
        var form = this.getReplyForm();

        form.find('textarea').bind('keyup', function (e) {
            var key = e.keyCode || e.which;
            if (e.ctrlKey && (key == 13)) {
                if ($(this).parents('form').find('[name=comment_mode]').val() == 'edit') {
                    form.find('.js-button-edit').click();
                } else {
                    form.find('.js-button-submit').click();
                }
                return false;
            }
        });

        if (this.options.folding) {
            $('.' + this.options.classes.folding).click(function (e) {
                if ($(e.target).hasClass("folded")) {
                    this.expandComment(e.target);
                } else {
                    this.collapseComment(e.target);
                }
            }.bind(this));
        }
    };

    // Добавляет комментарий
    this.add = function (form, targetId, targetType) {
        var textarea;

        form = this.getReplyForm(form);
        textarea = form.find('textarea');

        if (this.options.wysiwyg && tinyMCE.activeEditor) {
            textarea.val(tinyMCE.activeEditor.getContent());
        }

        textarea.addClass(this.options.classes.formLoading).attr('readonly', true);
        form.find('.js-button-submit').attr('disabled', 'disabled');

        ls.progressStart();
        ls.ajax(this.options.type[targetType].urlAdd, form.serializeJSON(), function (result) {
            ls.progressDone();
            form.find('.js-button-submit').removeAttr('disabled');
            this.formCommentWait();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
                return;
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                this.formCommentText('');

                // Load new comments
                this.load(targetId, targetType, result.sCommentId, true);
                ls.hook.run('ls_comments_add_after', [form, targetId, targetType, result]);
            }
        }.bind(this));
    };

    this.formCommentWait = function() {
        var replyForm = this.getReplyForm(),
            textarea = replyForm.find('textarea');

        if (this.options.wysiwyg && tinyMCE.activeEditor) {
            tinyMCE.activeEditor.setProgressState(1);
        } else {
            textarea.addClass(this.options.classes.formLoading).prop('readonly',true);
        }
        if (replyForm) {
            replyForm.find('.btn,[class|=btn],.button').prop('disabled', true);
        }
    }

    /**
     * Activates comment form
     */
    this.enableFormComment = function() {
        var replyForm = this.getReplyForm(),
            textarea = replyForm.find('textarea');

        if (this.options.wysiwyg && tinyMCE.activeEditor) {
            tinyMCE.activeEditor.setProgressState(0);
        } else {
            textarea.removeClass(this.options.classes.formLoading).prop('readonly',false);
        }
        if (replyForm) {
            replyForm.find('.btn,[class|=btn],.button').prop('disabled', false);
        }
    };

    /**
     * Hides comment form
     */
    this.formCommentHide = function() {
        var replyForm = this.getReplyForm();

        if(replyForm){
            $('.comment-actions [class|=comment]').removeClass('active');
            replyForm.hide();
        }
        $('.comment-preview').text('').hide();
    };

    /**
     * Hides/shows comment form
     *
     * @param idComment
     * @param bNoFocus
     * @param mode
     * @returns {boolean}   - false - форма скрыта, true - форма видима
     */
    this.toggleCommentForm = function (idComment, bNoFocus, mode) {
        var replyForm = this.getReplyForm(),
            textarea = replyForm.find('textarea'),
            textareaId = textarea.attr('id');

        if (!replyForm.length) {
            return false;
        }
        if (!mode) {
            mode = 'reply';
        }
        $('#comment_preview_' + this.iCurrentShowFormComment).remove();

        if (this.iCurrentShowFormComment == idComment && replyForm.is(':visible')) {
            this.formCommentHide();
            return false;
        }

        if (this.options.wysiwyg) {
            tinyMCE.execCommand('mceRemoveEditor', true, textareaId);
        }
        replyForm.insertAfter('#comment_id_' + idComment).show();
        if (this.options.wysiwyg) {
            tinyMCE.execCommand('mceAddEditor', true, textareaId);
            if (!bNoFocus && tinyMCE.activeEditor) {
                tinyMCE.activeEditor.focus();
            }
        } else {
            if (!bNoFocus) {
                textarea.focus();
            }
        }

        this.formCommentText('');
        replyForm.find('[name=comment_mode]').val(mode);
        $('#form_comment_reply').val(idComment);

        this.iCurrentShowFormComment = idComment;

        if ($('html').hasClass('ie7')) {
            var inputs = $('input.input-text, textarea');
            ls.ie.bordersizing(inputs);
        }
        this.enableFormComment();
        if (mode == 'edit') {
            replyForm.find('.js-button-submit').hide();
            replyForm.find('.js-button-edit').show();
            replyForm.find('.reply-notice-add').hide();
            replyForm.find('.reply-notice-edit').show();
            $('#comment_id_'+idComment + ' .comment-actions .comment-reply').removeClass('active');
            $('#comment_id_'+idComment + ' .comment-actions .comment-edit').addClass('active');
        } else {
            replyForm.find('.js-button-submit').show();
            replyForm.find('.js-button-edit').hide();
            replyForm.find('.reply-notice-add').show();
            replyForm.find('.reply-notice-edit').hide();
            $('#comment_id_'+idComment + ' .comment-actions .comment-reply').addClass('active');
            $('#comment_id_'+idComment + ' .comment-actions .comment-edit').removeClass('active');
        }
        if (!idComment) {
            $('.comment-actions [class|=comment]').removeClass('active');
        }
        return true;
    };

    this.reply = function(idComment) {

        this.toggleCommentForm(idComment, false, 'reply');
    };

    /**
     * Loads text of the comment
     *
     * @param idComment
     * @param targetType
     * @param targetId
     * @param options
     */
    this.loadComment = function(idComment, targetType, targetId, options) {
        if (!options.context) {
            options.context = this;
        }
        if (!options.success) {
            options.success = function() {};
        }
        var params = {
            targetId: targetId,
            commentId: idComment,
            submit: false
        };

        ls.progressStart();
        this.formCommentWait();
        ls.ajax(this.options.type[targetType].urlGet, params, function(result){
            ls.progressDone();
            $that.enableFormComment();
            if (!result) {
                ls.msg.error('Error','Please try again later');
            } else if (result.bStateError) {
                ls.msg.error(null,result.sMsg);
            } else {
                options.success(result);
            }
        }.bind(options.context));
    };

    /**
     *
     * @param idComment
     * @param targetType
     * @param targetId
     */
    this.editComment = function(idComment, targetType, targetId) {

        this.options.replyForm.find('[name=comment_id]').val(idComment);
        if (this.toggleCommentForm(idComment, false, 'edit')) {
            var options = {
                context: this,
                success: function(result) {
                    $that.formCommentText(result.sText);
                }
            };
            this.loadComment(idComment, targetType, targetId, options);
        }
    };

    /**
     *
     * @param form
     * @param targetId
     * @param targetType
     */
    this.editSubmit = function(form, targetId, targetType) {
        var textarea;

        form = this.getReplyForm(form);
        textarea = form.find('textarea');
        textarea.val(this.formCommentText());

        this.formCommentWait();
        ls.progressStart();
        ls.ajax(this.options.type[targetType].urlUpdate, form.serializeJSON(), function(result){
            ls.progressDone();
            this.enableFormComment();
            if (!result) {
                ls.msg.error('Error','Please try again later');
                return;
            }
            if (result.bStateError) {
                ls.msg.error(null,result.sMsg);
            } else {
                if (result.sMsg) ls.msg.notice(null,result.sMsg);
                this.formCommentText('');
                this.formCommentHide();
                var comment = $('#comment_id_' + result.nCommentId);
                comment.find('.comment-content').html(result.sText);
                comment.find('.comment-updated').show()
                    .find('time').text(result.sDateEditText).prop('datetime', result.sDateEdit);
            }
        }.bind(this));
    };

    // Подгружает новые комментарии
    this.load = function (idTarget, typeTarget, selfIdComment, bNotFlushNew) {
        var button = $('#update-comments'),
            idCommentLast = $("#comment_last_id").val(),
            params = { idCommentLast: idCommentLast, idTarget: idTarget, typeTarget: typeTarget };

        // Удаляем подсветку у комментариев
        if (!bNotFlushNew) {
            $('.comment').each(function (index, item) {
                $(item).removeClass(this.options.classes.showNew + ' ' + this.options.classes.showCurrent);
            }.bind(this));
        }

        if (selfIdComment) {
            params.selfIdComment = selfIdComment;
        }
        if ($('#comment_use_paging').val()) {
            params.bUsePaging = 1;
        }

        button.addClass('active').find('.glyphicon').addClass('spin');
        ls.progressStart();
        ls.ajax(this.options.type[typeTarget].urlResponse, params, function (result) {
            ls.progressDone();
            button.removeClass('active').find('.glyphicon').removeClass('spin');

            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var aCmt = result.aComments;
                if (aCmt.length > 0 && result.iMaxIdComment) {
                    $("#comment_last_id").val(result.iMaxIdComment);
                    $('#count-comments').text(parseInt($('#count-comments').text()) + aCmt.length);

                    var streamComments = $('#js-stream-tabs').is(':visible') ? $('#js-stream-tabs [data-name=block-stream-comments]') : $('#js-stream-dropdown [data-name=block-stream-comments]');
                    (streamComments.length && streamComments.hasClass('active')) && streamComments.tab('activate');
                }
                var iCountOld = 0;
                if (bNotFlushNew) {
                    iCountOld = this.aCommentNew.length;
                } else {
                    this.aCommentNew = [];
                }
                if (selfIdComment) {
                    this.toggleCommentForm(this.iCurrentShowFormComment, true);
                    this.setCountNewComment(aCmt.length - 1 + iCountOld);
                } else {
                    this.setCountNewComment(aCmt.length + iCountOld);
                }

                $.each(aCmt, function (index, item) {
                    if (!(selfIdComment && selfIdComment == item.id)) {
                        this.aCommentNew.push(item.id);
                    }
                    this.inject(item.idParent, item.id, item.html);
                }.bind(this));

                if (selfIdComment && $('#comment_id_' + selfIdComment).length) {
                    this.scrollToComment(selfIdComment);
                }
                this.checkFolding();
                this.checkEditTimers();
                ls.hook.run('ls_comments_load_after', [idTarget, typeTarget, selfIdComment, bNotFlushNew, result]);
            }
        }.bind(this));
    };


    // Вставка комментария
    this.inject = function (idCommentParent, idComment, sHtml) {
        var newComment = $('<div>', {'class': 'comment-wrapper', id: 'comment_wrapper_id_' + idComment}).html(sHtml);
        if (idCommentParent) {
            // Уровень вложенности родителя
            var iCurrentTree = $('#comment_wrapper_id_' + idCommentParent).parentsUntil('#comments').length;
            if (iCurrentTree == ls.registry.get('comment_max_tree')) {
                // Определяем id предыдушего родителя
                var prevCommentParent = $('#comment_wrapper_id_' + idCommentParent).parent();
                idCommentParent = parseInt(prevCommentParent.attr('id').replace('comment_wrapper_id_', ''));
            }
            $('#comment_wrapper_id_' + idCommentParent).append(newComment);
        } else {
            var lastComment = $('#comments > .comment-wrapper').last();
            if (lastComment.length) {
                $(newComment).insertAfter(lastComment);
            } else {
                $(newComment).insertAfter('#comments > .comments-header');
            }
        }
        ls.hook.run('ls_comment_inject_after', arguments, newComment);
    };


    // Удалить/восстановить комментарий
    this.toggle = function (obj, commentId) {
        var url = ls.routerUrl('ajax') + 'comment/delete/';
        var params = { idComment: commentId };

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);

                $('#comment_id_' + commentId).removeClass(this.options.classes.showSelf + ' ' + this.options.classes.showNew + ' ' + this.options.classes.showDeleted + ' ' + this.options.classes.showCurrent);
                if (result.bState) {
                    $('#comment_id_' + commentId).addClass(this.options.classes.showDeleted);
                }
                $(obj).text(result.sTextToggle);
                ls.hook.run('ls_comments_toggle_after', [obj, commentId, result]);
            }
        }.bind(this));
    };


    // Предпросмотр комментария
    this.preview = function () {
        if (this.formCommentText() == '') return;
        var replyForm = this.getReplyForm(),
            id = 'comment_preview_' + this.iCurrentShowFormComment,
            textarea = replyForm.find('textarea');
        $('#' + id).remove();
        replyForm.before('<div id="' + id + '" class="comment-preview text"></div>');
        ls.tools.textPreview(textarea, false, id);
    };


    // Устанавливает число новых комментариев
    this.setCountNewComment = function (count) {
        if (count > 0) {
            $('#new_comments_counter').show().text(count);
        } else {
            $('#new_comments_counter').text(0).hide();
        }
    };


    // Вычисляет кол-во новых комментариев
    this.calcNewComments = function () {
        var aCommentsNew = $('.' + this.options.classes.comment + '.' + this.options.classes.showNew);
        this.setCountNewComment(aCommentsNew.length);
        $.each(aCommentsNew, function (k, v) {
            this.aCommentNew.push(parseInt($(v).attr('id').replace('comment_id_', '')));
        }.bind(this));
    };


    // Переход к следующему комментарию
    this.goToNextComment = function () {
        if (this.aCommentNew[0]) {
            if ($('#comment_id_' + this.aCommentNew[0]).length) {
                this.scrollToComment(this.aCommentNew[0]);
            }
            this.aCommentNew.shift();
        }
        this.setCountNewComment(this.aCommentNew.length);
    };


    // Прокрутка к комментарию
    this.scrollToComment = function (idComment) {
        $.scrollTo('#comment_id_' + idComment, 1000, {offset: -250});

        if (this.iCurrentViewComment) {
            $('#comment_id_' + this.iCurrentViewComment).removeClass(this.options.classes.showCurrent);
        }
        $('#comment_id_' + idComment).addClass(this.options.classes.showCurrent);
        this.iCurrentViewComment = idComment;
    };


    // Прокрутка к родительскому комментарию
    this.goToParentComment = function (id, pid) {

        $('.' + this.options.classes.gotoChild).hide().find('a').unbind();

        $("#comment_id_" + pid).find('.' + this.options.classes.gotoChild).show().find("a").bind("click", function () {
            $(this).parent('.' + $that.options.classes.gotoChild).hide();
            $that.scrollToComment(id);
            return false;
        });
        this.scrollToComment(pid);
        return false;
    };


    // Сворачивание комментариев
    this.checkFolding = function () {
        if (!this.options.folding) {
            return false;
        }
        $('.' + this.options.classes.folding).each(function (index, element) {
            if ($(element).parent(".comment").next(".comment-wrapper").length == 0) {
                $(element).hide();
            } else {
                $(element).show();
            }
        });
        return false;
    };

    this.checkEditTimers = function() {

        $('.comment-edit-time-remainder').each(function(){
            var id = '';
            var el = $(this);
            var parent = el.parents('.b-comment').first();
            if (!parent.length) {
                parent = el.parents('.comment').first();
            }
            if (parent.length) {
                id = parent.prop('id');
            }
            if (parseInt(el.text()) > 0) {
                if (!$that.options.editTimers[id]) {
                    $that.options.editTimers[id] = setInterval(function() {
                        var time = parseInt(el.text()) - 1;
                        if (time <= 0 || isNaN(time)) {
                            $that.clearEditTimer(id);
                        } else {
                            el.text(time);
                            $('#' + id).find('.comment-edit-time-rest').text(ls.tools.timeRest(time));
                        }
                    }, 1000);
                }
            } else {
                $that.clearEditTimer(id);
            }
        });
    };

    this.clearEditTimer = function(id) {
        if (ls.comments.options.editTimers[id]) {
            clearInterval(this.options.editTimers[id]);
        }
        $('#' + id).find('.comment-edit').hide();
    };

    this.expandComment = function (folding) {
        $(folding).removeClass("folded").parent().nextAll(".comment-wrapper").show();
    };

    this.collapseComment = function (folding) {
        $(folding).addClass("folded").parent().nextAll(".comment-wrapper").hide();
    };

    this.expandCommentAll = function () {
        $.each($('.' + this.options.classes.folding), function (k, v) {
            this.expandComment(v);
        }.bind(this));
    };

    this.collapseCommentAll = function () {
        $.each($('.' + this.options.classes.folding), function (k, v) {
            this.collapseComment(v);
        }.bind(this));
    };

    $(function(){
        ls.comments.init();
    });

    return this;
}).call(ls.comments || {}, jQuery);
