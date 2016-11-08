<?php

/**
 * Настройка Виджетов
 * Widgets settings
 */
/*
 * $config['widgets'][<id>] = array(
 *     // ...
 * );
 *
 * $config['widgets'][] = array(
 *      'id' => <id>,
 *      // ...
 * );
 *
 * $config['widgets']['stream'] = array(
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
$config['widgets']['stream'] = array(
    'name' => 'stream',     // исполняемый виджет Stream (class WidgetStream)
    'type' => 'exec',       // тип - exec - исполняемый (если не задавать, то будет определяться автоматически)
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
    'params' => array(
        'items' => array(
            'comments' => array('text' => 'widget_stream_comments', 'type'=>'comment'),
            'topics' => array('text' => 'widget_stream_topics', 'type'=>'topic'),
        ),
        'limit' => 20, // max items for display
    ),
);

$config['widgets']['blogInfo.tpl'] = array(
    'name' => 'widgets/widget.blogInfo.tpl',  // шаблонный виджет
    'wgroup' => 'right',
    'action' => array(
        'content' => array('{add}', '{edit}'),
    ),
);

$config['widgets']['blogAvatar.tpl'] = array(
    'name' => 'widgets/widget.blogAvatar.tpl',  // шаблонный виджет
    'wgroup' => 'right',
    'priority' => 999,
    'on' => array(
        'blog/add', 'blog/edit',
    ),
);

// Теги
$config['widgets']['tags'] = array(
    'name' => 'tags',
    'type' => 'exec',
    'wgroup' => 'right',
    'priority' => 50,
    'action' => array(
        'index',
        'community',
        'filter',
        'comments',
        'blog' => array('{topics}', '{topic}', '{blog}'),
        'tag',
    ),
    'params' => array(
        'limit' => 70, // max items for display
    ),
);

// Блоги
$config['widgets']['blogs'] = array(
    'name' => 'blogs',
    'type' => 'exec',
    'wgroup' => 'right',
    'priority' => 1,
    'action' => array(
        'index',
        'community',
        'filter',
        'comments',
        'blog' => array('{topics}', '{topic}', '{blog}'),
    ),
    'params' => array(
        'limit' => 10, // max items for display
    ),
);
/*
$config['widgets'][] = array(
    'name' => 'usersStatistics.tpl',
    'wgroup' => 'right',
    'on' => 'people',
);
*/

$config['widgets']['profile.sidebar'] = array(
    'name' => 'actions/profile/action.profile.sidebar.tpl',
    'wgroup' => 'right',
    'priority' => 150,
    'on' => 'profile, talk, settings',
);

$config['widgets']['userfeedBlogs'] = array(
    'name' => 'userfeedBlogs',
    'type' => 'exec',
    'wgroup' => 'right',
    'action' => array(
        'feed' => array('{index}'),
    ),
);

$config['widgets']['userfeedUsers'] = array(
    'name' => 'userfeedUsers',
    'type' => 'exec',
    'wgroup' => 'right',
    'action' => array(
        'feed' => array('{index}'),
    ),
);

$config['widgets']['blog'] = array(
    'name' => 'widgets/widget.blog.tpl',
    'wgroup' => 'right',
    'priority' => 300,
    'action' => array(
        'blog' => array('{topic}')
    ),
);
/*
$config['widgets'][] = array(
    'name' => 'widgets/widget.userPhoto.tpl',
    'wgroup' => 'right',
    'priority' => 100,
    'on' => array(
        'settings/profile'
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
*/
// EOF