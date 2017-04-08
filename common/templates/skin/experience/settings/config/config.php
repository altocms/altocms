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

// Использовать или нет плавающую шапку редактора
$config['view']['float_editor'] = true;

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

$config['view']['cfg']['set'] = array(
    // Настройки отображения фотосета
    'photoset' => array(
        'gallery' => array(
            'fillLastRow' => false, // заполнение последней строки изображений
            'minHeight' => Config::Get('module.topic.photoset.thumb.height') / 2,  // минимальная высота изображений
            'maxHeight' => Config::Get('module.topic.photoset.thumb.height'), // максимальная высота изображений
            //'fixedHeight' => Config::Get('module.topic.photoset.thumb.height'), //
            'fixedHeight' => null, //
            'minWidth' => 70, // Минимальная ширина, которая должна быть у изображения
            'margin' => 1, // отступ вокруг миниатюры
        ),
    ),
    // Вставка в топик изображений из внешних источников
    // настройки берутся из конфига шаблона
    'module' => array(
        'topic' => array(
            'img_panel' => Config::Get('module.topic.img_panel'),
        ),
    ),
);


$config['module']['user']['profile_photo_size'] = '222x';

$config['module']['uploader']['images']['default']['size'] = array(
    'large'  => '112x112crop', // Шапка профиля блога/пользователя
    'big'    => '64x64crop',   // Список пользователей list_avatar
    'medium' => '50x50crop',
    'small'  => '24x24crop',
    'mini'   => '16x16crop',
);

//$config['module']['uploader']['images']['profile_avatar']['size'] = $config['module']['uploader']['images']['default']['size'];
//$config['module']['uploader']['images']['blog_avatar']['size'] = $config['module']['uploader']['images']['default']['size'];

$config['module']['uploader']['images']['profile_photo']['size'] = array(
    'default'  => '222x222crop',
    'popover'  => '136x136pad',
);

return $config;