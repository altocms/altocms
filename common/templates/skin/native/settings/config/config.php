<?php

$config = array();

$config['view']['wysiwyg'] = true;
$config['view']['theme'] = 'default';
$config['module']['user']['profile_photo_width'] = 300;

$config['path']['smarty']['template'] = array(
    'themes'  => '___path.skins.dir___/___view.skin___/themes/',
    'layouts' => '___path.skins.dir___/___view.skin___/themes/default/layouts/',
    'tpls'    => '___path.skins.dir___/___view.skin___/tpls/',
);

/**
 * Grid type:
 *
 * fluid - резина
 * fixed - фиксированная ширина
 */
$config['view']['grid']['type'] = 'fixed';

/* Fluid settings */
$config['view']['grid']['fluid_min_width'] = 900;
$config['view']['grid']['fluid_max_width'] = 1200;

/* Fixed settings */
$config['view']['grid']['fixed_width'] = 1000;

/*
$config['head']['default']['js'] = Config::Get('head.default.js');
$config['head']['default']['js']['___path.frontend.dir___/bootstrap-3.0.0/js/bootstrap.min.js'] = array('name' => 'bootstrap');
$config['head']['default']['js'][] = '___path.skin.dir___/assets/js/init.js';
*/
$config['head']['default']['js'] = array(
    /* Vendor libs */
    '___path.frontend.url___/libs/js/vendor/html5shiv.min.js' => array('browser' => 'lt IE 9'),
    '___path.frontend.url___/libs/js/vendor/jquery-1.9.1.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    //'___path.frontend.url___/libs/js/vendor/jquery-migrate-1.2.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.url___/libs/js/vendor/jquery-migrate-1.2.1.js' => array('asset' => 'mini'),
    '___path.frontend.url___/libs/js/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('asset' => 'mini'),
    '___path.frontend.url___/libs/js/vendor/jquery-ui/js/localization/jquery-ui-datepicker-ru.js',
    '___path.frontend.url___/libs/js/vendor/markitup/jquery.markitup.js' => array('name' => 'markitup'),
    '___path.frontend.url___/libs/js/vendor/tinymce_4.0.5/tinymce.min.js' => array('name' => 'tinymce', 'asset' => 'mini'),
    '___path.frontend.url___/libs/js/vendor/jquery.browser.js',
    '___path.frontend.url___/libs/js/vendor/jquery.scrollto.js',
    '___path.frontend.url___/libs/js/vendor/jquery.rich-array.min.js',
    '___path.frontend.url___/libs/js/vendor/jquery.form.js',
    '___path.frontend.url___/libs/js/vendor/jquery.jqplugin.js',
    '___path.frontend.url___/libs/js/vendor/jquery.cookie.js',
    '___path.frontend.url___/libs/js/vendor/jquery.serializejson.js',
    '___path.frontend.url___/libs/js/vendor/jquery.file.js',
    '___path.frontend.url___/libs/js/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.url___/libs/js/vendor/jquery.placeholder.min.js',
    '___path.frontend.url___/libs/js/vendor/jquery.charcount.js',
    '___path.frontend.url___/libs/js/vendor/jquery.imagesloaded.js',
    '___path.frontend.url___/libs/js/vendor/notifier/jquery.notifier.js',
    '___path.frontend.url___/libs/js/vendor/prettify/prettify.js',
    '___path.frontend.url___/libs/js/vendor/prettyphoto/js/jquery.prettyphoto.js',
    '___path.frontend.url___/libs/js/vendor/parsley/parsley.js',
    '___path.frontend.url___/libs/js/vendor/parsley/i18n/messages.ru.js',
    '___path.frontend.url___/libs/js/vendor/swfobject/swfobject.js',

    /* */
    '___path.frontend.dir___/libs/js/vendor/swfobject/plugin/swfupload.js' => array(
        'name'    => 'swfobject/plugin/swfupload.js',
        'prepare' => true
    ),
    '___path.frontend.dir___/libs/js/vendor/swfupload/swfupload.js'        => array(
        'name'    => 'swfupload/swfupload.js',
        'prepare' => true
    ),
    '___path.frontend.dir___/libs/js/vendor/swfupload/swfupload.swf'       => array(
        'name'     => 'swfupload/swfupload.swf',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),

    /* Core */
    '___path.frontend.url___/libs/js/core/main.js',
    '___path.frontend.url___/libs/js/core/hook.js',

    /* User Interface */
    '___path.frontend.dir___/bootstrap-3.0.0/js/bootstrap.min.js' => array('name' => 'bootstrap'),

    /* LiveStreet */
    '___path.frontend.url___/libs/js/livestreet/favourite.js',
    //'___path.frontend.url___/libs/js/livestreet/blocks.js',
    '___path.frontend.url___/libs/js/livestreet/pagination.js',
    '___path.frontend.url___/libs/js/livestreet/editor.js',
    '___path.frontend.url___/libs/js/livestreet/talk.js',
    '___path.frontend.url___/libs/js/livestreet/vote.js',
    '___path.frontend.url___/libs/js/livestreet/poll.js',
    '___path.frontend.url___/libs/js/livestreet/subscribe.js',
    '___path.frontend.url___/libs/js/livestreet/geo.js',
    '___path.frontend.url___/libs/js/livestreet/wall.js',
    '___path.frontend.url___/libs/js/livestreet/usernote.js',
    '___path.frontend.url___/libs/js/livestreet/comments.js',
    '___path.frontend.url___/libs/js/livestreet/blog.js',
    '___path.frontend.url___/libs/js/livestreet/user.js',
    '___path.frontend.url___/libs/js/livestreet/userfeed.js',
    '___path.frontend.url___/libs/js/livestreet/stream.js',
    '___path.frontend.url___/libs/js/livestreet/photoset.js',
    '___path.frontend.url___/libs/js/livestreet/toolbar.js',
    '___path.frontend.url___/libs/js/livestreet/settings.js',
    '___path.frontend.url___/libs/js/livestreet/topic.js',
    '___path.frontend.url___/libs/js/livestreet/admin.js',
    '___path.frontend.url___/libs/js/livestreet/admin.userfield.js',
    '___path.frontend.url___/libs/js/livestreet/captcha.js',
    '___path.skin.url___/assets/js/init.js',
);

