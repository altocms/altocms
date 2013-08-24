<?php

$config['head']['default']['css'] = array(
    '___path.root.engine_lib___/external/jquery/markitup/skins/simple/style.css',
    '___path.root.engine_lib___/external/jquery/markitup/sets/default/style.css',
    '___path.root.engine_lib___/external/jquery/jcrop/jquery.Jcrop.css',
    '___path.root.engine_lib___/external/prettify/prettify.css',
    '___path.static.skin___/assets/css/jquery-ui.css',
    '___path.static.skin___/assets/css/jquery-notifier.css',
    '___path.static.skin___/assets/css/jquery-modals.css',
    '___path.static.skin___/assets/css/uniform.css',

    '___path.static.skin___/assets/css/bootstrap.min.css',
    '___path.static.skin___/assets/css/bootstrap-responsive.min.css',
    '___path.static.skin___/assets/css/datepicker.css',
    '___path.static.skin___/assets/css/fullcalendar.css',
    '___path.static.skin___/assets/css/midnight.css',
    '___path.static.skin___/assets/css/admin.css',
);

$config['head']['default']['js'] = array(
    '___path.static.skin___/assets/js/excanvas.min.js',
    '___path.static.skin___/assets/js/jquery.min.js',
    '___path.static.skin___/assets/js/jquery.ui.custom.js',
    '___path.static.skin___/assets/js/bootstrap.min.js',
    '___path.static.skin___/assets/js/bootstrap-datepicker.js',
    '___path.static.skin___/assets/js/jquery.flot.min.js',
    '___path.static.skin___/assets/js/jquery.flot.resize.min.js',
    '___path.static.skin___/assets/js/jquery.peity.min.js',
    '___path.static.skin___/assets/js/jquery.uniform.js',
    '___path.static.skin___/assets/js/fullcalendar.min.js',
    '___path.static.skin___/assets/js/midnight.js',
    '___path.static.skin___/assets/js/midnight.dashboard.js',
	'___path.root.engine_lib___/external/jquery/jquery.notifier.js',
	'___path.root.engine_lib___/external/jquery/jquery.jqmodal.js',
	'___path.root.engine_lib___/external/jquery/jquery.scrollto.js',
	'___path.root.engine_lib___/external/jquery/jquery.rich-array.min.js',
	'___path.root.engine_lib___/external/jquery/markitup/jquery.markitup.js',
	'___path.root.engine_lib___/external/jquery/jquery.form.js',
	'___path.root.engine_lib___/external/jquery/jquery.jqplugin.js',
	'___path.root.engine_lib___/external/jquery/jquery.cookie.js',
	'___path.root.engine_lib___/external/jquery/jquery.serializejson.js',
	'___path.root.engine_lib___/external/jquery/jquery.file.js',
	'___path.root.engine_lib___/external/jquery/jcrop/jquery.Jcrop.js',
	'___path.root.engine_lib___/external/jquery/poshytip/jquery.poshytip.js',
	'___path.root.engine_lib___/external/jquery/jquery.placeholder.min.js',
	'___path.root.engine_lib___/external/jquery/jquery.charcount.js',
	'___path.root.engine_lib___/external/prettify/prettify.js',
    '___path.root.dir___/templates/framework/js/main.js',
    '___path.root.web___/templates/framework/js/favourite.js',
    '___path.root.web___/templates/framework/js/talk.js',
    '___path.root.web___/templates/framework/js/vote.js',
    '___path.root.web___/templates/framework/js/poll.js',
    '___path.root.web___/templates/framework/js/subscribe.js',
    '___path.root.web___/templates/framework/js/infobox.js',
    '___path.root.web___/templates/framework/js/geo.js',
    '___path.root.web___/templates/framework/js/wall.js',
    '___path.root.web___/templates/framework/js/usernote.js',
    '___path.root.web___/templates/framework/js/comments.js',
    '___path.root.web___/templates/framework/js/blog.js',
    '___path.root.web___/templates/framework/js/user.js',
    '___path.root.web___/templates/framework/js/userfeed.js',
    '___path.root.web___/templates/framework/js/userfield.js',
    '___path.root.web___/templates/framework/js/stream.js',
    '___path.root.web___/templates/framework/js/photoset.js',
    '___path.root.web___/templates/framework/js/toolbar.js',
    '___path.root.web___/templates/framework/js/settings.js',
    '___path.root.web___/templates/framework/js/topic.js',
    '___path.root.web___/templates/framework/js/hook.js',
    '___path.static.skin___/assets/js/admin.js',
);

$config['path']['skin']['img']['dir']          = '___path.skin.dir___/assets/img/'; // папка с изображениями скина
$config['path']['skin']['img']['url']          = '___path.skin.url___/assets/img/'; // URL с изображениями скина

$config['compress']['css']['merge'] = false; // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['css']['use'] = false; // указывает на необходимость компрессии файлов. Компрессия используется только в активированном

return $config;

// EOF