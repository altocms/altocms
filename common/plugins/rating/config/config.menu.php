<?php

$config['$root$']['menu']['data']['topics']['list']['top'] = array(
    'text'    => '{{blog_menu_all_top}}',
    'link'    => '___path.root.url___/index/top/',
    'active'  => array('topic_kind' => array('top')),
    'submenu' => 'top',
    'display' => 'conf:rating.enabled',
);

/**
 *  Подменю топовых
 */
$config['$root$']['menu']['data']['top'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list' => array(
        '24h' => array(
            'text'   => '{{blog_menu_top_period_24h}}',
            'link'   => '___path.root.url___/index/top/?period=1',
            'active' => array('compare_get_param' => array('period', 1)),
        ),
        '7d'  => array(
            'text'   => '{{blog_menu_top_period_7d}}',
            'link'   => '___path.root.url___/index/top/?period=7',
            'active' => array('compare_get_param' => array('period', 7)),
        ),
        '30d' => array(
            'text'   => '{{blog_menu_top_period_30d}}',
            'link'   => '___path.root.url___/index/top/?period=30',
            'active' => array('compare_get_param' => array('period', 30)),
        ),
        'all' => array(
            'text'   => '{{blog_menu_top_period_all}}',
            'link'   => '___path.root.url___/index/top/?period=all',
            'active' => array('compare_get_param' => array('period', 'all')),
        ),

    ),
);

return $config;

// EOF