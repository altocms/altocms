<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   Skin switcher for LiveStreet by Sergey S Yaglov
 *   Site: http://yaglov.ru/
 *----------------------------------------------------------------------------
 */

$config['get_param'] = 'skin';

$config['widgets']['skinswitch'] = array(
    'name' => 'skinswitch',
    'plugin' => 'skinswitch',
    'group' => 'toolbar',
    'priority' => 15,
    'off' => array(
        'admin',
    ),
);

return $config;

// EOF