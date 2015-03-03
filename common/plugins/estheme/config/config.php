<?php
/**
 * config.php
 * Файл конфигурационных параметров плагина Estheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Estheme
 * @version     0.0.1 от 27.02.2015 08:56
 */


/**
 * Роутеры плагина
 */
//Config::Set('router.page.estheme', 'PluginEstheme_ActionEstheme'); // Админка

/**
 * Параметры плагина
 */
$aConfig = array(

    'path_for_download' => '___path.root.dir______path.uploads.root___/estheme/',

    'use_client' => true,

    'color'   => array(
        'main'  => array(
            'color'       => '#333333',
            'light'       => '#4d4d4d',
            'dark'        => '#1a1a1a',
            'dark_2'      => '#0d0d0d',
            'font'        => '#333333',
            'active_link' => '#1a1a1a',
        ),
        'other' => array(
            'gray'       => '#555555',
            'blue'       => '#4b8bbc',
            'light_blue' => '#669cc6',
            'red'        => '#c43a3a',
            'green'      => '#57a839',
            'orange'     => '#e68f12',
        ),
    ),

    'metrics' => array(
        'main' => array(
            'width'               => '1030',
            'menu_main_height'    => '52',
            'menu_content_height' => '46',
            'font_size'           => '14',
            'font_size_small'     => '13',
            'h1'                  => '24',
            'h2'                  => '22',
            'h3'                  => '22',
            'h4'                  => '18',
            'h5'                  => '16',
            'h6'                  => '14',
        ),
    )

);

return $aConfig;