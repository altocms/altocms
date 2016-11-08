<?php
/**
 * Главное меню сайта
 * Настройки берутся из главного конфига меню common/config/menu.php
 */

// Настройки подменю, созданных в админке
$config['submenu'] = array(
    'class' => 'dropdown-menu',
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
 *  Вход и регистрация
 */
$config['data']['login'] = array(
    'list' => array(
        'login'        => array(
            'options' => array(
                'link_class' => 'js-modal-auth-login'
            ),
        ),
        'registration' => array(
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
    $config['data']['user'] = array(
        'list' => array(
            'talk'    => array(
                'text'    => array(
                    '<span class="glyphicon glyphicon-envelope"></span>&nbsp;+',
                    'new_talk' => array(),
                ),
                'options' => array(
                    'link_class' => 'new-messages'
                ),
            ),
            'userbar' => array(
                'text'    => array(
                    'user_name' => array(),
                    '<b class="caret"></b>'
                ),
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
    $config['data']['userbar'] = array(
        'class' => 'dropdown-menu',
        'list'  => array(
            'user'         => array(
                'link' => E::User()->getProfileUrl(),
            ),
            'talk'         => array(
                'text'    => array(
                    '{{user_privat_messages}}',
                    '<span class="new-messages">',
                    'new_talk_string' => array(),
                    '</span>'
                ),
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
$config['data']['topics'] = array(
    'list' => array(
        'good'                 => array(
            'options' => array(
                'class' => 'bordered menu-topics-good',
            )
        ),
        'new'                  => array(
            'text'    => array(
                '{{blog_menu_all_new}} + ',
                'new_topics_count' => array(),
            ),
            'options' => array(
                'class'      => 'bordered menu-topics-new',
                'link_title' => '{{blog_menu_top_period_24h}}'
            )
        ),

        'newall'               => array(
            'options' => array(
                'class'      => 'bordered menu-topics-all',
                'link_title' => '{{blog_menu_top_period_24h}}'
            )
        ),

        'feed'                 => array(
            'options' => array(
                'class' => 'bordered menu-topics-feed role-guest-hide',
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
            'submenu' => 'discussed',
            'options' => array(
                'class'      => 'dropdown menu-topics-discussed',
                'link_class' => 'dropdown-toggle',
                'link_data'  => array(
                    'toggle' => 'dropdown',
                )
            )
        ),

    )
);

if (C::Get('rating.enabled')) {
    $config['data']['topics']['list']['top'] = array(
        'text'    => array(
            '{{blog_menu_all_top}}',
            '<b class="caret"></b>',
        ),
        'submenu' => 'top',
        'options' => array(
            'class'      => 'dropdown menu-topics-top',
            'link_class' => 'dropdown-toggle',
            'link_data'  => array(
                'toggle' => 'dropdown',
            )
        )
    );
}

/**
 *  Подменю обсуждаемых
 */
$config['data']['discussed'] = array(
    'class' => 'dropdown-menu',
);

if (C::Get('rating.enabled')) {
    /**
     *  Подменю топовых
     */
    $config['data']['top'] = array(
        'class' => 'dropdown-menu',
    );
}
