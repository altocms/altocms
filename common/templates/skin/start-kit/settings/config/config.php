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

$config['head']['default']['js'] = Config::Get('head.default.js');
$config['head']['default']['js'][] = '___path.skin.dir___/assets/js/template.js';

/* Bootstrap */
$config['head']['default']['js']['___path.frontend.dir___/bootstrap-3/js/bootstrap.min.js'] = array('name' => 'bootstrap');


$config['head']['default']['css'] = array(
    /* Bootstrap */
    '___path.frontend.dir___/bootstrap-3/css/bootstrap.min.css',

    /* Structure */
    '___path.skin.dir___/assets/css/base.css',
    '___path.frontend.dir___/libs/vendor/markitup/skins/default/style.css',
    '___path.frontend.dir___/libs/vendor/markitup/sets/default/style.css',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.css',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.css',
    '___path.frontend.dir___/libs/vendor/nprogress/nprogress.css',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.css',
    '___path.frontend.dir___/libs/vendor/prettyphoto/css/prettyphoto.css',
    '___path.skin.dir___/assets/css/smoothness/jquery-ui.css',
    '___path.skin.dir___/assets/css/responsive.css',
    '___path.skin.dir___/assets/css/default.css',

    /* Theme */
    '___path.skin.dir___/themes/___view.theme___/style.css',
    '___path.skin.dir___/themes/___view.theme___/style.css.map',
    /* Themer Icons */
    '___path.skin.dir___/assets/icons/css/fontello.css',

    /* tinyMCE */
    '___path.skin.dir___/assets/css/tinymce.css'       => array(
        'name'      => 'template-tinymce.css',
        'prepare'   => true,
        'merge'     => false,
    ),
);

return $config;

// EOF