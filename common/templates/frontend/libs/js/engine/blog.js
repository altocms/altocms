/**
 * Блоги
 */

;var ls = ls || {};

ls.blog = (function ($) {
    "use strict";

    /**
     * Дефолтные опции
     */
    var defaults = {
        // Селекторы
        selectors: {
            addBlogSelectType: '.js-blog-add-type',
            addUserList: '#blog_admin_user_add'
        }
    };

    this.options = {

    };

    /**
     * Инициализация
     *
     * @param  {Object} options Опции
     */
    this.init = function (options) {

        this.options = $.extend({}, defaults, options);

        // Подгрузка информации о выбранном типе блога при создании блога
        $(this.options.selectors.addBlogSelectType).on('change', function (e) {
            ls.blog.loadInfoType($(this).val());
        });
    };

    /**
     * Subscribe to blog/Unsubscribe from blog
     */
    this.toggleJoin = function (button, idBlog) {
        var url = ls.routerUrl('blog') + 'ajaxblogjoin/',
            params = {idBlog: idBlog};
        button = $(button);

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);

                var text = result.bState ? ls.lang.get('blog_leave') : ls.lang.get('blog_join');

                button.empty().text(text);
                button.toggleClass('active');

                $('#blog_user_count_' + idBlog).text(result.iCountUser);
                //ls.hook.run('ls_blog_toggle_join_after', [idBlog, result], button);
            }
        });
    };

    /**
     * Отправляет приглашение вступить в блог
     */
    this.addInvite = function (idBlog) {
        var addUserList = $(this.options.selectors.addUserList),
            users = addUserList.val(),
            params = {users: users, idBlog: idBlog},
            url = ls.routerUrl('blog') + 'ajaxaddbloginvite/';

        if (!users) {
            return false;
        }

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                addUserList.val('');
                $.each(result.aUsers, function (index, item) {
                    if (item.bStateError) {
                        ls.msg.error(item.sMsgTitle ? item.sMsgTitle : 'Error', item.sMsg);
                    } else {
                        if (item.sMsg) {
                            ls.msg.notice(item.sMsgTitle ? item.sMsgTitle : null, item.sMsg);
                        }
                        if ($('#invited_list').length == 0) {
                            $('#invited_list_block').append($('<ul class="list" id="invited_list"></ul>'));
                        }
                        var listItem = $('<li><a href="' + item.sUserWebPath + '" class="user">' + item.sUserLogin + '</a></li>');
                        $('#invited_list').append(listItem);
                        $('#blog-invite-empty').hide();
                    }
                    //ls.hook.run('ls_blog_add_invite_user_after', [idBlog, item], listItem);
                });
                //ls.hook.run('ls_blog_add_invite_after', [idBlog, sUsers, result]);
            }
        });

        return false;
    };

    /**
     * Повторно отправляет приглашение
     */
    this.repeatInvite = function (idUser, idBlog) {
        var url = ls.routerUrl('blog') + 'ajaxrebloginvite/',
            params = {idUser: idUser, idBlog: idBlog};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                ls.msg.notice(null, result.sMsg);
                //ls.hook.run('ls_blog_repeat_invite_after', [idUser, idBlog, result]);
            }
        });

        return false;
    };

    /**
     * Удаляет приглашение в блог
     */
    this.removeInvite = function (idUser, idBlog) {
        var url = ls.routerUrl('blog') + 'ajaxremovebloginvite/',
            params = {idUser: idUser, idBlog: idBlog};

        ls.progressStart();
        ls.ajax(url, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $('#blog-invite-remove-item-' + idBlog + '-' + idUser).remove();
                ls.msg.notice(null, result.sMsg);
                if ($('#invited_list li').length == 0) $('#blog-invite-empty').show();
                //ls.hook.run('ls_blog_remove_invite_after', [idUser, idBlog, result]);
            }
        });

        return false;
    };

    /**
     * Отображение информации о блоге
     */
    this.loadInfo = function (idBlog) {
        var url = ls.routerUrl('blog') + 'ajaxbloginfo/',
            params = {idBlog: idBlog},
            block = $('#widget_blog_info');

        block.empty().addClass('loading');

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                block.removeClass('loading').html(result.sText);
                //ls.hook.run('ls_blog_load_info_after', [idBlog, result], block);
            }
        });
    };

    /**
     * Отображение информации о типе блога
     */
    this.loadInfoType = function (type) {
        $('#blog_type_note').text(ls.lang.get('blog_create_type_' + type + '_notice'));
    };

    /**
     * Поиск блогов
     */
    this.searchBlogs = function (form) {
        form = $(form);
        var url = ls.routerUrl('blogs') + 'ajax-search/';
        var inputSearch = form.find('input');
        inputSearch.addClass('loader');

        ls.ajaxSubmit(url, form, function (result) {
            inputSearch.removeClass('loader');
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                $('#blogs-list-search').hide();
                $('#blogs-list-original').show();
            } else {
                $('#blogs-list-original').hide();
                $('#blogs-list-search').html(result.sText).show();
                //ls.hook.run('ls_blog_search_blogs_after', [form, result]);
            }
        });
    };

    /**
     * Показать подробную информацию о блоге
     */
    this.toggleInfo = function () {
        var more = $('#blog-more');

        $('#blog-more-content').slideToggle();
        more.toggleClass('expanded');

        if (more.hasClass('expanded')) {
            more.html(ls.lang.get('blog_fold_info'));
        } else {
            more.html(ls.lang.get('blog_expand_info'));
        }

        return false;
    };

    /**
     * Подгружает блоги из категории
     * @param {String} id ID категории
     */
    this.loadBlogsByCategory = function (id) {
        var url = ls.routerUrl('ajax') + 'blogs/get-by-category/',
            params = {id: id},
            $blogs = $('#blog-navigator-blog').empty().prop('disabled', true),
            $button = $('#blog-navigator-button').prop('disabled', true);

        if (id !== '0') {
            ls.ajax(url, params, function (result) {
                if (!result) {
                    ls.msg.error(null, 'System error #1001');
                } else if (result.bStateError) {
                    $blogs.append('<option>' + result.sMsg + '</option>');
                } else {
                    $(result.aBlogs).each(function (k, v) {
                        $('<option value="' + v.id + '" data-url="' + v.url_full + '">' + v.title + '</option>').appendTo($blogs);
                    });

                    $blogs.prop('disabled', false);
                    $button.prop('disabled', false);

                    //ls.hook.run('ls_blog_load_blogs_by_category_after', [id, result]);
                }
            });
        } else {
            $blogs.append('<option>' + ls.lang.get('blog') + '</option>');
        }
    };

    /**
     * Переход на страницу выбранного блога
     */
    this.navigatorGoSelectBlog = function () {
        var $sel = $('#blog-navigator-blog').find('option:selected');

        if ($sel.length) {
            window.location.href = $sel.data('url');
        }
    };

    $(function() {
        ls.blog.init();
    });

    return this;
}).call(ls.blog || {}, jQuery);