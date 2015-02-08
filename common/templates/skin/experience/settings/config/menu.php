<?php

$config['menu']['allowed'] = array_merge(
    Config::Get('menu.allowed'),
    array(
        'blog_list',
    )
);

/**
 * Главное меню сайта  + experience
 * Настройки берутся из главного конфига меню common/config/menu.php
 * Добавлены классы иконок
 */
$config['menu']['data']['main'] = array(
    'list' => array(
        'index'  => array(
            'options' => array(
                'icon_class' => 'fa fa-file-text-o',
            )
        ),
        'blogs'  => array(
            'options' => array(
                'icon_class' => 'fa fa-comment-o',
            )
        ),
        'people' => array(
            'options' => array(
                'icon_class' => 'fa fa-circle-o',
            )
        ),
        'stream' => array(
            'options' => array(
                'icon_class' => 'fa fa-signal',
            )
        ),
    )
);

/**
 *  Вход и регистрация  + experience
 */
$config['menu']['data']['login'] = array(
    'list' => array(
        'login'        => array(
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
        'list' => array(
            'userbar' => array(
                'text'    => array(
                    'user_name' => array(),
                    '&nbsp;<i class="caret"></i>'
                ),
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
        'class' => 'dropdown-menu',
        'list'  => array(
            'user'         => array(
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
                'link' => E::User()->getProfileUrl() . 'wall/',
            ),
            'publication'  => array(
                'link' => E::User()->getProfileUrl() . 'created/topics/',
            ),
            'favourites'   => array(
                'link' => E::User()->getProfileUrl() . 'favourites/topics/',
            ),
            'userbar_item' => '',
            'logout'       => array(
                'link' => Router::GetPath('login') . 'exit/?security_key=' . E::Security_GetSecurityKey(),
            ),
        )
    );
}


/**
 *  Меню топиков
 */
$config['menu']['data']['topics'] = array(
    'list' => array(
        'homepage'             => array(
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
            'options' => array(
                'class'      => 'btn',
                'link_title' => '{{blog_menu_top_period_24h}}',
                'data'       => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),

        'newall'               => array(
            'options' => array(
                'class'      => 'btn',
                'link_title' => '{{blog_menu_top_period_24h}}',
                'data'       => array(
                    'hidden-class' => 'btn',
                ),
            )
        ),

        'feed'                 => array(
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

    )
);

if (C::Get('rating.enabled')) {
    $config['menu']['data']['topics']['list']['top'] = array(
        'text'    => array(
            '{{blog_menu_all_top}}',
            '&nbsp;<i class="caret"></i>',
        ),
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
    );
}

/**
 *  Подменю обсуждаемых
 */
$config['menu']['data']['discussed'] = array(
    'class' => 'dropdown-menu',
);

if (C::Get('rating.enabled')) {
    /**
     *  Подменю топовых
     */
    $config['menu']['data']['top'] = array(
        'class' => 'dropdown-menu',
    );
}


/**
 * Сеню с сыллками на блоги
 */
$config['menu']['data']['blog_list'] = array(
    'init' => array(
        'fill' => array(
            'list' => array('*'),
        ),
    ),
    'description' => 'Список блогов',
    'list' => array(
        'link1' => array(
            'text' => 'Дизайн',
            'link' => '___path.root.url___',
        ),

        'link2' => array(
            'text' => 'Техника',
            'link' => '___path.root.url___',
        ),

        'link3' => array(
            'text' => 'Смартфоны',
            'link' => '___path.root.url___',
        ),

        'link4' => array(
            'text' => 'Приложения',
            'link' => '___path.root.url___',
        ),

        'link5' => array(
            'text' => 'Спорт',
            'link' => '___path.root.url___',
        ),

        'link6' => array(
            'text' => 'Новости',
            'link' => '___path.root.url___',
        ),

        'link7' => array(
            'text' => 'Технологии',
            'link' => '___path.root.url___',
        ),

    )
);