<?php
/**
 * Файл конфигурации меню, использующихся на сайте.
 * Изменения в этот файл вносить не нужно, они должны
 * располагаться только в файле
 */

// Разрешенные для использования типы меню
$config['allowed'] = [
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
];

// Editable top menus
$config['editable'] = ['main', 'user', 'topics', 'login', 'footer_site_menu', 'footer_info'];

// Настройки подменю, созданных в админке
$config['submenu'] = [
    'class' => '',
    'options' => [],
];

/**
 * Главное меню сайта
 * Приложение, шаблоны и плагины переопределяют и расширяют эти настройки
 */
$config['data']['main'] = [
    'init'        => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_main_description}}',
    'list'        => [
        'index'          => [
            'text'        => '{{topic_title}}',
            'link'        => '___path.root.url___',
            'description' => '{{menu_main_index_description}}',
            'active'      => ['compare_action' => ['index', 'blog']],
        ],
        'blogs'          => [
            'text'        => '{{blogs}}',
            'link'        => '___path.root.url___/blogs/',
            'description' => '{{menu_main_blogs_description}}',
            'active'      => ['compare_action' => ['blogs']],
        ],
        'people'         => [
            'text'        => '{{people}}',
            'link'        => '___path.root.url___/people/',
            'description' => '{{menu_main_people_description}}',
            'active'      => ['compare_action' => ['people']],
        ],
        'stream'         => [
            'text'        => '{{stream_menu}}',
            'link'        => '___path.root.url___/stream/',
            'description' => '{{menu_main_stream_description}}',
            'active'      => ['compare_action' => ['stream']],
        ],
        'main_menu_item' => '',
    ]
];

/**
 *  Вход и регистрация
 */
$config['data']['login'] = [
    'init' => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_login_submenu_description}}',
    'list' => [
        'userbar_item' => '',
        'login'        => [
            'text'    => '{{user_login_submit}}',
            'link'    => '___path.root.url___/login/',
        ],
        'registration' => [
            'text'    => '{{registration_submit}}',
            'link'    => '___path.root.url___/registration/',
        ],
    ]
];

/**
 *  Меню пользователя
 */
$config['data']['user'] = [
    'init' => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_user_description}}',
    'list' => [
        'talk'    => [
            'text'   => '{{user_privat_messages_new}}',
            'link' => '___path.root.url___/talk/',
            'show'    => ['new_talk'],
        ],
        'userbar' => [
            'text'   => '{{menu_empty_user_name}}',
            'link'    => '___path.root.url___',
            'submenu' => 'userbar',
        ],
    ]
];

/**
 *  Подменю пользователя
 */
$config['data']['userbar'] = [
    'init'  => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_user_submenu_description}}',
    'list'  => [
        'pre' => false,
        'user'         => [
            'text' => '{{user_menu_profile}}',
            'link' => '___path.root.url___',
        ],
        'create'  => [
            'text'    => '{{block_create}}',
            'link'    => '#',
            'options' => [
                'data'  => [
                    'toggle'       => 'modal',
                    'target'       => '#modal-write',
                ],
            ],
        ],
        'talk'         => [
            'text'    => '{{user_privat_messages}}',
            'link'    => '___path.root.url___/talk/',
        ],
        'wall'         => [
            'text' => '{{user_menu_profile_wall}}',
            'link' => '___path.root.url___/wall/',
        ],
        'publication'  => [
            'text' => '{{user_menu_publication}}',
            'link' => '___path.root.url___',
        ],
        'favourites'   => [
            'text' => '{{user_menu_profile_favourites}}',
            'link' => '___path.root.url___',
        ],
        'settings'     => [
            'text' => '{{user_settings}}',
            'link' => '___path.root.url___/settings/',
        ],
        'userbar_item' => '',
        'logout'       => [
            'text' => '{{exit}}',
            'link' => '___path.root.url___/login/exit/?security_key=',
        ],
    ]
];


/**
 *  Меню топиков
 */
$config['data']['topics'] = [
    'init' => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_topics_submenu_description}}',
    'list' => [
        'good'                 => [
            'text'    => '{{blog_menu_all_good}}',
            'link'    => '___path.root.url___',
            'active'  => ['topic_kind' => ['good']],
        ],
        'new'                  => [
            'text'    => '{{blog_menu_all_new}}',
            'link'    => '___path.root.url___/index/new/',
            'active'  => ['topic_kind' => ['new']],
            'display' => ['new_topics_count'],
        ],

        'newall'               => [
            'text'    => '{{blog_menu_all_new}}',
            'link'    => '___path.root.url___/index/newall/',
            'active'  => ['topic_kind' => ['newall']],
            'display' => ['no_new_topics'],
        ],

        'feed'                 => [
            'text'    => '{{userfeed_title}}',
            'link'    => '___path.root.url___/feed/',
            'active'  => ['compare_action' => ['feed']],
        ],

        'discussed'            => [
            'text'    => '{{blog_menu_all_discussed}}',
            'link'    => '___path.root.url___/index/discussed/',
            'active'  => ['topic_kind' => ['discussed']],
            'submenu' => 'discussed',
        ],

        'menu_blog_index_item' => '',
    ]
];

/**
 *  Подменю обсуждаемых
 */
