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

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
chdir(dirname(__FILE__));

// Run engine loader
require_once('./engine/loader.php');

if (is_file('./install/index.php') && (!isset($_SERVER['HTTP_APP_ENV']) || $_SERVER['HTTP_APP_ENV']!='test')) {
    F::HttpLocation('install/', true);
    exit;
}

$oProfiler = ProfilerSimple::getInstance();
if (DEBUG) $iTimeId = $oProfiler->Start('full_time');

$oRouter = Router::getInstance();
$oRouter->Exec();

if (DEBUG) $oProfiler->Stop($iTimeId);

// EOF