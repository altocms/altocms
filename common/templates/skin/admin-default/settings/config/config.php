<?php
$config['view']['theme'] = 'default';

$config['smarty']['dir']['templates'] = array(
    'themes' => '___path.skins.dir___/___view.skin___/themes/',
    'tpls'   => '___path.skins.dir___/___view.skin___/tpls/',
);

$config['head']['default']['css'] = array(
    '___path.skin.url___/assets/css/jquery-ui.css',
    '___path.skin.url___/assets/css/bootstrap.min.css',
    '___path.skin.url___/assets/css/font-awesome.min.css',
    '___path.skin.url___/assets/css/fonts/ionicons/css/ionicons.min.css',
    '___path.skin.url___/assets/css/bootstrap3-wysihtml5.min.css',

    '___path.skin.url___/assets/css/formstyler.css',
    '___path.skin.url___/assets/css/main.css',

);

$config['head']['default']['js'] = array(
    '___path.skin.url___/assets/js/jquery-1.10.2.js',
    '___path.skin.url___/assets/js/jquery-ui.js',

    '___path.skin.url___/assets/js/bootstrap.min.js',

    '___path.skin.url___/assets/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
    '___path.skin.url___/assets/js/plugins/formstyler/formstyler.js',

    '___path.skin.url___/assets/js/menu-left/jquery.cookie.js',
    '___path.skin.url___/assets/js/menu-left/jquery.hoverIntent.minified.js',
    '___path.skin.url___/assets/js/menu-left/jquery.dcjqaccordion.2.6.min.js',

    '___path.frontend.dir___/libs/js/engine/admin-userfield.js',
    '___path.skin.url___/assets/js/main.js',
    '___path.skin.dir___/assets/js/admin.js',
    );

$config['footer']['default']['js'] = array();

$config['path']['skin']['img']['dir'] = '___path.skin.dir___/assets/img/'; // папка с изображениями скина
$config['path']['skin']['img']['url'] = '___path.skin.url___/assets/img/'; // URL с изображениями скина

//$config['compress']['css']['merge'] = false; // указывает на необходимость слияния файлов по указанным блокам.
//$config['compress']['css']['use'] = false; // указывает на необходимость компрессии файлов. Компрессия используется только в активированном

return $config;

// EOF