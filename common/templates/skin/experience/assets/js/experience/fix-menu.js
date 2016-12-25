$(function(){
    "use strict";
    setFixMenu('.menu-level-2-container', 'fixed', '.menu-level-1-container');

    function setFixMenu(element, stickyCssClass, heightElement) {
        var menuTop = $(element), height = $(heightElement).height();

        if (menuTop.length == 0) {
            return;
        }

        menuTop = menuTop.offset().top + height;

        $(window).scroll(function () {
            var htmlTop = $(window).scrollTop();

            if (htmlTop > menuTop) {
                $(element)
                    .addClass(stickyCssClass)
            } else {

                $(element).removeClass(stickyCssClass);
            }
        });
    }

});