$config['data']['discussed'] = [
    'init'  => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'list'  => [
        '24h' => [
            'text'   => '{{blog_menu_top_period_24h}}',
            'link'   => '___path.root.url___/index/discussed/?period=1',
            'active' => ['compare_get_param' => ['period', 1]],
        ],
        '7d'  => [
            'text'   => '{{blog_menu_top_period_7d}}',
            'link'   => '___path.root.url___/index/discussed/?period=7',
            'active' => ['compare_get_param' => ['period', 7]],
        ],
        '30d' => [
            'text'   => '{{blog_menu_top_period_30d}}',
            'link'   => '___path.root.url___/index/discussed/?period=30',
            'active' => ['compare_get_param' => ['period', 30]],
        ],
        'all' => [
            'text'   => '{{blog_menu_top_period_all}}',
            'link'   => '___path.root.url___/index/discussed/?period=all',
            'active' => ['compare_get_param' => ['period', 'all']],
        ],

    ]
];

/**
 *  Меню управления добавлением изображений
 */
$config['data']['image_insert'] = [
    'init' => [
        'cache' => false,
        'fill' => [
            'list' => ['*'],
            'insert_image' => []
        ],
    ],
    'actions' => ['ajax' => [
        'image-manager-load-images',
        'image-manager-load-tree',
    ]],
    'description' => '{{menu_image_insert_description}}',
    'list' => [
        'insert_from_pc' => [
            'text'    => [
                '{{insertimg_from_pc}}'
            ],
            'link'   => '#',
            'active'  => true,
            'options' => [
                'class'       => 'category-show active',
                'link_class'  => '',
                'link_data'   => [
                    'category' => 'insert-from-pc',
                ],
            ],
        ],
        'insert_from_link' => [
            'text'    => [
                '{{insertimg_from_link}}'
            ],
            'link'   => '#',
            'options' => [
                'class'       => 'category-show active',
                'link_class'  => '',
                'link_data'   => [
                    'category' => 'insert-from-link',
                ],
            ],
        ],
        'menu_image_insert_item' => '',
    ]
];
/**
 *  Меню управления добавлением изображений
 */
$config['data']['profile_images'] = [
    'init' => [
        'cache' => false,
        'fill' => [
            'list' => ['*'],
            'insert_image' => []
        ],
    ],
    'actions' => ['ajax' => [
        'image-manager-load-images',
        'image-manager-load-tree',
    ]],
    'protect' => true,
    'description' => '{{user_menu_publication_photos}}',
    'list' => [
        'menu_image_insert_item' => '',
    ]
];

/**
 * Меню подвала
 */
// Первая колонка, авторизация на сайте
$config['data']['footer_user_auth'] = [
    'init'        => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_footer_user_auth_description}}',
    'list'        => [
        'login'        => [
            'text' => '{{user_login_submit}}',
            'link' => '___path.root.url___/login/',
        ],
        'registration' => [
            'text' => '{{registration_submit}}',
            'link' => '___path.root.url___/registration/',
        ],
    ]
];
// Первая колонка меню подвала, действия пользователя
$config['data']['footer_user_actions'] = [
    'init'        => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_footer_user_actions_description}}',
    'list'        => [
        'profile'               => [
            'text' => '{{user_menu_profile}}',
            'link' => '___path.root.url___/',
        ],
        'settings'              => [
            'text' => '{{user_settings}}',
            'link' => '___path.root.url___/settings/',
        ],
        'create'                => [
            'text'    => '{{block_create}}',
            'link'    => '#',
            'options' => [
                'data' => [
                    'toggle' => 'modal',
                    'target' => '#modal-write',
                ],
            ],
        ],
        'footer_menu_user_item' => '',
        'logout'                => [
            'text' => '{{exit}}',
            'link' => '___path.root.url___/login/exit/?security_key=',
        ],
    ]
];
// Вторая колонка, меню сайта
$config['data']['footer_site_menu'] = [
    'init'        => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_footer_site_menu}}',
    'list'        => [
        'topic'                     => [
            'text' => '{{topic_title}}',
            'link' => '___path.root.url___/',
        ],
        'blogs'                     => [
            'text' => '{{blogs}}',
            'link' => '___path.root.url___/blogs/',
        ],
        'people'                    => [
            'text' => '{{people}}',
            'link' => '___path.root.url___/people/',
        ],
        'stream_menu'               => [
            'text' => '{{stream_menu}}',
            'link' => '___path.root.url___/stream/',
        ],
        'footer_menu_navigate_item' => '',
    ]
];
// Третья колонка, информация
$config['data']['footer_info'] = [
    'init'        => [
        'fill' => [
            'list' => ['*'],
        ],
    ],
    'description' => '{{menu_footer_info}}',
    'list'        => [
        'about'                    => [
            'text' => '{{footer_menu_project_about}}',
            'link' => '#',
        ],
        'rules'                    => [
            'text' => '{{footer_menu_project_rules}}',
            'link' => '#',
        ],
        'advert'                   => [
            'text' => '{{footer_menu_project_advert}}',
            'link' => '#',
        ],
        'help'                     => [
            'text' => '{{footer_menu_project_help}}',
            'link' => '#',
        ],
        'footer_menu_project_item' => '',
    ]
];