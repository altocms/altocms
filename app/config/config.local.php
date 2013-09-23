<?php
/*-------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *-------------------------------------------------------
 */

if (!defined('DEBUG')) {
    define('DEBUG', 0);
}

/**
 * Настройки для локального сервера.
 * Для использования - переименовать файл в config.local.php
 */

/**
 * Настройка базы данных
 */
$config['db']['params']['host'] = 'localhost';
$config['db']['params']['port'] = '3306';
$config['db']['params']['user'] = 'root';
$config['db']['params']['pass'] = '';
$config['db']['params']['type']   = 'mysqli';
$config['db']['params']['dbname'] = 'alto';
$config['db']['table']['prefix'] = 'prefix_';
$config['db']['tables']['engine'] = 'InnoDB';

/**
 * "Примеси" ("соли") для повышения безопасности хешируемых данных
 */
$config['security']['salt_sess'] = '6vmhcZ-Jn8mp%m4tB#ru2v3eL-fbean]v82uUnvFT+qEE07d!Q]hvBIF+58rz4?0';
$config['security']['salt_pass'] = 'UrDL>IZd0Ej:Vl-rVE>|X+SGZ[WL-$]|$vKErF4IW~XP[qsK|XkW1<Sj!MlG0?p8';
$config['security']['salt_auth'] = 'ICG^DeYPu~<0W@TE?65UTjIU?^uc~txn^17@cKux1Gl0H-)^N4Ltna3cK9tGhtum';

//$config['path']['root']['url'] = 'http://altocms.dev/';
//$config['path']['root']['dir'] = 'C:\www_server\OpenServer\domains\altocms.dev\www/';
$config['path']['offset_request_url'] = '0';

$_SERVER['HTTP_APP_ENV'] = 'test';

$config['view']['skin']        = 'native';                       // скин
$config['view']['name']        = ALTO_VERSION;              // название сайта

$config['sys']['cache']['use']    = false;               // использовать кеширование или нет
$config['sys']['cache']['type']   = 'file';             // тип кеширования: file, xcache и memory. memory использует мемкеш, xcache - использует XCache
//$config['sys']['cache']['type']   = 'memory'; // 'file';             // тип кеширования: file, xcache и memory. memory использует мемкеш, xcache - использует XCache


//$config['view']['skin']        = 'bootstrap';                       // скин

$config['compress']['css']['merge'] = false;         // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['css']['use']   = false;        // указывает на необходимость компрессии файлов. Компрессия используется только в активированном режиме слияния файлов.
$config['compress']['js']['merge']  = false;         // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['js']['use']    = false;        // указывает на необходимость компрессии файлов. Компрессия используется только в активированном режиме слияния файлов.

$config['compress']['js']['force']  = true;
$config['compress']['css']['force']  = true;

$config['router']['uri']['~^community\/?$~i'] = 'index/';
$config['router']['uri']['~^community/(\d+)\.html~i'] = 'blog/\\1.html';

return $config;

// EOF