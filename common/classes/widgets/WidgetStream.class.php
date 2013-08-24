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
 * Обработка виджета с комментариями (прямой эфир)
 *
 * @package widgets
 * @since 1.0
 */
class WidgetStream extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {
        // * Получаем комментарии
        if ($aComments = $this->Comment_GetCommentsOnline('topic', Config::Get('block.stream.row'))) {
            $oViewer = $this->Viewer_GetLocalViewer();
            $oViewer->Assign('aComments', $aComments);

            // * Формируем результат в виде шаблона и возвращаем
            $sTextResult = $oViewer->Fetch('widgets/widget.stream_comment.tpl');
            $this->Viewer_Assign('sStreamComments', $sTextResult);
        }
    }
}

// EOF