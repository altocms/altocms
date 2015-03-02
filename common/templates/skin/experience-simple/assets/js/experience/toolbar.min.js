
$(function () {
    var $content = $('.content');

    var $toolbar = $('.toolbar');

    if ($toolbar.length == 0) {
        return;
    }

    $(window).resize(function () {
        $toolbar
            .css("left", $content.width() + $content.offset().left + 16)
            .css("top", $('.banner-menu-down').outerHeight);
    });

    $toolbar
        .css("left", $content.width() + $content.offset().left + 16)
        .css("top", $('.banner-menu-down').outerHeight);


});