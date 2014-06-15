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
 * Блок настройки ленты активности (LS compatibility)
 *
 * @package blocks
 * @since   1.0
 */
class WidgetStreamConfig extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {

        // * пользователь авторизован?
        if ($oUserCurrent = $this->User_GetUserCurrent()) {

            // * Получаем и прогружаем необходимые переменные в шаблон
            $aTypesList = $this->Stream_GetTypesList($oUserCurrent->getId());
            $aUserSubscribes = $this->Stream_GetUserSubscribes($oUserCurrent->getId());
            $aFriends = $this->User_GetUsersFriend($oUserCurrent->getId());

            $this->Viewer_Assign('aStreamTypesList', $aTypesList);
            $this->Viewer_Assign('aStreamSubscribedUsers', $aUserSubscribes);
            $this->Viewer_Assign('aStreamFriends', $aFriends['collection']);
        }
    }
}

// EOF