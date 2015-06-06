/*!
 * menu.js
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 * @version     0.0.1 от 10.05.2014 19:54
 */

/*!
 * jQuery.flexMenu 1.1
 * https://github.com/352Media/flexMenu
 * Description: If a list is too long for all items to fit on one line, display a popup menu instead.
 * Dependencies: jQuery, Modernizr (optional). Without Modernizr, the menu can only be shown on click (not hover).
 **/

(function ($) {
    var flexObjects = [],   // Массив меню
        resizeTimeout;      // Тайм-аут обновления меню при ресайзе окна

    // Обновление меню при ресайзе окна
    function adjustFlexMenu() {
        $(flexObjects).each(function () {
            $(this).flexMenu({
                'undo': true
            }).flexMenu(this.options);
        });
    }

    // Перехватываем ресайз окна
    $(window).resize(function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            adjustFlexMenu();
        }, 200);
    });

    // Сам алгоритм перерисовки меню
    $.fn.flexMenu = function (options) {
        var s = $.extend({
            'threshold': 1,
            'undo':      false
        }, options);

        var checkFlexObject = $.inArray(this, flexObjects); // Checks if this object is already in the flexObjects array
        if (checkFlexObject >= 0) {
            flexObjects.splice(checkFlexObject, 1); // Remove this object if found
        } else {
            flexObjects.push(this); // Add this object to the flexObjects array
        }

        return this.each(function () {
            var $this = $(this),
                $lastChild,
                $popup = $('.menu-hidden'),
                $firstItem = $this.find('>li:first-child'),
                $lastItem = $this.find('>li:last-child'),
                numItems = $this.find('>li').length,
                firstItemTop = Math.floor($firstItem.offset().top),
                firstItemHeight = Math.floor($firstItem.outerHeight(true));

            function needsMenu($itemOfInterest) {
                return (Math.ceil($itemOfInterest.offset().top) >= (firstItemTop + firstItemHeight));
            }

            // Перенумеруем все правые элементы
            if (!$firstItem.data('place')) {
                var iterator = 0;
                $this.find('>li').each(function () {
                    $(this).data('place', iterator++);
                });
            }

            if (needsMenu($lastItem) && numItems > s.threshold) {
                // Нужно прятать меню

                $popup.parent().hide();

                // Блок спрятанных элементов
                var i;

                // Переберем скоытые элементы и добавим их под кнопку
                for (i = numItems - 1; i > 1; i--) {
                    $lastChild = $this.find('>li:last-child');

                    if (needsMenu($lastChild)) {

                        if ($lastChild.hasClass('right')) {
                            $lastChild.appendTo($popup);
                        } else {
                            $lastChild.prependTo($popup);
                        }

                        $lastChild.removeClass($lastChild.data('hidden-class'));
                        $popup.parent().show();
                    }

                }

                if ($popup.find('li').length > 0) {
                    $popup.dropdown();
                    $popup.find('>.dropdown').dropdown();
                    $popup.parent().show();
                    // Расставим элементы по своим местам
                    $popup.find('>li').each(function () {
                        $(this).insertAfter($popup.find('>li:eq(' + $(this).data('place') + ')'));
                    });
                }

            } else if (s.undo) {
                var numToRemove = $popup.find('>li').length;

                $popup.parent().hide();

                for (i = 1; i <= numToRemove; i++) {
                    $popup.find('>li:first-child').appendTo($this).each(function () {
                        $(this).addClass($(this).data('hidden-class'));
                    });
                }

                // Расставим элементы по своим местам
                $this.find('>li').each(function () {
                    $(this).insertAfter($this.find('>li:eq(' + $(this).data('place') + ')'));
                });


            }
        });
    };
})(jQuery);