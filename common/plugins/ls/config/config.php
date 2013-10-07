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

return $config;


// EOF