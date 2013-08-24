<?php

/**
 * Настройка путей для первичной загрузки
 */
$sRootDir = dirname(__DIR__);
$config = array();

$config['path']['dir']['engine']        = $sRootDir . '/engine/';           // Путь к папке движка
$config['path']['dir']['libs']          = $sRootDir . '/engine/libs/';      // Путь к библиотекам движка
$config['path']['dir']['common']        = $sRootDir . '/common/';           // Путь к общим компонентам
$config['path']['dir']['config']        = $sRootDir . '/common/config/';    // Путь к папке конфигурации
$config['path']['dir']['app']           = $sRootDir . '/app/';              // Путь к папке приложения

return $config;

// EOF