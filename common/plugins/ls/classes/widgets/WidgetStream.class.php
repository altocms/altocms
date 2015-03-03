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

        // * Получаем комментарии
        if ($aComments = E::ModuleComment()->GetCommentsOnline('topic', Config::Get('widgets.stream.params.limit'))) {
            $aVars = array('aComments' => $aComments);

            // * Формируем результат в виде шаблона и возвращаем
            $sTextResult = $this->Fetch('stream_comment.tpl', $aVars);
            E::ModuleViewer()->Assign('sStreamComments', $sTextResult);
        }
    }
}

// EOF