<?php
/*-------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *-------------------------------------------------------
 */

return [
    'alias'  => [
        'R' => 'Router',
        'C' => 'Config',
        'E' => 'Engine',
        'App' => 'Application',
    ],
    'class'  => [
        'LsObject' => '___path.dir.engine___/classes/abstract/LsObject.class.php',
        'Jevix' => '___path.dir.libs___/Jevix/jevix.class.php',
        'Qevix' => '___path.dir.libs___/php-qevix-0.4/qevix.php',
    ],
    'prefix' => [
        'DbSimple_' => '___path.dir.libs___/DbSimple3/lib/',
    ],
    'namespace' => [
        'alto\engine\ar' => '___path.dir.engine___/classes/ar/',
    ],
];

// EOF