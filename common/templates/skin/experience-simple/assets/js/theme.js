/*!
 * Тема оформления Experience v.1.0  для Alto CMS
 * @licence     CC Attribution-ShareAlike
 */

$(function () {

    var pretty_markup = '<div class="pp_pic_holder"> \
						<div class="ppt">&nbsp;</div> \
						<div class="pp_top"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
						<div class="pp_content_container"> \
							<div class="pp_left"> \
							<div class="pp_right"> \
								<div class="pp_content"> \
									<div class="pp_loaderIcon"></div> \
									<div class="pp_fade"> \
									    <a class="pp_close" href="#"></a> \
										<a href="#" class="pp_expand"></a> \
										<div class="pp_hoverContainer"> \
											<a class="pp_next" href="#"></a> \
											<a class="pp_previous" href="#"></a> \
										</div> \
										<div id="pp_full_res"></div> \
										<div class="pp_details"> \
											<div class="pp_nav"> \
												<a href="#" class="pp_arrow_previous"></a> \
												<p class="currentTextHolder">0/0</p> \
												<a href="#" class="pp_arrow_next"></a> \
											</div> \
											<p class="pp_description"></p> \
											<div class="pp_social">{pp_social}</div> \
										</div> \
									</div> \
								</div> \
							</div> \
							</div> \
						</div> \
						<div class="pp_bottom"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
					</div> \
					<div class="pp_overlay"></div>';

    $('.modal').on('show.bs.modal', function () {
        if ($(document).height() > $(window).height()) {
            // no-scroll
            $('body').addClass("modal-open-noscroll");
        }
        else {
            $('body').removeClass("modal-open-noscroll");
        }
    }).on('hide.bs.modal', function () {
        $('body').removeClass("modal-open-noscroll");
    });


    //noinspection JSUnresolvedFunction
    //$('.main-menu').flexMenu();
    //$('.navbar-main').flexMenuL2();

    // Сменим главный лоадер
    ls.options.progressType = 'nprogress';

    /**
     * ИНИЦИАЛИЗИРУЕМ МОДАЛЬНЫЕ ОКНА
     */
        // Авторизация
    $('.js-modal-auth-login').click(function () {
        $('#modal-auth').modal();
        $('#modal-auth input[type="text"]').val('');
        $('#modal-auth input[type="password"]').val('');
        $('.form-control-feedback-ok').hide();
        $('.validate-error-show').removeClass('validate-error-show').addClass('validate-error-hide');
        var tab = $('#modal-auth .js-tab-login').tab('show');

        return false;
    });
    // Обновляем капчу, если нужно
    $('.js-tab-registration').click(function () {
        $('.captcha-image').prop('src', ls.routerUrl('captcha') + '?n=' + Math.random());
    });
    // Регистрация
    $('.js-modal-auth-registration').click(function () {
        $('#modal-auth').modal();
        $('#modal-auth .js-tab-registration').tab('show');
        $('#modal-auth input[type="text"]').val('');
        $('#modal-auth input[type="password"]').val('');
        $('.form-control-feedback-ok').hide();
        $('.validate-error-show').removeClass('validate-error-show').addClass('validate-error-hide');

        return false;
    });


    /**
     * КОММЕНТАРИИ
     */

    $('.comment').each(function(){
       if ($(this).next('.comment-wrapper').length > 0) {
        $(this).find('.collapse-block').show();
       }
    });

        // Сворачиваем ветку
    $('.comment .collapse-block a').click(function () {
        var $t = $(this).parents('.comment').last().next('.comment-wrapper');
        if ($t.length > 0) {
            $(this).find('i').toggleClass('fa-minus-square-o').toggleClass('fa-plus-square-o');
            $t.slideToggle(0);
        }

    });
    // Сворачиваем все ветки
    ls.comments.toggleAll = function () {
        var $t = $('.comment-level-1');
        $t.next().slideToggle(0);
        $t.find('.collapse-block').find('i').toggleClass('fa-minus-square-o').toggleClass('fa-plus-square-o');
    };
    // Удалить/восстановить комментарий
    ls.comments.toggle = function (obj, commentId) {
        var url = ls.routerUrl('ajax') + 'comment/delete/';
        var params = {idComment: commentId};

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
                $(obj).html(result.sTextToggle);
                ls.hook.run('ls_comments_toggle_after', [obj, commentId, result]);
            }
        }.bind(this));
    };
    // Установим уровень нового комментария
    ls.hook.add('ls_comment_inject_after', function (arguments, newComment) {
        newComment = $('#comment_id_' + newComment);
        var levelParent = $('#comment_wrapper_id_' + arguments);
        if (levelParent.length > 0) {
            var level = levelParent.data('level');

            if (typeof level === 'undefined') {
                level = levelParent.find('> .comment').first().data('level');
            }

            if (typeof level !== 'undefined') {
                newComment
                    .attr('data-level', parseInt(level) + 1).removeClass('comment-level-1').addClass('comment-level-' + (parseInt(level) + 1))
                    .parent().attr('data-level', parseInt(level) + 1).removeClass('comment-level-1').addClass('wrapper-level-' + (parseInt(level) + 1))
            }
        }
    });
    // Установим теги топика
    ls.hook.add('ls_comment_inject_after', function (arguments, newComment) {

    });

    ls.comments = ls.comments || {};
    ls.comments.preview = function () {
        if (this.formCommentText() == '') return;
        $("#comment_preview_" + this.iCurrentShowFormComment).remove();
        this.options.replyForm.before('<div id="comment_preview_' + this.iCurrentShowFormComment + '" class="comment-preview text comment-level-' + $('#comment_id_' + this.iCurrentShowFormComment).data('level') + '"></div>');
        ls.tools.textPreview('#form_comment_text', false, 'comment_preview_' + this.iCurrentShowFormComment);
    };

    $('.js-search-link').click(function(){
        var type = $(this).data('search-type'), form = $('form.search');

        if (type && form.length) {
            form.prop('action', ls.routerUrl('search') + type).submit();
            return false;
        }
    });

    // Поиск по тегам
    $('.js-tag-search-form').submit(function () {
        window.location = ls.routerUrl('tag') + encodeURIComponent($(this).find('.js-tag-search').val()) + '/';
        return false;
    });


    // Автокомплит
    ls.autocomplete.add($(".autocomplete-tags-sep"), ls.routerUrl('ajax') + 'autocompleter/tag/', true);
    ls.autocomplete.add($(".autocomplete-tags"), ls.routerUrl('ajax') + 'autocompleter/tag/', false);
    ls.autocomplete.add($(".autocomplete-users-sep"), ls.routerUrl('ajax') + 'autocompleter/user/', true);
    ls.autocomplete.add($(".autocomplete-users"), ls.routerUrl('ajax') + 'autocompleter/user/', false);

    // Autofocus
    $('form').each(function () {
        $(this).find('.js-focus-in:visible').first().focus();
    });

    // Тул-бар топиков
    ls.toolbar.topic.init();
    // Кнопка "UP"
    ls.toolbar.up.init();

    $('.js-title-comment, .js-title-topic').tooltip({
        placement: 'left'
    });

    $('.js-tip-help').tooltip({
        placement: 'right'
    });

    prettyPrint();

    // Фикс бага с z-index у встроенных видео
    $("iframe").each(function () {
        var ifr_source = $(this).attr('src');

        if (ifr_source) {
            var wmode = "wmode=opaque";

            if (ifr_source.indexOf('?') != -1)
                $(this).attr('src', ifr_source + '&' + wmode);
            else
                $(this).attr('src', ifr_source + '?' + wmode);
        }
    });

    $('.js-modal-blog_delete').click(function () {
        ls.modal.show('#modal-blog_delete');
        return false;
    });


    ls.blog.toggleInfo = function () {
        $('#blog-more-content').slideToggle();
        var more = $('#blog-more');
        more.toggleClass('expanded');

        if (more.hasClass('expanded')) {
            more.html(ls.lang.get('fa_blog_fold_info'));
        } else {
            more.html(ls.lang.get('fa_blog_expand_info'));
        }

        return false;
    };

    ls.user.followToggleStar = function (button, iUserId) {
        button = $(button);
        if (button.hasClass('followed')) {
            ls.stream.unsubscribe(iUserId);
            button.toggleClass('followed').html('<i class="fa fa-star-half-full"></i>');
        } else {
            ls.stream.subscribe(iUserId);
            button.toggleClass('followed').html('<i class="fa fa-star"></i>');
        }
        return false;
    };

    $('.action-mail a, .action-user a, .action-favourite a').tooltip();


    ls.wall.loadReplyNext = function (iPid) {
        var divLast = $('#wall-reply-container-' + iPid).find('.js-wall-reply-item').first();
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

    ls.userstream = ( function ($) {
        this.isBusy = false;
        this.dateLast = null;

        this.getMoreByUser = function (iUserId) {
            if (this.isBusy) {
                return;
            }
            var lastId = $('#stream_last_id').val();
            if (!lastId) return;
            $('#stream_get_more').addClass('loading');
            this.isBusy = true;

            var url = aRouter['stream'] + 'get_more_user/';
            var params = {'iLastId': lastId, iUserId: iUserId, 'sDateLast': this.dateLast};

            ls.hook.marker('getMoreByUserBefore');
            ls.ajax(url, params, function (data) {
                if (!data.bStateError && data.events_count) {
                    $('#stream-list').append(data.result);
                    $('#stream_last_id').attr('value', data.iStreamLastId);
                }
                if (!data.events_count) {
                    $('#stream_get_more').hide();
                }
                $('#stream_get_more').removeClass('loading');
                ls.hook.run('ls_stream_get_more_by_user_after', [lastId, iUserId, data]);
                this.isBusy = false;
            }.bind(this));
        };

        return this;
    }).call(ls.stream || {}, jQuery);

    ls.talk.addToTalk = function (idTalk) {
        var sUsers = $('#talk_speaker_add').val();
        if (!sUsers) return false;
        $('#talk_speaker_add').val('');

        var url = ls.routerUrl('talk') + 'ajaxaddtalkuser/';
        var params = {users: sUsers, idTalk: idTalk};

        ls.ajax(url, params, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                $.each(result.aUsers, function (index, item) {
                    var list = $('#speaker_list');
                    if (list.length == 0) {
                        list = $('<ul class="list" id="speaker_list"></ul>');
                        $('#speaker_list_block').append(list);
                    }
                    var listItem = $('<li id="speaker_item_' + item.sUserId + '_area"><a href="' + item.sUserLink + '" class="user">' + item.sUserLogin + '</a> - <a href="#" id="speaker_item_' + item.sUserId + '" class="link link-lead link-red-blue delete"><i class="fa fa-times"></i></a></li>')
                    list.append(listItem);
                    ls.hook.run('ls_talk_add_to_talk_item_after', [idTalk, item], listItem);
                });

                ls.hook.run('ls_talk_add_to_talk_after', [idTalk, result]);
            }
        });
        return false;
    };

    ls.talk.init();


    ls.hook.add('ls_geo_load_regions_after', function ($country) {
        var $region = $country.parents('.js-geo-select').find('.js-geo-region');
        $region.parents('.form-group').show();
        $region.selecter("refresh");
    });
    ls.hook.add('ls_geo_load_cities_after', function ($region) {
        var $city = $region.parents('.js-geo-select').find('.js-geo-city');
        $city.parents('.form-group').show();
        $city.selecter('refresh');
    });

    ls.userfield.addFormField = function () {

        var item = this.fieldsConainer.find('.js-user-field-item:first').clone(),
            value = '';
        /**
         * Находим доступный тип контакта
         */
        item.find('select').find('option').each(function (k, v) {
            if (this.getCountFormField($(v).val()) < this.iCountMax) {
                value = $(v).val();
                return false;
            }
        }.bind(this));

        if (value) {
            $(this.fieldsConainer).append(item.show());
            item.find('select').val(value);
            var y = item.find('select');
            y.selecter();
            item.find('[type=text]').val('');
        } else {
            ls.msg.error('', ls.lang.get('settings_profile_field_error_max', {count: this.iCountMax}));
        }
        return false;
    };


    $('.modal .btn.btn-primary')
        .removeClass('btn-primary')
        .addClass('btn-blue')
        .addClass('btn-big')
        .addClass('corner-no');

    $('.modal .btn.btn-default')
        .removeClass('btn-default')
        .addClass('btn-light')
        .addClass('btn-big')
        .addClass('pull-left')
        .addClass('corner-no');

    $('.vote-popover').popover();


    $(window).scroll(function () {

        var htmlTop = $(window).scrollTop();

        if (htmlTop > 38) {
            $('body')
                .addClass('scrolled')
        } else {

            $('body')
                .removeClass('scrolled');
        }
    });

    ls.photoset.prepareLastImages = function($container) {
        var $imgs = $container.find('img').hide(),
            totalImgs	= $imgs.length,
            cnt			= 0;

        var cw = $container.data('width');
        if (cw) {
            $container
                .css({
                    width: '280px',
                    float: cw
                });
        } else {
            $container
                .css('width', $container.hasClass('page-photoset') ? '100%': '100.1%');
        }

        $container
            .find('a').removeAttr('rel').find('br').remove();


        $imgs.each(function() {
            var $img	= $(this),
                fixedHeight = ls.getConfig('photoset.gallery.fixedHeight') ? ls.getConfig('photoset.gallery.fixedHeight') : null,
                minHeight = ls.getConfig('photoset.gallery.minHeight') ? ls.getConfig('photoset.gallery.minHeight') : 60,
                maxHeight = ls.getConfig('photoset.gallery.maxHeight') ? ls.getConfig('photoset.gallery.maxHeight') : 120,
                alternateHeight = (!fixedHeight && minHeight < maxHeight),
                minWidth = ls.getConfig('photoset.gallery.minWidth') ? ls.getConfig('photoset.gallery.minWidth') : null;

            $('<img/>').load(function() {
                ++cnt;
                if( cnt === totalImgs ) {
                    $imgs.show();
                    $container.montage({
                        fillLastRow	: !!ls.getConfig('photoset.gallery.fillLastRow'),
                        alternateHeight	: alternateHeight,
                        alternateHeightRange : {
                            min	: minHeight,
                            max	: maxHeight
                        },
                        fixedHeight: fixedHeight,
                        minw: minWidth,
                        margin : parseInt(ls.getConfig('photoset.gallery.margin'))
                    });
                    $container.find('a').each(function(){
                        $(this).attr('rel', $(this).find('img').data('rel'))
                    });
                    $("a[rel^='prettyPhoto']").prettyPhoto({
                        social_tools:'',
                        show_title: true,
                        theme: 'pp_experience',
                        markup: pretty_markup,
                        slideshow: 5000,
                        deeplinking: false
                    });
                }
            }).attr('src',$img.attr('src'));
        });
    };

    ls.photoset.prepareLastImages($('.alto-photoset'));

    $('.topic-text > img, .comment-text > img').each(function(){

        var src = $(this).data('src') || $(this).attr('src');

        var a = $('<a href="'+src+'" rel="prettyPhoto[pp]" ></a>');
        a.insertAfter($(this));
        $(this).appendTo(a);
        a.attr('rel', "prettyPhoto[topic]").prettyPhoto({
            social_tools: '',
            theme: 'pp_experience',
            markup: pretty_markup,
            show_title: true,
            deeplinking: false
        })
    });

    ls.hook.add('ls_favourite_toggle_after', function(idTarget, objFavourite, type, params, result){
        if (params.type == 1) {
            $(objFavourite).find('i').removeClass('fa-star-o').addClass('fa fa-star');
        } else {
            $(objFavourite).find('i').removeClass('fa fa-star').addClass('fa fa-star-o');
        }
    });


    /**
     * Вступить или покинуть блог
     */
    ls.blog.toggleJoin = function (button, idBlog) {
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

                var text = result.bState
                        ? ls.lang.get('ex_blog_leave')
                        : ls.lang.get('ex_blog_join')
                    ;

                button.empty().html(text);
                button.toggleClass('active');

                if (!result.bState) {
                    button.addClass('link-blue').removeClass('link-dark');
                } else {
                    button.removeClass('link-blue').addClass('link-dark');
                }

                $('#blog_user_count_' + idBlog).text(result.iCountUser);
                ls.hook.run('ls_blog_toggle_join_after', [idBlog, result], button);
            }
        });
    };

    $('.spoiler-slider')
        .live('click', function(){
            $(this).parent().next().slideToggle();
        });

    $('.navbar-main>.container').altoCollapsedMenu({
        collapse: '.main.nav.nav-content',
        hidden: '.menu-hidden',
        widthCorrect: 60,
        other: [
            '.navbar-header',
            '.navbar-user'
        ]
    });

    $('#navbar-content').altoCollapsedMenu({
        collapse: '>ul',
        hidden: '.header-menu-hidden',
        widthCorrect: 20,
        other: [        ]
    });

});
