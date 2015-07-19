<?php

//$config['allowed'] = array_merge(
//    Config::Get('menu.allowed'),
//    array(
//        'blog_list',
//    )
//);

$config['submenu'] = array(
    'class' => 'dropdown-menu animated fadeIn dropdown-content-menu',
    'options' => array(
        'class'       => 'dropdown dropdown-submenu',
        'link_class'  => 'dropdown-toggle',
        'link_data'   => array(
            'toggle' => 'dropdown',
            'role'   => 'button',
            'target' => '#',
        ),
    ),
);

/**
 * Главное меню сайта  + experience
 * Настройки берутся из главного конфига меню common/config/menu.php
 * Добавлены классы иконок
 */
$config['data']['main'] = array(
    'list' => array(
        'index'  => array(
            'options' => array(
//                'icon_class' => 'fa fa-file-text-o',
            )
        ),
        'blogs'  => array(
            'options' => array(
//                'icon_class' => 'fa fa-comment-o',
            )
        ),
        'people' => array(
            'options' => array(
//                'icon_class' => 'fa fa-circle-o',
            )
        ),
        'stream' => array(
            'options' => array(
//                'icon_class' => 'fa fa-signal',
            )
        ),
    )
);

/**
 *  Вход и регистрация  + experience
 */
$config['data']['login'] = array(
    'list' => array(
        'login'        => array(
            'link'    => '#',
            'options' => array(
                'link_class' => 'js-modal-auth-login'
            ),
        ),
        'registration' => array(
            'link'    => '#',
            'options' => array(
                'link_class' => 'js-modal-auth-registration',
            ),
        ),
    )
);

if (E::IsUser()) {
    /**
     *  Меню пользователя + experience
     */
    $config['data']['user'] = array(
        'list' => array(
            'userbar' => array(
                'text'    => array(
                    'user_name' => array(),
                    'count_messages'  => array($sTemplate = '<span class="badge badge-danger badge-mail-counter">+{{count_messages}}</span>'),
                    '&nbsp;<span class="caret"></span>'
                ),
                'options' => array(
                    'class'       => 'dropdown',
                    'link_class'  => 'dropdown-toggle user-button',
                    'image_url'   => array('user_avatar_url' => array('32x32crop')),
                    'image_title' => array('user_name'),
                    'image_class' => 'user',
                    'link_data'   => array(
                        'toggle' => 'dropdown',
                        'role'   => 'button',
                        'target' => '#',
                    ),
                    'data'        => array(
                        'hidden-class' => 'btn',
                    ),
                ),
            ),
            'talk'    => FALSE,
        )
    );

    /**
     *  Меню пользователя + experience
     */
    $config['data']['toolbar_user'] = array(
        'init' => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'description' => '{{menu_user_description}}',
        'list' => array(
            'userbar' => array(
                'text'    => array(
                    'user_name' => array(),
                    'count_messages'  => array('<span class="badge badge-danger badge-mail-counter">+{{count_messages}}</span>'),
                ),
                'link' => E::User()->getProfileUrl(),
                'options' => array(

                    'image_url'   => array('user_avatar_url' => array('32x32crop')),
                ),
            ),
        )
    );

    /**
     *  Подменю пользователя + experience
     */
    $config['data']['toolbar_userbar'] = array(
        'init' => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'description' => '{{menu_toolbar_userbar_description}}',
        'class' => 'dropdown-menu dropdown-user-menu animated fadeIn',
        'list'  => array(
            'user'         => array(
                'text' => '<span><i class="fa fa-user"></i></span><span>{{user_menu_profile}}</span>',
                'link' => E::User()->getProfileUrl(),
                'options' => array(
                    'class' => 'fixed-item',
                )
            ),
            'favourites'   => array(
                'text' => '<span><i class="fa fa-star"></i></span><span>{{user_menu_profile_favourites}}</span>',
                'link' => E::User()->getProfileUrl() . 'favourites/topics/',
                'options' => array(
                    'class' => 'fixed-item',
                )
            ),
            'talk'         => array(
                'text'    => array(
                    '<span><i class="fa fa-envelope"></i></span><span>{{user_privat_messages}}</span>',
                    '&nbsp;<span class="new-messages">',
                    'new_talk_string' => array(),
                    '</span>'
                ),
                'link'    => Router::GetPath('talk'),
                'options' => array(
                    'link_id' => 'new_messages',
                    'class' => 'fixed-item',
                )
            ),
            'settings'     => array(
                'text' => '<span><i class="fa fa-cog"></i></span><span>{{user_settings}}</span>',
                'link' => '___path.root.url___/settings/',
                'options' => array(
                    'class' => 'fixed-item',
                )
            ),
            'toolbar_userbar_item' => '',
            'logout'       => array(
                'text' => '<span><i class="fa fa-sign-out"></i></span><span>{{exit}}</span>',
                'link' => Router::GetPath('login') . 'exit/?security_key=' . E::Security_GetSecurityKey(),
                'options' => array(
                    'class' => 'fixed-item',
                )
            ),
        )
    );


    /**
     *  Подменю пользователя + experience
     */
    $config['data']['userbar'] = array(
        'class' => 'dropdown-menu dropdown-user-menu animated fadeIn',
        'list'  => array(
            'pre'          => array(
                'text'    => FALSE,
                'link'    => FALSE,
                'options' => array(
                    'class' => 'user_activity_items',
                ),
                'submenu' => 'userinfo',
            ),
            'user'         => array(
                'text' => '<i class="fa fa-user"></i>&nbsp;{{user_menu_profile}}',
                'link' => E::User()->getProfileUrl(),
            ),
            'create'       => array(
                'text'    => '<i class="fa fa-pencil"></i>&nbsp;{{block_create}}',
                'link'    => '#',
                'options' => array(
                    'data' => array(
                        'toggle' => 'modal',
                        'target' => '#modal-write',
                    ),
                ),
            ),
            'talk'         => array(
                'text'    => array(
                    '<i class="fa fa-envelope-o"></i>&nbsp;{{user_privat_messages}}',
                    '&nbsp;<span class="new-messages">',
                    'new_talk_string' => array(),
                    '</span>'
                ),
                'link'    => Router::GetPath('talk'),
                'options' => array(
                    'link_id' => 'new_messages',
                )
            ),
            'wall'         => array(
                'text' => '<i class="fa fa-bars"></i>&nbsp;{{user_menu_profile_wall}}',
                'link' => E::User()->getProfileUrl() . 'wall/',
            ),
            'publication'  => array(
                'text' => '<i class="fa fa-file-o"></i>&nbsp;{{user_menu_publication}}',
                'link' => E::User()->getProfileUrl() . 'created/topics/',
            ),
            'favourites'   => array(
                'text' => '<i class="fa fa-star-o"></i>&nbsp;{{user_menu_profile_favourites}}',
                'link' => E::User()->getProfileUrl() . 'favourites/topics/',
            ),
            'settings'     => array(
                'text' => '<i class="fa fa-cogs"></i>&nbsp;{{user_settings}}',
                'link' => '___path.root.url___/settings/',
            ),
            'userbar_item' => '',
            'logout'       => array(
                'text' => '<i class="fa fa-sign-out"></i>&nbsp;{{exit}}',
                'link' => Router::GetPath('login') . 'exit/?security_key=' . E::Security_GetSecurityKey(),
            ),
        )
    );
}

