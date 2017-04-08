/*!
 * Тема оформления Experience v.1.0  для Alto CMS
 * @licence     CC Attribution-ShareAlike
 */


$(function () {

    //setFixMenu('.menu-level-2-container', 'fixed');

    var $selects = $("select");

    if ($selects.length > 0) {
        $selects.not('.hidden-select').selecter()
    }

    var $checks = $('input:not(.js-no-jq)');

    if ($checks.length > 0) {
        $checks.iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });
    }


    function setFixMenu(element, stickyCssClass) {

        var menuTop = $(element);

        if (menuTop.length == 0) {
            return;
        }

        menuTop = menuTop.offset().top + 110;

        $(window).scroll(function () {

            var htmlTop = $(window).scrollTop();

            if (htmlTop > menuTop) {
                $(element).add('body')
                    .addClass(stickyCssClass)
            } else {

                $(element).add('body').removeClass(stickyCssClass);
            }
        });
    }

    $('.toolbar-stream > a')
        .data('content', $('#hidden-stream'))
        .popover({
            html:      true,
            top:       true,
            toggle:    'manual',
            placement: 'left',
            container: '.toolbar.toolbar-container .toolbar-stream'
        }).on('show.bs.popover', function () {
            $('.toolbar-button > a').popover('hide');
        }).on('shown.bs.popover', function () {
            $('.popover-content .js-widget-stream-content').html($('#hidden-stream .js-widget-stream-content').html());
            $('.widget-type-stream').css('height', 'auto');
        });

    $('.toolbar-user > a')
        .data('content', $('#hidden-toolbar-user-content').html())
        .popover({
            html:      true,
            top:       true,
            toggle:    'manual',
            placement: 'left',
            container: '.toolbar.toolbar-container .toolbar-user'
        }).on('show.bs.popover', function () {
            $('.toolbar-stream > a').popover('hide');
            $('.toolbar-button:not(.toolbar-user) > a').popover('hide');
        });


    $('.toolbar-write > a')
        .data('content', $('#hidden-toolbar-write-content').html())
        .popover({
            html:      true,
            top:       true,
            toggle:    'manual',
            placement: 'left',
            container: '.toolbar.toolbar-container .toolbar-write'
        }).on('show.bs.popover', function () {
            $('.toolbar-stream > a').popover('hide');
            $('.toolbar-button:not(.toolbar-write) > a').popover('hide');
            $(this).css({top: $(this).parent().offset().top + 53});
        });

    $('.topic .topic-share').slideToggle();

});
