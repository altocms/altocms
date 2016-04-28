<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Регистрация хука для вывода меню страниц
 *
 */
class HookPage extends Hook {
    public function RegisterHook() {
        $this->AddHook('template_main_menu_item', 'Menu');
    }

    public function Menu() {
        $aPages = E::ModulePage()->GetPages(array('pid' => null, 'main' => 1, 'active' => 1));
        E::ModuleViewer()->assign('aPagesMain', $aPages);
        return E::ModuleViewer()->Fetch('menus/menu.main_pages.tpl');
    }
}

// EOF