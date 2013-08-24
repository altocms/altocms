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
 * Обрабатывает виджет облака тегов городов юзеров
 *
 * @package widgets
 * @since 1.0
 */
class WidgetTagsCity extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Получаем города
         */
        $aCities = $this->Geo_GetGroupCitiesByTargetType('user', 20);
        /**
         * Формируем облако тегов
         */
        $this->Tools_MakeCloud($aCities);
        /**
         * Выводим в шаблон
         */
        $this->Viewer_Assign('aCityList', $aCities);
    }
}

// EOF