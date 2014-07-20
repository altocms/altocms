;var ls = ls || {};

/**
 * Стена пользователя
 */
ls.wall = (function ($) {

    this.options = {
        login: ''
    };

    this.iIdForReply = null;
    /**
     * Добавление записи
     */
    this.add = function (sText, iPid) {
        var url = ls.routerUrl('profile') + this.options.login + '/wall/add/';
        var params = {sText: sText, iPid: iPid};

        $('.js-button-wall-submit').attr('disabled', true);
        $('#wall-text').addClass('loader');
        ls.ajax(url, params, function (result) {
            $('.js-button-wall-submit').attr('disabled', false);
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $('.js-wall-reply-parent-text').val('');
                $('#wall-note-list-empty').hide();
                this.loadNew();
                ls.hook.run('ls_wall_add_after', [sText, iPid, result]);
            }
            $('#wall-text').removeClass('loader');
        }.bind(this));
        return false;
    };

    this.addReply = function (sText, iPid) {
        var url = ls.routerUrl('profile') + this.options.login + '/wall/add/';
        var params = {sText: sText, iPid: iPid};

        $('.js-button-wall-submit').attr('disabled', true);
        $('#wall-reply-text-' + iPid).addClass('loader');
        ls.ajax(url, params, function (result) {
            $('.js-button-wall-submit').attr('disabled', false);
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $('.js-wall-reply-text').val('');
                this.loadReplyNew(iPid);
                ls.hook.run('ls_wall_addreply_after', [sText, iPid, result]);
            }
            $('#wall-reply-text-' + iPid).removeClass('loader');
        }.bind(this));
        return false;
    };

    this.load = function (iIdLess, iIdMore, callback) {
        var url = ls.routerUrl('profile') + this.options.login + '/wall/load/';
        var params = {iIdLess: iIdLess ? iIdLess : '', iIdMore: iIdMore ? iIdMore : ''};

        ls.ajax(url, params, callback);
        return false;
    };

    this.loadReply = function (iIdLess, iIdMore, iPid, callback) {
        var url = ls.routerUrl('profile') + this.options.login + '/wall/load-reply/';
        var params = {iIdLess: iIdLess ? iIdLess : '', iIdMore: iIdMore ? iIdMore : '', iPid: iPid};

        ls.ajax(url, params, callback);
        return false;
    };

    this.loadNext = function () {
        var divLast = $('#wall-container').find('.js-wall-item:last-child');
        if (divLast.length) {
            var idLess = divLast.attr('id').replace('wall-item-', '');
        } else {
            return false;
        }
        $('#wall-button-next').addClass('loading');
        this.load(idLess, '', function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (result.iCountWall) {
                    $('#wall-container').append(result.sText);
                }
                var iCount = result.iCountWall - result.iCountWallReturn;
                if (iCount) {
                    $('#wall-count-next').text(iCount);
                } else {
                    $('#wall-button-next').detach();
                }
                ls.hook.run('ls_wall_loadnext_after', [idLess, result]);
            }
            $('#wall-button-next').removeClass('loading');
        }.bind(this));
        return false;
    };

    this.loadNew = function () {
        var divFirst = $('#wall-container').find('.js-wall-item:first-child'),
            idMore = -1;

        if (divFirst.length) {
            idMore = divFirst.attr('id').replace('wall-item-', '');
        }
        this.load('', idMore, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (result.iCountWall) {
                    $('#wall-container').prepend(result.sText);
                }
                ls.hook.run('ls_wall_loadnew_after', [idMore, result]);
            }
        }.bind(this));
        return false;
    };

    this.loadReplyNew = function (iPid) {
        var divFirst = $('#wall-reply-container-' + iPid).find('.js-wall-reply-item:last-child');
        if (divFirst.length) {
            var idMore = divFirst.attr('id').replace('wall-reply-item-', '');
        } else {
            var idMore = -1;
        }
        this.loadReply('', idMore, iPid, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (result.iCountWall) {
                    $('#wall-reply-container-' + iPid).append(result.sText);
                }
                ls.hook.run('ls_wall_loadreplynew_after', [iPid, idMore, result]);
            }
        }.bind(this));
        return false;
    };

    this.loadReplyNext = function (iPid) {
        var divLast = $('#wall-reply-container-' + iPid).find('.js-wall-reply-item:first-child');
        if (divLast.length) {
            var idLess = divLast.attr('id').replace('wall-reply-item-', '');
        } else {
            return false;
        }
        $('#wall-reply-button-next-' + iPid).addClass('loading');
        this.loadReply(idLess, '', iPid, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                if (result.iCountWall) {
                    $('#wall-reply-container-' + iPid).prepend(result.sText);
                }
                var iCount = result.iCountWall - result.iCountWallReturn;
                if (iCount) {
                    $('#wall-reply-count-next-' + iPid).text(iCount);
                } else {
                    $('#wall-reply-button-next-' + iPid).detach();
                }
                ls.hook.run('ls_wall_loadreplynext_after', [iPid, idLess, result]);
            }
            $('#wall-reply-button-next-' + iPid).removeClass('loading');
        }.bind(this));
        return false;
    };

    this.toggleReply = function (iId) {
        $('#wall-item-' + iId + ' .wall-submit-reply').addClass('active').toggle().children('textarea').focus();
        return false;
    };

    this.expandReply = function (iId) {
        $('#wall-item-' + iId + ' .wall-submit-reply').addClass('active');
        return false;
    };

    this.init = function (opt) {
        if (opt) {
            $.extend(true, this.options, opt);
        }
        jQuery(function ($) {
            $(document).click(function (e) {
                if (e.which == 1) {
                    $('.wall-submit-reply.active').each(function (k, v) {
                        if (!$(v).find('.js-wall-reply-text').val()) {
                            $(v).removeClass('active');
                        }
                    });
                }
            });

            $('body').on("click", ".wall-submit-reply, .link-dotted", function (e) {
                e.stopPropagation();
            });

            $('.js-wall-reply-text').bind('keyup', function (e) {
                key = e.keyCode || e.which;
                if (e.ctrlKey && (key == 13)) {
                    var id = $(e.target).attr('id').replace('wall-reply-text-', '');
                    this.addReply($(e.target).val(), id);
                    return false;
                }
            }.bind(this));
            $('.js-wall-reply-parent-text').bind('keyup', function (e) {
                key = e.keyCode || e.which;
                if (e.ctrlKey && (key == 13)) {
                    this.add($(e.target).val(), 0);
                    return false;
                }
            }.bind(this));
        }.bind(this));
    };

    this.remove = function (iId) {
        var url = ls.routerUrl('profile') + this.options.login + '/wall/remove/';
        var params = {iId: iId};

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $('#wall-item-' + iId).fadeOut('slow', function () {
                    ls.hook.run('ls_wall_remove_item_fade', [iId, result], this);
                });
                $('#wall-reply-item-' + iId).fadeOut('slow', function () {
                    ls.hook.run('ls_wall_remove_reply_item_fade', [iId, result], this);
                });
                ls.hook.run('ls_wall_remove_after', [iId, result]);
            }
        });
        return false;
    };

    return this;
}).call(ls.wall || {}, jQuery);