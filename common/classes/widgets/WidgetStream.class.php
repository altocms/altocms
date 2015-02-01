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

        /*
        // * Получаем комментарии
        if ($aComments = E::ModuleComment()->GetCommentsOnline('topic', Config::Get('widgets.stream.params.limit'))) {
            $aVars = array('aComments' => $aComments);

            // * Формируем результат в виде шаблона и возвращаем
            $sTextResult = $this->Fetch('stream_comments.tpl', $aVars);
            E::ModuleViewer()->Assign('sStreamComments', $sTextResult);
        }
        */
    }
}

// EOF