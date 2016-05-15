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
        'vote'          => TRUE,
        'dislike'       => TRUE,
        'min_change'    => '0.42',
        'max_change'    => '3.2',
        'max_rating'    => '500',
        'right_border'  => '200',
        'left_border'   => '50',
        'left_divider'  => '70',
        'mid_divider'   => '40',
        'right_divider' => '2',
    ),

    'blog'    => array(
        'vote'          => TRUE,
        'dislike'       => TRUE,
        'min_change'    => '0.130',
        'max_change'    => '15',
        'max_rating'    => '500',
        'right_border'  => '200',
        'left_border'   => '50',
        'left_divider'  => '50',
        'mid_divider'   => '20',
        'right_divider' => '10',
    ),

    'comment' => array(
        'vote'          => TRUE,
        'dislike'       => TRUE,
        'min_change'    => '0.004',
        'max_change'    => '0.5',
        'max_rating'    => '500',
        'right_border'  => '200',
        'left_border'   => '50',
        'left_divider'  => '130',
        'mid_divider'   => '70',
        'right_divider' => '10',
    ),

    'topic'   => array(
        'vote'          => TRUE,
        //'dislike'       => TRUE,
        'min_change'    => '0.1',
        'max_change'    => '8',
        'max_rating'    => '500',
        'right_border'  => '200',
        'left_border'   => '50',
        'left_divider'  => '100',
        'mid_divider'   => '70',
        'right_divider' => '10',
        'auth_coef'     => '2.73',
    ),

    'rating'  => array(
        'vote'           => TRUE,
        'topic_border_1' => '100',
        'topic_border_2' => '250',
        'topic_border_3' => '400',
        'topic_k1'       => '1',
        'topic_k2'       => '2',
        'topic_k3'       => '3',
        'topic_k4'       => '4',
    ),


);

Config::Set('rating.enabled', true);

return $config;
