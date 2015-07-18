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

        $iLimit = C::Val('widgets.tags.params.limit', 70);
        // * Получаем список тегов
        $aTags = E::ModuleTopic()->GetOpenTopicTags($iLimit);

        // * Расчитываем логарифмическое облако тегов
        if ($aTags) {
            E::ModuleTools()->MakeCloud($aTags);

            // * Устанавливаем шаблон вывода
            E::ModuleViewer()->Assign('aTags', $aTags);
        }

        // * Теги пользователя
        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {
            $aTags = E::ModuleTopic()->GetOpenTopicTags($iLimit, $oUserCurrent->getId());

            // * Расчитываем логарифмическое облако тегов
            if ($aTags) {
                E::ModuleTools()->MakeCloud($aTags);

                // * Устанавливаем шаблон вывода
                E::ModuleViewer()->Assign('aTagsUser', $aTags);
            }
        }
    }
}

// EOF