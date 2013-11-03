<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

F::IncludeLib('kcaptcha/kcaptcha.php');

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
        //$this->Viewer_SetResponseAjax('json');
        $this->SetDefaultEvent('index');
    }

    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
    }

    public function EventIndex() {

        $oCaptcha = new KCAPTCHA();
        $this->Session_Set('captcha_keystring', $oCaptcha->getKeyString());
        exit;
    }
}

// EOF