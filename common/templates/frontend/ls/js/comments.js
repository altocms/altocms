;var ls = ls || {};

/**
* Обработка комментариев
*/
ls.comments = (function ($) {
    /**
     * Опции
     */
    this.options = {
        type: {
            topic: {
                url_add:        ls.routerUrl('blog') + 'ajaxaddcomment/',
                url_response:   ls.routerUrl('blog') + 'ajaxresponsecomment/',
                url_get:        ls.routerUrl('blog') + 'ajaxgetcomment/',
                url_update:     ls.routerUrl('blog') + 'ajaxupdatecomment/'
            },
            talk: {
                url_add:        ls.routerUrl('talk') + 'ajaxaddcomment/',
                url_response:   ls.routerUrl('talk') + 'ajaxresponsecomment/'
            }
        },
        classes: {
            form_loader:        'loader',
            comment_new:        'comment-new',
            comment_current:    'comment-current',
            comment_deleted:    'comment-deleted',
            comment_self:       'comment-self',
            comment:            'comment',
            comment_goto_parent:'goto-comment-parent',
            comment_goto_child: 'goto-comment-child'
        },
        replyForm: null,
        wysiwyg: null,
        folding: true,
        editTimers: []
    };

    this.iCurrentShowFormComment = 0;
    this.iCurrentViewComment = null;
    this.aCommentNew = [];

    this.getReplyForm = function() {
        if (this.options.replyForm && this.options.replyForm.length) {
            return this.options.replyForm;
        }
    };

    this.formCommentText = function() {
        if (arguments.length) {
            $('#form_comment_text').val(arguments[0]);
            if (this.options.wysiwyg) {
                tinyMCE.activeEditor.setContent(arguments[0]);
            }
        } else {
            if (this.options.wysiwyg) {
                return tinyMCE.activeEditor.getContent();
            } else {
                return $('#form_comment_text').val();
            }
        }
    };

    this.newComment = function(idComment) {
        this.options.replyForm.find('[name=comment_id]').val(0);
        this.toggleCommentForm(idComment);
        this.formCommentText('');
    };

    // Добавляет комментарий
    this.add = function (formObj, targetId, targetType) {
        $('#' + formObj + ' textarea').val(this.formCommentText());
        formObj = $('#' + formObj);

        this.waitFormComment();
        ls.ajax(this.options.type[targetType].url_add, formObj.serializeJSON(), function (result) {
            if (!result) {
                this.enableFormComment();
                ls.msg.error('Error', 'Please try again later');
                return;
            }
            if (result.bStateError) {
                this.enableFormComment();
                ls.msg.error(null, result.sMsg);
            } else {
                this.enableFormComment();
                this.formCommentText('');

                // Load new comments
                this.load(targetId, targetType, result.sCommentId, true);
                ls.hook.run('ls_comments_add_after', [formObj, targetId, targetType, result]);
            }
        }.bind(this));
    };


    this.waitFormComment = function() {
        var replyForm = this.getReplyForm();

        if (this.options.wysiwyg) {
            tinyMCE.activeEditor.setProgressState(1);
        } else {
            $('#form_comment_text').addClass(this.options.classes.form_loader).prop('readonly',true);
        }
        if (replyForm) {
            replyForm.find('.btn,[class|=btn],.button').prop('disabled', true);
        }
    }

    // Активирует форму
    this.enableFormComment = function() {
        var replyForm = this.getReplyForm();

        if (this.options.wysiwyg) {
            tinyMCE.activeEditor.setProgressState(0);
        } else {
            $('#form_comment_text').removeClass(this.options.classes.form_loader).prop('readonly',false);
        }
        if (replyForm) {
            replyForm.find('.btn,[class|=btn],.button').prop('disabled', false);
        }
    };

    this.hideCommentForm = function() {
        var replyForm = this.getReplyForm();
        if(replyForm){
            $('.comment-actions [class|=comment]').removeClass('active');
            replyForm.hide();
        }
        $('.comment-preview').text('').hide();
    };

    /**
     * Показывает/скрывает форму комментирования
     *
     * @param idComment
     * @param bNoFocus
     * @param mode
     * @returns {boolean}   - false - форма скрыта, true - форма видима
     */
    this.toggleCommentForm = function(idComment, bNoFocus, mode) {
        var replyForm = this.getReplyForm();
        if (!replyForm) {
            return;
        }
        if (!mode) mode = 'reply';
        $('#comment_preview_' + this.iCurrentShowFormComment).remove();

        if (this.iCurrentShowFormComment == idComment && replyForm.is(':visible')) {
            $('#comment_id_' + idComment + ' .comment-actions [class|=comment]').removeClass('active');
            if (replyForm.find('[name=comment_mode]').val() == mode) {
                this.hideCommentForm();
                return false;
            }
        }
        if (this.options.wysiwyg) {
            tinyMCE.execCommand('mceRemoveControl', true, 'form_comment_text');
        }
        replyForm.insertAfter('#comment_id_'+idComment).show();

        this.iCurrentShowFormComment = idComment;
        if (this.options.wysiwyg) {
            tinyMCE.execCommand('mceAddControl', true, 'form_comment_text');
            if (!bNoFocus) tinyMCE.activeEditor.focus();
        } else {
            if (!bNoFocus) $('#form_comment_text').focus();
        }
        this.formCommentText('');
        replyForm.find('[name=comment_mode]').val(mode);
        $('#form_comment_reply').val(idComment);

        if ($('html').hasClass('ie7')) {
            var inputs = $('input.input-text, textarea');
            ls.ie.bordersizing(inputs);
        }
        if (mode == 'edit') {
            //replyForm.find('.btn-reply').hide();
            //replyForm.find('.btn-edit').show();
            $('#comment-button-submit').hide();
            $('#comment-button-edit').show();
            replyForm.find('.reply-notice-add').hide();
            replyForm.find('.reply-notice-edit').show();
            $('#comment_id_'+idComment + ' .comment-actions .comment-reply').removeClass('active');
            $('#comment_id_'+idComment + ' .comment-actions .comment-edit').addClass('active');
        } else {
            //replyForm.find('.btn-reply').show();
            //replyForm.find('.btn-edit').hide();
            $('#comment-button-submit').show();
            $('#comment-button-edit').hide();
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

    this.loadComment = function(idComment, targetType, targetId, env) {
        if (!env.context) {
            env.context = this;
        }
        if (!env.success) {
            env.success = function() {};
        }
        var params = {
            targetId: targetId,
            commentId: idComment,
            submit: false
        };
        this.waitFormComment();
        ls.ajax(this.options.type[targetType].url_get, params, function(result){
            if (!result) {
                this.enableFormComment();
                ls.msg.error('Error','Please try again later');
                return;
            }
            if (result.bStateError) {
                this.enableFormComment();
                ls.msg.error(null,result.sMsg);
            } else {
                this.enableFormComment();
                env.success(result);
            }
        }.bind(env.context));
    };

    this.editComment = function(idComment, targetType, targetId) {
        this.options.replyForm.find('[name=comment_id]').val(idComment);
        if (this.toggleCommentForm(idComment, false, 'edit')) {
            var $that = this;
            var env = {
                context: this,
                success: function(result) {
                    $that.formCommentText(result.sText);
                }
            };
            this.loadComment(idComment, targetType, targetId, env);
        }
    };

    this.editSubmit = function(formObj, targetId, targetType) {
        $('#'+formObj+' textarea').val(this.formCommentText());
        formObj = $('#'+formObj);

        this.waitFormComment();
        ls.ajax(this.options.type[targetType].url_update, formObj.serializeJSON(), function(result){
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
                this.hideCommentForm();
                $('#comment_content_id_' + result.nCommentId).html(result.sText);
                $('#comment_updated_id_' + result.nCommentId + ' time')
                    .text(result.sDateEdit)
                    .prop('datetime', result.sDateEdit)
                    .show();
            }
        }.bind(this));
    };

    // Подгружает новые комментарии
    this.load = function (idTarget, typeTarget, selfIdComment, bNotFlushNew) {
        var idCommentLast = $("#comment_last_id").val();

        this.waitFormComment();
        // Удаляем подсветку у комментариев
        if (!bNotFlushNew) {
            $('.comment').each(function (index, item) {
                $(item).removeClass(this.options.classes.comment_new + ' ' + this.options.classes.comment_current);
            }.bind(this));
        }

        var objImg = $('#update-comments');
        objImg.addClass('active');

        var params = { idCommentLast: idCommentLast, idTarget: idTarget, typeTarget: typeTarget };
        if (selfIdComment) {
            params.selfIdComment = selfIdComment;
        }
        if ($('#comment_use_paging').val()) {
            params.bUsePaging = 1;
        }

        ls.ajax(this.options.type[typeTarget].url_response, params, function (result) {
            this.enableFormComment();
            objImg.removeClass('active');

            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var aCmt = result.aComments;
                if (aCmt.length > 0 && result.iMaxIdComment) {
                    $("#comment_last_id").val(result.iMaxIdComment);
                    $('#count-comments').text(parseInt($('#count-comments').text()) + aCmt.length);
                    if (ls.blocks) {
                        var curItemBlock = ls.blocks.getCurrentItem('stream');
                        if (curItemBlock.data('type') == 'comment') {
                            ls.blocks.load(curItemBlock, 'stream');
                        }
                    }
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
            $('#comments').append(newComment);
        }
        ls.hook.run('ls_comment_inject_after', arguments, newComment);
    };


    // Удалить/восстановить комментарий
    this.toggle = function (obj, commentId) {
        var url = aRouter['ajax'] + 'comment/delete/';
        var params = { idComment: commentId };

        ls.hook.marker('toggleBefore');
        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error('Error', 'Please try again later');
            }
            if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);

                $('#comment_id_' + commentId).removeClass(this.options.classes.comment_self + ' ' + this.options.classes.comment_new + ' ' + this.options.classes.comment_deleted + ' ' + this.options.classes.comment_current);
                if (result.bState) {
                    $('#comment_id_' + commentId).addClass(this.options.classes.comment_deleted);
                }
                $(obj).text(result.sTextToggle);
                ls.hook.run('ls_comments_toggle_after', [obj, commentId, result]);
            }
        }.bind(this));
    };


    // Предпросмотр комментария
    this.preview = function () {
        if (this.formCommentText() == '') return;
        $("#comment_preview_" + this.iCurrentShowFormComment).remove();
        this.options.replyForm.before('<div id="comment_preview_' + this.iCurrentShowFormComment + '" class="comment-preview text"></div>');
        ls.tools.textPreview('form_comment_text', false, 'comment_preview_' + this.iCurrentShowFormComment);
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
        var aCommentsNew = $('.' + this.options.classes.comment + '.' + this.options.classes.comment_new);
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
            $('#comment_id_' + this.iCurrentViewComment).removeClass(this.options.classes.comment_current);
        }
        $('#comment_id_' + idComment).addClass(this.options.classes.comment_current);
        this.iCurrentViewComment = idComment;
    };


    // Прокрутка к родительскому комментарию
    this.goToParentComment = function (id, pid) {
        var thisObj = this;
        $('.' + this.options.classes.comment_goto_child).hide().find('a').unbind();

        $("#comment_id_" + pid).find('.' + this.options.classes.comment_goto_child).show().find("a").bind("click", function () {
            $(this).parent('.' + thisObj.options.classes.comment_goto_child).hide();
            thisObj.scrollToComment(id);
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
        $(".folding").each(function (index, element) {
            if ($(element).parent(".comment").next(".comment-wrapper").length == 0) {
                $(element).hide();
            } else {
                $(element).show();
            }
        });
        return false;
    };

    this.expandComment = function (folding) {
        $(folding).removeClass("folded").parent().nextAll(".comment-wrapper").show();
    };

    this.collapseComment = function (folding) {
        $(folding).addClass("folded").parent().nextAll(".comment-wrapper").hide();
    };

    this.expandCommentAll = function () {
        $.each($(".folding"), function (k, v) {
            this.expandComment(v);
        }.bind(this));
    };

    this.collapseCommentAll = function () {
        $.each($(".folding"), function (k, v) {
            this.collapseComment(v);
        }.bind(this));
    };

    this.checkEditTimers = function() {
        var $that = this;
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

    this.init = function () {
        this.initEvent();

        this.options.replyForm = $('#reply');

        this.calcNewComments();
        this.checkFolding();
        this.toggleCommentForm(this.iCurrentShowFormComment);
        if (typeof(this.options.wysiwyg) != 'number') {
            this.options.wysiwyg = Boolean(BLOG_USE_TINYMCE && tinyMCE);
        }

        this.checkEditTimers();
        ls.hook.run('ls_comments_init_after', [], this);
    };

    this.initEvent = function () {
        $('#form_comment_text').bind('keyup', function (e) {
            var key = e.keyCode || e.which;
            if (e.ctrlKey && (key == 13)) {
                if ($(this).parents('form').find('[name=comment_mode]').val() == 'edit') {
                    $('#comment-button-edit').click();
                } else {
                    $('#comment-button-submit').click();
                }
                return false;
            }
        });

        if (this.options.folding) {
            $(".folding").click(function (e) {
                if ($(e.target).hasClass("folded")) {
                    this.expandComment(e.target);
                } else {
                    this.collapseComment(e.target);
                }
            }.bind(this));
        }
    };

    return this;
}).call(ls.comments || {},jQuery);
