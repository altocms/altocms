<?php

/**
 * User.entity.class.php
 * Файл сущности для модуля User плагина Recaptcha
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Recaptcha
 * @version     0.0.1 от 08.01.2015 21:01
 */
class PluginRecaptcha_ModuleUser_EntityUser extends PluginRecaptcha_Inherit_ModuleUser_EntityUser {

    /**
     * При инициализации сущности пользователя заменим
     * определение стандартного валидатора капчи
     * на валидатор рекапчи
     */
    public function Init() {

        parent::Init();

        // Что бы массив полностью не перебирать обратим его, поскольку
        // определение валидатора капчи стандартно располагается вконце
        // массива валидаторов.
        $this->aValidateRules = array_reverse($this->aValidateRules);

        // Удалим определение валидатора стандартной капчи
        foreach ($this->aValidateRules as $k => $aRule) {
            if (@$aRule[0] == 'captcha') {
                unset($this->aValidateRules[$k]);
                break;
            }
        }

        // Определяем правила валидации рекапчи
        if (Config::Get('module.user.captcha_use_registration')) {
            $this->aValidateRules[] = array('g-recaptcha-response', 'check_captcha', 'on' => array('registration'));
        }

    }

    /**
     * Валидатор рекапчи
     *
     * @return bool
     */
    public function validateCheckCaptcha() {

        // Получим код от рекапчи
        if (!($sCaptchaKey = getRequestStr('g-recaptcha-response', FALSE, 'post'))) {
            return FALSE;
        }

        // И вернем результат его проверки
        return E::Captcha_Verify($sCaptchaKey);

    }

}