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
 * Обработка виджета с комментариями (прямой эфир)
 *
 * @package widgets
 * @since   1.0
 */
class PluginLs_WidgetStream extends Widget {
    /**
     * Запуск обработки
     */
    public function Exec() {

        $iLimit = C::Get('block.stream.row');
        if (empty($iLimit)) {
            $iLimit = C::Get('widgets.stream.params.limit');
        }
        if (empty($iLimit)) {
            $iLimit = 20;
        }
        // * Получаем комментарии
        if ($aComments = $this->Comment_GetCommentsOnline('topic', $iLimit)) {
            $aVars = array('aComments' => $aComments);

            // * Формируем результат в виде шаблона и возвращаем
            $sTextResult = $this->Fetch('stream_comment.tpl', $aVars);
            $this->Viewer_Assign('sStreamComments', $sTextResult);
        }
    }
}

// EOF