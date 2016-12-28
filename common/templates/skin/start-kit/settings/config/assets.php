<?php

$config['assets']['default']['js'] = Config::Get('assets.default.js');
$config['assets']['default']['js'][] = '___path.skin.dir___/assets/js/template.js';

/* Bootstrap */
$config['assets']['default']['js']['___path.frontend.dir___/bootstrap-3/js/bootstrap.min.js'] = array('name' => 'bootstrap');


$config['assets']['default']['css'] = array(
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
    '___path.skin.dir___/themes/___view.theme___/css/theme-style.css',
    //'___path.skin.dir___/themes/___view.theme___/style.css.map',
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