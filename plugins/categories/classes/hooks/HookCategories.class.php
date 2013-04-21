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
class PluginCategories_HookCategories extends Hook {
    public function RegisterHook() {
        // Хук для админки для добавления опции выбора главной
        $this->AddHook('template_admin_select_homepage', 'TplAdminSelectHomepage');
		// Пункт меню админки
        $this->AddHook('template_admin_menu_config', 'TplAdminMenuConfig');
		//подключаем размеры mainpreview
		$this->AddHook('init_action', 'InitAction');
    }
	
	public function InitAction() {
		if(in_array('mainpreview', $this->Plugin_GetActivePlugins())){
			$this->PluginCategories_Categories_InitConfigMainPreview();
		}
	}

    public function TplAdminSelectHomepage() {
        $sHomePageSelect = Config::Get('router.config.homepage_select');
        $this->Viewer_Assign('sHomePageSelect', $sHomePageSelect);
        return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'hook.admin_select_homepage.tpl');
    }

	public function TplAdminMenuConfig() {
        return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'hook.admin_menu_config.tpl');
    }
}

// EOF