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
        $this->SetDefaultEvent('index');
    }

    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
    }

    public function EventIndex() {

        if (!class_exists('KCAPTCHA', false)) {
            F::IncludeLib('kcaptcha/kcaptcha.php');
        }
        $oCaptcha = new KCAPTCHA();
        $this->Session_Set('captcha_keystring', $oCaptcha->getKeyString());
        exit;
    }
}

// EOF