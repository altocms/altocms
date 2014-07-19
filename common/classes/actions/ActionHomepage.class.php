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

        $this->Viewer_Assign('sMenuHeadItemSelect', 'homepage');
        $sHomepage = Config::Get('router.config.homepage');
        if ($sHomepage) {
            if ($sHomepage == 'home') {
                if ($this->Viewer_TemplateExists('actions/homepage/action.homepage.index.tpl')) {
                    $this->SetTemplateAction('index');
                    return;
                }
            } else {
                return Router::Action($sHomepage);
            }
        }
        return Router::Action('index');
    }

}
// EOF