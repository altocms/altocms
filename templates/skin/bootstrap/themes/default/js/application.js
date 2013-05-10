(function ($) {

    $(function () {

        // fix sub nav on scroll
        var $win = $(window),
            $nav = $('#navmain'),
            navHeight = $('#navtop').first().height(),
            navTop = $('#navmain').length && $('#navmain').offset().top - navHeight,
            isFixed = 0;

        processScroll();

        $win.on('scroll', processScroll);

        function processScroll() {
            var i, scrollTop = $win.scrollTop();
            if (scrollTop >= navTop && !isFixed) {
                isFixed = 1;
                $nav.addClass('subnav-fixed');
                $('#navmain-fantom').show();
                //$nav.removeClass('navbar-inverse');
            } else if (scrollTop <= navTop && isFixed) {
                isFixed = 0;
                $nav.removeClass('subnav-fixed');
                $('#navmain-fantom').hide();
                //$nav.addClass('navbar-inverse');
            }
        }

    });

})(window.jQuery);