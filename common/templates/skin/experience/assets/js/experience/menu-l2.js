/*!
 * Тема оформления Experience v.1.0  для Alto CMS
 * @licence     CC Attribution-ShareAlike
 */

/*!
 * menu.js
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 * @version     0.0.1 от 10.05.2014 19:54
 */
;
/*!
 * jQuery.flexMenu 1.1
 * https://github.com/352Media/flexMenu
 * Description: If a list is too long for all items to fit on one line, display a popup menu instead.
 * Dependencies: jQuery, Modernizr (optional). Without Modernizr, the menu can only be shown on click (not hover).
 **/
(function ($) {
    var flexObjectsL2 = [],   // Массив меню
        resizeTimeout;      // Тайм-аут обновления меню при ресайзе окна

    // Обновление меню при ресайзе окна
    function adjustFlexMenuL2() {
        $(flexObjectsL2).each(function () {
            $(this).flexMenuL2({
                'undo': true
            }).flexMenuL2(this.options);
        });
    }

    // Перехватываем ресайз окна
    $(window).resize(function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            adjustFlexMenuL2();
        }, 200);
    });

    // Сам алгоритм перерисовки меню
    $.fn.flexMenuL2 = function (options) {
        var s = $.extend({
            'threshold': 1,
            'undo':      false
        }, options);

        var checkFlexObject = $.inArray(this, flexObjectsL2); // Checks if this object is already in the flexObjects array
        if (checkFlexObject >= 0) {
            flexObjectsL2.splice(checkFlexObject, 1); // Remove this object if found
        } else {
            flexObjectsL2.push(this); // Add this object to the flexObjects array
        }

        return this.each(function () {
            var $this = $(this),
                $firstItem = $this.find('>li:first-child'),
                $lastItem = $this.find('>li:last-child'),
                numItems = $this.find('>li').length,
                firstItemTop = Math.floor($firstItem.offset().top),
                firstItemHeight = Math.floor($firstItem.outerHeight(true));

            function needsMenu($itemOfInterest) {
                var t = (Math.ceil($itemOfInterest.offset().top + 5) >= (firstItemTop + firstItemHeight));

                return t;
            }

            if (needsMenu($lastItem) && numItems > s.threshold && $('body').width() > 620) {

                $this.addClass('mobile');

//                $this.find('.search').prependTo($this);
//                $this.find('>li').not('.menu-level-2-logo').appendTo($('.menu-level-2-hidden')).show();
//                $this.find('.menu-level-2-logo').removeAttr('style');

            } else if (s.undo) {

                $this.removeClass('mobile');

                $this.find('form').appendTo($this.find('.search'));

                var o = $('.menu-level-2-hidden');
                o.find('>li').appendTo($this);
                $this.find('.search').appendTo($this);
                $firstItem.removeAttr('style');
                o.hide();

            } else if ($('body').width() <= 620) {
                $this.addClass('mobile');

                $this.find('.search').prependTo($this);
                $this.find('>li').not('.menu-level-2-logo').appendTo($('.menu-level-2-hidden')).show();
                $this.find('.menu-level-2-logo').removeAttr('style');

            }
        });
    };
})(jQuery);

$(function(){
    $('.menu-level-2 .bars').click(function(){
        $('.menu-level-2-hidden').slideToggle(200);
    })
});