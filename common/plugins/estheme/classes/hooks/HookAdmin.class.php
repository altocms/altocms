<?php

/**
 * Hook.estheme.class.php
 * Файл хука плагина Estheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Estheme
 * @version     0.0.1 от 27.02.2015 08:56
 */
class PluginEstheme_HookAdmin extends Hook {
    /**
     * Регистрация хуков
     */
    public function RegisterHook() {
        if (E::IsAdmin()) {
            $this->AddHook('template_admin_menu_tools', 'AdminMenuInject');
        }
    }

    public function AdminMenuInject() {

        return E::ModuleViewer()->Fetch(Plugin::GetTemplatePath('estheme') . '/tpls/inject.admin.menu.tpl');

    }

}
