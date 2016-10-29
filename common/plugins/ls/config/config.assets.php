<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

// Assets for LS compatibility
$config['assets']['default']['js']  = array(
    '___path.frontend.dir___/libs/vendor/html5shiv.min.js' => array('browser'=>'lt IE 9'), // хак для IE версии ниже 9
    '___path.frontend.dir___/libs/vendor/jquery-1.12.4.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-migrate-1.4.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('name' => 'jquery-ui', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery.rich-array.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery.placeholder.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/ls/lib/jquery.notifier.js',
    '___path.frontend.dir___/ls/lib/jquery.jqmodal.js'=> array('asset' => 'jqmodal'),
    '___path.frontend.dir___/libs/vendor/jquery.scrollto.js',
    '___path.frontend.dir___/libs/vendor/jquery.form.js',
    '___path.frontend.dir___/libs/vendor/jquery.jqplugin.js',
    '___path.frontend.dir___/libs/vendor/jquery.cookie.js',
    '___path.frontend.dir___/libs/vendor/jquery.serializejson.js',
    '___path.frontend.dir___/libs/vendor/jquery.file.js',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.dir___/libs/vendor/jquery.charcount.js',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.js',
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
    '___path.frontend.dir___/ls/js/topic.js',
    '___path.frontend.dir___/ls/js/hook.js',
    '___path.frontend.dir___/ls/js/settings.js',

    '___path.frontend.dir___/libs/vendor/swfobject/swfobject.js',

    /* swfupload */
    '___path.frontend.dir___/libs/vendor/swfobject/plugin/swfupload.js' => array(
        'name'    => 'swfobject/plugin/swfupload.js',
        'prepare' => true
    ),
    '___path.frontend.dir___/libs/vendor/swfupload/swfupload.js'        => array(
        'name'    => 'swfupload/swfupload.js',
        'prepare' => true
    ),
    '___path.frontend.dir___/libs/vendor/swfupload/swfupload.swf'       => array(
        'name'     => 'swfupload/swfupload.swf',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),

    /* markitUp */
    '___path.frontend.dir___/libs/vendor/markitup/jquery.markitup.js'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/markitup/',
        'name'     => 'markitup',
    ),

    /* tinyMCE */
    '___path.frontend.dir___/libs/vendor/tinymce_4/tinymce.min.js'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'name'     => 'tinymce_4',
        'compress' => false,
        'merge'    => false
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/plugins/*'       => array(
        'dir_from'  => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'   => true,
        'compress'  => false,
        'merge'     => false
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/langs/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/skins/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/themes/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),

);

$config['assets']['default']['css'] = array(
    '___path.frontend.dir___/libs/css/reset.css',
    '___path.frontend.dir___/libs/css/print.css',
    '___path.frontend.dir___/libs/vendor/markitup/skins/default/style.css',
    '___path.frontend.dir___/libs/vendor/markitup/sets/default/style.css',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.css',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.css',
);

// EOF
