<?php
/*-------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *-------------------------------------------------------
 */

return array(
    'alias'  => array(
        'R' => 'Router',
        'C' => 'Config',
        'E' => 'Engine',
        'App' => 'Application',
    ),
    'class'  => array(
        'LsObject' => '___path.dir.engine___/classes/abstract/LsObject.class.php',
        'Qevix' => '___path.dir.libs___/php-qevix-0.4.3/qevix.php',
        'csstidy' => '___path.dir.libs___/CSSTidy-1.5.7/class.csstidy.php',
        'JShrink\Minifier' => '___path.dir.libs___/JShrink-1.3.0/src/JShrink/Minifier.php',
    ),
    'prefix' => array(
        'DbSimple_' => '___path.dir.libs___/DbSimple3/lib/',
    ),
);

// EOF