<?php
/**
 * Captcha.class.php
 * Файл модуля Captcha плагина Recaptcha
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Воронов
 *              Является частью плагина Recaptcha
 * @version     0.0.1 от 08.01.2014 19:04
 */
class PluginRecaptcha_ModuleCaptcha
    extends PluginRecaptcha_Inherit_ModuleCaptcha {

    /**
     * Проверка рекапчи
     *
     * @param string $sKeyString Строка с кодомвернутым от рекапчи
     * @param string $sKeyName Наименование поля рекапчи
     *
     * @return bool
     */
    public function Verify($sKeyString, $sKeyName = null) {

        /** @var string $xResult Результат валидации рекапчи который получен от гугла в формате json */
        $xResult = file_get_contents(str_replace(
            array('%SECRET%', '%RESPONSE%', '%IP%'),
            array(Config::Get('plugin.recaptcha.secret_key'), $sKeyString, F::GetUserIp()),
            "https://www.google.com/recaptcha/api/siteverify?secret=%SECRET%&response=%RESPONSE%&remoteip=%IP%"
        ));

        // Декодируем ответ
        $xResult = json_decode($xResult);

        // Учитывая возможные сбои вернём результат через собачку
        return @$xResult->success;

    }

}