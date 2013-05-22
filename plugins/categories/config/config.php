<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

$config=array();

Config::Set('db.table.categories_category_rel', '___db.table.prefix___category_rel');
Config::Set('db.table.categories_category', '___db.table.prefix___category');

Config::Set('router.page.category', 'PluginCategories_ActionCategory');

$config['topic_per_category_popular']='3';
$config['topic_per_category_new']='6';

$config['preview_size_w']=229;	// Ширина
$config['preview_size_h']=116;	// Высота, при crop=false используется как минимально возможная высота
$config['preview_crop']=true;	// Делать из картинки кроп? false - если не нужно обрезать картинки по высоте
$config['preview_big_size_w']=354;	// Ширина большого варианта
$config['preview_big_size_h']=186;	// Высота большого варианта, при crop=false используется как минимально возможная высота
$config['preview_big_crop']=true;	// Делать из картинки кроп для большого варианта? false - если не нужно обрезать картинки по высоте

$config['size_images_preview']=array(
	array(
		'w' => $config['preview_size_w'],
		'h' => $config['preview_crop'] ? $config['preview_size_h'] : null,
		'crop' => $config['preview_crop'],
	),
	array(
		'w' => $config['preview_big_size_w'],
		'h' => $config['preview_big_crop'] ? $config['preview_big_size_h'] : null,
		'crop' => $config['preview_big_crop'],
	)
);

// Прямой эфир
$config['widgets'][] = array(
    'name' => 'stream',     // исполняемый виджет Stream
    'group' => 'right',     // группа, куда нужно добавить виджет
    'priority' => 100,      // приоритет
    'action' => array(
        'index',
        'filter',
        'blogs',
        'blog' => array('{topics}', '{topic}', '{blog}'),
        'tag',
		'category',
    ),
    'title' => 'Прямой эфир',
);

// Теги
$config['widgets'][] = array(
    'name' => 'tags',
    'group' => 'right',
    'priority' => 50,
    'action' => array(
        'index',
        'filter',
        'blog' => array('{topics}', '{topic}', '{blog}'),
        'tag',
		'category',
    ),
);

// Блоги
$config['widgets'][] = array(
    'name' => 'blogs',
    'group' => 'right',
    'priority' => 1,
    'action' => array(
        'category','index', 'filter', 'blog' => array('{topics}', '{topic}', '{blog}')
    ),
);

// Категории
$config['widgets'][] = array(
    'name' => 'categories',
    'group' => 'right',
    'priority' => 150,
	'params'=>array('plugin'=>'categories'),
    'action' => array(
		'category',
    ),
);

return $config;


// EOF