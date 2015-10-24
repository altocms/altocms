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
    'image_insert',  // Меню добавления изображений
    'profile_images',  // Меню изображений в профиле пользователя
    'footer_site_menu',  // Вторая колонка, меню сайта
    'footer_info',  // Третья колонка, информация
);

// Editable top menus
$config['editable'] = array('main', 'user', 'topics', 'login', 'footer_site_menu', 'footer_info');

// Настройки подменю, созданных в админке
$config['submenu'] = array(
    'class' => '',
    'options' => array(),
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
        'pre' => false,
        'user'         => array(
            'text' => '{{user_menu_profile}}',
            'link' => '___path.root.url___',
        ),
        'create'  => array(
            'text'    => '{{block_create}}',
            'link'    => '#',
            'options' => array(
                'data'  => array(
                    'toggle'       => 'modal',
                    'target'       => '#modal-write',
                ),
            ),
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
            'link' => '___path.root.url___/login/exit/?security_key=',
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
            'link'    => '___path.root.url___/index/new/',
            'active'  => array('topic_kind' => array('new')),
            'display' => array('new_topics_count'),
        ),

        'newall'               => array(
            'text'    => '{{blog_menu_all_new}}',
            'link'    => '___path.root.url___/index/newall/',
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
            'link'    => '___path.root.url___/index/discussed/',
            'active'  => array('topic_kind' => array('discussed')),
            'submenu' => 'discussed',
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
 *  Меню управления добавлением изображений
 */
$config['data']['image_insert'] = array(
    'init' => array(
        'cache' => false,
        'fill' => array(
            'list' => array('*'),
            'insert_image' => array()
        ),
    ),
    'actions' => array('ajax' => array(
        'image-manager-load-images',
        'image-manager-load-tree',
    )),
    'description' => '{{menu_image_insert_description}}',
    'list' => array(
        'insert_from_pc' => array(
            'text'    => array(
                '{{insertimg_from_pc}}'
            ),
            'link'   => '#',
            'active'  => true,
            'options' => array(
                'class'       => 'category-show active',
                'link_class'  => '',
                'link_data'   => array(
                    'category' => 'insert-from-pc',
                ),
            ),
        ),
        'insert_from_link' => array(
            'text'    => array(
                '{{insertimg_from_link}}'
            ),
            'link'   => '#',
            'options' => array(
                'class'       => 'category-show active',
                'link_class'  => '',
                'link_data'   => array(
                    'category' => 'insert-from-link',
                ),
            ),
        ),
        'menu_image_insert_item' => '',
    )
);
/**
 *  Меню управления добавлением изображений
 */
$config['data']['profile_images'] = array(
    'init' => array(
        'cache' => false,
        'fill' => array(
            'list' => array('*'),
            'insert_image' => array()
        ),
    ),
    'actions' => array('ajax' => array(
        'image-manager-load-images',
        'image-manager-load-tree',
    )),
    'protect' => true,
    'description' => '{{user_menu_publication_photos}}',
    'list' => array(
        'menu_image_insert_item' => '',
    )
);

/**
 * Меню подвала
 */
// Первая колонка, авторизация на сайте
$config['data']['footer_user_auth'] = array(
    'init'        => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_footer_user_auth_description}}',
    'list'        => array(
        'login'        => array(
            'text' => '{{user_login_submit}}',
            'link' => '___path.root.url___/login/',
        ),
        'registration' => array(
            'text' => '{{registration_submit}}',
            'link' => '___path.root.url___/registration/',
        ),
    )
);
// Первая колонка меню подвала, действия пользователя
$config['data']['footer_user_actions'] = array(
    'init'        => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_footer_user_actions_description}}',
    'list'        => array(
        'profile'               => array(
            'text' => '{{user_menu_profile}}',
            'link' => '___path.root.url___/',
        ),
        'settings'              => array(
            'text' => '{{user_settings}}',
            'link' => '___path.root.url___/settings/',
        ),
        'create'                => array(
            'text'    => '{{block_create}}',
            'link'    => '#',
            'options' => array(
                'data' => array(
                    'toggle' => 'modal',
                    'target' => '#modal-write',
                ),
            ),
        ),
        'footer_menu_user_item' => '',
        'logout'                => array(
            'text' => '{{exit}}',
            'link' => '___path.root.url___/login/exit/?security_key=',
        ),
    )
);
// Вторая колонка, меню сайта
$config['data']['footer_site_menu'] = array(
    'init'        => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_footer_site_menu}}',
    'list'        => array(
        'topic'                     => array(
            'text' => '{{topic_title}}',
            'link' => '___path.root.url___/',
        ),
        'blogs'                     => array(
            'text' => '{{blogs}}',
            'link' => '___path.root.url___/blogs/',
        ),
        'people'                    => array(
            'text' => '{{people}}',
            'link' => '___path.root.url___/people/',
        ),
        'stream_menu'               => array(
            'text' => '{{stream_menu}}',
            'link' => '___path.root.url___/stream/',
        ),
        'footer_menu_navigate_item' => '',
    )
);
// Третья колонка, информация
$config['data']['footer_info'] = array(
    'init'        => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => '{{menu_footer_info}}',
    'list'        => array(
        'about'                    => array(
            'text' => '{{footer_menu_project_about}}',
            'link' => '#',
        ),
        'rules'                    => array(
            'text' => '{{footer_menu_project_rules}}',
            'link' => '#',
        ),
        'advert'                   => array(
            'text' => '{{footer_menu_project_advert}}',
            'link' => '#',
        ),
        'help'                     => array(
            'text' => '{{footer_menu_project_help}}',
            'link' => '#',
        ),
        'footer_menu_project_item' => '',
    )
);