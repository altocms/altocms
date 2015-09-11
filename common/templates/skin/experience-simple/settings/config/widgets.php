<?php
/**
 * Тема оформления Experience v.1.0  для Alto CMS
 * @licence     CC Attribution-ShareAlike
 */

/**
 * widgets.php
 * Файл виджетов темы оформления Experience
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью темы оформления Experience
 * @version     0.0.1 от 29.05.2014 1:20
 */

$config['widgets']['toolbar_write'] = array(
    'name'     => 'toolbar_write.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 99,
);
$config['widgets']['toolbar_search'] = array(
    'name'     => 'toolbar_search.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 10000,
);

$config['widgets']['toolbar_login'] = array(
    'name'     => 'toolbar_login.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 1000,
);

$config['widgets']['toolbar_menu'] = array(
    'name'     => 'toolbar_menu.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 100,
);
$config['widgets']['toolbar_admin'] = array(
    'name'     => 'toolbar_admin.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 'top',
);

$config['widgets']['toolbar_scrollup'] = array(
    'name'     => 'toolbar_scrollup.tpl',
    'wgroup'   => 'toolbar',
    'priority' => -100,
);

$config['widgets']['people.sidebar'] = array(
    'name'   => 'actions/people/action.people.sidebar.tpl',
    'wgroup' => 'right',
    'on'     => 'people, search',
);

$config['widgets']['UserfeedBlogs'] = array(
    'name'   => 'UserfeedBlogs',
    'wgroup' => 'right',
    'on'     => 'feed/track',
);

$config['widgets']['UserfeedUsers'] = array(
    'name'   => 'UserfeedUsers',
    'wgroup' => 'right',
    'on'     => 'feed/track',
);

// EOF