<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

$config=array();

$config['widgets'][] = array(
    'name' => 'widgets/widget.demo.tpl',  // шаблонный виджет
    'group' => 'right',
    'priority' => 'top',
    'plugin' => 'demo',
    'display' => array('date_from'=>'2012-12-10', 'date_upto'=>'2013-12-21'),
);

Config::Set('block.rule_demo', array(
    'action'  => array(
        'demo',
    ),
    'blocks'  => array(
        'right' => array(
            'Demoruleexec'=>array('params'=>array('plugin'=>'demo')),
            'blocks/block.demoruletpl.tpl'=>array('params'=>array('plugin'=>'demo')),
        )
    ),
    'clear' => false,
));

return $config;


// EOF