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
 * Обрабатывает виджет облака тегов стран юзеров
 *
 * @package widgets
 * @since   1.0
 */
class WidgetTagsCountry extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Получаем страны
         */
        $aCountries = E::ModuleGeo()->GetGroupCountriesByTargetType('user', 20);
        /**
         * Формируем облако тегов
         */
        E::ModuleTools()->MakeCloud($aCountries);
        /**
         * Выводим в шаблон
         */
        E::ModuleViewer()->Assign('aCountryList', $aCountries);
    }
}

// EOF