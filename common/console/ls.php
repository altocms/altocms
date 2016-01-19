<?php

// Для эмуляции работы, т.к используется в конфиге
$_SERVER['HTTP_HOST'] = 'localhost';

defined('ALTO_DIR') || define('ALTO_DIR', dirname(realpath((dirname(__FILE__)) . "/../")));
set_include_path(get_include_path() . PATH_SEPARATOR . ALTO_DIR);
chdir(ALTO_DIR);

require_once(ALTO_DIR . '/engine/loader.php');
require_once(dirname(__FILE__) . '/lsc.php');


LSC::Start();
