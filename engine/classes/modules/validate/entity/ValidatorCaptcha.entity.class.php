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
 * Валидатор каптчи (число с картинки)
 *
 * @package engine.modules.validate
 * @since   1.0
 */
class ModuleValidate_EntityValidatorCaptcha extends ModuleValidate_EntityValidator {
    /**
     * Допускать или нет пустое значение
     *
     * @var bool
     */
    public $allowEmpty = false;

    /**
     * Запуск валидации
     *
     * @param mixed $sValue    Данные для валидации
     *
     * @return bool|string
     */
    public function validate($sValue) {

        if ($this->allowEmpty && $this->isEmpty($sValue)) {
            return true;
        }
        if (E::Captcha_Verify(mb_strtolower($sValue)) !== 0) {
            return $this->getMessage(E::ModuleLang()->Get('validate_captcha_not_valid', null, false), 'msg');
        }
        return E::Captcha_Verify(mb_strtolower($sValue)) === 0;
    }
}

// EOF