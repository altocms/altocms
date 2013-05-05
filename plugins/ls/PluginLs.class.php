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

/**
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

class PluginLs extends Plugin {

    protected $aDelegates = array(
        'template' => array(
            'page_main_menu.tpl' => '_page_main_menu.tpl',
            'window_write.tpl' => '_window_write.tpl',
            'sharer.tpl' => '_sharer.tpl',
        ),
    );

    protected $aInherits = array(
        'module' => array(
            'ModuleViewer',
            'ModuleWidget',
        ),
    );

    public function __construct() {
        if (F::File_Exists(Config::Get('path.smarty.template') . 'page_main_menu.tpl')) {
            // считаем, что скин адаптирован и делегаты шаблонов не нужны
            $this->aDelegates = null;
        }
    }

    /**
     * Активация плагина
     */
    public function Activate() {
        return true;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {

    }
}

// EOF