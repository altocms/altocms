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
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Versions
 */
define('ALTO_VERSION', '0.9.6');
define('LS_VERSION', '1.0.3'); // LS-compatible
define('ALTO_PHP_REQUIRED', '5.3'); // required version of PHP
define('ALTO_MYSQL_REQUIRED', '5.0'); // required version of PHP

if (version_compare(phpversion(), ALTO_PHP_REQUIRED) < 0) {
    die ('PHP version ' . ALTO_PHP_REQUIRED . ' or more requires for Alto CMS');
}

define('ALTO_DEBUG_PROFILE', 1);
define('ALTO_DEBUG_FILES', 2);

// load system functions
require_once(dirname(dirname(__FILE__)) . '/engine/include/Func.php');

// load Loder class
F::IncludeFile(dirname(dirname(__FILE__)) . '/engine/classes/Loader.class.php');
F::IncludeFile(dirname(dirname(__FILE__)) . '/engine/classes/Storage.class.php');

// load Config class
F::IncludeFile(dirname(dirname(__FILE__)) . '/engine/lib/internal/ConfigSimple/Config.class.php');

if (!defined('NO_LOADER')) {
    Loader::init(dirname(__FILE__));
}

if (!defined('DEBUG')) {
    define('DEBUG', 0);
}

if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI']) && $_SERVER['SCRIPT_NAME'] == $_SERVER['REQUEST_URI']) {
    // для предотвращения зацикливания и ошибки 404
    $_SERVER['REQUEST_URI'] = '/';
}
// EOF