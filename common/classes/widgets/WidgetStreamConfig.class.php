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
        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {

            // * Получаем и прогружаем необходимые переменные в шаблон
            $aTypesList = E::ModuleStream()->GetTypesList($oUserCurrent->getId());
            $aUserSubscribes = E::ModuleStream()->GetUserSubscribes($oUserCurrent->getId());
            $aFriends = E::ModuleUser()->GetUsersFriend($oUserCurrent->getId());

            E::ModuleViewer()->Assign('aStreamTypesList', $aTypesList);
            E::ModuleViewer()->Assign('aStreamSubscribedUsers', $aUserSubscribes);
            E::ModuleViewer()->Assign('aStreamFriends', $aFriends['collection']);
        }
    }
}

// EOF