if (E::IsUser()) {
    $config['data']['userinfo'] = array(
        'init'        => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'description' => 'Индикаторы пользователя',
        'list'        => array(
            'user_rating'   => array(
                'text'    => array(
                    'user_rating' => array('<i class="fa fa-bar-chart-o"></i>', 'negative'),
                ),
                'link'    => E::User()->getProfileUrl(),
                'options' => array(
                    'class' => 'menu-item-user-rating',
                ),
            ),
            'user_comments' => array(
                'text'    => array(
                    'count_track' => array('<i class="fa fa-bullhorn"></i>'),
                ),
                'link'    => Router::GetPath('feed') . 'track/',
                'options' => array(
                    'class' => 'menu-item-user-comments',
                ),
            ),
            'user_mails'    => array(
                'text'    => array(
                    'new_talk_string' => array('<i class="fa fa-envelope-o"></i>'),
                ),
                'link'    => Router::GetPath('talk'),
                'options' => array(
                    'class' => 'menu-item-user-talks',
                ),
            ),


        )
    );
}


/**
 *  Меню топиков
 */
C::Set('menu.data.topics.discussed.text', array(
    '{{blog_menu_all_discussed}}',
    '&nbsp;<i class="caret"></i>',
));
$config['data']['topics'] = array(
    'list' => array(
        'homepage'  => array(
            'options' => array(
                'class' => '',
            )
        ),
        'good'                 => array(
            'active'  => array('topic_kind' => array('index')),
        ),
        'new'       => array(
            'text'    => array(
                '{{blog_menu_all_new}}',
                'new_topics_count' => array('red'),
            ),
            'options' => array(
                'class'      => '',
                'link_title' => '{{blog_menu_top_period_24h}}',
            )
        ),

        'newall'    => array(
            'options' => array(
                'class'      => '',
                'link_title' => '{{blog_menu_top_period_24h}}',
            )
        ),

        'feed'      => array(
            'options' => array(
                'class' => '',
            )
        ),


        'discussed' => array(
//            'text'    => array(
//                '{{blog_menu_all_discussed}}',
//                '&nbsp;<i class="caret"></i>',
//            ),
            'submenu' => 'discussed',
            'options' => array(
                'class'     => 'dropdown',
                'link_data' => array(
                    'toggle' => 'dropdown',
                ),
            )
        ),

    )
);

if (C::Get('rating.enabled')) {
    $config['data']['topics']['list']['top'] = array(

        'text'    => array(
            '{{blog_menu_all_top}}',
            '&nbsp;<i class="caret"></i>',
        ),
        'submenu' => 'top',
        'options' => array(
            'class'     => 'dropdown',
            'link_data' => array(
                'toggle' => 'dropdown',
            ),
        ),
    );
}

/**
 *  Подменю обсуждаемых
 */
$config['data']['discussed'] = array(
    'class' => 'dropdown-menu  dropdown-content-menu animated fadeIn',
);

if (C::Get('rating.enabled')) {
    /**
     *  Подменю топовых
     */
    $config['data']['top'] = array(
        'init'  => array(
            'fill' => array(
                'list' => array('*'),
            ),
        ),
        'class' => 'dropdown-menu  dropdown-content-menu animated fadeIn',
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
}

$config['data']['image_insert'] = array(
    'list' => array(
        'insert_from_pc'   => false,
        'insert_from_link' => false,
    )
);

$config['data']['footer_site_menu'] = array(
    'class' => 'footer-column',
    'list'        => array(
        'topic'                     => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
        'blogs'                     => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
        'people'                    => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
        'stream_menu'               => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
    )
);

$config['data']['footer_info'] = array(
    'class' => 'footer-column',
    'list'        => array(
        'about'                    => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
        'rules'                    => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
        'advert'                   => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
        'help'                     => array(
            'options' => array(
                'link_class' => 'link link-dual link-lead link-clear',
            )
        ),
    )
);