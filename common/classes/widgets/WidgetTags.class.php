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
 * Обрабатывает виджет облака тегов
 *
 * @package widgets
 * @since   1.0
 */
class WidgetTags extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Получаем список тегов
         */
        $aTags = E::Topic_GetOpenTopicTags(Config::Get('widgets.tags.params.limit'));
        /**
         * Расчитываем логарифмическое облако тегов
         */
        if ($aTags) {
            $this->Tools_MakeCloud($aTags);
            /**
             * Устанавливаем шаблон вывода
             */
            $this->Viewer_Assign('aTags', $aTags);
        }
        /**
         * Теги пользователя
         */
        if ($oUserCurrent = $this->User_GetUserCurrent()) {
            $aTags = E::Topic_GetOpenTopicTags(
                Config::Get('widgets.tags.params.limit'), $oUserCurrent->getId()
            );
            /**
             * Расчитываем логарифмическое облако тегов
             */
            if ($aTags) {
                $this->Tools_MakeCloud($aTags);
                /**
                 * Устанавливаем шаблон вывода
                 */
                $this->Viewer_Assign('aTagsUser', $aTags);
            }
        }
    }
}

// EOF