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

$config['widgets'][] = array(
    'name'     => 'toolbar_write.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 99,
);

$config['widgets'][] = array(
    'name'     => 'toolbar_menu.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 100,
);
$config['widgets'][] = array(
    'name'     => 'toolbar_admin.tpl',
    'wgroup'   => 'toolbar',
    'priority' => 'top',
);

$config['widgets'][] = array(
    'name'     => 'toolbar_scrollup.tpl',
    'wgroup'   => 'toolbar',
    'priority' => -100,
);

$config['widgets'][] = array(
    'name'   => 'actions/people/action.people.sidebar.tpl',
    'wgroup' => 'right',
    'on'     => 'people, search',
);

$config['widgets'][] = array(
    'name'   => 'UserfeedBlogs',
    'wgroup' => 'right',
    'on'     => 'feed/track',
);

$config['widgets'][] = array(
    'name'   => 'UserfeedUsers',
    'wgroup' => 'right',
    'on'     => 'feed/track',
);

$config['widgets'][] = array(
    'name'   => 'ActivitySettings',
    'wgroup' => 'right',
    'on'     => 'stream',
);

$config['widgets'][] = array(
    'name'   => 'ActivityFriends',
    'wgroup' => 'right',
    'on'     => 'stream',
);

$config['widgets'][] = array(
    'name'   => 'ActivityUsers',
    'wgroup' => 'right',
    'on'     => 'stream',
);