<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package actions
 * @since   1.0
 */
class ActionHomepage extends Action {

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->SetDefaultEvent('default');
    }

    /**
     * Регистрация евентов
     *
     */
    protected function RegisterEvent() {

        $this->AddEvent('default', 'EventDefault');
    }

    /**
     * Default homepage
     *
     * @return string
     */
    public function EventDefault() {

        E::ModuleViewer()->Assign('sMenuHeadItemSelect', 'homepage');
        $sHomepage = Config::Get('router.config.homepage');
        if ($sHomepage) {
            $sHomepageSelect = Config::Get('router.config.homepage_select');
            if ($sHomepageSelect == 'page') {
                // if page not active or deleted then this homepage is off
                $oPage = E::ModulePage()->GetPageByUrlFull($sHomepage, 1);
                if ($oPage) {
                    $sHomepage = $oPage->getLink();
                } else {
                    $sHomepage = '';
                }
            } else {
                if ($sHomepageSelect == 'category_homepage') {
                    $sHomepageSelect = 'plugin-categories-homepage';
                }
                $aHomePageSelect = explode('-', $sHomepageSelect);
                // if homepage was from plugin and plugin is not active then this homepage is off
                if ($aHomePageSelect[0] == 'plugin' && isset($aHomePageSelect[1])) {
                    if (!E::ActivePlugin($aHomePageSelect[1])) {
                        $sHomepage = '';
                    }
                }
            }
            if ($sHomepage == 'home') {
                if (E::ModuleViewer()->TemplateExists('actions/homepage/action.homepage.index.tpl')) {
                    $this->SetTemplateAction('index');
                    return;
                }
            } elseif ($sHomepage) {
                return R::Action($sHomepage);
            }
        }
        return R::Action('index');
    }

}
// EOF