<?php
/**
 * Captcha.entity.class.php
 * Файл сущности для модуля Captcha плагина Recaptcha
 *
 * Эта сущность просто затирает родительские методы для того что бы
 * ими нельзя было воспользоваться
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Recaptcha
 * @version     0.0.1 от 08.01.2015 21:01
 */
class PluginRecaptcha_ModuleCaptcha_EntityCaptcha
    extends PluginRecaptcha_Inherit_ModuleCaptcha_EntityCaptcha {

    public function Init()      { return FALSE; }
    public function Reset()     { return FALSE; }
    public function Display()   { return FALSE; }
    function getKeyString()     { return FALSE; }

}