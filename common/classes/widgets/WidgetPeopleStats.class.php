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
 * Обрабатывает виджет облака тегов городов юзеров
 *
 * @package widgets
 * @since   1.1.5
 */
class WidgetPeopleStats extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {

        // Статистика кто, где и т.п.
        $aPeopleStats = E::ModuleUser()->GetStatUsers();

        // Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPeopleStats', $aPeopleStats);
    }
}

// EOF