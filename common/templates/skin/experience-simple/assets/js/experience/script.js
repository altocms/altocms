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
            $('.dropdown-toggle').dropdown('toggle');
        }).on('shown.bs.popover', function () {
            $('.popover-content .js-widget-stream-content').html($('#hidden-stream .js-widget-stream-content').html());
            $('.widget-type-stream').css('height', 'auto');
            $(this).addClass('active');
        }).on('hidden.bs.popover', function(){
            $(this).removeClass('active');
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
            $(this).addClass('active');
            $('.dropdown-toggle').dropdown('toggle');
        }).on('hidden.bs.popover', function(){
            $(this).removeClass('active');
        });

    $('.toolbar-search > a')
        .data('content', $('#hidden-toolbar-search-content').html())
        .popover({
            html:      true,
            top:       true,
            toggle:    'manual',
            placement: 'left',
            container: '.toolbar.toolbar-container .toolbar-search'
        }).on('show.bs.popover', function () {
            $('.toolbar-stream > a').popover('hide');
            $('.toolbar-button:not(.toolbar-search) > a').popover('hide');
            $(this).addClass('active');
            $('.dropdown-toggle').dropdown('toggle');
        }).on('hidden.bs.popover', function(){
            $(this).removeClass('active');
        }).on('shown.bs.popover', function(){
            $(this).next().find('input').focus();
        });

    $('.dropdown-toggle').on('click', function(){
        $('.toolbar-button > a').popover('hide');
    })

    $('.topic .topic-share').slideToggle();

    $('.modal-header button').html('&nbsp;');

});
