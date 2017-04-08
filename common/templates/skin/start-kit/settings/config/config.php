<?php

$config['smarty']['dir']['templates'] = array(
    'themes' => '___path.skins.dir___/___view.skin___/themes/',
    'tpls'   => '___path.skins.dir___/___view.skin___/tpls/',
);

/* Theme */
//$config['view']['theme'] = 'default';
$config['view']['theme'] = 'light';
//$config['view']['theme'] = 'green';
//$config['view']['theme'] = 'red';

/* Top bar fixed or static */
//$config['view']['header']['top'] = 'static'; // static or fixed
$config['view']['header']['top'] = 'fixed'; // static or fixed


// Использовать или нет плавающую шапку редактора
$config['view']['float_editor'] = true;

/* Banner under top bar - turn on/off */
$config['view']['header']['banner'] = true;

// Relative path from skin dir OR absolute path on disk OR URL
$config['view']['header']['logo'] = Config::Get('path.skin.url') . 'themes/___view.theme___/img/favicon.png';
$config['view']['header']['name'] = 'START<span>KIT</span>';

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

// Aliases for sizes of user avatar
$config['module']['uploader']['images']['default']['size'] = array(
    'large'  => 100,
    'big'    => 64,
    'medium' => 48,
    'small'  => 36,
    'mini'   => 24,
    'photo'  => '250x250crop',
);

//$config['module']['uploader']['images']['profile_avatar']['size'] = $config['module']['uploader']['images']['default']['size'];
//$config['module']['uploader']['images']['blog_avatar']['size'] = $config['module']['uploader']['images']['default']['size'];

$config['module']['uploader']['images']['profile_photo']['size'] = array(
    'default'  => '250x250crop',
);

return $config;

// EOF