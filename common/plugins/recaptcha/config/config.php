<?php

/**
 * config.php
 * Файл конфигурационных параметров плагина Recaptcha
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Воронов
 *              Является частью плагина Recaptcha
 * @version     0.0.1 от 08.01.2014 19:04
 */

$config = array(

    // Публичный ключ,
    'public_key' => '',

    // Секретный ключ
    'secret_key' => '',

    // Тёмный стиль
    'dark_theme' => false,

    // Аудиокапча
    'audio' => false,

);

// Экшен капчи уже не нужен, выпилим его
Config::Set('router.page.captcha', 'ActionError');
Config::Set('captcha', FALSE);

return $config;
