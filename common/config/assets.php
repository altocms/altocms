<?php

$config['ls']['head']['default']['js']  = array(
    '___path.frontend.dir___/libs/js/vendor/html5shiv.min.js' => array('browser'=>'lt IE 9'), // хак для IE версии ниже 9
    //'___path.root.engine_lib___/external/jquery/jquery.js' => array('name'=>'jquery.js'), // файлы с таким же параметром 'name' добавляться повторно не будут
    '___path.frontend.dir___/libs/js/vendor/jquery-1.10.2.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/js/vendor/jquery-migrate-1.2.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/js/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('asset' => 'mini'),
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

    '___path.frontend.dir___/libs/js/vendor/swfobject/swfobject.js',

    /* swfupload */
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

    /* TinyMCE */
    '___path.frontend.dir___/libs/js/vendor/tinymce_4/tinymce.min.js'       => array(
        'name'     => 'tinymce_4/tinymce.min.js',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),
);

$config['ls']['head']['default']['css'] = array(
    '___path.frontend.dir___/libs/css/reset.css',
    '___path.frontend.dir___/libs/css/print.css',
    '___path.frontend.dir___/libs/js/vendor/markitup/skins/default/style.css',
    '___path.frontend.dir___/libs/js/vendor/markitup/sets/default/style.css',
    '___path.frontend.dir___/libs/js/vendor/jcrop/jquery.Jcrop.css',
    '___path.frontend.dir___/libs/js/vendor/prettify/prettify.css',
);

// EOF
