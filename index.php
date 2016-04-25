<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

defined('ALTO_DIR') || define('ALTO_DIR', dirname(__FILE__));

// Run engine loader
require_once(ALTO_DIR . '/engine/loader.php');

// Creates and executes application
App::create()->exec();

// EOF