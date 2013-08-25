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
 * Обработка виджета с рейтингом блогов
 *
 * @package widgets
 * @since   1.0
 */
class WidgetBlogs extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Получаем список блогов
         */
        if ($aResult = $this->Blog_GetBlogsRating(1, Config::Get('block.blogs.row'))) {
            $aBlogs = $aResult['collection'];
            $oViewer = $this->Viewer_GetLocalViewer();
            $oViewer->Assign('aBlogs', $aBlogs);

            // * Формируем результат в виде шаблона и возвращаем
            $sTextResult = $oViewer->FetchWidget('blogs_top.tpl');
            $this->Viewer_Assign('sBlogsTop', $sTextResult);
        }
    }
}

// EOF