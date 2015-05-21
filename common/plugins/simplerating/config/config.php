<?php
/**
 * config.php
 * Файл конфига плагина Rating
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *              Является частью плагина Rating
 * @version     0.0.1 от 30.01.2015 15:30
 */
$config = array(

    'user'    => array(
        'vote'        => TRUE,
        'dislike'     => TRUE,
        'add'         => '3',
        'remove'      => '0',
    ),

    'comment' => array(
        'vote'        => TRUE,
        'dislike'     => TRUE,
        'user_add'    => '1',
        'user_remove' => '0',
    ),

    'blog'    => array(
        'vote'        => TRUE,
        'dislike'     => TRUE,
        'add'         => '3',
        'user_remove' => '0',
    ),

    'topic'   => array(
        'vote'        => TRUE,
        'dislike'     => TRUE,
        'user_add'    => '2',
        'user_remove' => '0',
        'add'         => '1',
    ),


);
Config::Set('rating.enabled', true);


return $config;
