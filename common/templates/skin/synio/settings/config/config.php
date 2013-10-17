<?php

$config = array();

// Максимальная вложенность комментов при отображении
$config['module']['comment']['max_tree'] = 5;

// Ограничение на вывод числа друзей пользователя на странице его профиля
$config['module']['user']['friend_on_profile'] = 18;

$config['view']['theme'] = 'default';


/* Grid type:
 * 
 * fluid - резина
 * fixed - фиксированная ширина
 */
$config['view']['grid']['type'] = 'fixed';

/* Fluid settings */
$config['view']['grid']['fluid_min_width'] = 976; // Min - 976px
$config['view']['grid']['fluid_max_width'] = 1300;

/* Fixed settings */
$config['view']['grid']['fixed_width'] = 976;

/*
$config['head']['default']['js']  = array(
    '___path.frontend.dir___/libs/js/vendor/html5shiv.min.js' => array('browser'=>'lt IE 9'), // хак для IE версии ниже 9
    //'___path.root.engine_lib___/external/jquery/jquery.js' => array('name'=>'jquery.js'), // файлы с таким же параметром 'name' добавляться повторно не будут
    '___path.frontend.dir___/libs/js/vendor/jquery-1.9.1.min.js',
    '___path.frontend.dir___/libs/js/vendor/jquery-migrate-1.2.1.min.js',
    '___path.frontend.dir___/ls/lib/jquery-ui.js',
    '___path.frontend.dir___/ls/lib/jquery.notifier.js',
    '___path.frontend.dir___/ls/lib/jquery.jqmodal.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.scrollto.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.rich-array.min.js',
    '___path.frontend.dir___/libs/js/vendor/markitup/jquery.markitup.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.form.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.jqplugin.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.cookie.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.serializejson.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.file.js',
    '___path.frontend.dir___/libs/js/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.placeholder.min.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.charcount.js',
    '___path.frontend.dir___/libs/js/vendor/prettify/prettify.js',
    '___path.frontend.dir___/ls/lib/poshytip/jquery.poshytip.js',
    '___path.frontend.dir___/ls/js/main.js',
    '___path.frontend.dir___/ls/js/favourite.js',
    '___path.frontend.dir___/ls/js/blocks.js',
    '___path.frontend.dir___/ls/js/talk.js',
    '___path.frontend.dir___/ls/js/vote.js',
    '___path.frontend.dir___/ls/js/poll.js',
    '___path.frontend.dir___/ls/js/subscribe.js',
    '___path.frontend.dir___/ls/js/infobox.js',
    '___path.frontend.dir___/ls/js/geo.js',
    '___path.frontend.dir___/ls/js/wall.js',
    '___path.frontend.dir___/ls/js/usernote.js',
    '___path.frontend.dir___/ls/js/comments.js',
    '___path.frontend.dir___/ls/js/blog.js',
    '___path.frontend.dir___/ls/js/user.js',
    '___path.frontend.dir___/ls/js/userfeed.js',
    '___path.frontend.dir___/ls/js/userfield.js',
    '___path.frontend.dir___/ls/js/stream.js',
    '___path.frontend.dir___/ls/js/photoset.js',
    '___path.frontend.dir___/ls/js/toolbar.js',
    '___path.frontend.dir___/ls/js/settings.js',
    '___path.frontend.dir___/ls/js/topic.js',
    '___path.frontend.dir___/ls/js/hook.js',
    '___path.skin.dir___/js/template.js',
);
*/

$config['head']['default']['js'] = Config::Get('assets.ls.head.default.js');
$config['head']['default']['js'][] = '___path.skin.dir___/js/template.js';

$config['head']['default']['css'] = array(
    "___path.skin.dir___/css/reset.css",
    "___path.skin.dir___/css/base.css",
    "___path.frontend.dir___/libs/js/vendor/markitup/skins/synio/style.css",
    "___path.frontend.dir___/libs/js/vendor/markitup/sets/synio/style.css",
    "___path.frontend.dir___/libs/js/vendor/jcrop/jquery.Jcrop.css",
    "___path.frontend.dir___/libs/js/vendor/prettify/prettify.css",
    "___path.skin.dir___/css/grid.css",
    "___path.skin.dir___/css/common.css",
    "___path.skin.dir___/css/text.css",
    "___path.skin.dir___/css/forms.css",
    "___path.skin.dir___/css/buttons.css",
    "___path.skin.dir___/css/navs.css",
    "___path.skin.dir___/css/icons.css",
    "___path.skin.dir___/css/tables.css",
    "___path.skin.dir___/css/topic.css",
    "___path.skin.dir___/css/comments.css",
    "___path.skin.dir___/css/blocks.css",
    "___path.skin.dir___/css/modals.css",
    "___path.skin.dir___/css/blog.css",
    "___path.skin.dir___/css/profile.css",
    "___path.skin.dir___/css/wall.css",
    "___path.skin.dir___/css/infobox.css",
    "___path.skin.dir___/css/jquery.notifier.css",
    "___path.skin.dir___/css/smoothness/jquery-ui.css",
    "___path.skin.dir___/themes/___view.theme___/style.css",
    "___path.skin.dir___/css/print.css",
);


return $config;

// EOF