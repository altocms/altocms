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

    'user_add'            => '3',
    'user_remove'         => '1',

    'comment_user_add'    => '0.5',
    'comment_user_remove' => '-0.25',

    'blog_add'            => '0.75',
    'blog_user_remove'    => '-0.1',

    'topic_user_add'      => '1',
    'topic_user_remove'   => '-0.25',
    'topic_add'           => '0.25',


);

return $config;
