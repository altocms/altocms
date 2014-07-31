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
$config['view']['theme'] = 'default';

/**
 * Использовать ли плавующее меню
 *      - false (не использовать)
 *      - true (использовать)
 */
$config['view']['fix_menu'] = true;


/**
 * Показывать рейтинг топика всем
 *      - false (рейтинг видят только прогосовавшие - первый вариант);
 *      - true(рейтинг видят все - второй вариант)
 */
$config['view']['show_rating'] = false;

/*
 * Настройка логотипа, который будет показываться в "шапке" сайта
 *
 * Если указан $config['view']['header']['logo']['file'], то в качестве логотипа берется файл с указанным именем
 * из папки /common/templates/skin/experience/assets/images/
 *
 * Если указан $config['view']['header']['logo']['url'], то в качестве лого подставляется изображение по указанному URL
 *
 * Если ни один из этих параметров не указан (закомментирован), то графический логотип не выводится
 *
 * Если указан $config['view']['header']['logo']['name'], то на месте лого выводится заданный текст
 * (если задан графический логотип, то текстовый будет выводиться после него). Можно указать любой текст в кавычках,
 * например, так:
 *    $config['view']['header']['logo']['name'] = 'Это мой сайт';
 */
$config['view']['header']['logo']['file'] = 'logo.png';
//$config['view']['header']['logo']['url'] = 'http://site.com/logo.png';
$config['view']['header']['logo']['name'] = Config::Get('view.name');


// Пункты меню второго уровня
$config['view']['menu']['main'] = array(
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
$config['view']['menu']['blogs'] = array(
    'config' => array(
        //'fill_from' => 'blogs',
        //'fill_from' => array('blogs' => array('dev', 'special', 'trips', 'albums')),
        //'fill_from' => 'categories',
        //'fill_from' => array('blogs' => array('trips', 'albums'), 'categories' => array('events', 'news'), 'list' => array('blog_1', 'blog_2')),
        'limit' => 7,
    ),
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

$config['module']['user']['profile_photo_size'] = '222x';

// Настройка css- и js-наборов
require 'assets.php';

return $config;