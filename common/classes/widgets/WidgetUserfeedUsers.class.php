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
 * Блок настройки списка пользователей в ленте
 *
 * @package widgets
 * @since   1.0
 */
class WidgetUserfeedUsers extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Пользователь авторизован?
         */
        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {
            /**
             * Получаем необходимые переменные и передаем в шаблон
             */
            $aUserSubscribes = E::ModuleUserfeed()->GetUserSubscribes($oUserCurrent->getId());
            $aFriends = E::ModuleUser()->GetUsersFriend($oUserCurrent->getId());
            E::ModuleViewer()->assign('aUserfeedSubscribedUsers', $aUserSubscribes['users']);
            E::ModuleViewer()->assign('aUserfeedFriends', $aFriends['collection']);
        }
    }
}

// EOF