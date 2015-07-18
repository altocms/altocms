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

/*
 *  ALTO_DIR    - root directory of current site
 *  ALTO_CORE   - root directory of Alto CMS scripts
 */
defined('ALTO_CORE') || define('ALTO_CORE', ALTO_DIR);

/**
 * Настройка путей для первичной загрузки
 */
$config = array();

$config['path']['dir']['engine']        = ALTO_CORE . '/engine/';           // Путь к папке движка
$config['path']['dir']['libs']          = ALTO_CORE . '/engine/libs/';      // Путь к библиотекам движка по умолчанию
$config['path']['dir']['common']        = ALTO_CORE . '/common/';           // Путь к общим компонентам по умолчанию
$config['path']['dir']['config']        = ALTO_CORE . '/common/config/';    // Путь к папке конфигурации по умолчанию
$config['path']['dir']['app']           = ALTO_DIR . '/app/';               // Путь к папке приложения по умолчанию

return $config;

// EOF