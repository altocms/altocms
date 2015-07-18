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
 * Короткий алиас для вызова основных методов движка
 * LS-compatible
 *
 * @package engine
 * @since 1.0
 */
class LS extends LsObject {

    static protected $oInstance = null;

    static public function getInstance() {
        if (isset(self::$oInstance) && (self::$oInstance instanceof self)) {
            return self::$oInstance;
        } else {
            self::$oInstance = new self();
            return self::$oInstance;
        }
    }

    /**
     * Возвращает ядро
     * @see Engine::GetInstance
     *
     * @return Engine
     */
    static public function E() {

        return Engine::GetInstance();
    }

    /**
     * Возвращает объект сущности
     * @see Engine::GetEntity
     *
     * @param $sName    Название сущности
     * @param array $aParams    Параметры для передачи в конструктор
     * @return Entity
     */
    static public function Ent($sName, $aParams = array()) {

        return Engine::GetEntity($sName, $aParams);
    }

    /**
     * Возвращает объект маппера
     * @see Engine::GetMapper
     *
     * @param $sClassName Класс модуля маппера
     * @param string|null $sName    Имя маппера
     * @param DbSimple_Mysql|null $oConnect    Объект коннекта к БД
     * @return mixed
     */
    static public function Mpr($sClassName, $sName = null, $oConnect = null) {

        return Engine::GetMapper($sClassName, $sName, $oConnect);
    }

    /**
     * Возвращает текущего авторизованного пользователя
     * @see ModuleUser::GetUserCurrent
     *
     * @return ModuleUser_EntityUser
     */
    static public function CurUsr() {

        return E::User();
    }

    /**
     * Возвращает true если текущий пользователь администратор
     * @see ModuleUser::GetUserCurrent
     * @see ModuleUser_EntityUser::isAdministrator
     *
     * @return bool
     */
    static public function Adm() {

        return E::IsAdmin();
    }

    /**
     * Вызов метода модуля
     * Например <pre>$LS->Module_Method()</pre>
     *
     * @param $sName    Полное название метода, например <pre>Module_Method</pre>
     * @param array $aArgs Список аргуметов метода
     * @return mixed
     */
    public function __call($sName, $aArgs = array()) {

        return call_user_func_array(array(self::E(), $sName), $aArgs);
    }

    /**
     * Статический вызов метода модуля для PHP >= 5.3
     * Например <pre>LS::Module_Method()</pre>
     *
     * @static
     * @param $sName    Полное название метода, например <pre>Module_Method</pre>
     * @param array $aArgs Список аргуметов метода
     * @return mixed
     */
    public static function __callStatic($sName, $aArgs = array()) {
        
        return call_user_func_array(array(self::E(), $sName), $aArgs);
    }
}


// EOF