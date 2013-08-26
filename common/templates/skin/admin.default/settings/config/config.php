<?php

$config['path']['smarty']['template'] = array(
    'themes' => '___path.skins.dir___/___view.skin___/themes/',
    '___path.skins.dir___/___view.skin___/tpls/',
);

$config['head']['default']['css'] = array(
    '___path.framework.dir___/js/vendor/markitup/skins/simple/style.css',
    '___path.framework.dir___/js/vendor/markitup/sets/default/style.css',
    '___path.framework.dir___/js/vendor/jcrop/jquery.Jcrop.css',
    '___path.framework.dir___/js/vendor/prettify/prettify.css',
    '___path.skin.dir___/assets/css/jquery-ui.css',
    '___path.skin.dir___/assets/css/jquery-notifier.css',
    '___path.skin.dir___/assets/css/jquery-modals.css',
    '___path.skin.dir___/assets/css/uniform.css',

    '___path.skin.dir___/assets/css/bootstrap.min.css',
    '___path.skin.dir___/assets/css/bootstrap-responsive.min.css',
    '___path.skin.dir___/assets/css/datepicker.css',
    '___path.skin.dir___/assets/css/fullcalendar.css',
    '___path.skin.dir___/assets/css/midnight.css',
    '___path.skin.dir___/assets/css/admin.css',
);

$config['head']['default']['js'] = array(
    '___path.skin.dir___/assets/js/excanvas.min.js',
    '___path.skin.dir___/assets/js/jquery.min.js',
    '___path.skin.dir___/assets/js/jquery.ui.custom.js',
    '___path.skin.dir___/assets/js/bootstrap.min.js',
    '___path.skin.dir___/assets/js/bootstrap-datepicker.js',
    '___path.skin.dir___/assets/js/jquery.flot.min.js',
    '___path.skin.dir___/assets/js/jquery.flot.resize.min.js',
    '___path.skin.dir___/assets/js/jquery.peity.min.js',
    '___path.skin.dir___/assets/js/jquery.uniform.js',
    '___path.skin.dir___/assets/js/fullcalendar.min.js',
    '___path.skin.dir___/assets/js/midnight.js',
    '___path.skin.dir___/assets/js/midnight.dashboard.js',
	'___path.framework.dir___/js/vendor/jquery.notifier.js',
	'___path.framework.dir___/js/vendor/jquery.jqmodal.js',
	'___path.framework.dir___/js/vendor/jquery.scrollto.js',
	'___path.framework.dir___/js/vendor/jquery.rich-array.min.js',
	'___path.framework.dir___/js/vendor/markitup/jquery.markitup.js',
	'___path.framework.dir___/js/vendor/jquery.form.js',
	'___path.framework.dir___/js/vendor/jquery.jqplugin.js',
	'___path.framework.dir___/js/vendor/jquery.cookie.js',
	'___path.framework.dir___/js/vendor/jquery.serializejson.js',
	'___path.framework.dir___/js/vendor/jquery.file.js',
	'___path.framework.dir___/js/vendor/jcrop/jquery.Jcrop.js',
	'___path.framework.dir___/js/vendor/poshytip/jquery.poshytip.js',
	'___path.framework.dir___/js/vendor/jquery.placeholder.min.js',
	'___path.framework.dir___/js/vendor/jquery.charcount.js',
	'___path.framework.dir___/js/vendor/prettify/prettify.js',
    '___path.templates.dir___/framework_ls/js/main.js',
    '___path.templates.dir___/framework_ls/js/favourite.js',
    '___path.templates.dir___/framework_ls/js/talk.js',
    '___path.templates.dir___/framework_ls/js/vote.js',
    '___path.templates.dir___/framework_ls/js/poll.js',
    '___path.templates.dir___/framework_ls/js/subscribe.js',
    '___path.templates.dir___/framework_ls/js/infobox.js',
    '___path.templates.dir___/framework_ls/js/geo.js',
    '___path.templates.dir___/framework_ls/js/wall.js',
    '___path.templates.dir___/framework_ls/js/usernote.js',
    '___path.templates.dir___/framework_ls/js/comments.js',
    '___path.templates.dir___/framework_ls/js/blog.js',
    '___path.templates.dir___/framework_ls/js/user.js',
    '___path.templates.dir___/framework_ls/js/userfeed.js',
    '___path.templates.dir___/framework_ls/js/userfield.js',
    '___path.templates.dir___/framework_ls/js/stream.js',
    '___path.templates.dir___/framework_ls/js/photoset.js',
    '___path.templates.dir___/framework_ls/js/toolbar.js',
    '___path.templates.dir___/framework_ls/js/settings.js',
    '___path.templates.dir___/framework_ls/js/topic.js',
    '___path.templates.dir___/framework_ls/js/hook.js',
    '___path.skin.dir___/assets/js/admin.js',
);

$config['path']['skin']['img']['dir']          = '___path.skin.dir___/assets/img/'; // папка с изображениями скина
$config['path']['skin']['img']['url']          = '___path.skin.url___/assets/img/'; // URL с изображениями скина

$config['compress']['css']['merge'] = false; // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['css']['use'] = false; // указывает на необходимость компрессии файлов. Компрессия используется только в активированном

return $config;

// EOF