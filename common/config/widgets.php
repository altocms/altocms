<?php

/**
 * Настройка Виджетов
 * Widgets settings
 */
/*
 * $config['widgets'][<id>] = [
 *     // ...
 * ];
 *
 * $config['widgets'][] = [
 *      'id' => <id>,
 *      // ...
 * ];
 *
 * $config['widgets']['stream'] = [
    'name' => 'stream',     // виджет
    'wgroup' => 'right',    // имя группы
    'priority' => 100,      // приоритет - чем выше приоритет, тем раньше в группе выводится виджет
                            // виджеты с приоритетом 'top' выводятся раньше других в группе
    'on' => ['index', 'blog'], // где показывать виджет
    'off' => ['admin/*', 'settings/*', 'profile/*', 'talk/*', 'people/*'], // где НЕ показывать виджет
    'action' => [
        'blog' => ['{topics}', '{topic}', '{blog}'], // для совместимости с LiveStreet
    ],
    'display' => true,  // true - выводить, false - не выводить,
                        // ['date_from'=>'2011-10-10', 'date_upto'=>'2011-10-20'] - выводить с... по...
];

 */
// Прямой эфир
$config['widgets']['stream'] = array(
    'name' => 'stream',     // исполняемый виджет Stream (class WidgetStream)
    'type' => 'exec',       // тип - exec - исполняемый (если не задавать, то будет определяться автоматически)
    'wgroup' => 'right',    // группа, куда нужно добавить виджет
    'priority' => 100,      // приоритет
    'action' => [
        'index',
        'community',
        'filter',
        'blogs',
        'blog' => ['{topics}', '{topic}', '{blog}'],
        'tag',
    ],
    'params' => [
        'items' => [
            'comments' => ['text' => 'widget_stream_comments', 'type'=>'comment'],
            'topics' => ['text' => 'widget_stream_topics', 'type'=>'topic'],
        ],
        'limit' => 20, // max items for display
    ],
);

$config['widgets']['blogInfo.tpl'] = [
    'name' => 'widgets/widget.blogInfo.tpl',  // шаблонный виджет
    'wgroup' => 'right',
    'action' => [
        'content' => ['{add}', '{edit}'],
    ],
];

$config['widgets']['blogAvatar.tpl'] = [
    'name' => 'widgets/widget.blogAvatar.tpl',  // шаблонный виджет
    'wgroup' => 'right',
    'priority' => 999,
    'on' => [
        'blog/add', 'blog/edit',
    ],
];

// Теги
$config['widgets']['tags'] = [
    'name' => 'tags',
    'type' => 'exec',
    'wgroup' => 'right',
    'priority' => 50,
    'action' => [
        'index',
        'community',
        'filter',
        'comments',
        'blog' => ['{topics}', '{topic}', '{blog}'],
        'tag',
    ],
    'params' => [
        'limit' => 70, // max items for display
    ],
];

// Блоги
$config['widgets']['blogs'] = [
    'name' => 'blogs',
    'type' => 'exec',
    'wgroup' => 'right',
    'priority' => 1,
    'action' => [
        'index',
        'community',
        'filter',
        'comments',
        'blog' => ['{topics}', '{topic}', '{blog}'],
    ],
    'params' => [
        'limit' => 10, // max items for display
    ],
];
/*
$config['widgets'][] = [
    'name' => 'usersStatistics.tpl',
    'wgroup' => 'right',
    'on' => 'people',
];
*/

$config['widgets']['profile.sidebar'] = [
    'name' => 'actions/profile/action.profile.sidebar.tpl',
    'wgroup' => 'right',
    'priority' => 150,
    'on' => 'profile, talk, settings',
];

$config['widgets']['userfeedBlogs'] = [
    'name' => 'userfeedBlogs',
    'type' => 'exec',
    'wgroup' => 'right',
    'action' => [
        'feed' => ['{index}'],
    ],
];

$config['widgets']['userfeedUsers'] = [
    'name' => 'userfeedUsers',
    'type' => 'exec',
    'wgroup' => 'right',
    'action' => [
        'feed' => ['{index}'],
    ],
];

$config['widgets']['blog'] = [
    'name' => 'widgets/widget.blog.tpl',
    'wgroup' => 'right',
    'priority' => 300,
    'action' => [
        'blog' => ['{topic}']
    ],
];
/*
$config['widgets'][] = [
    'name' => 'widgets/widget.userPhoto.tpl',
    'wgroup' => 'right',
    'priority' => 100,
    'on' => [
        'settings/profile'
    ],
];

$config['widgets'][] = [
    'name' => 'widgets/widget.userNote.tpl',
    'wgroup' => 'right',
    'priority' => 25,
    'action' => [
        'profile'
    ],
];

$config['widgets'][] = [
    'name' => 'widgets/widget.userNav.tpl',
    'wgroup' => 'right',
    'priority' => 1,
    'action' => [
        'profile'
    ],
];
*/
// EOF