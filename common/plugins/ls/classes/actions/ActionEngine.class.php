<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class PluginLs_ActionEngine extends ActionPlugin {

    public function Init() {

        $this->SetDefaultEvent('lib');
    }

    protected function RegisterEvent() {

        $this->AddEventPreg('/^lib$/i', '/^external$/i', '/^kcaptcha$/i', 'EventLibCaptcha');
        $this->AddEventPreg('/^libs$/i', '/^external$/i', '/^kcaptcha$/i', 'EventLibCaptcha');
    }

    /**
     * Отображение каптчи старым способом (LS-compatible)
     */
    protected function EventLib() {

        if (Router::GetControllerPath() == 'external/kcaptcha/index.php') {
            return $this->EventLibCaptcha();
        }
    }

    protected function EventLibCaptcha() {

        return R::Action('Captcha');
    }

}

// EOF