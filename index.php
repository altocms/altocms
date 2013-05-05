<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
header('X-Powered-By: Alto CMS');

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
chdir(dirname(__FILE__));

// Получаем объект конфигурации
require_once('./config/loader.php');
require_once(Config::Get('path.root.engine') . '/classes/Engine.class.php');

if (is_file('./install/index.php') && (!isset($_SERVER['HTTP_APP_ENV']) || $_SERVER['HTTP_APP_ENV']!='test')) {
    F::HttpLocation('install/', true);
    exit;
}

$oProfiler = ProfilerSimple::getInstance(Config::Get('path.root.server') . '/logs/' . Config::Get('sys.logs.profiler_file'), Config::Get('sys.logs.profiler'));
if (DEBUG) $iTimeId = $oProfiler->Start('full_time');

$oRouter = Router::getInstance();
$oRouter->Exec();

if (DEBUG) $oProfiler->Stop($iTimeId);

// EOF