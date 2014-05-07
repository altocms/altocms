<?php
$config['view']['theme'] = 'default';

$config['smarty']['dir']['templates'] = array(
    'themes' => '___path.skins.dir___/___view.skin___/themes/',
    'tpls'   => '___path.skins.dir___/___view.skin___/tpls/',
);

$config['head']['default']['css'] = array(
    '___path.frontend.dir___/libs/vendor/markitup/skins/default/style.css',
    '___path.frontend.dir___/libs/vendor/markitup/sets/default/style.css',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.css',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.css',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.css',
    '___path.skin.dir___/assets/css/jquery-ui.css',
    '___path.skin.dir___/assets/css/jquery-notifier.css',

    '___path.skin.dir___/assets/css/bootstrap.min.css',
    '___path.skin.dir___/assets/css/simpleline/simple-line-icons.css',
    '___path.skin.dir___/assets/css/datepicker.css',
    '___path.skin.dir___/assets/css/fullcalendar.css',
    '___path.skin.dir___/assets/css/main-modals.css',
    '___path.skin.dir___/assets/css/main-admin.css',

    '___path.skin.dir___/assets/css/main.css',
    '___path.skin.dir___/assets/css/main-forms.css',
    '___path.skin.dir___/themes/___view.theme___/theme.css',
);

$config['head']['default']['js'] = array(
    '___path.frontend.dir___/libs/vendor/jquery-1.10.2.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-migrate-1.2.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/localization/jquery-ui-datepicker-ru.js',
    '___path.frontend.dir___/libs/vendor/markitup/jquery.markitup.js' => array('name' => 'markitup'),
    '___path.frontend.dir___/libs/vendor/tinymce_4/tinymce.min.js' => array('name' => 'tinymce', 'asset' => 'mini'),

    '___path.frontend.dir___/bootstrap-3/js/bootstrap.min.js' => array('name' => 'bootstrap'),

    '___path.skin.dir___/assets/js/excanvas.min.js' => array('asset' => 'mini'),
    '___path.skin.dir___/assets/js/jquery.flot.min.js' => array('asset' => 'mini'),
    '___path.skin.dir___/assets/js/jquery.flot.resize.min.js' => array('asset' => 'mini'),
    '___path.skin.dir___/assets/js/jquery.peity.min.js' => array('asset' => 'mini'),
    '___path.skin.dir___/assets/js/fullcalendar.min.js' => array('asset' => 'mini'),
    '___path.skin.dir___/assets/js/midnight.js',
    //'___path.skin.dir___/assets/js/midnight.dashboard.js',
    '___path.frontend.dir___/libs/vendor/notifier/jquery.notifier.js',
    '___path.frontend.dir___/libs/vendor/jquery.scrollto.js',
    '___path.frontend.dir___/libs/vendor/jquery.rich-array.min.js',

    '___path.frontend.dir___/libs/vendor/jquery.form.js',
    '___path.frontend.dir___/libs/vendor/jquery.cookie.js',
    '___path.frontend.dir___/libs/vendor/jquery.serializejson.js',
    '___path.frontend.dir___/libs/vendor/jquery.file.js',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.dir___/libs/vendor/jquery.placeholder.min.js',
    '___path.frontend.dir___/libs/vendor/jquery.charcount.js',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.js',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.js',
    '___path.frontend.dir___/libs/vendor/bootbox/bootbox.min.js' => array('asset' => 'mini'),

    '___path.frontend.dir___/libs/js/core/main.js',
    '___path.frontend.dir___/libs/js/core/hook.js',
    '___path.frontend.dir___/libs/js/core/modal.js',
    '___path.frontend.dir___/libs/js/engine/favourite.js',
    '___path.frontend.dir___/libs/js/engine/vote.js',
    '___path.frontend.dir___/libs/js/engine/poll.js',
    '___path.frontend.dir___/libs/js/engine/subscribe.js',
    '___path.frontend.dir___/libs/js/engine/geo.js',
    '___path.frontend.dir___/libs/js/engine/usernote.js',
    '___path.frontend.dir___/libs/js/engine/comments.js',
    '___path.frontend.dir___/libs/js/engine/blog.js',
    '___path.frontend.dir___/libs/js/engine/user.js',
    '___path.frontend.dir___/libs/js/engine/userfeed.js',
    '___path.frontend.dir___/libs/js/engine/admin-userfield.js',
    '___path.frontend.dir___/libs/js/engine/settings.js',
    '___path.frontend.dir___/libs/js/engine/topic.js',
    '___path.skin.dir___/assets/js/admin.js',
    '___path.skin.dir___/assets/js/jquery.formstyler.min.js',
);

$config['footer']['default']['js'] = array();

$config['path']['skin']['img']['dir'] = '___path.skin.dir___/assets/img/'; // папка с изображениями скина
$config['path']['skin']['img']['url'] = '___path.skin.url___/assets/img/'; // URL с изображениями скина

//$config['compress']['css']['merge'] = false; // указывает на необходимость слияния файлов по указанным блокам.
//$config['compress']['css']['use'] = false; // указывает на необходимость компрессии файлов. Компрессия используется только в активированном

return $config;

// EOF