$config['head']['default']['css'] = array_merge(
    Config::Get('head.default.css'),
    array(
         // Template styles
         '___path.skin.dir___/assets/css/base.css',
         '___path.frontend.url___/libs/js/vendor/jquery-ui/css/smoothness/jquery-ui-1.10.2.custom.css',
         '___path.frontend.url___/libs/js/vendor/markitup/skins/synio/style.css',
         '___path.frontend.url___/libs/js/vendor/markitup/sets/synio/style.css',
         '___path.frontend.url___/libs/js/vendor/jcrop/jquery.Jcrop.css',
         '___path.frontend.url___/libs/js/vendor/prettify/prettify.css',
         '___path.frontend.url___/libs/js/vendor/prettyphoto/css/prettyphoto.css',
         '___path.frontend.url___/libs/js/vendor/notifier/jquery.notifier.css',

         '___path.skin.url___/assets/css/grid.css',
         '___path.skin.url___/assets/css/forms.css',
         '___path.skin.url___/assets/css/common.css',
         '___path.skin.url___/assets/css/icons.css',
         '___path.skin.url___/assets/css/navs.css',
         '___path.skin.url___/assets/css/tooltip.css',
         '___path.skin.url___/assets/css/popover.css',
         '___path.skin.url___/assets/css/tables.css',
         '___path.skin.url___/assets/css/topic.css',
         '___path.skin.url___/assets/css/photoset.css',
         '___path.skin.url___/assets/css/comments.css',
         '___path.skin.url___/assets/css/widgets.css',
         '___path.skin.url___/assets/css/blog.css',
         '___path.skin.url___/assets/css/modals.css',
         '___path.skin.url___/assets/css/profile.css',
         '___path.skin.url___/assets/css/wall.css',
         '___path.skin.url___/assets/css/activity.css',
         '___path.skin.url___/assets/css/admin.css',
         '___path.skin.url___/assets/css/toolbar.css',
         '___path.skin.url___/assets/css/poll.css',
         '___path.skin.url___/assets/css/tinymce.css',
         '___path.skin.url___/themes/___view.theme___/style.css',
         '___path.skin.url___/assets/css/print.css',
    )
);


return $config;

// EOF