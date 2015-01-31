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

    'user_min_change'       => '0.42',
    'user_max_change'       => '3.2',
    'user_max_rating'       => '500',
    'user_right_border'     => '200',
    'user_left_border'      => '50',
    'user_left_divider'     => '70',
    'user_mid_divider'      => '40',
    'user_right_divider'    => '2',

    'blog_min_change'       => '0.130',
    'blog_max_change'       => '15',
    'blog_max_rating'       => '500',
    'blog_right_border'     => '200',
    'blog_left_border'      => '50',
    'blog_left_divider'     => '50',
    'blog_mid_divider'      => '20',
    'blog_right_divider'    => '10',

    'comment_min_change'    => '0.004',
    'comment_max_change'    => '0.5',
    'comment_max_rating'    => '500',
    'comment_right_border'  => '200',
    'comment_left_border'   => '50',
    'comment_left_divider'  => '130',
    'comment_mid_divider'   => '70',
    'comment_right_divider' => '10',

    'topic_min_change'      => '0.1',
    'topic_max_change'      => '8',
    'topic_max_rating'      => '500',
    'topic_right_border'    => '200',
    'topic_left_border'     => '50',
    'topic_left_divider'    => '100',
    'topic_mid_divider'     => '70',
    'topic_right_divider'   => '10',
    'topic_auth_coef'       => '2.73',

    'rating_topic_border_1' => '100',
    'rating_topic_border_2' => '250',
    'rating_topic_border_3' => '400',
    'rating_topic_k1'       => '1',
    'rating_topic_k2'       => '2',
    'rating_topic_k3'       => '3',
    'rating_topic_k4'       => '4',

);

return $config;
