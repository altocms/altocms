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
 * @since 0.9
 */
class ActionCaptcha extends Action {

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->SetDefaultEvent('registration');
    }

    protected function RegisterEvent() {

        $this->AddEvent('registration', 'EventRegistration');
    }

    public function EventRegistration() {

        /** @var ModuleCaptcha_EntityCaptcha $oCaptcha */
        $oCaptcha = E::Captcha_GetCaptcha();
        $oCaptcha->Display();
        exit;
    }

}

// EOF