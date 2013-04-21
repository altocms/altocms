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
 * Регистрация хука
 *
 */
class PluginDemo_HookDemo extends Hook {
    public function RegisterHook() {
        // Хук для админки для добавления опции выбора глвной
        $this->AddHook('template_admin_select_homepage', 'TplAdminSelectHomepage');
    }

    public function TplAdminSelectHomepage() {
        $sHomePageSelect = Config::Get('router.config.homepage_select');
        $this->Viewer_Assign('sHomePageSelect', $sHomePageSelect);
        return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'hook.admin_select_homepage.tpl');
    }
}

// EOF