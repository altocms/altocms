<?php

$config['widgets']['topbanner_image'] = array(
    'name' => 'topbanner_image.tpl',
    'wgroup' => 'topbanner',
    'params' => array(
        'style' => 'background:url(' . Config::Get('path.static.skin') . 'assets/images/header-banner.jpg)',
        'title' => Config::Get('view.name'),
    ),
    'display' => true,
);

$config['widgets']['topbanner_slider'] = array(
    'name' => 'topbanner_slider.tpl',
    'wgroup' => 'topbanner',
    'params' => array(
        'images' => array(
            array(
                'image' => Config::Get('path.static.skin') . 'assets/images/header-banner1.jpg',
                'title' => 'Picture 1',
            ),
            array(
                'image' => Config::Get('path.static.skin') . 'assets/images/header-banner2.jpg',
                'title' => 'Picture 2',
            ),
            array(
                'image' => Config::Get('path.static.skin') . 'assets/images/header-banner3.jpg',
                'title' => '<a href="#">Picture 3</a>',
            ),
        ),
    ),
    'display' => false,
);

$config['widgets']['toolbar_admin'] = array(
    'name' => 'toolbar_admin.tpl',
    'wgroup' => 'toolbar',
    'priority' => 'top',
);

$config['widgets']['toolbar_scrollup'] = array(
    'name' => 'toolbar_scrollup.tpl',
    'wgroup' => 'toolbar',
    'priority' => -100,
);

$config['widgets']['people.sidebar'] = array(
    'name' => 'actions/people/action.people.sidebar.tpl',
    'wgroup' => 'right',
    'on' => 'people, search',
);

// EOF