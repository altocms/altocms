<?php
/**
 * Файл конфигурации меню, использующихся на сайте.
 * Изменения в этот файл вносить не нужно, они должны
 * располагаться только в файле
 */

// Разрешенные для использования типы меню
$config['allowed'] = array(
    'main',     // Главное меню сайта
    'login',    // Авторизация на сайте
    'user',     // Меню пользователя
    'userbar',  // Выпадающее меню
    'topics',   // Меню топиков
    'discussed',// Подменю обсуждаемых топиков
    'top',      // Подменю популярных топиков
);

/**
 * Главное меню сайта
 * Приложение, шаблоны и плагины переопределяют и расширяют эти настройки
 */
$config['data']['main'] = array(
    'init'        => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_main_description}}',
    'list'        => array(
        'index'          => array(
            'text'        => '{{topic_title}}',
            'link'        => '___path.root.url___',
            'description' => '{{menu_main_index_description}}',
            'active'      => array('compare_action' => array('index', 'blog')),
        ),
        'blogs'          => array(
            'text'        => '{{blogs}}',
            'link'        => '___path.root.url___/blogs/',
            'description' => '{{menu_main_blogs_description}}',
            'active'      => array('compare_action' => array('blogs')),
        ),
        'people'         => array(
            'text'        => '{{people}}',
            'link'        => '___path.root.url___/people/',
            'description' => '{{menu_main_people_description}}',
            'active'      => array('compare_action' => array('people')),
        ),
        'stream'         => array(
            'text'        => '{{stream_menu}}',
            'link'        => '___path.root.url___/stream/',
            'description' => '{{menu_main_stream_description}}',
            'active'      => array('compare_action' => array('stream')),
        ),
        'main_menu_item' => '',
    )
);

/**
 *  Вход и регистрация
 */
$config['data']['login'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_login_submenu_description}}',
    'list' => array(
        'userbar_item' => '',
        'login'        => array(
            'text'    => '{{user_login_submit}}',
            'link'    => '___path.root.url___/login/',
        ),
        'registration' => array(
            'text'    => '{{registration_submit}}',
            'link'    => '___path.root.url___/registration/',
        ),
    )
);

/**
 *  Меню пользователя
 */
$config['data']['user'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_user_description}}',
    'list' => array(
        'talk'    => array(
            'text'   => '{{user_privat_messages_new}}',
            'link' => '___path.root.url___/talk/',
            'show'    => array('new_talk'),
        ),
        'userbar' => array(
            'text'   => '{{menu_empty_user_name}}',
            'link'    => '___path.root.url___',
            'submenu' => 'userbar',
        ),
    )
);

/**
 *  Подменю пользователя
 */
$config['data']['userbar'] = array(
    'init'  => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_user_submenu_description}}',
    'list'  => array(
        'user'         => array(
            'text' => '{{user_menu_profile}}',
            'link' => '___path.root.url___',
        ),
        'talk'         => array(
            'text'    => '{{user_privat_messages}}',
            'link'    => '___path.root.url___/talk/',
        ),
        'wall'         => array(
            'text' => '{{user_menu_profile_wall}}',
            'link' => '___path.root.url___/wall/',
        ),
        'publication'  => array(
            'text' => '{{user_menu_publication}}',
            'link' => '___path.root.url___',
        ),
        'favourites'   => array(
            'text' => '{{user_menu_profile_favourites}}',
            'link' => '___path.root.url___',
        ),
        'settings'     => array(
            'text' => '{{user_settings}}',
            'link' => '___path.root.url___/settings/',
        ),
        'userbar_item' => '',
        'logout'       => array(
            'text' => '{{exit}}',
            'link' => '___path.root.url___/talk/',
        ),
    )
);


/**
 *  Меню топиков
 */
$config['data']['topics'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_topics_submenu_description}}',
    'list' => array(
        'good'                 => array(
            'text'    => '{{blog_menu_all_good}}',
            'link'    => '___path.root.url___',
            'active'  => array('topic_kind' => array('good')),
        ),
        'new'                  => array(
            'text'    => '{{blog_menu_all_new}}',
            'link'    => '___path.root.url___/new/',
            'active'  => array('topic_kind' => array('new')),
            'display' => array('new_topics_count'),
        ),

        'newall'               => array(
            'text'    => '{{blog_menu_all_new}}',
            'link'    => '___path.root.url___/newall/',
            'active'  => array('topic_kind' => array('newall')),
            'display' => array('no_new_topics'),
        ),

        'feed'                 => array(
            'text'    => '{{userfeed_title}}',
            'link'    => '___path.root.url___/feed/',
            'active'  => array('compare_action' => array('feed')),
        ),

        'discussed'            => array(
            'text'    => '{{blog_menu_all_discussed}}',
            'link'    => '___path.root.url___/discussed/',
            'active'  => array('topic_kind' => array('discussed')),
            'submenu' => 'discussed',
        ),

        'top'                  => array(
            'text'    => '{{blog_menu_all_top}}',
            'link'    => '___path.root.url___/top/',
            'active'  => array('topic_kind' => array('top')),
            'submenu' => 'top',
        ),

        'menu_blog_index_item' => '',
    )
);

/**
 *  Подменю обсуждаемых
 */
$config['data']['discussed'] = array(
    'init'  => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list'  => array(
        '24h' => array(
            'text'   => '{{blog_menu_top_period_24h}}',
            'link'   => '___path.root.url___/index/discussed/?period=1',
            'active' => array('compare_param' => array(0, 1)),
        ),
        '7d'  => array(
            'text'   => '{{blog_menu_top_period_7d}}',
            'link'   => '___path.root.url___/index/discussed/?period=7',
            'active' => array('compare_param' => array(0, 7)),
        ),
        '30d' => array(
            'text'   => '{{blog_menu_top_period_30d}}',
            'link'   => '___path.root.url___/index/discussed/?period=30',
            'active' => array('compare_param' => array(0, 30)),
        ),
        'all' => array(
            'text'   => '{{blog_menu_top_period_all}}',
            'link'   => '___path.root.url___/index/discussed/?period=all',
            'active' => array('compare_param' => array(0, 'all')),
        ),

    )
);

/**
 *  Подменю топовых
 */
$config['data']['top'] = array(
    'init'  => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list'  => array(
        '24h' => array(
            'text'   => '{{blog_menu_top_period_24h}}',
            'link'   => '___path.root.url___/index/top/?period=1',
            'active' => array('compare_param' => array(0, 1)),
        ),
        '7d'  => array(
            'text'   => '{{blog_menu_top_period_7d}}',
            'link'   => '___path.root.url___/index/top/?period=7',
            'active' => array('compare_param' => array(0, 7)),
        ),
        '30d' => array(
            'text'   => '{{blog_menu_top_period_30d}}',
            'link'   => '___path.root.url___/index/top/?period=30',
            'active' => array('compare_param' => array(0, 30)),
        ),
        'all' => array(
            'text'   => '{{blog_menu_top_period_all}}',
            'link'   => '___path.root.url___/index/top/?period=all',
            'active' => array('compare_param' => array(0, 'all')),
        ),

    )
);