<?php

$config['smarty']['dir']['templates'] = array(
    'themes' => '___path.skins.dir___/___view.skin___/themes/',
    'tpls'   => '___path.skins.dir___/___view.skin___/tpls/',
);

$config['head']['default']['css'] = array(
    '___path.frontend.dir___/libs/js/vendor/markitup/skins/default/style.css',
    '___path.frontend.dir___/libs/js/vendor/markitup/sets/default/style.css',
    '___path.frontend.dir___/libs/js/vendor/jcrop/jquery.Jcrop.css',
    '___path.frontend.dir___/libs/js/vendor/prettify/prettify.css',
    '___path.skin.dir___/assets/css/jquery-ui.css',
    '___path.skin.dir___/assets/css/jquery-notifier.css',
    '___path.skin.dir___/assets/css/jquery-modals.css',
    '___path.skin.dir___/assets/css/uniform.css',

    '___path.skin.dir___/assets/css/bootstrap.min.css',
    '___path.skin.dir___/assets/css/bootstrap-responsive.min.css',
    '___path.skin.dir___/assets/css/datepicker.css',
    '___path.skin.dir___/assets/css/fullcalendar.css',
    '___path.skin.dir___/assets/css/modals.css',
    '___path.skin.dir___/assets/css/midnight.css',
    '___path.skin.dir___/assets/css/admin.css',
);

$config['head']['default']['js'] = array(
    '___path.frontend.dir___/libs/js/vendor/jquery-1.10.2.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/js/vendor/jquery-migrate-1.2.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/js/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/js/vendor/jquery-ui/js/localization/jquery-ui-datepicker-ru.js',
    '___path.frontend.dir___/libs/js/vendor/markitup/jquery.markitup.js' => array('name' => 'markitup'),
    '___path.frontend.dir___/libs/js/vendor/tinymce_4/tinymce.min.js' => array('name' => 'tinymce', 'asset' => 'mini'),

    '___path.frontend.dir___/bootstrap-3.0.0/js/bootstrap.min.js' => array('name' => 'bootstrap'),

    '___path.skin.dir___/assets/js/excanvas.min.js',
    '___path.skin.dir___/assets/js/jquery.flot.min.js',
    '___path.skin.dir___/assets/js/jquery.flot.resize.min.js',
    '___path.skin.dir___/assets/js/jquery.peity.min.js',
    '___path.skin.dir___/assets/js/jquery.uniform.js',
    '___path.skin.dir___/assets/js/fullcalendar.min.js',
    '___path.skin.dir___/assets/js/midnight.js',
    '___path.skin.dir___/assets/js/midnight.dashboard.js',
    '___path.frontend.dir___/libs/js/vendor/notifier/jquery.notifier.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.scrollto.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.rich-array.min.js',

    '___path.frontend.dir___/libs/js/vendor/jquery.form.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.cookie.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.serializejson.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.file.js',
    '___path.frontend.dir___/libs/js/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.placeholder.min.js',
    '___path.frontend.dir___/libs/js/vendor/jquery.charcount.js',
    '___path.frontend.dir___/libs/js/vendor/prettify/prettify.js',

    '___path.frontend.dir___/libs/js/core/main.js',
    '___path.frontend.dir___/libs/js/ui/modal.js',
    '___path.frontend.dir___/ls/js/favourite.js',
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
    '___path.skin.dir___/assets/js/admin.js',
);

$config['path']['skin']['img']['dir'] = '___path.skin.dir___/assets/img/'; // папка с изображениями скина
$config['path']['skin']['img']['url'] = '___path.skin.url___/assets/img/'; // URL с изображениями скина

$config['compress']['css']['merge'] = false; // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['css']['use']
    = false; // указывает на необходимость компрессии файлов. Компрессия используется только в активированном

return $config;

// EOF