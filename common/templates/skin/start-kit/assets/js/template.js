;jQuery(document).ready(function ($) {
    var body = $('body');
    // Хук начала инициализации javascript-составляющих шаблона
    ls.hook.run('ls_template_init_start', [], window);

    $('html').removeClass('no-js');

    // Определение браузера
    if ($.browser.opera) {
        body.addClass('opera opera' + parseInt($.browser.version));
    }
    if ($.browser.mozilla) {
        body.addClass('mozilla mozilla' + parseInt($.browser.version));
    }
    if ($.browser.webkit) {
        body.addClass('webkit webkit' + parseInt($.browser.version));
    }
    if ($.browser.msie) {
        body.addClass('ie');
        if (parseInt($.browser.version) > 8) {
            body.addClass('ie' + parseInt($.browser.version));
        }
    }

    // Всплывающие окна
    $('.js-modal-auth-login').click(function () {
        $('#modal-auth').modal();
        var tab = $('#modal-auth .js-tab-login').tab('show');
        return false;
    });

    $('.js-modal-auth-registration').click(function () {
        $('#modal-auth').modal();
        $('#modal-auth .js-tab-registration').tab('show');
        $('.captcha-image').prop('src', ls.routerUrl('captcha') + '?n=' + Math.random());
        return false;
    });

    $('.js-modal-blog_delete').click(function () {
        ls.modal.show('#modal-blog_delete');
        return false;
    });

    $('[id^=modal-].modal').on('shown.bs.modal', function(e){
        $(this).find('.js-focus-in:visible').first().focus();
    });

    /* Special toggles */
    $('[data-toggle=file][data-target]').each(function () {
        var target = $($(this).data('target')).first();
        if (target.length && target.is('input') && target.attr('type') == 'file') {
            target.css({opacity: 0, position: 'absolute', width: 0});
        }
        $(this).click(function () {
            var target = $($(this).data('target')).first();
            if (target.length && target.is('input') && target.attr('type') == 'file') {
                target.click();
            }
            return false;
        });
    });

    /* Datepicker */
    /**
     * TODO: навесить языки на datepicker
     */
    $('.date-picker').datepicker({
        dateFormat: 'dd.mm.yy',
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        firstDay: 1
    });

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
    ls.autocomplete.add($(".autocomplete-tags-sep,.js-autocomplete-tags-sep"), ls.routerUrl('ajax') + 'autocompleter/tag/', true);
    ls.autocomplete.add($(".autocomplete-tags,.js-autocomplete-tags"), ls.routerUrl('ajax') + 'autocompleter/tag/', false);
    ls.autocomplete.add($(".autocomplete-users-sep,.js-autocomplete-users-sep"), ls.routerUrl('ajax') + 'autocompleter/user/', true);
    ls.autocomplete.add($(".autocomplete-users,.js-autocomplete-users"), ls.routerUrl('ajax') + 'autocompleter/user/', false);

    // Autofocus
    $('form').each(function(){
        $(this).find('.js-focus-in:visible').first().focus();
    });

    // Stylization of [type=file]
    body.on('change', '.btn-file input[type=file]', function(){
        var input = $(this),
            value = input.val(), // get value
            pos = value.lastIndexOf('/') > value.lastIndexOf('\\') ? value.lastIndexOf('/') : value.lastIndexOf('\\'),
            fileName = value.substring(pos + 1); // get file name
        // remove existing file info
        input.next().remove();
        // append file info
        $('<span>: ' + fileName + '</span>').insertAfter(input);
    });

    // Тул-бар топиков
    ls.toolbar.topic.init();
    // Кнопка "UP"
    ls.toolbar.up.init();

    body.tooltip({
        selector: '.js-title-comment, .js-title-topic',
        placement: 'left'
    });

    $('.js-tip-help').tooltip({
        placement: 'right'
    });

    /*
    // Всплывающие сообщения
    if (ls.registry.get('block_stream_show_tip')) {
        $('.js-title-comment, .js-title-topic').poshytip({
            className: 'infobox-yellow',
            alignTo: 'target',
            alignX: 'left',
            alignY: 'center',
            offsetX: 10,
            liveEvents: true,
            showTimeout: 1000
        });
    }

    $('.js-title-talk').poshytip({
        className: 'infobox-yellow',
        alignTo: 'target',
        alignX: 'left',
        alignY: 'center',
        offsetX: 10,
        liveEvents: true,
        showTimeout: 500
    });

    $('.js-infobox-vote-topic').poshytip({
        content: function () {
            var id = $(this).attr('id').replace('vote_total_topic_', 'vote-info-topic-');
            return $('#' + id).html();
        },
        className: 'infobox-standart',
        alignTo: 'target',
        alignX: 'center',
        alignY: 'top',
        offsetX: 2,
        liveEvents: true,
        showTimeout: 100
    });

    $('.js-tip-help').poshytip({
        className: 'infobox-standart',
        alignTo: 'target',
        alignX: 'right',
        alignY: 'center',
        offsetX: 5,
        liveEvents: true,
        showTimeout: 500
    });

    $('.js-infobox').poshytip({
        className: 'infobox-standart',
        liveEvents: true,
        showTimeout: 300
    });
    */

    // подсветка кода
    prettyPrint();

    var inputs = $('input.input-text, textarea');
    // эмуляция border-sizing в IE 7-
    // ls.ie.bordersizing(inputs);
    // эмуляция placeholder'ов в IE
    inputs.placeholder();

    // комментарии
    //ls.comments.options.folding = false;
    //ls.comments.init();

    // избранное
    ls.hook.add('ls_favourite_toggle_after', function (idTarget, objFavourite, type, params, result) {
        $('#fav_count_' + type + '_' + idTarget).text((result.iCount > 0) ? result.iCount : '');
    });

    // лента активности
    //ls.hook.add('ls_stream_append_user_after', function (length, data) {
    //    if (length == 0) {
    //        $('#strm_u_' + data.uid).parent().find('a').before('<a href="' + data.user_web_path + '"><img src="' + data.user_avatar_48 + '" alt="avatar" class="avatar" /></a> ');
    //    }
    //});

    // опрос
    ls.hook.add('ls_pool_add_answer_after', function (removeAnchor) {
        var removeAnchor = $('<a href="#" class="glyphicon glyphicon-trash icon-remove" />').attr('title', ls.lang.get('delete')).click(function (e) {
            e.preventDefault();
            return this.removeAnswer(e.target);
        }.bind(ls.poll));
        $(this).find('a').remove();
        $(this).append(removeAnchor);
    });

    /****************
     * TALK
     */

    ls.talk.init();


    // Help-tags link
    $('.js-tags-help-link').click(function () {
        var target = ls.registry.get('tags-help-target-id'),
            text='';
        if (!target || !$('#' + target).length) {
            return false;
        }
        target = $('#' + target);
        if ($(this).data('insert')) {
            text = $(this).data('insert');
        } else {
            text = $(this).text();
        }
        $.markItUp({target: target, replaceWith: text});
        return false;
    });


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


    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
        var msViewportStyle = document.createElement("style");
        msViewportStyle.appendChild(
            document.createTextNode(
                "@-ms-viewport{width:auto!important}"
            )
        );
        document.getElementsByTagName("head")[0].appendChild(msViewportStyle)
    }

    if (tinyMCE && ls.settings && ls.settings.presets.tinymce) {
        var cssUrl = ls.getAssetUrl('template-tinymce.css');
        if (cssUrl) {
            ls.settings.presets.tinymce.default['content_css'] = cssUrl;
        }
    }


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
                        slideshow:5000,
                        deeplinking: false
                    });
                }
            }).attr('src',$img.attr('src'));
        });
    };

    $('.spoiler-slider')
        .live('click', function(){
            $(this).parent().next().slideToggle();
        });

    ls.photoset.prepareLastImages($('.alto-photoset'));

    // Хук конца инициализации javascript-составляющих шаблона
    ls.hook.run('ls_template_init_end', [], window);
});
