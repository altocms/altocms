<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */
if (!defined('ALTO_DIR')) die('');

/**
 * Versions
 */
define('ALTO_VERSION', '1.1.19');
define('LS_VERSION', '1.0.3'); // LS-compatible
define('ALTO_PHP_REQUIRED', '5.3'); // required version of PHP
define('ALTO_MYSQL_REQUIRED', '5.0'); // required version of PHP

if (version_compare(phpversion(), ALTO_PHP_REQUIRED) < 0) {
    die ('PHP version ' . ALTO_PHP_REQUIRED . ' or more requires for Alto CMS');
}

// Available since PHP 5.4.0, so fix it
if (empty($_SERVER['REQUEST_TIME_FLOAT'])) {
    $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
}

define('ALTO_DEBUG_PROFILE', 1);
define('ALTO_DEBUG_FILES', 2);

if (is_file(__DIR__ . '/config.defines.php')) {
    include(__DIR__ . '/config.defines.php');
}
defined('DEBUG') || define('DEBUG', 0);

// load basic config with paths
$config = include(__DIR__ . '/config.php');
if (!$config) {
    die('Fatal error: Cannot load file "' . __DIR__ . '/config.php"');
}

// load system functions
$sFuncFile = $config['path']['dir']['engine'] . 'include/Func.php';
if (!is_file($sFuncFile) || !include($sFuncFile)) {
    die('Fatal error: Cannot load file "' . $sFuncFile . '"');
}

// load Storage class
F::IncludeFile($config['path']['dir']['engine'] . '/classes/core/Storage.class.php');

if (!isset($config['url']['request'])) {
    $config['url']['request'] = F::ParseUrl();
}

// load Config class
F::IncludeFile($config['path']['dir']['engine'] . '/classes/core/Config.class.php');

if (!defined('ALTO_NO_LOADER')) {
    // load Loder class
    F::IncludeFile($config['path']['dir']['engine'] . '/classes/core/Loader.class.php');
    Loader::Init($config);
}

// load Application class
F::IncludeFile($config['path']['dir']['engine'] . '/classes/core/Application.class.php');


// EOF
