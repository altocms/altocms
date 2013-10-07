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
 * Обработка виджета с комментариями (прямой эфир)
 *
 * @package widgets
 * @since   1.0
 */
class WidgetStream extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {

        // * Получаем комментарии
        if ($aComments = $this->Comment_GetCommentsOnline('topic', Config::Get('block.stream.row'))) {
            $aVars = array('aComments' => $aComments);

            // * Формируем результат в виде шаблона и возвращаем
            $sTextResult = $this->Viewer_FetchWidget('stream_comment.tpl', $aVars);
            $this->Viewer_Assign('sStreamComments', $sTextResult);
        }
    }
}

// EOF