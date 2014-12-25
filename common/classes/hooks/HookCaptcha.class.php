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
 * Регистрация хука для вывода каптчи
 *
 * @package hooks
 * @since 1.0
 */
class HookCaptcha extends Hook {
    /**
     * Регистрируем хуки
     */
    public function RegisterHook() {
        $this->AddHook('template_registration_captcha', 'TemplateCaptcha', __CLASS__);
        $this->AddHook('captcha', 'CheckCaptcha', __CLASS__);
    }

    /**
     * Обработка хука
     *
     * @return string
     */
    public function TemplateCaptcha($aData) {

        $sType = isset($aData['type']) ? $aData['type'] : 'registration';

        return $this->Viewer_Fetch("tpls/commons/common.captcha.$sType.tpl");
    }

    public function CheckCaptcha() {

        if (!class_exists('KCAPTCHA', FALSE)) {
            F::IncludeLib('kcaptcha/kcaptcha.php');
        }
        $oCaptcha = new KCAPTCHA();
        $this->Session_Set('captcha_keystring', $oCaptcha->getKeyString());
        exit;

    }

}

// EOF