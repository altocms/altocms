<?php

$config['menu']['allowed'] = array_merge(
    Config::Get('menu.allowed'),
    array(
        'blog_list',
    )
);

/**
 * Главное меню сайта  + experience
 */
$config['menu']['data']['main'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list' => array(
        'index'          => array(
            'text'    => '{{topic_title}}',
            'link'    => '___path.root.url___',
            'active'  => array(
                'compare_action' => array('index', 'blog')
            ),
            'options' => array(
                'icon_class' => 'fa fa-file-text-o',
            )
        ),
        'blogs'          => array(
            'text'    => '{{blogs}}',
            'link'    => Router::GetPath('blogs'),
            'active'  => array('compare_action' => array('blogs')),
            'options' => array(
                'icon_class' => 'fa fa-comment-o',
            )
        ),
        'people'         => array(
            'text'    => '{{people}}',
            'link'    => Router::GetPath('people'),
            'active'  => array('compare_action' => array('people')),
            'options' => array(
                'icon_class' => 'fa fa-circle-o',
            )
        ),
        'stream'         => array(
            'text'    => '{{stream_menu}}',
            'link'    => Router::GetPath('stream'),
            'active'  => array('compare_action' => array('stream')),
            'options' => array(
                'icon_class' => 'fa fa-signal',
            )
        ),
        'main_menu_item' => '',
    )
);

/**
 *  Вход и регистрация  + experience
 */
$config['menu']['data']['login'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list' => array(
        'userbar_item' => '',
        'login'        => array(
            'text'    => '{{user_login_submit}}',
            'link'    => '#',
            'options' => array(
                'class'      => 'btn right',
                'data'       => array(
                    'hidden-class' => 'btn-right',
                ),
                'link_class' => 'js-modal-auth-login'
            ),
        ),
        'registration' => array(
            'text'    => '{{registration_submit}}',
            'link'    => '#',
            'options' => array(
                'class'      => 'btn right',
                'data'       => array(
                    'hidden-class' => 'btn-right',
                ),
                'link_class' => 'js-modal-auth-registration',
            ),
        ),
    )
);

if (E::IsUser()) {

    /**
     *  Меню пользователя + experience
     */
    $config['menu']['data']['user'] = array(
        'init' => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'list' => array(
            'userbar' => array(
                'text'    => array(
                    'user_name' => array(),
                    '&nbsp;<i class="caret"></i>'
                ),
                'link'    => E::User()->getProfileUrl(),
                'submenu' => 'userbar',
                'options' => array(
                    'class'       => 'btn dropdown right',
                    'link_class'  => 'userlogo',
                    'image_url'   => array('user_avatar_url' => array('24')),
                    'image_title' => array('user_name'),
                    'image_class' => 'user',
                    'link_data'   => array(
                        'toggle' => 'dropdown',
                        'target' => '#',
                    ),
                    'data'        => array(
                        'hidden-class' => 'btn',
                    ),
                ),
            ),
            'talk'    => array(
                'text'    => array(
                    '<i class="fa fa-envelope-o"></i>&nbsp;+',
                    'new_talk' => array(),
                ),
                'link'    => Router::GetPath('talk'),
                'show'    => array('new_talk'),
                'options' => array(
                    'class'      => 'btn right inbox',
                    'link_class' => 'messages',
                    'data'       => array(
                        'hidden-class' => 'btn right',
                    ),
                ),
            ),
            'create'  => array(
                'text'    => '{{block_create}}',
                'link'    => '#',
                'options' => array(
                    'class' => 'btn right create',
                    'data'  => array(
                        'hidden-class' => 'btn right',
                        'toggle'       => 'modal',
                        'target'       => '#modal-write',
                    ),
                ),
            ),
        )
    );

    /**
     *  Подменю пользователя + experience
     */
    $config['menu']['data']['userbar'] = array(
        'init'  => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'class' => 'dropdown-menu',
        'list'  => array(
            'user'         => array(
                'text' => '{{user_menu_profile}}',
                'link' => E::User()->getProfileUrl(),
            ),
            'talk'         => array(
                'text'    => array(
                    '{{user_privat_messages}}',
                    '&nbsp;<span class="new-messages">',
                    'new_talk_string' => array(),
                    '</span>'
                ),
                'link'    => Router::GetPath('talk'),
                'options' => array(
                    'link_id'    => 'new_messages',
                    'link_title' => array('new_talk' => array())
                )
            ),
            'wall'         => array(
                'text' => '{{user_menu_profile_wall}}',
                'link' => E::User()->getProfileUrl() . 'wall/',
            ),
            'publication'  => array(
                'text' => '{{user_menu_publication}}',
                'link' => E::User()->getProfileUrl() . 'created/topics/',
            ),
            'favourites'   => array(
                'text' => '{{user_menu_profile_favourites}}',
                'link' => E::User()->getProfileUrl() . 'favourites/topics/',
            ),
            'settings'     => array(
                'text' => '{{user_settings}}',
                'link' => E::User()->getProfileUrl() . 'profile/',
            ),
            'userbar_item' => '',
            'logout'       => array(
                'text' => '{{exit}}',
                'link' => Router::GetPath('login') . 'exit/?security_key=' . E::Security_GetSecurityKey(),
            ),
        )
    );
}


/**
 *  Меню топиков
 */
