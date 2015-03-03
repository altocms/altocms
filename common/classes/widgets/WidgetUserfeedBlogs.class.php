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
 * Блок настройки списка блогов в ленте
 *
 * @package widgets
 * @since   1.0
 */
class WidgetUserfeedBlogs extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Пользователь авторизован?
         */
        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {
            $aUserSubscribes = E::ModuleUserfeed()->GetUserSubscribes($oUserCurrent->getId());
            /**
             * Получаем список ID блогов, в которых состоит пользователь
             */
            $aBlogsId = E::ModuleBlog()->GetBlogUsersByUserId(
                $oUserCurrent->getId(), array(ModuleBlog::BLOG_USER_ROLE_USER, ModuleBlog::BLOG_USER_ROLE_MODERATOR,
                                              ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR), true
            );
            /**
             * Получаем список ID блогов, которые создал пользователь
             */
            $aBlogsOwnerId = E::ModuleBlog()->GetBlogsByOwnerId($oUserCurrent->getId(), true);
            $aBlogsId = array_merge($aBlogsId, $aBlogsOwnerId);

            $aBlogs = E::ModuleBlog()->GetBlogsAdditionalData(
                $aBlogsId, array('owner' => array()), array('blog_title' => 'asc')
            );
            /**
             * Выводим в шаблон
             */
            E::ModuleViewer()->Assign('aUserfeedSubscribedBlogs', $aUserSubscribes['blogs']);
            E::ModuleViewer()->Assign('aUserfeedBlogs', $aBlogs);
        }
    }
}

// EOF