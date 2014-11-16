<?php

/**
 * Главное меню сайта
 */
$config['menu']['data']['main'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'list' => array(
        'index'          => array(
            'text'   => '{{topic_title}}',
            'link'   => '___path.root.url___',
            'active' => array(
                'compare_action' => array('index', 'blog')
            ),
        ),
        'blogs'          => array(
            'text'   => '{{blogs}}',
            'link'   => Router::GetPath('blogs'),
            'active' => array('compare_action' => array('blogs')),
        ),
        'people'         => array(
            'text'   => '{{people}}',
            'link'   => Router::GetPath('people'),
            'active' => array('compare_action' => array('people')),
        ),
        'stream'         => array(
            'text'   => '{{stream_menu}}',
            'link'   => Router::GetPath('stream'),
            'active' => array('compare_action' => array('stream')),
        ),
        'main_menu_item' => '',
    )
);

/**
 *  Вход и регистрация
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
            'link'    => Router::GetPath('login'),
            'options' => array(
                'link_class' => 'js-modal-auth-login'
            ),
        ),
        'registration' => array(
            'text'    => '{{registration_submit}}',
            'link'    => Router::GetPath('registration'),
            'options' => array(
                'class'      => 'hidden-sm',
                'link_class' => 'js-modal-auth-registration',
            ),
        ),
    )
);

if (E::IsUser()) {
    /**
     *  Меню пользователя
     */
    $config['menu']['data']['user'] = array(
        'init' => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'list' => array(
            'talk'    => array(
                'text'    => array(
                    '<span class="glyphicon glyphicon-envelope"></span>&nbsp;+',
                    'new_talk' => array(),
                ),
                'title'   => '{{user_privat_messages_new}}',
                'link' => Router::GetPath('talk'),
                'show'    => array('new_talk'),
                'options' => array(
                    'link_class' => 'new-messages'
                ),
            ),
            'userbar' => array(
                'text'    => array(
                    'user_name' => array(),
                    '<b class="caret"></b>'
                ),
                'link'    => E::User()->getProfileUrl(),
                'submenu' => 'userbar',
                'options' => array(
                    'class'       => 'dropdown nav-userbar',
                    'link_class'  => 'dropdown-toggle username',
                    'image_url'   => array('user_avatar_url' => array('32')),
                    'image_title' => array('user_name'),
                    'image_class' => 'avatar',
                    'link_data'   => array(
                        'toggle' => 'dropdown',
                        'target' => '#',
                    ),
                ),
            ),
        )
    );

    /**
     *  Подменю пользователя
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
                    '<span class="new-messages">',
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
                'link' => E::User()->getProfileUrl() . 'settings/',
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
        'good'                 => array(
            'text'    => '{{blog_menu_all_good}}',
            'link'    => '___path.root.url___',
            'active'  => array('topic_kind' => array('good')),
            'options' => array(
                'class' => 'bordered',
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
                'class'      => 'bordered',
                'link_title' => '{{blog_menu_top_period_24h}}'
            )
        ),

        'newall'               => array(
            'text'    => '{{blog_menu_all_new}}',
            'link'    => Router::GetPath('index') . 'newall/',
            'active'  => array('topic_kind' => array('newall')),
            'display' => array('no_new_topics'),
            'options' => array(
                'class'      => 'bordered',
                'link_title' => '{{blog_menu_top_period_24h}}'
            )
        ),

        'feed'                 => array(
            'text'    => '{{userfeed_title}}',
            'link'    => Router::GetPath('feed'),
            'active'  => array('compare_action' => array('feed')),
            'options' => array(
                'class' => 'bordered',
            )
        ),

        'empty'                => array(
            'text' => false,
            'options' => array(
                'class'      => 'divider',
            ),
        ),

        'discussed'            => array(
            'text'    => array(
                '{{blog_menu_all_discussed}}',
                '<b class="caret"></b>',
            ),
            'link'    => Router::GetPath('index') . 'discussed/',
            'active'  => array('topic_kind' => array('discussed')),
            'submenu' => 'discussed',
            'options' => array(
                'class'      => 'dropdown',
                'link_class' => 'dropdown-toggle',
                'link_data'  => array(
                    'toggle' => 'dropdown',
                )
            )
        ),

        'top'                  => array(
            'text'    => array(
                '{{blog_menu_all_top}}',
                '<b class="caret"></b>',
            ),
            'link'    => Router::GetPath('index') . 'top/',
            'active'  => array('topic_kind' => array('top')),
            'submenu' => 'top',
            'options' => array(
                'class'      => 'dropdown',
                'link_class' => 'dropdown-toggle',
                'link_data'  => array(
                    'toggle' => 'dropdown',
                )
            )
        ),

        'menu_blog_index_item' => '',
    )
);

/**
 *  Подменю обсуждаемых
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
 *  Подменю топовых
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