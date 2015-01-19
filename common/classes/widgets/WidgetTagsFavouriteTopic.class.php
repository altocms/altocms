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
 * Обрабатывает виджет облака тегов для избранного
 *
 * @package widgets
 * @since   1.0
 */
class WidgetTagsFavouriteTopic extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {

        // * Пользователь авторизован?
        if ($oUserCurrent = E::User_GetUserCurrent()) {
            if (!($oUser = $this->getParam('user'))) {
                $oUser = $oUserCurrent;
            }

            // * Получаем список тегов
            $aTags = E::Favourite_GetGroupTags($oUser->getId(), 'topic', false, 70);

            // * Расчитываем логарифмическое облако тегов
            E::Tools_MakeCloud($aTags);

            // * Устанавливаем шаблон вывода
            E::Viewer_Assign('aFavouriteTopicTags', $aTags);

            // * Получаем список тегов пользователя
            $aTags = E::Favourite_GetGroupTags($oUser->getId(), 'topic', true, 70);

            // * Расчитываем логарифмическое облако тегов
            E::Tools_MakeCloud($aTags);

            // * Устанавливаем шаблон вывода
            E::Viewer_Assign('aFavouriteTopicUserTags', $aTags);
            E::Viewer_Assign('oFavouriteUser', $oUser);
        }
    }
}

// EOF