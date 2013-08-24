<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * Блок настройки списка пользователей в ленте
 *
 * @package widgets
 * @since 1.0
 */
class WidgetUserfeedUsers extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Пользователь авторизован?
         */
        if ($oUserCurrent = $this->User_getUserCurrent()) {
            /**
             * Получаем необходимые переменные и передаем в шаблон
             */
            $aUserSubscribes = $this->Userfeed_getUserSubscribes($oUserCurrent->getId());
            $aFriends = $this->User_getUsersFriend($oUserCurrent->getId());
            $this->Viewer_Assign('aUserfeedSubscribedUsers', $aUserSubscribes['users']);
            $this->Viewer_Assign('aUserfeedFriends', $aFriends['collection']);
        }
    }
}

// EOF