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

header('Content-Type: text/html; charset=utf-8');
header('X-Powered-By: Alto CMS');

defined('ALTO_DIR') or define('ALTO_DIR', dirname(__FILE__));

// Run engine loader
require_once(ALTO_DIR . '/engine/loader.php');

$oRouter = Router::getInstance();
$oRouter->Exec();

// EOF