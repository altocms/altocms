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
        if ($oUserCurrent = $this->User_GetUserCurrent()) {
            /**
             * Получаем необходимые переменные и передаем в шаблон
             */
            $aUserSubscribes = $this->Userfeed_GetUserSubscribes($oUserCurrent->getId());
            $aFriends = $this->User_GetUsersFriend($oUserCurrent->getId());
            $this->Viewer_Assign('aUserfeedSubscribedUsers', $aUserSubscribes['users']);
            $this->Viewer_Assign('aUserfeedFriends', $aFriends['collection']);
        }
    }
}

// EOF