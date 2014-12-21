<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------*/

/**
 * HookSnippet.class.php
 * Файл хука сниппетов
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @version     0.0.1.1 от 21.12.2014 21:45
 * @since       1.1
 */
class HookSnippet extends Hook {

    /**
     * Регистрация хуков
     */
    public function RegisterHook() {
        $this->AddHook('snippet_user', 'SnippetUser');
    }

    /**
     * Метод осуществляет обработку сниппета вставки имени
     * пользователя.
     *
     * @param $aData
     * @return bool
     */
    public function SnippetUser($aData) {

        // Получим параметры, собственно, он тут единственный - это
        // имя пользователя которое и добавляем в редактор
        if (!($sUserName = isset($aData['params']['name']) ? $aData['params']['name'] : FALSE)) {
            return FALSE;
        }

        // Если пользователь найден, то вернём ссылку на него
        if (is_string($sUserName) && ($oUser = $this->User_GetUserByLogin($sUserName))) {
            return ($aData['result'] = "<a href='{$oUser->getUserWebPath()}'>{$oUser->getLogin()}</a>");
        }

        // Иначе, затрём сниппет
        return FALSE;

    }

}
