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
 * Виджет настройки ленты активности (события)
 *
 * @package blocks
 * @since   1.0
 */
class WidgetActivitySettings extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * пользователь авторизован?
         */
        if ($oUserCurrent = $this->User_GetUserCurrent()) {
            // * Получаем и прогружаем необходимые переменные в шаблон
            $aTypesList = $this->Stream_GetTypesList($oUserCurrent->getId());
            $this->Viewer_Assign('aStreamTypesList', $aTypesList ? $aTypesList : array());
        }
    }
}

// EOF