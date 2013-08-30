<?php

/**
 * Настройка Виджетов
 */
/*
 * $config['widgets'][] = array(
    'name' => 'stream',     // виджет
    'wgroup' => 'right',    // имя группы
    'priority' => 100,      // приоритет - чем выше приоритет, тем раньше в группе выводится виджет
                            // виджеты с приоритетом 'top' выводятся раньше других в группе
    'on' => array('index', 'blog'), // где показывать виджет
    'off' => array('admin/*', 'settings/*', 'profile/*', 'talk/*', 'people/*'), // где НЕ показывать виджет
    'action' => array(
        'blog' => array('{topics}', '{topic}', '{blog}'), // для совместимости с LiveStreet
    ),
    'display' => true,  // true - выводить, false - не выводить,
                        // array('date_from'=>'2011-10-10', 'date_upto'=>'2011-10-20') - выводить с... по...
);

 */
// Прямой эфир
$config['widgets'][] = array(
    'name' => 'stream',     // исполняемый виджет Stream
    'wgroup' => 'right',    // группа, куда нужно добавить виджет
    'priority' => 100,      // приоритет
    'action' => array(
        'index',
        'community',
        'filter',
        'blogs',
        'blog' => array('{topics}', '{topic}', '{blog}'),
        'tag',
    ),
    'title' => 'Прямой эфир',
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.blogInfo.tpl',  // шаблонный виджет
    'wgroup' => 'right',
    'action' => array(
        'content' => array('{add}', '{edit}'),
    ),
);

// Теги
$config['widgets'][] = array(
    'name' => 'tags',
    'wgroup' => 'right',
    'priority' => 50,
    'action' => array(
        'index',
        'community',
        'filter',
        'blog' => array('{topics}', '{topic}', '{blog}'),
        'tag',
    ),
);

// Блоги
$config['widgets'][] = array(
    'name' => 'blogs',
    'wgroup' => 'right',
    'priority' => 1,
    'action' => array(
        'index',
        'community',
        'filter',
        'blog' => array('{topics}', '{topic}', '{blog}'),
    ),
);

$config['widgets'][] = array(
    'name' => 'actions/ActionPeople/sidebar.tpl',
    'wgroup' => 'right',
    'on' => 'people',
);

$config['widgets'][] = array(
    'name' => 'actions/ActionProfile/sidebar.tpl',
    'wgroup' => 'right',
    'on' => 'profile, talk, settings',
);

$config['widgets'][] = array(
    'name' => 'userfeedBlogs',
    'wgroup' => 'right',
    'action' => array(
        'feed' => array('{index}'),
    ),
);

$config['widgets'][] = array(
    'name' => 'userfeedUsers',
    'wgroup' => 'right',
    'action' => array(
        'feed' => array('{index}'),
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.blog.tpl',
    'wgroup' => 'right',
    'priority' => 300,
    'action' => array(
        'blog' => array('{topic}')
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.blogAdd.tpl',
    'wgroup' => 'right',
    'priority' => 125,
    'action' => array(
        'blogs'
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.contentAdd.tpl',
    'wgroup' => 'right',
    'priority' => 125,
    'action' => array(
        'index','filter'
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.userPhoto.tpl',
    'wgroup' => 'right',
    'priority' => 100,
    'action' => array(
        'profile'
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.userActions.tpl',
    'wgroup' => 'right',
    'priority' => 50,
    'action' => array(
        'profile'
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.userNote.tpl',
    'wgroup' => 'right',
    'priority' => 25,
    'action' => array(
        'profile'
    ),
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.userNav.tpl',
    'wgroup' => 'right',
    'priority' => 1,
    'action' => array(
        'profile'
    ),
);

// EOF