$config['menu']['data']['topics'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list' => array(
        'homepage'             => array(
            'text'    => '{{menu_homepage}}',
            'link'    => '___path.root.url___',
            'active'  => array('topic_kind' => array('good')),
            'options' => array(
                'class' => 'btn active',
                'data'  => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),
        'new'                  => array(
            'text'    => array(
                '{{blog_menu_all_new}} + ',
                'new_topics_count' => array(),
            ),
            'link'    => Router::GetPath('index') . 'new/',
            'active'  => array('topic_kind' => array('new')),
            'display' => array('new_topics_count'),
            'options' => array(
                'class'      => 'btn',
                'link_title' => '{{blog_menu_top_period_24h}}',
                'data'       => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),

        'newall'               => array(
            'text'    => '{{blog_menu_all_new}}',
            'link'    => Router::GetPath('index') . 'newall/',
            'active'  => array('topic_kind' => array('newall')),
            'display' => array('no_new_topics'),
            'options' => array(
                'class'      => 'btn',
                'link_title' => '{{blog_menu_top_period_24h}}',
                'data'       => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),

        'feed'                 => array(
            'text'    => '{{userfeed_title}}',
            'link'    => Router::GetPath('feed'),
            'active'  => array('compare_action' => array('feed')),
            'options' => array(
                'class' => 'btn final',
                'data'  => array(
                    'hidden-class' => 'btn final',
                ),
            )
        ),


        'discussed'            => array(
            'text'    => array(
                '{{blog_menu_all_discussed}}',
                '&nbsp;<i class="caret"></i>',
            ),
            'link'    => Router::GetPath('index') . 'discussed/',
            'active'  => array('topic_kind' => array('discussed')),
            'submenu' => 'discussed',
            'options' => array(
                'class'     => 'btn dropdown active',
                'link_data' => array(
                    'toggle' => 'dropdown',
                ),
                'data'      => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),

        'top'                  => array(
            'text'    => array(
                '{{blog_menu_all_top}}',
                '&nbsp;<i class="caret"></i>',
            ),
            'link'    => Router::GetPath('index') . 'top/',
            'active'  => array('topic_kind' => array('top')),
            'submenu' => 'top',
            'options' => array(
                'class'     => 'btn dropdown active',
                'link_data' => array(
                    'toggle' => 'dropdown',
                ),
                'data'      => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),

        'menu_blog_index_item' => '',
    )
);

/**
 *  Подменю обсуждаемых  + experience
 */
$config['menu']['data']['discussed'] = array(
    'init'  => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'class' => 'dropdown-menu',
    'list'  => array(
        '24h' => array(
            'text'   => '{{blog_menu_top_period_24h}}',
            'link'   => Router::GetPath('index') . 'discussed/?period=1',
            'active' => array('compare_param' => array(0, 1)),
        ),
        '7d'  => array(
            'text'   => '{{blog_menu_top_period_7d}}',
            'link'   => Router::GetPath('index') . 'discussed/?period=7',
            'active' => array('compare_param' => array(0, 7)),
        ),
        '30d' => array(
            'text'   => '{{blog_menu_top_period_30d}}',
            'link'   => Router::GetPath('index') . 'discussed/?period=30',
            'active' => array('compare_param' => array(0, 30)),
        ),
        'all' => array(
            'text'   => '{{blog_menu_top_period_all}}',
            'link'   => Router::GetPath('index') . 'discussed/?period=all',
            'active' => array('compare_param' => array(0, 'all')),
        ),

    )
);

/**
 *  Подменю топовых  + experience
 */
$config['menu']['data']['top'] = array(
    'init'  => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'class' => 'dropdown-menu',
    'list'  => array(
        '24h' => array(
            'text'   => '{{blog_menu_top_period_24h}}',
            'link'   => Router::GetPath('index') . 'top/?period=1',
            'active' => array('compare_param' => array(0, 1)),
        ),
        '7d'  => array(
            'text'   => '{{blog_menu_top_period_7d}}',
            'link'   => Router::GetPath('index') . 'top/?period=7',
            'active' => array('compare_param' => array(0, 7)),
        ),
        '30d' => array(
            'text'   => '{{blog_menu_top_period_30d}}',
            'link'   => Router::GetPath('index') . 'top/?period=30',
            'active' => array('compare_param' => array(0, 30)),
        ),
        'all' => array(
            'text'   => '{{blog_menu_top_period_all}}',
            'link'   => Router::GetPath('index') . 'top/?period=all',
            'active' => array('compare_param' => array(0, 'all')),
        ),

    )
);

/**
 * Сеню с сыллками на блоги
 */
$config['menu']['data']['blog_list'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list' => array(
        'link1'          => array(
            'text'    => 'Дизайн',
            'link'    => '___path.root.url___',
        ),

        'link2'          => array(
            'text'    => 'Техника',
            'link'    => '___path.root.url___',
        ),

        'link3'          => array(
            'text'    => 'Смартфоны',
            'link'    => '___path.root.url___',
        ),

        'link4'          => array(
            'text'    => 'Приложения',
            'link'    => '___path.root.url___',
        ),

        'link5'          => array(
            'text'    => 'Спорт',
            'link'    => '___path.root.url___',
        ),

        'link6'          => array(
            'text'    => 'Новости',
            'link'    => '___path.root.url___',
        ),

        'link7'          => array(
            'text'    => 'Технологии',
            'link'    => '___path.root.url___',
        ),

    )
);