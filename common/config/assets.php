<?php
/*-------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *-------------------------------------------------------
 */

/**
 * Подключаемые ресурсы - js- и css-файлы
 * <local_path|URL>
 * <local_path|URL> => <parameters_array>
 *
 * Параметры:
 *      'asset' - указывает на один набор при слиянии файлов
 *      'name'  - "каноническое" имя файла
 *      'place' - место размещения (только для js)
 *      'prepare' - файл готовится, но не включается в HTML
 */
$config['default']['js'] = array(
    /* Vendor libs */
    '___path.frontend.dir___/libs/vendor/html5shiv.min.js' => array('browser' => 'lt IE 9'),
    '___path.frontend.dir___/libs/vendor/jquery-1.10.2.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-migrate-1.2.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('name' => 'jquery-ui', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/localization/jquery-ui-datepicker-ru.js',
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/jquery.ui.autocomplete.html.js',
    '___path.frontend.dir___/libs/vendor/jquery.browser.js',
    '___path.frontend.dir___/libs/vendor/jquery.scrollto.js',
    '___path.frontend.dir___/libs/vendor/jquery.rich-array.min.js',
    '___path.frontend.dir___/libs/vendor/jquery.form.js',
    //'___path.frontend.dir___/libs/vendor/jquery.jqplugin.js',
    '___path.frontend.dir___/libs/vendor/jquery.cookie.js',
    '___path.frontend.dir___/libs/vendor/jquery.serializejson.js',
    '___path.frontend.dir___/libs/vendor/jquery.file.js',
    '___path.frontend.dir___/libs/vendor/jquery.placeholder.min.js',
    '___path.frontend.dir___/libs/vendor/jquery.charcount.js',
    '___path.frontend.dir___/libs/vendor/jquery.imagesloaded.js',
    '___path.frontend.dir___/libs/vendor/jquery.montage.min.js',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.dir___/libs/vendor/markitup/jquery.markitup.js',
    '___path.frontend.dir___/libs/vendor/notifier/jquery.notifier.js',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.js',
    '___path.frontend.dir___/libs/vendor/nprogress/nprogress.js',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.js',
    '___path.frontend.dir___/libs/vendor/prettyphoto/js/jquery.prettyphoto.js',
    '___path.frontend.dir___/libs/vendor/rowgrid/jquery.row-grid.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery.pulse/jquery.pulse.min.js' => array('asset' => 'mini'),

    '___path.frontend.dir___/libs/vendor/parsley/parsley.js',
    '___path.frontend.dir___/libs/vendor/parsley/i18n/messages.ru.js',
    //'___path.frontend.dir___/libs/vendor/bootbox/bootbox.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/bootbox/bootbox.js',

    //'___path.frontend.dir___/libs/vendor/swfobject/swfobject.js',

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
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/plugins/*'       => array(
        'dir_from'  => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'   => true,
        'compress'  => false,
        'merge'     => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/langs/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/skins/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/themes/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/jquery.fileapi/FileAPI/*'       => array(
        'dir_from'  => '___path.frontend.dir___/libs/vendor/jquery.fileapi/FileAPI/',
        'prepare'   => true,
        'merge'    => false,
    ),

    /* Core */
    '___path.frontend.dir___/libs/js/core/main.js',
    '___path.frontend.dir___/libs/js/core/modal.js',
    '___path.frontend.dir___/libs/js/core/hook.js',

    '___path.frontend.dir___/bootstrap-3/js/bootstrap.min.js' => array('name' => 'bootstrap'),

    /* Engine */
    '___path.frontend.dir___/libs/js/engine/favourite.js',
    '___path.frontend.dir___/libs/js/engine/widgets.js',
    '___path.frontend.dir___/libs/js/engine/pagination.js',
    '___path.frontend.dir___/libs/js/engine/editor.js',
    '___path.frontend.dir___/libs/js/engine/talk.js',
    '___path.frontend.dir___/libs/js/engine/vote.js',
    '___path.frontend.dir___/libs/js/engine/poll.js',
    '___path.frontend.dir___/libs/js/engine/subscribe.js',
    '___path.frontend.dir___/libs/js/engine/geo.js',
    '___path.frontend.dir___/libs/js/engine/wall.js',
    '___path.frontend.dir___/libs/js/engine/usernote.js',
    '___path.frontend.dir___/libs/js/engine/comments.js',
    '___path.frontend.dir___/libs/js/engine/blog.js',
    '___path.frontend.dir___/libs/js/engine/user.js',
    '___path.frontend.dir___/libs/js/engine/userfeed.js',
    '___path.frontend.dir___/libs/js/engine/stream.js',
    '___path.frontend.dir___/libs/js/engine/swfuploader.js',
    '___path.frontend.dir___/libs/js/engine/photoset.js',
    '___path.frontend.dir___/libs/js/engine/toolbar.js',
    '___path.frontend.dir___/libs/js/engine/settings.js',
    '___path.frontend.dir___/libs/js/engine/topic.js',
    //'___path.frontend.dir___/libs/js/engine/admin.js',
    '___path.frontend.dir___/libs/js/engine/userfield.js',
    '___path.frontend.dir___/libs/js/engine/init.js',
    '___path.frontend.dir___/libs/js/engine/altoUploader.js',

    '___path.frontend.dir___/libs/vendor/jquery.fileapi/FileAPI/FileAPI.min.js',
    //    '___path.frontend.dir___/libs/vendor/jquery.fileapi/FileAPI/FileAPI.exif.js',
    '___path.frontend.dir___/libs/vendor/jquery.fileapi/jquery.fileapi.js',
    '___path.frontend.dir___/libs/js/engine/altoMultiUploader.js',
    '___path.frontend.dir___/libs/js/engine/altoImageManager.js',
    '___path.frontend.dir___/libs/js/engine/altoPopover.js',
    '___path.frontend.dir___/libs/vendor/masonry.pkgd.js',
    '___path.frontend.dir___/libs/vendor/imagesloaded.pkgd.js',
);

//потенциально проблемные файлы выводим в футере
$config['footer']['js'] = array(
    '//yandex.st/share/share.js',
);

$config['default']['css'] = array(
    // Framework styles
    '___path.frontend.dir___/libs/css/reset.css',
    '___path.frontend.dir___/libs/css/helpers.css',
    '___path.frontend.dir___/libs/css/text.css',
    '___path.frontend.dir___/libs/css/dropdowns.css',
    '___path.frontend.dir___/libs/css/buttons.css',
    '___path.frontend.dir___/libs/css/forms.css',
    '___path.frontend.dir___/libs/css/navs.css',
    '___path.frontend.dir___/libs/css/modals.css',
    //'___path.frontend.dir___/libs/css/tooltip.css',
    '___path.frontend.dir___/libs/css/popover.css',
    '___path.frontend.dir___/libs/css/alerts.css',
    '___path.frontend.dir___/libs/css/toolbar.css',
    '___path.frontend.dir___/libs/vendor/nprogress/nprogress.css',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.css',
    '___path.frontend.dir___/libs/vendor/prettyphoto/css/prettyphoto.css',
);

// EOF
