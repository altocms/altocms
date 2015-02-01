<?php

/**
 * HookAdmin
 * Файл хука плагина Rating
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *              Является частью плагина Rating
 * @version     0.0.1 от 30.01.2015 22:45
 */
class PluginSimplerating_HookAdmin extends Hook {
    /**
     * Регистрация хуков
     */
    public function RegisterHook() {
        if (E::IsAdmin()) {
            $this->AddHook('template_admin_menu_settings', 'AdminMenuInject');
        }
    }

    public function AdminMenuInject() {

        return E::ModuleViewer()->Fetch(Plugin::GetTemplatePath('simplerating') . '/tpls/inject.admin.menu.tpl');
    }

}
