<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

$config = array();

$config['$root$']['router']['uri']['~^topic/edit/(.+)$~'] = 'content/edit/\\1';
$config['$root$']['router']['uri']['~^topic/delete/(.+)$~'] = 'content/delete/\\1';
$config['$root$']['router']['uri']['~^topic/add\/?$~'] = 'content/topic/add/';

$config['$root$']['view']['img_max_width']    = '___module.uploader.images.default.max_width___';
$config['$root$']['view']['img_max_height']   = '___module.uploader.images.default.max_height___';

$config['$root$']['module']['topic']['max_filesize_limit'] = '___module.uploader.files.default.file_maxsize___';
$config['$root$']['module']['topic']['upload_mime_types'] = '___module.uploader.files.default.file_extensions___';

$config['$root$']['classes']['class'] = array(
    'LS' => '___path.dir.plugin.ls___libs/Ls.class.php',
);

// набор алиасов для совместимости LS
$config['$root$']['lang']['aliases'] = array(
    'ru' => 'russian',
    'en' => 'english',
);

return $config;


// EOF