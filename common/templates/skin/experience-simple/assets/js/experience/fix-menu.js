$(function(){

    //setFixMenu('.navbar-main', 'fixed');

    function setFixMenu(element, stickyCssClass) {

        var menuTop = $(element);

        if (menuTop.length == 0) {
            return;
        }

        menuTop = menuTop.offset().top + 110;

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