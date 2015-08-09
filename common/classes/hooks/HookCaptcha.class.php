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

        $this->AddHookTemplate('registration_captcha', 'TemplateCaptcha');
    }

    /**
     * Обработка хука
     *
     * @param array $aData
     *
     * @return string
     */
    public function TemplateCaptcha($aData) {

        $sType = isset($aData['type']) ? $aData['type'] : 'registration';

        return E::ModuleViewer()->Fetch("tpls/commons/common.captcha.$sType.tpl");
    }

}

// EOF