<?php
/**
 * Тема оформления Experience v.1.0  для Alto CMS
 * @licence     CC Attribution-ShareAlike
 */

/**
 * config.php
 * Файл конфигурационных параметров темы оформления Experience
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью темы оформления Experience
 * @version     0.0.1 от 29.05.2014 1:20
 */

/**
 * Цветовая схема оформления
 *      - 'default' - дефолтная, в серых тонах
 */
$aConfig['view']['theme'] = 'default';

/**
 * Использовать ли плавующее меню
 *      - false (не использовать)
 *      - true (использовать)
 */
$aConfig['view']['fix_menu'] = true;


/**
 * Показывать рейтинг топика всем
 *      - false (рейтинг видят только прогосовавшие - первый вариант);
 *      - true(рейтинг видят все - второй вариант)
 */
$aConfig['view']['show_rating'] = false;


$aConfig['head']['default']['js'] = Config::Get('head.default.js');
$aConfig['head']['default']['js']["___path.skin.dir___/assets/js/experience/dropdown.min.js"] = array('name' => 'bootstrap');
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/menu.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/menu-l2.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/script.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/toolbar.min.js";
if ($aConfig['view']['fix_menu']) {
    $aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/fix-menu.min.js";
}

$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/userinfo.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/experience/ch-datepicker.min.js";
$aConfig['head']['default']['js'][] = "___path.frontend.dir___/bootstrap-3/js/transition.min.js";
$aConfig['head']['default']['js'][] = "___path.frontend.dir___/bootstrap-3/js/tab.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/bootstrap/tooltip.min.js";
$aConfig['head']['default']['js'][] = "___path.frontend.dir___/bootstrap-3/js/popover.min.js";
$aConfig['head']['default']['js'][] = "___path.frontend.dir___/bootstrap-3/js/carousel.min.js";
$aConfig['head']['default']['js'][] = "___path.frontend.dir___/bootstrap-3/js/collapse.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/bootstrap/modal.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/icheck/icheck.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/selecter/jquery.fs.selecter.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/moment/moment.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/moment/moment.lang.ru.min.js";
$aConfig['head']['default']['js'][] = "___path.skin.dir___/assets/js/jasny/fileinput.min.js";

$aConfig['module']['user']['profile_photo_size'] = '222x';

$aConfig['head']['default']['css'] = array(
    '___path.skin.dir___/assets/css/style.min.css',
    '___path.frontend.dir___/libs/vendor/prettyphoto/css/prettyphoto.css',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.css',
);

// Пункты меню второго уровня
$aConfig['view']['header']['menu'] = array(
    'items' => array(
        'index' => array(
            'lang' => 'topic_title',
            'url' => Config::Get('path.root.url'),
            'icon_class' => 'fa fa-file-text-o',
        ),
        'blogs' => array(
            'lang' => 'blogs',
            'url' => Router::GetPath('blogs'),
            'icon_class' => 'fa fa-comment-o',
        ),
        'people' => array(
            'lang' => 'people',
            'url' => Router::GetPath('people'),
            'icon_class' => 'fa fa-circle-o',
        ),
        'stream' => array(
            'lang' => 'stream_menu',
            'url' => Router::GetPath('stream'),
            'icon_class' => 'fa fa-signal',
        ),
    ),
);

// Пункты меню третьего уровня
$aConfig['view']['header']['blogs'] = array(
    'items' => array(
        'blog_1' => array(
            'text' => 'Дизайн',
            'url' => Config::Get('path.root.url'),
        ),
        'blog_2' => array(
            'text' => 'Техника',
            'url' => Config::Get('path.root.url'),
        ),
        'blog_3' => array(
            'text' => 'Смартфоны',
            'url' => Config::Get('path.root.url'),
        ),
        'blog_4' => array(
            'text' => 'Приложения',
            'url' => Config::Get('path.root.url'),
        ),
        'blog_5' => array(
            'text' => 'Спорт',
            'url' => Config::Get('path.root.url'),
        ),
        'blog_6' => array(
            'text' => 'Новости',
            'url' => Config::Get('path.root.url'),
        ),
        'blog_7' => array(
            'text' => 'Технологии',
            'url' => Config::Get('path.root.url'),
        ),
    ),
);

return $aConfig;