<?php

$config['widgets'] = Config::Get('widgets');

// Widgets for toolbar
$config['widgets'][] = array(
    'name' => 'widgets/widget.toolbar_admin.tpl',
    'group' => 'toolbar',
    'priority' => 100,
);

$config['widgets'][] = array(
    'name' => 'widgets/widget.toolbar_scrollup.tpl',
    'group' => 'toolbar',
    'priority' => -100,
);

// EOF