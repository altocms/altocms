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
    }

    /**
     * Обработка хука
     *
     * @return string
     */
    public function TemplateCaptcha() {
        $s = $this->Viewer_Fetch('inc.captcha.tpl');
        return $this->Viewer_Fetch('inc.captcha.tpl');
    }
}

// EOF