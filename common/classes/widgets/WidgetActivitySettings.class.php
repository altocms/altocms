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
        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {
            // * Получаем и прогружаем необходимые переменные в шаблон
            $aTypesList = E::ModuleStream()->GetTypesList($oUserCurrent->getId());
            E::ModuleViewer()->assign('aStreamTypesList', $aTypesList ? $aTypesList : array());
        }
    }
}

// EOF